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

        // Ambil daftar cabang
        if ($user->role === 'admin') {
            $branches = Branch::withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])->get();
        } else {
            $branches = collect();

            // Branch utama
            if ($user->branch_id) {
                $mainBranch = Branch::withCount(['users' => function ($q) {
                    $q->where('is_active', true);
                }])->find($user->branch_id);
                if ($mainBranch) $branches->push($mainBranch);
            }

            // Branch kelolaan
            $managedBranches = $user->branches()->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])->get();

            foreach ($managedBranches as $mb) {
                if (!$branches->contains('id', $mb->id)) {
                    $branches->push($mb);
                }
            }
        }

        return view('employee_evaluations.branches', compact('branches'));
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

        $branch = Branch::findOrFail($branch_id);

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
        $user = User::findOrFail($user_id);

        $currentUser = Auth::user();
        if ($currentUser->id != $user_id && !in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $date = $request->query('date', now()->format('Y-m-d'));

        $evaluation = EmployeeEvaluation::with('assessor')
            ->where('user_id', $user_id)
            ->whereDate('evaluation_date', $date)
            ->first();

        if (!$evaluation) {
            return back()->with('error', 'Data evaluasi tidak ditemukan untuk tanggal tersebut.');
        }

        $pdf = app('dompdf.wrapper')->loadView('pdf.employee-evaluation', compact('user', 'evaluation', 'date'));
        $pdf->setPaper('A4', 'portrait');

        $dateFormatted = \Carbon\Carbon::parse($date)->translatedFormat('d_F_Y');
        $fileName = 'Rapor_Karyawan_' . str_replace(' ', '_', $user->name) . '_' . $dateFormatted . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportBranchPdf(Request $request, $branch_id)
    {
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
            ->whereDate('evaluation_date', $date)
            ->get()
            ->keyBy('user_id');

        // Generate QuickChart for each user
        $userCharts = [];
        foreach ($users as $u) {
            $eval = $evaluations->get($u->id);
            if ($eval) {
                $chartData = [
                    'type' => 'radar',
                    'data' => [
                        'labels' => ['Kecerdasan', 'Amanah', 'Sosial media', 'Kepemimpinan', 'Data & ketelitian', 'Komunikasi', 'Kedisiplinan'],
                        'datasets' => [
                            [
                                'label' => 'Nilai',
                                'data' => [
                                    (int) $eval->kecerdasan_score,
                                    (int) $eval->amanah_score,
                                    (int) $eval->sosial_media_score,
                                    (int) $eval->kepemimpinan_score,
                                    (int) $eval->data_ketelitian_score,
                                    (int) $eval->komunikasi_score,
                                    (int) $eval->kedisiplinan_score
                                ],
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
                                'color' => '#1e40af',
                                'align' => 'bottom',
                                'font' => ['weight' => 'bold', 'size' => 10],
                                'backgroundColor' => 'rgba(255, 255, 255, 0.7)',
                                'borderRadius' => 3
                            ]
                        ],
                        'scale' => [
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

        $pdf = app('dompdf.wrapper')->loadView('pdf.branch-evaluation', compact('branch', 'users', 'evaluations', 'date', 'userCharts'));
        $pdf->setPaper('A4', 'portrait');

        $dateFormatted = \Carbon\Carbon::parse($date)->translatedFormat('d_F_Y');
        $fileName = 'Rapor_Cabang_' . str_replace(' ', '_', $branch->name) . '_' . $dateFormatted . '.pdf';

        return $pdf->download($fileName);
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $date = $request->get('date', now()->format('Y-m-d'));
        $branch_id = $request->get('branch_id');

        // Ambil daftar cabang yang boleh diakses
        $branches = collect();
        if ($user->role === 'admin' || $user->role === 'audit') {
            $branches = Branch::all();
        } else {
            if ($user->branch_id) {
                $mainBranch = Branch::find($user->branch_id);
                if ($mainBranch) $branches->push($mainBranch);
            }
            $managedBranches = $user->branches()->get();
            foreach ($managedBranches as $mb) {
                if (!$branches->contains('id', $mb->id)) {
                    $branches->push($mb);
                }
            }
        }

        // Jika belum ada cabang yang dipilih, jangan tampilkan data
        if (!$branch_id) {
            $evaluations = collect(); // Kosongkan agar user harus pilih cabang dulu
            return view('employee_evaluations.history', compact('evaluations', 'date', 'branches', 'branch_id'));
        }

        $query = EmployeeEvaluation::with(['user', 'user.branch', 'assessor'])
            ->whereDate('evaluation_date', $date)
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

        return view('employee_evaluations.history', compact('evaluations', 'date', 'branches', 'branch_id'));
    }
}
