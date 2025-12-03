<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest; // <--- JANGAN LUPA IMPORT INI
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

        // =========================================================================
        // 1. CEK CUTI DI PREVIEW SCAN (ALERT DI SECURITY)
        // =========================================================================
        $today = today();
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', '!=', 'telat') // Izin telat tetap boleh scan
            // ->where('type', '!=', 'wfh') // Hapus komentar ini jika WFH Wajib Scan (Jarang terjadi)
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
        // =========================================================================

        $attendanceSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->whereDate('check_in_time', today())
            ->first();

        if (!$attendanceSession) {
            $attendanceSession = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
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
                    'has_checked_out' => !is_null($attendanceSession->check_out_time),
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

        // =========================================================================
        // 2. CEK CUTI SAAT SIMPAN (DOUBLE PROTECTION)
        // =========================================================================
        $isOnLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', '!=', 'telat')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();

        if ($isOnLeave) {
             return response()->json(['status' => 'error', 'message' => 'Gagal: User sedang dalam status Cuti/Izin.'], 403);
        }
        // =========================================================================

        // --- PROSES BASE64 IMAGE ---
        $image = $request->image;
        $image = preg_replace('/^data:image\/(jpeg|png|jpg);base64,/', '', $image);
        $image = str_replace(' ', '+', $image);
        
        $typeLabel = $request->type;
        $imageName = 'attendance/capture_' . $typeLabel . '_' . time() . '_' . $user->id . '.jpg';
        
        Storage::disk('public')->put($imageName, base64_decode($image));
        // ----------------------------------------

        // Logic Absen Masuk
        if ($request->type == 'masuk') {
            
            // Auto Reset Sesi Lama
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->whereDate('check_in_time', '<', today())
                ->get();
            foreach ($hangingSessions as $hanging) {
                $hanging->update(['check_out_time' => Carbon::parse($hanging->check_in_time)->endOfDay(), 'notes' => 'Auto-closed by Security Scan']);
            }

            if (Attendance::where('user_id', $user->id)->whereDate('check_in_time', today())->whereNull('check_out_time')->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah absen masuk hari ini!'], 409);
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
        // Logic Absen Pulang
        elseif ($request->type == 'pulang') {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->whereDate('check_in_time', today())
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini belum absen masuk hari ini!'], 404);
            }

            $isEarlyCheckout = false;
            if ($workSchedule) {
                 $coTime = Carbon::parse($currentTime->format('H:i:s'));
                 if ($coTime->lt(Carbon::parse($workSchedule->check_out_start))) {
                     $isEarlyCheckout = true;
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
            'total_scans_today' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->count(),
            'check_in_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->whereNotNull('check_in_time')->count(),
            'check_out_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->whereNotNull('check_out_time')->count(),
            'late_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->where('is_late_checkin', true)->count(),
         ];
         return response()->json(['status' => 'success', 'data' => $stats]);
    }
}