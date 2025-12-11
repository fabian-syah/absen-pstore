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
        } else {
            // Leader, Security, User Biasa: Hanya Diri Sendiri
            $selectableUsers = User::where('id', $currentUser->id)->get();
        }

        // --- 2. LOGIKA TARGET USER (SIAPA YG DITAMPILKAN) ---
        
        // Cek jika ada request 'user_id' dari dropdown
        if ($request->has('user_id')) {
            $requestedId = $request->user_id;

            // Validasi Hak Akses Melihat Orang Lain
            if ($requestedId != $currentUser->id) {
                // Jika bukan admin & bukan audit, tolak akses lihat orang lain
                if (!in_array($currentUser->role, ['admin', 'audit'])) {
                    abort(403, 'Anda hanya boleh melihat data diri sendiri.');
                }

                // Jika Audit, pastikan target ada di wilayahnya
                if ($currentUser->role === 'audit') {
                    $targetCheck = User::find($requestedId);
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if ($targetCheck && !in_array($targetCheck->branch_id, $allowedBranches)) {
                        abort(403, 'User ini di luar wilayah audit Anda.');
                    }
                }
            }
            $targetUser = User::find($requestedId);
        } else {
            // Default: Tampilkan Diri Sendiri
            $targetUser = $currentUser;
        }

        // --- 3. AMBIL DATA HISTORI ---
        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'previousBranch']) 
                ->orderBy('event_date', 'desc')
                ->get();
        }

        return view('employment_history.index', compact('histories', 'selectableUsers', 'targetUser'));
    }

    /**
     * MENAMPILKAN FORMULIR TAMBAH (CREATE)
     */
    public function create(Request $request)
    {
        $currentUser = auth()->user();
        
        // Ambil target ID dari URL, default ke diri sendiri
        $targetId = $request->get('user_id', $currentUser->id);
        $targetUser = User::findOrFail($targetId);

        // --- VALIDASI AKSES CREATE ---
        // Jika user biasa mencoba membuatkan data untuk orang lain -> TOLAK
        if ($targetId != $currentUser->id) {
            if (!in_array($currentUser->role, ['admin', 'audit'])) {
                abort(403, 'Anda hanya bisa menambah data untuk diri sendiri.');
            }
            // Validasi Audit
            if ($currentUser->role === 'audit') {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches) && $targetUser->id !== $currentUser->id) {
                    abort(403, 'User ini di luar wilayah audit Anda.');
                }
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

        // --- KEAMANAN DATA ---
        // Jika bukan Admin/Audit, Paksa user_id menjadi diri sendiri (Anti Injeksi)
        if (!in_array($currentUser->role, ['admin', 'audit'])) {
            $request->merge(['user_id' => $currentUser->id]);
        }

        $data = $request->only(['user_id', 'type', 'event_date', 'description', 'division_id', 'branch_id']);

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

        return redirect()->route('employment-history.index', ['user_id' => $data['user_id']])
            ->with('success', 'Riwayat berhasil dicatat (Timeline Only).');
    }

    /**
     * MENAMPILKAN FORM EDIT
     */
    public function edit($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        // --- VALIDASI AKSES EDIT ---
        // Boleh edit jika: Admin/Audit ATAU Data Milik Sendiri
        $isOwner = ($history->user_id == $currentUser->id);
        $isAdminOrAudit = in_array($currentUser->role, ['admin', 'audit']);

        if (!$isOwner && !$isAdminOrAudit) {
            abort(403, 'Anda tidak berhak mengedit data ini.');
        }

        // Jika Audit mengedit orang lain, pastikan wilayahnya benar
        if ($currentUser->role === 'audit' && !$isOwner) {
            $targetUser = $history->user;
            $allowedBranches = $currentUser->branches->pluck('id')->toArray();
            if (!in_array($targetUser->branch_id, $allowedBranches)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
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

        // --- VALIDASI AKSES UPDATE ---
        $isOwner = ($history->user_id == $currentUser->id);
        $isAdminOrAudit = in_array($currentUser->role, ['admin', 'audit']);

        if (!$isOwner && !$isAdminOrAudit) {
            abort(403, 'Anda tidak berhak mengedit data ini.');
        }

        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['type', 'event_date', 'description', 'division_id', 'branch_id']);

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

        return redirect()->route('employment-history.index', ['user_id' => $history->user_id])
            ->with('success', 'Data riwayat berhasil diperbarui.');
    }
}