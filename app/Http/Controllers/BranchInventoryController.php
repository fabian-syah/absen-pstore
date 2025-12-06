<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory; // Jangan lupa use Model Inventory
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
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Branch::query();

        // --- 1. LOGIC FILTER DATA CABANG (Security Layer) ---

        // Jika Admin Cabang ATAU Leader -> Hanya lihat cabangnya sendiri
        if (($user->role == 'admin' && $user->branch_id != null) || $user->role == 'leader') {
            $query->where('id', $user->branch_id);
        } 
        // Jika Audit -> Hanya lihat cabang yang diaudit (dari tabel pivot)
        elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('id', $auditBranchIds);
        }
        // Jika Super Admin (Admin tanpa branch_id) -> Melihat SEMUA (Tidak ada where)

        
        // --- 2. LOGIC SEARCH / PENCARIAN ---
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")       // Cari Nama Cabang
                  ->orWhere('address', 'like', "%{$search}%"); // Cari Alamat Cabang
            });
        }


        // --- 3. AMBIL DATA DENGAN EAGER LOADING ---
        // Kita perlu relasi: Branch -> Users -> Inventories
        $branches = $query->with(['users.inventories' => function ($q) {
            // Kita bisa filter barang di sini jika mau
        }])->latest()->get();


        // --- 4. HITUNG RINGKASAN KATEGORI ---
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

        // Pastikan nama view sesuai dengan lokasi file index.blade.php kamu
        return view('inventory.branch.index', compact('branches'));
    }

    /**
     * Menampilkan Detail Inventaris di Satu Cabang Tertentu
     */
    public function show($id, Request $request)
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
        $inventoryQuery = Inventory::with('user')
            ->whereHas('user', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            });

        // --- SEARCH DI HALAMAN DETAIL (Opsional tapi bagus) ---
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $inventoryQuery->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('user', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $inventories = $inventoryQuery->latest()->get();

        // Pastikan nama view sesuai dengan lokasi file detail kamu
        // Biasanya: inventory/branch/detail.blade.php atau inventory/branch_inventory_detail.blade.php
        return view('inventory.branch.detail', compact('branch', 'inventories'));
    }
}