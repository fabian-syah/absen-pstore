<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ViolationController extends Controller
{
    /**
     * Menampilkan daftar pelanggaran.
     */
    public function index()
    {
        $user = auth()->user();
        $query = Violation::with(['user', 'reporter']);

        // LOGIKA FILTER VIEW
        if ($user->role === 'admin') {
            // Admin lihat semua, tidak ada filter
        } elseif ($user->role === 'audit') {
            // FIX: Audit melihat user di cabang yang dipegang ATAU pelanggaran dirinya sendiri
            $branchIds = $user->branches->pluck('id');
            
            $query->where(function($q) use ($branchIds, $user) {
                // 1. User yang ada di cabang yang dipegang audit
                $q->whereHas('user', function ($subQ) use ($branchIds) {
                    $subQ->whereIn('branch_id', $branchIds);
                })
                // 2. ATAU pelanggaran milik diri sendiri (Audit juga bisa kena pelanggaran)
                ->orWhere('user_id', $user->id);
            });

        } else {
            // User Biasa, Security, Leader hanya lihat punya sendiri
            $query->where('user_id', $user->id);
        }

        // LOGIKA SORTING (Berat -> Sedang -> Ringan)
        $violations = $query->orderByRaw("FIELD(category, 'berat', 'sedang', 'ringan')")
                            ->orderBy('created_at', 'desc')
                            ->get();

        return view('violations.index', compact('violations'));
    }

    /**
     * Form tambah pelanggaran (Hanya Admin & Audit).
     */
    public function create()
    {
        $this->authorizeAccess();

        $currentUser = auth()->user();
        
        // Admin bisa pilih semua user
        if ($currentUser->role === 'admin') {
            $users = User::where('role', '!=', 'admin')->orderBy('name')->get();
        } 
        // Audit hanya bisa pilih user di cabangnya
        elseif ($currentUser->role === 'audit') {
            $branchIds = $currentUser->branches->pluck('id');
            $users = User::whereIn('branch_id', $branchIds)
                         ->where('role', '!=', 'audit') // Opsional: audit tidak menindak sesama audit
                         ->where('role', '!=', 'admin')
                         ->orderBy('name')
                         ->get();
        } else {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('violations.create', compact('users'));
    }

    /**
     * Simpan data pelanggaran.
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:ringan,sedang,berat',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except(['photo']);
        $data['reported_by'] = auth()->id();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('violation-photos', 'public');
        }

        Violation::create($data);

        return redirect()->route('violations.index')->with('success', 'Pelanggaran berhasil dicatat.');
    }

    /**
     * Form edit pelanggaran.
     */
    public function edit(Violation $violation)
    {
        $this->authorizeAccess(); // Cek admin/audit
        
        // Pastikan audit hanya mengedit user cabangnya
        if (auth()->user()->role === 'audit') {
            $branchIds = auth()->user()->branches->pluck('id');
            if (!in_array($violation->user->branch_id, $branchIds->toArray())) {
                abort(403, 'Akses ditolak. User ini bukan di cabang Anda.');
            }
        }

        $users = User::all(); // Untuk dropdown (bisa difilter lagi jika perlu)
        return view('violations.edit', compact('violation', 'users'));
    }

    /**
     * Update pelanggaran.
     */
    public function update(Request $request, Violation $violation)
    {
        $this->authorizeAccess();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:ringan,sedang,berat',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except(['photo']);

        if ($request->hasFile('photo')) {
            // Hapus foto lama
            if ($violation->photo_path) {
                Storage::disk('public')->delete($violation->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('violation-photos', 'public');
        }

        $violation->update($data);

        return redirect()->route('violations.index')->with('success', 'Data pelanggaran diperbarui.');
    }

    /**
     * Hapus pelanggaran.
     */
    public function destroy(Violation $violation)
    {
        $this->authorizeAccess();

        // Cek akses audit
        if (auth()->user()->role === 'audit') {
            $branchIds = auth()->user()->branches->pluck('id');
            if (!in_array($violation->user->branch_id, $branchIds->toArray())) {
                abort(403, 'Akses ditolak.');
            }
        }

        if ($violation->photo_path) {
            Storage::disk('public')->delete($violation->photo_path);
        }

        $violation->delete();

        return redirect()->route('violations.index')->with('success', 'Data pelanggaran dihapus.');
    }

    // Helper untuk cek role
    private function authorizeAccess()
    {
        if (!in_array(auth()->user()->role, ['admin', 'audit'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola data ini.');
        }
    }
}