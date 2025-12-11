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
            // [UPDATE] Leader: User di Cabang yang sama + Diri Sendiri
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
                
                // Validasi Leader (Hanya boleh lihat user di cabangnya)
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

        // --- 3. LOGIKA HAK AKSES EDIT (CRUCIAL) ---
        // Edit hanya boleh jika:
        // a. Role adalah Admin/Audit/Leader (Untuk orang lain/diri sendiri)
        // b. Parameter '?mode=edit' ada di URL (Dikirim dari tombol User Show)
        // c. ATAU User biasa mengedit dirinya sendiri (opsional, tapi di sini kita batasi sesuai prompt)
        
        $canEdit = false;
        if (in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            // Cek apakah mode edit diaktifkan dari URL
            if ($request->get('mode') === 'edit') {
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

        // Validasi Akses
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
             // User biasa tidak boleh create data orang lain (atau create data sendiri jika tidak diinginkan)
             // Asumsi: User biasa tidak boleh akses ini.
             abort(403, 'Akses ditolak.');
        }

        // Validasi Scope (Leader/Audit tidak boleh create user luar wilayah)
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

        // Redirect kembali ke index DENGAN mode edit agar tetap bisa kelola
        return redirect()->route('employment-history.index', ['user_id' => $data['user_id'], 'mode' => 'edit'])
            ->with('success', 'Riwayat berhasil dicatat (Timeline Only).');
    }

    /**
     * MENAMPILKAN FORM EDIT
     */
    public function edit($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        // Validasi Akses
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            abort(403, 'Akses ditolak.');
        }

        // Validasi Wilayah
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
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
            abort(403, 'Akses ditolak.');
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

        return redirect()->route('employment-history.index', ['user_id' => $history->user_id, 'mode' => 'edit'])
            ->with('success', 'Data riwayat berhasil diperbarui.');
    }
}