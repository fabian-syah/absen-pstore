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
            $branches = Branch::withCount(['users' => function($q) {
                $q->where('is_active', true);
            }])->get();
        } else {
            $branches = collect();
            
            // Branch utama
            if ($user->branch_id) {
                $mainBranch = Branch::withCount(['users' => function($q) {
                    $q->where('is_active', true);
                }])->find($user->branch_id);
                if ($mainBranch) $branches->push($mainBranch);
            }
            
            // Branch kelolaan
            $managedBranches = $user->branches()->withCount(['users' => function($q) {
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
        
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        // Cek apakah sudah ada evaluasi di bulan dan tahun tersebut
        $evaluation = EmployeeEvaluation::where('user_id', $user_id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        return view('employee_evaluations.form', compact('employee', 'evaluation', 'month', 'year'));
    }

    /**
     * Simpan atau update rapor.
     */
    public function store(Request $request, $user_id)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
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

        EmployeeEvaluation::updateOrCreate(
            [
                'user_id' => $user_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
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
        
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $evaluation = EmployeeEvaluation::with('assessor')
            ->where('user_id', $user_id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$evaluation) {
            return back()->with('error', 'Data evaluasi tidak ditemukan untuk bulan tersebut.');
        }

        $pdf = app('dompdf.wrapper')->loadView('pdf.employee-evaluation', compact('user', 'evaluation', 'month', 'year'));
        $pdf->setPaper('A4', 'portrait');
        
        $monthName = \Carbon\Carbon::create()->month($month)->translatedFormat('F');
        $fileName = 'Rapor_Karyawan_' . str_replace(' ', '_', $user->name) . '_' . $monthName . '_' . $year . '.pdf';
        
        return $pdf->download($fileName);
    }

    public function exportBranchPdf(Request $request, $branch_id)
    {
        $currentUser = Auth::user();
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $branch = Branch::findOrFail($branch_id);
        
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $users = User::where('branch_id', $branch_id)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN role = 'leader' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada karyawan di cabang ini.');
        }

        $evaluations = EmployeeEvaluation::whereIn('user_id', $users->pluck('id'))
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('user_id');

        // Calculate average for branch chart
        $avgScores = [
            'kecerdasan' => 0, 'amanah' => 0, 'sosial_media' => 0,
            'kepemimpinan' => 0, 'data_ketelitian' => 0, 'komunikasi' => 0,
            'kedisiplinan' => 0
        ];
        
        $count = 0;
        foreach ($users as $u) {
            $eval = $evaluations->get($u->id);
            if ($eval) {
                $avgScores['kecerdasan'] += (int) $eval->kecerdasan_score;
                $avgScores['amanah'] += (int) $eval->amanah_score;
                $avgScores['sosial_media'] += (int) $eval->sosial_media_score;
                $avgScores['kepemimpinan'] += (int) $eval->kepemimpinan_score;
                $avgScores['data_ketelitian'] += (int) $eval->data_ketelitian_score;
                $avgScores['komunikasi'] += (int) $eval->komunikasi_score;
                $avgScores['kedisiplinan'] += (int) $eval->kedisiplinan_score;
                $count++;
            }
        }

        if ($count > 0) {
            foreach ($avgScores as $k => $v) {
                $avgScores[$k] = round($v / $count, 1);
            }
        }

        // Generate QuickChart
        $chartData = [
            'type' => 'radar',
            'data' => [
                'labels' => ['Kecerdasan', 'Amanah', 'Sosial media', 'Kepemimpinan', 'Data & ketelitian', 'Komunikasi', 'Kedisiplinan'],
                'datasets' => [
                    [
                        'label' => 'Rata-rata Cabang',
                        'data' => array_values($avgScores),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'pointBackgroundColor' => 'rgba(54, 162, 235, 1)',
                        'pointBorderColor' => '#fff',
                    ]
                ]
            ],
            'options' => [
                'scale' => [
                    'ticks' => [
                        'beginAtZero' => true,
                        'max' => 100,
                        'min' => 0,
                        'stepSize' => 20
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
            // Ignore if chart fails to load
        }

        $pdf = app('dompdf.wrapper')->loadView('pdf.branch-evaluation', compact('branch', 'users', 'evaluations', 'month', 'year', 'chartImage'));
        $pdf->setPaper('A4', 'portrait');
        
        $monthName = \Carbon\Carbon::create()->month($month)->translatedFormat('F');
        $fileName = 'Rapor_Cabang_' . str_replace(' ', '_', $branch->name) . '_' . $monthName . '_' . $year . '.pdf';
        
        return $pdf->download($fileName);
    }
}
