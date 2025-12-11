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
            // Admin: Semua User
            $selectableUsers = User::with('branch')->orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            // Audit: User Cabang + Diri Sendiri
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            $selectableUsers = User::with('branch')->whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } elseif ($currentUser->role === 'leader') {
            // Leader: User di Cabang yang sama + Diri Sendiri
            $selectableUsers = User::with('branch')->where('branch_id', $currentUser->branch_id)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            // Security / User Biasa: Hanya Diri Sendiri
            $selectableUsers = User::with('branch')->where('id', $currentUser->id)->get();
        }

        // --- 2. TARGET USER ---
        if ($request->has('user_id')) {
            $requestedId = $request->user_id;
            
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
            // Default: Jika Admin/Audit/Leader akses index tanpa param, tampilkan diri sendiri dulu
            // Nanti di view mereka bisa pilih user lain dari dropdown
            $targetUser = $currentUser;
        }

        // --- 3. HAK AKSES ---
        $isOwner = ($targetUser->id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);
        
        // Mode Edit aktif jika:
        // 1. User biasa akses diri sendiri
        // 2. Management akses diri sendiri
        // 3. Management akses orang lain DAN ada parameter ?mode=edit (biasanya dari tombol tambah/edit)
        // 4. ATAU Management akses orang lain via Sidebar (otomatis mode edit agar bisa create)
        
        // Simplifikasi: Management selalu bisa Create/Edit di halaman ini untuk user yg valid
        $canEdit = ($isRegular && $isOwner) || $isManagement;
        $canCreate = $canEdit;

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
        // Ambil target ID, jika tidak ada, default ke diri sendiri
        $targetId = $request->get('user_id', $currentUser->id);
        $targetUser = User::findOrFail($targetId);

        // Validasi Akses
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader']) && $targetId != $currentUser->id) {
             abort(403, 'Akses ditolak.');
        }

        // Scope Wilayah (Pastikan Admin/Leader/Audit hanya mengelola user di bawahnya)
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
        // PENTING: Untuk tujuan Mutasi (Pindah Cabang), kita harus menampilkan SEMUA cabang
        // agar karyawan bisa dipindahkan ke cabang mana saja, bukan hanya cabang si Leader/Audit.
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

        // Redirect dengan user_id agar tetap di halaman user tersebut
        return redirect()->route('employment-history.index', ['user_id' => $data['user_id']])
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

        // Tampilkan semua cabang agar history bisa diedit ke cabang manapun
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

        return redirect()->route('employment-history.index', ['user_id' => $history->user_id])
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

        if ($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }

        $userId = $history->user_id;
        $history->delete();

        return redirect()->route('employment-history.index', ['user_id' => $userId])
            ->with('success', 'Riwayat berhasil dihapus.');
    }
}