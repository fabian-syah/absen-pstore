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

    public function create()
    {
        $user = Auth::user();
        $today = today();
        $now = now();

        // 1. AUTO-CLOSE (Membersihkan sesi basi > 30 Jam)
        $hangingSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '<', $now->subHours(30))
            ->where('status', '!=', 'alpha')
            ->first();

        if ($hangingSession) {
            $hangingSession->update([
                'check_out_time' => Carbon::parse($hangingSession->check_in_time)->endOfDay(),
                'notes' => $hangingSession->notes . ' | Auto-closed (Expired)',
                'status' => ($hangingSession->status == 'verified' || $hangingSession->status == 'present') ? $hangingSession->status : 'pending_verification'
            ]);
        }

        // 2. CEK SESI AKTIF
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) 
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
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', "Anda sedang status " . strtoupper($isOnLeave->type) . " hari ini.");
            }

            // Cek sudah selesai hari ini?
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

        $attendanceToUpdate = null;

        // A. Jika ID dikirim dari Form
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }
        
        // B. Fallback: Cari manual sesi aktif user ini
        if (!$attendanceToUpdate) {
             $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(24))
                ->latest('check_in_time')
                ->first();
        }

        // 2. PROCESS IMAGE
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
                 $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                 $checkOutOnly = Carbon::parse($currentTime->format('H:i:s'));
                 if ($attendanceToUpdate->check_in_time->isSameDay($currentTime)) {
                     if($checkOutOnly->lt($scheduleStart)) $isEarly = true;
                 }
            }

            // Tambahkan Label 'Pulang (Selfie)' ke notes agar terdeteksi di view
            $finalNotes = $attendanceToUpdate->notes;
            $extraNote = (!$attendanceToUpdate->check_in_time->isSameDay($currentTime)) ? "[Lembur/Lintas Hari] " : "";
            $userNote = $request->notes ? ": " . $request->notes : "";
            $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . $extraNote . "Pulang (Selfie)" . $userNote;

            // Status Pulang: Jika sebelumnya Rejected/Alpha, ubah ke Pending agar dicek Audit
            $currentStatus = $attendanceToUpdate->status;
            $newStatus = $currentStatus; 
            if (in_array($currentStatus, ['rejected', 'alpha'])) {
                $newStatus = 'pending_verification';
            }
            
            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => $newStatus, 
                'notes'             => $finalNotes,
            ]);

            $message = "Berhasil absen pulang.";
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
            $existingSessionToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
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
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                if(Carbon::parse($workSchedule->check_in_start)->lt($scheduleEnd)) {
                    if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                        $isLate = true;
                    }
                }
            }

            // PENTING: Status 'pending_verification' dan verified_by = NULL
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
                'verified_by_user_id' => null, // Pastikan NULL agar tidak dianggap verified
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
        $attendance = Attendance::where('id', $id)->where('user_id', $user->id)->whereNull('check_out_time')->first();

        if ($attendance) {
            $status = ($attendance->status == 'verified' || $attendance->status == 'present') ? $attendance->status : 'pending_verification';
            
            $attendance->update([
                'check_out_time' => Carbon::parse($attendance->check_in_time)->endOfDay(),
                'photo_out_path' => null,
                'status'         => $status, 
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