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

        $query = User::with(['branch', 'division'])->where('is_active', true)
            ->where(function($q) use ($branch_id) {
                $q->where('branch_id', $branch_id)
                  ->orWhereHas('branches', function($q2) use ($branch_id) {
                      $q2->where('branches.id', $branch_id);
                  });
            });

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

        $average_score = $scores->count() > 0 ? $scores->average() : 0;
        
        // Tentukan Grade
        $grade = 'D';
        if ($average_score >= 95) $grade = 'A+';
        elseif ($average_score >= 90) $grade = 'A';
        elseif ($average_score >= 85) $grade = 'B+';
        elseif ($average_score >= 80) $grade = 'B';
        elseif ($average_score >= 70) $grade = 'C';

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
            ]
        );

        return redirect()->route('employee-evaluations.branch-employees', $user->branch_id ?? 1)->with('success', 'Rapor karyawan berhasil disimpan!');
    }
}
