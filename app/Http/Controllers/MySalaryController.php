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
    public function index()
    {
        $user = Auth::user();

        // Query Gaji
        $query = Salary::with(['user.branch', 'user.division'])
            ->where('status', '!=', 'draft'); // Hanya tampilkan yang sudah diproses/pending/paid

        // Jika bukan admin/admin_gaji, HANYA tampilkan punya sendiri
        if (!in_array($user->role, ['admin', 'admin_gaji'])) {
            $query->where('user_id', $user->id);
            // Optional: Hanya tampilkan jika status 'paid' jika ingin user lihat setelah lunas
            // $query->where('status', 'paid'); 
        }

        $salaries = $query->orderBy('year', 'desc')
                          ->orderBy('month', 'desc')
                          ->paginate(10);

        return view('my-salary.index', compact('salaries'));
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