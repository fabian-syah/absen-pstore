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
     * MENAMPILKAN HALAMAN UTAMA (INDEX / TIMELINE)
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $targetUser = null;
        $selectableUsers = collect([]);

        // --- 1. LOGIKA LIST USER (DROPDOWN) ---
        if ($currentUser->role === 'admin') {
            // Admin: Semua User
            $selectableUsers = User::orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            // Audit: User Cabang + Diri Sendiri
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            $selectableUsers = User::whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } elseif ($currentUser->role === 'leader') {
            // Leader: User di Cabang yang sama + Diri Sendiri
            $selectableUsers = User::where('branch_id', $currentUser->branch_id)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            // Security / User Biasa: Hanya Diri Sendiri
            $selectableUsers = User::where('id', $currentUser->id)->get();
        }

        // --- 2. LOGIKA TARGET USER (SIAPA YG DITAMPILKAN) ---
        if ($request->has('user_id')) {
            $requestedId = $request->user_id;

            // Validasi Hak Akses Melihat Orang Lain
            if ($requestedId != $currentUser->id) {
                // User biasa/Security tidak boleh lihat orang lain
                if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
                    abort(403, 'Anda hanya boleh melihat data diri sendiri.');
                }

                $targetCheck = User::find($requestedId);
                
                // Validasi Audit
                if ($currentUser->role === 'audit') {
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if ($targetCheck && !in_array($targetCheck->branch_id, $allowedBranches)) {
                        abort(403, 'User ini di luar wilayah audit Anda.');
                    }
                }
                
                // Validasi Leader
                if ($currentUser->role === 'leader') {
                    if ($targetCheck && $targetCheck->branch_id != $currentUser->branch_id) {
                        abort(403, 'Anda hanya boleh melihat karyawan di cabang Anda.');
                    }
                }
            }
            $targetUser = User::find($requestedId);
        } else {
            // Default: Tampilkan Diri Sendiri
            $targetUser = $currentUser;
        }

        // --- 3. LOGIKA HAK AKSES EDIT ---
        $canEdit = false;

        // A. Admin/Audit/Leader: Harus kirim parameter '?mode=edit' atau edit diri sendiri
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            if ($request->get('mode') === 'edit') {
                $canEdit = true;
            }
            // Opsional: Jika ingin Admin/Leader otomatis bisa edit diri sendiri tanpa parameter, uncomment ini:
            // if ($targetUser->id == $currentUser->id) $canEdit = true; 
        } 
        
        // B. [BARU] User Biasa / Security: Otomatis BISA edit jika melihat diri sendiri
        elseif (in_array($currentUser->role, ['user_biasa', 'security'])) {
            if ($targetUser->id == $currentUser->id) {
                $canEdit = true;
            }
        }

        // --- 4. AMBIL DATA HISTORI ---
        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'previousBranch']) 
                ->orderBy('event_date', 'desc')
                ->get();
        }

        return view('employment_history.index', compact('histories', 'selectableUsers', 'targetUser', 'canEdit'));
    }

    /**
     * MENAMPILKAN FORMULIR TAMBAH (CREATE)
     */
    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $targetId = $request->get('user_id', $currentUser->id);
        $targetUser = User::findOrFail($targetId);

        // --- VALIDASI AKSES CREATE ---
        // 1. Jika User Biasa/Security mencoba create untuk ORANG LAIN -> Tolak
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader']) && $targetId != $currentUser->id) {
             abort(403, 'Akses ditolak.');
        }

        // 2. Validasi Scope Wilayah (Khusus Leader & Audit)
        if ($targetId != $currentUser->id) {
            if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
            }
            if ($currentUser->role === 'leader') {
                if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
            }
        }

        $branches = Branch::all();
        $divisions = Division::all();

        return view('employment_history.create', compact('targetUser', 'branches', 'divisions'));
    }

    /**
     * SIMPAN DATA
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

        // Jika Pindah Cabang, kosongkan Divisi
        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        // Snapshot Previous Branch
        $targetUser = User::find($request->user_id);
        $data['previous_branch_id'] = $targetUser->branch_id; 

        // Handle Audit Multi Branch Snapshot
        if ($targetUser->role == 'audit' && $request->has('audit_branch_ids')) {
            $oldBranchNames = $targetUser->branches->pluck('name')->toArray();
            $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
            $newBranchNames = $newBranches->pluck('name')->toArray();

            $data['audit_branch_snapshot'] = [
                'from' => $oldBranchNames,
                'to'   => $newBranchNames
            ];
        }

        // Upload File
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        EmploymentHistory::create($data);

        // Redirect logika: Jika user biasa/security, tidak perlu ?mode=edit karena otomatis aktif di index
        $redirectParams = ['user_id' => $data['user_id']];
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            $redirectParams['mode'] = 'edit';
        }

        return redirect()->route('employment-history.index', $redirectParams)
            ->with('success', 'Riwayat berhasil dicatat (Timeline Only).');
    }

    /**
     * MENAMPILKAN FORM EDIT
     */
    public function edit($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        // Validasi Akses: Boleh edit jika Admin/Audit/Leader ATAU Milik Sendiri
        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);

        if (!$isManagement && !$isOwner) {
            abort(403, 'Akses ditolak.');
        }

        // Validasi Wilayah (Jika Manajemen mengedit orang lain)
        $targetUser = $history->user;
        if ($targetUser->id != $currentUser->id && $isManagement) {
            if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
            }
            if ($currentUser->role === 'leader') {
                if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
            }
        }

        $branches = Branch::all();
        $divisions = Division::all();
        $targetUser = $history->user;

        return view('employment_history.edit', compact('history', 'branches', 'divisions', 'targetUser'));
    }

    /**
     * UPDATE DATA
     */
    public function update(Request $request, $id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        // Validasi Akses (Sama seperti Edit)
        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);

        if (!$isManagement && !$isOwner) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['type', 'event_date', 'description', 'division_id', 'branch_id']);

        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($history->attachment) {
                Storage::disk('public')->delete($history->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // Handle Audit Snapshot Update
        if ($history->user->role == 'audit' && $request->has('audit_branch_ids')) {
             $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
             $newBranchNames = $newBranches->pluck('name')->toArray();
             
             $currentSnapshot = $history->audit_branch_snapshot ?? ['from' => [], 'to' => []];
             $data['audit_branch_snapshot'] = [
                 'from' => $currentSnapshot['from'] ?? [], 
                 'to' => $newBranchNames
             ];
        }

        $history->update($data);

        // Redirect logika
        $redirectParams = ['user_id' => $history->user_id];
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            $redirectParams['mode'] = 'edit';
        }

        return redirect()->route('employment-history.index', $redirectParams)
            ->with('success', 'Data riwayat berhasil diperbarui.');
    }
}