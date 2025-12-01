<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Branch;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // $myId = $user->id; // DELETE: Tidak perlu filter ID sendiri lagi

        // 1. KUMPULKAN SEMUA ID CABANG MILIK USER LOGIN
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. QUERY USER (TIM)
        // UPDATE: Hapus ->where('users.id', '!=', $myId) agar akun sendiri muncul
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            $query->where('users.id', 0);
        } else {
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
        }

        // Ambil Data Tim
        $myTeam = $query->with([
            // Ambil absensi hari ini
            'attendances' => function ($q) {
                $q->whereDate('check_in_time', today());
            },
            // UPDATE: Ambil Izin/Sakit/Cuti yang Approved hari ini
            'leaveRequests' => function ($q) {
                $q->where('status', 'approved')
                  ->whereDate('start_date', '<=', today())
                  ->whereDate('end_date', '>=', today());
            },
            'activeLateStatus',
            'divisions',
            'branch'
        ])
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->orderBy('branches.name', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('users.*')
            ->get();

        // 3. AMBIL DATA DETAIL CABANG
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches'));
    }

    public function showBranch($id)
    {
        $user = Auth::user();

        // Validasi Akses (Pastikan Audit/Admin berhak lihat cabang ini)
        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if (!in_array($id, $allowedBranches)) abort(403);
        } elseif ($user->role == 'admin' && $user->branch_id) {
            if ($user->branch_id != $id) abort(403);
        }

        $branch = Branch::findOrFail($id);

        // Ambil karyawan di cabang ini
        // Eager load: attendance hari ini, dan leave request hari ini
        $employees = User::where('branch_id', $id)
            ->where('role', '!=', 'admin') // Sembunyikan admin
            ->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) {
                $q->whereDate('check_in_time', today());
            }])
            ->get();

        // LOGIKA BARU: Attach status Izin/Sakit hari ini ke setiap karyawan
        // Kita tidak pakai Eager Loading 'leaveRequests' karena butuh filter tanggal yang kompleks
        foreach ($employees as $emp) {
            $todayLeave = \App\Models\LeaveRequest::where('user_id', $emp->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])
                            ->whereDate('start_date', '<=', today())
                            ->whereDate('end_date', '>=', today());
                    })->orWhere(function ($sub) {
                        $sub->where('type', 'telat')
                            ->whereDate('start_date', today());
                    });
                })
                ->first();

            // Simpan ke atribut sementara di object user
            $emp->today_leave = $todayLeave;
        }

        return view('user_biasa.branch_detail', compact('branch', 'employees'));
    }

    public function myBranches()
    {
        $user = Auth::user();

        // Pastikan hanya audit yang bisa akses
        if ($user->role !== 'audit') {
            abort(403, 'Unauthorized action.');
        }

        $myId = $user->id;

        // 1. KUMPULKAN ID CABANG MILIK USER
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. AMBIL DATA CABANG YANG DIKONTROL
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.my_branches', compact('controlledBranches'));
    }

    /**
     * Menampilkan riwayat absensi karyawan
     */
    public function showEmployeeHistory(Request $request, $branchId, $employeeId)
    {
        $user = Auth::user();

        // Validasi Akses Cabang (Security Check)
        // Pastikan Audit/Admin boleh akses cabang ini
        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if (!in_array($branchId, $allowedBranches)) {
                abort(403, 'Anda tidak memiliki akses ke cabang ini.');
            }
        } elseif ($user->role == 'admin' && $user->branch_id) {
            if ($user->branch_id != $branchId) {
                abort(403);
            }
        }

        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);

        // Filter Tanggal
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // 1. AMBIL DATA ABSENSI REAL
        $attendances = Attendance::where('user_id', $employeeId)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 2. AMBIL DATA IZIN (Approved)
        $leaves = LeaveRequest::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                    ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                        $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                    });
            })
            ->get();

        // 3. MERGE DATA (Logika sama dengan AttendanceHistoryController)
        $historyCollection = $attendances;

        foreach ($leaves as $leave) {
            $startDate = \Carbon\Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date) : $startDate;
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    $alreadyAttendance = $attendances->filter(function ($att) use ($date) {
                        return $att->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    if (!$alreadyAttendance) {
                        $fakeAtt = new Attendance();
                        $fakeAtt->id = 'leave_' . $leave->id . '_' . $date->timestamp;
                        $fakeAtt->user_id = $employeeId;
                        $fakeAtt->check_in_time = $date->copy()->setTime(8, 0, 0);
                        $fakeAtt->check_out_time = null;

                        $typeLabel = ucfirst($leave->type);
                        if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                        if ($leave->type == 'wfh') $typeLabel = 'WFH';

                        $fakeAtt->presence_status = $typeLabel;
                        $fakeAtt->status = 'verified';
                        $fakeAtt->attendance_type = 'leave';
                        $fakeAtt->is_late_checkin = false;
                        $fakeAtt->is_early_checkout = false;
                        $fakeAtt->photo_path = null;
                        $fakeAtt->audit_note = "Pengajuan: " . $leave->reason;

                        $historyCollection->push($fakeAtt);
                    }
                }
            }
        }

        $history = $historyCollection->sortByDesc('check_in_time');

        // 4. HITUNG SUMMARY (Wajib lengkap agar view tidak error)
        $summary = [
            'total' => $history->count(),
            'hadir' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                $isExplicitPresent = in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
                $isImplicitPresent = empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']);
                return $isExplicitPresent || $isImplicitPresent;
            })->count(),

            // KEY YANG HILANG SEBELUMNYA:
            'sakit' => $history->filter(function ($i) {
                return strtolower($i->presence_status ?? '') === 'sakit';
            })->count(),

            'izin' => $history->filter(function ($i) {
                return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']);
            })->count(),

            'alpha' => $history->filter(function ($i) {
                return strtolower($i->presence_status ?? '') === 'alpha';
            })->count(),

            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return view('attendance.history', compact(
            'history',
            'summary',
            'selectedMonth',
            'selectedYear',
            'employee' // Kirim data employee agar view tahu ini mode admin/audit
        ));
    }
}