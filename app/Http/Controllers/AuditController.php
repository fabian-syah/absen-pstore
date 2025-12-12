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
            $body = "Absensi mandiri Anda pada " . $attendance->check_in_time->format('d/m/Y H:i') . " telah diverifikasi.";
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

        // 1. Query Dasar: Ambil data Attendance
        $query = Attendance::with(['user.division', 'user.branch'])
            ->where('status', 'pending_verification')
            ->whereNotNull('photo_path');

        // [LOGIKA BARU] Cek apakah dia Leader Audit
        // Syarat: Role Audit DAN Nama Divisi mengandung kata 'Leader'
        $isLeaderAudit = $user->role == 'audit' && stripos($user->division->name ?? '', 'leader') !== false;

        // [PENTING] FILTER:
        // Jika dia BUKAN Leader Audit, sembunyikan data diri sendiri.
        // Jika dia Leader Audit, biarkan data diri sendiri muncul.
        if (!$isLeaderAudit) {
            $query->where('user_id', '!=', $user->id);
        }

        // 2. Logika Hak Akses (Copy dari method showLatePermissions)
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
                // Jika user audit tidak punya cabang pegangan, kosongkan hasil
                $query->where('id', 0);
            }
        }

        $pendingAttendances = $query->oldest()->get();

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    /**
     * HALAMAN RIWAYAT (Approved, Rejected, Cancelled)
     */
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

        $requests = $query->oldest()->paginate(10);

        return view('leave_requests.history', compact('requests'));
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

        // Kirim notifikasi
        $title = "Izin Disetujui";
        $body = "Pengajuan izin Anda pada " . $leaveRequest->start_date->format('d/m/Y') . " telah disetujui oleh " . $approver->name . ".";
        $this->sendNotificationToUser($leaveRequest->user, $title, $body);

        return redirect()->route('leave-requests.index')
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

        return redirect()->route('leave-requests.index')
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

        $checkInDate = Carbon::parse($attendance->check_in_time);
        $checkOutDateTime = Carbon::parse($checkInDate->format('Y-m-d') . ' ' . $request->checkout_time);

        if ($checkOutDateTime->lt($attendance->check_in_time)) {
            $checkOutDateTime->addDay();
        }

        $attendance->update([
            'check_out_time' => $checkOutDateTime,
            'status' => 'verified',
            'verified_by_user_id' => Auth::id(),
            'audit_note' => 'Manual checkout by Audit: ' . $request->notes
        ]);

        $title = "Absen Pulang Diperbarui";
        $body = "Audit telah mengatur jam pulang Anda untuk tanggal " . $checkInDate->format('d/m/Y') . " menjadi jam " . $checkOutDateTime->format('H:i') . ".";
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
            'audit_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'audit_note'      => 'nullable|string',
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
            'presence_status'     => $request->presence_status,
            'status'              => 'verified',
            'verified_by_user_id' => $user->id, // Simpan ID Audit yang memverifikasi
            'audit_note'          => $request->audit_note,
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
            $title = "Verifikasi Absensi";
            $body  = "Absensi tanggal " . $attendance->check_in_time->format('d M Y') .
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
     */
    public function updateAttendance(Request $request, $id)
    {
        $request->validate([
            'check_in_time'   => 'required', // Jam masuk wajib ada
            'check_out_time'  => 'nullable',
            'presence_status' => 'required|string',
            'status'          => 'required|string',
            'audit_note'      => 'nullable|string',
            'audit_photo'     => 'nullable|image|max:2048'
        ]);

        $attendance = Attendance::findOrFail($id);

        // 1. Atur Jam Masuk & Pulang (Gabungkan Tanggal Asli + Jam Baru)
        $originalDate = $attendance->check_in_time->format('Y-m-d');

        // Parse jam baru dari input form
        $newCheckIn  = Carbon::parse($originalDate . ' ' . $request->check_in_time);

        $newCheckOut = null;
        if ($request->check_out_time) {
            $newCheckOut = Carbon::parse($originalDate . ' ' . $request->check_out_time);
            // Jika jam pulang lebih kecil dari jam masuk, asumsikan lewat tengah malam (tambah 1 hari)
            if ($newCheckOut->lt($newCheckIn)) {
                $newCheckOut->addDay();
            }
        }

        // 2. Siapkan Data Update
        $updateData = [
            'check_in_time'       => $newCheckIn,
            'check_out_time'      => $newCheckOut,
            'presence_status'     => $request->presence_status, // Izin, Libur, Masuk, dll
            'status'              => $request->status, // verified, pending, rejected
            'audit_note'          => $request->audit_note . ' (Dikoreksi: ' . Auth::user()->name . ')',
            'verified_by_user_id' => Auth::id(),
        ];

        // 3. Handle Foto Bukti (Jika ada upload baru saat koreksi)
        if ($request->hasFile('audit_photo')) {
            if ($attendance->audit_photo_path && Storage::disk('public')->exists($attendance->audit_photo_path)) {
                Storage::disk('public')->delete($attendance->audit_photo_path);
            }
            $path = $request->file('audit_photo')->store('attendance/audit', 'public');
            $updateData['audit_photo_path'] = $path;
        }

        // 4. Simpan Perubahan
        $attendance->update($updateData);

        return back()->with('success', 'Data absensi berhasil dikoreksi.');
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
}