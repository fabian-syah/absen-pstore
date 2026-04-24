<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $salaries = Salary::with(['user.branch', 'user.division'])
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('salaries.index', compact('salaries', 'month', 'year'));
    }

    public function create(Request $request)
    {
        $selectedUserId = $request->query('user_id');
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        // [BARU] Range Tanggal untuk Freelance (Optional)
        // Jika tidak diisi, defaultnya adalah tanggal 1 sampai akhir bulan yang dipilih
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::createFromDate($year, $month, 1);
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $selectedUser = null;
        $activeLoans = collect([]);
        $totalRemainingDebt = 0;
        $alphaCount = 0;
        $lateCount = 0;
        $masterSalary = null;
        $freelanceAttendance = 0;

        $cutiCount = 0;
        $sakitCount = 0;
        $izinCount = 0;
        $wfhCount = 0;

        $role = Auth::user()->role;
        if (in_array($role, ['admin', 'super_admin', 'admin_gaji', 'owner'])) {
            $users = User::orderBy('is_active', 'desc')->orderBy('name')->get();
        } else {
            $users = User::where('is_active', true)->orderBy('name')->get();
        }

        if ($selectedUserId) {
            $selectedUser = User::with(['branch', 'division', 'employeeSalary'])->find($selectedUserId);

            if ($selectedUser->employeeSalary) {
                $masterSalary = $selectedUser->employeeSalary;
            }

            // Hutang Aktif
            $activeLoans = CashAdvance::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereRaw('total_paid < amount')
                ->orderBy('created_at', 'asc')->get();

            foreach ($activeLoans as $loan) {
                $totalRemainingDebt += ($loan->amount - $loan->total_paid);
            }

            // --- HITUNG ABSENSI ---

            $branchTimezone = $selectedUser->branch?->timezone ?? 'Asia/Jakarta';

            // LOGIC CUTOFF: 26 Bulan Kemarin - 25 Bulan Ini
            // Definisikan Variable Tanggal menggunakan timezone cabang
            $monthStartDate = Carbon::createFromDate($year, $month, 1, $branchTimezone)->subMonth()->day(26)->startOfDay();
            $monthEndDate = Carbon::createFromDate($year, $month, 1, $branchTimezone)->day(25)->endOfDay();
            $today = Carbon::now($branchTimezone)->startOfDay();

            // Limit Date pastikan tidak melewati hari ini
            $limitDate = ($monthEndDate->gt(Carbon::now($branchTimezone))) ? $today : $monthEndDate;

            // Ambil semua attendance dengan buffer hari agar timezone tidak terpotong
            $attendances = Attendance::where('user_id', $selectedUserId)
                ->whereBetween('check_in_time', [
                    $monthStartDate->copy()->subDays(2)->startOfDay(),
                    $monthEndDate->copy()->addDays(2)->endOfDay()
                ])
                ->get();

            // Absensi Regular (Bulanan) untuk Employee/Promotor
            $lateDates = [];
            $lateCount = $attendances->filter(function ($a) use ($monthStartDate, $limitDate, $branchTimezone, &$lateDates) {
                $attFullDate = Carbon::parse($a->check_in_time)->timezone($branchTimezone);
                $attDate = $attFullDate->copy()->startOfDay();
                $isInRange = $attDate->between($monthStartDate, $limitDate);
                $isTelat = $a->is_late_checkin || $a->status === 'late' || str_contains(strtolower($a->presence_status ?? ''), 'telat');
                
                if ($isInRange && $isTelat) {
                    $lateDates[] = $attFullDate->format('d/m');
                    return true;
                }
                return false;
            })->count();

            // ALPHA COUNT - LOGIC TRANSLASI LANGSUNG DARI AttendanceHistoryController
            $alphaCount = 0;

            // Sesuaikan query LeaveRequest menggunakan format 'Y-m-d' sesuai AttendanceHistoryController
            $leaves = LeaveRequest::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->where(function ($query) use ($monthStartDate, $monthEndDate) {
                    $s = $monthStartDate->format('Y-m-d');
                    $e = $monthEndDate->format('Y-m-d');
                    // Overlap condition: leave starts on or before the end of the month AND ends on or after the start of the month
                    $query->where('start_date', '<=', $e)
                        ->where(function ($q) use ($s) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $s);
                    });
                })->get();

            // Buat period (PENTING: Gunakan startOfDay agar tidak terpotong jam)
            $period = \Carbon\CarbonPeriod::create($monthStartDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

            $alphaDates = [];
            foreach ($period as $date) {
                $currentDateStr = $date->format('Y-m-d');

                // 1. Cari Attendance yang Scan Masuk-nya di tanggal ini (Prioritaskan 'Masuk')
                $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                    if ($a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha') return false;
                    return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') === $currentDateStr;
                })->sortBy(fn($a) => strtolower($a->presence_status) === 'masuk' ? 0 : 1)->first();

                // 2. Cari leave
                $leave = $leaves->filter(function ($l) use ($date) {
                    return $date->between(
                        Carbon::parse($l->start_date)->startOfDay(),
                        Carbon::parse($l->end_date ?? $l->start_date)->endOfDay()
                    );
                });

                // Syarat Alpha Hard Reset 00:00: Tidak ada Masuk baru & Tidak ada Izin
                if (!$att && $leave->isEmpty()) {
                    $alphaCount++;
                    $alphaDates[] = $date->format('d/m');
                } else if ($att) {
                    // Jika ADA record tapi statusnya khusus sistem Alpha
                    $status = strtolower($att->presence_status ?? '');
                    if ($status === 'alpha') {
                        $alphaCount++;
                        $alphaDates[] = $date->format('d/m');
                    }
                }
            }

            // [BARU] FREELANCE ATTENDANCE BERDASARKAN RANGE TANGGAL
            // Hanya menghitung kehadiran dalam rentang Start Date - End Date
            $freelanceAttendance = Attendance::where('user_id', $selectedUserId)
                ->whereDate('check_in_time', '>=', $startDate)
                ->whereDate('check_in_time', '<=', $endDate)
                ->where(function ($q) {
                    $q->whereIn('presence_status', ['Masuk', 'WFH', 'Telat', 'Izin Telat', 'Dinas Luar'])
                        ->orWhereIn('status', ['present', 'late', 'wfh']);
                })->count();

            // Info Cuti (Bulanan) - Ikut Cutoff
            $approvedLeaves = LeaveRequest::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereBetween('start_date', [$monthStartDate, $monthEndDate])->get();

            $cutiCount = $approvedLeaves->where('type', 'cuti')->count();
            $sakitCount = $approvedLeaves->where('type', 'sakit')->count();
            $izinCount = $approvedLeaves->where('type', 'izin')->count();
            $wfhCount = $approvedLeaves->where('type', 'wfh')->count();
        }

        // [BARU] Hitung Cuti Lebih (kelebihan dari jatah tahunan)
        // Dihitung DINAMIS dari approved cuti di TAHUN INI
        $cutiLebih = 0;
        if ($selectedUser) {
            $yearlyLimit = $selectedUser->yearly_leave_limit ?? 12;
            $currentYear = now()->year;

            // Hitung total hari cuti yang sudah disetujui TAHUN INI
            $totalApprovedCutiDays = LeaveRequest::where('user_id', $selectedUserId)
                ->where('type', 'cuti')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum(function ($req) {
                    $start = Carbon::parse($req->start_date);
                    $end = $req->end_date ? Carbon::parse($req->end_date) : $start;
                    return $start->diffInDays($end) + 1;
                });

            if ($totalApprovedCutiDays > $yearlyLimit) {
                $cutiLebih = $totalApprovedCutiDays - $yearlyLimit;
            }
        }

        return view('salaries.create', compact(
            'users',
            'selectedUser',
            'activeLoans',
            'totalRemainingDebt',
            'alphaCount',
            'lateCount',
            'masterSalary',
            'month',
            'year',
            'startDate',
            'endDate',
            'freelanceAttendance',
            'cutiCount',
            'sakitCount',
            'izinCount',
            'wfhCount',
            'cutiLebih',
            'alphaDates',
            'lateDates'
        ));
    }

    public function store(Request $request)
    {
        // 1. Clean Rupiah
        $inputsToClean = [
            'employee_basic_salary',
            'employee_position_allowance',
            'employee_owner_privilege',
            'promotor_bonus',
            'dispensation_amount',
            'alpha_deduction',
            'late_deduction',
            'cuti_lebih_deduction', // [BARU] Potongan cuti lebih
            'other_deduction',
            'freelance_daily_salary',
            'freelance_total_income' // [NEW] Total income freelance
        ];

        foreach ($inputsToClean as $field) {
            if ($request->has($field)) {
                $cleanValue = str_replace('.', '', $request->input($field));
                $request->merge([$field => $cleanValue]);
            }
        }

        // 2. Validate
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'year' => 'required',
            'category' => 'required',
            'payment_method' => 'required|in:cash,transfer',
            'send_type' => 'required|in:now,later',
            'scheduled_date' => 'required_if:send_type,later',
        ]);

        // Cek Duplikasi (Kecuali Freelance boleh berkali-kali karena bisa mingguan)
        if ($request->category != 'freelance') {
            $exists = Salary::where('user_id', $request->user_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();
            if ($exists)
                return back()->with('error', 'Gaji bulanan karyawan ini sudah dibuat!');
        }

        DB::transaction(function () use ($request) {
            $data = $request->except(['_token', 'send_type', 'scheduled_date', 'selected_loans', 'start_date', 'end_date', 'freelance_total_income']);
            $data['created_by'] = Auth::id();

            if ($request->send_type == 'now') {
                $data['published_at'] = now();
                $data['status'] = 'paid';
            } else {
                $data['published_at'] = $request->scheduled_date;
                $data['status'] = 'pending';
            }

            // Simpan info range tanggal di notes jika freelance
            if ($request->category == 'freelance') {
                $rangeInfo = "Periode Kerja: " . $request->start_date . " s/d " . $request->end_date;
                $data['notes'] = $data['notes'] ? $data['notes'] . "\n" . $rangeInfo : $rangeInfo;
            }

            // [BARU] Simpan Tanggal Alpha & Terlambat di Notes agar muncul di Slip
            $attendanceNotes = "";
            if ($request->filled('alpha_dates_str')) {
                $attendanceNotes .= "\nDetail Alpha: " . $request->alpha_dates_str;
            }
            if ($request->filled('late_dates_str')) {
                $attendanceNotes .= "\nDetail Terlambat: " . $request->late_dates_str;
            }
            if ($attendanceNotes) {
                $data['notes'] = ($data['notes'] ?? '') . $attendanceNotes;
            }

            // Logic Potong Kasbon
            $totalKasbonDeduction = 0;
            if ($request->has('selected_loans') && is_array($request->selected_loans)) {
                foreach ($request->selected_loans as $loanId => $amountStr) {
                    $payAmount = (float) str_replace('.', '', $amountStr);
                    if ($payAmount > 0) {
                        $loan = CashAdvance::find($loanId);
                        $sisa = $loan->amount - $loan->total_paid;
                        if ($payAmount > $sisa)
                            $payAmount = $sisa;

                        CashAdvanceInstallment::create([
                            'cash_advance_id' => $loan->id,
                            'user_id' => $request->user_id,
                            'amount_paid' => $payAmount,
                            'received_by' => 'SYSTEM (PAYROLL)',
                            'status' => 'approved',
                            'note' => 'Potongan Gaji ' . $request->month . '/' . $request->year
                        ]);

                        $loan->total_paid += $payAmount;
                        if ($loan->total_paid >= $loan->amount) {
                            $loan->status = 'paid';
                            $loan->repayment_date = now();
                        }
                        $loan->save();
                        $totalKasbonDeduction += $payAmount;
                    }
                }
            }
            $data['kasbon_deduction'] = $totalKasbonDeduction;

            // Hitung Income
            $income = 0;
            if ($request->category == 'employee') {
                $income = (float)($request->employee_basic_salary ?? 0) +
                    (float)($request->employee_position_allowance ?? 0) +
                    (float)($request->employee_owner_privilege ?? 0);
            } elseif ($request->category == 'promotor') {
                $income = (float)($request->employee_basic_salary ?? 0);
            } elseif ($request->category == 'freelance') {
                // Freelance: Gunakan Total Income yang dihitung JS (Rate * Hari)
                $income = (float)($request->freelance_total_income ?? 0);

                // Simpan Rate Harian di kolom basic_salary agar tercatat ratenya
                $data['employee_basic_salary'] = (float)($request->freelance_daily_salary ?? 0);
            }

            $income += (float)($request->promotor_bonus ?? 0);
            $income += (float)($request->dispensation_amount ?? 0);

            // Total Deduction (termasuk cuti lebih)
            $deduction = (float)($request->alpha_deduction ?? 0) +
                (float)($request->late_deduction ?? 0) +
                (float)($request->cuti_lebih_deduction ?? 0) + // [BARU] Potongan Cuti Lebih
                ((float)$totalKasbonDeduction) +
                (float)($request->other_deduction ?? 0);

            $data['total_amount'] = $income - $deduction;

            Salary::create($data);
        });

        return redirect()->route('branch-salary.show', User::find($request->user_id)->branch_id)
            ->with('success', 'Payroll disimpan.');
    }

    // ... method lain (show, edit, update, destroy) ...
    public function show($id)
    {
        $salary = Salary::with(['user.branch', 'user.division', 'user.employeeSalary'])->findOrFail($id);
        $details = $salary->getAttendanceDetails();

        return view('salaries.show', array_merge(compact('salary'), $details));
    }
    public function edit(Salary $salary)
    {
        $users = User::orderBy('name')->get();
        return view('salaries.edit', compact('salary', 'users'));
    }
    public function update(Request $request, Salary $salary)
    {
        $salary->update($request->only(['notes']));
        return redirect()->route('salaries.index')->with('success', 'Data updated.');
    }
    public function destroy(Salary $salary)
    {
        $salary->delete();
        return back()->with('success', 'Deleted.');
    }

    public function togglePaymentMethod(Salary $salary)
    {
        $newMethod = ($salary->payment_method == 'transfer') ? 'cash' : 'transfer';
        $salary->update(['payment_method' => $newMethod]);

        return back()->with('success', 'Metode pembayaran diperbarui menjadi: ' . ucfirst($newMethod));
    }
}