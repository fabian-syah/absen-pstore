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

        // Ambil semua data absensi pada tanggal tersebut (Kecuali yang sudah ditolak/dihapus admin)
        $attendances = Attendance::with(['user', 'branch'])
            ->whereDate('check_in_time', $date)
            ->where('status', '!=', 'rejected')
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

    // Fungsi: Hapus Permanen Data Absensi
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        // 1. HAPUS FOTO DARI STORAGE (AGAR TIDAK JADI SAMPAH)
        try {
            if ($attendance->photo_path && Storage::disk('public')->exists($attendance->photo_path)) {
                Storage::disk('public')->delete($attendance->photo_path);
            }
            if ($attendance->photo_out_path && Storage::disk('public')->exists($attendance->photo_out_path)) {
                Storage::disk('public')->delete($attendance->photo_out_path);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal hapus foto saat admin koreksi dev: " . $e->getMessage());
        }

        // 2. HAPUS PENGAJUAN IZIN TERKAIT (WFH / DINAS / CUTI)
        // [SYNC CUTI] Hapus pengajuan cuti jika ada agar saldo kembali
        $this->syncWithLeaveRequest($attendance);

        // [SYNC WFH/DINAS] Jika data absen ini adalah WFH atau Dinas, hapus pengajuannya juga agar hilang dari history
        if (str_contains(strtolower($attendance->presence_status ?? ''), 'wfh') || str_contains(strtolower($attendance->presence_status ?? ''), 'dinas')) {
            $checkInDate = $attendance->check_in_time->format('Y-m-d');
            \App\Models\LeaveRequest::where('user_id', $attendance->user_id)
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
                })
                ->delete();
        }

        // 3. HAPUS PERMANEN DATA ABSENSI
        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi telah dihapus secara permanen.');
    }

    /**
     * Helper untuk sinkronisasi dengan tabel LeaveRequest agar saldo cuti terupdate
     */
    private function syncWithLeaveRequest($attendance)
    {
        $date = \Carbon\Carbon::parse($attendance->check_in_time)->format('Y-m-d');
        $userId = $attendance->user_id;

        // Karena ini fungsi delete/destroy, kita asumsikan tujuannya adalah membatalkan cuti jika ada
        \App\Models\LeaveRequest::where('user_id', $userId)
            ->where('type', 'cuti')
            ->where(function ($q) use ($date) {
                $q->whereDate('start_date', $date)
                    ->orWhere(function ($q2) use ($date) {
                        $q2->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date);
                    });
            })
            ->delete();
    }
}