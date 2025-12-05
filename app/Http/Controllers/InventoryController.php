<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    // ... method index tetap sama ...
    public function index(Request $request)
    {
        $query = Inventory::with('user'); 

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
     * Form Create
     * Logic: 
     * - Admin: Load All Users
     * - Audit: Load Users in their Branches
     * - Others: No need to load users (Self Only)
     */
    public function create()
    {
        $currentUser = Auth::user();
        $users = collect(); // Default kosong

        if ($currentUser->role == 'admin') {
            // Admin melihat semua user aktif
            $users = User::where('is_active', 1)
                        ->orderBy('name')
                        ->get();

        } elseif ($currentUser->role == 'audit') {
            // Audit melihat user yang ada di cabang yang dia pegang (Multi Branch)
            // Mengambil ID cabang dari relasi branches()
            $branchIds = $currentUser->branches()->pluck('branches.id'); // Pastikan relasi branches ada di model User
            
            $users = User::where('is_active', 1)
                        ->whereIn('branch_id', $branchIds) // Filter user berdasarkan cabang audit
                        ->orderBy('name')
                        ->get();
        }
        
        // Untuk role lain (User Biasa, Leader, Security), $users tetap kosong 
        // karena di view akan otomatis terkunci ke diri sendiri.

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

        // Validasi User ID hanya untuk Admin/Audit
        if (in_array($user->role, ['admin', 'audit'])) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'document']);

            // === LOGIKA PENENTUAN PEMILIK ===
            if (in_array($user->role, ['admin', 'audit'])) {
                // Admin/Audit mengambil dari input dropdown
                $data['user_id'] = $request->user_id;
            } else {
                // Role lain dipaksa menjadi milik sendiri (Server-side enforcement)
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

    // ... method show, edit, update, destroy tetap sama ...
    public function show($id) {
        $inventory = Inventory::with('user')->findOrFail($id);
        return view('inventory.show', compact('inventory'));
    }

    public function edit($id) {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) abort(403);
        $inventory = Inventory::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'users'));
    }

    public function update(Request $request, $id) {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) abort(403);
        $inventory = Inventory::findOrFail($id);
        // ... validasi & update logic ...
        // (Gunakan kode update dari jawaban sebelumnya)
        return redirect()->route('inventory.index')->with('success', 'Update berhasil');
    }

    public function destroy(Request $request, $id) {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) abort(403);
        // ... delete logic ...
        return redirect()->route('inventory.index')->with('success', 'Hapus berhasil');
    }
}