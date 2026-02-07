<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminKtpController extends Controller
{
    /**
     * Show Print View containing User Biodata and KTP Photo.
     * Use Native GD to resize images and avoid memory leaks.
     * Returns a VIEW that can be printed as PDF.
     */
    public function downloadPdf()
    {
        set_time_limit(1200); // 20 Minutes
        ini_set('memory_limit', '2048M'); // 2GB

        // 1. Setup Temp Directory
        $tempDir = storage_path('app/public/temp_ktp_export');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        // Clean up old files (older than 2 hour)
        $files = glob($tempDir . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < (time() - 7200)) {
                    @unlink($file);
                }
            }
        }

        // 2. Constants
        $targetWidth = 350; // Optimized for Print View
        $quality = 60;

        // 3. Get Users
        $users = User::whereNotNull('ktp_photo_path')
            ->where('ktp_photo_path', '!=', '')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada data user dengan foto KTP.');
        }

        $optimizedUsers = [];

        foreach ($users as $user) {
            $path = $user->ktp_photo_path;

            // Resolve Path
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/' . $path);
            }

            $webPath = null;

            if (file_exists($fullPath)) {
                try {
                    // Unique temp filename
                    $bname = basename($fullPath);
                    // Sanitize filename
                    $bname = preg_replace('/[^a-zA-Z0-9\._-]/', '', $bname);

                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    $tempFilename = 'thumb_' . $user->id . '_' . time() . '.jpg';
                    $targetPath = $tempDir . '/' . $tempFilename;

                    // NATIVE GD RESIZE
                    $size = @getimagesize($fullPath);
                    if ($size) {
                        list($width, $height) = $size;

                        if ($width > 0 && $height > 0) {
                            $ratio = $width / $height;
                            $newWidth = $targetWidth;
                            $newHeight = $targetWidth / $ratio;

                            $src = null;
                            if ($ext == 'jpg' || $ext == 'jpeg') {
                                $src = @imagecreatefromjpeg($fullPath);
                            } elseif ($ext == 'png') {
                                $src = @imagecreatefrompng($fullPath);
                            }

                            if ($src) {
                                $dst = imagecreatetruecolor((int) $newWidth, (int) $newHeight);

                                if ($ext == 'png') {
                                    imagealphablending($dst, false);
                                    imagesavealpha($dst, true);
                                }

                                imagecopyresampled($dst, $src, 0, 0, 0, 0, (int) $newWidth, (int) $newHeight, $width, $height);

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

                                if (file_exists($targetPath)) {
                                    $webPath = asset('storage/temp_ktp_export/' . $tempFilename);
                                }
                            }
                        }
                    }

                } catch (\Throwable $e) {
                    Log::error("GD Resize fail User {$user->id}: " . $e->getMessage());
                }
            }

            // Generic Object
            $u = new \stdClass();
            $u->name = $user->name;
            $u->employee_id = $user->employee_id;
            $u->position = $user->position;
            $u->branch_name = $user->branch->name ?? '-';
            $u->division_name = $user->division->name ?? '-';
            $u->email = $user->email;
            $u->ktp_url = $webPath; // Pass Web URL

            $optimizedUsers[] = $u;
        }

        // Return View directly
        return view('admin.ktp.pdf', [
            'users' => $optimizedUsers,
            'isPrintMode' => true
        ]);
    }
}
