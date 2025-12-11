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

        // 1. LOGIKA PEMILIHAN USER (FILTER)
        if ($currentUser->role === 'admin') {
            $selectableUsers = User::orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            $selectableUsers = User::whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            // User Biasa/Leader/Security hanya lihat diri sendiri
            $targetUser = $currentUser;
        }

        // 2. TENTUKAN SIAPA YANG DITAMPILKAN
        if ($currentUser->role === 'admin' || $currentUser->role === 'audit') {
            if ($request->has('user_id')) {
                $requestedUser = User::find($request->user_id);
                
                // Validasi Audit
                if ($currentUser->role === 'audit') {
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if (!in_array($requestedUser->branch_id, $allowedBranches) && $requestedUser->id !== $currentUser->id) {
                        abort(403, 'Akses Ditolak.');
                    }
                }
                $targetUser = $requestedUser;
            } else {
                // Default tampilkan diri sendiri jika belum pilih
                $targetUser = $currentUser;
            }
        }

        // 3. AMBIL DATA HISTORI
        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'previousBranch']) // Eager load
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
        // Pastikan ada user_id yang dituju (dari parameter URL/Request)
        // Jika tidak ada, default ke diri sendiri (atau redirect error jika admin lupa pilih)
        $targetId = $request->get('user_id', auth()->id());
        $targetUser = User::findOrFail($targetId);

        // Validasi Akses (Cegah user biasa ngisi punya orang lain)
        if(auth()->user()->role != 'admin' && auth()->user()->role != 'audit' && auth()->id() != $targetId) {
            abort(403);
        }

        $branches = Branch::all();
        $divisions = Division::all();

        return view('employment_history.create', compact('targetUser', 'branches', 'divisions'));
    }

    /**
     * SIMPAN DATA (HANYA RECORD TIMELINE, TIDAK UPDATE USER)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['user_id', 'type', 'event_date', 'description', 'division_id', 'branch_id']);

        // Set Previous Branch (Opsional, manual input atau ambil dari current user sebagai referensi snapshot)
        // Karena requirementnya "timeline doang", kita snapshot kondisi user SAAT INI sebagai "Previous"
        $targetUser = User::find($request->user_id);
        $data['previous_branch_id'] = $targetUser->branch_id; 

        // Handle Audit Multi Branch Snapshot (Jika User Audit)
        if ($targetUser->role == 'audit' && $request->has('audit_branch_ids')) {
            $oldBranchNames = $targetUser->branches->pluck('name')->toArray();
            $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
            $newBranchNames = $newBranches->pluck('name')->toArray();

            $data['audit_branch_snapshot'] = [
                'from' => $oldBranchNames,
                'to'   => $newBranchNames
            ];
            // Catatan: Kita TIDAK men-sync data user asli, hanya mencatat snapshotnya.
        }

        // Upload File
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        EmploymentHistory::create($data);

        return redirect()->route('employment-history.index', ['user_id' => $request->user_id])
            ->with('success', 'Riwayat berhasil dicatat (Timeline Only).');
    }

    /**
     * MENAMPILKAN FORM EDIT
     */
    public function edit($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        
        // Validasi akses edit (Admin/Audit only)
        if (!in_array(auth()->user()->role, ['admin', 'audit'])) {
            abort(403, 'Hanya Admin/Audit yang boleh mengedit riwayat.');
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

        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['type', 'event_date', 'description', 'division_id', 'branch_id']);

        // Handle File Update
        if ($request->hasFile('attachment')) {
            // Hapus file lama jika ada
            if ($history->attachment) {
                Storage::disk('public')->delete($history->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // Handle Audit Snapshot Update (Jika ada perubahan manual di array snapshot)
        if ($history->user->role == 'audit' && $request->has('audit_branch_ids')) {
             // Logic snapshot ulang jika perlu, atau biarkan statis sesuai saat create
             // Disini saya update jika ada input baru
             $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
             $newBranchNames = $newBranches->pluck('name')->toArray();
             
             // Ambil 'from' yang lama, update 'to' nya
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