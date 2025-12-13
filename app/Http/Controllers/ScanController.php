<?php

namespace App\Http\Controllers;

use DB;
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
        
        $attendanceSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(32)) 
            ->latest('check_in_time')
            ->first();

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
                    'has_checked_out' => !is_null($attendanceSession->check_out_time),
                    'check_in_time' => $attendanceSession->check_in_time ? Carbon::parse($attendanceSession->check_in_time)->format('H:i') : null,
                    'check_out_time' => $attendanceSession->check_out_time ? Carbon::parse($attendanceSession->check_out_time)->format('H:i') : null,
                    'is_late' => $attendanceSession->is_late_checkin,
                    'type' => $attendanceSession->attendance_type
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
            'notes' => 'nullable|string|max:255',
        ]);

        $user = User::with(['division', 'branch'])->find($request->user_id);
        $securityUser = Auth::user(); 
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);
        $currentTime = now();
        $today = today();

        $image = $request->image;
        $image = preg_replace('/^data:image\/(jpeg|png|jpg);base64,/', '', $image);
        $image = str_replace(' ', '+', $image);
        
        $typeLabel = $request->type;
        $imageName = 'attendance/capture_' . $typeLabel . '_' . time() . '_' . $user->id . '.jpg';
        
        Storage::disk('public')->put($imageName, base64_decode($image));

        $manualNotes = $request->notes ? $request->notes : null;

        if ($request->type == 'masuk') {
            
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->exists();

            if ($isOnLeave) {
                 return response()->json(['status' => 'error', 'message' => 'Gagal: User sedang dalam status Cuti/Izin.'], 403);
            }

            // Auto-Close sesi > 32 Jam
            Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', $currentTime->copy()->subHours(32))
                ->update(['check_out_time' => DB::raw("DATE_ADD(check_in_time, INTERVAL 12 HOUR)"), 'notes' => 'Auto-closed by Security Scan (Expired)']);

            if (Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', $currentTime->copy()->subHours(32))
                ->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini masih memiliki sesi aktif (Belum Pulang)!'], 409);
            }
            
            if (Attendance::where('user_id', $user->id)->whereDate('check_in_time', $today)->whereNotNull('check_out_time')->exists()) {
                 return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah selesai absen hari ini.'], 409);
            }

            $isLate = false;
            if ($workSchedule) {
                if (Carbon::parse($currentTime)->gt(Carbon::parse($workSchedule->check_in_end))) {
                    $isLate = true;
                }
            }

            $snapIn = $user->check_in_start;
            if (!$snapIn && $workSchedule) $snapIn = $workSchedule->check_in_start;

            $snapOut = $user->check_out_start;
            if (!$snapOut && $workSchedule) $snapOut = $workSchedule->check_out_start;

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => $currentTime,
                'status' => 'verified',
                'presence_status' => 'Masuk',
                'photo_path' => $imageName, 
                'scanned_by_user_id' => $securityUser->id,
                'verified_by_user_id' => $securityUser->id, 
                'work_schedule_id' => $workSchedule?->id,
                'is_late_checkin' => $isLate,
                'attendance_type' => 'scan',
                'notes' => $manualNotes, 
                'scheduled_check_in' => $snapIn,
                'scheduled_check_out' => $snapOut,
            ]);

            $msg = $isLate ? "Absen MASUK (TERLAMBAT)" : "Absen MASUK Berhasil";

        } 
        elseif ($request->type == 'pulang') {
            
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(32))
                ->latest('check_in_time')
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan atau expired.'], 404);
            }

            $isEarlyCheckout = false;
            if ($workSchedule) {
                 $coTime = Carbon::parse($currentTime->format('H:i:s'));
                 $schedStart = Carbon::parse($workSchedule->check_out_start);
                 
                 if (Carbon::parse($workSchedule->check_in_start)->lt($schedStart)) {
                     if ($coTime->lt($schedStart)) $isEarlyCheckout = true;
                 } else {
                     if ($coTime->lt($schedStart) && $coTime->gt(Carbon::parse("00:00:00"))) $isEarlyCheckout = true;
                 }
            }

            $updateData = [
                'check_out_time' => $currentTime,
                'photo_out_path' => $imageName, 
                'is_early_checkout' => $isEarlyCheckout,
            ];

            if ($attendance->status != 'verified') {
                $updateData['status'] = 'verified';
                $updateData['verified_by_user_id'] = $securityUser->id;
            }

            $securityNote = ' | Pulang via Security Scan by ' . $securityUser->name;
            $userNoteString = $manualNotes ? " | Catatan: " . $manualNotes : "";
            
            $updateData['notes'] = ($attendance->notes ? $attendance->notes : '') . $userNoteString . $securityNote;

            $attendance->update($updateData);

            $msg = $isEarlyCheckout ? "Absen PULANG (PULANG CEPAT)" : "Absen PULANG Berhasil";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'name' => $user->name,
                'role' => $user->role, 
                'division' => $user->division->name ?? '-', 
                'branch' => $user->branch->name ?? '-', 
                'profile_photo' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'photo' => asset('storage/' . $imageName), 
                'notes' => $manualNotes, 
                'time' => $currentTime->format('H:i'),
                'date' => $currentTime->format('d M Y'),
                'is_late' => $isLate ?? false,
                'is_early_checkout' => $isEarlyCheckout ?? false,
            ]
        ]);
    }

    public function getStats(Request $request)
    {
         $securityUser = Auth::user(); $today = today();
         $stats = [
            'total_scans_today' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('updated_at', $today)->count(),
            'check_in_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->whereNotNull('check_in_time')->count(),
            'check_out_count' => Attendance::where(function($q) use ($securityUser, $today) {
                $q->where('scanned_by_user_id', $securityUser->id); 
            })->whereDate('check_out_time', $today)->whereNotNull('check_out_time')->count(),
            'late_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->where('is_late_checkin', true)->count(),
         ];
         return response()->json(['status' => 'success', 'data' => $stats]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with(['user.division', 'user.branch', 'branch', 'scanner', 'verifier']);

        if ($user->role == 'admin') {
            $query->where(function($q) {
                $q->whereNotNull('scanned_by_user_id')
                  ->orWhere('notes', 'LIKE', '%Security Scan by%');
            });
        } else {
            $query->where(function($q) use ($user) {
                $q->where('scanned_by_user_id', $user->id)
                  ->orWhere('notes', 'LIKE', '%Security Scan by ' . $user->name . '%');
            });
        }

        if ($request->date) {
            $query->whereDate('check_in_time', $request->date);
        }

        $logs = $query->orderBy('updated_at', 'desc')->paginate(10);
        return view('security.history', compact('logs'));
    }
}