<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\JobTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Exports\BranchAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && in_array(Auth::user()->role, ['admin', 'audit', 'leader'])) {
                return $next($request);
            }
            return abort(403, 'Hanya Admin, Audit, atau Leader yang boleh mengakses halaman ini.');
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Branch::query();

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('id', $user->branch_id);
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) {
                $allowedBranchIds[] = $user->branch_id;
            }
            $allowedBranchIds = array_unique($allowedBranchIds);
            $query->whereIn('id', $allowedBranchIds);
        }

        // Hide special administrative branch from general listing
        $query->where('name', '!=', 'Cabang User Non Karyawan');

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        $branches = $query->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('branch.branch_index', compact('branches'));
    }

    public function show(Branch $branch)
    {
        $user = Auth::user();

        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id)
                abort(403, 'Akses Ditolak.');
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id)
                $allowedBranchIds[] = $user->branch_id;

            if (!in_array($branch->id, $allowedBranchIds))
                abort(403, 'Akses Ditolak. Cabang ini bukan wilayah Anda.');
        }

        // Mengambil data karyawan (termasuk last_login_at secara otomatis dari model User)
        $employees = User::with([
            'division',
            'attendances' => function ($q) {
                $q->whereDate('check_in_time', now());
            }
        ])
            ->where('branch_id', $branch->id)
            ->whereNotIn('role', ['admin', 'super_admin', 'admin_gaji'])
            ->latest()
            ->paginate(10);

        $totalEmployees = User::where('branch_id', $branch->id)->count();

        $assignedAudits = User::where('role', 'audit')
            ->where('is_active', true)
            ->whereHas('branches', function ($q) use ($branch) {
                $q->where('branches.id', $branch->id);
            })
            ->get();

        // --- TAMBAHAN: DATA TARGET & PENCAPAIAN CABANG (TIM) ---

        // 1. Target Tim Aktif (On Going)
        $branchTargets = JobTarget::where('branch_id', $branch->id)
            ->where('type', 'team_target')
            ->where('status', '!=', 'completed')
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Pencapaian Tim & History Target Selesai
        $branchAchievements = JobTarget::where('branch_id', $branch->id)
            ->where(function ($q) {
                $q->where('type', 'team_achievement')
                    ->orWhere(function ($subQ) {
                        $subQ->where('type', 'team_target')
                            ->where('status', 'completed');
                    });
            })
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('branch.branch_show', compact('branch', 'employees', 'totalEmployees', 'assignedAudits', 'branchTargets', 'branchAchievements'));
    }

    public function create()
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403, 'Anda tidak memiliki akses untuk menambah cabang.');
        }
        return view('branch.branch_create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null)
            abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura', // Validasi Timezone
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader']))
            abort(403, 'Anda tidak memiliki akses edit.');

        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id)
                abort(403);
        }

        return view('branch.branch_edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader']))
            abort(403);
        if ($user->role == 'admin' && $user->branch_id != null && $branch->id != $user->branch_id)
            abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura', // Validasi Timezone
            'is_active' => 'required|boolean',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null)
            abort(403);

        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada user yang terhubung.');
        }
    }
    public function exportBranchExcel(Request $request, Branch $branch)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            abort(403);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $branchNameSlug = Str::slug($branch->name);
        $fileName = "Laporan_Absensi_Cabang_{$branchNameSlug}_{$month}-{$year}.xlsx";

        return Excel::download(new BranchAttendanceExport($branch->id, $month, $year), $fileName);
    }

    public function exportBranchPdf(Request $request, Branch $branch)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            abort(403);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $dateObj = Carbon::createFromDate($year, $month, 1);
        $monthName = $dateObj->translatedFormat('F Y');
        $startDate = $dateObj->copy()->startOfMonth();
        $endDate = $dateObj->copy()->endOfMonth();

        $employees = User::where('branch_id', $branch->id)
            ->where('role', '!=', 'admin')
            ->orderBy('name', 'asc')
            ->get();

        $data = [];
        foreach ($employees as $employee) {
            $attendances = \App\Models\Attendance::where('user_id', $employee->id)
                ->whereBetween('check_in_time', [$startDate, $endDate])
                ->get();

            $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'libur' => 0, 'telat' => 0, 'total_jam' => 0];
            foreach ($attendances as $atten) {
                // Normalize status to lowercase
                $status = strtolower(trim($atten->presence_status));

                // Logic MATCHING UI: Hadir includes WFH, Dinas Luar, Lembur
                $isHadir = in_array($status, [
                    'hadir',
                    'tepat waktu',
                    'masuk',
                    'wfh',
                    'work from home',
                    'dinas luar',
                    'kunjungan rutin',
                    'lembur'
                ]);

                if ($isHadir) {
                    $summary['hadir']++;
                } elseif ($status == 'sakit') {
                    $summary['sakit']++;
                } elseif (in_array($status, ['izin', 'cuti'])) {
                    $summary['izin']++;
                } elseif ($status == 'alpha') {
                    $summary['alfa']++;
                } elseif ($status == 'libur') {
                    $summary['libur']++;
                }

                if ($atten->is_late_checkin) {
                    $summary['telat']++;
                }

                if ($atten->check_in_time && $atten->check_out_time) {
                    $summary['total_jam'] += $atten->check_in_time->diffInHours($atten->check_out_time);
                }
            }
            $data[] = ['user' => $employee, 'summary' => $summary];
        }

        $pdf = Pdf::loadView('branch.export_pdf', [
            'branch' => $branch,
            'monthName' => $monthName,
            'data' => $data,
            'generatedBy' => $user
        ]);

        $sanitizedBranchName = Str::slug($branch->name);
        return $pdf->download("Laporan_Absensi_{$sanitizedBranchName}_{$monthName}.pdf");
    }
}