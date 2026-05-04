<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendAuditNotificationJob;

class SelfAttendanceController extends Controller
{
    // use SendFcmNotification;

    /**
     * Helper untuk mendapatkan Offset Timezone (contoh: +07:00)
     */
    private function getOffset($timezone)
    {
        return Carbon::now($timezone)->format('P');
    }

    /**
     * Halaman Form Absen (Kamera)
     */
    public function create()
    {
        $user = Auth::user();

        // Setup Timezone Cabang
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        $todayLocal = $localTime->copy()->startOfDay();

        // 1. CEK BLOKIR (SECURITY ONLY)
        if ($user->only_security_scan) {
            return redirect()->route('dashboard')->with('error', 'AKSES DITOLAK: Akun Anda diatur hanya boleh absen melalui Scan Security (QR Code).');
        }

        // 2. CEK SESI AKTIF (Termasuk Lembur Lintas Hari)
        // Cari sesi yang belum checkout dan check_in dalam batas wajar (24 jam terakhir)
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $localTime->copy()->subHours(24)) // Gunakan localTime (Peningkatan range ke 24jam)
            ->where('check_in_time', '<=', $localTime) // Gunakan localTime
            ->where('status', '!=', 'alpha')
            ->where('status', '!=', 'rejected') // <--- FIX: Jangan anggap sesi rejected sebagai sesi aktif
            ->where('attendance_type', '!=', 'leave')
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            // Jika ada sesi aktif -> Mode PULANG (Otomatis Lembur jika lewat hari)
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // === MODE MASUK ===

            /* [REMOVED] Cooldown Lintas Hari (Requested: Urgensi Karyawan Lanjut Shift)
            $lastRecentCheckout = Attendance::where('user_id', $user->id)
                ->whereNotNull('check_out_time')
                ->where('check_out_time', '>=', $localTime->copy()->subHours(4))
                ->latest('check_out_time')
                ->first();
...
            }
            */

            // 3. Cek Status Cuti / Izin (Selain Telat)
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->where('is_active', true)
                ->whereDate('start_date', '<=', $todayLocal)
                ->whereDate('end_date', '>=', $todayLocal)
                ->first();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', "Anda sedang status " . strtoupper($isOnLeave->type) . " hari ini. Tidak perlu absen.");
            }

            // 4. Cek apakah sudah selesai absen hari ini (Lokal Time)
            // Menggunakan Offset dari Config App sebagai Source, dan Offset Cabang sebagai Target
            $branchOffset = $this->getOffset($branchTimezone);
            $storageOffset = Carbon::now(config('app.timezone'))->format('P');

            $finishedRecently = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $todayLocal->format('Y-m-d')])
                ->whereNotNull('check_out_time')
                ->where('check_out_time', '>=', $localTime->copy()->subMinutes(5)) // Cooldown 5 menit setelah pulang baru boleh masuk lagi
                ->where('status', '!=', 'alpha')
                ->exists();

            if ($finishedRecently) {
                return redirect()->route('dashboard')->with('success', 'Anda baru saja menyelesaikan absensi. Mohon tunggu beberapa saat jika ingin memulai shift baru.');
            }

            // 5. Cek Laporan Telat (LateNotification)
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', today())
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Anda masih memiliki status Laporan Telat aktif. Silahkan hapus laporan di dashboard sebelum absen masuk.');
            }

            $mode = 'masuk';
            $attendance = null;
        }

        // [PENTING] Kirim $branchTimezone ke View agar jam di UI sesuai lokasi
        return view('user_biasa.absen', compact('mode', 'attendance', 'branchTimezone'));
    }

    /**
     * Proses Simpan Data Absensi (Masuk / Pulang)
     */
    public function store(Request $request)
    {
        // Security Check Layer 2
        if (Auth::user()->only_security_scan) {
            return redirect()->route('dashboard')->with('error', 'AKSES DITOLAK: Anda hanya boleh absen melalui Scan Security.');
        }

        $request->validate([
            'photo' => 'required|image|max:51200', // Max 50MB
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $currentTime = now(); // Waktu Server

        // Setup Timezone
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        $todayDateLocal = $localTime->format('Y-m-d');

        $branchName = $user->branch?->name ?? '-';
        $attendanceToUpdate = null;

        // A. Cek apakah ini request PULANG (Update Sesi)
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }

        // Fallback jika ID tidak dikirim tapi mode pulang
            $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', $localTime->copy()->subHours(24))
                ->where('status', '!=', 'rejected') // <--- FIX: Jangan ambil yang sudah ditolak
                ->where('attendance_type', '!=', 'leave')
                ->latest('check_in_time')
                ->first();

        // --- PROSES KOMPRESI GAMBAR ---
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'public/foto_mandiri/' . Str::random(40) . '.jpg';

            // Di SelfAttendanceController.php
            $img = Image::make($file);
            $img->orientate();

            // Hanya resize jika gambar aslinya lebih besar dari 1000px
            $img->resize(1000, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Menghindari gambar kecil ditarik jadi besar
            });

            // Naikkan ke 80 agar tidak terlalu banyak artifak kompresi
            $compressedImage = (string) $img->encode('jpg', 80);
            Storage::disk('public')->put($filename, $compressedImage);
            $path = $filename;
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);
        $shouldSendNotif = false;
        $notifTitle = "";
        $notifBody = "";

        // =====================================================================
        // CASE 1: ABSEN PULANG (UPDATE)
        // =====================================================================
        if ($attendanceToUpdate) {

            if ($attendanceToUpdate->user_id != $user->id) {
                return redirect()->route('dashboard')->with('error', 'Sesi absensi tidak valid.');
            }

            // Cek Pulang Cepat (Early Checkout)
            $isEarly = false;
            if ($workSchedule && $workSchedule->check_out_start) {
                $scheduleStartStr = Carbon::parse($workSchedule->check_out_start)->format('H:i:s');
                $scheduleStartLocal = Carbon::createFromFormat('Y-m-d H:i:s', $localTime->format('Y-m-d') . ' ' . $scheduleStartStr, $branchTimezone);

                if ($localTime->lt($scheduleStartLocal)) {
                    $isEarly = true;
                }
            }

            // Logic Notes
            $finalNotes = $attendanceToUpdate->notes;
            $userNote = $request->notes ? ": " . $request->notes : "";

            // Cek Lembur Lintas Hari (Check In Kemarin, Pulang Hari Ini)
            $checkInLocal = Carbon::parse($attendanceToUpdate->check_in_time)->timezone($branchTimezone);
            $isCrossDay = !$checkInLocal->isSameDay($localTime);

            $currentStatus = $attendanceToUpdate->status;
            $newStatus = $currentStatus;

            // Aturan Status:
            // 1. Jika sebelumnya rejected/alpha -> ubah jadi pending verification.
            if (in_array($currentStatus, ['rejected', 'alpha'])) {
                $newStatus = 'pending_verification';
            }

            // 2. Auto Verify untuk Lembur Lintas Hari jika sebelumnya sudah Verified
            if ($isCrossDay) {
                $extraNote = "[Lembur/Lintas Hari] ";
                // Jika sudah verified (biasanya karena selfie masuk), maka checkout juga verified
                if ($currentStatus == 'verified') {
                    $newStatus = 'verified';
                }
            } else {
                $extraNote = "";
            }

            $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . $extraNote . "Pulang (Selfie)" . $userNote;

            $attendanceToUpdate->update([
                'check_out_time' => $currentTime,
                'photo_out_path' => $path,
                'is_early_checkout' => $isEarly,
                'status' => $newStatus,
                'notes' => $finalNotes,
                'latitude_out' => $request->latitude,
                'longitude_out' => $request->longitude,
            ]);

            $message = "Berhasil absen pulang.";
            if ($isCrossDay) {
                $message = "Konfirmasi Lembur Berhasil. Absen pulang tercatat.";
            }

            if ($newStatus == 'pending_verification') {
                $shouldSendNotif = true;
                $notifTitle = "Verifikasi Pulang";
                $notifBody = "{$user->name} absen pulang di {$branchName}";
            }

        }
        // =====================================================================
        // CASE 2: ABSEN MASUK (CREATE BARU)
        // =====================================================================
        else {
            $checkAgain = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', now()->subHours(24))
                ->where('check_in_time', '<=', now()) // Abaikan record masa depan (cuti dll)
                ->where('status', '!=', 'alpha') // Jangan block oleh record Alpha otomatis
                ->where('status', '!=', 'rejected') // <--- FIX: Jangan block oleh record yang sudah ditolak Audit
                ->where('attendance_type', '!=', 'leave') // Jangan block oleh record izin/cuti
                ->first();

            if ($checkAgain) {
                return redirect()->route('dashboard')->with('error', 'Anda masih memiliki sesi aktif. Mohon refresh halaman.');
            }

            $storageOffset = Carbon::now(config('app.timezone'))->format('P');
            $branchOffset = $this->getOffset($branchTimezone);

            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEndStr = Carbon::parse($workSchedule->check_in_end)->format('H:i:s');
                $scheduleEndLocal = Carbon::createFromFormat('Y-m-d H:i:s', $todayDateLocal . ' ' . $scheduleEndStr, $branchTimezone);

                if ($localTime->gt($scheduleEndLocal)) {
                    $isLate = true;
                }
            }

            $latePermission = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'telat')
                ->where('status', 'approved')
                ->whereDate('start_date', $todayDateLocal)
                ->first();

            $finalNotes = $request->notes;

            if ($latePermission) {
                $reasonNote = "[Izin Telat Approved: " . $latePermission->reason . "]";
                $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . $reasonNote;
            }

            // Tentukan presence_status berdasarkan izin telat
            $presenceStatus = $latePermission ? 'Izin Telat' : 'Masuk';

            $snapIn = $user->check_in_start;
            if (!$snapIn && $workSchedule)
                $snapIn = $workSchedule->check_in_start;
            $snapOut = $user->check_out_start;
            if (!$snapOut && $workSchedule)
                $snapOut = $workSchedule->check_out_start;

            // FIX: Cek apakah hari ini sudah ada record attendance (apapun jenisnya)
            // Jika ada, UPDATE record tersebut, jangan create baru (biar tidak double)
            $existingAttendanceToday = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $todayDateLocal])
                ->first();

            if ($existingAttendanceToday) {
                // Jika sudah ada jam pulang di record ini, jangan update record ini tapi izinkan buat baru (Shift Baru)
                if ($existingAttendanceToday->check_out_time != null) {
                    $existingAttendanceToday = null; 
                }
            }

            if ($existingAttendanceToday) {

                $existingAttendanceToday->update([
                    'check_in_time' => $currentTime, // Ambil jam asli selfie
                    'photo_path' => $path,           // Simpan foto selfie
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'attendance_type' => 'self',      // Ubah tipe jadi self
                    'status' => 'pending_verification',
                    // Preserve presence_status if it was already set (e.g. Izin Telat)
                    'presence_status' => $existingAttendanceToday->presence_status ?: 'Masuk',
                    'notes' => ($existingAttendanceToday->notes ? $existingAttendanceToday->notes . " | " : "") . "[Selfie: " . ($request->notes ?? 'Tanpa catatan') . "]",
                ]);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'branch_id' => $user->branch_id,
                    'check_in_time' => $currentTime,
                    'status' => 'pending_verification',
                    'presence_status' => $presenceStatus,
                    'attendance_type' => 'self',
                    'photo_path' => $path,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'work_schedule_id' => $workSchedule?->id,
                    'is_late_checkin' => $isLate || (bool) $latePermission,
                    'notes' => $finalNotes,
                    'verified_by_user_id' => null,
                    'scheduled_check_in' => $snapIn,
                    'scheduled_check_out' => $snapOut,
                ]);
            }

            $message = 'Berhasil absen masuk. Menunggu verifikasi.';
            $shouldSendNotif = true;
            $notifTitle = "Verifikasi Masuk";
            $notifBody = "{$user->name} absen masuk (Selfie) di {$branchName}";
        }

        // <-- TAMBAHKAN INI DI AWAL
        if ($shouldSendNotif) {
            try {
                // Gunakan class khusus untuk kirim langsung (bukan Job)
                $notifier = new class { use \App\Traits\SendWebPushNotification; };
                $notifier->sendWebPushToBranchRoles(['audit', 'admin'], $user->branch_id, $notifTitle, $notifBody, url('/verifikasi/absensi'));
            } catch (\Exception $e) {
                \Log::error("Web Push Error: " . $e->getMessage());
            }
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function skipCheckOut($id)
    {
        $user = Auth::user();
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->first();

        if ($attendance) {
            $endOfPreviousDay = Carbon::parse($attendance->check_in_time)->endOfDay();

            $status = ($attendance->status == 'verified' || $attendance->status == 'present')
                ? $attendance->status
                : 'pending_verification';

            $attendance->update([
                'check_out_time' => $endOfPreviousDay,
                'photo_out_path' => null,
                'status' => $status,
                'notes' => $attendance->notes . ' | User LEWATI absen pulang (Lupa/Skip)',
            ]);
            return redirect()->route('dashboard')->with('success', 'Sesi kemarin ditutup. Anda bisa absen masuk baru sekarang.');
        }
        return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
    }

    public function storeLateStatus(Request $request)
    {
        $request->validate(['message' => 'required|string|max:255']);
        $user = Auth::user();

        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);

        LateNotification::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'message' => $request->message,
            'is_active' => true,
        ]);

        /* MATIKAN JUGA YANG INI
        try {
            $this->sendNotificationToBranchRoles(['audit', 'admin'], $user->branch_id, "Laporan Telat", "{$user->name} melaporkan akan terlambat.");
        } catch (\Exception $e) {}
        */

        return redirect()->route('dashboard')->with('success', 'Laporan telat dikirim.');
    }

    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->first();

        if ($notification) {
            $notification->delete();
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus. Silahkan absen.');
        }
        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }

    /**
     * Lupa Absen Pulang (Tanpa Foto - Hanya untuk Lintas Hari / Lembur)
     */
    public function manualCheckOut(Request $request)
    {
        $user = Auth::user();
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $localNow = Carbon::now($branchTimezone);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $localNow->copy()->subHours(48)) 
            ->latest('check_in_time')
            ->first();

        if (!$attendance) {
            return redirect()->route('dashboard')->with('error', 'Tidak ada sesi aktif yang ditemukan.');
        }

        // --- LOGIKA BARU: Jam Masuk + 8 Jam ---
        $checkInTime = Carbon::parse($attendance->check_in_time);
        $checkOutTime = $checkInTime->copy()->addHours(8);

        // Catatan otomatis sesuai permintaan user
        $autoNote = "saya lupa absen pulang maaf gak ada foto";
        $notes = ($attendance->notes ? $attendance->notes . " | " : "") . $autoNote;

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'notes' => $notes,
            'status' => 'verified', // Langsung verified sesuai sistem PStore
            'verified_at' => now(),
            'verified_by_user_id' => Auth::id(), 
        ]);

        return redirect()->route('dashboard')->with('success', 'Absen pulang manual berhasil diproses (Jam Masuk + 8 Jam).');
    }
}
