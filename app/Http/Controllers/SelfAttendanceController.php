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
use Illuminate\Support\Facades\DB;
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
        // [FIX] LOGIC AUTO-CLOSE (Hanya tutup jika sesi sudah terlalu lama, misal > 20 Jam)
        // =========================================================================
        // Cari sesi yang lupa di-close (sudah lebih dari 20 jam dari waktu check-in)
        $hangingSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '<', $now->subHours(20)) // [FIX] Batas toleransi 20 jam
            ->where('status', '!=', 'alpha')
            ->first();

        if ($hangingSession) {
            // Auto Close sesi yang sudah basi
            $hangingSession->update([
                'check_out_time' => Carbon::parse($hangingSession->check_in_time)->endOfDay(),
                'notes' => $hangingSession->notes . ' | Auto-closed by System (Expired Session > 20h)',
                'status' => 'pending_verification'
            ]);
            session()->flash('warning', 'Sesi kedaluwarsa sebelumnya otomatis ditutup.');
        }

        // =========================================================================
        // [FIX] CEK SESI AKTIF (Lintas Hari)
        // =========================================================================
        // Cari sesi aktif dalam 24 jam terakhir, tidak peduli tanggalnya kapan
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) // [FIX] Lookback 24 jam
            ->where('status', '!=', 'alpha')
            ->latest('check_in_time')
            ->first();

        // Jika ada sesi aktif (masuk kemarin sore, sekarang jam 1 pagi), maka MODE = PULANG
        if ($activeSession) {
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Jika tidak ada sesi aktif, cek dulu apa hari ini user sedang Cuti?
            // (Cek cuti diletakkan di sini agar user yang lembur tidak terblokir logic cuti hari berikutnya)
            
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', "Anda sedang status " . strtoupper($isOnLeave->type) . " hari ini.");
            }

            // Cek apakah sudah selesai absen (Masuk & Pulang) HARI INI
            // Logic ini perlu hati-hati: Kalau baru jam 1 pagi dan user belum check-in hari ini, maka aman.
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->where('status', '!=', 'alpha')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            $mode = 'masuk';
            $attendance = null;

            // Cek Laporan Telat
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', $today)
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Hapus laporan telat dulu sebelum absen.');
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
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $currentTime = now();
        $today = today();
        $branchName = $user->branch->name ?? '-';

        // Tentukan attendance mana yang mau diupdate (jika ada)
        $attendanceToUpdate = null;
        if ($request->has('attendance_id') && $request->attendance_id) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
            // Validasi: Pastikan punya user ini dan belum checkout
            if ($attendanceToUpdate && ($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null)) {
                $attendanceToUpdate = null;
            }
        }

        // [FIX] Fallback Logic: Jika ID tidak dikirim, cari sesi aktif Lintas Hari
        if (!$attendanceToUpdate) {
            $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(24)) // [FIX] Cek 24 jam ke belakang
                ->where('status', '!=', 'alpha')
                ->latest('check_in_time')
                ->first();
        }

        // Upload Foto
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
            
            $compressedImage = (string) $img->encode('jpg', 60);
            Storage::disk('public')->put($filename, $compressedImage);
            $path = $filename;
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);
        $shouldSendNotif = false;
        $notifTitle = "";
        $notifBody = "";
        $message = "";

        // === ABSEN PULANG (Jika sesi ditemukan) ===
        if ($attendanceToUpdate) {
            
            // Logic Early Checkout
            $isEarly = false;
            // Hanya cek early checkout jika pulang di HARI YANG SAMA dengan jadwal pulang
            // Jika lintas hari, logic ini mungkin perlu penyesuaian, tapi basicnya:
            if ($workSchedule && $workSchedule->check_out_start) {
                // Konversi jam sekarang ke jam saja
                $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));
                $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                
                // Jika shift malam (misal pulang jam 07:00 pagi besoknya), dan user pulang jam 06:00
                // Logic perbandingan jam harus pintar. 
                // Simplifikasi: Jika pulang sebelum jam check_out_start jadwal, anggap early.
                // (Implementasi full shift malam butuh logic tanggal jadwal, tapi ini basic)
                
                // Jika jadwal tidak lintas hari (Normal)
                if (Carbon::parse($workSchedule->check_in_start)->lt(Carbon::parse($workSchedule->check_out_start))) {
                     if ($checkOutTimeOnly->lt($scheduleStart)) {
                        $isEarly = true;
                    }
                } 
                // Jika jadwal Lintas Hari (Shift Malam, misal Masuk 20:00, Pulang 05:00)
                else {
                    // Logic sederhana: Jika sekarang masih pagi (sebelum jam 12 siang) dan kurang dari jam pulang
                     if ($checkOutTimeOnly->lt($scheduleStart) && $checkOutTimeOnly->gt(Carbon::parse("00:00:00"))) {
                        $isEarly = true;
                    }
                }
            }

            $currentStatus = $attendanceToUpdate->status;
            $newStatus = ($currentStatus == 'verified' || $currentStatus == 'present') ? $currentStatus : 'pending_verification';

            $finalNotes = $attendanceToUpdate->notes;
            if ($request->filled('notes')) {
                $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . "Pulang: " . $request->notes;
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
                $notifBody = "{$user->name} melakukan absen pulang (Mandiri) di cabang {$branchName}";
            }

        } 
        // === ABSEN MASUK (Jika tidak ada sesi aktif) ===
        else {
            
            // [FIX] Validasi Cuti hanya dilakukan saat mau check-in BARU
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->exists();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', 'Validasi Gagal: Anda sedang status Cuti/Izin.');
            }

            // Cek Double Entry Hari Ini (Hanya jika check-in HARI INI sudah ada dan sudah check-out)
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->where('status', '!=', 'alpha')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                // Handle shift malam untuk validasi telat agak kompleks, 
                // disini diasumsikan checkin selalu dekat dengan jam jadwal
                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    // Kecuali jika shift malam dan user absen sebelum tengah malam (misal jadwal 20:00, absen 19:55 - aman)
                    // Jika jadwal masuk 20:00, batas telat 21:00. User absen 21:30 (telat).
                    // Jika user absen jam 01:00 dini hari (sangat telat atau lupa absen)
                    $isLate = true;
                }
            }

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
            ]);

            $message = 'Berhasil absen masuk. Menunggu verifikasi.';
            $shouldSendNotif = true;
            $notifTitle = "Verifikasi Masuk";
            $notifBody = "{$user->name} melakukan absen masuk (Mandiri) di cabang {$branchName}";
        }

        if ($shouldSendNotif) {
            try {
                $this->sendNotificationToBranchRoles(['audit'], $user->branch_id, $notifTitle, $notifBody);
            } catch (\Exception $e) {
                Log::error("Gagal kirim notif FCM: " . $e->getMessage());
            }
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function skipCheckOut($id)
    {
        // Logic skip checkout tetap sama, menutup sesi.
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

    public function storeLateStatus(Request $request)
    {
        // ... (Kode sama, tidak berubah) ...
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
        $branchName = $user->branch->name ?? '-';
        $body = "{$user->name} mengajukan izin telat di cabang {$branchName}";
        try {
            $this->sendNotificationToBranchRoles(['audit', 'admin'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {}
        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
    }

    public function deleteLateStatus()
    {
        // ... (Kode sama, tidak berubah) ...
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)->whereDate('created_at', today())->first();
        if ($notification) {
            $notification->delete();
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus.');
        }
        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
}