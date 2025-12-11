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
        } 
        elseif ($currentUser->role === 'audit') {
            // Audit: User di cabang pegangannya + dirinya sendiri
            $branchIds = $currentUser->branches->pluck('id')->toArray();
            
            $selectableUsers = User::whereIn('branch_id', $branchIds)
                ->orWhere('id', $currentUser->id)
                ->orderBy('name')
                ->get();
        } 
        else {
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
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
            // Admin/Audit wajib kirim user_id jika memilih orang lain
            'user_id' => 'nullable|exists:users,id' 
        ]);

        $currentUser = auth()->user();
        $data = $request->except(['_token', 'user_id']); // Kita set user_id manual di bawah

        // LOGIKA PENENTUAN USER_ID UNTUK DISIMPAN
        if ($currentUser->role === 'admin') {
            // Admin bebas nentuin user_id (dari hidden input atau dropdown form)
            $data['user_id'] = $request->user_id ?? $currentUser->id;
        } 
        elseif ($currentUser->role === 'audit') {
            // Audit validasi dulu
            $targetId = $request->user_id ?? $currentUser->id;
            $targetUser = User::findOrFail($targetId);
            
            // Cek akses audit
            $allowedBranches = $currentUser->branches->pluck('id')->toArray();
            if (!in_array($targetUser->branch_id, $allowedBranches) && $targetId != $currentUser->id) {
                 return back()->with('error', 'Anda tidak berhak menambah data untuk user ini.');
            }
            $data['user_id'] = $targetId;
        } 
        else {
            // User Biasa/Leader/Security: HANYA BOLEH UNTUK DIRI SENDIRI
            $data['user_id'] = $currentUser->id;
        }

        // Upload Foto
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // Logika Pindah Divisi / Resign
        if ($request->type == 'transfer_division') {
            // Ambil branch ID dari user yang bersangkutan (bukan auth user, karena admin bisa edit orang lain)
            $userTarget = User::find($data['user_id']);
            $data['branch_id'] = $userTarget->branch_id;
        } 
        elseif ($request->type == 'resign') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        EmploymentHistory::create($data);

        return redirect()->back()->with('success', 'Riwayat berhasil ditambahkan.');
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

        if($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }
        
        $history->delete();
        return redirect()->back()->with('success', 'Riwayat dihapus.');
    }
}