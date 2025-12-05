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
     * Menampilkan Halaman List Pengembalian (Sidebar Menu)
     */
    public function index()
    {
        // Hanya Admin/Audit yang boleh lihat history & approve
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403, 'Akses Ditolak');
        }

        $returns = InventoryReturn::with(['inventory', 'user', 'approver'])
                    ->latest()
                    ->paginate(10);

        return view('inventory_returns.index', compact('returns'));
    }

    /**
     * TAHAP 1: Request Pengembalian (Oleh User atau Admin)
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'return_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'note'         => 'nullable|string',
        ]);

        $inventory = Inventory::findOrFail($id);

        if (!$inventory->user_id) {
            return back()->with('error', 'Barang ini statusnya Available (tidak ada pemilik).');
        }

        try {
            // 1. Upload & Compress Foto
            $file = $request->file('return_photo');
            $filename = 'return_' . Str::random(10) . '_' . time() . '.jpg';
            $path = 'inventory_returns/' . $filename;
            
            $this->compressAndSaveImage($file, $path, 100); // Max 100KB

            // 2. Buat Data Pengembalian (STATUS: PENDING)
            InventoryReturn::create([
                'inventory_id' => $inventory->id,
                'user_id'      => $inventory->user_id, // User pemilik saat ini
                'photo_path'   => $path,
                'note'         => $request->note,
                'return_date'  => now(),
                'status'       => 'pending', // <--- PENTING: Masih Pending
                // 'approved_by' dikosongkan dulu
            ]);

            // CATATAN: Kita TIDAK mengubah inventory->user_id menjadi NULL disini.
            // Barang masih milik user sampai Admin klik Approve.

            return redirect()->route('inventory.index')->with('success', 'Permintaan pengembalian dikirim. Menunggu persetujuan Admin.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 2: Approve Pengembalian (Oleh Admin)
     */
    public function approve($id)
    {
        // Cek Role
        if (!in_array(Auth::user()->role, ['admin', 'audit'])) {
            abort(403);
        }

        $returnRequest = InventoryReturn::findOrFail($id);
        $inventory = Inventory::findOrFail($returnRequest->inventory_id);

        try {
            // 1. Update Status Pengembalian jadi Approved
            $returnRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id() // Catat siapa yang approve
            ]);

            // 2. Update Inventory jadi Available (Lepas User)
            $inventory->update([
                'user_id' => null, // <--- DISINI BARU DILEPAS
                'condition' => 'Baik' // Opsional: reset kondisi default
            ]);

            return back()->with('success', 'Pengembalian disetujui. Barang sekarang statusnya Available.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * Helper Compress Image
     */
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