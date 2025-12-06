<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchInventoryController extends Controller
{
    /**
     * Constructor: Admin, Audit, dan Leader boleh akses.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && in_array(Auth::user()->role, ['admin', 'audit', 'leader'])) {
                return $next($request);
            }
            return abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        });
    }

    /**
     * Menampilkan daftar cabang beserta ringkasan inventarisnya.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Branch::query();

        // --- LOGIC FILTER DATA CABANG ---

        // 1. Jika Admin Cabang ATAU Leader -> Hanya lihat cabangnya sendiri
        if (($user->role == 'admin' && $user->branch_id != null) || $user->role == 'leader') {
            $query->where('id', $user->branch_id);
        } 
        
        // 2. Jika Audit -> Hanya lihat cabang yang diaudit (dari tabel pivot)
        elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('id', $auditBranchIds);
        }
        
        // 3. Jika Super Admin (Admin tanpa branch_id) -> Melihat SEMUA (Tidak ada where)

        
        // --- AMBIL DATA DENGAN EAGER LOADING ---
        // Kita perlu relasi: Branch -> Users -> Inventories
        $branches = $query->with(['users.inventories' => function ($q) {
            // Opsional: Jika ingin memfilter barang rusak berat agar tidak dihitung, buka komen ini:
            // $q->where('condition', '!=', 'Rusak Berat'); 
        }])->latest()->get();

        // --- HITUNG RINGKASAN KATEGORI ---
        $branches->transform(function ($branch) {
            // Gabungkan semua inventaris dari semua user di cabang ini menjadi satu koleksi
            $allInventories = $branch->users->flatMap(function ($user) {
                return $user->inventories;
            });

            // Kelompokkan berdasarkan 'category' dan hitung jumlahnya
            // Hasil: ['Laptop' => 5, 'Handphone' => 10, ...]
            $branch->inventory_summary = $allInventories->groupBy('category')->map(function ($items) {
                return $items->count();
            });

            // Hitung total aset keseluruhan
            $branch->total_assets = $allInventories->count();

            return $branch;
        });

        return view('inventory.branch_inventory_list', compact('branches'));
    }
}