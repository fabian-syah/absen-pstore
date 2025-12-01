<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;

class AuditController extends Controller
{
    use SendFcmNotification;

    /**
     * Menampilkan daftar absensi mandiri yang butuh verifikasi (Status: Pending)
     */
    public function showVerificationList()
    {
        $user = Auth::user();

        $query = Attendance::where('status', 'pending_verification')
            ->with(['user.division', 'user.branch']);

        $isUniversalAccess = in_array($user->role, ['admin']);

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

        $pendingAttendances = $query->latest()->get();

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    /**
     * Menyetujui Absensi Mandiri
     */
    public function approve(Attendance $attendance)
    {
        $attendance->update([
            'status' => 'verified',
            'verified_by_user_id' => Auth::id()
        ]);

        $title = "Absensi Disetujui";
        $body = "Absen mandiri Anda pada " . $attendance->check_in_time->format('d/m/Y') . " telah disetujui.";
        $this->sendNotificationToUser($attendance->user, $title, $body);

        return back()->with('success', 'Absensi disetujui.');
    }

    /**
     * Menolak Absensi Mandiri
     */
    public function reject(Attendance $attendance)
    {
        $user = $attendance->user;
        $date = $attendance->check_in_time->format('d/m/Y');

        if ($attendance->photo_path) {
            Storage::delete($attendance->photo_path);
        }
        $attendance->delete();

        $title = "Absensi Ditolak";
        $body = "Absen mandiri Anda pada " . $date . " ditolak oleh Audit.";
        $this->sendNotificationToUser($user, $title, $body);

        return back()->with('success', 'Absensi ditolak dan dihapus.');
    }

    /**
     * Verifikasi detail attendance
     */
    public function verifyAttendance(Request $request, Attendance $attendance)
    {
        $request->validate([
            'presence_status' => 'required|string|in:Masuk,WFH / Dinas Luar,Izin Telat,Sakit,Cuti,Alpha',
            'audit_photo' => 'nullable|image|max:5120',
            'audit_note' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        $auditPhotoPath = $attendance->audit_photo_path;

        if ($request->hasFile('audit_photo')) {
            if ($auditPhotoPath && Storage::disk('public')->exists($auditPhotoPath)) {
                Storage::disk('public')->delete($auditPhotoPath);
            }
            $auditPhotoPath = $request->file('audit_photo')->store('audit-evidence', 'public');
        }

        $attendance->update([
            'status' => 'verified',
            'presence_status' => $request->presence_status,
            'audit_photo_path' => $auditPhotoPath,
            'audit_note' => $request->audit_note,
            'verified_by_user_id' => $user->id,
        ]);

        $title = "Absensi Diverifikasi";
        $body = "Absensi Anda tanggal " . $attendance->check_in_time->format('d/m/Y') . " telah diverifikasi sebagai: " . $request->presence_status;
        $this->sendNotificationToUser($attendance->user, $title, $body);

        return back()->with('success', 'Absensi berhasil diverifikasi.');
    }

    /**
     * Menampilkan daftar izin telat (HANYA PENDING - Status: pending)
     */
    public function showLatePermissions()
    {
        $user = Auth::user();

        // FIX: Hanya ambil yang status = 'pending' saja
        $query = LeaveRequest::with(['user.division', 'user.branch'])
            ->where('status', 'pending'); // <-- INI PASTIKAN 'pending'

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

        return view('leave_requests.index', compact('requests'));
    }

    /**
     * HALAMAN RIWAYAT (Approved, Rejected, Cancelled)
     */
    public function showLatePermissionsHistory()
    {
        $user = Auth::user();

        // FIX: Ambil semua kecuali pending
        $query = LeaveRequest::with(['user.division', 'user.branch'])
            ->whereIn('status', ['approved', 'rejected', 'cancelled']); // <-- INI PASTIKAN

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
     * Approve izin telat
     */
    public function approveLatePermission($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        // FIX: Validasi status
        if ($leaveRequest->status != 'pending') {
            return back()->with('error', 'Izin ini sudah diproses sebelumnya (Status: ' . $leaveRequest->status . ').');
        }
        
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        $title = "Izin Disetujui";
        $body = "Pengajuan izin Anda pada " . $leaveRequest->start_date->format('d/m/Y') . " telah disetujui.";
        $this->sendNotificationToUser($leaveRequest->user, $title, $body);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Izin telah disetujui dan dipindahkan ke riwayat.');
    }

    /**
     * Reject izin telat
     */
    public function rejectLatePermission($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        // FIX: Validasi status
        if ($leaveRequest->status != 'pending') {
            return back()->with('error', 'Izin ini sudah diproses sebelumnya (Status: ' . $leaveRequest->status . ').');
        }
        
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        $title = "Izin Ditolak";
        $body = "Pengajuan izin Anda pada " . $leaveRequest->start_date->format('d/m/Y') . " telah ditolak.";
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
}