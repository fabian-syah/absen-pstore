<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryController extends Controller
{
    // Menampilkan daftar karyawan untuk di-setting gajinya
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $users = User::with(['branch', 'employeeSalary']) // Load relasi master gaji
            ->where('is_active', true)
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return view('employee-salaries.index', compact('users', 'search'));
    }

    // Form Edit Master Gaji
    public function edit($userId)
    {
        $user = User::with('employeeSalary')->findOrFail($userId);
        return view('employee-salaries.edit', compact('user'));
    }

    // Simpan Master Gaji
    public function update(Request $request, $userId)
    {
        $request->validate([
            'category' => 'required',
            'basic_salary' => 'nullable|numeric',
            'position_allowance' => 'nullable|numeric',
            'owner_privilege' => 'nullable|numeric',
        ]);

        EmployeeSalary::updateOrCreate(
            ['user_id' => $userId], // Kunci pencarian
            [
                'category' => $request->category,
                'basic_salary' => $request->basic_salary ?? 0,
                'position_allowance' => $request->position_allowance ?? 0,
                'owner_privilege' => $request->owner_privilege ?? 0,
                'daily_salary' => $request->daily_salary ?? 0,
                'updated_by' => Auth::id()
            ]
        );

        return redirect()->route('employee-salaries.index')->with('success', 'Master gaji karyawan berhasil diperbarui.');
    }
}