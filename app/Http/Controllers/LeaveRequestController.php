<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveRequestController extends Controller
{
    // MENAMPILKAN LIST DATA (Pending List untuk Verifikasi)
    public function index()
    {
        $user = Auth::user();

        Log::info('LeaveRequestController@index dipanggil', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role
        ]);

        // Eager load 'approver' agar nama penyetuju bisa diambil
        $query = LeaveRequest::with(['user.division', 'user.branch', 'approver'])->latest();

        if ($user->role == 'admin') {
            // ADMIN: Melihat Semua Data
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

    // LIST PENGAJUAN SAYA (History User)
    public function myRequests()
    {
        $user = Auth::user();

        $requests = LeaveRequest::with(['user.division', 'user.branch', 'approver'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('leave_requests.my_requests', compact('requests'));
    }

    // MENAMPILKAN FORM
    public function create()
    {
        return view('leave_requests.create');
    }

    // MENYIMPAN DATA
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

        if ($request->type === 'telat') {
            $data['start_time'] = $request->start_time;
            $data['end_date'] = null;
        } else {
            $data['end_date'] = $request->end_date;
            $data['start_time'] = null;
        }

        if ($request->hasFile('file_proof')) {
            $path = $request->file('file_proof')->store('proofs', 'public');
            $data['file_proof'] = $path;
        }

        LeaveRequest::create($data);

        // LOGIKA REDIRECT BERDASARKAN ROLE
        $role = Auth::user()->role;
        if (in_array($role, ['admin', 'audit'])) {
            return redirect()->route('leave-requests.index')->with('success', 'Pengajuan berhasil dikirim.');
        }

        return redirect()->route('leave-requests.my-requests')->with('success', 'Pengajuan berhasil dikirim.');
    }

    // ACTION: APPROVE - Method ini TIDAK DIGUNAKAN karena route menggunakan AuditController
    public function approve(LeaveRequest $leaveRequest)
    {
        Log::info('LeaveRequestController@approve dipanggil', [
            'leave_request_id' => $leaveRequest->id,
            'current_status' => $leaveRequest->status,
            'approver_id' => Auth::id(),
            'approver_name' => Auth::user()->name
        ]);

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
        ]);

        Log::info('LeaveRequestController@approve selesai', [
            'leave_request_id' => $leaveRequest->id,
            'new_status' => $leaveRequest->status,
            'approved_by' => $leaveRequest->approved_by
        ]);

        return redirect()->back()->with('success', 'Pengajuan disetujui.');
    }

    // ACTION: REJECT - Method ini TIDAK DIGUNAKAN karena route menggunakan AuditController
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        Log::info('LeaveRequestController@reject dipanggil', [
            'leave_request_id' => $leaveRequest->id,
            'current_status' => $leaveRequest->status,
            'approver_id' => Auth::id()
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'is_active' => false,
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Pengajuan ditolak.');
    }

    // ACTION: CANCEL (User)
    public function cancel(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id != Auth::id()) {
            abort(403);
        }

        $leaveRequest->update([
            'status' => 'cancelled',
            'is_active' => false
        ]);

        $msg = $leaveRequest->type == 'telat' ? 'Izin telat dibatalkan.' : 'Pengajuan izin dibatalkan.';
        return redirect()->route('leave-requests.my-requests')->with('success', $msg);
    }

    // ACTION: FINISH EARLY
    public function finishEarly(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id != Auth::id()) abort(403);

        if (!in_array($leaveRequest->type, ['sakit', 'izin', 'cuti', 'wfh'])) {
            return back()->with('error', 'Tipe izin ini tidak bisa diselesaikan lebih awal.');
        }

        $leaveRequest->update(['end_date' => Carbon::yesterday()]);
        return redirect()->route('dashboard')->with('success', 'Status izin diperbarui.');
    }
}