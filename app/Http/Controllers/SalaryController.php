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

        $users = User::where('is_active', true)->orderBy('name')->get();

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

            $branchTimezone = $selectedUser->branch->timezone ?? 'Asia/Jakarta';

            // LOGIC CUTOFF: 26 Bulan Kemarin - 25 Bulan Ini
            // Definisikan Variable Tanggal menggunakan timezone cabang
            $monthStartDate = Carbon::createFromDate($year, $month, 1, $branchTimezone)->subMonth()->day(26)->startOfDay();
            $monthEndDate = Carbon::createFromDate($year, $month, 1, $branchTimezone)->day(25)->endOfDay();
            $today = Carbon::now($branchTimezone)->startOfDay(); // Gunakan startOfDay seperti di AttendanceHistory

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
            $telatFisik = $attendances->filter(function ($a) use ($monthStartDate, $limitDate, $branchTimezone) {
                $attDate = Carbon::parse($a->check_in_time)->timezone($branchTimezone)->startOfDay();
                $isInRange = $attDate->between($monthStartDate, $limitDate);
                $isTelat = $a->is_late_checkin || $a->status === 'late' || str_contains(strtolower($a->presence_status ?? ''), 'telat');
                return $isInRange && $isTelat;
            })->count();

            $izinTelat = LeaveRequest::where('user_id', $selectedUserId)
                ->where('type', 'telat')->where('status', 'approved')
                ->whereBetween('start_date', [$monthStartDate, $monthEndDate])->count();

            $lateCount = $telatFisik + $izinTelat;

            // ALPHA COUNT - LOGIC YANG SAMA DENGAN AttendanceHistoryController
            // Hitung dari total hari dalam bulan dikurangi attendance yang ada

            // Total hari yang sudah lewat di Range Cutoff ini
            $totalDays = 0;
            // Hitung manual days, karena diffInDays bisa tricky dengan jam
            for ($d = $monthStartDate->copy(); $d->lte($limitDate); $d->addDay()) {
                $totalDays++;
            }

            // Ambil semua approved leaves di bulan ini
            $leaves = LeaveRequest::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->where(function ($query) use ($monthStartDate, $monthEndDate) {
                    $query->whereBetween('start_date', [$monthStartDate, $monthEndDate])
                        ->orWhereBetween('end_date', [$monthStartDate, $monthEndDate])
                        ->orWhere(function ($q) use ($monthStartDate, $monthEndDate) {
                            $q->where('start_date', '<=', $monthStartDate)
                                ->where('end_date', '>=', $monthEndDate);
                        });
                })->get();

            // Hitung berapa hari yang ada attendance atau leave
            $coveredDays = 0;
            for ($date = $monthStartDate->copy(); $date->lte($limitDate); $date->addDay()) {
                $currentDateStr = $date->format('Y-m-d');

                // Cek apakah ada attendance YANG BUKAN ALPHA
                $hasAttendance = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                    $dateMatch = Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
                    $notAlpha = strtolower($a->presence_status ?? '') !== 'alpha';
                    return $dateMatch && $notAlpha;
                })->isNotEmpty();

                // Cek apakah ada leave
                $hasLeave = $leaves->filter(function ($l) use ($date) {
                    return $date->between(
                        Carbon::parse($l->start_date)->startOfDay(),
                        Carbon::parse($l->end_date ?? $l->start_date)->endOfDay()
                    );
                })->isNotEmpty();

                if ($hasAttendance || $hasLeave) {
                    $coveredDays++;
                }
            }

            // Alpha = Total hari - Hari yang ada attendance/leave
            $alphaCount = $totalDays - $coveredDays;

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
            'cutiLebih'
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
                $income = ($request->employee_basic_salary ?? 0) +
                    ($request->employee_position_allowance ?? 0) +
                    ($request->employee_owner_privilege ?? 0);
            } elseif ($request->category == 'promotor') {
                $income = ($request->employee_basic_salary ?? 0);
            } elseif ($request->category == 'freelance') {
                // Freelance: Gunakan Total Income yang dihitung JS (Rate * Hari)
                // Kita ambil dari input hidden 'freelance_total_income' atau hitung ulang di server
                // Untuk aman, kita hitung ulang di server based on input rate & days (tapi days tidak dikirim form, jadi pakai total income yang dikirim)
                // Atau lebih aman: Ambil rate dari request, hitung manual jika hari dikirim.
                // Disini kita pakai nilai yang dikirim frontend 'freelance_total_income' karena hari attendance dinamis di view
                $income = $request->freelance_total_income ?? 0;

                // Simpan Rate Harian di kolom basic_salary agar tercatat ratenya
                $data['employee_basic_salary'] = $request->freelance_daily_salary;
            }

            $income += ($request->promotor_bonus ?? 0);
            $income += ($request->dispensation_amount ?? 0);

            // Total Deduction (termasuk cuti lebih)
            $deduction = ($request->alpha_deduction ?? 0) +
                ($request->late_deduction ?? 0) +
                ($request->cuti_lebih_deduction ?? 0) + // [BARU] Potongan Cuti Lebih
                ($totalKasbonDeduction) +
                ($request->other_deduction ?? 0);

            $data['total_amount'] = $income - $deduction;

            Salary::create($data);
        });

        return redirect()->route('branch-salary.show', User::find($request->user_id)->branch_id)
            ->with('success', 'Payroll disimpan.');
    }

    // ... method lain (show, edit, update, destroy) ...
    public function show($id)
    {
        $salary = Salary::with(['user.branch', 'user.division'])->findOrFail($id);
        return view('salaries.show', compact('salary'));
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