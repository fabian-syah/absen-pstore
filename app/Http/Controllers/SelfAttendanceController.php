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
use DB;

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    private function getOffset($timezone) {
        return Carbon::now($timezone)->format('P');
    }

    public function create()
    {
        $user = Auth::user();
        
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        $todayLocal = $localTime->copy()->startOfDay();

        // CEK BLOKIR
        if ($user->only_security_scan) {
            return redirect()->route('dashboard')->with('error', 'AKSES DITOLAK: Akun Anda diatur hanya boleh absen melalui Scan Security (QR Code).');
        }

        // 1. CEK SESI AKTIF (Termasuk Lembur)
        // Sesi yang belum checkout dan check_in >= 32 jam lalu (batas wajar)
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', now()->subHours(32)) 
            ->where('status', '!=', 'alpha')
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            $mode = 'pulang';
            $attendance = $activeSession;
        } 
        else {
            // === MODE MASUK ===
            
            // Cek Cuti
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $todayLocal)
                ->whereDate('end_date', '>=', $todayLocal)
                ->first();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', "Anda sedang status " . strtoupper($isOnLeave->type) . " hari ini.");
            }

            // Cek sudah selesai hari ini (Lokal)
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, '+07:00', ?)) = ?", [$this->getOffset($branchTimezone), $todayLocal->format('Y-m-d')])
                ->whereNotNull('check_out_time')
                ->where('status', '!=', 'alpha')
                ->exists();
            
            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Laporan Telat
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', today())
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Hapus laporan telat dulu sebelum absen.');
            }

            $mode = 'masuk';
            $attendance = null;
        }

        return view('user_biasa.absen', compact('mode', 'attendance'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->only_security_scan) {
            return redirect()->route('dashboard')->with('error', 'AKSES DITOLAK: Anda hanya boleh absen melalui Scan Security.');
        }

        $request->validate([
            'photo' => 'required|image|max:51200',
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $currentTime = now(); 
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        
        $branchName = $user->branch->name ?? '-';
        $attendanceToUpdate = null;

        // A. Ambil ID dari Form (Prioritas Utama untuk Checkout Lembur)
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }
        
        // B. Fallback
        if (!$attendanceToUpdate && $request->has('mode') && $request->mode == 'pulang') {
             $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', now()->subHours(32))
                ->latest('check_in_time')
                ->first();
        }

        // PROCESS IMAGE
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'public/foto_mandiri/' . Str::random(40) . '.jpg';
            
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
        // LOGIKA ABSEN PULANG (UPDATE)
        // =====================================================================
        if ($attendanceToUpdate) {
            
            if($attendanceToUpdate->user_id != $user->id){
                 return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
            }

            $isEarly = false;
            if ($workSchedule && $workSchedule->check_out_start) {
                 $scheduleStartStr = Carbon::parse($workSchedule->check_out_start)->format('H:i:s');
                 $scheduleStartLocal = Carbon::createFromFormat('Y-m-d H:i:s', $localTime->format('Y-m-d') . ' ' . $scheduleStartStr, $branchTimezone);
                 
                 if ($localTime->lt($scheduleStartLocal)) {
                     $isEarly = true;
                 }
            }

            // Notes Logic
            $finalNotes = $attendanceToUpdate->notes;
            $userNote = $request->notes ? ": " . $request->notes : "";
            
            // Cek apakah ini Lembur Lintas Hari?
            $checkInLocal = Carbon::parse($attendanceToUpdate->check_in_time)->timezone($branchTimezone);
            $isCrossDay = !$checkInLocal->isSameDay($localTime);

            $currentStatus = $attendanceToUpdate->status;
            $newStatus = $currentStatus; 

            // LOGIKA STATUS:
            // 1. Jika sebelumnya rejected/alpha -> ubah jadi pending.
            if (in_array($currentStatus, ['rejected', 'alpha'])) {
                $newStatus = 'pending_verification';
            }
            // 2. [PERMINTAAN KHUSUS] Jika Lembur & Sebelumnya 'verified' -> Tetap 'verified' (Auto Verify)
            if ($isCrossDay) {
                $extraNote = "[Lembur/Lintas Hari] ";
                // Jika status sebelumnya verified, maka pulang lembur juga verified.
                // Jika pending, tetap pending.
                // Jika request meminta "verif ulang jika lembur dan statusnya terverifikasi", maka:
                if ($currentStatus == 'verified') {
                    $newStatus = 'verified'; // Tetap verified
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

            // Notif hanya jika status pending / berubah jadi pending
            if ($newStatus == 'pending_verification') {
                $shouldSendNotif = true;
                $notifTitle = "Verifikasi Pulang";
                $notifBody = "{$user->name} absen pulang di {$branchName}";
            }

        } 
        // =====================================================================
        // LOGIKA ABSEN MASUK (CREATE BARU)
        // =====================================================================
        else {
            // Cek ulang sesi aktif
            $checkAgain = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', now()->subHours(32))
                ->first();
            
            if ($checkAgain) {
                 return redirect()->route('dashboard')->with('error', 'Anda masih memiliki sesi aktif. Refresh halaman.');
            }

            // Cek Absen Harian (Lokal) - Double check
            $existingSessionToday = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, '+07:00', ?)) = ?", [$this->getOffset($branchTimezone), $localTime->format('Y-m-d')])
                ->where('status', '!=', 'alpha')
                ->first();

            if ($existingSessionToday) {
                if ($existingSessionToday->check_out_time == null) {
                    return redirect()->route('dashboard')->with('warning', 'Terdeteksi sesi aktif hari ini. Silakan refresh halaman.');
                } else {
                    return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
                }
            }

            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEndStr = Carbon::parse($workSchedule->check_in_end)->format('H:i:s');
                $scheduleEndLocal = Carbon::createFromFormat('Y-m-d H:i:s', $localTime->format('Y-m-d') . ' ' . $scheduleEndStr, $branchTimezone);
                if ($localTime->gt($scheduleEndLocal)) {
                    $isLate = true;
                }
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
                'notes'             => $request->notes,
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
                Log::error("FCM Error: " . $e->getMessage());
            }
        }

        return redirect()->route('dashboard')->with('success', $message);
    }
    
    public function skipCheckOut($id)
    {
        $user = Auth::user();
        // Cari sesi yang ID-nya cocok DAN belum checkout
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->first();

        if ($attendance) {
            // Tutup sesi pada 23:59:59 di hari check-in tersebut
            // Agar dianggap sudah selesai kemarin dan tidak mengganggu hari ini.
            $endOfPreviousDay = Carbon::parse($attendance->check_in_time)->endOfDay();

            // Status tetap verified/present jika sebelumnya sudah verified, 
            // tapi tandai di notes bahwa user skip checkout.
            // Atau bisa diubah ke 'pending_verification' jika kebijakan perusahaan mengharuskan check-out.
            // Sesuai prompt: "dia status di team saya buat yang belum absen masuk itu belum hadir / alpha"
            // Jadi kita tutup sesi ini, sehingga DashboardController tidak mendeteksi sesi aktif lagi.
            
            $status = ($attendance->status == 'verified' || $attendance->status == 'present') 
                        ? $attendance->status 
                        : 'pending_verification';
            
            $attendance->update([
                'check_out_time' => $endOfPreviousDay,
                'photo_out_path' => null, // Tidak ada foto
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
            $this->sendNotificationToBranchRoles(['audit', 'admin'], $user->branch_id, "Izin Telat", "{$user->name} izin telat.");
        } catch (\Exception $e) {}
        return redirect()->route('dashboard')->with('success', 'Laporan telat dikirim.');
    }

    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)->whereDate('created_at', today())->first();
        if ($notification) {
            $notification->delete();
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus.');
        }
        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
}