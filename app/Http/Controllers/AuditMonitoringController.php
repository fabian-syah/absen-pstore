<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditMonitoringController extends Controller
{
    /**
     * Menampilkan daftar absensi yang telah diedit/diverifikasi oleh tim Audit.
     */
    public function index()
    {
        // Ambil ID semua user dengan role 'audit'
        $auditUserIds = User::where('role', 'audit')->pluck('id');

        // Ambil data absensi yang diverifikasi oleh Audit
        // Filter Berdasarkan Tipe: 
        // 1. 'manual' -> Hasil input langsung Audit atau Editan Audit (updateByAudit otomatis mengubah tipe ke manual)
        // 2. 'leave' -> Hasil persetujuan Izin/Cuti oleh Audit
        // Ini otomatis akan MEMBUANG tipe 'self' (selfie) dan 'scan' (QR) yang hanya diverifikasi tanpa editan.
        $attendances = Attendance::with(['user.branch', 'user.division', 'verifier'])
            ->whereIn('verified_by_user_id', $auditUserIds)
            ->where('status', 'verified')
            ->whereIn('attendance_type', ['manual', 'leave'])
            ->latest('updated_at')
            ->paginate(30);

        // [TAMBAHAN] Untuk data lama yang audit_note/audit_photo_path nya masih kosong, 
        // kita coba tarik dari LeaveRequest berdasarkan user_id dan tanggal.
        foreach ($attendances as $attendance) {
            if (empty($attendance->audit_note) || empty($attendance->audit_photo_path)) {
                $leave = \App\Models\LeaveRequest::where('user_id', $attendance->user_id)
                    ->whereDate('start_date', $attendance->check_in_time->format('Y-m-d'))
                    ->first();
                
                if ($leave) {
                    if (empty($attendance->audit_note)) {
                        $attendance->audit_note = "(Auto-Fetch) " . $leave->reason;
                    }
                    if (empty($attendance->audit_photo_path)) {
                        $attendance->audit_photo_path = $leave->file_proof;
                    }
                }
            }
        }

        return view('admin.audit_monitor', compact('attendances'));
    }

    /**
     * Menghapus editan audit dan mengembalikan status user menjadi Alpha (dengan cara menghapus record absensi).
     */
    public function revert($id)
    {
        $attendance = Attendance::findOrFail($id);
        
        Log::info('Super Admin menghapus editan audit', [
            'attendance_id' => $id,
            'user_id' => $attendance->user_id,
            'date' => $attendance->check_in_time->format('Y-m-d'),
            'audit_note' => $attendance->audit_note,
            'admin_id' => Auth::id()
        ]);

        // Simpan info untuk pesan sukses
        $userName = $attendance->user->name;
        $date = $attendance->check_in_time->format('d/m/Y');

        // Hapus record absensi. Secara sistem, jika tidak ada record absensi pada tanggal tersebut, 
        // maka status user akan dianggap Alpha atau Tidak Hadir di rekapitulasi.
        $attendance->delete();

        return back()->with('success', "Editan Audit untuk $userName pada tanggal $date telah dihapus. Status user kini kembali menjadi Alpha.");
    }
}
