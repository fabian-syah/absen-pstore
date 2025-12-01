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

        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $yesterday)
            ->orderBy('check_in_time', 'desc')
            ->first();

        if ($activeSession) {
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            $mode = 'masuk';
            $attendance = null;

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
            'photo' => 'required|image|max:51200', // Upload awal boleh besar
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
        ]);

        $user = Auth::user();
        $currentTime = now();

        $attendanceToUpdate = null;

        // Cari data absensi yang akan di-update (pulang)
        if ($request->has('attendance_id') && $request->attendance_id) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
            // Validasi kepemilikan dan status checkout
            if ($attendanceToUpdate && ($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null)) {
                $attendanceToUpdate = null;
            }
        }

        // Fallback: Cari sesi aktif hari ini
        if (!$attendanceToUpdate) {
            $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time')
                ->first();
        }

        // LOGIKA KOMPRESI GAMBAR
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'public/foto_mandiri/' . Str::random(40) . '.jpg';
            $img = Image::make($file);
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $compressedImage = (string) $img->encode('jpg', 60);
            Storage::disk('public')->put($filename, $compressedImage);
            $path = $filename;
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // --- LOGIKA ABSEN PULANG ---
        if ($attendanceToUpdate) {
            $isEarly = false;
            // Cek Pulang Cepat hanya jika hari ini (bukan sesi kemarin yg tertinggal)
            if ($attendanceToUpdate->check_in_time->isToday()) {
                if ($workSchedule && $workSchedule->check_out_start) {
                    $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                    $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));
                    if ($checkOutTimeOnly->lt($scheduleStart)) {
                        $isEarly = true;
                    }
                }
            }

            // PERBAIKAN LOGIKA STATUS: Pertahankan jika sudah verified
            $currentStatus = $attendanceToUpdate->status;
            $newStatus = ($currentStatus == 'verified' || $currentStatus == 'present') ? $currentStatus : 'pending_verification';

            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => $newStatus, 
            ]);

            $title = "Absen Pulang";
            $body = "{$user->name} melakukan absen pulang.";
            $message = "Berhasil absen pulang.";
        }
        // --- LOGIKA ABSEN MASUK (Tetap Pending) ---
        else {
            // Tutup sesi gantung kemarin (Auto-close)
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', today())
                ->get();

            foreach ($hangingSessions as $hanging) {
                $autoOutTime = Carbon::parse($hanging->check_in_time)->endOfDay();
                $hanging->update([
                    'check_out_time' => $autoOutTime,
                    'notes' => 'Auto-closed by system (Lupa Absen Pulang)',
                ]);
            }

            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    $isLate = true;
                }
            }

            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification', // Masuk SELALU pending dulu
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

        // Kirim Notifikasi (Hanya kirim saat Masuk agar tidak spam, karena Pulang otomatis ikut status Masuk)
        if ($attendanceToUpdate == null) { 
            try {
                $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function skipCheckOut($id)
    {
        $user = Auth::user();
        $attendance = Attendance::where('id', $id)->where('user_id', $user->id)->whereNull('check_out_time')->first();

        if ($attendance) {
            // PERBAIKAN LOGIKA STATUS: Pertahankan jika sudah verified
            $currentStatus = $attendance->status;
            $newStatus = ($currentStatus == 'verified' || $currentStatus == 'present') ? $currentStatus : 'pending_verification';

            $attendance->update([
                'check_out_time' => Carbon::parse($attendance->check_in_time)->endOfDay(), // Set jam 23:59:59 hari H
                'photo_out_path' => null,
                'status'         => $newStatus, 
                'notes'          => 'User lupa absen pulang (Sesi ditutup via tombol Lewati)',
            ]);

            return redirect()->route('dashboard')->with('success', 'Sesi kemarin ditutup.');
        }

        return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
    }

    public function storeLateStatus(Request $request)
    {
        $request->validate(['message' => 'required|string|max:255']);
        $user = Auth::user();
        
        // Nonaktifkan notifikasi lama jika ada
        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);
        
        LateNotification::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'message' => $request->message,
            'is_active' => true,
        ]);
        
        $title = "Izin Telat Masuk";
        $body = "{$user->name} dari Divisi " . ($user->division->name ?? 'N/A') . " mengajukan izin telat.";
        
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
        }
        
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