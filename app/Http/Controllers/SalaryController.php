<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest; // Model Izin
use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    /**
     * Menampilkan daftar riwayat gaji.
     */
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

    /**
     * Menampilkan Form Input Gaji (Payroll).
     * Disini terjadi perhitungan otomatis Absensi & Hutang.
     */
    public function create(Request $request)
    {
        // 1. Ambil Parameter
        $selectedUserId = $request->query('user_id');
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        // 2. Inisialisasi Variable
        $selectedUser = null;
        $remainingDebt = 0;
        $alphaCount = 0;
        $lateCount = 0;
        $masterSalary = null;
        $freelanceAttendance = 0;

        // List User untuk Dropdown
        $users = User::where('is_active', true)->orderBy('name')->get();

        // 3. Jika User Dipilih, Hitung Data
        if ($selectedUserId) {
            $selectedUser = User::with(['branch', 'division', 'employeeSalary'])->find($selectedUserId);

            // A. Ambil Template Master Gaji
            if ($selectedUser->employeeSalary) {
                $masterSalary = $selectedUser->employeeSalary;
            }

            // B. Hitung Sisa Hutang (Kasbon)
            $activeLoans = CashAdvance::where('user_id', $selectedUserId)
                ->where('status', 'approved')
                ->whereRaw('total_paid < amount')
                ->get();
            
            foreach($activeLoans as $loan) {
                $remainingDebt += ($loan->amount - $loan->total_paid);
            }

            // =======================================================
            // C. HITUNG ABSENSI (ALPHA & TELAT) - LOGIC FIX
            // =======================================================
            
            // 1. Hitung TELAT FISIK (Dari tabel attendance / mesin)
            // Logic: is_late_checkin = 1 ATAU status string mengandung 'late'/'Telat'
            $telatFisik = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->where(function($q) {
                    $q->where('is_late_checkin', true)
                      ->orWhere('status', 'late')
                      ->orWhere('presence_status', 'like', '%Telat%');
                })
                ->count();

            // 2. Hitung IZIN TELAT (Dari tabel leave_requests)
            // Logic: Type = 'telat' dan Status = 'approved'
            $izinTelat = LeaveRequest::where('user_id', $selectedUserId)
                ->where('type', 'telat')
                ->where('status', 'approved')
                ->whereMonth('start_date', $month)
                ->whereYear('start_date', $year)
                ->count();

            // TOTAL TELAT (Gabungan)
            $lateCount = $telatFisik + $izinTelat;

            // 3. Hitung ALPHA
            // Logic: Status = 'alpha' ATAU 'Alpha'
            // Gunakan created_at karena alpha mungkin tidak punya check_in_time
            $alphaCount = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where(function($q) {
                    $q->where('status', 'alpha')
                      ->orWhere('presence_status', 'Alpha');
                })
                ->count();

            // 4. Hitung Kehadiran Freelance (Untuk pengali gaji harian)
            // Hadir = Masuk, WFH, Dinas, Telat
            $freelanceAttendance = Attendance::where('user_id', $selectedUserId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->where(function($q) {
                    $q->whereIn('presence_status', ['Masuk', 'WFH', 'Telat', 'Izin Telat', 'Dinas Luar'])
                      ->orWhereIn('status', ['present', 'late', 'wfh']);
                })
                ->count();
        }

        return view('salaries.create', compact(
            'users', 'selectedUser', 'remainingDebt', 
            'alphaCount', 'lateCount', 'masterSalary', 
            'month', 'year', 'freelanceAttendance'
        ));
    }

    /**
     * Menyimpan Data Gaji.
     */
    public function store(Request $request)
    {
        // 1. BERSIHKAN FORMAT RUPIAH (Hapus Titik) SEBELUM VALIDASI
        // Input dari view: "2.000.000" -> Menjadi: "2000000"
        $inputsToClean = [
            'employee_basic_salary', 'employee_position_allowance', 
            'employee_owner_privilege', 'promotor_bonus', 
            'dispensation_amount', 'alpha_deduction', 
            'late_deduction', 'kasbon_deduction', 'other_deduction',
            'freelance_daily_salary'
        ];

        foreach ($inputsToClean as $field) {
            if ($request->has($field)) {
                $cleanValue = str_replace('.', '', $request->input($field));
                $request->merge([$field => $cleanValue]);
            }
        }

        // 2. Validasi
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'year' => 'required',
            'category' => 'required',
            'payment_method' => 'required|in:cash,transfer',
            'send_type' => 'required|in:now,later',
            'scheduled_date' => 'required_if:send_type,later',
        ]);

        // Cek Duplikasi Gaji Bulan Ini
        $exists = Salary::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Gaji untuk karyawan ini di periode tersebut sudah dibuat!');
        }

        // 3. Proses Transaksi Database
        DB::transaction(function() use ($request) {
            // Siapkan Data Dasar
            $data = $request->except(['_token', 'send_type', 'scheduled_date']);
            $data['created_by'] = Auth::id();

            // Logic Jadwal Pengiriman
            if ($request->send_type == 'now') {
                $data['published_at'] = now();
                $data['status'] = 'paid';
            } else {
                $data['published_at'] = $request->scheduled_date;
                $data['status'] = 'pending';
            }

            // Logic Potong Kasbon (Buat record cicilan otomatis)
            if ($request->kasbon_deduction > 0) {
                $deductionAmount = $request->kasbon_deduction;
                $userId = $request->user_id;

                // Cari hutang aktif user (urutkan dari yang terlama)
                $activeLoans = CashAdvance::where('user_id', $userId)
                    ->where('status', 'approved')
                    ->whereRaw('total_paid < amount')
                    ->orderBy('due_date', 'asc')
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
                        'received_by' => 'SYSTEM (Payroll)',
                        'payment_proof' => null,
                        'status' => 'approved',
                        'note' => 'Potongan Gaji ' . $request->month . '/' . $request->year
                    ]);

                    // Update Induk Kasbon
                    $loan->total_paid += $bayar;
                    if ($loan->total_paid >= $loan->amount) {
                        $loan->status = 'paid';
                        $loan->repayment_date = now();
                    }
                    $loan->save();
                }
            }

            // 4. Hitung Total Akhir (Income - Deduction)
            $income = 0;

            // Hitung Income Berdasarkan Kategori
            if ($request->category == 'employee') {
                $income = ($request->employee_basic_salary ?? 0) + 
                          ($request->employee_position_allowance ?? 0) + 
                          ($request->employee_owner_privilege ?? 0);
            
            } elseif ($request->category == 'promotor') {
                // Promotor: Input Basic Salary = Gaji 1 Bulan
                $income = ($request->employee_basic_salary ?? 0); 
            
            } elseif ($request->category == 'freelance') {
                // Freelance: Gaji Harian * Kehadiran
                // Kita hitung ulang kehadiran server-side untuk keamanan
                $attendanceCount = Attendance::where('user_id', $request->user_id)
                    ->whereMonth('check_in_time', $request->month)
                    ->whereYear('check_in_time', $request->year)
                    ->where(function($q) {
                        $q->whereIn('presence_status', ['Masuk', 'WFH', 'Telat', 'Izin Telat', 'Dinas Luar'])
                          ->orWhereIn('status', ['present', 'late', 'wfh']);
                    })
                    ->count();
                
                $income = ($request->freelance_daily_salary ?? 0) * $attendanceCount;
                
                // Simpan jumlah hari hadir freelance ke kolom notes atau kolom khusus jika perlu
                $data['freelance_attendance_count'] = $attendanceCount; 
            }

            // Tambahan Income Global
            $income += ($request->promotor_bonus ?? 0);
            $income += ($request->dispensation_amount ?? 0);

            // Total Deduction
            $deduction = ($request->alpha_deduction ?? 0) + 
                         ($request->late_deduction ?? 0) + 
                         ($request->kasbon_deduction ?? 0) + 
                         ($request->other_deduction ?? 0);

            $data['total_amount'] = $income - $deduction;

            // Simpan Salary
            Salary::create($data);
        });

        // Redirect ke halaman detail cabang
        return redirect()->route('branch-salary.show', User::find($request->user_id)->branch_id)
            ->with('success', 'Payroll berhasil disimpan.');
    }

    public function show($id)
    {
        $salary = Salary::with(['user.branch', 'user.division'])->findOrFail($id);
        return view('salaries.show', compact('salary'));
    }

    public function edit(Salary $salary)
    {
        $users = User::orderBy('name')->get();
        // Disini kita return view edit yang hanya bisa ubah status/notes
        // Jika ingin full edit, gunakan view edit_full
        return view('salaries.edit', compact('salary', 'users'));
    }

    public function update(Request $request, Salary $salary)
    {
        $salary->update($request->only(['notes', 'status', 'published_at']));
        return redirect()->route('branch-salary.show', $salary->user->branch_id)->with('success', 'Data updated.');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();
        // Note: Idealnya rollback kasbon juga dilakukan disini jika perlu
        return back()->with('success', 'Data gaji dihapus.');
    }
}