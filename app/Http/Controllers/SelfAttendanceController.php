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
                'status' => ($hangingSession->status == 'verified') ? 'verified' : 'pending_verification'
            ]);
        }

        // 2. CEK SESI AKTIF (Priority)
        // Cari sesi yang belum check-out dalam rentang 24 jam terakhir
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) 
            ->where('status', '!=', 'alpha') // Jangan ambil yang status Alpha
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

        // =====================================================================
        // 1. RESOLUSI TARGET ABSEN (FIX GANDA)
        // =====================================================================
        $attendanceToUpdate = null;

        // A. Jika ID dikirim dari Form (Skenario Ideal)
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }
        
        // B. Fallback: Jika ID hilang, cari manual sesi aktif user ini
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
            
            // Validasi kepemilikan
            if($attendanceToUpdate->user_id != $user->id){
                 return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
            }

            // Early Checkout Check
            $isEarly = false;
            if ($workSchedule && $workSchedule->check_out_start) {
                 $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                 $checkOutOnly = Carbon::parse($currentTime->format('H:i:s'));
                 if ($attendanceToUpdate->check_in_time->isSameDay($currentTime)) {
                     if($checkOutOnly->lt($scheduleStart)) $isEarly = true;
                 }
            }

            // Notes merging
            $finalNotes = $attendanceToUpdate->notes;
            if ($request->filled('notes')) {
                $extraNote = (!$attendanceToUpdate->check_in_time->isSameDay($currentTime)) ? "[Lembur/Lintas Hari] " : "";
                $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . $extraNote . "Pulang: " . $request->notes;
            }

            // Handling Status Saat Pulang
            $currentStatus = $attendanceToUpdate->status;
            $newStatus = $currentStatus; // Default: pertahankan status lama

            // Jika sebelumnya ditolak/alpha, reset ke pending saat pulang agar dicek ulang
            if (in_array($currentStatus, ['rejected', 'alpha'])) {
                $newStatus = 'pending_verification';
            }
            // OPSI: Jika ingin status Verified berubah jadi Pending saat pulang, uncomment baris ini:
            // if ($currentStatus == 'verified') { $newStatus = 'pending_verification'; }
            
            // Update Data
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
            // [FIX GANDA] Cek Keras: Apakah ADA sesi hari ini (Entah selesai atau belum)
            $existingSessionToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->where('status', '!=', 'alpha') // Abaikan alpha
                ->first();

            // Jika sudah ada sesi...
            if ($existingSessionToday) {
                // Jika sesi itu belum di-checkout (Masih aktif)
                if ($existingSessionToday->check_out_time == null) {
                    // Berarti sistem gagal mendeteksi mode pulang (ID hilang/Fallback gagal).
                    // JANGAN BUAT BARIS BARU. Redirect user untuk refresh agar dapat mode pulang.
                    return redirect()->route('dashboard')->with('warning', 'Terdeteksi sesi aktif hari ini. Silakan refresh halaman atau coba lagi untuk Absen Pulang.');
                } 
                // Jika sudah checkout (sudah selesai)
                else {
                    return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
                }
            }

            // Logic Telat
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                // Hanya hitung telat jika bukan shift malam yang aneh
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
                'status'            => 'pending_verification', // Default selalu pending
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