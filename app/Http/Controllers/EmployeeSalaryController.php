<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        // [FIX] Filter: HANYA TAMPILKAN USER SELAIN ADMIN & ADMIN GAJI
        $query = User::with(['branch', 'division', 'employeeSalary'])
            ->where('is_active', true)
            ->whereNotIn('role', ['admin', 'admin_gaji']); // <--- INI KUNCINYA

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('category')) {
            if ($request->category == 'unset') {
                $query->doesntHave('employeeSalary');
            } else {
                $query->whereHas('employeeSalary', function($q) use ($request) {
                    $q->where('category', $request->category);
                });
            }
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('employee-salaries.index', compact('users', 'branches', 'divisions'));
    }

    public function edit($userId)
    {
        // [FIX] Validasi Edit: Cegah akses edit gaji admin
        $user = User::with('employeeSalary')->findOrFail($userId);
        
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            return redirect()->route('employee-salaries.index')->with('error', 'Gaji Admin & Admin Gaji tidak dapat diedit di sini (Rahasia).');
        }

        return view('employee-salaries.edit', compact('user'));
    }

    public function update(Request $request, $userId)
    {
        // [FIX] Validasi Update: Cegah update gaji admin via request langsung
        $user = User::findOrFail($userId);
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            abort(403, 'Akses Ditolak: Anda tidak dapat mengubah gaji Admin.');
        }

        $clean = function($val) {
            return str_replace('.', '', $val);
        };

        $request->validate([
            'category' => 'required',
        ]);

        if ($request->category == 'employee') {
            $request->merge(['basic_salary' => $clean($request->basic_salary)]);
            $request->validate(['basic_salary' => 'required|numeric|max:6000000']);
        }

        // Siapkan Data Dasar
        $data = [
            'category' => $request->category,
            'bank_account_number' => $request->bank_account_number,
            'bank_name' => $request->bank_name,
            'notes' => $request->notes, 
            'updated_by' => Auth::id(),
            // Reset nilai default 0 agar bersih jika ganti kategori
            'basic_salary' => 0, 
            'position_allowance' => 0, 
            'owner_privilege' => 0, 
            'daily_salary' => 0, 
            'promotor_bonus' => 0,
            'use_privilege_mode' => 0
        ];

        // Isi Data Sesuai Kategori
        if ($request->category == 'employee') {
            $data['basic_salary'] = $clean($request->basic_salary);
            $data['position_allowance'] = $clean($request->position_allowance);
            $data['owner_privilege'] = $clean($request->owner_privilege);
            // Simpan status Checkbox Privilege
            $data['use_privilege_mode'] = $request->has('use_privilege_mode') ? 1 : 0;
            
        } elseif ($request->category == 'promotor') {
            // Promotor pakai field promotor_bonus
            $data['promotor_bonus'] = $clean($request->promotor_bonus);
            
        } elseif ($request->category == 'freelance') {
            $data['daily_salary'] = $clean($request->daily_salary);
        }

        EmployeeSalary::updateOrCreate(['user_id' => $userId], $data);

        return redirect()->route('employee-salaries.index')->with('success', 'Master gaji berhasil disimpan.');
    }
}