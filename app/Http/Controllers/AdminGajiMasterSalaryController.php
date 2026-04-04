<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGajiMasterSalaryController extends Controller
{
    private function getBranch()
    {
        return Branch::where('name', 'Cabang User Non Karyawan')->first();
    }

    public function index(Request $request)
    {
        $specialBranch = $this->getBranch();
        
        $query = User::with(['branch', 'division', 'employeeSalary'])
            ->where('is_active', true)
            ->where('branch_id', $specialBranch?->id ?? 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
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

        return view('admin_gaji.employee-salaries.index', compact('users'));
    }

    public function edit(Request $request, $userId)
    {
        $user = User::with('employeeSalary')->findOrFail($userId);
        
        // Pastikan user ini berada di cabang non-karyawan
        $specialBranch = $this->getBranch();
        if ($user->branch_id != $specialBranch?->id) {
            return redirect()->route('admin-gaji.employee-salaries.index')->with('error', 'Akses Ditolak: User ini bukan anggota Non Karyawan.');
        }

        $currentPage = $request->input('page', 1);

        return view('admin_gaji.employee-salaries.edit', compact('user', 'currentPage'));
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // Pastikan user ini berada di cabang non-karyawan
        $specialBranch = $this->getBranch();
        if ($user->branch_id != $specialBranch?->id) {
            abort(403, 'Akses Ditolak.');
        }

        $clean = function($val) {
            return str_replace('.', '', $val);
        };

        $request->validate([
            'category' => 'required',
        ]);

        $data = [
            'category' => $request->category,
            'bank_account_number' => $request->bank_account_number,
            'bank_name' => $request->bank_name,
            'notes' => $request->notes, 
            'updated_by' => Auth::id(),
            'basic_salary' => 0, 
            'position_allowance' => 0, 
            'owner_privilege' => 0, 
            'daily_salary' => 0, 
            'promotor_bonus' => 0,
            'use_privilege_mode' => 0
        ];

        if ($request->category == 'employee') {
            $basicSalary = (int) $clean($request->basic_salary);
            
            if ($basicSalary > 6000000) {
                return back()->withInput()->with('error', 'Gagal Simpan: Gaji Pokok tidak boleh melebihi Rp 6.000.000!');
            }

            $data['basic_salary'] = $basicSalary;
            $data['position_allowance'] = (int) $clean($request->position_allowance);
            $data['owner_privilege'] = (int) $clean($request->owner_privilege);
            $data['use_privilege_mode'] = $request->has('use_privilege_mode') ? 1 : 0;
            
        } elseif ($request->category == 'promotor') {
            $data['promotor_bonus'] = (int) $clean($request->promotor_bonus);
            
        } elseif ($request->category == 'freelance') {
            $data['daily_salary'] = (int) $clean($request->daily_salary);
        }

        EmployeeSalary::updateOrCreate(['user_id' => $userId], $data);

        return redirect()->route('admin-gaji.employee-salaries.index', [
            'page'      => $request->input('page', 1),            
            'search'    => $request->input('current_search'),     
            'category'  => $request->input('current_category')    
        ])->with('success', 'Master gaji berhasil disimpan.');
    }
}
