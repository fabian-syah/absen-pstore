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
        // 1. AUTO-CLOSE CLEANUP (Hanya untuk sesi yang TERLALU lama/lupa, misal > 30 Jam)
        // =========================================================================
        $hangingSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '<', $now->subHours(30)) // Safety net diperpanjang
            ->where('status', '!=', 'alpha')
            ->first();

        if ($hangingSession) {
            $hangingSession->update([
                'check_out_time' => Carbon::parse($hangingSession->check_in_time)->endOfDay(),
                'notes' => $hangingSession->notes . ' | Auto-closed (Expired > 30h)',
                'status' => 'pending_verification'
            ]);
        }

        // =========================================================================
        // 2. LOGIKA UTAMA: CEK SESI AKTIF (MENDUKUNG LEMBUR LINTAS HARI)
        // =========================================================================
        // Kita cari sesi yang check_in-nya dalam 24 jam terakhir & belum check_out.
        // Ini akan menangkap sesi kemarin sore yang terbawa sampai jam 2 pagi hari ini.
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) 
            ->where('status', '!=', 'alpha')
            ->latest('check_in_time')
            ->first();

        // Jika ada sesi aktif (Entah masuk hari ini atau kemarin), paksa MODE PULANG
        if ($activeSession) {
            $mode = 'pulang';
            $attendance = $activeSession;
        } 
        else {
            // === MODE MASUK ===
            
            // Cek Cuti (Hanya jika mau absen masuk baru)
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if ($isOnLeave) {
                return redirect()->route('dashboard')->with('error', "Anda sedang status " . strtoupper($isOnLeave->type) . " hari ini.");
            }

            // Cek apakah hari ini SUDAH selesai (Masuk & Pulang di hari yang sama)
            // Note: Jika sekarang jam 01:00 pagi dan user baru saja menutup sesi kemarin,
            // baris ini akan return false (karena check_in sesi kemarin tglnya beda), 
            // jadi user BISA absen masuk lagi untuk hari baru (Double shift).
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
            'photo' => 'required|image|max:51200', // Support high-res
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_id' => 'nullable|exists:attendances,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $currentTime = now();
        $today = today();
        $branchName = $user->branch->name ?? '-';

        // 1. Tentukan Attendance Target (Untuk Pulang)
        $attendanceToUpdate = null;
        
        // Prioritas 1: ID dari form
        if ($request->filled('attendance_id')) {
            $attendanceToUpdate = Attendance::find($request->attendance_id);
        }
        
        // Prioritas 2: Cari manual jika ID hilang/manipulasi (Backup Logic)
        if (!$attendanceToUpdate) {
             $attendanceToUpdate = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(24))
                ->latest('check_in_time')
                ->first();
        }

        // Upload Foto (Kompresi Server-side)
        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            // Folder beda untuk masuk/pulang bisa diatur, disini disatukan
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
        // LOGIKA ABSEN PULANG (Termasuk Lembur Lintas Hari)
        // =====================================================================
        if ($attendanceToUpdate) {
            
            // Validasi: Jangan update jika user beda atau sudah checkout
            if($attendanceToUpdate->user_id != $user->id || $attendanceToUpdate->check_out_time != null){
                 return redirect()->route('dashboard')->with('error', 'Sesi tidak valid atau sudah ditutup.');
            }

            // Hitung Early Checkout (Opsional, disesuaikan dengan toleransi lembur)
            $isEarly = false;
            // ... (Logika early checkout bisa dimasukkan di sini jika perlu) ...

            // Update Sesi
            $finalNotes = $attendanceToUpdate->notes;
            if ($request->filled('notes')) {
                // Tambah keterangan "Lembur" otomatis jika lintas hari
                $extraNote = "";
                if(!$attendanceToUpdate->check_in_time->isSameDay($currentTime)) {
                    $extraNote = "[Lembur/Lintas Hari] ";
                }
                $finalNotes = ($finalNotes ? $finalNotes . " | " : "") . $extraNote . "Pulang: " . $request->notes;
            }

            $currentStatus = $attendanceToUpdate->status;
            // Jika sebelumnya verified, kembalikan ke pending agar dicek ulang foto pulangnya (opsional)
            // Atau biarkan verified jika policy perusahaan longgar. Di sini kita set ke pending.
            $newStatus = 'pending_verification'; 

            $attendanceToUpdate->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
                'status'            => $newStatus,
                'notes'             => $finalNotes,
                // Update lokasi pulang juga jika perlu (tambah kolom latitude_out di db)
            ]);

            $message = "Berhasil absen pulang (Sesi ditutup).";
            $shouldSendNotif = true;
            $notifTitle = "Verifikasi Pulang";
            $notifBody = "{$user->name} absen pulang di {$branchName}" . (!$attendanceToUpdate->check_in_time->isSameDay($currentTime) ? " (Lintas Hari)" : "");

        } 
        // =====================================================================
        // LOGIKA ABSEN MASUK
        // =====================================================================
        else {
            // Cek Double Entry Hari Ini (Strict untuk Masuk)
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                // Kecuali user punya izin khusus lembur terpisah, blokir double shift
                 return redirect()->route('dashboard')->with('error', 'Anda sudah absen hari ini. Hubungi admin jika ini lembur terpisah.');
            }

            // Cek Telat
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                // Logic telat sederhana
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                // Jika jadwal pagi/siang
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

            $message = 'Berhasil absen masuk.';
            $shouldSendNotif = true;
            $notifTitle = "Verifikasi Masuk";
            $notifBody = "{$user->name} absen masuk di {$branchName}";
        }

        // Kirim Notifikasi FCM
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