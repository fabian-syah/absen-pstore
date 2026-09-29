<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeEvaluation;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeEvaluationController extends Controller
{
    /**
     * Tampilkan daftar karyawan untuk dinilai.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Pengecekan Hak Akses
        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Hapus stale route cache jika ada
        $routeCache = base_path('bootstrap/cache/routes-v7.php');
        if (file_exists($routeCache)) {
            @unlink($routeCache);
        }

        // Direct Download All Pusat PDF jika diakses lewat query parameter
        if ($request->has('download_pusat_pdf') || $request->has('export_all_pusat')) {
            return $this->exportAllPusatPdf($request);
        }

        $userQuery = function ($q) {
            $q->where('is_active', true);
        };

        $evaluatedQuery = function ($q) {
            $q->where('is_active', true)
                ->whereHas('employeeEvaluations');
        };

        // Ambil daftar cabang
        if ($user->role === 'admin') {
            $branches = Branch::withCount([
                'users as users_count' => $userQuery,
                'users as evaluated_users_count' => $evaluatedQuery,
            ])->orderBy('name')->get();
        } else {
            $branches = collect();

            $isTeamAuditNonLeader = ($user->branch_id == 64 && $user->role !== 'leader' && !in_array(strtolower($user->login_id ?? ''), ['herlina', 'eva', 'agung', 'adminherlina']));

            // Branch utama
            if ($user->branch_id) {
                $mainBranch = Branch::withCount([
                    'users as users_count' => $userQuery,
                    'users as evaluated_users_count' => $evaluatedQuery,
                ])->find($user->branch_id);
                if ($mainBranch && !$isTeamAuditNonLeader) {
                    $branches->push($mainBranch);
                }
            }

            // Branch kelolaan
            $managedBranches = $user->branches()->withCount([
                'users as users_count' => $userQuery,
                'users as evaluated_users_count' => $evaluatedQuery,
            ])->orderBy('name')->get();

            foreach ($managedBranches as $mb) {
                if (!$branches->contains('id', $mb->id)) {
                    if ($mb->id == 64 && $isTeamAuditNonLeader) {
                        continue;
                    }
                    $branches->push($mb);
                }
            }
        }

        // Pisahkan cabang Pusat dan Cabang Operasional
        $pusatBranches = $branches->filter(fn($b) => $b->is_pusat)->values();
        $cabangBranches = $branches->filter(fn($b) => !$b->is_pusat)->values();

        // Hitung total karyawan dan yang sudah dinilai
        $totalPusatUsers = $pusatBranches->sum('users_count');
        $evaluatedPusatUsers = $pusatBranches->sum('evaluated_users_count');

        $totalCabangUsers = $cabangBranches->sum('users_count');
        $evaluatedCabangUsers = $cabangBranches->sum('evaluated_users_count');

        return view('employee_evaluations.branches', compact(
            'branches',
            'pusatBranches',
            'cabangBranches',
            'totalPusatUsers',
            'evaluatedPusatUsers',
            'totalCabangUsers',
            'evaluatedCabangUsers'
        ));
    }

    /**
     * Tampilkan daftar karyawan dalam satu cabang.
     */
    public function branchEmployees($branch_id, Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $branch = Branch::withCount([
            'users as users_count' => function ($q) {
                $q->where('is_active', true);
            },
            'users as evaluated_users_count' => function ($q) {
                $q->where('is_active', true)->whereHas('employeeEvaluations');
            }
        ])->findOrFail($branch_id);

        // Hanya ambil user yang branch utamanya adalah cabang ini
        $query = User::with(['branch', 'division'])
            ->where('is_active', true)
            ->where('branch_id', $branch_id);

        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(20)->appends($request->all());

        return view('employee_evaluations.index', compact('users', 'branch'));
    }

    /**
     * Tampilkan form pengisian rapor bulanan.
     */
    public function form($user_id, Request $request)
    {
        $employee = User::findOrFail($user_id);

        // Hanya admin, audit, leader, atau karyawan yang bersangkutan yang bisa melihat rapor
        if (!in_array(Auth::user()->role, ['admin', 'audit', 'leader']) && Auth::id() != $user_id) {
            abort(403, 'Anda tidak memiliki akses ke rapor karyawan lain.');
        }

        $date = $request->get('date', now()->format('Y-m-d'));
        // Fallback backward compatibility for month/year if they still access old URLs
        if ($request->has('month') && $request->has('year')) {
            $date = $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '-01';
        }

        // Cek apakah sudah ada evaluasi di tanggal tersebut
        $evaluation = EmployeeEvaluation::where('user_id', $user_id)
            ->whereDate('evaluation_date', $date)
            ->first();

        $isReadOnly = $evaluation !== null;

        return view('employee_evaluations.form', compact('employee', 'evaluation', 'date', 'isReadOnly'));
    }

    /**
     * Simpan atau update rapor.
     */
    public function store(Request $request, $user_id)
    {
        $date = now()->format('Y-m-d');

        // Cek apakah sudah dinilai hari ini
        $existing = EmployeeEvaluation::where('user_id', $user_id)
            ->whereDate('evaluation_date', $date)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Karyawan ini sudah dinilai hari ini. Tidak bisa direvisi di hari yang sama.');
        }

        $request->validate([
            'kecerdasan_score' => 'nullable|integer|min:0|max:100',
            'amanah_score' => 'nullable|integer|min:0|max:100',
            'sosial_media_score' => 'nullable|integer|min:0|max:100',
            'kepemimpinan_score' => 'nullable|integer|min:0|max:100',
            'data_ketelitian_score' => 'nullable|integer|min:0|max:100',
            'komunikasi_score' => 'nullable|integer|min:0|max:100',
            'kedisiplinan_score' => 'nullable|integer|min:0|max:100',
            'custom_score' => 'nullable|integer|min:0|max:100',
        ]);

        $user = User::findOrFail($user_id);

        // Hitung rata-rata
        $scores = collect([
            $request->kecerdasan_score,
            $request->amanah_score,
            $request->sosial_media_score,
            $request->kepemimpinan_score,
            $request->data_ketelitian_score,
            $request->komunikasi_score,
            $request->kedisiplinan_score,
            $request->custom_score
        ])->filter(function ($score) {
            return $score !== null && $score !== '';
        });

        $average_score = $request->filled('average_score') ? $request->average_score : ($scores->count() > 0 ? $scores->average() : 0);

        // Tentukan Grade
        if ($request->filled('grade')) {
            $grade = $request->grade;
        } else {
            $grade = 'D';
            if ($average_score >= 95) $grade = 'A+';
            elseif ($average_score >= 90) $grade = 'A';
            elseif ($average_score >= 85) $grade = 'B+';
            elseif ($average_score >= 80) $grade = 'B';
            elseif ($average_score >= 70) $grade = 'C';
        }

        EmployeeEvaluation::create(
            [
                'user_id' => $user_id,
                'evaluation_date' => $date,
                'month' => now()->month,
                'year' => now()->year,
                'assessor_id' => Auth::id(),
                'kecerdasan_score' => $request->kecerdasan_score,
                'kecerdasan_note' => $request->kecerdasan_note,
                'amanah_score' => $request->amanah_score,
                'amanah_note' => $request->amanah_note,
                'sosial_media_score' => $request->sosial_media_score,
                'sosial_media_note' => $request->sosial_media_note,
                'kepemimpinan_score' => $request->kepemimpinan_score,
                'kepemimpinan_note' => $request->kepemimpinan_note,
                'data_ketelitian_score' => $request->data_ketelitian_score,
                'data_ketelitian_note' => $request->data_ketelitian_note,
                'komunikasi_score' => $request->komunikasi_score,
                'komunikasi_note' => $request->komunikasi_note,
                'kedisiplinan_score' => $request->kedisiplinan_score,
                'kedisiplinan_note' => $request->kedisiplinan_note,
                'custom_title' => $request->custom_title,
                'custom_score' => $request->custom_score,
                'custom_note' => $request->custom_note,
                'average_score' => $average_score,
                'grade' => $grade,
                'final_remark' => $request->final_remark,
            ]
        );

        return redirect()->route('employee-evaluations.branch-employees', $user->branch_id ?? 1)->with('success', 'Rapor karyawan berhasil disimpan!');
    }

    public function exportPdf(Request $request, $user_id)
    {
        ini_set('memory_limit', '512M');

        $user = User::findOrFail($user_id);

        $currentUser = Auth::user();
        if ($currentUser->id != $user_id && !in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $evaluationQuery = EmployeeEvaluation::with('assessor')->where('user_id', $user_id);
        
        if ($request->has('id')) {
            $evaluationQuery->where('id', $request->query('id'));
        } elseif ($request->has('date')) {
            $evaluationQuery->whereDate('evaluation_date', $request->query('date'));
        }

        $evaluation = $evaluationQuery->orderBy('evaluation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$evaluation) {
            return back()->with('error', 'Data evaluasi tidak ditemukan.');
        }

        $date = $evaluation->evaluation_date ?? now()->format('Y-m-d');

        $labels = ['Kecerdasan', 'Amanah', 'Sosial media', 'Kepemimpinan', 'Data & ketelitian', 'Komunikasi', 'Kedisiplinan'];
        $dataScores = [
            (int) $evaluation->kecerdasan_score,
            (int) $evaluation->amanah_score,
            (int) $evaluation->sosial_media_score,
            (int) $evaluation->kepemimpinan_score,
            (int) $evaluation->data_ketelitian_score,
            (int) $evaluation->komunikasi_score,
            (int) $evaluation->kedisiplinan_score
        ];

        if ($evaluation->custom_score !== null && $evaluation->custom_score !== '') {
            $labels[] = $evaluation->custom_title ?? 'Kriteria Tambahan';
            $dataScores[] = (int) $evaluation->custom_score;
        }

        $chartData = [
            'type' => 'radar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Nilai',
                        'data' => $dataScores,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'pointBackgroundColor' => 'rgba(54, 162, 235, 1)',
                        'pointBorderColor' => '#fff',
                    ]
                ]
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'display' => true,
                        'color' => '#000000',
                        'align' => 'bottom',
                        'font' => ['weight' => 'bold', 'size' => 12],
                        'backgroundColor' => 'rgba(255, 255, 255, 0.7)',
                        'borderRadius' => 3
                    ]
                ],
                'scale' => [
                    'pointLabels' => [
                        'fontColor' => '#000000',
                        'fontStyle' => 'bold',
                        'fontSize' => 14
                    ],
                    'ticks' => [
                        'beginAtZero' => true,
                        'max' => 100,
                        'min' => 0,
                        'stepSize' => 20,
                        'display' => false
                    ]
                ]
            ]
        ];
        
        $chartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartData)) . '&w=400&h=400';
        $chartImage = null;
        try {
            $imageContent = file_get_contents($chartUrl);
            if ($imageContent) {
                $chartImage = 'data:image/png;base64,' . base64_encode($imageContent);
            }
        } catch (\Exception $e) {
            // Biarkan null jika gagal fetch chart
        }

        $photoUrl = self::getSquareProfilePhotoBase64($user->profile_photo_path, 280);

        $pdf = app('dompdf.wrapper')->loadView('pdf.employee-evaluation', compact('user', 'evaluation', 'date', 'chartImage', 'photoUrl'));
        $paperSize = in_array(strtolower($request->query('paper', 'a4')), ['a4', 'a5']) ? strtolower($request->query('paper', 'a4')) : 'a4';
        $pdf->setPaper($paperSize, 'portrait');

        $dateFormatted = \Carbon\Carbon::parse($date)->translatedFormat('d_F_Y');
        $fileName = 'Rapor_Karyawan_' . str_replace(' ', '_', $user->name) . '_' . $dateFormatted . '.pdf';

        return $pdf->stream($fileName);
    }

    public function exportBranchPdf(Request $request, $branch_id)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $currentUser = Auth::user();
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $branch = Branch::findOrFail($branch_id);

        $date = $request->query('date', now()->format('Y-m-d'));

        $users = User::where('branch_id', $branch_id)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN role = 'leader' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada karyawan di cabang ini.');
        }

        $evaluations = EmployeeEvaluation::whereIn('user_id', $users->pluck('id'))
            ->orderBy('evaluation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        // Generate QuickChart & Photos for each user
        $userCharts = [];
        $userPhotos = [];
        foreach ($users as $u) {
            // Photo (upright & square)
            $userPhotos[$u->id] = self::getSquareProfilePhotoBase64($u->profile_photo_path, 200);

            $eval = $evaluations->get($u->id);
            if ($eval) {
                $labels = ['Kecerdasan', 'Amanah', 'Sosial media', 'Kepemimpinan', 'Data & ketelitian', 'Komunikasi', 'Kedisiplinan'];
                $dataScores = [
                    (int) $eval->kecerdasan_score,
                    (int) $eval->amanah_score,
                    (int) $eval->sosial_media_score,
                    (int) $eval->kepemimpinan_score,
                    (int) $eval->data_ketelitian_score,
                    (int) $eval->komunikasi_score,
                    (int) $eval->kedisiplinan_score
                ];

                if ($eval->custom_score !== null && $eval->custom_score !== '') {
                    $labels[] = $eval->custom_title ?? 'Kriteria Tambahan';
                    $dataScores[] = (int) $eval->custom_score;
                }

                $chartData = [
                    'type' => 'radar',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Nilai',
                                'data' => $dataScores,
                                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                                'borderColor' => 'rgba(54, 162, 235, 1)',
                                'pointBackgroundColor' => 'rgba(54, 162, 235, 1)',
                                'pointBorderColor' => '#fff',
                            ]
                        ]
                    ],
                    'options' => [
                        'plugins' => [
                            'legend' => ['display' => false],
                            'datalabels' => [
                                'display' => true,
                                'color' => '#000000',
                                'align' => 'bottom',
                                'font' => ['weight' => 'bold', 'size' => 10],
                                'backgroundColor' => 'rgba(255, 255, 255, 0.7)',
                                'borderRadius' => 3
                            ]
                        ],
                        'scale' => [
                            'pointLabels' => [
                                'fontColor' => '#000000',
                                'fontStyle' => 'bold',
                                'fontSize' => 11
                            ],
                            'ticks' => [
                                'beginAtZero' => true,
                                'max' => 100,
                                'min' => 0,
                                'stepSize' => 20,
                                'display' => false
                            ]
                        ]
                    ]
                ];
                $chartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartData)) . '&w=300&h=300';
                try {
                    $imageContent = file_get_contents($chartUrl);
                    if ($imageContent) {
                        $userCharts[$u->id] = 'data:image/png;base64,' . base64_encode($imageContent);
                    } else {
                        $userCharts[$u->id] = null;
                    }
                } catch (\Exception $e) {
                    $userCharts[$u->id] = null;
                }
            } else {
                $userCharts[$u->id] = null;
            }
        }

        $pdf = app('dompdf.wrapper')->loadView('pdf.branch-evaluation', compact('branch', 'users', 'evaluations', 'date', 'userCharts', 'userPhotos'));
        $paperSize = in_array(strtolower($request->query('paper', 'a4')), ['a4', 'a5']) ? strtolower($request->query('paper', 'a4')) : 'a4';
        $pdf->setPaper($paperSize, 'portrait');

        $dateFormatted = \Carbon\Carbon::parse($date)->translatedFormat('d_F_Y');
        $fileName = 'Rapor_Cabang_' . str_replace(' ', '_', $branch->name) . '_' . $dateFormatted . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Download All Pusat PDF Rapor untuk karyawan yang sudah dinilai.
     */
    public function exportAllPusatPdf(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $currentUser = Auth::user();
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader', 'admin_gaji'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $date = $request->query('date', now()->format('Y-m-d'));

        // Ambil daftar nama unit Pusat
        $pusatList = Branch::pusatList();
        $pusatBranches = Branch::whereIn('name', $pusatList)->get();
        $pusatBranchIds = $pusatBranches->pluck('id');

        // Ambil karyawan aktif di unit Pusat yang SUDAH DINILAI (punya data di employee_evaluations)
        $users = User::with(['branch', 'division', 'divisions'])
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->whereIn('users.branch_id', $pusatBranchIds)
            ->where('users.is_active', true)
            ->whereHas('employeeEvaluations')
            ->orderBy('branches.name', 'asc')
            ->orderByRaw("CASE WHEN users.role = 'leader' THEN 1 ELSE 2 END")
            ->orderBy('users.name', 'asc')
            ->select('users.*')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Belum ada karyawan di unit Pusat yang sudah dinilai.');
        }

        // Ambil evaluasi terbaru untuk tiap user
        $evaluations = EmployeeEvaluation::whereIn('user_id', $users->pluck('id'))
            ->orderBy('evaluation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        // Generate Photos and QuickCharts
        $userCharts = [];
        $userPhotos = [];
        $chartUrls = [];

        foreach ($users as $u) {
            // Photo (upright & square)
            $userPhotos[$u->id] = self::getSquareProfilePhotoBase64($u->profile_photo_path, 200);

            // Radar Chart Data
            $eval = $evaluations->get($u->id);
            if ($eval) {
                $labels = ['Kecerdasan', 'Amanah', 'Sosial media', 'Kepemimpinan', 'Data & ketelitian', 'Komunikasi', 'Kedisiplinan'];
                $dataScores = [
                    (int) $eval->kecerdasan_score,
                    (int) $eval->amanah_score,
                    (int) $eval->sosial_media_score,
                    (int) $eval->kepemimpinan_score,
                    (int) $eval->data_ketelitian_score,
                    (int) $eval->komunikasi_score,
                    (int) $eval->kedisiplinan_score
                ];

                if ($eval->custom_score !== null && $eval->custom_score !== '') {
                    $labels[] = $eval->custom_title ?? 'Kriteria Tambahan';
                    $dataScores[] = (int) $eval->custom_score;
                }

                $chartData = [
                    'type' => 'radar',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Nilai',
                                'data' => $dataScores,
                                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                                'borderColor' => 'rgba(54, 162, 235, 1)',
                                'pointBackgroundColor' => 'rgba(54, 162, 235, 1)',
                                'pointBorderColor' => '#fff',
                            ]
                        ]
                    ],
                    'options' => [
                        'plugins' => [
                            'legend' => ['display' => false],
                            'datalabels' => [
                                'display' => true,
                                'color' => '#000000',
                                'align' => 'bottom',
                                'font' => ['weight' => 'bold', 'size' => 10],
                                'backgroundColor' => 'rgba(255, 255, 255, 0.7)',
                                'borderRadius' => 3
                            ]
                        ],
                        'scale' => [
                            'pointLabels' => [
                                'fontColor' => '#000000',
                                'fontStyle' => 'bold',
                                'fontSize' => 11
                            ],
                            'ticks' => [
                                'beginAtZero' => true,
                                'max' => 100,
                                'min' => 0,
                                'stepSize' => 20,
                                'display' => false
                            ]
                        ]
                    ]
                ];
                $chartUrls[$u->id] = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartData)) . '&w=300&h=300';
            }
        }

        // Fetch QuickCharts secara paralel (curl_multi) dengan timeout cepat
        if (!empty($chartUrls) && function_exists('curl_multi_init')) {
            $mh = curl_multi_init();
            $handles = [];
            foreach ($chartUrls as $userId => $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_multi_add_handle($mh, $ch);
                $handles[$userId] = $ch;
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh, 0.1);
            } while ($running > 0);

            foreach ($handles as $userId => $ch) {
                $content = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($content && $httpCode == 200) {
                    $userCharts[$userId] = 'data:image/png;base64,' . base64_encode($content);
                } else {
                    $userCharts[$userId] = null;
                }
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        } else {
            foreach ($chartUrls as $userId => $url) {
                try {
                    $ctx = stream_context_create([
                        'http' => ['timeout' => 2],
                        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                    ]);
                    $content = @file_get_contents($url, false, $ctx);
                    $userCharts[$userId] = $content ? 'data:image/png;base64,' . base64_encode($content) : null;
                } catch (\Exception $e) {
                    $userCharts[$userId] = null;
                }
            }
        }

        $pdf = app('dompdf.wrapper')->loadView('pdf.all-pusat-evaluation', compact(
            'users',
            'evaluations',
            'date',
            'userCharts',
            'userPhotos',
            'pusatBranches'
        ));

        $paperSize = in_array(strtolower($request->query('paper', 'a4')), ['a4', 'a5']) ? strtolower($request->query('paper', 'a4')) : 'a4';
        $pdf->setPaper($paperSize, 'portrait');

        $dateFormatted = \Carbon\Carbon::parse($date)->translatedFormat('d_F_Y');
        $fileName = 'Rapor_Semua_Pusat_' . $dateFormatted . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Mengambil foto profil karyawan, mengoreksi orientasi EXIF (agar tidak miring di DomPDF),
     * memotong (center-crop) ke rasio 1:1 bujur sangkar (agar tidak gepeng/terdistorsi),
     * dan mengonversinya menjadi base64 JPEG berkualitas tinggi.
     *
     * @param string|null $photoRelativePath
     * @param int $targetSize
     * @return string|null
     */
    public static function getSquareProfilePhotoBase64($photoRelativePath, $targetSize = 280)
    {
        if (empty($photoRelativePath)) {
            return null;
        }

        $cleanPath = ltrim($photoRelativePath, '/\\');
        $cleanPath = preg_replace('#^storage/#', '', $cleanPath);

        $pathsToTry = [
            public_path('storage/' . $cleanPath),
            storage_path('app/public/' . $cleanPath),
            public_path($cleanPath),
            base_path('public/storage/' . $cleanPath),
        ];

        $resolvedPath = null;
        foreach ($pathsToTry as $p) {
            if (file_exists($p) && is_file($p)) {
                $resolvedPath = $p;
                break;
            }
        }

        if (!$resolvedPath) {
            return null;
        }

        // Jika ekstensi GD tidak aktif, fallback ke base64 mentah
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            $mime = @mime_content_type($resolvedPath) ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($resolvedPath));
        }

        try {
            $data = file_get_contents($resolvedPath);
            if (!$data) {
                return null;
            }

            $image = @imagecreatefromstring($data);
            if (!$image) {
                $mime = @mime_content_type($resolvedPath) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($data);
            }

            // Koreksi orientasi EXIF kamera smartphone (iOS / Android)
            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($resolvedPath);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 2:
                            imageflip($image, IMG_FLIP_HORIZONTAL);
                            break;
                        case 3:
                            $rotated = imagerotate($image, 180, 0);
                            if ($rotated) {
                                imagedestroy($image);
                                $image = $rotated;
                            }
                            break;
                        case 4:
                            imageflip($image, IMG_FLIP_VERTICAL);
                            break;
                        case 5:
                            imageflip($image, IMG_FLIP_HORIZONTAL);
                            $rotated = imagerotate($image, -90, 0);
                            if ($rotated) {
                                imagedestroy($image);
                                $image = $rotated;
                            }
                            break;
                        case 6:
                            // 90 derajat searah jarum jam (CW)
                            $rotated = imagerotate($image, -90, 0);
                            if ($rotated) {
                                imagedestroy($image);
                                $image = $rotated;
                            }
                            break;
                        case 7:
                            imageflip($image, IMG_FLIP_HORIZONTAL);
                            $rotated = imagerotate($image, 90, 0);
                            if ($rotated) {
                                imagedestroy($image);
                                $image = $rotated;
                            }
                            break;
                        case 8:
                            // 90 derajat berlawanan jarum jam (CCW)
                            $rotated = imagerotate($image, 90, 0);
                            if ($rotated) {
                                imagedestroy($image);
                                $image = $rotated;
                            }
                            break;
                    }
                }
            }

            // Center-crop ke rasio 1:1 sempurna (menghilangkan efek gepeng)
            $srcW = imagesx($image);
            $srcH = imagesy($image);
            $cropSize = min($srcW, $srcH);
            $cropX = (int) (($srcW - $cropSize) / 2);
            $cropY = (int) (($srcH - $cropSize) / 2);

            $thumb = imagecreatetruecolor($targetSize, $targetSize);

            // Background putih jika transparan
            $white = imagecolorallocate($thumb, 255, 255, 255);
            imagefilledrectangle($thumb, 0, 0, $targetSize, $targetSize, $white);

            imagecopyresampled($thumb, $image, 0, 0, $cropX, $cropY, $targetSize, $targetSize, $cropSize, $cropSize);

            ob_start();
            imagejpeg($thumb, null, 88);
            $jpegContent = ob_get_clean();

            imagedestroy($thumb);
            imagedestroy($image);

            return 'data:image/jpeg;base64,' . base64_encode($jpegContent);
        } catch (\Throwable $e) {
            $mime = @mime_content_type($resolvedPath) ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($resolvedPath));
        }
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $branch_id = $request->get('branch_id');

        $userQuery = function ($q) {
            $q->where('is_active', true);
        };

        $evaluatedQuery = function ($q) {
            $q->where('is_active', true)
                ->whereHas('employeeEvaluations');
        };

        // Ambil daftar cabang yang boleh diakses
        $branches = collect();
        if ($user->role === 'admin') {
            $branches = Branch::withCount([
                'users as users_count' => $userQuery,
                'users as evaluated_users_count' => $evaluatedQuery,
            ])->orderBy('name')->get();
        } else {
            $isTeamAuditNonLeader = ($user->branch_id == 64 && $user->role !== 'leader' && !in_array(strtolower($user->login_id ?? ''), ['herlina', 'eva', 'agung', 'adminherlina']));

            if ($user->branch_id) {
                $mainBranch = Branch::withCount([
                    'users as users_count' => $userQuery,
                    'users as evaluated_users_count' => $evaluatedQuery,
                ])->find($user->branch_id);
                if ($mainBranch && !$isTeamAuditNonLeader) {
                    $branches->push($mainBranch);
                }
            }
            $managedBranches = $user->branches()->withCount([
                'users as users_count' => $userQuery,
                'users as evaluated_users_count' => $evaluatedQuery,
            ])->orderBy('name')->get();

            foreach ($managedBranches as $mb) {
                if (!$branches->contains('id', $mb->id)) {
                    if ($mb->id == 64 && $isTeamAuditNonLeader) {
                        continue;
                    }
                    $branches->push($mb);
                }
            }
        }

        // Pisahkan cabang Pusat dan Cabang Operasional
        $pusatBranches = $branches->filter(fn($b) => $b->is_pusat)->values();
        $cabangBranches = $branches->filter(fn($b) => !$b->is_pusat)->values();

        // Jika belum ada cabang yang dipilih, jangan tampilkan data
        if (!$branch_id) {
            $evaluations = collect(); // Kosongkan agar user harus pilih cabang dulu
            return view('employee_evaluations.history', compact('evaluations', 'branches', 'pusatBranches', 'cabangBranches', 'branch_id'));
        }

        $query = EmployeeEvaluation::with(['user', 'user.branch', 'assessor'])
            ->whereHas('user', function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            })
            ->orderBy('created_at', 'desc');

        // Jika leader, validasi apakah cabang yang dipilih berhak diakses
        if ($user->role == 'leader') {
            $allowedBranchIds = $branches->pluck('id')->toArray();
            if (!in_array($branch_id, $allowedBranchIds)) {
                return redirect()->route('employee-evaluations.history')->with('error', 'Anda tidak memiliki akses ke cabang ini.');
            }
        }

        $evaluations = $query->paginate(20)->appends($request->all());

        return view('employee_evaluations.history', compact('evaluations', 'branches', 'pusatBranches', 'cabangBranches', 'branch_id'));
    }

    public function myHistory(Request $request)
    {
        $user_id = Auth::id();

        $evaluations = EmployeeEvaluation::with('assessor')
            ->where('user_id', $user_id)
            ->orderBy('evaluation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('employee_evaluations.my_history', compact('evaluations'));
    }

    /**
     * Generate kesimpulan dan motivasi penilaian via Sekai Gateway AI
     * Menggunakan bansos/glm-5.3 sebagai prioritas (kuota gratis harian),
     * dan otomatis beralih (fallback) ke ds/deepseek-v4.1-flash jika kuota bansos habis/gagal.
     */
    public function generateAi(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $apiKey = env('SEKAI_API_KEY', 'sk-c98ae4ca8de19e0e-v5a0ku-e975f589');
        $primaryModel = env('SEKAI_BANSOS_MODEL', 'z-ai/glm-5.3-flash');
        $fallbackModel = env('SEKAI_AI_MODEL', 'ds/deepseek-v4.1-flash');
        $prompt = $request->input('prompt');

        try {
            $usedModel = $primaryModel;
            $data = $this->requestSekaiCompletion($apiKey, $primaryModel, $prompt);

            // Cek apakah model bansos berhasil memberikan hasil yang valid
            $isSuccess = isset($data['choices'][0]['message']) && (
                !empty($data['choices'][0]['message']['content']) || 
                !empty($data['choices'][0]['message']['reasoning_content'])
            );

            // Jika kuota bansos habis, rate limited, atau error, otomatis beralih ke DeepSeek V4.1 Flash
            if (!$isSuccess) {
                \Illuminate\Support\Facades\Log::warning("Bansos model {$primaryModel} tidak tersedia atau kuota habis. Otomatis beralih ke {$fallbackModel}. Respon bansos: " . json_encode($data));
                $usedModel = $fallbackModel;
                $data = $this->requestSekaiCompletion($apiKey, $fallbackModel, $prompt);
            }

            if (isset($data['choices'][0]['message'])) {
                $msg = $data['choices'][0]['message'];
                $content = $msg['content'] ?? $msg['reasoning_content'] ?? '';
                $content = trim($content, " \t\n\r\0\x0B\"'");
                $content = str_replace('*', '', $content);

                return response()->json([
                    'status' => 'success',
                    'remark' => $content,
                    'model'  => $usedModel,
                ]);
            }

            if (isset($data['error']['message'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $data['error']['message'],
                ], 400);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Respon AI tidak sesuai format.',
            ], 500);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper request completions ke Sekai Gateway
     */
    private function requestSekaiCompletion(string $apiKey, string $model, string $prompt): ?array
    {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah asisten HR yang profesional dan pandai memberikan evaluasi kinerja yang memotivasi.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'thinking' => [
                'type' => 'disabled'
            ],
            'temperature' => 0.7,
            'max_tokens' => 400,
        ];

        $rawBody = null;
        $baseUrl = rtrim(env('SEKAI_BASE_URL', 'https://api.sekaigateway.xyz/v2'), '/');
        $endpoint = $baseUrl . '/chat/completions';

        // Percobaan 1: Gunakan cURL bawaan PHP jika fungsi tersedia
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 45,
            ]);
            $exec = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if (!$err && $exec) {
                $rawBody = $exec;
            }
        }

        // Percobaan 2: Gunakan stream context (file_get_contents) jika cURL gagal atau tidak tersedia
        if (!$rawBody) {
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Authorization: Bearer {$apiKey}\r\nContent-Type: application/json\r\n",
                    'content' => json_encode($payload),
                    'timeout' => 45,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ];
            $rawBody = @file_get_contents($endpoint, false, stream_context_create($opts));
        }

        if (!$rawBody) {
            return null;
        }

        // Tangani trailing token "data: [DONE]" jika ada
        if (preg_match('/\{[\s\S]*\}/', $rawBody, $matches)) {
            return json_decode($matches[0], true);
        }

        return json_decode($rawBody, true);
    }
}
