<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ViolationController extends Controller
{
    public function index(Request $request) // [UPDATED] Tambah Request
    {
        $user = auth()->user();
        
        // [UPDATED] Logika Filter Tahun
        // Ambil tahun dari request, jika tidak ada pakai tahun sekarang
        $selectedYear = $request->get('year', date('Y')); 

        $query = Violation::with(['user', 'reporter'])
            ->whereYear('created_at', $selectedYear); // [UPDATED] Filter Query

        if ($user->role === 'admin') {
            // Admin lihat semua (sudah terfilter tahun di atas)
        } elseif ($user->role === 'audit') {
            $branchIds = $user->branches->pluck('id');
            $query->where(function($q) use ($branchIds, $user) {
                $q->whereHas('user', function ($subQ) use ($branchIds) {
                    $subQ->whereIn('branch_id', $branchIds);
                })
                ->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('user_id', $user->id);
        }

        $violations = $query->orderByRaw("FIELD(category, 'berat', 'sedang', 'ringan')")
                            ->orderBy('created_at', 'desc')
                            ->get();

        // [UPDATED] Kirim $selectedYear ke view
        return view('violations.index', compact('violations', 'selectedYear'));
    }

    // ... (Method create, store, edit, update, destroy TETAP SAMA seperti sebelumnya) ...
    // Copy paste sisa method create sampai destroy dari kode lama Anda di sini
    public function create()
    {
        // ... kode create Anda ...
        if (!in_array(auth()->user()->role, ['admin', 'audit'])) {
            abort(403);
        }

        $currentUser = auth()->user();
        
        if ($currentUser->role === 'admin') {
            $users = User::where('role', '!=', 'admin')->orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            $branchIds = $currentUser->branches->pluck('id');
            $users = User::whereIn('branch_id', $branchIds)
                          ->where('role', '!=', 'audit')
                          ->where('role', '!=', 'admin')
                          ->orderBy('name')
                          ->get();
        }

        return view('violations.create', compact('users'));
    }

    public function store(Request $request)
    {
        // ... kode store Anda ...
        if (!in_array(auth()->user()->role, ['admin', 'audit'])) {
            abort(403);
        }

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

    public function edit(Violation $violation)
    {
         if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang boleh mengedit data pelanggaran.');
        }

        return view('violations.edit', compact('violation'));
    }

    public function update(Request $request, Violation $violation)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:ringan,sedang,berat',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except(['photo']);

        if ($request->hasFile('photo')) {
            if ($violation->photo_path) {
                Storage::disk('public')->delete($violation->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('violation-photos', 'public');
        }

        $violation->update($data);

        return redirect()->route('violations.index')->with('success', 'Data pelanggaran diperbarui.');
    }

    public function destroy(Violation $violation)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang boleh menghapus data pelanggaran.');
        }

        if ($violation->photo_path) {
            Storage::disk('public')->delete($violation->photo_path);
        }

        $violation->delete();

        return redirect()->route('violations.index')->with('success', 'Data pelanggaran dihapus.');
    }
}