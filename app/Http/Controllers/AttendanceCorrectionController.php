<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request)
    {
        // Filter tanggal, default hari ini
        $date = $request->input('date', date('Y-m-d'));

        // Ambil semua data absensi pada tanggal tersebut
        $attendances = Attendance::with(['user', 'branch'])
            ->whereDate('check_in_time', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pisahkan data Masuk (Kantor/Scan) dan WFH
        $attendancesWfh = $attendances->filter(function ($item) {
            return str_contains(strtolower($item->presence_status ?? ''), 'wfh') || str_contains(strtolower($item->presence_status ?? ''), 'dinas');
        });

        $attendancesOffice = $attendances->filter(function ($item) {
            return !str_contains(strtolower($item->presence_status ?? ''), 'wfh') && !str_contains(strtolower($item->presence_status ?? ''), 'dinas');
        });

        return view('admin.correction.index', compact('attendancesOffice', 'attendancesWfh', 'date'));
    }

    // Fungsi: Reset Checkout (User bisa absen pulang ulang)
    public function resetCheckout($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hapus foto pulang fisik jika ada
        if ($attendance->photo_out_path) {
            if (Storage::disk('public')->exists($attendance->photo_out_path)) {
                Storage::disk('public')->delete($attendance->photo_out_path);
            }
        }

        // Reset kolom database ke NULL
        $attendance->update([
            'check_out_time' => null,
            'photo_out_path' => null,
            'latitude_out' => null,
            'longitude_out' => null,
            'is_early_checkout' => false,
        ]);

        return redirect()->back()->with('success', 'Jam pulang berhasil di-reset. User dapat melakukan checkout ulang.');
    }

    // Fungsi: Hapus Data Absen Permanen
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hapus Foto Masuk
        if ($attendance->photo_path) {
            if (Storage::disk('public')->exists($attendance->photo_path)) {
                Storage::disk('public')->delete($attendance->photo_path);
            }
        }

        // Hapus Foto Pulang
        if ($attendance->photo_out_path) {
            if (Storage::disk('public')->exists($attendance->photo_out_path)) {
                Storage::disk('public')->delete($attendance->photo_out_path);
            }
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus permanen.');
    }
}