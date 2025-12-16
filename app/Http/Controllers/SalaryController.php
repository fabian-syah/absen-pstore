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
    // Menampilkan halaman index (List Gaji)
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

    // Menampilkan Form Buat Gaji
    public function create(Request $request)
    {
        // Ambil User ID dari URL (dikirim dari menu Gaji Cabang)
        $selectedUserId = $request->query('user_id');
        $selectedUser = null;
        $remainingDebt = 0; // Sisa Hutang
        $alphaCount = 0;
        $lateCount = 0;

        if ($selectedUserId) {
            $selectedUser = User::with(['branch', 'division'])->find($selectedUserId);

            // 1. HITUNG SISA HUTANG (KASBON)
            // Cari kasbon 'approved' yang belum lunas (total_paid < amount)
            $activeLoans = CashAdvance::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereRaw('total_paid < amount')
                ->get();
            
            foreach($activeLoans as $loan) {
                $remainingDebt += ($loan->amount - $loan->total_paid);
            }

            // 2. HITUNG ABSENSI BULAN INI
            $month = date('m');
            $year = date('Y');
            
            // Hitung Telat (Status 'late' atau presence_status 'Telat')
            $lateCount = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->where(function($q) {
                    $q->where('status', 'late')->orWhere('presence_status', 'Telat');
                })->count();

            // Hitung Alpha (Disini saya set 0 agar Admin input manual berdasarkan rekap, 
            // tapi jika mau otomatis query 'status' = 'alpha' bisa ditambahkan disini)
            $alphaCount = 0; 
        }

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('salaries.create', compact('users', 'selectedUser', 'remainingDebt', 'alphaCount', 'lateCount'));
    }

    // Menyimpan Gaji
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'year' => 'required',
            'category' => 'required',
            // Pastikan nominal tidak negatif
            'employee_basic_salary' => 'nullable|numeric|min:0',
            'kasbon_deduction' => 'nullable|numeric|min:0',
        ]);

        // Cek apakah gaji bulan ini sudah pernah dibuat
        $exists = Salary::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Gaji untuk karyawan ini di periode tersebut sudah dibuat!');
        }

        DB::transaction(function() use ($request) {
            // 1. Siapkan Data
            $data = $request->except(['_token']);
            $data['created_by'] = Auth::id();

            // 2. PROSES POTONG KASBON OTOMATIS
            // Jika admin mengisi input 'kasbon_deduction' > 0
            if ($request->kasbon_deduction > 0) {
                $deductionAmount = $request->kasbon_deduction;
                $userId = $request->user_id;

                // Ambil semua hutang aktif user, urutkan dari yang jatuh tempo duluan
                $activeLoans = CashAdvance::where('user_id', $userId)
                    ->where('status', 'approved')
                    ->whereRaw('total_paid < amount')
                    ->orderBy('due_date', 'asc')
                    ->get();

                foreach ($activeLoans as $loan) {
                    if ($deductionAmount <= 0) break;

                    $sisaHutangIni = $loan->amount - $loan->total_paid;
                    $bayar = 0;

                    // Logika alokasi pembayaran
                    if ($deductionAmount >= $sisaHutangIni) {
                        $bayar = $sisaHutangIni;
                        $deductionAmount -= $sisaHutangIni;
                    } else {
                        $bayar = $deductionAmount;
                        $deductionAmount = 0;
                    }

                    // Buat Record Cicilan (CashAdvanceInstallment)
                    CashAdvanceInstallment::create([
                        'cash_advance_id' => $loan->id,
                        'user_id' => $userId,
                        'amount_paid' => $bayar,
                        'received_by' => 'SYSTEM (Potong Gaji)', // Penanda otomatis
                        'payment_proof' => null, 
                        'status' => 'approved',
                        'note' => 'Potongan Payroll Bulan ' . $request->month . '/' . $request->year
                    ]);

                    // Update Status Hutang Induk
                    $loan->total_paid += $bayar;
                    if ($loan->total_paid >= $loan->amount) {
                        $loan->status = 'paid';
                        $loan->repayment_date = now();
                    }
                    $loan->save();
                }
            }

            // 3. HITUNG ULANG TOTAL (Server Side Verification)
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

            // Take Home Pay
            $data['total_amount'] = $income - $deduction;

            Salary::create($data);
        });

        return redirect()->route('salaries.index')->with('success', 'Payroll berhasil disimpan. Hutang (jika ada) otomatis terpotong.');
    }

    // Menampilkan Slip Gaji
    public function show($id)
    {
        $salary = Salary::with(['user.branch', 'user.division'])->findOrFail($id);
        return view('salaries.show', compact('salary'));
    }

    // Edit, Update, Destroy (Standard)
    public function edit(Salary $salary)
    {
        $users = User::orderBy('name')->get();
        return view('salaries.edit', compact('salary', 'users'));
    }

    public function update(Request $request, Salary $salary)
    {
        // Update logic sederhana (biasanya hanya edit notes atau status)
        // Jika mau edit nominal, harus handle logic reverse kasbon dll (kompleks)
        // Disini kita update data dasar saja
        $salary->update($request->only(['notes', 'category']));
        return redirect()->route('salaries.index')->with('success', 'Data gaji diperbarui (hanya info dasar).');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();
        return redirect()->route('salaries.index')->with('success', 'Data gaji dihapus.');
    }

    // API Helper absensi (opsional jika masih dipakai di create blade lama)
     public function checkAttendance(Request $request)
    {
        $userId = $request->user_id;
        $month = $request->month;
        $year = $request->year;

        // Hitung semua yang check_in_time nya ada di bulan tsb
        // Dan statusnya dianggap hadir (Masuk, Telat, WFH, Verified, dll)
        $count = Attendance::where('user_id', $userId)
            ->whereMonth('check_in_time', $month) // Gunakan check_in_time, lebih akurat dr created_at
            ->whereYear('check_in_time', $year)
            ->where(function ($query) {
                // Cek kolom 'status' (teknis)
                $query->whereIn('status', ['present', 'late', 'verified', 'pending_verification', 'wfh'])
                    // ATAU Cek kolom 'presence_status' (label yang tampil di UI kamu: "Masuk", "Telat")
                    ->orWhereIn('presence_status', ['Masuk', 'WFH / Dinas Luar', 'Telat']);
            })
            ->count();

        return response()->json(['count' => $count]);
    }
}