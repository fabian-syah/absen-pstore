<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Menampilkan daftar divisi dengan jumlah karyawannya.
     */
    public function index(Request $request)
    {
        // Gunakan 'withCount' untuk menghitung jumlah user di setiap divisi secara otomatis.
        // Hasilnya nanti bisa diakses via property: $division->users_count
        $query = Division::withCount('users');

        // LOGIKA SEARCH
        if ($request->has('search') && $request->search != null) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // Gunakan paginate agar halaman rapi jika data sudah ratusan
        $divisions = $query->latest()->paginate(10)->withQueryString();

        return view('division.division_index', compact('divisions'));
    }

    /**
     * Menampilkan form untuk membuat divisi baru.
     */
    public function create()
    {
        return view('division.division_create');
    }

    /**
     * Menyimpan divisi baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:divisions',
        ]);

        Division::create([
            'name' => $request->name,
        ]);

        return redirect()->route('divisions.index')
            ->with('success', 'Divisi baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu divisi (List Anggota).
     */
    public function show(Division $division)
    {
        // Load relasi users beserta data branch-nya
        $members = $division->users()->with('branch')->latest()->paginate(10);

        return view('division.division_show', compact('division', 'members'));
    }

    /**
     * Menampilkan form untuk mengedit divisi.
     */
    public function edit(Division $division)
    {
        return view('division.division_edit', compact('division'));
    }

    /**
     * Update data divisi di database.
     */
    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $division->id,
        ]);

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
            // Cek manual jika ingin memastikan lebih aman (opsional, karena constraint database biasanya sudah handle)
            if ($division->users()->count() > 0) {
                return redirect()->route('divisions.index')
                ->with('error', 'Gagal hapus: Masih ada ' . $division->users()->count() . ' karyawan di divisi ini.');
            }

            $division->delete();

            return redirect()->route('divisions.index')
                ->with('success', 'Divisi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('divisions.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}