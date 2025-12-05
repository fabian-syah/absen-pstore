<?php

namespace App\Http\Controllers;

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
        // Menggunakan fresh() untuk memastikan data status (verified/request) paling baru dari DB
        $user = Auth::user()->fresh();

        $workHistories = $user->workHistories;
        $inventories = $user->inventories()->latest()->get();

        return view('profile.edit', compact('user', 'workHistories', 'inventories'));
    }

    /**
     * Update data TEKS (Nama, Email, Sosmed, Password).
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:100',
        ]);

        $data = $request->only([
            'name', 'email', 'whatsapp', 'instagram', 'tiktok', 'facebook', 'linkedin'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('profile.edit')
            ->with('success', 'Informasi profil berhasil diperbarui.');
    }

    /**
     * Update HANYA foto profil (DENGAN LOGIKA KUNCI/LOCK).
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. CEK KEAMANAN: Jika Verified TAPI status request belum 'approved', TOLAK.
        if ($user->is_verified && $user->photo_request_status !== 'approved') {
            return redirect()->route('profile.edit')
                ->with('error', 'AKSES DITOLAK: Akun Anda terverifikasi. Silakan ajukan Request Ganti Foto terlebih dahulu.');
        }

        try {
            // Hapus foto lama jika ada
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Simpan foto baru
            $path = $request->file('profile_photo')->store('profile_photos', 'public');

            // Data yang akan diupdate
            $updateData = ['profile_photo_path' => $path];

            // 2. KUNCI KEMBALI (RE-LOCK):
            // Jika user verified, setelah berhasil upload, kembalikan status ke 'none'
            // Supaya dia tidak bisa upload lagi kecuali request ulang.
            if ($user->is_verified) {
                $updateData['photo_request_status'] = 'none';
            }

            $user->update($updateData);

            return redirect()->route('profile.edit')
                ->with('success', 'Foto profil berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
        }
    }

    /**
     * User Verified mengajukan ganti foto.
     */
    public function requestPhotoChange()
    {
        $user = Auth::user();

        // Jika belum verified, suruh langsung ganti saja
        if (!$user->is_verified) {
            return back()->with('error', 'Akun Anda belum diverifikasi, Anda bebas mengganti foto tanpa izin.');
        }

        // Cek jika sudah pending
        if ($user->photo_request_status === 'pending') {
            return back()->with('error', 'Pengajuan Anda sedang diproses. Mohon tunggu.');
        }

        // Ubah status jadi pending (masuk notif admin)
        $user->update(['photo_request_status' => 'pending']);

        return back()->with('success', 'Permintaan dikirim. Tunggu persetujuan Admin/Audit untuk membuka kunci foto.');
    }

    /**
     * Hapus Foto Profil
     */
    public function deleteProfilePhoto()
    {
        $user = Auth::user();

        // User verified tidak boleh hapus foto sembarangan untuk menghindari akun anonim
        if ($user->is_verified) {
            return back()->with('error', 'Akun terverifikasi tidak diperbolehkan menghapus foto profil.');
        }

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Foto profil dihapus.');
    }

    // --- LOGIKA KTP (TIDAK DIUBAH) ---
    public function requestKtpChange(Request $request)
    {
        $request->validate(['ktp_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
        $user = Auth::user();
        $path = $request->file('ktp_photo')->store('ktp_photos/temp', 'public');
        $user->update([
            'ktp_photo_temp_path' => $path,
            'ktp_request_status' => 'pending'
        ]);
        return redirect()->back()->with('success', 'Foto KTP baru berhasil diupload dan diajukan ke Admin.');
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

    public function getKtpPhoto(User $user)
    {
        // Logika security akses file bisa ditambahkan disini
    }
    
    public function getProfilePhoto(User $user)
    {
        // Logika security akses file bisa ditambahkan disini
    }

    // --- LOGIKA HISTORY & INVENTORY (TIDAK DIUBAH) ---
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
    
    public function showInventory()
    {
        // Placeholder
    }

    public function storeWorkHistory(Request $request)
    {
        $request->validate([
            'position' => 'required',
            'department' => 'required',
            'start_date' => 'required|date',
        ]);
        $user = Auth::user();
        WorkHistory::create(array_merge($request->all(), ['user_id' => $user->id]));
        return back()->with('success', 'Riwayat pekerjaan ditambahkan.');
    }

    public function destroyWorkHistory($id)
    {
        WorkHistory::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Dihapus.');
    }
}