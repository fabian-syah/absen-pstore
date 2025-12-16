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

    public function create()
    {
        // Ambil semua user kecuali admin/super admin jika perlu, urutkan nama
        $users = User::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
            
        return view('salaries.create', compact('users'));
    }

    // Helper untuk AJAX cek absensi freelance
    public function checkAttendance(Request $request)
    {
        $userId = $request->user_id;
        $month = $request->month;
        $year = $request->year;

        $count = Attendance::where('user_id', $userId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status', ['present', 'wfh']) // Menghitung Present dan WFH
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

            // Hitung Absensi Otomatis (Present + WFH)
            $attendanceCount = Attendance::where('user_id', $request->user_id)
                ->whereMonth('created_at', $request->month)
                ->whereYear('created_at', $request->year)
                ->whereIn('status', ['present', 'wfh'])
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
        // Logika update mirip store, tapi tanpa validasi duplikasi user+bulan (karena sedang edit record yg sama)
        $request->validate([
            'category' => 'required|in:promotor,freelance,employee',
        ]);

        $totalAmount = 0;
        $data = [
            'category' => $request->category,
            'notes' => $request->notes,
        ];

        // LOGIKA PERHITUNGAN ULANG
        if ($request->category == 'promotor') {
            $request->validate([
                'promotor_basic_salary' => 'required|numeric',
                'promotor_bonus' => 'nullable|numeric',
            ]);
            $data['promotor_basic_salary'] = $request->promotor_basic_salary;
            $data['promotor_bonus'] = $request->promotor_bonus ?? 0;
            // Reset field lain agar bersih
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

            // Hitung ulang absensi jika mau (atau pakai nilai lama jika tidak mau berubah)
            // Disini kita hitung ulang untuk memastikan akurasi realtime saat edit
            $attendanceCount = Attendance::where('user_id', $salary->user_id)
                ->whereMonth('created_at', $salary->month)
                ->whereYear('created_at', $salary->year)
                ->whereIn('status', ['present', 'wfh'])
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