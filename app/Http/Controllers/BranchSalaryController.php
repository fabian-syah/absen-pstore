<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class BranchSalaryController extends Controller
{
    /**
     * Menampilkan daftar seluruh cabang
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $branches = Branch::withCount('users') // Hitung jumlah karyawan
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('salary-branches.index', compact('branches', 'search'));
    }

    /**
     * Menampilkan daftar karyawan di cabang tertentu
     */
    public function show(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $search = $request->input('search');

        $users = User::where('branch_id', $id)
            ->where('is_active', true) // Hanya karyawan aktif
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('login_id', 'like', "%{$search}%");
            })
            ->with(['division', 'salaries' => function($q) {
                // Ambil gaji bulan ini untuk cek status
                $q->where('month', date('m'))->where('year', date('Y'));
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('salary-branches.show', compact('branch', 'users', 'search'));
    }
}