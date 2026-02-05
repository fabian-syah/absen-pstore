<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($user->role == 'admin' || $user->role == 'admin_gaji') {
            // ADMIN & ADMIN GAJI: Melihat Semua Data PENDING
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

        // [FIX] Exclude 'cuti' type for Approvers (karena sudah ada menu khusus)
        // Kecuali user biasa yang melihat punya sendiri, mungkin tetap ingin lihat pending di list umum?
        // User request: "bikin menu ... jadi yang butuh acc ... tipe nya khusus izin cuti aja fixing"
        // Artinya di menu lama jangan muncul cuti lagi bagi Approver.
        if (in_array($user->role, ['admin', 'admin_gaji', 'audit', 'leader'])) {
            $query->where('type', '!=', 'cuti');
        }

        $requests = $query->paginate(10);

        // DEBUG: Cek kenapa cuti masih muncul
        // dd($requests->items());

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
     * MENAMPILKAN FORM KHUSUS CUTI
     */
    public function createCuti()
    {
        $user = Auth::user();
        return view('leave_requests.create_cuti', compact('user'));
    }

    /**
     * MENYIMPAN DATA PENGAJUAN
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:telat,wfh,izin,sakit,cuti,libur',
            'reason' => 'required|string|max:255',
            'file_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'start_date' => 'required|date',
            'end_date' => 'required_unless:type,telat|nullable|date|after_or_equal:start_date',
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

        // === LOGIC CUTI: Tidak ada validasi saldo ===
        // User boleh ambil cuti melebihi jatah.
        // Kelebihan akan dipotong dari gaji di payroll (fitur "Cuti Lebih")
        // NOTE: Saldo TIDAK dipotong disini, akan dipotong saat APPROVE
        // ==========================================

        // Upload File
        if ($request->hasFile('file_proof')) {
            $path = $request->file('file_proof')->store('proofs', 'public');
            $data['file_proof'] = $path;
        }

        LeaveRequest::create($data);

        // === REDIRECT KE DASHBOARD (Agar status pending terlihat) ===
        return redirect()->route('dashboard')->with('success', 'Pengajuan berhasil dikirim.');
    }

    /**
     * ACTION: APPROVE
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        // === VALIDASI KHUSUS CABANG AUDIT (ID 64) ===
        // Hanya boleh di-ACC oleh: Admin, Herlina, Eva, Agung
        // User request: "dia gabisa acc orang lain maupun diri sendiri untuk id 64... kecuali idlogin yang aku sebutin"
        if ($leaveRequest->user && $leaveRequest->user->branch_id == 64) {
            $actor = Auth::user();
            $allowedLogins = ['herlina', 'eva', 'agung', 'adminherlina'];

            $isSuperUser = in_array($actor->role, ['admin', 'super_admin']);
            $isWhitelisted = in_array(strtolower($actor->login_id), $allowedLogins);

            Log::info("DEBUG AUDIT APPROVAL:", [
                'actor' => $actor->name,
                'login_id' => $actor->login_id,
                'role' => $actor->role,
                'is_super' => $isSuperUser,
                'is_white' => $isWhitelisted
            ]);

            if (!$isSuperUser && !$isWhitelisted) {
                return redirect()->back()->with('error', 'AKSES DITOLAK: Khusus Team Audit (ID 64), approval hanya bisa dilakukan oleh Admin, Herlina, Eva, atau Agung.');
            }
        }

        DB::beginTransaction();
        try {
            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'rejection_reason' => null,
            ]);

            // === POTONG SALDO CUTI SAAT APPROVE ===
            if ($leaveRequest->type === 'cuti') {
                $startDate = Carbon::parse($leaveRequest->start_date);
                $endDate = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $startDate;
                $daysCount = $startDate->diffInDays($endDate) + 1;

                $leaveRequest->user->decrement('leave_balance', $daysCount);
                $leaveRequest->user->increment('leave_taken', $daysCount);

                Log::info("Cuti APPROVED: User {$leaveRequest->user_id} dipotong {$daysCount} hari");
            }
            // ======================================

            // AUTO-CREATE/UPDATE ATTENDANCE FOR ALL LEAVE TYPES
            // Untuk setiap tanggal dalam range izin
            $startDate = Carbon::parse($leaveRequest->start_date);
            $endDate = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $startDate;

            // Map leave type to presence status
            $presenceStatusMap = [
                'telat' => 'Masuk',  // Telat tetap dianggap masuk
                'wfh' => 'WFH',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'cuti' => 'Cuti',
                'libur' => 'Libur',
                'dinas' => 'Dinas Luar',
            ];

            $presenceStatus = $presenceStatusMap[$leaveRequest->type] ?? ucfirst($leaveRequest->type);

            // Loop through each date in the range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $currentDate = $date->format('Y-m-d');

                // Cek apakah sudah ada attendance di tanggal tersebut
                $existingAttendance = Attendance::where('user_id', $leaveRequest->user_id)
                    ->whereDate('check_in_time', $currentDate)
                    ->first();

                if ($existingAttendance) {
                    // SUDAH ADA ATTENDANCE: Update presence_status jika masih Alpha atau null
                    if (
                        !$existingAttendance->presence_status ||
                        strtolower($existingAttendance->presence_status) === 'alpha'
                    ) {

                        $updateData = [
                            'presence_status' => $presenceStatus,
                            'status' => 'verified',
                            'attendance_type' => 'leave',
                            'verified_by_user_id' => Auth::id(),
                        ];

                        // Khusus untuk telat: update jam masuk dan flag is_late_checkin
                        if ($leaveRequest->type === 'telat' && $leaveRequest->start_time) {
                            $updateData['check_in_time'] = Carbon::parse($currentDate . ' ' . $leaveRequest->start_time);
                            $updateData['is_late_checkin'] = true;
                            $updateData['notes'] = 'Izin Telat: ' . $leaveRequest->reason;
                        } else {
                            $updateData['notes'] = ucfirst($leaveRequest->type) . ': ' . $leaveRequest->reason;
                        }

                        $existingAttendance->update($updateData);
                    }
                } else {
                    // BELUM ADA ATTENDANCE: Create baru
                    $attendanceData = [
                        'user_id' => $leaveRequest->user_id,
                        'branch_id' => $leaveRequest->user->branch_id,
                        'presence_status' => $presenceStatus,
                        'status' => 'verified',
                        'attendance_type' => 'leave',
                        'verified_by_user_id' => Auth::id(),
                    ];

                    // Khusus untuk telat: set jam masuk sesuai izin
                    if ($leaveRequest->type === 'telat' && $leaveRequest->start_time) {
                        $attendanceData['check_in_time'] = Carbon::parse($currentDate . ' ' . $leaveRequest->start_time);
                        $attendanceData['is_late_checkin'] = true;
                        $attendanceData['notes'] = 'Izin Telat: ' . $leaveRequest->reason;
                    } else {
                        // Untuk tipe lain: set jam masuk 00:00 (karena tidak ada jam spesifik)
                        $attendanceData['check_in_time'] = Carbon::parse($currentDate)->startOfDay();
                        $attendanceData['notes'] = ucfirst($leaveRequest->type) . ': ' . $leaveRequest->reason;
                    }

                    Attendance::create($attendanceData);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan disetujui dan data absensi diperbarui otomatis.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    /**
     * ACTION: REJECT
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        // === VALIDASI KHUSUS CABANG AUDIT (ID 64) ===
        if ($leaveRequest->user && $leaveRequest->user->branch_id == 64) {
            $actor = Auth::user();
            $allowedLogins = ['herlina', 'eva', 'agung', 'adminherlina'];

            $isSuperUser = in_array($actor->role, ['admin', 'super_admin']);
            $isWhitelisted = in_array(strtolower($actor->login_id), $allowedLogins);

            if (!$isSuperUser && !$isWhitelisted) {
                return redirect()->back()->with('error', 'AKSES DITOLAK: Khusus Team Audit (ID 64), reject hanya bisa dilakukan oleh Admin, Herlina, Eva, atau Agung.');
            }
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'is_active' => false,
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // NOTE: Tidak perlu refund karena saldo hanya dipotong saat APPROVE
        Log::info('Leave Request REJECTED', [
            'id' => $leaveRequest->id,
            'type' => $leaveRequest->type,
            'user_id' => $leaveRequest->user_id
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

        // NOTE: Tidak perlu refund karena saldo hanya dipotong saat APPROVE
        $msg = $leaveRequest->type == 'telat' ? 'Izin telat dibatalkan.' : 'Pengajuan izin dibatalkan.';

        // Redirect back agar fleksibel (bisa dari dashboard atau list)
        return redirect()->back()->with('success', $msg);
    }

    /**
     * ACTION: FINISH EARLY (Masuk kantor sebelum izin selesai)
     */
    // app/Http/Controllers/LeaveRequestController.php

    public function finishEarly(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id != Auth::id())
            abort(403);

        // FIX: Khusus Izin Telat, jangan "batalkan" tanggalnya.
        // Biarkan request tetap aktif sebagai catatan history,
        // tapi arahkan user untuk melakukan Absen Masuk (Attendance).
        if ($leaveRequest->type === 'telat') {
            return redirect()->route('self.attend.create')
                ->with('info', 'Izin telat tercatat. Silahkan lakukan foto selfie untuk jam masuk kehadiran.');
        }

        if (!in_array($leaveRequest->type, ['sakit', 'izin', 'cuti', 'wfh', 'libur'])) {
            return back()->with('error', 'Tipe izin ini tidak bisa diselesaikan lebih awal.');
        }

        // Logic lama untuk Sakit/Cuti (dimana user sembuh/masuk lebih cepat)
        $leaveRequest->update(['end_date' => Carbon::yesterday()]);

        return redirect()->route('dashboard')->with('success', 'Status izin dibatalkan. Silahkan absen mandiri.');
    }

    /**
     * HISTORY PRIBADI (Halaman khusus riwayat user)
     */
    /**
     * HISTORY PRIBADI (Halaman khusus riwayat user)
     */
    public function personalHistory()
    {
        // Tampilkan semua KECUALI cuti (karena ada menu sendiri)
        $requests = LeaveRequest::with(['approver'])
            ->where('user_id', Auth::id())
            ->where('type', '!=', 'cuti') // <--- EXCLUDE CUTI
            ->whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->latest()
            ->paginate(10);

        return view('leave_requests.personal_history', compact('requests'));
    }

    /**
     * RIWAYAT CUTI (Menu Terpisah)
     */
    public function cutiHistory()
    {
        $user = Auth::user();
        $currentYear = now()->year;

        // Hitung leave_taken DINAMIS (hanya tahun ini)
        $approvedCutiDays = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'cuti')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum(function ($req) {
                $start = Carbon::parse($req->start_date);
                $end = $req->end_date ? Carbon::parse($req->end_date) : $start;
                return $start->diffInDays($end) + 1;
            });

        // Override nilai untuk tampilan (agar selalu akurat per tahun)
        $user->leave_taken = $approvedCutiDays;
        $user->leave_balance = ($user->yearly_leave_limit ?? 12) - $approvedCutiDays;

        // Ambil data cuti tahun ini saja
        $requests = LeaveRequest::with(['approver'])
            ->where('user_id', $user->id)
            ->where('type', 'cuti')
            ->whereYear('start_date', $currentYear)
            ->latest()
            ->paginate(10);

        return view('leave_requests.cuti_history', compact('requests', 'user', 'currentYear'));
    }
    /**
     * MONITORING CUTI (ADMIN & ADMIN GAJI)
     * Menampilkan daftar user beserta sisa cuti mereka.
     */
    public function adminSummary(Request $request)
    {
        $user = Auth::user();
        $currentYear = now()->year;

        // Security Check (Middleware should handle this, but extra safety)
        if (!in_array($user->role, ['admin', 'admin_gaji', 'audit'])) {
            abort(403, 'Unauthorized');
        }

        $query = \App\Models\User::query()
            ->with(['branch', 'division'])
            ->where('is_active', 1) // Hanya user aktif
            ->where('role', '!=', 'super_admin'); // Exclude super admin if needed

        // Filter Pencarian Nama
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter Cabang (Jika Audit)
        $myBranchIds = [];
        if ($user->role == 'audit') {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereIn('branch_id', $myBranchIds);
            } else {
                // Audit tanpa cabang msg-msg tidak lihat apa2
                $query->where('id', 0);
            }
        }

        // Get users first, then calculate stats dynamically
        $allUsers = $query->orderBy('name')->get();

        // Calculate leave stats DYNAMICALLY per user (current year only)
        $totalTaken = 0;
        $totalBalance = 0;
        foreach ($allUsers as $usr) {
            // Hitung approved cuti untuk user ini di tahun ini
            $approvedDays = LeaveRequest::where('user_id', $usr->id)
                ->where('type', 'cuti')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum(function ($req) {
                    $start = Carbon::parse($req->start_date);
                    $end = $req->end_date ? Carbon::parse($req->end_date) : $start;
                    return $start->diffInDays($end) + 1;
                });

            // Override nilai untuk tampilan
            $usr->leave_taken = $approvedDays;
            $usr->leave_balance = ($usr->yearly_leave_limit ?? 12) - $approvedDays;

            $totalTaken += $approvedDays;
            $totalBalance += $usr->leave_balance;
        }

        // Sort by leave_taken descending (yang sudah ambil cuti di atas)
        $sortedUsers = $allUsers->sortByDesc('leave_taken')->values();

        // Manual pagination
        $perPage = 15;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedUsers = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedUsers->slice($offset, $perPage)->values(),
            $sortedUsers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Pending Requests Count (All or Scoped)
        $pendingQuery = \App\Models\LeaveRequest::where('status', 'pending')
            ->where('type', 'cuti')
            ->whereYear('start_date', $currentYear);
        if ($user->role == 'audit' && !empty($myBranchIds)) {
            $pendingQuery->whereHas('user', function ($q) use ($myBranchIds) {
                $q->whereIn('branch_id', $myBranchIds);
            });
        }
        $pendingCount = $pendingQuery->count();

        $stats = [
            'total_users' => $allUsers->count(),
            'total_taken' => $totalTaken,
            'total_balance' => $totalBalance,
            'pending_requests' => $pendingCount,
            'current_year' => $currentYear
        ];

        return view('leave_requests.admin_summary', [
            'users' => $paginatedUsers,
            'stats' => $stats,
            'currentYear' => $currentYear
        ]);
    }
    /**
     * HALAMAN APPROVAL CUTI (KHUSUS FORMAT BARU)
     */
    public function approvalCuti(Request $request)
    {
        $user = Auth::user();

        // 1. Cek Role (Hanya Admin / Audit / Leader / HR)
        if (!in_array($user->role, ['admin', 'admin_gaji', 'audit', 'leader'])) {
            abort(403, 'Unauthorized');
        }

        // 2. Query Data Pending & Type = Cuti
        $query = LeaveRequest::with(['user.branch', 'user.division'])
            ->where('status', 'pending')
            ->where('type', 'cuti');

        // 3. Filter Audit (Branch Scope)
        if ($user->role == 'audit') {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0); // No access
            }
        }

        // 4. Default Sort: Oldest First (Prioritas Lama)
        $requests = $query->orderBy('start_date', 'asc')->paginate(10);

        return view('leave_requests.approval_cuti', compact('requests'));
    }
}
