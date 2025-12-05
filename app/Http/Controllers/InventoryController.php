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
     * Tampilkan List Barang AKTIF (Sedang Dipakai)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Inventory::with('user')
                    ->whereNotNull('user_id'); // HANYA YANG AKTIF (PUNYA PEMILIK)

        // === FILTER HAK AKSES ===
        if ($user->role == 'admin') {
            // Admin: Lihat Semua (Tidak ada filter tambahan)
        } 
        elseif ($user->role == 'audit') {
            // Audit: Hanya user di cabang yang dipegang
            $branchIds = $user->branches()->pluck('branches.id');
            $query->whereHas('user', function($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
            });
        } 
        else {
            // User Biasa/Leader/Security: Hanya milik sendiri
            $query->where('user_id', $user->id);
        }

        // === SEARCH ===
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

        $inventories = $query->latest()->paginate(10)->withQueryString();
        
        // Kirim variabel title agar view dinamis
        $pageTitle = 'Daftar Inventaris Aktif (Dipinjamkan)';
        
        return view('inventory.index', compact('inventories', 'pageTitle'));
    }

    /**
     * Tampilkan List Barang AVAILABLE (Sudah Dikembalikan / Gudang)
     * Menu Baru: "Gudang / Available"
     */
    public function available(Request $request)
    {
        $user = Auth::user();
        
        // Admin & Audit bisa lihat semua barang available (di gudang)
        // User biasa tidak perlu lihat gudang (biasanya), tapi kalau mau, bisa dibuka.
        // Disini saya buat Admin & Audit saja yang bisa lihat stok gudang.
        if (!in_array($user->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak');
        }

        $query = Inventory::whereNull('user_id'); // HANYA YANG KOSONG (AVAILABLE)

        // Filter Audit: Mungkin audit hanya mau lihat aset gudang cabang tertentu?
        // Karena table inventory tidak ada kolom branch_id, maka aset available dianggap GLOBAL atau milik PUSAT.
        // Jadi Audit tetap bisa lihat semua yang available untuk diaudit fisiknya.

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $inventories = $query->latest()->paginate(10)->withQueryString();
        
        $pageTitle = 'Daftar Inventaris Available (Gudang)';
        
        // Kita reuse view index, tapi nanti di view kita cek jika user_id null
        return view('inventory.index', compact('inventories', 'pageTitle'));
    }

    /**
     * Form Create
     */
    public function create()
    {
        $currentUser = Auth::user();
        $users = collect(); 

        if ($currentUser->role == 'admin') {
            $users = User::where('is_active', 1)->orderBy('name')->get();
        } elseif ($currentUser->role == 'audit') {
            $branchIds = $currentUser->branches()->pluck('branches.id');
            $users = User::where('is_active', 1)->whereIn('branch_id', $branchIds)->orderBy('name')->get();
        }
        
        return view('inventory.create', compact('users'));
    }

    /**
     * Simpan Data
     */
    public function store(Request $request)
    {
        $user = Auth::user();

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

        // Validasi User ID
        if (in_array($user->role, ['admin', 'audit'])) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'document']);

            if (in_array($user->role, ['admin', 'audit'])) {
                $data['user_id'] = $request->user_id;
            } else {
                $data['user_id'] = $user->id;
            }

            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

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
        // Pastikan User Biasa tidak mengintip barang orang lain lewat URL
        $inventory = Inventory::with('user')->findOrFail($id);
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'audit']) && $inventory->user_id != $user->id) {
            abort(403, 'Anda tidak berhak melihat data ini.');
        }
        
        // Audit cek cabang
        if ($user->role == 'audit' && $inventory->user) {
             $branchIds = $user->branches()->pluck('branches.id')->toArray();
             if (!in_array($inventory->user->branch_id, $branchIds)) {
                 abort(403, 'Aset ini berada di luar wilayah audit Anda.');
             }
        }

        return view('inventory.show', compact('inventory'));
    }

    /**
     * Edit (HANYA ADMIN) - Audit TIDAK BISA EDIT/HAPUS (Sesuai Request)
     */
    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Admin yang bisa mengedit.');
        }

        $inventory = Inventory::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'users'));
    }

    /**
     * Update (HANYA ADMIN)
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'item_name' => 'required|string|max:255',
            'user_id'   => 'nullable', // Boleh null jika mau ditaruh gudang
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
     * Hapus (HANYA ADMIN)
     */
    public function destroy(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Admin yang bisa menghapus data.');
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