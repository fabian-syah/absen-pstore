<?php

namespace App\Http\Controllers;

use App\Models\EmploymentHistory;
use App\Models\Branch;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $targetUserId = $request->user_id ?? auth()->id();
        $targetUser = User::with('branches')->findOrFail($targetUserId);

        // ... validasi hak akses audit (tidak berubah) ...

        DB::beginTransaction(); // Pakai transaction biar aman
        try {
            $data = $request->only(['type', 'event_date', 'description', 'division_id']);
            $data['user_id'] = $targetUser->id;

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
            }

            // === LOGIKA UTAMA ===
            if ($request->type == 'transfer_branch') {

                // 1. KHUSUS AUDIT (MULTI BRANCH)
                if ($targetUser->role == 'audit') {
                    $request->validate(['audit_branch_ids' => 'required|array']);

                    // a. Ambil nama-nama cabang SEBELUM update (Existing)
                    $oldBranchNames = $targetUser->branches->pluck('name')->toArray();

                    // b. Ambil nama-nama cabang BARU (Selected)
                    $newBranches = Branch::whereIn('id', $request->audit_branch_ids)->get();
                    $newBranchNames = $newBranches->pluck('name')->toArray();

                    // c. Simpan Snapshot "Dari A,B ke C,D"
                    $data['audit_branch_snapshot'] = [
                        'from' => $oldBranchNames,
                        'to'   => $newBranchNames
                    ];

                    $data['branch_id'] = null;
                    $data['previous_branch_id'] = null;

                    // d. Sync ke tabel pivot user
                    $targetUser->branches()->sync($request->audit_branch_ids);
                }

                // 2. USER LAIN (SINGLE BRANCH)
                else {
                    $request->validate(['branch_id' => 'required|exists:branches,id']);
                    $data['previous_branch_id'] = $targetUser->branch_id;
                    $data['branch_id'] = $request->branch_id;
                    $targetUser->branch_id = $request->branch_id;
                    $targetUser->save();
                }
            }
            // Logika Join / Rejoin
            elseif ($request->type == 'join' || $request->type == 'rejoin') {
                if ($targetUser->role == 'audit') {
                    // Jika baru masuk/rejoin sebagai audit, simpan cabang awalnya
                    if ($request->audit_branch_ids) {
                        $targetUser->branches()->sync($request->audit_branch_ids);
                    }
                } else {
                    $data['branch_id'] = $request->branch_id;
                    $targetUser->branch_id = $request->branch_id;
                    $targetUser->save();
                }
            } elseif ($request->type == 'transfer_division') {
                $data['branch_id'] = $targetUser->branch_id;
                $targetUser->division_id = $request->division_id;
                $targetUser->save();
            } elseif ($request->type == 'resign') {
                $data['branch_id'] = null;
                $data['division_id'] = null;
                // Opsional: $targetUser->is_active = false; $targetUser->save();
            }

            EmploymentHistory::create($data);
            DB::commit();

            return redirect()->back()->with('success', 'Riwayat berhasil disimpan & Data User diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
