<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use App\Models\Attendance;
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
        // 1. AMBIL PARAMETER (Agar data dinamis saat bulan/tahun diganti)
        $selectedUserId = $request->query('user_id');
        $month = $request->query('month', date('m')); // Default bulan ini
        $year = $request->query('year', date('Y'));   // Default tahun ini

        $selectedUser = null;
        $remainingDebt = 0;
        $alphaCount = 0;
        $lateCount = 0;
        $masterSalary = null;

        // List User untuk Dropdown
        $users = User::where('is_active', true)->orderBy('name')->get();

        if ($selectedUserId) {
            $selectedUser = User::with(['branch', 'division', 'employeeSalary'])->find($selectedUserId);

            // A. Ambil Master Gaji (Template)
            if ($selectedUser->employeeSalary) {
                $masterSalary = $selectedUser->employeeSalary;
            }

            // B. HITUNG SISA HUTANG (KASBON)
            $activeLoans = CashAdvance::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereRaw('total_paid < amount')
                ->get();
            
            foreach($activeLoans as $loan) {
                $remainingDebt += ($loan->amount - $loan->total_paid);
            }

            // ==========================================================
            // C. HITUNG OTOMATIS ABSENSI (ALPHA & TELAT)
            // ==========================================================
            
            // 1. Hitung TELAT (Berdasarkan Bulan & Tahun yang dipilih)
            // Logic: Cek kolom 'is_late_checkin' = 1 ATAU status string mengandung 'late'/'Telat'
            $lateCount = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->where(function($q) {
                    $q->where('is_late_checkin', true)
                      ->orWhere('status', 'late')
                      ->orWhere('presence_status', 'like', '%Telat%');
                })
                ->count();

            // 2. Hitung ALPHA
            // Logic: Cek kolom status = 'alpha' ATAU presence_status = 'Alpha'
            // Menggunakan created_at karena biasanya Alpha tidak punya check_in_time
            $alphaCount = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where(function($q) {
                    $q->where('status', 'alpha')
                      ->orWhere('presence_status', 'Alpha');
                })
                ->count();
        }

        return view('salaries.create', compact(
            'users', 
            'selectedUser', 
            'remainingDebt', 
            'alphaCount', 
            'lateCount', 
            'masterSalary',
            'month',
            'year'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'year' => 'required',
            'category' => 'required',
            'employee_basic_salary' => 'nullable|numeric|min:0',
            'kasbon_deduction' => 'nullable|numeric|min:0',
        ]);

        // Cek Double Data
        $exists = Salary::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Gaji untuk karyawan ini di periode tersebut sudah dibuat! Silakan edit data yang ada.');
        }

        DB::transaction(function() use ($request) {
            // 1. Siapkan Data Dasar
            $data = $request->except(['_token']);
            $data['created_by'] = Auth::id();

            // 2. PROSES POTONG KASBON OTOMATIS
            if ($request->kasbon_deduction > 0) {
                $deductionAmount = $request->kasbon_deduction;
                $userId = $request->user_id;

                $activeLoans = CashAdvance::where('user_id', $userId)
                    ->where('status', 'approved')
                    ->whereRaw('total_paid < amount')
                    ->orderBy('due_date', 'asc') // Bayar hutang terlama dulu
                    ->get();

                foreach ($activeLoans as $loan) {
                    if ($deductionAmount <= 0) break;

                    $sisaHutangIni = $loan->amount - $loan->total_paid;
                    $bayar = 0;

                    if ($deductionAmount >= $sisaHutangIni) {
                        $bayar = $sisaHutangIni;
                        $deductionAmount -= $sisaHutangIni;
                    } else {
                        $bayar = $deductionAmount;
                        $deductionAmount = 0;
                    }

                    // Buat Record Cicilan
                    CashAdvanceInstallment::create([
                        'cash_advance_id' => $loan->id,
                        'user_id' => $userId,
                        'amount_paid' => $bayar,
                        'received_by' => 'SYSTEM (Potong Gaji)',
                        'payment_proof' => null,
                        'status' => 'approved',
                        'note' => 'Potongan Payroll Bulan ' . $request->month . '/' . $request->year
                    ]);

                    // Update Saldo Kasbon
                    $loan->total_paid += $bayar;
                    if ($loan->total_paid >= $loan->amount) {
                        $loan->status = 'paid';
                        $loan->repayment_date = now();
                    }
                    $loan->save();
                }
            }

            // 3. HITUNG FINAL TOTAL (Server Side Verification)
            // Income
            $income = ($request->employee_basic_salary ?? 0) + 
                      ($request->employee_position_allowance ?? 0) + 
                      ($request->employee_owner_privilege ?? 0) + 
                      ($request->promotor_bonus ?? 0) + 
                      ($request->dispensation_amount ?? 0);

            // Deduction
            $deduction = ($request->alpha_deduction ?? 0) + 
                         ($request->late_deduction ?? 0) + 
                         ($request->kasbon_deduction ?? 0) + 
                         ($request->other_deduction ?? 0);

            $data['total_amount'] = $income - $deduction;

            Salary::create($data);
        });

        return redirect()->route('salaries.index')->with('success', 'Payroll berhasil disimpan.');
    }

    public function show($id)
    {
        $salary = Salary::with(['user.branch', 'user.division'])->findOrFail($id);
        return view('salaries.show', compact('salary'));
    }

    public function edit(Salary $salary)
    {
        $users = User::orderBy('name')->get();
        // Saat edit, kita tidak load otomatis Alpha/Telat baru, pakai data tersimpan saja
        return view('salaries.edit', compact('salary', 'users'));
    }

    public function update(Request $request, Salary $salary)
    {
        // Update sederhana (hanya kategori/catatan), karena hitungan uang kompleks jika diedit
        $salary->update($request->only(['category', 'notes']));
        return redirect()->route('salaries.index')->with('success', 'Data gaji diperbarui.');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();
        return redirect()->route('salaries.index')->with('success', 'Data gaji dihapus.');
    }
}