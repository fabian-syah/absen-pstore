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
        // TAMBAHKAN ->fresh() DI SINI
        // Ini memaksa Laravel mengambil data terbaru dari DB (termasuk is_verified)
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
            'name',
            'email',
            'whatsapp',
            'instagram',
            'tiktok',
            'facebook',
            'linkedin'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Update HANYA foto profil (DENGAN LOGIKA CENTANG BIRU).
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- LOGIKA PENGUNCIAN ---
        // Jika User Verified DAN status request bukan 'approved', tolak upload.
        if ($user->is_verified && $user->photo_request_status !== 'approved') {
            return redirect()->route('profile.edit')
                ->with('error', 'Akun Terverifikasi. Silakan ajukan izin ganti foto terlebih dahulu.');
        }

        try {
            // Hapus foto lama jika ada
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Simpan foto baru
            $path = $request->file('profile_photo')->store('profile_photos', 'public');

            // Siapkan data update
            $updateData = ['profile_photo_path' => $path];

            // PENTING: Jika user verified, setelah upload berhasil, KUNCI KEMBALI
            // Reset status request kembali ke 'none' agar user harus request lagi jika mau ganti
            if ($user->is_verified) {
                $updateData['photo_request_status'] = 'none';
            }

            $user->update($updateData);

            return redirect()->route('profile.edit')
                ->with('success', 'Foto profil berhasil di-upload.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
        }
    }

    /**
     * FITUR BARU: Request Ganti Foto (Untuk user verified)
     */
    public function requestPhotoChange()
    {
        $user = Auth::user();

        if (!$user->is_verified) {
            return back()->with('error', 'Anda belum terverifikasi, silakan langsung ganti foto tanpa pengajuan.');
        }

        if ($user->photo_request_status === 'pending') {
            return back()->with('error', 'Pengajuan Anda sedang diproses oleh Admin/Audit.');
        }

        // Set status jadi pending
        $user->update(['photo_request_status' => 'pending']);

        return back()->with('success', 'Pengajuan ganti foto telah dikirim. Tunggu persetujuan Admin/Audit.');
    }

    public function requestKtpChange(Request $request)
    {
        $request->validate([
            'ktp_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB
        ]);

        $user = Auth::user();

        // 1. Upload ke folder temp
        $path = $request->file('ktp_photo')->store('ktp_photos/temp', 'public');

        // 2. Simpan path temp dan ubah status jadi pending
        $user->update([
            'ktp_photo_temp_path' => $path,
            'ktp_request_status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Foto KTP baru berhasil diupload dan diajukan ke Admin.');
    }

    // updateKtp (Hanya untuk upload PERTAMA KALI)
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

    /**
     * Menghapus foto profil user
     */
    public function deleteProfilePhoto()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // User verified tidak boleh hapus foto sembarangan
        if ($user->is_verified) {
            return back()->with('error', 'Akun terverifikasi tidak boleh menghapus foto profil.');
        }

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Foto profil dihapus.');
    }

    /**
     * Menyimpan inventaris baru
     */
    public function storeInventory(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|in:elektronik,perkantoran,kendaraan,lainnya',
            'serial_number' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'condition' => 'required|string|in:baik,rusak_ringan,rusak_berat,perbaikan',
            'description' => 'nullable|string|max:1000',
            'item_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB
        ]);

        try {
            $data = [
                'user_id' => $user->id,
                'item_name' => $request->item_name,
                'category' => $request->category,
                'serial_number' => $request->serial_number,
                'received_date' => $request->received_date,
                'condition' => $request->condition,
                'description' => $request->description,
            ];

            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            }

            Inventory::create($data);

            return redirect()->route('profile.edit')
                ->with('success', 'Inventaris berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal menambahkan inventaris: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus inventaris
     */
    public function destroyInventory(Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if ($inventory->item_photo_path) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }
            if ($inventory->document_path) {
                Storage::disk('public')->delete($inventory->document_path);
            }

            $inventory->delete();

            return redirect()->route('profile.edit')
                ->with('success', 'Inventaris berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal menghapus inventaris: ' . $e->getMessage());
        }
    }

    /**
     * Menambah Riwayat Pekerjaan (Work History)
     */
    public function storeWorkHistory(Request $request)
    {
        $request->validate([
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();

        WorkHistory::create([
            'user_id' => $user->id,
            'position' => $request->position,
            'department' => $request->department,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Riwayat pekerjaan ditambahkan.');
    }

    /**
     * Menghapus Riwayat Pekerjaan
     */
    public function destroyWorkHistory($id)
    {
        $history = WorkHistory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $history->delete();
        return redirect()->route('profile.edit')->with('success', 'Riwayat pekerjaan dihapus.');
    }
}
