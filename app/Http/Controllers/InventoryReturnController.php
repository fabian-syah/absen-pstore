<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryReturnController extends Controller
{
    /**
     * Menampilkan Halaman List Pengembalian (Sidebar Menu - Admin Only)
     */
    public function index()
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak');
        }

        // Load relasi inventory, user, dan admin
        $returns = InventoryReturn::with(['inventory', 'user', 'admin'])
            ->latest()
            ->paginate(10);

        return view('inventory_returns.index', compact('returns'));
    }

    /**
     * TAHAP 1: Request Pengembalian (Oleh User/Admin -> Status Pending)
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'return_photo'  => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'receiver_name' => 'required|string|max:255',
            'note'          => 'nullable|string',
        ]);

        $inventory = Inventory::findOrFail($id);

        // Cek apakah barang statusnya Available (user_id null)
        if (!$inventory->user_id) {
            return back()->with('error', 'Gagal: Barang ini statusnya Available (tidak ada pemilik).');
        }

        try {
            // 1. Proses Gambar
            $file = $request->file('return_photo');
            $filename = 'return_' . Str::random(10) . '_' . time() . '.jpg';
            $path = 'inventory_returns/' . $filename;

            $this->compressAndSaveImage($file, $path, 100);

            // 2. Buat Data (Status Pending)
            InventoryReturn::create([
                'inventory_id'  => $inventory->id,
                'user_id'       => $inventory->user_id,
                'receiver_name' => $request->receiver_name,
                'photo_path'    => $path,
                'note'          => $request->note,
                'return_date'   => now(),
                'status'        => 'pending',
                'admin_id'      => null,
            ]);

            return redirect()->route('inventory.index')->with('success', 'Permintaan pengembalian dikirim. Menunggu verifikasi Admin.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 2: Approve Pengembalian (Oleh Admin -> Barang Dilepas)
     */
    public function approve($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403);
        }

        $returnRequest = InventoryReturn::findOrFail($id);

        if ($returnRequest->status != 'pending') {
            return back()->with('error', 'Status pengembalian tidak valid.');
        }

        $inventory = Inventory::findOrFail($returnRequest->inventory_id);

        try {
            // 1. Update Status Pengembalian jadi Approved
            $returnRequest->update([
                'status'   => 'approved',
                'admin_id' => Auth::id()
            ]);

            // 2. Update Inventory jadi Available (Lepas User)
            $inventory->update([
                'user_id' => null,
            ]);

            return back()->with('success', 'Pengembalian disetujui. Barang sekarang statusnya Available.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 3: Reject Pengembalian (Oleh Admin -> Ditolak)
     * (INI YANG DITAMBAHKAN)
     */
    public function reject(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403);
        }

        $request->validate([
            'rejection_note' => 'required|string|max:255',
        ]);

        $returnRequest = InventoryReturn::findOrFail($id);

        if ($returnRequest->status != 'pending') {
            return back()->with('error', 'Status pengembalian tidak valid untuk ditolak.');
        }

        try {
            $returnRequest->update([
                'status'   => 'rejected',
                'admin_id' => Auth::id(),
                // Tambahkan alasan penolakan ke catatan
                'note'     => $returnRequest->note . " | [Ditolak]: " . $request->rejection_note
            ]);

            return back()->with('success', 'Pengembalian berhasil ditolak.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }

    // Helper Compress Image
    private function compressAndSaveImage($file, $path, $targetKb)
    {
        $source = imagecreatefromstring(file_get_contents($file));
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($file);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3: $source = imagerotate($source, 180, 0); break;
                    case 6: $source = imagerotate($source, -90, 0); break;
                    case 8: $source = imagerotate($source, 90, 0); break;
                }
            }
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $maxWidth = 800;
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
            $tempImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($tempImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $source = $tempImage;
        }
        $quality = 75;
        $tempPath = sys_get_temp_dir() . '/' . basename($path);
        do {
            imagejpeg($source, $tempPath, $quality);
            $fileSize = filesize($tempPath) / 1024;
            $quality -= 5;
        } while ($fileSize > $targetKb && $quality > 10);
        Storage::disk('public')->put($path, file_get_contents($tempPath));
        imagedestroy($source);
        unlink($tempPath);
    }
}