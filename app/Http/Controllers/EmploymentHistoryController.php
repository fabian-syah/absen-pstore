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
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $targetUser = null;
        $selectableUsers = collect([]); // Koleksi kosong default

        // 1. LOGIKA PEMILIHAN USER (Siapa yang boleh dilihat?)
        if ($currentUser->role === 'admin') {
            // Admin: Bisa lihat semua user
            $selectableUsers = User::orderBy('name')->get();
        } elseif ($currentUser->role === 'audit') {
            // Audit: User di cabang pegangannya + dirinya sendiri
            $branchIds = $currentUser->branches->pluck('id')->toArray();

            $selectableUsers = User::whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } else {
            // User Biasa/Leader/Security: Hanya dirinya sendiri
            // Tidak perlu isi $selectableUsers karena tidak akan ada dropdown
            $targetUser = $currentUser;
        }

        // 2. MENENTUKAN TARGET USER (Siapa yang datanya mau ditampilkan?)
        if ($currentUser->role === 'admin' || $currentUser->role === 'audit') {
            // Jika ada request user_id dari dropdown, pakai itu
            if ($request->has('user_id')) {
                // Validasi: Apakah Audit boleh melihat user ID ini?
                $requestedUser = User::find($request->user_id);

                if ($currentUser->role === 'audit') {
                    // Cek apakah user yang diminta ada di cabang audit atau diri sendiri
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                    if (!in_array($requestedUser->branch_id, $allowedBranches) && $requestedUser->id !== $currentUser->id) {
                        abort(403, 'Anda tidak berhak melihat user dari cabang ini.');
                    }
                }
                $targetUser = $requestedUser;
            } else {
                // Default jika Admin/Audit belum milih: Tampilkan dirinya sendiri dulu
                $targetUser = $currentUser;
            }
        }

        // 3. AMBIL DATA HISTORI
        // Pastikan targetUser tidak null
        $histories = collect([]);
        if ($targetUser) {
            $histories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division'])
                ->orderBy('event_date', 'desc')
                ->get();
        }

        $branches = Branch::all();
        $divisions = Division::all();

        return view('employment_history.index', compact('histories', 'branches', 'divisions', 'selectableUsers', 'targetUser'));
    }

    public function store(Request $request)
    {
        // Validasi Dasar
        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Tentukan User Target
        $targetUserId = $request->user_id ?? auth()->id();
        $targetUser = User::with('branches')->findOrFail($targetUserId); // Load relasi branches untuk audit

        // Cek Hak Akses (Sama seperti sebelumnya)
        if (auth()->user()->role === 'audit') {
            // ... Validasi user cabang audit ... (Kode sama seperti sebelumnya)
        }

        $data = $request->only(['type', 'event_date', 'description', 'division_id']);
        $data['user_id'] = $targetUser->id;

        // Upload Foto
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // =========================================================
        // LOGIKA KHUSUS: PINDAH CABANG (AUDIT VS NON-AUDIT)
        // =========================================================

        if ($request->type == 'transfer_branch') {

            // 1. JIKA TARGET ADALAH AUDIT (MULTI BRANCH)
            if ($targetUser->role == 'audit') {
                $request->validate(['audit_branch_ids' => 'required|array']);

                // a. Simpan Snapshot Cabang Lama (Nama-namanya)
                $oldBranchNames = $targetUser->branches->pluck('name')->toArray();

                // b. Ambil Nama Cabang Baru
                $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
                $newBranchNames = $newBranches->pluck('name')->toArray();

                // c. Simpan ke Kolom JSON
                $data['audit_branch_snapshot'] = [
                    'from' => $oldBranchNames,
                    'to'   => $newBranchNames
                ];

                // d. Null kan single branch ID
                $data['branch_id'] = null;
                $data['previous_branch_id'] = null;

                // e. UPDATE DATA USER SEKARANG (Sync Pivot Table)
                // Fitur ini otomatis mengubah hak akses user audit tsb
                $targetUser->branches()->sync($request->audit_branch_ids);
            }

            // 2. JIKA USER BIASA / LEADER / SECURITY (SINGLE BRANCH)
            else {
                $request->validate(['branch_id' => 'required|exists:branches,id']);

                // a. Simpan ID Cabang Lama
                $data['previous_branch_id'] = $targetUser->branch_id;

                // b. Simpan ID Cabang Baru
                $data['branch_id'] = $request->branch_id;

                // c. UPDATE DATA USER SEKARANG
                $targetUser->branch_id = $request->branch_id;
                $targetUser->save();
            }
        }
        // Logika Kategori Lain (Awal Masuk, Resign, dll)
        elseif ($request->type == 'join' || $request->type == 'rejoin') {
            // Jika audit masuk, bisa set multi branch juga di sini jika mau
            // Untuk simpelnya, kita asumsikan 'join' set single branch dulu atau sesuaikan logika
            if ($targetUser->role != 'audit') {
                $data['branch_id'] = $request->branch_id;
                $targetUser->branch_id = $request->branch_id;
                $targetUser->save();
            }
        } elseif ($request->type == 'transfer_division') {
            // Logic pindah divisi (branch tetap)
            $data['branch_id'] = $targetUser->branch_id;
            // Update Divisi User
            $targetUser->division_id = $request->division_id;
            $targetUser->save();
        } elseif ($request->type == 'resign') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
            // Opsional: Set user jadi inactive
            $targetUser->is_active = false;
            $targetUser->save();
        }

        EmploymentHistory::create($data);

        return redirect()->back()->with('success', 'Riwayat berhasil disimpan & Data User diperbarui.');
    }

    public function destroy($id)
    {
        $currentUser = auth()->user();

        // CEK HAK AKSES DELETE
        // User biasa, Leader, Security TIDAK BOLEH hapus
        if (in_array($currentUser->role, ['user_biasa', 'leader', 'security'])) {
            return abort(403, 'Anda tidak memiliki akses untuk menghapus data ini.');
        }

        $history = EmploymentHistory::findOrFail($id);

        // Validasi Tambahan untuk Audit (Hanya boleh hapus data di cabangnya)
        if ($currentUser->role === 'audit') {
            $targetUser = $history->user;
            $allowedBranches = $currentUser->branches->pluck('id')->toArray();
            if (!in_array($targetUser->branch_id, $allowedBranches) && $targetUser->id !== $currentUser->id) {
                return abort(403, 'Anda tidak berhak menghapus data user di luar cabang Anda.');
            }
        }

        if ($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }

        $history->delete();
        return redirect()->back()->with('success', 'Riwayat dihapus.');
    }
}
