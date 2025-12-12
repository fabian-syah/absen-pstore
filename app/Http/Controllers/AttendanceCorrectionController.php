<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data absensi hari ini secara default, atau sesuai filter tanggal
        $date = $request->input('date', date('Y-m-d'));

        $attendances = Attendance::with(['user', 'branch'])
            ->whereDate('check_in_time', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.correction.index', compact('attendances', 'date'));
    }

    // Fungsi untuk menghapus JAM PULANG saja (Kasus: Kepencet pulang padahal baru masuk)
    public function resetCheckout($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hapus foto pulang jika ada (opsional, biar bersih)
        if ($attendance->photo_out_path) {
            Storage::disk('public')->delete($attendance->photo_out_path);
        }

        // Set kolom terkait kepulangan menjadi NULL
        $attendance->update([
            'check_out_time'    => null,
            'photo_out_path'    => null,
            'latitude_out'      => null,
            'longitude_out'     => null,
            'is_early_checkout' => false, // Reset status pulang cepat
        ]);

        return redirect()->back()->with('success', 'Data Checkout berhasil di-reset. User bisa absen pulang kembali.');
    }

    // Fungsi Hapus TOTAL (Satu baris hilang)
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hapus file foto check in
        if ($attendance->photo_path) {
            Storage::disk('public')->delete($attendance->photo_path);
        }
        // Hapus file foto check out
        if ($attendance->photo_out_path) {
            Storage::disk('public')->delete($attendance->photo_out_path);
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus permanen.');
    }
}