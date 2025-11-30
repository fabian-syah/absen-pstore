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
     * Tampilkan List Barang (Sidebar Menu)
     * - Semua Role bisa melihat.
     * - Admin/Audit punya tombol aksi lebih banyak di View.
     */
    public function index(Request $request)
    {
        $query = Inventory::with('user'); // Load data user pemilik barang

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

        $inventories = $query->latest()->paginate(10)->withQueryString();
        return view('inventory.index', compact('inventories'));
    }

    /**
     * Form Create (Khusus Admin/Audit lewat Sidebar)
     */
    public function create()
    {
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.create', compact('users'));
    }

    /**
     * Store (Simpan Data)
     * Menangani request dari 2 sumber:
     * 1. Halaman Profile (User input barang sendiri) -> Redirect ke Profile
     * 2. Halaman Admin (Sidebar input barang orang lain) -> Redirect ke Index Inventory
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Cek hidden input 'is_profile_action' untuk menentukan sumber request
        $isFromProfile = $request->has('is_profile_action');

        // Validasi
        $rules = [
            'item_name'     => 'required|string|max:255',
            'category'      => 'required|string',
            'serial_number' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'condition'     => 'required|string',
            'description'   => 'nullable|string|max:1000',
            'item_photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'document'      => 'nullable|file|mimes:pdf,doc,docx|max:10240',    // Max 10MB
        ];

        // Jika dari Admin Sidebar, WAJIB pilih user_id target
        if (!$isFromProfile) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'document', 'is_profile_action']);

            // LOGIKA PENENTUAN PEMILIK BARANG
            if ($isFromProfile) {
                $data['user_id'] = $user->id; // Otomatis diri sendiri
            } else {
                $data['user_id'] = $request->user_id; // Dari dropdown admin
            }

            // UPLOAD FOTO
            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

            // UPLOAD DOKUMEN
            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            }

            Inventory::create($data);

            // REDIRECT SESUAI SUMBER REQUEST
            if ($isFromProfile) {
                return redirect()->route('profile.edit')->with('success', 'Inventaris berhasil ditambahkan ke profil Anda.');
            } else {
                return redirect()->route('inventory.index')->with('success', 'Data inventaris berhasil ditambahkan.');
            }

        } catch (\Exception $e) {
            $route = $isFromProfile ? 'profile.edit' : 'inventory.index';
            return redirect()->route($route)->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Show Detail Barang
     */
    public function show($id)
    {
        $inventory = Inventory::with('user')->findOrFail($id);
        return view('inventory.show', compact('inventory'));
    }

    /**
     * Edit Form (Khusus Admin Sidebar)
     */
    public function edit($id)
    {
        $inventory = Inventory::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'users'));
    }

    /**
     * Update (Khusus Admin Sidebar)
     */
    public function update(Request $request, $id)
    {
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

        // Update Foto
        if ($request->hasFile('item_photo')) {
            if ($inventory->item_photo_path && Storage::disk('public')->exists($inventory->item_photo_path)) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
        }

        // Update Dokumen
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
     * Destroy (Hapus Data)
     * - User Biasa: Hanya boleh hapus barang miliknya.
     * - Admin/Audit: Boleh hapus barang siapa saja.
     */
    public function destroy(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        $user = Auth::user();

        // CEK PERMISSIONS
        if ($user->role !== 'admin' && $user->role !== 'audit' && $inventory->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak menghapus data ini.');
        }

        try {
            // Hapus file fisik
            if ($inventory->item_photo_path && Storage::disk('public')->exists($inventory->item_photo_path)) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            if ($inventory->document_path && Storage::disk('public')->exists($inventory->document_path)) {
                Storage::disk('public')->delete($inventory->document_path);
            }

            $inventory->delete();

            return redirect()->back()->with('success', 'Inventaris berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}