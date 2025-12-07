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

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    public function create()
    {
        $user = Auth::user();
        $today = today();
        $now = now();

        // =========================================================================
        // 1. AUTO-CLOSE CLEANUP (Sesi basi > 30 Jam)
        // =========================================================================
        $hangingSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '<', $now->subHours(30))
            ->where('status', '!=', 'alpha')
            ->first();

        if ($hangingSession) {
            $hangingSession->update([
                'check_out_time' => Carbon::parse($hangingSession->check_in_time)->endOfDay(),
                'notes' => $hangingSession->notes . ' | Auto-closed (Expired)',
                // Jangan ubah status jika sudah verified
                'status' => ($hangingSession->status == 'verified') ? 'verified' : 'pending_verification'
            ]);
        }

        // =========================================================================
        // 2. LOGIKA UTAMA: CEK SESI AKTIF (MENDUKUNG LEMBUR LINTAS HARI)
        // =========================================================================
        // Cari sesi yang check_in-nya dalam 24 jam terakhir & belum check_out.
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) 
            ->where('status', '!=', 'alpha')
            ->latest('check_in_time')
            ->first();

        // Jika ada sesi aktif, paksa MODE PULANG
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
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', "Anda sedang status " . strtoupper($isOnLeave->type) . " hari ini.");
            }

            // Cek apakah hari ini SUDAH selesai
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->where('status', '!=', 'alpha')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Laporan Telat
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', $today)
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
        $request->validate([
            'photo' => 'required|image|max:51200',
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $currentTime = now();
        $today = today();
        $branchName = $user->branch->name ?? '-';

        // 1. Tentukan Attendance Target
        $attendanceToUpdate = null;
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }
        
        // Fallback Logic
        if (!$attendanceToUpdate) {
             $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(24))
                ->latest('check_in_time')
                ->first();
        }

        // 2. Upload Foto
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
        // LOGIKA ABSEN PULANG (FIX STATUS VERIFIED)
        // =====================================================================
        if ($attendanceToUpdate) {
            
            if($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null){
                 return redirect()->route('dashboard')->with('error', 'Sesi tidak valid atau sudah ditutup.');
            }

            // Logic Early Checkout
            $isEarly = false;
            if ($workSchedule && $workSchedule->check_out_start) {
                // Logic simplifikasi early checkout
                 $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                 $checkOutOnly = Carbon::parse($currentTime->format('H:i:s'));
                 // Hanya hitung early jika pulang di hari yg sama & sebelum jam pulang
                 if ($attendanceToUpdate->check_in_time->isSameDay($currentTime)) {
                     if($checkOutOnly->lt($scheduleStart)) $isEarly = true;
                 }
            }

            // Notes
            $finalNotes = $attendanceToUpdate->notes;
            if ($request->filled('notes')) {
                $extraNote = (!$attendanceToUpdate->check_in_time->isSameDay($currentTime)) ? "[Lembur/Lintas Hari] " : "";
                $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . $extraNote . "Pulang: " . $request->notes;
            }

            // [PERBAIKAN STATUS DISINI]
            // Ambil status sekarang
            $currentStatus = $attendanceToUpdate->status;
            
            // Default: Gunakan status yang sudah ada (Verified tetap Verified)
            $newStatus = $currentStatus;

            // Jika status sebelumnya 'rejected' atau 'alpha', reset ke pending agar dicek ulang
            if (in_array($currentStatus, ['rejected', 'alpha'])) {
                $newStatus = 'pending_verification';
            }
            
            // Update
            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => $newStatus, // Status tidak berubah jika sudah verified
                'notes'             => $finalNotes,
            ]);

            $message = "Berhasil absen pulang.";
            // Kirim notif hanya jika status berubah jadi pending (artinya butuh aksi audit)
            if ($newStatus == 'pending_verification') {
                $shouldSendNotif = true;
                $notifTitle = "Verifikasi Pulang";
                $notifBody = "{$user->name} absen pulang di {$branchName}";
            }

        } 
        // =====================================================================
        // LOGIKA ABSEN MASUK
        // =====================================================================
        else {
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                 return redirect()->route('dashboard')->with('error', 'Anda sudah absen hari ini.');
            }

            // Logic Telat
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                // Hanya hitung telat jika jadwal pagi/siang normal (bukan shift malam yang aneh)
                if(Carbon::parse($workSchedule->check_in_start)->lt($scheduleEnd)) {
                    if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                        $isLate = true;
                    }
                }
            }

            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification', // Masuk selalu pending dulu
                'presence_status'   => 'Masuk',
                'attendance_type'   => 'self',
                'photo_path'        => $path,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
                'notes'             => $request->notes,
            ]);

            $message = 'Berhasil absen masuk.';
            $shouldSendNotif = true;
            $notifTitle = "Verifikasi Masuk";
            $notifBody = "{$user->name} absen masuk di {$branchName}";
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
        $attendance = Attendance::where('id', $id)->where('user_id', $user->id)->whereNull('check_out_time')->first();

        if ($attendance) {
            $status = ($attendance->status == 'verified') ? 'verified' : 'pending_verification';
            $attendance->update([
                'check_out_time' => Carbon::parse($attendance->check_in_time)->endOfDay(),
                'photo_out_path' => null,
                'status'         => $status, // Pertahankan status
                'notes'          => $attendance->notes . ' | User lupa absen pulang (Skip)',
            ]);
            return redirect()->route('dashboard')->with('success', 'Sesi ditutup.');
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