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
        
        // REVISI LOGIC: Cari sesi aktif dalam 32 jam terakhir (untuk cover lembur lintas hari)
        // Jangan pakai whereDate($today) untuk cek sesi aktif, karena kalau lewat jam 00:00 akan null.
        $attendanceSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(32)) 
            ->latest('check_in_time')
            ->first();

        // Jika tidak ada sesi aktif (artinya mau absen masuk), baru cek Cuti & Absen hari ini
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
            
            // Cek apakah hari ini SUDAH selesai (Masuk & Pulang done)
            // Ini tetap pakai $today karena untuk mencegah absen masuk 2x di hari yang sama
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

        $user = User::find($request->user_id);
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

        // ============================
        // LOGIC ABSEN MASUK
        // ============================
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

            // Bersihkan sesi yang SANGAT lama (lebih dari 32 jam) -> Auto close
            Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '<', $currentTime->copy()->subHours(32))
                ->update(['check_out_time' => DB::raw("DATE_ADD(check_in_time, INTERVAL 12 HOUR)"), 'notes' => 'Auto-closed by Security Scan (Expired)']);

            // Cek Sesi Aktif dalam 32 Jam terakhir (Untuk mencegah double login saat lembur)
            if (Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', $currentTime->copy()->subHours(32))
                ->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini masih memiliki sesi aktif (Belum Pulang)! Silakan pilih tombol PULANG.'], 409);
            }
            
            // Cek apakah hari ini sudah selesai
            if (Attendance::where('user_id', $user->id)->whereDate('check_in_time', $today)->whereNotNull('check_out_time')->exists()) {
                 return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah selesai absen (Masuk & Pulang) hari ini.'], 409);
            }

            $isLate = false;
            if ($workSchedule) {
                if (Carbon::parse($currentTime)->gt(Carbon::parse($workSchedule->check_in_end))) {
                    $isLate = true;
                }
            }

            Attendance::create([
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
            ]);

            $msg = $isLate ? "Absen MASUK Berhasil (TERLAMBAT)" : "Absen MASUK Berhasil";

        } 
        // ============================
        // LOGIC ABSEN PULANG
        // ============================
        elseif ($request->type == 'pulang') {
            
            // REVISI: Lookback diperluas ke 32 Jam agar cover lembur parah
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->where('check_in_time', '>=', Carbon::now()->subHours(32))
                ->latest('check_in_time')
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini belum absen masuk atau sesi sudah expired!'], 404);
            }

            $isEarlyCheckout = false;
            if ($workSchedule) {
                 $coTime = Carbon::parse($currentTime->format('H:i:s'));
                 $schedStart = Carbon::parse($workSchedule->check_out_start);
                 
                 // Logic cek pulang cepat
                 if (Carbon::parse($workSchedule->check_in_start)->lt($schedStart)) {
                     if ($coTime->lt($schedStart)) $isEarlyCheckout = true;
                 } else {
                     // Shift malam (lintas hari)
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
            
            $updateData['notes'] = $attendance->notes . $userNoteString . $securityNote;

            $attendance->update($updateData);

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

        $query->whereNotNull('scanned_by_user_id');

        if ($user->role !== 'admin') {
            $query->where(function($q) use ($user) {
                $q->where('scanned_by_user_id', $user->id)
                  ->orWhere('verified_by_user_id', $user->id);
            });
        }

        if ($request->date) {
            $query->whereDate('check_in_time', $request->date);
        }

        $logs = $query->orderBy('updated_at', 'desc')->paginate(10);

        return view('security.history', compact('logs'));
    }
}