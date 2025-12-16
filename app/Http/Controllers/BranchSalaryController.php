<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\Salary; // Tambahkan Model Salary
use Illuminate\Http\Request;

class BranchSalaryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $currentMonth = date('m');
        $currentYear = date('Y');

        // 1. Ambil Data Cabang beserta Karyawan Aktif & Gaji Bulan Ini
        $branches = Branch::with(['users' => function($q) use ($currentMonth, $currentYear) {
                $q->where('is_active', true)
                  ->with(['salaries' => function($s) use ($currentMonth, $currentYear) {
                      $s->where('month', $currentMonth)
                        ->where('year', $currentYear);
                  }]);
            }])
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->get();

        // 2. Hitung Statistik Global & Per Cabang
        $globalTotalEmployees = 0;
        $globalTotalSalary = 0;

        // Loop untuk memproses data tambahan (Total Gaji per Cabang)
        foreach ($branches as $branch) {
            $branchEmployeeCount = $branch->users->count();
            $branchTotalSalary = 0;

            foreach ($branch->users as $user) {
                // Jika user punya gaji bulan ini, tambahkan ke total
                $salary = $user->salaries->first();
                if ($salary) {
                    $branchTotalSalary += $salary->total_amount;
                }
            }

            // Simpan data perhitungan ke object branch (temporary attribute)
            $branch->setAttribute('employee_count', $branchEmployeeCount);
            $branch->setAttribute('total_salary_expense', $branchTotalSalary);

            // Tambahkan ke Global Total
            $globalTotalEmployees += $branchEmployeeCount;
            $globalTotalSalary += $branchTotalSalary;
        }

        return view('salary-branches.index', compact(
            'branches', 
            'search', 
            'globalTotalEmployees', 
            'globalTotalSalary',
            'currentMonth', 
            'currentYear'
        ));
    }

    public function show(Request $request, $id)
    {
        // ... (Kode show tetap sama seperti sebelumnya) ...
        $branch = Branch::findOrFail($id);
        $search = $request->input('search');

        $users = User::where('branch_id', $id)
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('login_id', 'like', "%{$search}%");
            })
            ->with(['division', 'salaries' => function($q) {
                $q->where('month', date('m'))->where('year', date('Y'));
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('salary-branches.show', compact('branch', 'users', 'search'));
    }
}