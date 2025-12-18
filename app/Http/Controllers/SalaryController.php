<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest; // Pastikan Model ini di-import
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

        $selectedUser = null;
        $remainingDebt = 0;
        $alphaCount = 0;
        $lateCount = 0;
        $masterSalary = null;
        $freelanceAttendance = 0;

        // Variabel Info Tambahan (Default 0)
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

            // Hitung Hutang
            $activeLoans = CashAdvance::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereRaw('total_paid < amount')
                ->get();
            foreach ($activeLoans as $loan) {
                $remainingDebt += ($loan->amount - $loan->total_paid);
            }

            // --- HITUNG ABSENSI (ALPHA & TELAT) ---

            // 1. Telat
            $telatFisik = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->where(function ($q) {
                    $q->where('is_late_checkin', true)
                        ->orWhere('status', 'late')
                        ->orWhere('presence_status', 'like', '%Telat%');
                })->count();

            $izinTelat = LeaveRequest::where('user_id', $selectedUserId)
                ->where('type', 'telat')
                ->where('status', 'approved')
                ->whereMonth('start_date', $month)
                ->whereYear('start_date', $year)
                ->count();

            $lateCount = $telatFisik + $izinTelat;

            // 2. Alpha
            $alphaCount = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where(function ($q) {
                    $q->where('status', 'alpha')
                        ->orWhere('presence_status', 'Alpha');
                })->count();

            // 3. Freelance Attendance
            $freelanceAttendance = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->where(function ($q) {
                    $q->whereIn('presence_status', ['Masuk', 'WFH', 'Telat', 'Izin Telat', 'Dinas Luar'])
                        ->orWhereIn('status', ['present', 'late', 'wfh']);
                })->count();

            // ==========================================
            // 4. HITUNG INFO CUTI/IZIN/SAKIT/WFH (INFO ONLY)
            // ==========================================
            $approvedLeaves = LeaveRequest::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereMonth('start_date', $month)
                ->whereYear('start_date', $year)
                ->get();

            $cutiCount = $approvedLeaves->where('type', 'cuti')->count();
            $sakitCount = $approvedLeaves->where('type', 'sakit')->count();
            $izinCount = $approvedLeaves->where('type', 'izin')->count(); // Izin biasa (bukan telat)
            $wfhCount = $approvedLeaves->where('type', 'wfh')->count();
        }

        return view('salaries.create', compact(
            'users',
            'selectedUser',
            'remainingDebt',
            'alphaCount',
            'lateCount',
            'masterSalary',
            'month',
            'year',
            'freelanceAttendance',
            'cutiCount',
            'sakitCount',
            'izinCount',
            'wfhCount' // Kirim ke View
        ));
    }
    public function store(Request $request)
    {
        // 1. Clean Rupiah Inputs
        $inputsToClean = [
            'employee_basic_salary',
            'employee_position_allowance',
            'employee_owner_privilege',
            'promotor_bonus',
            'dispensation_amount',
            'alpha_deduction',
            'late_deduction',
            'kasbon_deduction',
            'other_deduction',
            'freelance_daily_salary'
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

        if ($request->category != 'freelance') {
            $exists = Salary::where('user_id', $request->user_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();
            if ($exists) return back()->with('error', 'Gaji bulanan karyawan ini sudah dibuat!');
        }

        DB::transaction(function () use ($request) {
            $data = $request->except(['_token', 'send_type', 'scheduled_date']);
            $data['created_by'] = Auth::id();

            // Logic Jadwal
            if ($request->send_type == 'now') {
                $data['published_at'] = now();
                $data['status'] = 'paid';
            } else {
                $data['published_at'] = $request->scheduled_date;
                $data['status'] = 'pending';
            }

            // Logic Potong Kasbon
            if ($request->kasbon_deduction > 0) {
                $deductionAmount = $request->kasbon_deduction;

                // [FIXED] Changed orderBy 'due_date' to 'created_at'
                $activeLoans = CashAdvance::where('user_id', $request->user_id)
                    ->where('status', 'approved')
                    ->whereRaw('total_paid < amount')
                    ->orderBy('created_at', 'asc') // <--- FIXED HERE
                    ->get();

                foreach ($activeLoans as $loan) {
                    if ($deductionAmount <= 0) break;
                    $sisa = $loan->amount - $loan->total_paid;
                    $bayar = ($deductionAmount >= $sisa) ? $sisa : $deductionAmount;
                    $deductionAmount -= $bayar;

                    CashAdvanceInstallment::create([
                        'cash_advance_id' => $loan->id,
                        'user_id' => $request->user_id,
                        'amount_paid' => $bayar,
                        'received_by' => 'SYSTEM',
                        'status' => 'approved',
                        'note' => 'Potongan Gaji ' . $request->month . '/' . $request->year
                    ]);

                    $loan->total_paid += $bayar;
                    if ($loan->total_paid >= $loan->amount) {
                        $loan->status = 'paid';
                        $loan->repayment_date = now();
                    }
                    $loan->save();
                }
            }

            // Hitung Income
            $income = 0;
            if ($request->category == 'employee') {
                $income = ($request->employee_basic_salary ?? 0) +
                    ($request->employee_position_allowance ?? 0) +
                    ($request->employee_owner_privilege ?? 0);
            } elseif ($request->category == 'promotor') {
                $income = ($request->employee_basic_salary ?? 0);
            } elseif ($request->category == 'freelance') {
                $income = ($request->freelance_daily_salary ?? 0);
            }

            $income += ($request->promotor_bonus ?? 0);
            $income += ($request->dispensation_amount ?? 0);

            // Total Deduction
            $deduction = ($request->alpha_deduction ?? 0) +
                ($request->late_deduction ?? 0) +
                ($request->kasbon_deduction ?? 0) +
                ($request->other_deduction ?? 0);

            $data['total_amount'] = $income - $deduction;
            Salary::create($data);
        });

        return redirect()->route('branch-salary.show', User::find($request->user_id)->branch_id)
            ->with('success', 'Payroll disimpan.');
    }
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
    public function checkAttendance(Request $request) {}
}
