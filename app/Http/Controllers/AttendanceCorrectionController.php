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

        // ====== CANCEL TERKAIT LEAVE REQUEST ======
        // Jika data absen yang dihapus adalah WFH atau Dinas
        if (str_contains(strtolower($attendance->presence_status ?? ''), 'wfh') || str_contains(strtolower($attendance->presence_status ?? ''), 'dinas')) {
            $checkInDate = $attendance->check_in_time->format('Y-m-d');

            // Cari pengajuan izin yang "approved" milik user pada tanggal absen tersebut
            $matchingLeaveRequests = \App\Models\LeaveRequest::where('user_id', $attendance->user_id)
                ->where('status', 'approved')
                ->where(function ($q) use ($checkInDate) {
                    $q->where(function ($subQ) use ($checkInDate) {
                        // Jika ada end_date, cek dalam range
                        $subQ->whereNotNull('end_date')
                            ->whereDate('start_date', '<=', $checkInDate)
                            ->whereDate('end_date', '>=', $checkInDate);
                    })->orWhere(function ($subQ) use ($checkInDate) {
                        // Jika end_date null (seperti telat/izin harian tanpa range), cocokkan dengan start_date
                        $subQ->whereNull('end_date')
                            ->whereDate('start_date', $checkInDate);
                    });
                })->get();

            foreach ($matchingLeaveRequests as $lr) {
                // Batalkan (cancel) agar tidak lagi muncul di history aktif
                $lr->update([
                    'status' => 'cancelled',
                    'is_active' => false,
                    'rejection_reason' => 'Dibatalkan otomatis karena data absen dihapus oleh Admin.'
                ]);
            }
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus permanen.');
    }
}