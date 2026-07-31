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
        $selectableUsers = collect([]);

        // --- 1. LOGIKA LIST USER (DROPDOWN) ---
        if ($currentUser->role === 'admin') {
            // Admin bisa lihat semua
            $selectableUsers = User::with('branch')->orderBy('name')->get();
        } elseif (in_array($currentUser->role, ['audit', 'leader'])) {
            // [UPDATE] Audit & Leader sekarang logic-nya SAMA (Multi Cabang)
            // Mengambil ID cabang dari relasi many-to-many (table pivot)
            $branchIds = $currentUser->branches->pluck('id')->toArray();

            $selectableUsers = User::with('branch')
                ->whereIn('branch_id', $branchIds) // Cek user yang ada di cabang-cabang tersebut
                ->orWhere('id', $currentUser->id)  // Selalu sertakan diri sendiri
                ->orderBy('name')
                ->get();
        } else {
            // User biasa / Security hanya melihat diri sendiri
            $selectableUsers = User::with('branch')->where('id', $currentUser->id)->get();
        }

        // --- 2. LOGIKA TARGET USER & VALIDASI KEAMANAN ---
        if ($request->has('user_id')) {
            $requestedId = $request->user_id;
            $targetUser = User::find($requestedId);

            if (!$targetUser) {
                return back()->with('error', 'User tidak ditemukan.');
            }

            // Jika melihat orang lain (bukan diri sendiri)
            if ($targetUser->id !== $currentUser->id) {

                // Cek Role Management
                if (!in_array($currentUser->role, ['admin', 'audit', 'leader'])) {
                    abort(403, 'Anda hanya boleh melihat data diri sendiri.');
                }

                // [UPDATE] Validasi Multi Cabang untuk Audit & Leader
                if (in_array($currentUser->role, ['audit', 'leader'])) {
                    $allowedBranches = $currentUser->branches->pluck('id')->toArray();

                    if (!in_array($targetUser->branch_id, $allowedBranches)) {
                        abort(403, 'User ini berada di luar wilayah cabang akses Anda.');
                    }
                }
            }
        } else {
            // Default ke diri sendiri jika tidak ada param user_id
            $targetUser = $currentUser;
        }

        // --- 3. HAK AKSES TOMBOL (Create/Edit/Delete) ---
        $isOwner = ($targetUser->id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        $isModeEdit = ($request->get('mode') === 'edit');

        $canCreate = ($isRegular && $isOwner) || $isManagement;
        $canEdit = ($isRegular && $isOwner) || ($isManagement && $isModeEdit);
        $canDelete = ($isRegular && $isOwner) || $isManagement;

        // --- 4. AMBIL DATA HISTORI ---
        $internalHistories = collect([]);
        $externalHistories = collect([]);

        if ($targetUser) {
            $allHistories = EmploymentHistory::where('user_id', $targetUser->id)
                ->with(['branch', 'division', 'previousBranch', 'creator', 'editor'])
                ->orderBy('event_date', 'desc')
                ->get();

            $internalHistories = $allHistories->filter(fn($item) => $item->type !== 'external');
            $externalHistories = $allHistories->filter(fn($item) => $item->type === 'external');
        }

        return view('employment_history.index', compact(
            'internalHistories',
            'externalHistories',
            'selectableUsers',
            'targetUser',
            'canEdit',
            'canCreate',
            'canDelete'
        ));
    }

    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $targetId = $request->get('user_id', $currentUser->id);
        $targetUser = User::findOrFail($targetId);

        // Validasi Akses Halaman Create
        if (!in_array($currentUser->role, ['admin', 'audit', 'leader']) && $targetId != $currentUser->id) {
            abort(403, 'Akses ditolak.');
        }

        // Validasi Branch Multi-Cabang
        if ($targetId != $currentUser->id) {
            if (in_array($currentUser->role, ['audit', 'leader'])) {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) {
                    abort(403, 'Anda tidak memiliki akses ke cabang user ini.');
                }
            }
        }

        $branches = Branch::all();
        $divisions = Division::all();

        return view('employment_history.create', compact('targetUser', 'branches', 'divisions'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'event_date' => 'required_unless:type,external|date|nullable',
            'attachment' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'title' => 'nullable|string|required_if:type,external',
        ]);

        if (in_array($currentUser->role, ['user_biasa', 'security'])) {
            $request->merge(['user_id' => $currentUser->id]);
        }

        // Cek Security Layer terakhir sebelum save (untuk Leader/Audit)
        if (in_array($currentUser->role, ['audit', 'leader']) && $request->user_id != $currentUser->id) {
            $targetUserCheck = User::find($request->user_id);
            $allowedBranches = $currentUser->branches->pluck('id')->toArray();
            if (!in_array($targetUserCheck->branch_id, $allowedBranches)) {
                abort(403, 'Manipulasi data terdeteksi: User di luar jangkauan cabang.');
            }
        }

        $data = $request->only(['user_id', 'type', 'title', 'event_date', 'description', 'division_id', 'branch_id']);
        $data['created_by'] = $currentUser->id;

        if ($request->type == 'external' && empty($data['event_date'])) {
            $data['event_date'] = now();
        }

        if ($request->type == 'transfer_branch' || $request->type == 'external') {
            $data['division_id'] = null;
        }

        if ($request->type == 'external') {
            $data['branch_id'] = null;
        }

        $targetUser = User::find($request->user_id);
        $data['previous_branch_id'] = $targetUser->branch_id;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        EmploymentHistory::create($data);

        return redirect()->route('employment-history.index', ['user_id' => $data['user_id']])
            ->with('success', 'Riwayat berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();
        $targetUser = $history->user;

        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);
        $isRegular = in_array($currentUser->role, ['user_biasa', 'security']);

        if ($isRegular && !$isOwner) abort(403, 'Akses ditolak.');
        if (!$isManagement && !$isRegular) abort(403);

        // Validasi Wilayah Multi-Cabang (Audit & Leader)
        if ($targetUser->id != $currentUser->id && $isManagement) {
            if (in_array($currentUser->role, ['audit', 'leader'])) {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
            }
        }

        $branches = Branch::all();
        $divisions = Division::all();

        return view('employment_history.edit', compact('history', 'branches', 'divisions', 'targetUser'));
    }

    public function update(Request $request, $id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();

        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);

        if (!$isOwner && !$isManagement) abort(403);

        $request->validate([
            'type' => 'required',
            'event_date' => 'required_unless:type,external|date|nullable',
            'attachment' => 'nullable|image|max:2048',
            'title' => 'nullable|string|required_if:type,external',
        ]);

        $data = $request->only(['type', 'title', 'event_date', 'description', 'division_id', 'branch_id']);
        $data['updated_by'] = $currentUser->id;

        if ($request->type == 'external' && empty($data['event_date'])) {
            $data['event_date'] = $history->event_date ?? now();
        }

        if ($request->type == 'transfer_branch' || $request->type == 'external') {
            $data['division_id'] = null;
        }

        if ($request->type == 'external') {
            $data['branch_id'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($history->attachment) Storage::disk('public')->delete($history->attachment);
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        $history->update($data);

        return redirect()->route('employment-history.index', ['user_id' => $history->user_id])
            ->with('success', 'Data riwayat diperbarui.');
    }

    public function destroy($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        $currentUser = auth()->user();
        $targetUser = $history->user;

        $isOwner = ($history->user_id == $currentUser->id);
        $isManagement = in_array($currentUser->role, ['admin', 'audit', 'leader']);

        if (!$isOwner && !$isManagement) abort(403, 'Akses ditolak.');

        // Validasi Wilayah Multi-Cabang saat Delete
        if ($isManagement && !$isOwner) {
            if (in_array($currentUser->role, ['audit', 'leader'])) {
                $allowedBranches = $currentUser->branches->pluck('id')->toArray();
                if (!in_array($targetUser->branch_id, $allowedBranches)) abort(403);
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

    /**
     * Upload / ganti lampiran riwayat karir (Admin Only)
     */
    public function updateAttachment(Request $request, $id)
    {
        $currentUser = auth()->user();
        if ($currentUser->role !== 'admin') abort(403, 'Hanya Admin yang dapat mengupload lampiran.');

        $history = EmploymentHistory::findOrFail($id);

        $request->validate([
            'attachment' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120', // 5MB
        ], [
            'attachment.required' => 'File wajib dipilih.',
            'attachment.mimes'    => 'Format file harus JPG, PNG, WebP, atau PDF.',
            'attachment.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        // Hapus lampiran lama jika ada
        if ($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }

        // Simpan lampiran baru
        $path = $request->file('attachment')->store('employment_attachments', 'public');
        $history->update([
            'attachment'  => $path,
            'updated_by'  => $currentUser->id,
        ]);

        return redirect()
            ->route('employment-history.index', ['user_id' => $history->user_id])
            ->with('success', 'Lampiran berhasil diupload.');
    }
}
