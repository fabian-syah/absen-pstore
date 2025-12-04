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
        $yesterday = Carbon::yesterday()->startOfDay();

        // Cek Cuti
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', '!=', 'telat') 
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($isOnLeave) {
            $msg = "Anda tercatat sedang " . strtoupper($isOnLeave->type) . " hari ini.";
            return redirect()->route('dashboard')->with('error', $msg);
        }

        // Cek Sesi Gantung
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $yesterday)
            ->orderBy('check_in_time', 'desc')
            ->first();

        if ($activeSession) {
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Cek Selesai
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini.');
            }
            $mode = 'masuk';
            $attendance = null;

            // Cek Laporan Telat Aktif
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

        // Validasi Cuti
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', '!=', 'telat')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();

        if ($isOnLeave) {
            return redirect()->route('dashboard')->with('error', 'Validasi Gagal: Anda sedang status Cuti/Izin.');
        }

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

        // [PERBAIKAN SINTAKS] Simpan nama cabang di variabel dulu
        $branchName = $user->branch->name ?? '-';

        // === ABSEN PULANG ===
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

            $currentStatus = $attendanceToUpdate->status;
            $newStatus = ($currentStatus == 'verified' || $currentStatus == 'present') ? $currentStatus : 'pending_verification';

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
                'notes'             => $finalNotes,
            ]);

            $message = "Berhasil absen pulang.";

            if ($newStatus == 'pending_verification') {
                $shouldSendNotif = true;
                $notifTitle = "Verifikasi Pulang";
                // [PERBAIKAN SINTAKS] Menggunakan variabel $branchName
                $notifBody = "{$user->name} melakukan absen pulang (Mandiri) di cabang {$branchName}";
            }
        } 
        // === ABSEN MASUK ===
        else {
            // Auto Close Sesi Lama
            Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', today())
                ->update(['check_out_time' => DB::raw("DATE_ADD(check_in_time, INTERVAL 12 HOUR)"), 'notes' => 'Auto-closed', 'status' => 'rejected']);

            // Cek Double Entry
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
            // [PERBAIKAN SINTAKS] Menggunakan variabel $branchName
            $notifBody = "{$user->name} melakukan absen masuk (Mandiri) di cabang {$branchName}";
        }

        // KIRIM NOTIFIKASI KE AUDIT
        if ($shouldSendNotif) {
            try {
                // Kirim ke role 'audit' di branch yang sama
                $this->sendNotificationToBranchRoles(['audit'], $user->branch_id, $notifTitle, $notifBody);
            } catch (\Exception $e) {
                Log::error("Gagal kirim notif FCM Controller: " . $e->getMessage());
            }
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

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
        // [PERBAIKAN SINTAKS] Simpan cabang dulu
        $branchName = $user->branch->name ?? '-';
        $body = "{$user->name} mengajukan izin telat di cabang {$branchName}";
        
        try {
            $this->sendNotificationToBranchRoles(['audit', 'admin'], $user->branch_id, $title, $body);
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