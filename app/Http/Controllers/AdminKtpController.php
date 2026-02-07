<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Facades\Image; // Import Intervention Image
use Illuminate\Support\Facades\Log;

class AdminKtpController extends Controller
{
    /**
     * Download PDF containing User Biodata and KTP Photo.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf()
    {
        // 1. Optimization: Increase Time & Memory Limit
        set_time_limit(600); // 10 Minutes
        ini_set('memory_limit', '1024M'); // 1GB

        // 2. Query Users
        $users = User::whereNotNull('ktp_photo_path')
            ->where('ktp_photo_path', '!=', '')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada data user dengan foto KTP yang ditemukan.');
        }

        // 3. Process Images (Resize & Compress)
        // We create a new collection of objects to pass to the view
        $optimizedUsers = [];

        foreach ($users as $user) {
            $base64Image = null;

            // Resolve Path (Check public storage first, then root storage)
            $path = $user->ktp_photo_path;
            $fullPath = storage_path('app/public/' . $path);

            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/' . $path);
            }

            if (file_exists($fullPath)) {
                try {
                    // Resize to max 500px width, Quality 50%
                    // This drastically reduces PDF size and memory usage during rendering
                    $img = Image::make($fullPath)
                        ->resize(500, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->encode('jpg', 50);

                    $base64Image = 'data:image/jpeg;base64,' . base64_encode($img);
                } catch (\Exception $e) {
                    Log::error("Gagal resize KTP User ID {$user->id}: " . $e->getMessage());
                    // Fallback: Don't show image or show placeholder if needed
                }
            }

            // Create generic object
            $userData = new \stdClass();
            $userData->name = $user->name;
            $userData->employee_id = $user->employee_id;
            $userData->position = $user->position;
            $userData->branch_name = $user->branch->name ?? '-';
            $userData->division_name = $user->division->name ?? '-';
            $userData->email = $user->email;
            $userData->ktp_base64 = $base64Image; // Pass Base64 string

            $optimizedUsers[] = $userData;
        }

        // 4. Load View PDF
        $pdf = Pdf::loadView('admin.ktp.pdf', [
            'users' => $optimizedUsers
        ]);

        // 5. PDF Settings (Compress = 1 is crucial)
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'compress' => 1, 'dpi' => 72]);

        return $pdf->download('Data_KTP_User_Compressed_' . date('d-m-Y') . '.pdf');
    }
}
