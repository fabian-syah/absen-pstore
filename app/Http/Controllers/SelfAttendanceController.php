<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    /**
     * Menampilkan Halaman Form Absen (Selfie)
     */
    /**
     * Menampilkan Halaman Form Absen (Selfie)
     */
    public function create()
    {
        $user = Auth::user();
        $today = today();

        // [MODIFIKASI LOGIKA]
        // Kita batasi pencarian sesi aktif.
        // Hanya cari sesi yang check_in-nya dilakukan HARI INI atau KEMARIN.
        // Sesi yang lebih tua dari kemarin (misal 2 hari lalu) akan diabaikan di sini,
        // sehingga user akan diarahkan ke mode 'Masuk' (dan sesi lama itu akan di-auto close di proses store).
        
        $yesterday = Carbon::yesterday()->startOfDay(); // Batas waktu: Kemarin jam 00:00

        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $yesterday) // <--- TAMBAHAN PENTING INI
            ->orderBy('check_in_time', 'desc')
            ->first();

        if ($activeSession) {
            // Jika ada sesi aktif (Hari ini atau Kemarin), paksa Absen Pulang
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Jika tidak ada sesi aktif (atau sesi gantungnya sudah terlalu lama/tua)
            
            // Cek apakah HARI INI sudah selesai absen (Masuk & Pulang)?
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Mode MASUK
            $mode = 'masuk';
            $attendance = null;

            // Cek Status Laporan Telat
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', $today)
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Anda memiliki laporan telat aktif. Harap hapus laporan tersebut di dashboard.');
            }
        }

        return view('user_biasa.absen', compact('mode', 'attendance'));
    }

    /**
     * Memproses Penyimpanan Absen (Masuk & Pulang)
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'photo' => 'required|image|max:51200',
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id', // [MODIFIKASI] Validasi ID absen jika ada
        ]);

        $user = Auth::user();
        $currentTime = now();

        // [MODIFIKASI UTAMA] Tentukan apakah ini Check-Out atau Check-In
        $attendanceToUpdate = null;

        // 1. Jika ada ID yang dikirim dari form (prioritas utama untuk kasus lintas hari)
        if ($request->has('attendance_id') && $request->attendance_id) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);

            // Pastikan user-nya benar dan belum checkout
            if ($attendanceToUpdate && ($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null)) {
                $attendanceToUpdate = null; // Invalid
            }
        }

        // 2. Jika tidak ada ID, cari sesi aktif HARI INI (fallback normal)
        if (!$attendanceToUpdate) {
            $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time')
                ->first();
        }

        // Simpan Foto
        $path = $request->file('photo')->store('public/foto_mandiri');

        // Ambil Jadwal Kerja
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);


        // ==============================================================
        // LOGIKA ABSEN PULANG (CHECK-OUT)
        // ==============================================================
        if ($attendanceToUpdate) {

            // Cek Pulang Cepat (Early Checkout)
            $isEarly = false;
            // Hanya cek early checkout jika tanggal check-in SAMA dengan hari ini
            // Jika lintas hari (check-in kemarin), otomatis tidak dianggap early checkout
            if ($attendanceToUpdate->check_in_time->isToday()) {
                if ($workSchedule && $workSchedule->check_out_start) {
                    $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                    $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));
                    if ($checkOutTimeOnly->lt($scheduleStart)) {
                        $isEarly = true;
                    }
                }
            }

            // Update Data (Menutup sesi)
            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => 'pending_verification', // Tetap butuh verifikasi
                // Update lokasi pulang jika perlu
                // 'latitude_out'   => $request->latitude,
                // 'longitude_out'  => $request->longitude,
            ]);

            $title = "Verifikasi Pulang";
            $body = "{$user->name} melakukan absen pulang (Mandiri).";
            $message = "Berhasil absen pulang. Hati-hati di jalan!";
        }

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN)
        // ==============================================================
        else {
            // --- [FITUR AUTO RESET] ---
            // Hanya dijalankan jika kita MEMANG melakukan Check-In baru.
            // Ini akan menutup sesi masa lalu yang BENAR-BENAR lupa (bukan yang sedang diproses di atas)
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', today()) // Tanggal sebelum hari ini
                ->get();

            foreach ($hangingSessions as $hanging) {
                $autoOutTime = Carbon::parse($hanging->check_in_time)->endOfDay();
                $hanging->update([
                    'check_out_time' => $autoOutTime,
                    'notes' => 'Auto-closed by system (Lupa Absen Pulang)',
                ]);
            }
            // --------------------------

            // Cek apakah sudah ada absen selesai hari ini (double check)
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Keterlambatan
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    $isLate = true;
                }
            }

            // Create Data Baru
            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification',
                'attendance_type'   => 'self',
                'photo_path'        => $path,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
            ]);

            $title = "Verifikasi Masuk";
            $body = "{$user->name} melakukan absen masuk (Mandiri).";
            $message = 'Berhasil absen masuk. Menunggu verifikasi Audit/Leader.';
        }

        // Kirim Notifikasi
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Menyimpan Laporan Izin Telat (SAMA SEPERTI SEBELUMNYA)
     */
    public function storeLateStatus(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);

        LateNotification::create([
            'user_id'   => $user->id,
            'branch_id' => $user->branch_id,
            'message'   => $request->message,
            'is_active' => true,
        ]);

        $title = "Izin Telat Masuk";
        $body = "{$user->name} dari Divisi " . ($user->division->name ?? 'N/A') . " mengajukan izin telat.";

        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error Late: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
    }

    /**
     * Menghapus Status Laporan Telat (SAMA SEPERTI SEBELUMNYA)
     */
    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->first();

        if ($notification) {
            $notification->delete();
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus. Anda sekarang bisa melakukan absen.');
        }

        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }

    /**
     * Memproses Lewati Absen Pulang (Force Close sesi kemarin)
     * Tanpa Foto, Tanpa Verifikasi
     */
    public function skipCheckOut($id)
    {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->first();

        if ($attendance) {
            // Set jam pulang otomatis ke 23:59:59 di hari check-in tersebut
            $autoOutTime = Carbon::parse($attendance->check_in_time)->endOfDay();

            $attendance->update([
                'check_out_time' => $autoOutTime,
                'photo_out_path' => null, // Pastikan null karena dilewati
                'notes'          => 'User lupa absen pulang (Sesi ditutup via tombol Lewati)',
            ]);

            return redirect()->route('dashboard')->with('success', 'Sesi kemarin ditutup (Lupa Absen). Silakan Absen Masuk untuk hari ini.');
        }

        return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
    }
}
