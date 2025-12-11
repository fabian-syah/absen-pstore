<?php

namespace App\Http\Controllers;

use App\Models\EmploymentHistory;
use App\Models\Branch;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmploymentHistoryController extends Controller
{
    /**
     * INDEX (HALAMAN TIMELINE)
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $targetUser = null;
        $selectableUsers = collect([]);

        // --- 1. LIST USER (Untuk Dropdown Filter) ---
        if ($currentUser->role === 'admin') {
            $selectableUsers = User::with('branch')->orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            $selectableUsers = User::with('branch')->whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } elseif ($currentUser->role === 'leader') {
            $selectableUsers = User::with('branch')->where('branch_id', $currentUser->branch_id)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            // Security / User Biasa: Hanya Diri Sendiri
            $selectableUsers = User::with('branch')->where('id', $currentUser->id)->get();
        }

        // --- 2. TARGET USER (Siapa yang ditampilkan timeline-nya) ---
        if ($request->has('user_id')) {
            $requestedId = $request->user_id;

            // Validasi Hak Akses Melihat Orang Lain
            if ($requestedId != $currentUser->id) {
                if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
                    abort(403, 'Anda hanya boleh melihat data diri sendiri.');
                }

                $targetCheck = User::find($requestedId);
                
                // Validasi Audit
                if ($currentUser->role === 'audit') {
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if ($targetCheck && !in_array($targetCheck->branch_id, $allowedBranches)) abort(403);
                }
                // Validasi Leader
                if ($currentUser->role === 'leader') {
                    if ($targetCheck && $targetCheck->branch_id != $currentUser->branch_id) abort(403);
                }
            }
            $targetUser = User::find($requestedId);
        } else {
            // Default: Tampilkan Diri Sendiri jika belum pilih user
            $targetUser = $currentUser;
        }

        // --- 3. HAK AKSES TOMBOL (LOGIKA BARU) ---
        $isOwner = ($targetUser->id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);
        $isModeEdit = ($request->get('mode') === 'edit');

        // A. CREATE (TAMBAH RIWAYAT)
        // - User Biasa/Security: BISA tambah untuk diri sendiri.
        // - Management: BISA tambah (baik diri sendiri/orang lain) jika Mode Edit aktif ATAU dari sidebar (untuk diri sendiri/orang lain).
        // (Sesuai request sebelumnya: "buat admin leader dan audit dari menu sidebar itu bisa create")
        $canCreate = true; // Default true, nanti dibatasi di method create() jika perlu. Di view biar muncul terus.

        // B. EDIT (TOMBOL PENSIL)
        // - User Biasa/Security: BISA edit diri sendiri langsung dari sidebar.
        // - Management: HANYA BISA edit jika ada ?mode=edit (akses dari profil). Sidebar = View Only.
        if ($isRegular && $isOwner) {
            $canEdit = true;
        } elseif ($isManagement && $isModeEdit) {
            $canEdit = true;
        } else {
            $canEdit = false;
        }

        // C. DELETE (TOMBOL HAPUS)
        // - "bisa dihapus sekarang tambahkan menu hapus... hapus bisa lewat sidebar bisa semua role"
        // Jadi tombol hapus selalu muncul asalkan user punya akses ke data tersebut.
        $canDelete = true; 

        // --- 4. AMBIL DATA HISTORI ---
        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'creator', 'editor']) 
                ->orderBy('event_date', 'desc')
                ->get();
        }

        return view('employment_history.index', compact('histories', 'selectableUsers', 'targetUser', 'canEdit', 'canCreate', 'canDelete'));
    }

    /**
     * CREATE FORM
     */
    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $targetId = $request->get('user_id', $currentUser->id);
        $targetUser = User::findOrFail($targetId);

        // Validasi Akses
        // User Biasa/Security hanya boleh create untuk diri sendiri
        if (in_array($currentUser->role, ['user_biasa', 'security']) && $targetId != $currentUser->id) {
             abort(403, 'Akses ditolak.');
        }

        // Validasi Scope Wilayah (Management)
        if ($targetId != $currentUser->id) {
            if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
            }
            if ($currentUser->role === 'leader') {
                if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
            }
        }

        // Tampilkan SEMUA cabang agar bisa mutasi ke mana saja
        $branches = Branch::all(); 
        $divisions = Division::all();

        return view('employment_history.create', compact('targetUser', 'branches', 'divisions'));
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        // Keamanan: Jika User Biasa/Security, paksa user_id jadi diri sendiri
        if (in_array($currentUser->role, ['user_biasa', 'security'])) {
            $request->merge(['user_id' => $currentUser->id]);
        }

        $data = $request->only(['user_id', 'type', 'event_date', 'description', 'division_id', 'branch_id']);
        
        // Simpan Pembuat
        $data['created_by'] = $currentUser->id;

        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        $targetUser = User::find($request->user_id);
        $data['previous_branch_id'] = $targetUser->branch_id; 

        // Handle Audit Snapshot
        if ($targetUser->role == 'audit' && $request->has('audit_branch_ids')) {
            $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
            $newBranchNames = $newBranches->pluck('name')->toArray();

            $data['audit_branch_snapshot'] = [
                'from' => [], 
                'to'   => $newBranchNames
            ];
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        EmploymentHistory::create($data);

        // Redirect logika: 
        // Jika management, cek apakah tadi datang dari mode edit? Jika iya, pertahankan.
        // Tapi create biasanya bisa dari sidebar juga. Kita kembalikan user_id saja.
        // Jika ingin konsisten balik ke mode edit untuk management:
        $redirectParams = ['user_id' => $data['user_id']];
        // Note: Kita tidak paksa mode=edit di sini agar sesuai request "gabisa edit dari sidebar"
        // Kecuali jika memang awalnya ada mode=edit di referer, tapi itu kompleks.
        // Sesuai request: "admin leader audit ... gabisa edit ... dari sidebar".
        // Jadi redirect default tanpa mode edit aman.

        return redirect()->route('employment-history.index', $redirectParams)
            ->with('success', 'Riwayat berhasil ditambahkan.');
    }

    /**
     * EDIT FORM
     */
    public function edit($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        // Validasi Akses Halaman Edit
        // 1. User Biasa/Security: Boleh jika punya sendiri
        if ($isRegular && !$isOwner) abort(403, 'Akses ditolak.');
        
        // 2. Management: Boleh jika punya sendiri ATAU orang lain
        if ($isManagement) {
             // Validasi Wilayah Edit Orang Lain
             $targetUser = $history->user;
             if ($targetUser->id != $currentUser->id) {
                if ($currentUser->role === 'audit') {
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
                }
                if ($currentUser->role === 'leader') {
                    if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
                }
             }
        }
        
        // CATATAN: Controller tidak bisa membedakan akses dari sidebar vs user_show hanya dari URL route edit ini.
        // Pembatasan "tidak bisa edit dari sidebar" dilakukan dengan menyembunyikan tombol Edit di View Index.
        // Jika mereka menebak URL edit, controller ini tetap mengizinkan selama hak akses data valid.

        $branches = Branch::all();
        $divisions = Division::all();
        $targetUser = $history->user;

        return view('employment_history.edit', compact('history', 'branches', 'divisions', 'targetUser'));
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        // Validasi Akses (Sama seperti Edit)
        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        if ($isRegular && !$isOwner) abort(403);
        
        // Management Wilayah Check
        if ($isManagement && !$isOwner) {
             $targetUser = $history->user;
             if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
             }
             if ($currentUser->role === 'leader') {
                if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
             }
        }

        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['type', 'event_date', 'description', 'division_id', 'branch_id']);
        $data['updated_by'] = $currentUser->id;

        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($history->attachment) Storage::disk('public')->delete($history->attachment);
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        if ($history->user->role == 'audit' && $request->has('audit_branch_ids')) {
             $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
             $newBranchNames = $newBranches->pluck('name')->toArray();
             
             $currentSnapshot = $history->audit_branch_snapshot ?? [];
             $data['audit_branch_snapshot'] = [
                 'from' => $currentSnapshot['from'] ?? [], 
                 'to' => $newBranchNames
             ];
        }

        $history->update($data);

        // Redirect logika: Kembalikan mode edit jika Management
        $redirectParams = ['user_id' => $history->user_id];
        if ($isManagement) {
            $redirectParams['mode'] = 'edit';
        }

        return redirect()->route('employment-history.index', $redirectParams)
            ->with('success', 'Data riwayat diperbarui.');
    }

    /**
     * DESTROY (HAPUS)
     */
    public function destroy($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        // Validasi Akses Hapus
        // Semua role BISA hapus (sesuai request: "hapus bisa lewat sidebar bisa semua role")
        // Asal datanya valid milik mereka atau wilayah mereka
        
        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        if ($isRegular && !$isOwner) abort(403, 'Akses ditolak.');
        
        if ($isManagement && !$isOwner) {
             $targetUser = $history->user;
             if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
             }
             if ($currentUser->role === 'leader') {
                if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
             }
        }

        if ($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }

        $userId = $history->user_id;
        $history->delete();

        // Redirect tanpa mode edit agar balik ke tampilan sidebar default
        // Kecuali jika memang ingin tetap di mode edit (opsional, disini saya buat default)
        return redirect()->route('employment-history.index', ['user_id' => $userId])
            ->with('success', 'Riwayat berhasil dihapus.');
    }
}