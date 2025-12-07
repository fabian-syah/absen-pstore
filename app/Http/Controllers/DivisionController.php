<?php

namespace App\Http\Controllers;

use App\Models\Division; // <-- PENTING: Panggil modelnya
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Menampilkan daftar semua divisi (Dengan Fitur Search).
     */
    public function index(Request $request)
    {
        $query = Division::query();

        // LOGIKA SEARCH
        if ($request->has('search') && $request->search != null) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $divisions = $query->latest()->get(); // Mengambil data terbaru (terfilter jika ada search)

        // Memanggil file view: resources/views/division/division_index.blade.php
        return view('division.division_index', compact('divisions'));
    }

    /**
     * Menampilkan form untuk membuat divisi baru.
     */
    public function create()
    {
        // Memanggil file view: resources/views/division/division_create.blade.php
        return view('division.division_create');
    }

    /**
     * Menyimpan divisi baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255|unique:divisions',
        ]);

        // Buat divisi baru
        Division::create([
            'name' => $request->name,
        ]);

        return redirect()->route('divisions.index')
            ->with('success', 'Divisi baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu divisi.
     * (Kita arahkan ke halaman edit)
     */
    public function show(Division $division)
    {
        // Load relasi users beserta data branch-nya untuk ditampilkan di tabel
        // Kita gunakan pagination untuk user supaya halaman tidak berat jika anggota ribuan
        $members = $division->users()->with('branch')->latest()->paginate(10);

        // Return ke view baru: division_show.blade.php
        return view('division.division_show', compact('division', 'members'));
    }

    /**
     * Menampilkan form untuk mengedit divisi.
     */
    public function edit(Division $division)
    {
        // Memanggil file view: resources/views/division/division_edit.blade.php
        return view('division.division_edit', compact('division'));
    }

    /**
     * Update data divisi di database.
     */
    public function update(Request $request, Division $division)
    {
        // Validasi input
        $request->validate([
            // Pastikan nama unik, KECUALI untuk ID dia sendiri
            'name' => 'required|string|max:255|unique:divisions,name,' . $division->id,
        ]);

        // Update divisi
        $division->update([
            'name' => $request->name,
        ]);

        return redirect()->route('divisions.index')
            ->with('success', 'Data divisi berhasil diperbarui.');
    }

    /**
     * Menghapus divisi dari database.
     */
    public function destroy(Division $division)
    {
        try {
            // Hapus divisi
            $division->delete();

            return redirect()->route('divisions.index')
                ->with('success', 'Divisi berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap error jika divisi tidak bisa dihapus (karena masih dipakai user)
            return redirect()->route('divisions.index')
                ->with('error', 'Gagal menghapus divisi. Pastikan tidak ada user yang terhubung ke divisi ini.');
        }
    }
}