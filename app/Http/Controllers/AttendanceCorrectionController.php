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

    // Fungsi: Tolak Data Absen (Dulu Hapus Permanen)
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();

        // --- OPTIMASI PENYIMPANAN: Perkecil foto jika ada (untuk history) ---
        try {
            // Foto Masuk
            if ($attendance->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_path)) {
                $pathIn = \Illuminate\Support\Facades\Storage::disk('public')->path($attendance->photo_path);
                $imgIn = \Intervention\Image\Facades\Image::make($pathIn);
                $imgIn->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $compressedIn = (string) $imgIn->encode('jpg', 60);
                \Illuminate\Support\Facades\Storage::disk('public')->put($attendance->photo_path, $compressedIn);
            }

            // Foto Pulang
            if ($attendance->photo_out_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path)) {
                $pathOut = \Illuminate\Support\Facades\Storage::disk('public')->path($attendance->photo_out_path);
                $imgOut = \Intervention\Image\Facades\Image::make($pathOut);
                $imgOut->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $compressedOut = (string) $imgOut->encode('jpg', 60);
                \Illuminate\Support\Facades\Storage::disk('public')->put($attendance->photo_out_path, $compressedOut);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal optimasi foto reject (Admin): " . $e->getMessage());
        }

        // ====== CANCEL TERKAIT LEAVE REQUEST ======
        // Jika data absen yang ditolak adalah WFH atau Dinas
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
                    'rejection_reason' => 'Ditolak otomatis karena data absen dikoreksi oleh Admin.'
                ]);
            }
        }

        // Update status menjadi rejected (Logical Delete for History)
        $attendance->update([
            'status' => 'rejected',
            'verified_by_user_id' => $user->id,
            'audit_note' => 'Rejected/Deleted by Admin ' . $user->name,
            'rejected_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Data absensi telah ditolak dan dipindahkan ke History Ditolak.');
    }
}