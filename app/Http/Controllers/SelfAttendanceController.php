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

// --- TAMBAHAN IMPORT LIBRARY IMAGE & STRING ---
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
            'photo' => 'required|image|max:51200', // Upload awal boleh besar (nanti dikompress)
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
        ]);

        $user = Auth::user();
        $currentTime = now();

        $attendanceToUpdate = null;

        if ($request->has('attendance_id') && $request->attendance_id) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
            if ($attendanceToUpdate && ($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null)) {
                $attendanceToUpdate = null;
            }
        }

        if (!$attendanceToUpdate) {
            $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time')
                ->first();
        }

        // ==============================================================
        // LOGIKA BARU: KOMPRESI GAMBAR & PATH
        // ==============================================================
        $path = null; // Default null

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            // 1. Buat nama file unik (Format JPG)
            // Hasil: "foto_mandiri/randomstring.jpg" (TANPA awalan 'public/')
            $filename = 'public/foto_mandiri/' . Str::random(40) . '.jpg';

            // 2. Proses Resize & Kompresi
            $img = Image::make($file);

            // Resize: Lebar max 800px, Tinggi menyesuaikan, jangan dipaksa membesar (upsize)
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Encode jadi JPG kualitas 60%
            $compressedImage = (string) $img->encode('jpg', 60);

            // 3. Simpan ke Storage Public
            // Storage::disk('public') sudah menunjuk ke folder "storage/app/public"
            // Jadi kita cukup masukkan $filename ("foto_mandiri/xxx.jpg")
            Storage::disk('public')->put($filename, $compressedImage);

            // 4. Set variabel path untuk Database
            // Path ini bersih, bisa langsung dipanggil via asset('storage/' . $path)
            $path = $filename; 
        }
        // ==============================================================

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // --- LOGIKA ABSEN PULANG ---
        if ($attendanceToUpdate) {
            $isEarly = false;
            if ($attendanceToUpdate->check_in_time->isToday()) {
                if ($workSchedule && $workSchedule->check_out_start) {
                    $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                    $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));
                    if ($checkOutTimeOnly->lt($scheduleStart)) {
                        $isEarly = true;
                    }
                }
            }

            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path, // Simpan path hasil kompresi
                'is_early_checkout' => $isEarly,
                'status'            => 'pending_verification',
            ]);

            $title = "Verifikasi Pulang";
            $body = "{$user->name} melakukan absen pulang (Mandiri).";
            $message = "Berhasil absen pulang. Hati-hati di jalan!";
        }
        // --- LOGIKA ABSEN MASUK ---
        else {
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
                'status'            => 'pending_verification',
                'attendance_type'   => 'self',
                'photo_path'        => $path, // Simpan path hasil kompresi
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
            ]);

            $title = "Verifikasi Masuk";
            $body = "{$user->name} melakukan absen masuk (Mandiri).";
            $message = 'Berhasil absen masuk. Menunggu verifikasi Audit/Leader.';
        }

        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function storeLateStatus(Request $request)
    {
        $request->validate([ 'message' => 'required|string|max:255' ]);
        $user = Auth::user();
        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);
        LateNotification::create([
            'user_id' => $user->id, 'branch_id' => $user->branch_id, 'message' => $request->message, 'is_active' => true,
        ]);
        $title = "Izin Telat Masuk";
        $body = "{$user->name} dari Divisi " . ($user->division->name ?? 'N/A') . " mengajukan izin telat.";
        try { $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body); } catch (\Exception $e) {}
        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
    }

    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)->whereDate('created_at', today())->first();
        if ($notification) { $notification->delete(); return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus.'); }
        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }

    public function skipCheckOut($id)
    {
        $user = Auth::user();
        $attendance = Attendance::where('id', $id)->where('user_id', $user->id)->whereNull('check_out_time')->first();
        if ($attendance) {
            $attendance->update([
                'check_out_time' => Carbon::parse($attendance->check_in_time)->endOfDay(),
                'photo_out_path' => null,
                'notes'          => 'User lupa absen pulang (Sesi ditutup via tombol Lewati)',
            ]);
            return redirect()->route('dashboard')->with('success', 'Sesi kemarin ditutup.');
        }
        return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
    }
}