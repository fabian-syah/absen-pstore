<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\Salary;
use Illuminate\Http\Request;

class BranchSalaryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $currentMonth = date('m');
        $currentYear = date('Y');

        $branches = Branch::with([
            'users' => function ($q) use ($currentMonth, $currentYear) {
                $q->where('is_active', true)
                    ->with([
                        'salaries' => function ($s) use ($currentMonth, $currentYear) {
                            $s->where('month', $currentMonth)->where('year', $currentYear);
                        }
                    ]);
            }
        ])
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')->get();

        $globalTotalEmployees = 0;
        $globalTotalSalary = 0;

        foreach ($branches as $branch) {
            $cnt = $branch->users->count();
            $tot = 0;
            foreach ($branch->users as $user) {
                $sal = $user->salaries->first();
                // Hanya hitung total jika status paid (opsional, atau hitung semua)
                if ($sal)
                    $tot += $sal->total_amount;
            }
            $branch->setAttribute('employee_count', $cnt);
            $branch->setAttribute('total_salary_expense', $tot);
            $globalTotalEmployees += $cnt;
            $globalTotalSalary += $tot;
        }

        return view('salary-branches.index', compact('branches', 'search', 'globalTotalEmployees', 'globalTotalSalary', 'currentMonth', 'currentYear'));
    }

    public function show(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $search = $request->input('search');
        $month = str_pad($request->input('month', date('m')), 2, '0', STR_PAD_LEFT);
        $year = $request->input('year', date('Y'));

        $users = User::where('branch_id', $id)
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")->orWhere('login_id', 'like', "%{$search}%");
            })
            ->with([
                'division',
                'salaries' => function ($q) {
                    $q->orderBy('year', 'desc')->orderBy('month', 'desc');
                }
            ])
            ->orderBy('name', 'asc')->get();

        return view('salary-branches.show', compact('branch', 'users', 'search', 'month', 'year'));
    }
}