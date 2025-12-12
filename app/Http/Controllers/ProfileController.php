<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\WorkHistory;
use App\Models\Inventory;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil.
     */
    public function edit()
    {
        $user = Auth::user()->fresh();
        $workHistories = $user->workHistories;
        $inventories = $user->inventories()->latest()->get();

        // AMBIL DATA PENGHARGAAN (TIDAK DIHAPUS)
        $achievements = LeaderboardHistory::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->groupBy('year');

        return view('profile.edit', compact('user', 'workHistories', 'inventories', 'achievements'));
    }

    /**
     * Update data (Nama, Tgl Lahir, Email, Sosmed, Password, & SETTING AI).
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            // Validasi Sosmed
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            // Validasi Setting AI (Boleh 0 atau 1)
            'use_face_recognition' => 'nullable|in:0,1',
        ]);

        $data = $request->only([
            'name',
            'birth_date',
            'email',
            'whatsapp',
            'instagram',
            'tiktok',
            'facebook',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // =========================================================
        // [FIXED] UPDATE PENGATURAN AI WAJAH
        // =========================================================
        // Kita ambil input langsung. Karena ada hidden input di view,
        // nilainya pasti terkirim: '1' jika dicentang, '0' jika tidak.
        $data['use_face_recognition'] = $request->input('use_face_recognition');

        $user->update($data);

        return redirect()->route('profile.edit')
            ->with('success', 'Informasi profil dan pengaturan berhasil diperbarui.');
    }

    /**
     * Upload Foto Profil PERTAMA KALI (Langsung Aktif)
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->profile_photo_path) {
            return back()->with('error', 'Anda sudah memiliki foto profil. Gunakan fitur "Ganti Foto".');
        }

        try {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');

            $user->update([
                'profile_photo_path' => $path,
                'photo_request_status' => 'none'
            ]);

            return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil diupload.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    /**
     * REQUEST Ganti Foto (Upload ke Temp Path & Set Pending)
     */
    public function requestPhotoChange(Request $request)
    {
        $request->validate(['profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
        $user = Auth::user();

        if ($user->photo_request_status === 'pending') {
            return back()->with('error', 'Pengajuan sebelumnya masih diproses.');
        }

        try {
            if ($user->profile_photo_temp_path) {
                Storage::disk('public')->delete($user->profile_photo_temp_path);
            }

            $path = $request->file('profile_photo')->store('profile_photos/temp', 'public');

            $user->update([
                'profile_photo_temp_path' => $path,
                'photo_request_status' => 'pending'
            ]);

            return back()->with('success', 'Foto baru berhasil diajukan. Menunggu persetujuan Admin.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload request: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Foto Profil
     */
    public function deleteProfilePhoto()
    {
        $user = Auth::user();
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
        $user->update(['profile_photo_path' => null, 'photo_request_status' => 'none']);
        return back()->with('success', 'Foto profil dihapus.');
    }

    // --- LOGIKA KTP ---

    public function requestKtpChange(Request $request)
    {
        $request->validate(['ktp_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
        $user = Auth::user();

        if ($user->ktp_photo_temp_path) {
            Storage::disk('public')->delete($user->ktp_photo_temp_path);
        }

        $path = $request->file('ktp_photo')->store('ktp_photos/temp', 'public');

        $user->update([
            'ktp_photo_temp_path' => $path,
            'ktp_request_status' => 'pending'
        ]);

        return back()->with('success', 'Foto KTP baru berhasil diajukan. Menunggu approval Admin.');
    }

    public function updateKtp(Request $request)
    {
        $request->validate(['ktp_photo' => 'required|image|max:5120']);
        $user = Auth::user();

        if ($user->ktp_photo_path) {
            return back()->with('error', 'KTP sudah ada, gunakan tombol Ganti KTP.');
        }

        $path = $request->file('ktp_photo')->store('ktp_photos', 'public');
        $user->update(['ktp_photo_path' => $path]);

        return back()->with('success', 'KTP berhasil diupload.');
    }

    // --- INVENTORY & WORK HISTORY ---

    public function storeInventory(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string',
            'received_date' => 'required|date',
            'condition' => 'required|string',
            'item_photo' => 'nullable|image|max:5120',
        ]);

        try {
            $data = $request->except(['item_photo', 'document']);
            $data['user_id'] = $user->id;

            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }
            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            }

            Inventory::create($data);
            return redirect()->route('profile.edit')->with('success', 'Inventaris ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroyInventory(Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) abort(403);
        $inventory->delete();
        return back()->with('success', 'Inventaris dihapus.');
    }

    public function storeWorkHistory(Request $request)
    {
        $request->validate(['position' => 'required', 'department' => 'required', 'start_date' => 'required|date']);
        WorkHistory::create(array_merge($request->all(), ['user_id' => Auth::id()]));
        return back()->with('success', 'Riwayat pekerjaan ditambahkan.');
    }

    public function destroyWorkHistory($id)
    {
        WorkHistory::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Dihapus.');
    }
}