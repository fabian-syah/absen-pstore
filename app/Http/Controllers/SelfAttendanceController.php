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

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    public function create()
    {
        $user = Auth::user();
        $today = today();
        $yesterday = Carbon::yesterday()->startOfDay();

        // ---------------------------------------------------------
        // 1. CEK STATUS CUTI/IZIN/SAKIT (PROTEKSI FRONTEND)
        // ---------------------------------------------------------
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', '!=', 'telat') 
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($isOnLeave) {
            $msg = "Anda tercatat sedang " . strtoupper($isOnLeave->type) . " hari ini. Tidak perlu melakukan absen.";
            return redirect()->route('dashboard')->with('error', $msg);
        }

        // ---------------------------------------------------------
        // 2. CEK SESI ABSENSI
        // ---------------------------------------------------------
        // Cek apakah ada sesi gantung (belum checkout) dari kemarin atau hari ini
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $yesterday)
            ->orderBy('check_in_time', 'desc')
            ->first();

        if ($activeSession) {
            // Mode Pulang
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Cek apakah sudah selesai total hari ini
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Mode Masuk
            $mode = 'masuk';
            $attendance = null;

            // Cek laporan telat
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

    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:51200', 
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
            'notes' => 'nullable|string|max:1000', // Validasi Notes
        ]);

        $user = Auth::user();
        $currentTime = now();
        $today = today();

        // ---------------------------------------------------------
        // CEK CUTI (PROTEKSI BACKEND)
        // ---------------------------------------------------------
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', '!=', 'telat')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();

        if ($isOnLeave) {
            return redirect()->route('dashboard')->with('error', 'Validasi Gagal: Anda sedang status Cuti/Izin.');
        }

        // Tentukan apakah Update (Pulang) atau Create (Masuk)
        $attendanceToUpdate = null;
        if ($request->has('attendance_id') && $request->attendance_id) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
            // Security check: pastikan milik user ini dan belum checkout
            if ($attendanceToUpdate && ($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null)) {
                $attendanceToUpdate = null;
            }
        }

        // Fallback: cari sesi aktif jika ID tidak dikirim
        if (!$attendanceToUpdate) {
            $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time')
                ->first();
        }

        // ---------------------------------------------------------
        // PROSES UPLOAD FOTO
        // ---------------------------------------------------------
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'public/foto_mandiri/' . Str::random(40) . '.jpg';
            
            // Compress Image
            $img = Image::make($file);
            $img->orientate(); 
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $compressedImage = (string) $img->encode('jpg', 60);
            Storage::disk('public')->put($filename, $compressedImage);
            $path = $filename;
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // =========================================================
        // LOGIKA ABSEN PULANG (UPDATE)
        // =========================================================
        if ($attendanceToUpdate) {
            $isEarly = false;
            // Cek jadwal pulang jika hari yang sama
            if ($attendanceToUpdate->check_in_time->isToday()) {
                if ($workSchedule && $workSchedule->check_out_start) {
                    $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                    $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));
                    if ($checkOutTimeOnly->lt($scheduleStart)) {
                        $isEarly = true;
                    }
                }
            }

            // Status reset ke pending jika sebelumnya verified, agar dicek ulang audit
            $currentStatus = $attendanceToUpdate->status;
            $newStatus = ($currentStatus == 'verified' || $currentStatus == 'present') ? $currentStatus : 'pending_verification';

            // Gabungkan Notes (Agar catatan masuk tidak tertimpa)
            $finalNotes = $attendanceToUpdate->notes;
            if ($request->filled('notes')) {
                if ($finalNotes) {
                    $finalNotes .= " | Pulang: " . $request->notes;
                } else {
                    $finalNotes = $request->notes;
                }
            }

            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => $newStatus,
                'notes'             => $finalNotes, // Update Notes
            ]);

            $title = "Absen Pulang";
            $body = "{$user->name} melakukan absen pulang.";
            $message = "Berhasil absen pulang.";
        }
        
        // =========================================================
        // LOGIKA ABSEN MASUK (CREATE)
        // =========================================================
        else {
            // Bersihkan sesi gantung lama
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', today())
                ->get();

            foreach ($hangingSessions as $hanging) {
                $autoOutTime = Carbon::parse($hanging->check_in_time)->endOfDay();
                $hanging->update([
                    'check_out_time' => $autoOutTime,
                    'notes' => $hanging->notes . ' | Auto-closed by system',
                    'status' => 'rejected' // Atau biarkan status lama
                ]);
            }

            // Double check sudah absen hari ini
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Terlambat
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    $isLate = true;
                }
            }

            // Create Data Baru sesuai Model Baru
            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification',
                'presence_status'   => 'Masuk', // <-- Set Default Status Kehadiran
                'attendance_type'   => 'self',
                'photo_path'        => $path,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
                'notes'             => $request->notes, // <-- Simpan Notes Awal
            ]);

            $title = "Verifikasi Masuk";
            $body = "{$user->name} melakukan absen masuk (Mandiri).";
            $message = 'Berhasil absen masuk. Menunggu verifikasi.';
        }

        // Kirim Notifikasi FCM ke Admin/Audit jika absen Masuk
        if ($attendanceToUpdate == null) { 
            try {
                $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
            } catch (\Exception $e) {
                // Ignore FCM Error
            }
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    // Fungsi Lewati Checkout (untuk sesi gantung)
    public function skipCheckOut($id)
    {
        $user = Auth::user();
        $attendance = Attendance::where('id', $id)->where('user_id', $user->id)->whereNull('check_out_time')->first();

        if ($attendance) {
            $attendance->update([
                'check_out_time' => Carbon::parse($attendance->check_in_time)->endOfDay(),
                'photo_out_path' => null,
                'notes'          => $attendance->notes . ' | User lupa absen pulang (Sesi ditutup via tombol Lewati)',
            ]);

            return redirect()->route('dashboard')->with('success', 'Sesi kemarin ditutup.');
        }

        return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
    }

    // Fungsi Izin Telat (Tidak berubah)
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
        
        $title = "Izin Telat Masuk";
        $body = "{$user->name} mengajukan izin telat.";
        
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) { }
        
        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
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