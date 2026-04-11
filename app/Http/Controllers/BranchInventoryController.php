<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        // A. Jika Admin Cabang (Bukan Super Admin) -> Hanya lihat cabangnya sendiri
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('id', $user->branch_id);
        } 
        // B. Jika Audit ATAU Leader -> Melihat Multi-Cabang (Dari Pivot + Main Branch)
        elseif (in_array($user->role, ['audit', 'leader'])) {
            // Ambil cabang dari relasi many-to-many (pivot)
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            
            // Jika Leader: homebase branch otomatis masuk.
            // Jika Audit: homebase branch (64) TIDAK otomatis masuk agar tidak mengotori list wilayah audit aset.
            if ($user->role == 'leader' && $user->branch_id) {
                $allowedBranchIds[] = $user->branch_id;
            }
            
            // Hapus duplikat id dan filter query
            $allowedBranchIds = array_unique($allowedBranchIds);
            $query->whereIn('id', $allowedBranchIds);
        }
        // C. Jika Super Admin (Admin tanpa branch_id) -> Melihat SEMUA (Tidak ada where)

        
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
            // Filter tambahan inventory jika diperlukan bisa disini
        }])->latest()->get();

        // --- 4. HITUNG RINGKASAN KATEGORI & SORTING ---
        $branches->transform(function ($branch) {
            // Gabungkan semua inventaris dari semua user di cabang ini menjadi satu koleksi
            $allInventories = $branch->users->flatMap(function ($user) {
                return $user->inventories;
            });

            // Kelompokkan berdasarkan 'category' -> hitung jumlahnya -> URUTKAN DARI TERBANYAK
            // Hasil: ['Laptop' => 10, 'Handphone' => 5, ...] (Urut Terbanyak)
            $branch->inventory_summary = $allInventories->groupBy('category')
                ->map(function ($items) {
                    return $items->count();
                })
                ->sortDesc(); // Logika Sorting desc

            // Hitung total aset keseluruhan
            $branch->total_assets = $allInventories->count();

            return $branch;
        });

        return view('inventory.branch_inventory_list', compact('branches'));
    }

    /**
     * Menampilkan Detail Inventaris di Satu Cabang Tertentu
     */
    public function show($id, Request $request)
    {
        $user = Auth::user();
        $branch = Branch::findOrFail($id);

        // --- VALIDASI AKSES (Security) ---
        
        // 1. Validasi untuk Admin Cabang (Single Branch)
        if ($user->role == 'admin' && $user->branch_id != null && $user->branch_id != $branch->id) {
             abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }

        // 2. Validasi untuk Audit ATAU Leader (Multi Branch)
        if (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            
            // Leader dapat akses homebase, Audit hanya dapat yang di pivot
            if ($user->role != 'audit' && $user->branch_id) {
                $allowedBranchIds[] = $user->branch_id;
            }

            if (!in_array($branch->id, $allowedBranchIds)) {
                abort(403, 'Cabang ini bukan wilayah otoritas/audit Anda.');
            }
        }

        // --- AMBIL DATA INVENTARIS ---
        $inventoryQuery = Inventory::with('user')
            ->whereHas('user', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            });

        // --- SEARCH DI HALAMAN DETAIL ---
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

        return view('inventory.branch_inventory_detail', compact('branch', 'inventories'));
    }
}