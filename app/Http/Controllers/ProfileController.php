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
        // Ambil data user terbaru (termasuk status request & path foto)
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
     * Update FOTO PROFIL (Logika: Sekali Upload -> Terkunci)
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- LOGIKA UTAMA ---
        // Jika user SUDAH PUNYA foto DAN status requestnya BUKAN approved, maka TOLAK.
        // Ini berlaku untuk user verified maupun belum.
        if ($user->profile_photo_path && $user->photo_request_status !== 'approved') {
            return redirect()->route('profile.edit')
                ->with('error', 'Foto profil terkunci. Anda sudah pernah upload. Silakan ajukan request ganti foto.');
        }

        try {
            // Hapus foto lama jika ada
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Simpan foto baru
            $path = $request->file('profile_photo')->store('profile_photos', 'public');

            // Update database dan LANGSUNG KUNCI KEMBALI (set status ke 'none')
            $user->update([
                'profile_photo_path' => $path,
                'photo_request_status' => 'none' 
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Foto profil berhasil diperbarui dan sekarang terkunci kembali.');

        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
        }
    }

    /**
     * Request Ganti Foto (Berlaku untuk semua user yg sudah punya foto)
     */
    public function requestPhotoChange()
    {
        $user = Auth::user();

        // Jika belum punya foto, tidak perlu request, langsung upload saja.
        if (!$user->profile_photo_path) {
            return back()->with('success', 'Anda belum memiliki foto profil. Silakan langsung upload tanpa perlu izin.');
        }

        // Cek jika sudah pending
        if ($user->photo_request_status === 'pending') {
            return back()->with('error', 'Pengajuan Anda sedang diproses. Mohon tunggu persetujuan Admin.');
        }

        // Cek jika sudah approved tapi user malah request lagi (kasus jarang, tapi preventif)
        if ($user->photo_request_status === 'approved') {
            return back()->with('success', 'Izin sudah diberikan! Silakan langsung upload foto baru.');
        }

        // Kirim Request
        $user->update(['photo_request_status' => 'pending']);

        return back()->with('success', 'Permintaan ganti foto dikirim. Tunggu Admin memberikan akses.');
    }

    /**
     * Hapus Foto Profil
     */
    public function deleteProfilePhoto()
    {
        $user = Auth::user();

        // Jika aturan "sekali pasang", maka user TIDAK BOLEH hapus foto sembarangan
        // karena kalau dihapus, dia jadi "belum punya foto" dan bisa upload lagi tanpa izin.
        // Jadi kita kunci fitur delete kecuali sudah dapat izin approved.
        
        if ($user->profile_photo_path && $user->photo_request_status !== 'approved') {
            return back()->with('error', 'Foto profil tidak dapat dihapus sembarangan. Silakan request ganti foto untuk mengubahnya.');
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

    public function getKtpPhoto(User $user) { }
    public function getProfilePhoto(User $user) { }

    // --- WORK HISTORY & INVENTORY ---
    public function storeInventory(Request $request)
    {
        // ... (Kode sama seperti sebelumnya)
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
            if ($request->hasFile('item_photo')) $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            if ($request->hasFile('document')) $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            Inventory::create($data);
            return redirect()->route('profile.edit')->with('success', 'Inventaris ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function destroyInventory(Inventory $inventory) {
        if ($inventory->user_id !== Auth::id()) abort(403);
        $inventory->delete();
        return back()->with('success', 'Inventaris dihapus.');
    }
    public function showInventory() { }
    public function storeWorkHistory(Request $request) {
        $request->validate([ 'position' => 'required', 'department' => 'required', 'start_date' => 'required|date' ]);
        WorkHistory::create(array_merge($request->all(), ['user_id' => Auth::id()]));
        return back()->with('success', 'Riwayat pekerjaan ditambahkan.');
    }
    public function destroyWorkHistory($id) {
        WorkHistory::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Dihapus.');
    }
}