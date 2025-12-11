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
     * INDEX
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $targetUser = null;
        $selectableUsers = collect([]);

        // --- 1. LIST USER ---
        if ($currentUser->role === 'admin') {
            $selectableUsers = User::orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            $selectableUsers = User::whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } elseif ($currentUser->role === 'leader') {
            $selectableUsers = User::where('branch_id', $currentUser->branch_id)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            $selectableUsers = User::where('id', $currentUser->id)->get();
        }

        // --- 2. TARGET USER ---
        if ($request->has('user_id')) {
            $requestedId = $request->user_id;
            
            if ($requestedId != $currentUser->id) {
                if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
                    abort(403, 'Anda hanya boleh melihat data diri sendiri.');
                }
                $targetCheck = User::find($requestedId);
                
                if ($currentUser->role === 'audit') {
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if ($targetCheck && !in_array($targetCheck->branch_id, $allowedBranches)) abort(403);
                }
                if ($currentUser->role === 'leader') {
                    if ($targetCheck && $targetCheck->branch_id != $currentUser->branch_id) abort(403);
                }
            }
            $targetUser = User::find($requestedId);
        } else {
            $targetUser = $currentUser;
        }

        // --- 3. HAK AKSES EDIT/DELETE ---
        $isOwner = ($targetUser->id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);
        $isModeEdit = ($request->get('mode') === 'edit');

        // Can Create: Owner atau Management dalam mode edit
        $canCreate = $isOwner || ($isManagement && $isModeEdit);
        
        // Can Edit/Delete: User Biasa langsung bisa, Management perlu mode edit
        $canEdit = ($isRegular && $isOwner) || ($isManagement && $isModeEdit);

        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'creator', 'editor']) 
                ->orderBy('event_date', 'desc')
                ->get();
        }

        return view('employment_history.index', compact('histories', 'selectableUsers', 'targetUser', 'canEdit', 'canCreate'));
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
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader']) && $targetId != $currentUser->id) {
             abort(403, 'Akses ditolak.');
        }

        // Scope Wilayah
        if ($targetId != $currentUser->id) {
            if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
            }
            if ($currentUser->role === 'leader') {
                if ($targetUser->branch_id != $currentUser->branch_id) abort(403);
            }
        }

        // --- FILTER CABANG DI FORM ---
        $branches = $this->getBranchOptions($currentUser);
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
            // Kita tidak simpan 'from', langsung 'to' saja karena history baru
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

        $redirectParams = ['user_id' => $data['user_id']];
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            $redirectParams['mode'] = 'edit';
        }

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

        if ($isRegular && !$isOwner) abort(403, 'Akses ditolak.');
        if (!$isManagement && !$isRegular) abort(403);

        // Validasi Wilayah Edit Orang Lain
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

        // --- FILTER CABANG DI FORM ---
        $branches = $this->getBranchOptions($currentUser);
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

        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        if ($isRegular && !$isOwner) abort(403);
        if (!$isManagement && !$isRegular) abort(403);

        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['type', 'event_date', 'description', 'division_id', 'branch_id']);
        
        // Simpan Pengedit
        $data['updated_by'] = $currentUser->id;

        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($history->attachment) Storage::disk('public')->delete($history->attachment);
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // Handle Audit Snapshot
        if ($history->user->role == 'audit' && $request->has('audit_branch_ids')) {
             $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
             $newBranchNames = $newBranches->pluck('name')->toArray();
             
             // Update hanya 'to'
             $currentSnapshot = $history->audit_branch_snapshot ?? [];
             $data['audit_branch_snapshot'] = [
                 'from' => $currentSnapshot['from'] ?? [], 
                 'to' => $newBranchNames
             ];
        }

        $history->update($data);

        $redirectParams = ['user_id' => $history->user_id];
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
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

        // Validasi Akses Hapus (Sama seperti Edit)
        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        if ($isRegular && !$isOwner) abort(403, 'Akses ditolak.');
        if (!$isManagement && !$isRegular) abort(403);

        // Hapus file
        if ($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }

        $userId = $history->user_id;
        $history->delete();

        $redirectParams = ['user_id' => $userId];
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            $redirectParams['mode'] = 'edit';
        }

        return redirect()->route('employment-history.index', $redirectParams)
            ->with('success', 'Riwayat berhasil dihapus.');
    }

    /**
     * Helper: Filter Cabang di Form berdasarkan Role
     */
    private function getBranchOptions($user)
    {
        if ($user->role === 'admin') {
            return Branch::all();
        } 
        if ($user->role === 'audit') {
            return $user->branches; // Hanya wilayah auditnya
        } 
        // Leader, User Biasa, Security -> Hanya cabangnya sendiri
        return Branch::where('id', $user->branch_id)->get();
    }
}