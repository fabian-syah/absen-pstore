<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function create(Request $request) // Tambahkan Request $request
    {
        // Ambil ID user dari URL jika ada (misal: ?user_id=5)
        $selectedUserId = $request->query('user_id');

        // Ambil semua user aktif, urutkan nama
        $users = User::where('is_active', true)
            ->with('branch') // Eager load branch biar query ringan
            ->orderBy('name', 'asc')
            ->get();

        // Kirim variable $selectedUserId ke view
        return view('salaries.create', compact('users', 'selectedUserId'));
    }

    // =========================================================================
    // [PERBAIKAN] LOGIKA HITUNG ABSENSI LEBIH LUAS
    // =========================================================================
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

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'year' => 'required',
            'category' => 'required|in:promotor,freelance,employee',
        ]);

        // Cek duplikasi gaji bulan yang sama
        $exists = Salary::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Gaji untuk karyawan ini di periode tersebut sudah dibuat. Silakan edit data yang ada.');
        }

        $totalAmount = 0;
        $data = [
            'user_id' => $request->user_id,
            'month' => $request->month,
            'year' => $request->year,
            'category' => $request->category,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ];

        // LOGIKA PERHITUNGAN
        if ($request->category == 'promotor') {
            $request->validate([
                'promotor_basic_salary' => 'required|numeric',
                'promotor_bonus' => 'nullable|numeric',
            ]);
            $data['promotor_basic_salary'] = $request->promotor_basic_salary;
            $data['promotor_bonus'] = $request->promotor_bonus ?? 0;

            $totalAmount = $data['promotor_basic_salary'] + $data['promotor_bonus'];
        } elseif ($request->category == 'freelance') {
            $request->validate([
                'freelance_daily_salary' => 'required|numeric',
            ]);

            // [PERBAIKAN] LOGIKA HITUNG ULANG SAAT SAVE (SAMA SEPERTI DI ATAS)
            $attendanceCount = Attendance::where('user_id', $request->user_id)
                ->whereMonth('check_in_time', $request->month)
                ->whereYear('check_in_time', $request->year)
                ->where(function ($query) {
                    $query->whereIn('status', ['present', 'late', 'verified', 'pending_verification', 'wfh'])
                        ->orWhereIn('presence_status', ['Masuk', 'WFH / Dinas Luar', 'Telat']);
                })
                ->count();

            $data['freelance_daily_salary'] = $request->freelance_daily_salary;
            $data['freelance_attendance_count'] = $attendanceCount;

            $totalAmount = $data['freelance_daily_salary'] * $attendanceCount;
        } elseif ($request->category == 'employee') {
            $request->validate([
                'employee_basic_salary' => 'required|numeric|max:6000000', // Max 6jt
                'employee_position_allowance' => 'nullable|numeric',
                'employee_owner_privilege' => 'nullable|numeric',
            ]);

            $data['employee_basic_salary'] = $request->employee_basic_salary;
            $data['employee_position_allowance'] = $request->employee_position_allowance ?? 0;
            $data['employee_owner_privilege'] = $request->employee_owner_privilege ?? 0;

            $totalAmount = $data['employee_basic_salary'] + $data['employee_position_allowance'] + $data['employee_owner_privilege'];
        }

        $data['total_amount'] = $totalAmount;

        Salary::create($data);

        return redirect()->route('salaries.index')->with('success', 'Data gaji berhasil disimpan.');
    }

    public function show(Salary $salary)
    {
        return view('salaries.show', compact('salary'));
    }

    public function edit(Salary $salary)
    {
        $users = User::orderBy('name', 'asc')->get();
        return view('salaries.edit', compact('salary', 'users'));
    }

    public function update(Request $request, Salary $salary)
    {
        $request->validate([
            'category' => 'required|in:promotor,freelance,employee',
        ]);

        $totalAmount = 0;
        $data = [
            'category' => $request->category,
            'notes' => $request->notes,
        ];

        if ($request->category == 'promotor') {
            $request->validate([
                'promotor_basic_salary' => 'required|numeric',
                'promotor_bonus' => 'nullable|numeric',
            ]);
            $data['promotor_basic_salary'] = $request->promotor_basic_salary;
            $data['promotor_bonus'] = $request->promotor_bonus ?? 0;

            // Reset field lain
            $data['freelance_daily_salary'] = null;
            $data['freelance_attendance_count'] = null;
            $data['employee_basic_salary'] = null;
            $data['employee_position_allowance'] = null;
            $data['employee_owner_privilege'] = null;

            $totalAmount = $data['promotor_basic_salary'] + $data['promotor_bonus'];
        } elseif ($request->category == 'freelance') {
            $request->validate([
                'freelance_daily_salary' => 'required|numeric',
            ]);

            // [PERBAIKAN] HITUNG ULANG ABSENSI SAAT UPDATE
            $attendanceCount = Attendance::where('user_id', $salary->user_id)
                ->whereMonth('check_in_time', $salary->month)
                ->whereYear('check_in_time', $salary->year)
                ->where(function ($query) {
                    $query->whereIn('status', ['present', 'late', 'verified', 'pending_verification', 'wfh'])
                        ->orWhereIn('presence_status', ['Masuk', 'WFH / Dinas Luar', 'Telat']);
                })
                ->count();

            $data['freelance_daily_salary'] = $request->freelance_daily_salary;
            $data['freelance_attendance_count'] = $attendanceCount;

            // Reset field lain
            $data['promotor_basic_salary'] = null;
            $data['promotor_bonus'] = null;
            $data['employee_basic_salary'] = null;
            $data['employee_position_allowance'] = null;
            $data['employee_owner_privilege'] = null;

            $totalAmount = $data['freelance_daily_salary'] * $attendanceCount;
        } elseif ($request->category == 'employee') {
            $request->validate([
                'employee_basic_salary' => 'required|numeric|max:6000000',
                'employee_position_allowance' => 'nullable|numeric',
                'employee_owner_privilege' => 'nullable|numeric',
            ]);

            $data['employee_basic_salary'] = $request->employee_basic_salary;
            $data['employee_position_allowance'] = $request->employee_position_allowance ?? 0;
            $data['employee_owner_privilege'] = $request->employee_owner_privilege ?? 0;

            // Reset field lain
            $data['promotor_basic_salary'] = null;
            $data['promotor_bonus'] = null;
            $data['freelance_daily_salary'] = null;
            $data['freelance_attendance_count'] = null;

            $totalAmount = $data['employee_basic_salary'] + $data['employee_position_allowance'] + $data['employee_owner_privilege'];
        }

        $data['total_amount'] = $totalAmount;

        $salary->update($data);

        return redirect()->route('salaries.index')->with('success', 'Data gaji berhasil diperbarui.');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();
        return redirect()->route('salaries.index')->with('success', 'Data gaji dihapus.');
    }
}
