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

    // Tambahkan method ini di dalam class BranchInventoryController

    /**
     * Menampilkan Detail Inventaris di Satu Cabang Tertentu
     */
    public function show($id)
    {
        $user = Auth::user();
        $branch = Branch::findOrFail($id);

        // --- VALIDASI AKSES (Security) ---
        // 1. Jika Leader/Admin Cabang -> Cek apakah ini cabangnya dia?
        if (($user->role == 'leader' || ($user->role == 'admin' && $user->branch_id != null)) && $user->branch_id != $branch->id) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
        
        // 2. Jika Audit -> Cek apakah cabang ini ada di list audit dia?
        if ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            if (!in_array($branch->id, $auditBranchIds)) {
                abort(403, 'Cabang ini bukan wilayah audit Anda.');
            }
        }

        // --- AMBIL DATA INVENTARIS ---
        // Ambil semua barang yang user_id nya adalah karyawan di cabang ini
        // Menggunakan whereHas untuk memfilter user berdasarkan branch_id
        $inventories = \App\Models\Inventory::with('user')
            ->whereHas('user', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })
            ->latest()
            ->get();

        return view('inventory.branch_inventory_detail', compact('branch', 'inventories'));
    }
}