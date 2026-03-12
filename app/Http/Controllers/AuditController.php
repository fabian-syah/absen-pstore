<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;

class AuditController extends Controller
{
    use SendFcmNotification;

    /**
     * Menampilkan daftar izin telat (HANYA PENDING - Status: pending)
     */
    public function showLatePermissions()
    {
        $user = Auth::user();

        Log::info('AuditController@showLatePermissions dipanggil', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role
        ]);

        // Hanya ambil yang status = 'pending' saja
        $query = LeaveRequest::with(['user.division', 'user.branch', 'approver'])
            ->where('status', 'pending'); // <-- INI HARUS 'pending'

        // Logika Hak Akses
        $isUniversalAccess = in_array($user->role, ['admin']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        $requests = $query->oldest()->paginate(10);

        Log::info('Data pending ditemukan di showLatePermissions', [
            'total' => $requests->total()
        ]);

        return view('leave_requests.index', compact('requests'));
    }

    /**
     * MENYETUJUI Absensi Mandiri (Verifikasi)
     * Route: audit.approve
     */
    public function approve($id)
    {
        $user = Auth::user();
        // Cari data absensi berdasarkan ID
        $attendance = Attendance::findOrFail($id);

        // [LOGIKA BARU] Cek apakah dia Leader Audit
        // Syarat: Role Audit DAN Nama Divisi mengandung kata 'Leader'
        $isLeaderAudit = $user->role == 'audit' && stripos($user->division->name ?? '', 'leader') !== false;

        // [VALIDASI] 
        // Jika absen milik sendiri DAN dia BUKAN Leader Audit, tolak akses.
        if ($attendance->user_id == $user->id && !$isLeaderAudit) {
            return back()->with('error', 'Anda tidak dapat memverifikasi absensi Anda sendiri.');
        }

        // status menjadi verified
        $attendance->update([
            'status' => 'verified',
            'verified_by_user_id' => $user->id,
            'audit_note' => 'Verified by ' . $user->name
        ]);

        // Kirim notifikasi ke user bahwa absennya diterima
        try {
            $title = "Absensi Disetujui";
            // Pastikan format jam notifikasi juga sesuai timezone user (opsional, disini default app timezone)
            $userTz = $attendance->user->branch->timezone ?? 'Asia/Jakarta';
            $checkInLocal = Carbon::parse($attendance->check_in_time)->timezone($userTz);

            $body = "Absensi mandiri Anda pada " . $checkInLocal->format('d/m/Y H:i') . " telah diverifikasi.";
            $this->sendNotificationToUser($attendance->user, $title, $body);
        } catch (\Exception $e) {
            // Abaikan error notifikasi agar tidak merusak flow
        }

        return back()->with('success', 'Absensi berhasil diverifikasi dan disetujui.');
    }

    /**
     * MENOLAK Absensi Mandiri
     * Route: audit.reject
     */
    public function reject($id)
    {
        $user = Auth::user();
        // Cari data absensi
        $attendance = Attendance::findOrFail($id);

        // [LOGIKA BARU] Cek Leader Audit
        $isLeaderAudit = $user->role == 'audit' && stripos($user->division->name ?? '', 'leader') !== false;

        // [VALIDASI] Mencegah Audit biasa menolak data diri sendiri
        if ($attendance->user_id == $user->id && !$isLeaderAudit) {
            return back()->with('error', 'Anda tidak dapat memproses absensi Anda sendiri.');
        }

        // Hapus file foto dari storage agar tidak menuh-menuhin server
        if ($attendance->photo_path && Storage::disk('public')->exists($attendance->photo_path)) {
            Storage::disk('public')->delete($attendance->photo_path);
        }

        if ($attendance->photo_out_path && Storage::disk('public')->exists($attendance->photo_out_path)) {
            Storage::disk('public')->delete($attendance->photo_out_path);
        }

        // Hapus data dari database (Karena tombolnya "Tolak/Hapus")
        $attendance->delete();

        return back()->with('success', 'Data absensi berhasil ditolak dan dihapus.');
    }

    /**
     * Menampilkan daftar absensi manual yang perlu diverifikasi
     */
    public function showVerificationList()
    {
        $user = Auth::user();
        $query = Attendance::with(['user.division', 'user.branch'])
            ->where('status', 'pending_verification')
            ->whereNotNull('photo_path');

        $isLeaderAudit = $user->role == 'audit' && stripos($user->division->name ?? '', 'leader') !== false;

        if (!$isLeaderAudit) {
            $query->where('user_id', '!=', $user->id);
        }

        $isUniversalAccess = in_array($user->role, ['admin']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        // Pagination yang optimal untuk performa (30 item per halaman)
        $pendingAttendances = $query->oldest()->paginate(30);

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    public function showLatePermissionsHistory()
    {
        $user = Auth::user();

        // Ambil semua kecuali pending
        $query = LeaveRequest::with(['user.division', 'user.branch', 'approver'])
            ->whereIn('status', ['approved', 'rejected', 'cancelled']);

        $isUniversalAccess = in_array($user->role, ['admin']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        $requests = $query->latest()->paginate(10);
        $page_title = 'Riwayat Pengajuan (Selesai)';

        return view('leave_requests.history', compact('requests', 'page_title'));
    }

    /**
     * HALAMAN RIWAYAT (HANYA DITOLAK)
     */
    public function showRejectedLatePermissionsHistory()
    {
        $user = Auth::user();

        // Ambil khusus rejected
        $query = LeaveRequest::with(['user.division', 'user.branch', 'approver'])
            ->where('status', 'rejected');

        $isUniversalAccess = in_array($user->role, ['admin']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        $requests = $query->latest()->paginate(10);
        $page_title = 'Riwayat Ditolak';

        return view('leave_requests.history', compact('requests', 'page_title'));
    }

    /**
     * Approve izin telat
     */
    public function approveLatePermission($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $approver = Auth::user();

        Log::info('AuditController@approveLatePermission dipanggil', [
            'leave_request_id' => $id,
            'current_status' => $leaveRequest->status,
            'approver_id' => $approver->id,
            'approver_name' => $approver->name
        ]);

        // === VALIDASI KHUSUS CABANG AUDIT (ID 64) ===
        // User request: "dia gabisa acc orang lain maupun diri sendiri untuk id 64... kecuali idlogin yang aku sebutin"
        if ($leaveRequest->user && $leaveRequest->user->branch_id == 64) {
            $allowedLogins = ['herlina', 'eva', 'agung', 'adminherlina'];

            $isSuperUser = in_array($approver->role, ['admin', 'super_admin']);
            $isWhitelisted = in_array(strtolower($approver->login_id), $allowedLogins);

            // Debug Log
            Log::info("DEBUG AUDIT VALIDATION:", [
                'actor_login' => $approver->login_id,
                'is_super' => $isSuperUser,
                'is_white' => $isWhitelisted
            ]);

            if (!$isSuperUser && !$isWhitelisted) {
                return redirect()->back()->with('swal_error', 'AKSES DITOLAK: Anda tidak memiliki akses untuk memverifikasi anggota tim ini.');
            }
        }

        // Validasi status
        if ($leaveRequest->status != 'pending') {
            Log::warning('Izin sudah diproses sebelumnya', [
                'leave_request_id' => $id,
                'current_status' => $leaveRequest->status
            ]);

            return back()->with('error', 'Izin ini sudah diproses sebelumnya (Status: ' . $leaveRequest->status . ').');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'is_active' => true,
        ]);

        Log::info('Izin berhasil diapprove', [
            'leave_request_id' => $id,
            'new_status' => 'approved',
            'approved_by' => $approver->id
        ]);

        // Cari atau buat data absensi untuk tanggal izin tersebut
        $attendance = Attendance::firstOrNew([
            'user_id' => $leaveRequest->user_id,
            // Pastikan format tanggal sesuai
            'check_in_time' => $leaveRequest->start_date->startOfDay()
        ]);

        // Mapping status dari tipe izin ke status kehadiran
        $statusMap = [
            'telat' => 'Masuk',
            'wfh' => 'WFH',
            'dinas' => 'Dinas Luar',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'libur' => 'Libur',
        ];

        $attendance->presence_status = $statusMap[$leaveRequest->type] ?? ucfirst($leaveRequest->type);
        $attendance->status = 'verified'; // Langsung verified karena sudah di-approve Audit
        $attendance->attendance_type = 'leave';
        $attendance->verified_by_user_id = $approver->id; // Set siapa yang memverifikasi (Audit)
        $attendance->save();

        // Kirim notifikasi
        $title = "Izin Disetujui";
        $body = "Pengajuan izin Anda pada " . $leaveRequest->start_date->format('d/m/Y') . " telah disetujui oleh " . $approver->name . ".";
        $this->sendNotificationToUser($leaveRequest->user, $title, $body);

        return redirect()->back()
            ->with('success', 'Izin telah disetujui dan dipindahkan ke riwayat.');
    }

    /**
     * Reject izin telat
     */
    public function rejectLatePermission(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);
        $approver = Auth::user();

        Log::info('AuditController@rejectLatePermission dipanggil', [
            'leave_request_id' => $id,
            'current_status' => $leaveRequest->status,
            'approver_id' => $approver->id,
            'approver_name' => $approver->name
        ]);

        // === VALIDASI KHUSUS CABANG AUDIT (ID 64) ===
        if ($leaveRequest->user && $leaveRequest->user->branch_id == 64) {
            $allowedLogins = ['herlina', 'eva', 'agung', 'adminherlina'];

            $isSuperUser = in_array($approver->role, ['admin', 'super_admin']);
            $isWhitelisted = in_array(strtolower($approver->login_id), $allowedLogins);

            if (!$isSuperUser && !$isWhitelisted) {
                return redirect()->back()->with('swal_error', 'AKSES DITOLAK: Anda tidak memiliki akses untuk memverifikasi anggota tim ini.');
            }
        }

        // Validasi status
        if ($leaveRequest->status != 'pending') {
            Log::warning('Izin sudah diproses sebelumnya (reject)', [
                'leave_request_id' => $id,
                'current_status' => $leaveRequest->status
            ]);

            return back()->with('error', 'Izin ini sudah diproses sebelumnya (Status: ' . $leaveRequest->status . ').');
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'is_active' => false,
            'rejection_reason' => $request->rejection_reason,
        ]);

        Log::info('Izin berhasil direject', [
            'leave_request_id' => $id,
            'new_status' => 'rejected',
            'approved_by' => $approver->id
        ]);

        // Kirim notifikasi
        $title = "Izin Ditolak";
        $body = "Pengajuan izin Anda pada " . $leaveRequest->start_date->format('d/m/Y') . " telah ditolak oleh " . $approver->name . ". Alasan: " . $request->rejection_reason;
        $this->sendNotificationToUser($leaveRequest->user, $title, $body);

        return redirect()->back()
            ->with('success', 'Izin telah ditolak dan dipindahkan ke riwayat.');
    }

    /**
     * Menampilkan daftar missed checkout
     */
    public function showMissedCheckouts()
    {
        $user = Auth::user();

        $query = Attendance::whereNull('check_out_time')
            ->whereDate('check_in_time', '<', today())
            ->with('user.division');

        $isUniversalAccess = in_array($user->role, ['admin', 'audit']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('users.branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        $missedCheckouts = $query->orderBy('check_in_time', 'asc')->get();

        return view('audit.missed_checkout_list', compact('missedCheckouts'));
    }

    /**
     * Update missed checkout
     */
    public function updateMissedCheckout(Request $request, $id)
    {
        $request->validate([
            'checkout_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string'
        ]);

        $attendance = Attendance::findOrFail($id);

        // [TIMEZONE FIX] Ambil Timezone Cabang User
        $branchTimezone = $attendance->user->branch->timezone ?? 'Asia/Jakarta';

        // Konversi checkin time ke waktu lokal user dulu untuk referensi tanggal
        $checkInDateLocal = Carbon::parse($attendance->check_in_time)->timezone($branchTimezone);
        $checkInDateStr = $checkInDateLocal->format('Y-m-d');

        // Buat Waktu Checkout Lokal sesuai input Audit
        $checkOutDateTimeLocal = Carbon::createFromFormat('Y-m-d H:i', $checkInDateStr . ' ' . $request->checkout_time, $branchTimezone);

        // Jika checkout kurang dari checkin (misal checkout jam 01:00 pagi besoknya), tambah 1 hari
        if ($checkOutDateTimeLocal->lt($checkInDateLocal)) {
            $checkOutDateTimeLocal->addDay();
        }

        // Konversi balik ke UTC/Server Time untuk disimpan di DB
        $checkOutForDB = $checkOutDateTimeLocal->copy()->setTimezone(config('app.timezone'));

        $attendance->update([
            'check_out_time' => $checkOutForDB,
            'status' => 'verified',
            'verified_by_user_id' => Auth::id(),
            'audit_note' => 'Manual checkout by Audit: ' . $request->notes
        ]);

        $title = "Absen Pulang Diperbarui";
        $body = "Audit telah mengatur jam pulang Anda untuk tanggal " . $checkInDateLocal->format('d/m/Y') . " menjadi jam " . $checkOutDateTimeLocal->format('H:i') . " (" . $branchTimezone . ").";
        $this->sendNotificationToUser($attendance->user, $title, $body);

        return back()->with('success', 'Absen pulang berhasil diperbarui manual.');
    }

    /**
     * Memverifikasi Absensi (Dari Modal Verifikasi)
     * Route: audit.verify.attendance
     */
    public function verifyAttendance(Request $request, $id)
    {
        $user = Auth::user();
        // 1. Validasi Input
        $request->validate([
            'presence_status' => 'required|string',
            'audit_photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:8192', // Max 8MB
            'audit_note' => 'nullable|string',
        ]);

        // 2. Cari Data Absensi
        $attendance = Attendance::findOrFail($id);

        // [LOGIKA BARU] Cek Leader Audit
        $isLeaderAudit = $user->role == 'audit' && stripos($user->division->name ?? '', 'leader') !== false;

        // [VALIDASI] Mencegah Audit verifikasi diri sendiri KECUALI Leader
        if ($attendance->user_id == $user->id && !$isLeaderAudit) {
            return back()->with('error', 'Anda tidak dapat memverifikasi absensi Anda sendiri.');
        }

        // 3. Siapkan Data Update
        $updateData = [
            'presence_status' => $request->presence_status,
            'status' => 'verified',
            'verified_by_user_id' => $user->id, // Simpan ID Audit yang memverifikasi
            'audit_note' => $request->audit_note,
        ];

        // 4. Handle Upload Foto Audit (Jika ada)
        if ($request->hasFile('audit_photo')) {
            // Hapus foto audit lama jika ada agar tidak menumpuk sampah file
            if ($attendance->audit_photo_path && Storage::disk('public')->exists($attendance->audit_photo_path)) {
                Storage::disk('public')->delete($attendance->audit_photo_path);
            }

            // Simpan foto baru
            $path = $request->file('audit_photo')->store('attendance/audit', 'public');
            $updateData['audit_photo_path'] = $path;
        }

        // 5. Update Database
        $attendance->update($updateData);

        // 6. Kirim Notifikasi ke User (Opsional)
        try {
            // Gunakan timezone user
            $userTz = $attendance->user->branch->timezone ?? 'Asia/Jakarta';
            $checkInLocal = Carbon::parse($attendance->check_in_time)->timezone($userTz);

            $title = "Verifikasi Absensi";
            $body = "Absensi tanggal " . $checkInLocal->format('d M Y') .
                " telah diverifikasi menjadi: " . $request->presence_status;

            if (method_exists($this, 'sendNotificationToUser')) {
                $this->sendNotificationToUser($attendance->user, $title, $body);
            }
        } catch (\Exception $e) {
            // Abaikan error notifikasi agar tidak menggagalkan proses simpan
            Log::error("Gagal kirim notif audit: " . $e->getMessage());
        }

        // 7. Redirect kembali
        return back()->with('success', 'Absensi berhasil diverifikasi.');
    }

    /**
     * Mengupdate/Mengoreksi Data Absensi (Dari Modal Koreksi/Edit)
     * Route: audit.update.attendance
     * * [UPDATE TIMEZONE AWARE]
     * Input dari admin diasumsikan sebagai waktu LOKAL CABANG USER.
     */
    public function updateAttendance(Request $request, $id)
    {
        $request->validate([
            'check_in_time' => 'required', // Jam masuk wajib ada (Format H:i)
            'check_out_time' => 'nullable', // Format H:i
            'presence_status' => 'required|string',
            'status' => 'required|string',
            'audit_note' => 'nullable|string',
            'audit_photo' => 'nullable|image|max:8192'
        ]);

        $attendance = Attendance::findOrFail($id);

        // 1. Identifikasi Timezone Cabang User
        $branchTimezone = $attendance->user->branch->timezone ?? 'Asia/Jakarta';

        // Ambil tanggal asli dalam timezone user agar tidak bergeser hari
        $originalDateLocal = Carbon::parse($attendance->check_in_time)->timezone($branchTimezone)->format('Y-m-d');

        // 2. Proses Jam Masuk (Parse sebagai waktu lokal user)
        $newCheckInLocal = Carbon::createFromFormat('Y-m-d H:i', $originalDateLocal . ' ' . $request->check_in_time, $branchTimezone);
        // Konversi ke App Timezone (UTC/WIB) untuk simpan ke DB
        $newCheckInDB = $newCheckInLocal->copy()->setTimezone(config('app.timezone'));

        // 3. Proses Jam Pulang (Jika ada)
        $newCheckOutDB = null;
        if ($request->check_out_time) {
            $newCheckOutLocal = Carbon::createFromFormat('Y-m-d H:i', $originalDateLocal . ' ' . $request->check_out_time, $branchTimezone);

            // Jika jam pulang lebih kecil dari jam masuk, asumsikan lewat tengah malam (tambah 1 hari)
            if ($newCheckOutLocal->lt($newCheckInLocal)) {
                $newCheckOutLocal->addDay();
            }

            $newCheckOutDB = $newCheckOutLocal->copy()->setTimezone(config('app.timezone'));
        }

        // 4. Siapkan Data Update
        $updateData = [
            'check_in_time' => $newCheckInDB,
            'check_out_time' => $newCheckOutDB,
            'presence_status' => $request->presence_status,
            'status' => $request->status,
            'audit_note' => $request->audit_note . ' (Dikoreksi Audit: ' . Auth::user()->name . ')',
            'verified_by_user_id' => Auth::id(),
        ];

        // 5. Handle Foto Bukti (Jika ada upload baru saat koreksi)
        if ($request->hasFile('audit_photo')) {
            if ($attendance->audit_photo_path && Storage::disk('public')->exists($attendance->audit_photo_path)) {
                Storage::disk('public')->delete($attendance->audit_photo_path);
            }
            $path = $request->file('audit_photo')->store('attendance/audit', 'public');
            $updateData['audit_photo_path'] = $path;
        }

        // 6. Simpan Perubahan
        $attendance->update($updateData);

        return back()->with('success', 'Data absensi berhasil dikoreksi (Waktu disesuaikan dengan timezone cabang user: ' . $branchTimezone . ').');
    }

    /**
     * Helper method untuk mengirim notifikasi ke satu user.
     */
    private function sendNotificationToUser($user, $title, $body)
    {
        // 1. Cek apakah user ada dan punya token
        if (!$user || !$user->fcm_token) {
            Log::info("Skip notifikasi: User tidak ditemukan atau tidak punya token FCM.");
            return;
        }

        try {
            if (method_exists($this, 'sendNotification')) {
                $this->sendNotification($user->fcm_token, $title, $body);
            } else {
                Log::warning("Method 'sendNotification' tidak ditemukan di Trait SendFcmNotification.");
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi ke user " . $user->name . ": " . $e->getMessage());
        }
    }

    /**
     * Menyimpan Data Absensi Baru (Input Manual oleh Audit)
     * Digunakan untuk mengisi tanggal yang statusnya Alpha/Kosong
     * Route: audit.store.attendance
     */
    public function storeByAudit(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',      // Tanggal yang sedang diisi (Y-m-d)
            'check_in_time' => 'nullable',           // Jam masuk (H:i)
            'presence_status' => 'required|string',
            'audit_note' => 'nullable|string',
            'audit_photo' => 'nullable|image|max:8192'
        ]);

        // 2. Ambil User & Timezone Cabangnya
        $user = User::findOrFail($request->user_id);
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';

        // 3. Proses Waktu Check-In
        // Gabungkan Tanggal (dari hidden input) + Jam (dari input time) sesuai timezone cabang
        $timeIn = $request->check_in_time ?: '08:00';
        $checkInDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $timeIn, $branchTimezone);

        // Convert ke UTC/App Timezone untuk disimpan ke DB
        $checkInDB = $checkInDateTime->copy()->setTimezone(config('app.timezone'));

        // 4. Proses Waktu Check-Out (Jika ada)
        $checkOutDB = null;
        if ($request->check_out_time) {
            $checkOutDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->check_out_time, $branchTimezone);

            // Jika jam pulang lebih kecil dari jam masuk (misal pulang dini hari 01:00), anggap besoknya (+1 hari)
            if ($checkOutDateTime->lt($checkInDateTime)) {
                $checkOutDateTime->addDay();
            }
            $checkOutDB = $checkOutDateTime->copy()->setTimezone(config('app.timezone'));
        }

        // 5. Handle Upload Foto Bukti Audit
        $auditPhotoPath = null;
        if ($request->hasFile('audit_photo')) {
            $auditPhotoPath = $request->file('audit_photo')->store('attendance/audit', 'public');
        }

        // 6. Simpan Data Baru ke Database
        Attendance::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id ?? 1,
            'check_in_time' => $checkInDB,
            'check_out_time' => $checkOutDB,
            'presence_status' => $request->presence_status,
            'status' => 'verified',     // Langsung verified karena diinput oleh Audit
            'attendance_type' => 'manual',       // Tipe manual
            'audit_note' => $request->audit_note . ' (Input Manual Audit: ' . Auth::user()->name . ')',
            'audit_photo_path' => $auditPhotoPath,
            'verified_by_user_id' => Auth::id(),
            'is_late_checkin' => false,          // Default false, atau bisa tambahkan logika hitung telat jika mau
            'is_early_checkout' => false,
        ]);

        return back()->with('success', 'Data absensi baru berhasil ditambahkan.');
    }
} // <--- Ini penutup class AuditController
