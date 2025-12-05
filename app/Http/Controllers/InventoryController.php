<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /**
     * Tampilkan List Barang
     */
    public function index(Request $request)
    {
        $query = Inventory::with('user'); 

        // Logika Pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Jika User Biasa/Leader/Security, hanya lihat barang sendiri? 
        // (Opsional: Jika ingin semua bisa lihat semua, biarkan kode ini. 
        // Jika hanya ingin lihat punya sendiri, uncomment baris bawah)
        // if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
        //     $query->where('user_id', Auth::id());
        // }

        $inventories = $query->latest()->paginate(10)->withQueryString();
        return view('inventory.index', compact('inventories'));
    }

    /**
     * Form Create
     * Diakses oleh: Admin, Audit, Leader, Security, User Biasa
     */
    public function create()
    {
        // Kita tetap kirim data users untuk dropdown (hanya dipakai Admin/Audit di view)
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.create', compact('users'));
    }

    /**
     * Simpan Data
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi Dasar
        $rules = [
            'item_name'     => 'required|string|max:255',
            'category'      => 'required|string',
            'serial_number' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'condition'     => 'required|string',
            'description'   => 'nullable|string|max:1000',
            'item_photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'document'      => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];

        // Jika Admin/Audit, Wajib pilih user lain. Jika User Biasa, tidak perlu validasi user_id (otomatis)
        if ($user->role == 'admin' || $user->role == 'audit') {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'document']);

            // === LOGIKA PENENTUAN PEMILIK ===
            if ($user->role == 'admin' || $user->role == 'audit') {
                // Admin bisa set pemilik barang ke orang lain
                $data['user_id'] = $request->user_id;
            } else {
                // User biasa/Leader/Security otomatis barang milik sendiri
                $data['user_id'] = $user->id;
            }

            // Upload Foto
            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

            // Upload Dokumen
            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            }

            Inventory::create($data);

            return redirect()->route('inventory.index')->with('success', 'Data inventaris berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Show Detail
     */
    public function show($id)
    {
        $inventory = Inventory::with('user')->findOrFail($id);
        return view('inventory.show', compact('inventory'));
    }

    /**
     * Edit (Hanya Admin & Audit)
     */
    public function edit($id)
    {
        // Cek Role di Middleware atau disini
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Anda tidak memiliki akses edit.');
        }

        $inventory = Inventory::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'users'));
    }

    /**
     * Update (Hanya Admin & Audit)
     */
    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403);
        }

        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'item_name' => 'required|string|max:255',
            'user_id'   => 'required',
            'category'  => 'required',
            'condition' => 'required',
            'item_photo'=> 'nullable|image|max:5120',
            'document'  => 'nullable|file|max:10240',
        ]);

        $data = $request->except(['item_photo', 'document']);

        if ($request->hasFile('item_photo')) {
            if ($inventory->item_photo_path && Storage::disk('public')->exists($inventory->item_photo_path)) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
        }

        if ($request->hasFile('document')) {
            if ($inventory->document_path && Storage::disk('public')->exists($inventory->document_path)) {
                Storage::disk('public')->delete($inventory->document_path);
            }
            $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
        }

        $inventory->update($data);

        return redirect()->route('inventory.index')->with('success', 'Data inventaris berhasil diperbarui.');
    }

    /**
     * Hapus (Hanya Admin & Audit)
     */
    public function destroy(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak');
        }

        $inventory = Inventory::findOrFail($id);

        try {
            if ($inventory->item_photo_path && Storage::disk('public')->exists($inventory->item_photo_path)) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            if ($inventory->document_path && Storage::disk('public')->exists($inventory->document_path)) {
                Storage::disk('public')->delete($inventory->document_path);
            }

            $inventory->delete();

            return redirect()->route('inventory.index')->with('success', 'Inventaris berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}