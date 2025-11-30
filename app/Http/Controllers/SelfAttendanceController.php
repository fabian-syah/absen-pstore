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
use Illuminate\Support\Str;
// Import Library Image
use Intervention\Image\Facades\Image; 

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    // ... (Function create() biarkan sama seperti sebelumnya) ...
    public function create()
    {
        // (Kode create tidak berubah, gunakan kode lama Anda)
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

    /**
     * Memproses Penyimpanan Absen (DENGAN KOMPRESI)
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:51200', // Max upload awal tetap besar tidak apa-apa
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
        ]);

        $user = Auth::user();
        $currentTime = now();

        // 1. Logic Penentuan Sesi (Check-in / Check-out)
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

        // ==========================================
        // FITUR BARU: KOMPRESI GAMBAR (Server Side)
        // ==========================================
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            // Ubah nama file jadi .jpg
            $filename = 'foto_mandiri/' . Str::random(40) . '.jpg'; 

            // Proses Kompresi
            $img = Image::make($file);

            // Resize: Lebar max 800px, Tinggi menyesuaikan (aspectRatio), jangan di-upsize kalau kecil
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Encode ke JPG kualitas 60%
            $compressedImage = (string) $img->encode('jpg', 60);

            // Simpan ke Storage Public
            Storage::disk('public')->put($filename, $compressedImage);
            
            // Set path untuk database
            $path = 'public/' . $filename; 
        }

        // Ambil Jadwal Kerja
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // --- Logic Absen Pulang ---
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
                'photo_out_path'    => $path, // Path hasil kompresi
                'is_early_checkout' => $isEarly,
                'status'            => 'pending_verification',
            ]);

            $title = "Verifikasi Pulang";
            $body = "{$user->name} melakukan absen pulang (Mandiri).";
            $message = "Berhasil absen pulang. Hati-hati di jalan!";
        }
        // --- Logic Absen Masuk ---
        else {
            // Auto Reset sesi gantung kemarin
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', today())
                ->get();

            foreach ($hangingSessions as $hanging) {
                $autoOutTime = Carbon::parse($hanging->check_in_time)->endOfDay();
                $hanging->update(['check_out_time' => $autoOutTime, 'notes' => 'Auto-closed by system']);
            }

            // Cek Double
            if (Attendance::where('user_id', $user->id)->whereDate('check_in_time', today())->whereNotNull('check_out_time')->exists()) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Telat
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
                'photo_path'        => $path, // Path hasil kompresi
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

    // ... (Sisa function storeLateStatus, deleteLateStatus, skipCheckOut sama seperti sebelumnya) ...
    public function storeLateStatus(Request $request)
    {
        // (Gunakan kode lama Anda)
        $request->validate(['message' => 'required|string|max:255']);
        $user = Auth::user();
        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);
        LateNotification::create([
            'user_id' => $user->id, 'branch_id' => $user->branch_id, 'message' => $request->message, 'is_active' => true,
        ]);
        // ... notif logic ...
        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
    }

    public function deleteLateStatus()
    {
         // (Gunakan kode lama Anda)
         $notification = LateNotification::where('user_id', Auth::id())->where('is_active', true)->whereDate('created_at', today())->first();
         if ($notification) { $notification->delete(); return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus.'); }
         return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
    
    public function skipCheckOut($id)
    {
        // (Gunakan kode lama Anda)
        $user = Auth::user();
        $attendance = Attendance::where('id', $id)->where('user_id', $user->id)->whereNull('check_out_time')->first();
        if ($attendance) {
            $attendance->update(['check_out_time' => Carbon::parse($attendance->check_in_time)->endOfDay(), 'notes' => 'Sesi ditutup via tombol Lewati']);
            return redirect()->route('dashboard')->with('success', 'Sesi ditutup.');
        }
        return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
    }
}