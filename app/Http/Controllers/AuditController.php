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

    // ... method lainnya ...

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

        $requests = $query->latest()->paginate(10);

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
        // Cari data absensi berdasarkan ID
        $attendance = Attendance::findOrFail($id);

        //  status menjadi verified
        $attendance->update([
            'status' => 'verified',
            'verified_by_user_id' => Auth::id(),
            'audit_note' => 'Verified by ' . Auth::user()->name
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
        // Cari data absensi
        $attendance = Attendance::findOrFail($id);

        // Hapus file foto dari storage agar tidak menuh-menuhin server
        if ($attendance->photo_path && Storage::disk('public')->exists($attendance->photo_path)) {
            Storage::disk('public')->delete($attendance->photo_path);
        }

        if ($attendance->photo_out_path && Storage::disk('public')->exists($attendance->photo_out_path)) {
            Storage::disk('public')->delete($attendance->photo_out_path);
        }

        // Hapus data dari database (Karena tombolnya "Tolak/Hapus")
        $attendance->delete();

        // OPSI: Jika tidak ingin dihapus tapi cuma diganti status, pakai ini:
        // $attendance->update(['status' => 'rejected', 'verified_by_user_id' => Auth::id()]);

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
            // UBAH BARIS DI BAWAH INI:
            // Dari ->where('status', 'pending') 
            // Menjadi:
            ->where('status', 'pending_verification')
            ->whereNotNull('photo_path');

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

        $pendingAttendances = $query->latest()->get();

        // 3. Return View
        // Pastikan file blade yang kamu paste tadi disimpan di folder:
        // resources/views/audit/verification_list.blade.php (atau sesuaikan namanya)
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

        $requests = $query->latest()->paginate(10);

        return view('leave_requests.history', compact('requests'));
    }

    /**
     * Approve izin telat - DIUBAH untuk menggunakan kolom 'approved_by'
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

        // PERBAIKAN: Gunakan kolom 'approved_by' bukan 'approved_by_user_id'
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id, // <- INI YANG DIPERBAIKI
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
     * Reject izin telat - DIUBAH untuk menggunakan kolom 'approved_by'
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

        // PERBAIKAN: Gunakan kolom 'approved_by' bukan 'approved_by_user_id'
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approver->id, // <- INI YANG DIPERBAIKI
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
        // 1. Validasi Input
        $request->validate([
            'presence_status' => 'required|string',
            'audit_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'audit_note'      => 'nullable|string',
        ]);

        // 2. Cari Data Absensi
        $attendance = Attendance::findOrFail($id);

        // 3. Siapkan Data Update
        $updateData = [
            'presence_status'     => $request->presence_status,
            'status'              => 'verified',
            'verified_by_user_id' => Auth::id(), // Simpan ID Audit yang memverifikasi
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

            // Pastikan method sendNotificationToUser ada di Trait SendFcmNotification Anda
            // Jika menggunakan trait bawaan sebelumnya, mungkin perlu penyesuaian nama method
            if (method_exists($this, 'sendNotificationToUser')) {
                $this->sendNotificationToUser($attendance->user, $title, $body);
            } else {
                // Fallback jika menggunakan method generic (misal kirim ke token user langsung)
                // $this->sendFCM($attendance->user->fcm_token, $title, $body);
            }
            
        } catch (\Exception $e) {
            // Abaikan error notifikasi agar tidak menggagalkan proses simpan
            Log::error("Gagal kirim notif audit: " . $e->getMessage());
        }

        // 7. Redirect kembali
        return back()->with('success', 'Absensi berhasil diverifikasi.');
    }
}
