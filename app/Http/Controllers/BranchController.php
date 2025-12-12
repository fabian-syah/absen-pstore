<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Constructor: Cek Login & Role Awal
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && in_array(Auth::user()->role, ['admin', 'audit', 'leader'])) {
                return $next($request);
            }
            return abort(403, 'Hanya Admin, Audit, atau Leader yang boleh mengakses halaman ini.');
        });
    }

    /**
     * Menampilkan daftar cabang (Difilter sesuai Role + Search).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Branch::query();

        // 1. FILTER ROLE (Logika Lama)
        // Jika Admin Cabang -> Hanya lihat cabangnya sendiri
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('id', $user->branch_id);
        }
        // Jika Audit ATAU Leader -> Hanya lihat cabang wilayahnya (dari tabel pivot)
        elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            
            // Tambahkan branch_id utama user jika ada (untuk safety)
            if ($user->branch_id) {
                $allowedBranchIds[] = $user->branch_id;
            }
            $allowedBranchIds = array_unique($allowedBranchIds);

            $query->whereIn('id', $allowedBranchIds);
        }
        // Jika Super Admin -> Melihat SEMUA (Tidak ada filter role)

        // 2. FITUR SEARCH (Logika Baru)
        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            // Menggunakan closure function agar logika OR tidak merusak filter Role di atas
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        $branches = $query->latest()->get();
        
        return view('branch.branch_index', compact('branches'));
    }

    /**
     * Menampilkan Detail Cabang & Daftar Karyawannya
     */
    public function show(Branch $branch)
    {
        $user = Auth::user();

        // 1. Validasi Akses Melihat
        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id) abort(403, 'Akses Ditolak.');
        }
        elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) $allowedBranchIds[] = $user->branch_id;
            
            if (!in_array($branch->id, $allowedBranchIds)) abort(403, 'Akses Ditolak. Cabang ini bukan wilayah Anda.');
        }

        // 2. Ambil User di Cabang Ini (Eager Loading Division)
        // [UPDATE] Menggunakan nama variabel $employees agar cocok dengan blade view yang diberikan
        $employees = User::with(['division', 'attendances' => function($q) {
                // Ambil attendance hari ini saja untuk ditampilkan di tabel
                $q->whereDate('check_in_time', now());
            }])
            ->where('branch_id', $branch->id)
            ->where('role', '!=', 'admin') // Opsional: Sembunyikan super admin jika ada
            ->latest()
            ->paginate(10); 

        // 3. Hitung Statistik Ringan
        $totalEmployees = User::where('branch_id', $branch->id)->count();

        // 4. Ambil Audit Penanggung Jawab Cabang Ini
        $assignedAudits = User::where('role', 'audit')
            ->where('is_active', true)
            ->whereHas('branches', function($q) use ($branch) {
                $q->where('branches.id', $branch->id);
            })
            ->get();

        return view('branch.branch_show', compact('branch', 'employees', 'totalEmployees', 'assignedAudits'));
    }

    /**
     * Form tambah cabang (Hanya Super Admin).
     */
    public function create()
    {
        // Proteksi: Hanya Super Admin
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403, 'Anda tidak memiliki akses untuk menambah cabang.');
        }

        return view('branch.branch_create');
    }

    /**
     * Simpan cabang baru.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'nullable|string',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    /**
     * Form edit cabang.
     */
    public function edit(Branch $branch)
    {
        $user = Auth::user();

        // Proteksi: Audit/Leader tidak boleh edit
        if (in_array($user->role, ['audit', 'leader'])) abort(403, 'Anda tidak memiliki akses edit.');

        // Proteksi: Admin Cabang hanya boleh edit cabangnya sendiri
        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id) abort(403);
        }

        return view('branch.branch_edit', compact('branch'));
    }

    /**
     * Update data cabang.
     */
    public function update(Request $request, Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader'])) abort(403);
        if ($user->role == 'admin' && $user->branch_id != null && $branch->id != $user->branch_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    /**
     * Hapus cabang (Hanya Super Admin).
     */
    public function destroy(Branch $branch)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) abort(403);

        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada user yang terhubung.');
        }
    }
}