<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminKtpController extends Controller
{
    /**
     * Download PDF containing User Biodata and KTP Photo.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf()
    {
        // 1. Ambil user yang punya foto KTP
        // Filter: Active users only? Or all users? Defaulting to active for now.
        $users = User::whereNotNull('ktp_photo_path')
            ->where('ktp_photo_path', '!=', '')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada data user dengan foto KTP yang ditemukan.');
        }

        // 2. Load View PDF
        $pdf = Pdf::loadView('admin.ktp.pdf', [
            'users' => $users
        ]);

        // 3. Set Paper Size & Orientation (A4 Portrait)
        $pdf->setPaper('A4', 'portrait');

        // 4. Download
        return $pdf->download('Data_KTP_User_' . date('d-m-Y') . '.pdf');
    }
}
