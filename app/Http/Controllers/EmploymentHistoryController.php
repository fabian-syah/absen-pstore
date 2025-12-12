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
            $selectableUsers = User::with('branch')->orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            $selectableUsers = User::with('branch')
                ->whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } elseif ($currentUser->role === 'leader') {
            $selectableUsers = User::with('branch')
                ->where('branch_id', $currentUser->branch_id)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            $selectableUsers = User::with('branch')->where('id', $currentUser->id)->get();
        }

        // --- 2. LOGIKA TARGET USER ---
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
            $targetUser = $currentUser;
        }

        // --- 3. HAK AKSES TOMBOL ---
        $isOwner = ($targetUser->id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);
        $isModeEdit = ($request->get('mode') === 'edit');

        $canCreate = ($isRegular && $isOwner) || $isManagement;
        $canEdit = ($isRegular && $isOwner) || ($isManagement && $isModeEdit);
        $canDelete = ($isRegular && $isOwner) || $isManagement;

        // --- 4. AMBIL DATA HISTORI ---
        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'previousBranch', 'creator', 'editor']) 
                ->orderBy('event_date', 'desc')
                ->get();
        }

        return view('employment_history.index', compact('histories', 'selectableUsers', 'targetUser', 'canEdit', 'canCreate', 'canDelete'));
    }

    /**
     * MENAMPILKAN FORMULIR TAMBAH (CREATE)
     */
    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $targetId = $request->get('user_id', $currentUser->id);
        $targetUser = User::findOrFail($targetId);

        if (!in_array($currentUser->role, ['admin', 'audit', 'leader']) && $targetId != $currentUser->id) {
             abort(403, 'Akses ditolak.');
        }

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

        if (in_array($currentUser->role, ['user_biasa', 'security'])) {
            $request->merge(['user_id' => $currentUser->id]);
        }

        $data = $request->only(['user_id', 'type', 'event_date', 'description', 'division_id', 'branch_id']);
        $data['created_by'] = $currentUser->id;

        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        $targetUser = User::find($request->user_id);
        $data['previous_branch_id'] = $targetUser->branch_id; 

        // NOTE: Logika Audit Multi Branch Dihapus sesuai permintaan, sekarang Audit pakai branch_id single biasa.
        
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
        $data['updated_by'] = $currentUser->id;

        if ($request->type == 'transfer_branch') {
            $data['division_id'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($history->attachment) Storage::disk('public')->delete($history->attachment);
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // NOTE: Logika update snapshot Audit dihapus, sekarang pakai branch_id biasa.

        $history->update($data);

        $redirectParams = ['user_id' => $history->user_id];
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            $redirectParams['mode'] = 'edit';
        }

        return redirect()->route('employment-history.index', $redirectParams)
            ->with('success', 'Data riwayat diperbarui.');
    }

    /**
     * DESTROY
     */
    public function destroy($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        if ($isRegular && !$isOwner) abort(403, 'Akses ditolak.');
        if (!$isManagement && !$isRegular) abort(403);

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

        return redirect()->route('employment-history.index', ['user_id' => $userId])
            ->with('success', 'Riwayat berhasil dihapus.');
    }
}