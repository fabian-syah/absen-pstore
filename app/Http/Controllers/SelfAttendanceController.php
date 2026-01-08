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

class SelfAttendanceController extends Controller
{
    // use SendFcmNotification;

    /**
     * Helper untuk mendapatkan Offset Timezone (contoh: +07:00)
     */
    private function getOffset($timezone) {
        return Carbon::now($timezone)->format('P');
    }

    /**
     * Halaman Form Absen (Kamera)
     */
    public function create()
    {
        $user = Auth::user();
        
        // Setup Timezone Cabang
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        $todayLocal = $localTime->copy()->startOfDay();

        // 1. CEK BLOKIR (SECURITY ONLY)
        if ($user->only_security_scan) {
            return redirect()->route('dashboard')->with('error', 'AKSES DITOLAK: Akun Anda diatur hanya boleh absen melalui Scan Security (QR Code).');
        }

        // 2. CEK SESI AKTIF (Termasuk Lembur Lintas Hari)
        // Cari sesi yang belum checkout dan check_in dalam batas wajar (32 jam terakhir)
        $activeSession = Attendance::where('user_id', $user->id)
    ->whereNull('check_out_time')
    // UBAH dari subHours(24) menjadi 32
    ->where('check_in_time', '>=', now()->subHours(32)) 
    ->where('status', '!=', 'alpha')
    ->latest('check_in_time')
    ->first();

        if ($activeSession) {
            // Jika ada sesi aktif -> Mode PULANG (Otomatis Lembur jika lewat hari)
            $mode = 'pulang';
            $attendance = $activeSession;
        } 
        else {
            // === MODE MASUK ===
            
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
            $appOffset = $this->getOffset(config('app.timezone'));
            $branchOffset = $this->getOffset($branchTimezone);

            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$appOffset, $branchOffset, $todayLocal->format('Y-m-d')])
                ->whereNotNull('check_out_time')
                ->where('status', '!=', 'alpha')
                ->exists();
            
            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
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
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        $todayDateLocal = $localTime->format('Y-m-d');
        
        $branchName = $user->branch->name ?? '-';
        $attendanceToUpdate = null;

        // A. Cek apakah ini request PULANG (Update Sesi)
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }
        
        // Fallback jika ID tidak dikirim tapi mode pulang
        if (!$attendanceToUpdate && $request->has('mode') && $request->mode == 'pulang') {
             $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', now()->subHours(32))
                ->latest('check_in_time')
                ->first();
        }

        // --- PROSES KOMPRESI GAMBAR ---
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'public/foto_mandiri/' . Str::random(40) . '.jpg';
            
            // Resize & Compress
            $img = Image::make($file);
            $img->orientate();
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            $compressedImage = (string) $img->encode('jpg', 70);
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
            
            if($attendanceToUpdate->user_id != $user->id){
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
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => $newStatus,
                'notes'             => $finalNotes,
                'latitude_out'      => $request->latitude,
                'longitude_out'     => $request->longitude,
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
    // Pastikan pengecekan masuk baru juga konsisten 32 jam
    ->where('check_in_time', '>=', now()->subHours(32))
    ->first();
            
            if ($checkAgain) {
                 return redirect()->route('dashboard')->with('error', 'Anda masih memiliki sesi aktif. Mohon refresh halaman.');
            }

            $appOffset = $this->getOffset(config('app.timezone'));
            $branchOffset = $this->getOffset($branchTimezone);

            $existingSessionToday = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$appOffset, $branchOffset, $todayDateLocal])
                ->where('status', '!=', 'alpha')
                ->first();

            if ($existingSessionToday) {
                if ($existingSessionToday->check_out_time == null) {
                    return redirect()->route('dashboard')->with('warning', 'Sesi aktif terdeteksi. Silakan refresh halaman.');
                } else {
                    return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
                }
            }

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

            $snapIn = $user->check_in_start;
            if (!$snapIn && $workSchedule) $snapIn = $workSchedule->check_in_start;
            $snapOut = $user->check_out_start;
            if (!$snapOut && $workSchedule) $snapOut = $workSchedule->check_out_start;

            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification',
                'presence_status'   => 'Masuk',
                'attendance_type'   => 'self',
                'photo_path'        => $path,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
                'notes'             => $finalNotes,
                'verified_by_user_id' => null, 
                'scheduled_check_in' => $snapIn,
                'scheduled_check_out' => $snapOut,
            ]);

            $message = 'Berhasil absen masuk. Menunggu verifikasi.';
            $shouldSendNotif = true;
            $notifTitle = "Verifikasi Masuk";
            $notifBody = "{$user->name} absen masuk (Selfie) di {$branchName}";
        }

        if ($shouldSendNotif) {
            try {
                $this->sendNotificationToBranchRoles(['audit', 'admin'], $user->branch_id, $notifTitle, $notifBody);
            } catch (\Exception $e) {
                // Log::error("FCM Error: " . $e->getMessage());
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
                'status'         => $status,
                'notes'          => $attendance->notes . ' | User LEWATI absen pulang (Lupa/Skip)',
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

        try {
            $this->sendNotificationToBranchRoles(['audit', 'admin'], $user->branch_id, "Laporan Telat", "{$user->name} melaporkan akan terlambat.");
        } catch (\Exception $e) {}

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
}