<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $users = User::with(['branch', 'employeeSalary'])
            ->where('is_active', true)
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return view('employee-salaries.index', compact('users', 'search'));
    }

    public function edit($userId)
    {
        $user = User::with('employeeSalary')->findOrFail($userId);
        return view('employee-salaries.edit', compact('user'));
    }

    public function update(Request $request, $userId)
    {
        // 1. Bersihkan Format Rupiah (Hapus titik jika ada input manual "1.000.000")
        // Fungsi helper sederhana untuk membersihkan input
        $clean = function($val) {
            return str_replace('.', '', $val);
        };

        // 2. Validasi Umum
        $request->validate([
            'category' => 'required|in:employee,promotor,freelance',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:50',
        ]);

        // 3. Validasi Khusus per Kategori
        if ($request->category == 'employee') {
            $request->merge(['basic_salary' => $clean($request->basic_salary)]);
            $request->validate([
                'basic_salary' => 'required|numeric|max:6000000', // MAKSIMAL 6 JUTA
            ], [
                'basic_salary.max' => 'Gaji Pokok Karyawan Tetap maksimal Rp 6.000.000',
            ]);
        }

        // 4. Siapkan Data Simpan
        $data = [
            'category' => $request->category,
            'bank_account_number' => $request->bank_account_number,
            'bank_name' => $request->bank_name,
            'updated_by' => Auth::id(),
            // Reset nilai default 0, nanti diisi sesuai kategori di bawah
            'basic_salary' => 0,
            'position_allowance' => 0,
            'owner_privilege' => 0,
            'daily_salary' => 0,
            'promotor_bonus' => 0,
        ];

        // 5. Isi Data Sesuai Kategori
        if ($request->category == 'employee') {
            $data['basic_salary'] = $clean($request->basic_salary);
            $data['position_allowance'] = $clean($request->position_allowance);
            $data['owner_privilege'] = $clean($request->owner_privilege);

        } elseif ($request->category == 'promotor') {
            // Promotor pakai field basic_salary utk gaji bulanan
            $data['basic_salary'] = $clean($request->promotor_monthly_salary); 
            $data['promotor_bonus'] = $clean($request->promotor_bonus);

        } elseif ($request->category == 'freelance') {
            $data['daily_salary'] = $clean($request->daily_salary);
        }

        // 6. Simpan ke Database
        EmployeeSalary::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        return redirect()->route('employee-salaries.index')->with('success', 'Master gaji karyawan berhasil diperbarui.');
    }
}