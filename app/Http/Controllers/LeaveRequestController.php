<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveRequestController extends Controller
{
    /**
     * MENAMPILKAN LIST DATA (Pending List untuk Verifikasi oleh Audit/Admin)
     */
    public function index()
    {
        $user = Auth::user();

        Log::info('LeaveRequestController@index dipanggil', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);

        // Hanya ambil yang status = 'pending' untuk verifikasi
        $query = LeaveRequest::with(['user.division', 'user.branch', 'approver'])
            ->where('status', 'pending') 
            ->latest();

        if ($user->role == 'admin') {
            // ADMIN: Melihat Semua Data PENDING
        } elseif ($user->role == 'audit') {
            // AUDIT: Melihat data cabang yang dipegang + Punya sendiri
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            $query->where(function ($mainQ) use ($user, $myBranchIds) {
                if (!empty($myBranchIds)) {
                    $mainQ->whereHas('user', function ($q) use ($myBranchIds) {
                        $q->whereIn('users.branch_id', $myBranchIds);
                    });
                } else {
                    $mainQ->where('id', 0);
                }
                $mainQ->orWhere('user_id', $user->id);
            });
        } else {
            // USER BIASA: Hanya lihat punya sendiri
            $query->where('user_id', $user->id);
        }

        $requests = $query->paginate(10);

        return view('leave_requests.index', compact('requests'));
    }

    /**
     * LIST PENGAJUAN SAYA (History User)
     */
    public function myRequests()
    {
        $user = Auth::user();

        $requests = LeaveRequest::with(['user.division', 'user.branch', 'approver'])
            ->where('user_id', $user->id)
            ->oldest()
            ->paginate(10);

        return view('leave_requests.index', compact('requests'));
    }

    /**
     * MENAMPILKAN FORM
     */
    public function create()
    {
        return view('leave_requests.create');
    }

    /**
     * MENYIMPAN DATA PENGAJUAN
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:telat,wfh,izin,sakit,cuti',
            'reason' => 'required|string|max:255',
            'file_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required_unless:type,telat|nullable|date|after_or_equal:start_date',
            'start_time' => 'required_if:type,telat|nullable|date_format:H:i',
        ], [
            'file_proof.required' => 'Bukti foto/dokumen wajib diupload.',
            'start_time.required_if' => 'Jam kedatangan wajib diisi jika izin terlambat.',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'status' => 'pending',
            'is_active' => true,
        ];

        // Logika khusus tipe Telat vs Lainnya
        if ($request->type === 'telat') {
            $data['start_time'] = $request->start_time;
            $data['end_date'] = null;
        } else {
            $data['end_date'] = $request->end_date;
            $data['start_time'] = null;
        }

        // Upload File
        if ($request->hasFile('file_proof')) {
            $path = $request->file('file_proof')->store('proofs', 'public');
            $data['file_proof'] = $path;
        }

        LeaveRequest::create($data);

        // === REDIRECT KE DASHBOARD (Agar status pending terlihat) ===
        return redirect()->route('dashboard')->with('success', 'Pengajuan berhasil dikirim. Status saat ini: Menunggu Verifikasi Audit.');
    }

    /**
     * ACTION: APPROVE (Biasanya dipanggil via AuditController, tapi disimpan untuk backup)
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('success', 'Pengajuan disetujui.');
    }

    /**
     * ACTION: REJECT
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'is_active' => false,
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Pengajuan ditolak.');
    }

    /**
     * ACTION: CANCEL (User membatalkan pengajuan sendiri)
     */
    public function cancel(LeaveRequest $leaveRequest)
    {
        // Pastikan yang cancel adalah pemiliknya
        if ($leaveRequest->user_id != Auth::id()) {
            abort(403);
        }

        $leaveRequest->update([
            'status' => 'cancelled',
            'is_active' => false
        ]);

        $msg = $leaveRequest->type == 'telat' ? 'Izin telat dibatalkan.' : 'Pengajuan izin dibatalkan.';
        
        // Redirect back agar fleksibel (bisa dari dashboard atau list)
        return redirect()->back()->with('success', $msg);
    }

    /**
     * ACTION: FINISH EARLY (Masuk kantor sebelum izin selesai)
     */
    public function finishEarly(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id != Auth::id()) abort(403);

        if (!in_array($leaveRequest->type, ['sakit', 'izin', 'cuti', 'wfh'])) {
            return back()->with('error', 'Tipe izin ini tidak bisa diselesaikan lebih awal.');
        }

        // Set tanggal selesai menjadi KEMARIN, otomatis hari ini dianggap masuk
        $leaveRequest->update(['end_date' => Carbon::yesterday()]);
        
        return redirect()->route('dashboard')->with('success', 'Status izin diperbarui. Selamat bekerja kembali.');
    }

    /**
     * HISTORY PRIBADI (Halaman khusus riwayat user)
     */
    public function personalHistory()
    {
        $requests = LeaveRequest::with(['approver'])
            ->where('user_id', Auth::id())
            // Ambil yang statusnya sudah final (Approved/Rejected/Cancelled)
            ->whereIn('status', ['approved', 'rejected', 'cancelled']) 
            ->latest()
            ->paginate(10);

        return view('leave_requests.personal_history', compact('requests'));
    }
}