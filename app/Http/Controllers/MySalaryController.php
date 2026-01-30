<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MySalaryController extends Controller
{
    /**
     * Menampilkan daftar gaji milik user yang sedang login.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil Filter dari Request
        $month = $request->input('month');
        $year = $request->input('year');
        $branchId = $request->input('branch_id');
        $search = $request->input('search');

        // Query Gaji
        $query = Salary::with(['user.branch', 'user.division'])
            ->where('status', '!=', 'draft');

        // Filter Periode
        if ($month) {
            $query->where('month', $month);
        }
        if ($year) {
            $query->where('year', $year);
        }

        // Logic Admin / Admin Gaji
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            // Admin bisa filter Cabang
            if ($branchId) {
                $query->whereHas('user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
            // Admin bisa Search Nama
            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
        } else {
            // User Biasa: HANYA punya sendiri
            $query->where('user_id', $user->id);
            // User biasa TIDAK BOLEH filter user lain (ignore branch_id / search input if exists)
        }

        $salaries = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc') // Sort tambahan biar rapi
            ->paginate(10)
            ->withQueryString(); // Agar pagination link tetap bawa filter

        $branches = [];
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            $branches = \App\Models\Branch::all();
        }

        return view('my-salary.index', compact('salaries', 'branches'));
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();
        $date = date('d-m-Y');

        // Logic Security: Tentukan apakah harus difilter per user ID
        $restrictedUserId = null;
        if (!in_array($user->role, ['admin', 'admin_gaji'])) {
            $restrictedUserId = $user->id;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SalaryExport($filters, $restrictedUserId), "laporan_gaji_{$date}.xlsx");
    }

    /**
     * Menampilkan Detail Struk Gaji
     * (Sebenarnya bisa pakai SalaryController@show, tapi kita buat wrapper aman disini)
     */
    public function show($id)
    {
        $salary = Salary::with(['user.branch', 'user.division', 'user.employeeSalary'])->findOrFail($id);
        $currentUser = Auth::user();

        // Validasi Akses: User hanya boleh liat punya sendiri, kecuali Admin/Admin Gaji
        if ($currentUser->id != $salary->user_id && !in_array($currentUser->role, ['admin', 'admin_gaji'])) {
            abort(403, 'Anda tidak berhak melihat struk gaji ini.');
        }

        // Kita gunakan view yang sama dengan admin agar desain konsisten
        return view('salaries.show', compact('salary'));
    }
}