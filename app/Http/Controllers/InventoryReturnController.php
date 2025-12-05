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
        // Hanya Admin/Audit
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'audit') {
            abort(403, 'Akses Ditolak');
        }

        $returns = InventoryReturn::with(['inventory', 'user', 'admin'])
                    ->latest()
                    ->paginate(10);

        return view('inventory_returns.index', compact('returns'));
    }

    /**
     * Proses Pengembalian Barang (Dari Modal di Inventory Index)
     */
    public function store(Request $request, $id)
    {
        // Validasi
        $request->validate([
            'return_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max upload awal 5MB, nanti dicompress
            'note'         => 'nullable|string',
        ]);

        $inventory = Inventory::findOrFail($id);

        // Pastikan barang memang sedang dipakai seseorang
        if (!$inventory->user_id) {
            return back()->with('error', 'Barang ini statusnya sudah tidak ada pemilik (Available).');
        }

        try {
            // 1. PROSES GAMBAR (COMPRESS MAX 100KB)
            $file = $request->file('return_photo');
            $filename = 'return_' . Str::random(10) . '_' . time() . '.jpg';
            $path = 'inventory_returns/' . $filename;
            
            // Fungsi Kompresi Custom
            $this->compressAndSaveImage($file, $path, 100); // Target 100KB

            // 2. SIMPAN DATA PENGEMBALIAN
            InventoryReturn::create([
                'inventory_id' => $inventory->id,
                'user_id'      => $inventory->user_id, // User lama
                'admin_id'     => Auth::id(),          // Admin yang memproses
                'photo_path'   => $path,
                'note'         => $request->note,
                'return_date'  => now(),
                'status'       => 'approved',
            ]);

            // 3. UPDATE INVENTORY (Lepas User & Update Kondisi jika perlu)
            $inventory->update([
                'user_id' => null, // Barang jadi available
                // 'condition' => 'Baik' // Opsional: reset kondisi atau biarkan sesuai input terakhir
            ]);

            return redirect()->route('inventory.index')->with('success', 'Barang berhasil dikembalikan & User dilepas.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    /**
     * Helper Function: Compress Image until < targetSize (KB)
     */
    private function compressAndSaveImage($file, $path, $targetKb)
    {
        // Load gambar ke memory (PHP GD Library)
        $source = imagecreatefromstring(file_get_contents($file));
        
        // Cek orientasi (kadang HP upload miring) - Opsional
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

        // Resize jika terlalu besar (misal lebar > 1000px) agar file size turun drastis
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

        // Loop kompresi kualitas
        $quality = 75; // Start quality
        $tempPath = sys_get_temp_dir() . '/' . basename($path);
        
        do {
            // Simpan ke temp
            imagejpeg($source, $tempPath, $quality);
            $fileSize = filesize($tempPath) / 1024; // KB
            
            // Kurangi kualitas jika masih kegedean
            $quality -= 5;
        } while ($fileSize > $targetKb && $quality > 10);

        // Pindahkan dari temp ke Storage Laravel
        Storage::disk('public')->put($path, file_get_contents($tempPath));
        
        // Bersihkan memory
        imagedestroy($source);
        unlink($tempPath);
    }
}