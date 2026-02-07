<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminKtpController extends Controller
{
    /**
     * Show Print View containing User Biodata.
     * Images are loaded lazily via getThumbnail() to prevent timeout.
     */
    public function downloadPdf()
    {
        // 1. Get Active Users with KTP
        $users = User::whereNotNull('ktp_photo_path')
            ->where('ktp_photo_path', '!=', '')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada data user dengan foto KTP.');
        }

        // Return View directly
        // Note: we don't process images here anymore.
        return view('admin.ktp.pdf', [
            'users' => $users,
            'isPrintMode' => true
        ]);
    }

    /**
     * Get Resized KTP Thumbnail on-demand.
     */
    public function getThumbnail($id)
    {
        // Disable time limit for this specific request if needed, though individual resize is fast.
        set_time_limit(60);

        $user = User::findOrFail($id);

        if (!$user->ktp_photo_path) {
            abort(404);
        }

        $path = $user->ktp_photo_path;

        // Resolve Path
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $path);
        }

        if (!file_exists($fullPath)) {
            // Return a placeholder or 404
            abort(404);
        }

        // Cache Logic (Temp File)
        $tempDir = storage_path('app/public/temp_ktp_export');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        // Use file modification time for cache busting/validity
        $mtime = filemtime($fullPath);
        $tempFilename = 'thumb_' . $user->id . '_' . $mtime . '.jpg';
        $targetPath = $tempDir . '/' . $tempFilename;

        // If cached file exists, serve it
        if (file_exists($targetPath)) {
            return response()->file($targetPath);
        }

        // If not, Resize and Save
        try {
            $targetWidth = 350;
            $quality = 60;

            // NATIVE GD RESIZE
            $size = @getimagesize($fullPath);
            if ($size) {
                list($width, $height) = $size;
                if ($width > 0 && $height > 0) {
                    $ratio = $width / $height;
                    $newWidth = $targetWidth;
                    $newHeight = $targetWidth / $ratio;

                    $src = null;
                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

                    if ($ext == 'jpg' || $ext == 'jpeg') {
                        $src = @imagecreatefromjpeg($fullPath);
                    } elseif ($ext == 'png') {
                        $src = @imagecreatefrompng($fullPath);
                    }

                    if ($src) {
                        $dst = imagecreatetruecolor((int) $newWidth, (int) $newHeight);

                        // Handle PNG
                        if ($ext == 'png') {
                            imagealphablending($dst, false);
                            imagesavealpha($dst, true);
                        }

                        imagecopyresampled($dst, $src, 0, 0, 0, 0, (int) $newWidth, (int) $newHeight, $width, $height);

                        // Save as JPG
                        if ($ext == 'png') {
                            $bg = imagecreatetruecolor((int) $newWidth, (int) $newHeight);
                            $white = imagecolorallocate($bg, 255, 255, 255);
                            imagefill($bg, 0, 0, $white);
                            imagecopy($bg, $dst, 0, 0, 0, 0, (int) $newWidth, (int) $newHeight);
                            imagejpeg($bg, $targetPath, $quality);
                            imagedestroy($bg);
                        } else {
                            imagejpeg($dst, $targetPath, $quality);
                        }

                        imagedestroy($src);
                        imagedestroy($dst);

                        return response()->file($targetPath);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Thumbnail error User {$id}: " . $e->getMessage());
        }

        // Fallback: Return original if resize fails
        return response()->file($fullPath);
    }
}
