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
            // Admin: Lihat Semua (tapi tetap via route index biasa)
        } elseif ($user->role == 'audit') {
            // Audit: Lihat aset di cabang yang dipegang + aset sendiri
            $branchIds = $user->branches()->pluck('branches.id');
            $query->where(function($q) use ($branchIds, $user) {
                $q->whereHas('user', function($sub) use ($branchIds) {
                    $sub->whereIn('branch_id', $branchIds);
                })->orWhere('user_id', $user->id);
            });
        } else {
            // Leader, Security, User Biasa: Hanya milik sendiri
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
     * [BARU] Tampilkan SEMUA DATA (KHUSUS ADMIN)
     * Menampilkan semua barang (baik yang dipakai maupun di gudang) tanpa filter user.
     */
    public function adminIndex(Request $request)
    {
        // 1. CEK HAK AKSES (Security Layer)
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Halaman ini khusus Admin.');
        }

        // 2. QUERY SEMUA DATA
        // Kita ambil relasi user dan branch untuk keperluan search & display
        $query = Inventory::with(['user', 'user.branch']); 

        // 3. FITUR PENCARIAN GLOBAL
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%')
                  ->orWhere('condition', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('branch', function($b) use ($search) {
                            $b->where('name', 'like', '%' . $search . '%');
                        });
                  });
            });
        }

        // 4. RETURN VIEW (Gunakan view yang sama)
        $inventories = $query->latest()->paginate(10)->withQueryString();
        $pageTitle = 'Master Data Inventaris (Admin View)';
        
        return view('inventory.index', compact('inventories', 'pageTitle'));
    }

    /**
     * Tampilkan List Barang AVAILABLE (Gudang)
     */
    public function available(Request $request)
    {
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
        $targetBranchId = $request->get('branch_id'); // Parameter dari tombol di menu cabang
        
        $users = collect(); 
        $fixedUser = null; // Jika tidak null, maka form user terkunci ke orang ini

        // 1. JIKA ADMIN -> Bebas pilih siapa saja
        if ($currentUser->role == 'admin') {
            if ($targetBranchId) {
                $users = User::where('branch_id', $targetBranchId)->where('is_active', 1)->orderBy('name')->get();
            } else {
                $users = User::where('is_active', 1)->orderBy('name')->get();
            }
        }
        
        // 2. JIKA AUDIT / LEADER MEMBUKA DARI MENU CABANG
        elseif (($currentUser->role == 'audit' || $currentUser->role == 'leader') && $targetBranchId) {
            
            // Validasi: Apakah Audit/Leader berhak atas cabang ini?
            $canAccess = false;
            if ($currentUser->role == 'leader' && $currentUser->branch_id == $targetBranchId) $canAccess = true;
            if ($currentUser->role == 'audit' && in_array($targetBranchId, $currentUser->branches->pluck('id')->toArray())) $canAccess = true;

            if ($canAccess) {
                // AMBIL SEMUA USER DI CABANG TERSEBUT
                $users = User::where('branch_id', $targetBranchId)->where('is_active', 1)->orderBy('name')->get();
                // SET FIXED USER JADI NULL AGAR BISA PILIH
                $fixedUser = null; 
            } else {
                abort(403, 'Anda tidak memiliki akses untuk menambah aset di cabang ini.');
            }
        }

        // 3. JIKA DARI MENU UTAMA (Audit/Leader/Security/User Biasa) -> Hanya Diri Sendiri
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

        // Validasi User ID
        // Jika Admin, atau (Audit/Leader dengan branch_id target) -> User ID Wajib dipilih
        if ($user->role == 'admin' || ($request->has('target_branch_id') && in_array($user->role, ['audit', 'leader']))) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $data = $request->except(['item_photo', 'user_item_photo', 'document', 'target_branch_id']);

            // LOGIC USER ID
            if ($user->role == 'admin') {
                $data['user_id'] = $request->user_id;
            } 
            // LOGIC BARU: Audit/Leader Input untuk user lain di cabang
            elseif ($request->has('target_branch_id') && in_array($user->role, ['audit', 'leader'])) {
                // Pastikan user yang dipilih benar-benar ada di cabang target (Security Layer)
                $targetUser = User::find($request->user_id);
                if($targetUser->branch_id != $request->target_branch_id) {
                      return back()->with('error', 'User tidak valid untuk cabang ini.');
                }
                $data['user_id'] = $request->user_id;
            }
            else {
                // Default: Barang milik diri sendiri
                $data['user_id'] = $user->id;
            }

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

            // Redirect balik sesuai asal
            if ($request->has('target_branch_id')) {
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

        // 1. Barang Gudang -> Semua boleh lihat
        if ($inventory->user_id === null) {
             return view('inventory.show', compact('inventory'));
        }

        // 2. Barang Milik Sendiri -> Boleh
        if ($inventory->user_id == $user->id) {
            return view('inventory.show', compact('inventory'));
        }

        // 3. Admin -> Boleh Semua
        if ($user->role == 'admin') {
            return view('inventory.show', compact('inventory'));
        }

        // 4. Audit & Leader -> Cek Cabang
        if (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranches = [];
            if ($user->role == 'audit') {
                $allowedBranches = $user->branches->pluck('id')->toArray();
            } else { // Leader
                $allowedBranches = [$user->branch_id];
            }

            if (in_array($inventory->user->branch_id, $allowedBranches)) {
                return view('inventory.show', compact('inventory'));
            }
        }

        abort(403, 'Anda tidak berhak melihat data ini.');
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