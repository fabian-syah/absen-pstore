<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
use App\Models\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Traits\SendFcmNotification;

class ScanController extends Controller
{
    use SendFcmNotification;

    public function index()
    {
        return view('security.scan');
    }

    // Helper Offset
    private function getOffset($timezone)
    {
        return Carbon::now($timezone)->format('P');
    }

    public function checkUser(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);
        $user = User::with(['division', 'branch'])->where('qr_code_value', $request->qr_code)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak ditemukan.'], 404);
        }

        // [TIMEZONE]
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $localNow = Carbon::now($branchTimezone);
        $todayLocal = $localNow->copy()->startOfDay();

        $attendanceSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $localNow->copy()->subHours(24)) // Konsisten 24 jam
            ->where('attendance_type', '!=', 'leave')
            ->latest('check_in_time')
            ->first();

        if (!$attendanceSession) {
            /* [REMOVED] Cooldown Lintas Hari (Requested: Urgensi Karyawan Lanjut Shift)
            $lastRecentCheckout = Attendance::where('user_id', $user->id)
                ->whereNotNull('check_out_time')
                ->where('check_out_time', '>=', $localNow->copy()->subHours(4))
                ->latest('check_out_time')
                ->first();
...
                }
            }
            */

            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', '!=', 'telat')
                ->whereDate('start_date', '<=', $todayLocal)
                ->whereDate('end_date', '>=', $todayLocal)
                ->first();

            if ($isOnLeave) {
                $endDateFormat = Carbon::parse($isOnLeave->end_date)->format('d M Y');
                return response()->json([
                    'status' => 'error',
                    'message' => "User sedang {$isOnLeave->type} (Sampai {$endDateFormat}). Tidak dapat melakukan absen."
                ], 403);
            }

            // Cek Sesi Hari Ini (Lokal)
            $branchOffset = $this->getOffset($branchTimezone);
            $storageOffset = '+00:00';

            $attendanceSession = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $todayLocal->format('Y-m-d')])
                ->latest('check_in_time')
                ->first();
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'rank_title' => $user->calculateRank()['name'],
                'rank_image' => $user->calculateRank()['rank_image'] ? asset($user->calculateRank()['rank_image']) : null,
                'rank_icon' => $user->calculateRank()['icon'],
                'rank_color' => $user->calculateRank()['color'],
                'rank_effect' => $user->calculateRank()['effect_class'],
                'role' => $user->role,
                'division' => $user->division->name ?? '-',
                'branch' => $user->branch?->name ?? 'Pusat',
                'photo_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'attendance_status' => $attendanceSession ? [
                    'has_checked_in' => !is_null($attendanceSession->check_in_time),
                    'has_checked_out' => !is_null($attendanceSession->check_out_time),
                    // Tampilkan di HP security sesuai timezone cabang user
                    'check_in_time' => $attendanceSession->check_in_time ? Carbon::parse($attendanceSession->check_in_time)->timezone($branchTimezone)->format('H:i') : null,
                    'check_out_time' => $attendanceSession->check_out_time ? Carbon::parse($attendanceSession->check_out_time)->timezone($branchTimezone)->format('H:i') : null,
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
        $currentTime = now(); // WIB Server

        // [TIMEZONE]
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $localTime = Carbon::now($branchTimezone);
        $todayLocal = $localTime->copy()->startOfDay();

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
                ->whereDate('start_date', '<=', $todayLocal)
                ->whereDate('end_date', '>=', $todayLocal)
                ->exists();

            if ($isOnLeave) {
                return response()->json(['status' => 'error', 'message' => 'Gagal: User sedang dalam status Cuti/Izin.'], 403);
            }

            Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', $localTime->copy()->subHours(24))
                ->update(['check_out_time' => DB::raw("DATE_ADD(check_in_time, INTERVAL 12 HOUR)"), 'notes' => 'Auto-closed by Security Scan (Expired)']);

            // Check existing session today (Timezone Aware)
            $todayDateLocal = Carbon::now($branchTimezone)->format('Y-m-d');
            $branchOffset = $this->getOffset($branchTimezone);
            $storageOffset = '+00:00';

            $existingAttendanceToday = Attendance::where('user_id', $user->id)
                ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $todayDateLocal])
                ->first();

            if ($existingAttendanceToday) {
                // Update existing record
                $existingAttendanceToday->update([
                    'check_in_time' => $currentTime,
                    'photo_path' => $imageName,
                    'attendance_type' => 'scan',
                    'status' => 'verified',
                    'scanned_by_user_id' => $securityUser->id,
                    // Preserve presence_status if it was already set (e.g. Izin Telat)
                    'presence_status' => $existingAttendanceToday->presence_status ?: 'Masuk',
                ]);
                return response()->json(['status' => 'success', 'message' => 'Absen Masuk tercatat (Update Record).']);
            }

            // Cek sudah absen hari ini (Lokal)
            if (
                Attendance::where('user_id', $user->id)
                    ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $todayLocal->format('Y-m-d')])
                    ->whereNotNull('check_out_time')->exists()
            ) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah selesai absen hari ini.'], 409);
            }

            $isLate = false;
            if ($workSchedule) {
                // Convert Jadwal ke Lokal DateTime hari ini
                $scheduleEndStr = Carbon::parse($workSchedule->check_in_end)->format('H:i:s');
                $scheduleEndLocal = Carbon::createFromFormat('Y-m-d H:i:s', $localTime->format('Y-m-d') . ' ' . $scheduleEndStr, $branchTimezone);

                if ($localTime->gt($scheduleEndLocal)) {
                    $isLate = true;
                }
            }

            $snapIn = $user->check_in_start;
            if (!$snapIn && $workSchedule)
                $snapIn = $workSchedule->check_in_start;

            $snapOut = $user->check_out_start;
            if (!$snapOut && $workSchedule)
                $snapOut = $workSchedule->check_out_start;

            // Cek izin telat yang sudah di-approve hari ini
            $latePermission = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'telat')
                ->where('status', 'approved')
                ->whereDate('start_date', $todayLocal->format('Y-m-d'))
                ->first();

            $presenceStatus = $latePermission ? 'Izin Telat' : 'Masuk';

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => $currentTime,
                'status' => 'verified',
                'presence_status' => $presenceStatus,
                'photo_path' => $imageName,
                'scanned_by_user_id' => $securityUser->id,
                'verified_by_user_id' => $securityUser->id,
                'work_schedule_id' => $workSchedule?->id,
                'is_late_checkin' => $isLate || (bool) $latePermission,
                'attendance_type' => 'scan',
                'notes' => $manualNotes,
                'scheduled_check_in' => $snapIn,
                'scheduled_check_out' => $snapOut,
            ]);

            $msg = $isLate ? "Absen MASUK (TERLAMBAT)" : "Absen MASUK Berhasil";
        } elseif ($request->type == 'pulang') {

            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', $localTime->copy()->subHours(24)) // Konsisten 24 jam
                ->where('attendance_type', '!=', 'leave')
                ->latest('check_in_time')
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan atau expired.'], 404);
            }

            $isEarlyCheckout = false;
            if ($workSchedule) {
                // Logic Pulang Cepat
                $scheduleStartStr = Carbon::parse($workSchedule->check_out_start)->format('H:i:s');
                $scheduleStartLocal = Carbon::createFromFormat('Y-m-d H:i:s', $localTime->format('Y-m-d') . ' ' . $scheduleStartStr, $branchTimezone);

                if ($localTime->lt($scheduleStartLocal)) {
                    $isEarlyCheckout = true;
                }
            }

            // [FIX UTAMA]: Pastikan scanned_out_by_user_id tersimpan
            $updateData = [
                'check_out_time' => $currentTime,
                'photo_out_path' => $imageName,
                'is_early_checkout' => $isEarlyCheckout,
                'scanned_out_by_user_id' => $securityUser->id, // INI KOLOMNYA
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
                'rank_title' => $user->calculateRank()['name'],
                'rank_image' => $user->calculateRank()['rank_image'] ? asset($user->calculateRank()['rank_image']) : null,
                'rank_icon' => $user->calculateRank()['icon'],
                'rank_color' => $user->calculateRank()['color'],
                'rank_effect' => $user->calculateRank()['effect_class'],
                'role' => $user->role,
                'division' => $user->division->name ?? '-',
                'branch' => $user->branch?->name ?? '-',
                'profile_photo' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'photo' => asset('storage/' . $imageName),
                'notes' => $manualNotes,
                'time' => $localTime->format('H:i'),
                'date' => $localTime->format('d M Y'),
                'is_late' => $isLate ?? false,
                'is_early_checkout' => $isEarlyCheckout ?? false,
            ]
        ]);
    }

    public function getStats(Request $request)
    {
        $securityUser = Auth::user();
        $today = today();
        $branchIds = $securityUser->branches()->pluck('branches.id')->toArray();
        $branchIds[] = $securityUser->branch_id;
        $branchIds = array_unique(array_filter($branchIds));

        $stats = [
            'total_scans_today' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('updated_at', $today)->count(),
            'check_in_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->whereNotNull('check_in_time')->count(),
            'check_out_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_out_time', $today)->whereNotNull('check_out_time')->count(),
            'late_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->where('is_late_checkin', true)->count(),
            
            // [NEW] Counter Real-time Cabang
            'branch_total_in' => Attendance::whereIn('branch_id', $branchIds)->whereDate('check_in_time', $today)->count(),
            'branch_still_in' => Attendance::whereIn('branch_id', $branchIds)->whereDate('check_in_time', $today)->whereNull('check_out_time')->count(),
        ];
        return response()->json(['status' => 'success', 'data' => $stats]);
    }

    /**
     * [NEW] Memanggil bantuan darurat ke Admin melalui FCM
     */
    public function sendPanicMessage(Request $request)
    {
        $user = Auth::user();
        $message = $request->input('message', 'BUTUH BANTUAN SEGERA DI GERBANG!');
        
        $adminTokens = User::whereIn('role', ['admin', 'audit'])
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($adminTokens)) {
            return response()->json(['status' => 'error', 'message' => 'Admin tidak sedang online.']);
        }

        // [NEW] Buat record Broadcast agar muncul di dashboard Admin (Polling)
        Broadcast::create([
            'title' => '🚨 DARURAT: ' . $user->name,
            'message' => $message . ' (Lokasi: ' . ($user->branch->name ?? 'Unknown') . ')',
            'priority' => 'high',
            'created_by' => $user->id,
            'is_published' => true,
            'published_at' => now()
        ]);

        // [NEW] Kirim real-time push notification ke semua Admin/Audit via Trait
        $this->sendNotificationToBranchRoles(['admin', 'audit'], null, '🚨 EMERGENCY: ' . $user->name, $message);

        return response()->json([
            'status' => 'success', 
            'message' => 'Pesan darurat terkirim ke ' . count($adminTokens) . ' Admin.'
        ]);
    }

    /**
     * [NEW] Mengambil catatan absensi user selama 3 hari terakhir
     */
    public function getUserNotes($id)
    {
        $notes = Attendance::where('user_id', $id)
            ->whereNotNull('notes')
            ->where('check_in_time', '>=', now()->subDays(3))
            ->orderByDesc('check_in_time')
            ->get(['check_in_time', 'notes', 'presence_status']);

        return response()->json(['status' => 'success', 'data' => $notes]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with(['user.division', 'user.branch', 'branch', 'scanner', 'verifier']);

        if ($user->role == 'admin') {
            $query->where(function ($q) {
                $q->whereNotNull('scanned_by_user_id')
                    ->orWhere('notes', 'LIKE', '%Security Scan by%');
            });
        } else {
            $query->where(function ($q) use ($user) {
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