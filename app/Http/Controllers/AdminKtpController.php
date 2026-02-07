<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User; // Just in case
use Illuminate\Support\Str;

class AdminKtpController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.ktp.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|max:20', // NIK from OCR (hidden input)
            'ktp_image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        try {
            // 1. Handle File Upload
            if ($request->hasFile('ktp_image')) {
                $file = $request->file('ktp_image');
                $filename = 'KTP_' . Str::slug($request->nama_lengkap) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/ktp_uploads', $filename);
                $fullPath = storage_path('app/' . $path);
            } else {
                return back()->with('error', 'Gagal mengupload gambar KTP.');
            }

            // 2. Prepare Data for Excel
            $data = [
                'Nama Lengkap' => $request->nama_lengkap,
                'NIK' => $request->nik,
                'File Path' => $path,
                'Waktu Upload' => now()->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            ];

            // 3. Save to CSV/Excel (Simple Append)
            $csvPath = storage_path('app/public/data_admin_ktp.csv');

            // Add header if file doesn't exist
            if (!file_exists($csvPath)) {
                $header = implode(',', array_keys($data)) . "\n";
                file_put_contents($csvPath, $header);
            }

            // Append data
            $csvData = implode(',', array_map(function ($item) {
                return '"' . str_replace('"', '""', $item) . '"'; // Escape quotes
            }, array_values($data))) . "\n";

            file_put_contents($csvPath, $csvData, FILE_APPEND);

            return redirect()->route('admin.ktp.create')->with('success', 'Data berhasil disimpan dan diexport ke CSV! NIK: ' . $request->nik);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
