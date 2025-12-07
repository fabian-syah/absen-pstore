<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index()
    {
        return view('security.scan');
    }

    public function checkUser(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);
        $user = User::with(['division', 'branch'])->where('qr_code_value', $request->qr_code)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak ditemukan.'], 404);
        }

        $today = today();
        
        // =========================================================================
        // [FIX] PENCARIAN SESI UNTUK PREVIEW (LINTAS HARI)
        // =========================================================================
        // Cari sesi aktif dalam 24 jam terakhir (abaikan tanggal check_in harus hari ini)
        $attendanceSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) // Lookback 24 jam
            ->latest('check_in_time')
            ->first();

        // Jika tidak ada sesi aktif, baru cek validasi cuti
        if (!$attendanceSession) {
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if ($isOnLeave) {
                $endDateFormat = Carbon::parse($isOnLeave->end_date)->format('d M Y');
                return response()->json([
                    'status' => 'error', 
                    'message' => "User sedang {$isOnLeave->type} (Sampai {$endDateFormat}). Tidak dapat melakukan absen."
                ], 403);
            }
            
            // Cek history terakhir hari ini buat info display
            $attendanceSession = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->latest('check_in_time')
                ->first();
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'division' => $user->division->name ?? '-',
                'branch' => $user->branch->name ?? 'Pusat',
                'photo_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'attendance_status' => $attendanceSession ? [
                    'has_checked_in' => !is_null($attendanceSession->check_in_time),
                    'has_checked_out' => !is_null($attendanceSession->check_out_time), // Jika sesi aktif (belum checkout), ini false
                    'check_in_time' => $attendanceSession->check_in_time?->format('H:i'),
                    'check_out_time' => $attendanceSession->check_out_time?->format('H:i'),
                    'is_late' => $attendanceSession->is_late_checkin,
                ] : null,
                'work_schedule' => $workSchedule ? [
                    'check_in_start' => $workSchedule->check_in_start->format('H:i'),
                    'check_in_end' => $workSchedule->check_in_end->format('H:i'),
                    'check_out_start' => $workSchedule->check_out_start->format('H:i'),
                    'check_out_end' => $workSchedule->check_out_end->format('H:i'),
                ] : null
            ]
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:masuk,pulang',
            'image' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $securityUser = Auth::user();
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);
        $currentTime = now();
        $today = today();

        // --- PROSES BASE64 IMAGE ---
        $image = $request->image;
        $image = preg_replace('/^data:image\/(jpeg|png|jpg);base64,/', '', $image);
        $image = str_replace(' ', '+', $image);
        
        $typeLabel = $request->type;
        $imageName = 'attendance/capture_' . $typeLabel . '_' . time() . '_' . $user->id . '.jpg';
        
        Storage::disk('public')->put($imageName, base64_decode($image));
        // ----------------------------------------

        // ============================
        // LOGIC ABSEN MASUK
        // ============================
        if ($request->type == 'masuk') {
            
            // Validasi Cuti
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->exists();

            if ($isOnLeave) {
                 return response()->json(['status' => 'error', 'message' => 'Gagal: User sedang dalam status Cuti/Izin.'], 403);
            }

            // Auto Reset Sesi Sangat Lama ( > 20 Jam)
            Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', $currentTime->copy()->subHours(20))
                ->update(['check_out_time' => DB::raw("DATE_ADD(check_in_time, INTERVAL 12 HOUR)"), 'notes' => 'Auto-closed by Security Scan (Expired)']);

            // Cek apakah sudah absen masuk (Active Session < 20 Jam)
            if (Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', $currentTime->copy()->subHours(20))
                ->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini masih memiliki sesi aktif (Belum Pulang)!'], 409);
            }
            
            // Cek apakah sudah selesai absen hari ini
            if (Attendance::where('user_id', $user->id)->whereDate('check_in_time', $today)->whereNotNull('check_out_time')->exists()) {
                 return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah selesai absen (Masuk & Pulang) hari ini.'], 409);
            }

            $isLate = false;
            $status = 'present';
            if ($workSchedule) {
                if (Carbon::parse($currentTime)->gt(Carbon::parse($workSchedule->check_in_end))) {
                    $isLate = true;
                    $status = 'late';
                }
            }

            Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => $currentTime,
                'status' => $status,
                'photo_path' => $imageName, 
                'scanned_by_user_id' => $securityUser->id,
                'work_schedule_id' => $workSchedule?->id,
                'is_late_checkin' => $isLate,
                'attendance_type' => 'scan',
            ]);

            $msg = $isLate ? "Absen MASUK Berhasil (TERLAMBAT)" : "Absen MASUK Berhasil";

        } 
        // ============================
        // LOGIC ABSEN PULANG
        // ============================
        elseif ($request->type == 'pulang') {
            
            // [FIX] Cari sesi aktif dalam 24 jam terakhir (Lintas Hari)
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(24))
                ->latest('check_in_time')
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini belum absen masuk (atau sesi sudah kadaluarsa)!'], 404);
            }

            $isEarlyCheckout = false;
            if ($workSchedule) {
                 // Logic sederhana early checkout
                 $coTime = Carbon::parse($currentTime->format('H:i:s'));
                 $schedStart = Carbon::parse($workSchedule->check_out_start);
                 
                 // Jika tidak lintas hari
                 if (Carbon::parse($workSchedule->check_in_start)->lt($schedStart)) {
                     if ($coTime->lt($schedStart)) $isEarlyCheckout = true;
                 } else {
                     // Shift malam (Lintas hari), dan absen pulang pagi tapi kepagian
                     if ($coTime->lt($schedStart) && $coTime->gt(Carbon::parse("00:00:00"))) $isEarlyCheckout = true;
                 }
            }

            $attendance->update([
                'check_out_time' => $currentTime,
                'photo_out_path' => $imageName, 
                'is_early_checkout' => $isEarlyCheckout,
            ]);

            $msg = $isEarlyCheckout ? "Absen PULANG Berhasil (PULANG CEPAT)" : "Absen PULANG Berhasil";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'name' => $user->name,
                'photo' => asset('storage/' . $imageName),
                'time' => $currentTime->format('H:i:s'),
                'date' => $currentTime->format('d-m-Y'),
                'is_late' => $isLate ?? false,
                'is_early_checkout' => $isEarlyCheckout ?? false,
                'work_schedule' => $workSchedule ? [
                    'check_in_start' => $workSchedule->check_in_start->format('H:i'),
                    'check_in_end' => $workSchedule->check_in_end->format('H:i'),
                    'check_out_start' => $workSchedule->check_out_start->format('H:i'),
                    'check_out_end' => $workSchedule->check_out_end->format('H:i'),
                ] : null
            ]
        ]);
    }

    public function getStats(Request $request)
    {
         $securityUser = Auth::user(); $today = today();
         $stats = [
            'total_scans_today' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('updated_at', $today)->count(), // Gunakan updated_at agar hitung pulang juga
            'check_in_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->whereNotNull('check_in_time')->count(),
            // Hitung checkout hari ini meskipun checkin kemarin
            'check_out_count' => Attendance::where(function($q) use ($securityUser, $today) {
                $q->where('scanned_by_user_id', $securityUser->id) // Scan masuk oleh security ini (opsional tergantung rules)
                  ->orWhere('user_id', '!=', 0); // Hacky way, better remove restriction or log who scanned check_out
            })->whereDate('check_out_time', $today)->whereNotNull('check_out_time')->count(),
            
            'late_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->where('is_late_checkin', true)->count(),
         ];
         return response()->json(['status' => 'success', 'data' => $stats]);
    }
}