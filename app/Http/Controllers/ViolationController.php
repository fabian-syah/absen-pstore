<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ViolationController extends Controller
{
    /**
     * Menampilkan daftar pelanggaran yang MASIH BERLAKU (Aktif).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Violation::with(['user', 'reporter'])->where('expires_at', '>', now());
        $query = $this->applyAccessFilter($query, $user);
        $violations = $query->orderBy('created_at', 'desc')->get();
        return view('violations.index', compact('violations'));
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $query = Violation::with(['user', 'reporter', 'resolver'])->where('expires_at', '<=', now()); // Tambah resolver
        $query = $this->applyAccessFilter($query, $user);
        $violations = $query->orderBy('expires_at', 'desc')->get();
        return view('violations.history', compact('violations'));
    }

    private function applyAccessFilter($query, $user)
    {
        if ($user->role === 'admin') {
            // No filter
        } elseif ($user->role === 'audit') {
            $branchIds = $user->branches->pluck('id');
            $query->where(function ($q) use ($branchIds, $user) {
                $q->whereHas('user', function ($subQ) use ($branchIds) {
                    $subQ->whereIn('branch_id', $branchIds);
                })->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('user_id', $user->id);
        }
        return $query;
    }
    public function create()
    {
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

        // LOGIKA MASA BERLAKU OTOMATIS
        $createdAt = now();
        if ($request->category == 'berat') {
            $data['expires_at'] = $createdAt->copy()->addYear(); // 1 Tahun
        } elseif ($request->category == 'sedang') {
            $data['expires_at'] = $createdAt->copy()->addMonths(6); // 6 Bulan
        } else {
            $data['expires_at'] = $createdAt->copy()->addMonth(); // 1 Bulan
        }

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

    /**
     * [BARU] Logic untuk menyelesaikan pelanggaran secara manual.
     * Menggantikan fungsi Destroy/Delete.
     */
    public function resolve(Request $request, Violation $violation)
    {
        if (!in_array(auth()->user()->role, ['admin', 'audit'])) {
            abort(403, 'Anda tidak berhak menyelesaikan pelanggaran ini.');
        }

        $request->validate([
            'resolution_notes' => 'required|string|max:255', // Wajib isi alasan
        ]);

        $violation->update([
            'expires_at' => now(), // Set expired SEKARANG (langsung masuk history)
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
            'resolution_notes' => $request->resolution_notes,
        ]);

        return redirect()->route('violations.index')->with('success', 'Pelanggaran diselesaikan & dipindah ke history.');
    }
    
    // // Fitur Hapus Permanen (Opsional, buat Admin Master saja jika perlu)
    // public function destroy(Violation $violation)
    // {
    //     if (auth()->user()->role !== 'admin') abort(403);
    //     if ($violation->photo_path) Storage::disk('public')->delete($violation->photo_path);
    //     $violation->delete();
    //     return back()->with('success', 'Data dihapus permanen.');
    // }
}
