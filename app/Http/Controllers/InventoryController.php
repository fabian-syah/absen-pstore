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
     * Tampilkan List Barang (Semua Role Bisa Lihat)
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

        $inventories = $query->latest()->paginate(10)->withQueryString();
        return view('inventory.index', compact('inventories'));
    }

    /**
     * Form Create (Semua Role Bisa Akses)
     */
    public function create()
    {
        $currentUser = Auth::user();
        $users = collect(); // Default kosong

        // Admin: Load semua user
        if ($currentUser->role == 'admin') {
            $users = User::where('is_active', 1)->orderBy('name')->get();
        } 
        // Audit: Load user di cabangnya
        elseif ($currentUser->role == 'audit') {
            $branchIds = $currentUser->branches()->pluck('branches.id');
            $users = User::where('is_active', 1)->whereIn('branch_id', $branchIds)->orderBy('name')->get();
        }
        
        // Role lain (User Biasa, Leader, Security): $users tetap kosong (Input otomatis diri sendiri di view)

        return view('inventory.create', compact('users'));
    }

    /**
     * Simpan Data (Semua Role Bisa Input)
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

        // Validasi User ID hanya wajib jika penginput adalah Admin/Audit (karena pakai dropdown)
        if (in_array($user->role, ['admin', 'audit'])) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'document']);

            // === LOGIKA PENENTUAN PEMILIK ===
            if (in_array($user->role, ['admin', 'audit'])) {
                // Admin/Audit bisa set barang untuk orang lain
                $data['user_id'] = $request->user_id;
            } else {
                // User Biasa/Leader/Security OTOMATIS barang milik sendiri
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
     * Show Detail (Semua Role Bisa Lihat)
     */
    public function show($id)
    {
        $inventory = Inventory::with('user')->findOrFail($id);
        return view('inventory.show', compact('inventory'));
    }

    /**
     * Edit (HANYA ADMIN & AUDIT)
     */
    public function edit($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak: Hanya Admin yang bisa mengedit.');
        }

        $inventory = Inventory::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'users'));
    }

    /**
     * Update (HANYA ADMIN & AUDIT)
     */
    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak');
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
     * Hapus (HANYA ADMIN & AUDIT)
     * User biasa TIDAK BISA hapus, meskipun itu barang miliknya (untuk keamanan data).
     */
    public function destroy(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak: Hanya Admin yang bisa menghapus data.');
        }

        $inventory = Inventory::findOrFail($id);

        try {
            // Hapus file fisik
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