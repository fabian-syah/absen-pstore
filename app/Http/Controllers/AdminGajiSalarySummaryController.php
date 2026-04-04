<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class AdminGajiSalarySummaryController extends Controller
{
    public function index()
    {
        $branches = Branch::with(['users' => function($q) {
            $q->where('is_active', true)->with('employeeSalary');
        }])->orderBy('name')->get();

        $summary = $branches->map(function($branch) {
            $totalGross = 0;
            $count = 0;

            foreach ($branch->users as $user) {
                if ($user->employeeSalary) {
                    $count++;
                    $totalGross += ($user->employeeSalary->basic_salary ?? 0) + 
                                   ($user->employeeSalary->position_allowance ?? 0) + 
                                   ($user->employeeSalary->owner_privilege ?? 0) +
                                   ($user->employeeSalary->promotor_bonus ?? 0) +
                                   ($user->employeeSalary->daily_salary ?? 0); // Jika mau harian dianggap sebulan? User biasanya minta yang master.
                }
            }

            return (object) [
                'name' => $branch->name,
                'employee_count' => $count,
                'total_gross_salary' => $totalGross
            ];
        });

        // Hitung total keseluruhan
        $grandTotalEmployees = $summary->sum('employee_count');
        $grandTotalSalary = $summary->sum('total_gross_salary');

        return view('admin_gaji.salary_summary', compact('summary', 'grandTotalEmployees', 'grandTotalSalary'));
    }
}
