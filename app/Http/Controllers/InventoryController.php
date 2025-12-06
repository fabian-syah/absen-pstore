<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\User;
use App\Models\Branch;
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
        $query = Inventory::with('user')->whereNotNull('user_id');

        // === FILTER HAK AKSES ===
        if ($user->role == 'admin') {
            // 1. ADMIN: Bisa melihat SEMUA barang
            // Tidak ada filter user_id
        } else {
            // 2. AUDIT, LEADER, SECURITY, USER BIASA:
            // Hanya bisa melihat barang milik DIRI SENDIRI
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
        $pageTitle = 'Daftar Inventaris Saya / Aktif';
        
        return view('inventory.index', compact('inventories', 'pageTitle'));
    }

    /**
     * Tampilkan List Barang AVAILABLE (Gudang)
     */
    public function available(Request $request)
    {
        // Barang gudang (available) biasanya boleh dilihat semua role untuk referensi
        // atau jika ingin dibatasi hanya admin, tambahkan logic if admin di sini.
        // Default: Semua role bisa lihat barang available.
        
        $query = Inventory::whereNull('user_id'); 

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
        
        return view('inventory.index', compact('inventories', 'pageTitle'));
    }

    /**
     * Form Create
     */
    public function create(Request $request)
    {
        $currentUser = Auth::user();
        $targetBranchId = $request->get('branch_id'); 
        
        $users = collect(); 
        $fixedUser = null; 

        // 1. JIKA ADMIN -> Bebas pilih siapa saja (Bisa filter by branch jika ada param)
        if ($currentUser->role == 'admin') {
            if ($targetBranchId) {
                $users = User::where('branch_id', $targetBranchId)->where('is_active', 1)->orderBy('name')->get();
            } else {
                $users = User::where('is_active', 1)->orderBy('name')->get();
            }
        }
        
        // 2. SELAIN ADMIN (Audit, Leader, Security, User Biasa)
        // Hanya bisa input untuk DIRI SENDIRI
        else {
            $fixedUser = $currentUser;
        }
        
        return view('inventory.create', compact('users', 'fixedUser', 'targetBranchId'));
    }

    /**
     * Simpan Data
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'item_name'       => 'required|string|max:255',
            'category'        => 'required|string',
            'serial_number'   => 'nullable|string|max:100',
            'received_date'   => 'required|date',
            'condition'       => 'required|string',
            'description'     => 'nullable|string|max:1000',
            'item_photo'      => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', 
            'user_item_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'document'        => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];

        // Validasi User ID: Hanya wajib jika Admin yang input (karena dropdown)
        if ($user->role == 'admin') {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'user_item_photo', 'document', 'target_branch_id']);

            // LOGIC USER ID
            if ($user->role == 'admin') {
                // Admin bisa set user lain
                $data['user_id'] = $request->user_id;
            } else {
                // Role lain (Audit/Leader/Security/User) otomatis ke diri sendiri
                $data['user_id'] = $user->id;
            }

            // SIMPAN FOTO & DOKUMEN
            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

            if ($request->hasFile('user_item_photo')) {
                $data['user_item_photo_path'] = $request->file('user_item_photo')->store('inventory_user_photos', 'public');
            }

            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            }

            Inventory::create($data);

            // Redirect logic
            if ($request->has('target_branch_id') && $user->role == 'admin') {
                return redirect()->route('inventory.branch.detail', $request->target_branch_id)
                    ->with('success', 'Inventaris cabang berhasil ditambahkan.');
            }

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
        $user = Auth::user();

        // 1. Barang Gudang (Available) -> Semua boleh lihat detail
        if ($inventory->user_id === null) {
             return view('inventory.show', compact('inventory'));
        }

        // 2. Admin -> Boleh lihat punya siapa saja
        if ($user->role == 'admin') {
            return view('inventory.show', compact('inventory'));
        }

        // 3. Pemilik Barang -> Boleh lihat punya sendiri
        if ($inventory->user_id == $user->id) {
            return view('inventory.show', compact('inventory'));
        }

        // 4. Role Lain (Audit, Leader, dll) -> DITOLAK jika bukan punya sendiri
        abort(403, 'Anda tidak berhak melihat data inventaris ini.');
    }

    /**
     * Edit (HANYA ADMIN)
     */
    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $inventory = Inventory::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'users'));
    }

    /**
     * Update (HANYA ADMIN)
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'item_name'       => 'required|string|max:255',
            'user_id'         => 'nullable',
            'category'        => 'required',
            'condition'       => 'required',
            'item_photo'      => 'nullable|image|max:10240',
            'user_item_photo' => 'nullable|image|max:10240',
            'document'        => 'nullable|file|max:10240',
        ]);

        $data = $request->except(['item_photo', 'user_item_photo', 'document']);

        if ($request->hasFile('item_photo')) {
            if ($inventory->item_photo_path && Storage::disk('public')->exists($inventory->item_photo_path)) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
        }

        if ($request->hasFile('user_item_photo')) {
            if ($inventory->user_item_photo_path && Storage::disk('public')->exists($inventory->user_item_photo_path)) {
                Storage::disk('public')->delete($inventory->user_item_photo_path);
            }
            $data['user_item_photo_path'] = $request->file('user_item_photo')->store('inventory_user_photos', 'public');
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
        if (Auth::user()->role !== 'admin') abort(403);

        $inventory = Inventory::findOrFail($id);

        try {
            if ($inventory->item_photo_path && Storage::disk('public')->exists($inventory->item_photo_path)) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            if ($inventory->user_item_photo_path && Storage::disk('public')->exists($inventory->user_item_photo_path)) {
                Storage::disk('public')->delete($inventory->user_item_photo_path);
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