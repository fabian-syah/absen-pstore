<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;
use App\Models\Violation;
use App\Models\JobTarget;
use App\Models\CashAdvance;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    use SendFcmNotification;
    // Set Timezone Admin (Pusat)
    protected $adminTimezone = 'Asia/Jakarta';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $routeAction = $request->route()->getActionMethod();

            if ($routeAction === 'show') {
                if (!in_array($user->role, ['admin', 'audit', 'admin_gaji', 'leader'])) {
                    abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
                }
            } else {
                if (!in_array($user->role, ['admin', 'audit', 'admin_gaji', 'leader'])) {
                    abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->search;

        // 1. Base query untuk user Aktif (Tetap menggunakan filter keamanan)
        $activeQuery = User::with(['division', 'branch', 'branches', 'divisions'])->where('is_active', true);

        // 2. Base query untuk user Non-Aktif (Hapus filter branch agar semua EX muncul)
        $inactiveQuery = User::with(['division', 'branch', 'branches', 'divisions'])->where('is_active', false);

        // --- FILTER UNTUK USER AKTIF SAJA ---
        if ($user->role == 'admin' && $user->branch_id != null) {
            $activeQuery->where('branch_id', $user->branch_id);
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if ($user->branch_id)
                $allowedBranchIds[] = $user->branch_id;

            // Fix: Pastikan user selalu bisa melihat dirinya sendiri meskipun branch_id null atau tidak ada di list
            $activeQuery->where(function ($q) use ($allowedBranchIds, $user) {
                $q->whereIn('branch_id', array_unique($allowedBranchIds))
                    ->orWhere('id', $user->id);
            });
        }

        // --- PENCARIAN (Berlaku untuk keduanya) ---
        if ($search != '') {
            $activeQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%");
            });

            $inactiveQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        $users = $activeQuery->latest()->paginate(10, ['*'], 'active_page')->appends(['search' => $search, 'tab' => 'active']);
        $inactiveUsers = $inactiveQuery->latest('updated_at')->paginate(10, ['*'], 'inactive_page')->appends(['search' => $search, 'tab' => 'inactive']);

        return view('users.user_index', compact('users', 'inactiveUsers'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $branches = $user->branches;
            if ($branches->isEmpty() && $user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->get();
            }
            $allowedRoles = ['audit', 'leader', 'security', 'user_biasa'];
        } else {
            $branches = Branch::all();
            $allowedRoles = ['admin', 'admin_gaji', 'audit', 'leader', 'security', 'user_biasa'];
        }

        $divisions = Division::all();

        return view('users.user_create', compact('divisions', 'branches', 'allowedRoles'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,admin_gaji,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin,admin_gaji|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
            'check_in_start' => 'nullable',
            'check_out_start' => 'nullable',
            'only_security_scan' => 'nullable',
            'use_face_recognition' => 'nullable',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        $data['only_security_scan'] = $request->has('only_security_scan') ? 1 : 0;
        $data['use_face_recognition'] = $request->input('use_face_recognition', 1);

        if ($request->branch_id) {
            $branch = Branch::find($request->branch_id);
            $targetTz = $branch->timezone ?? $this->adminTimezone;

            if ($request->check_in_start) {
                $timeIn = Carbon::createFromFormat('H:i', $request->check_in_start, $this->adminTimezone);
                $timeIn->setTimezone($targetTz);
                $data['check_in_start'] = $timeIn->format('H:i:s');
            } else {
                $data['check_in_start'] = null;
            }

            if ($request->check_out_start) {
                $timeOut = Carbon::createFromFormat('H:i', $request->check_out_start, $this->adminTimezone);
                $timeOut->setTimezone($targetTz);
                $data['check_out_start'] = $timeOut->format('H:i:s');
            } else {
                $data['check_out_start'] = null;
            }
        } else {
            $data['check_in_start'] = $request->check_in_start ?: null;
            $data['check_out_start'] = $request->check_out_start ?: null;
        }

        $data['check_in_end'] = null;
        $data['check_out_end'] = null;

        $data['division_id'] = ($request->has('multi_divisions') && count($request->multi_divisions) > 0) ? $request->multi_divisions[0] : null;

        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }

        if (in_array($request->role, ['admin', 'admin_gaji'])) {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();
        $data['hire_date'] = $request->hire_date ?? null;

        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        $newUser = User::create($data);

        if (in_array($request->role, ['audit', 'leader']) && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }

        if ($request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load(['branches', 'divisions', 'branch']);
        $auth_user = Auth::user();

        if (in_array($auth_user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $auth_user->branches()->pluck('branches.id')->toArray();
            if ($auth_user->branch_id)
                $allowedBranchIds[] = $auth_user->branch_id;
            if ($user->branch_id && !in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'Akses Ditolak: User ini berada di cabang yang tidak Anda pegang.');
            }
        }
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id)
                abort(403);
        }

        if (in_array($auth_user->role, ['audit', 'leader'])) {
            $branches = $auth_user->branches;
            if ($branches->isEmpty() && $auth_user->branch_id) {
                $branches = Branch::where('id', $auth_user->branch_id)->get();
            }
        } elseif ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $branches = Branch::where('id', $auth_user->branch_id)->get();
        } else {
            $branches = Branch::all();
        }
        $divisions = Division::all();
        $allowedRoles = ['admin', 'admin_gaji', 'audit', 'leader', 'security', 'user_biasa'];

        $displayCheckIn = '';
        $displayCheckOut = '';

        if ($user->branch) {
            $branchTz = $user->branch->timezone ?? $this->adminTimezone;

            if ($user->check_in_start) {
                $timeIn = Carbon::createFromFormat('H:i:s', $user->check_in_start, $branchTz);
                $timeIn->setTimezone($this->adminTimezone);
                $displayCheckIn = $timeIn->format('H:i');
            }

            if ($user->check_out_start) {
                $timeOut = Carbon::createFromFormat('H:i:s', $user->check_out_start, $branchTz);
                $timeOut->setTimezone($this->adminTimezone);
                $displayCheckOut = $timeOut->format('H:i');
            }
        } else {
            $displayCheckIn = $user->check_in_start ? date('H:i', strtotime($user->check_in_start)) : '';
            $displayCheckOut = $user->check_out_start ? date('H:i', strtotime($user->check_out_start)) : '';
        }

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles', 'displayCheckIn', 'displayCheckOut'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,admin_gaji,audit,leader,security,user_biasa',
            'branch_id' => 'nullable|exists:branches,id',
            'whatsapp' => 'nullable|string|max:20',
            'check_in_start' => 'nullable',
            'check_out_start' => 'nullable',
            'only_security_scan' => 'nullable',
            'use_face_recognition' => 'nullable',
        ]);

        if (in_array(Auth::user()->role, ['audit', 'leader']) && $request->filled('branch_id')) {
            $allowedBranchIds = Auth::user()->branches()->pluck('branches.id')->toArray();
            if (Auth::user()->branch_id)
                $allowedBranchIds[] = Auth::user()->branch_id;
            if (!in_array($request->branch_id, $allowedBranchIds)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk memindahkan user ke cabang tersebut.');
            }
        }

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        $data['only_security_scan'] = $request->has('only_security_scan') ? 1 : 0;
        $data['use_face_recognition'] = $request->input('use_face_recognition');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $data['hire_date'] = $request->hire_date ?? null;

        $targetBranchId = $request->branch_id ?? $user->branch_id;

        if ($targetBranchId) {
            $branch = Branch::find($targetBranchId);
            $targetTz = $branch->timezone ?? $this->adminTimezone;

            if ($request->check_in_start) {
                $timeIn = Carbon::createFromFormat('H:i', $request->check_in_start, $this->adminTimezone);
                $timeIn->setTimezone($targetTz);
                $data['check_in_start'] = $timeIn->format('H:i:s');
            } else {
                $data['check_in_start'] = null;
            }

            if ($request->check_out_start) {
                $timeOut = Carbon::createFromFormat('H:i', $request->check_out_start, $this->adminTimezone);
                $timeOut->setTimezone($targetTz);
                $data['check_out_start'] = $timeOut->format('H:i:s');
            } else {
                $data['check_out_start'] = null;
            }
        } else {
            $data['check_in_start'] = $request->check_in_start ?: null;
            $data['check_out_start'] = $request->check_out_start ?: null;
        }

        $data['check_in_end'] = null;
        $data['check_out_end'] = null;

        if ($request->hasFile('profile_photo_path')) {
            if ($user->profile_photo_path)
                Storage::disk('public')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = $request->file('profile_photo_path')->store('profile-photos', 'public');
        }

        if (in_array($request->role, ['admin', 'admin_gaji'])) {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        $user->update($data);

        $currentUserRole = Auth::user()->role;
        if (in_array($currentUserRole, ['admin', 'admin_gaji'])) {
            if (in_array($request->role, ['audit', 'leader'])) {
                $user->branches()->sync($request->multi_branches ?? []);
            }
        }

        $user->divisions()->sync($request->multi_divisions ?? []);

        if (!in_array($request->role, ['admin', 'admin_gaji'])) {
            if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
                $user->division_id = $request->multi_divisions[0];
                $user->save();
            }
        }

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->role == 'audit') {
            return back()->with('error', 'Akses Ditolak: Role Audit tidak diizinkan menghapus data user.');
        }
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Tidak bisa hapus akun sendiri.');
        }

        try {
            if ($user->profile_photo_path)
                Storage::disk('public')->delete($user->profile_photo_path);
            if ($user->ktp_photo_path)
                Storage::disk('public')->delete($user->ktp_photo_path);
            $user->branches()->detach();
            $user->divisions()->detach();
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus user. Pastikan tidak ada data absensi terkait.');
        }
    }

    public function show(User $user)
    {
        $auth_user = Auth::user();

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id)
                abort(403);
        }

        if (in_array($auth_user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $auth_user->branches()->pluck('branches.id')->toArray();
            if ($auth_user->branch_id)
                $allowedBranchIds[] = $auth_user->branch_id;

            if ($user->branch_id && !in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'Akses Ditolak: User ini berada di luar wilayah cabang Anda.');
            }
        }

        $user->load(['branch', 'division', 'branches', 'divisions']);
        $stats = $this->getSpecificUserStats($user->id);

        $recentAttendance = Attendance::where('user_id', $user->id)
            ->whereNotNull('check_in_time')
            ->whereTime('check_in_time', '!=', '00:00:00')
            ->where('status', '!=', 'alpha')
            ->where('status', '!=', 'absent')
            ->where('presence_status', '!=', 'Alpha')
            ->where('attendance_type', '!=', 'system')
            ->whereIn('status', ['present', 'verified', 'late', 'pending_verification'])
            ->latest('check_in_time')
            ->take(5)
            ->get();

        foreach ($recentAttendance as $att) {
            if ($user->branch && $user->branch->timezone) {
                $att->check_in_local = Carbon::parse($att->check_in_time)->timezone($user->branch->timezone);
                if ($att->check_out_time) {
                    $att->check_out_local = Carbon::parse($att->check_out_time)->timezone($user->branch->timezone);
                }
            } else {
                $att->check_in_local = $att->check_in_time;
                $att->check_out_local = $att->check_out_time;
            }
        }

        $activeViolations = Violation::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        $historyViolations = Violation::where('user_id', $user->id)
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at', 'desc')
            ->get();

        $activeTargets = JobTarget::where('user_id', $user->id)
            ->where('type', 'personal_target')
            ->where('status', '!=', 'completed')
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        $achievements = JobTarget::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('type', 'personal_achievement')
                    ->orWhere(function ($subQ) {
                        $subQ->where('type', 'personal_target')
                            ->where('status', 'completed');
                    });
            })
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // --- HITUNG TOTAL SISA UTANG (UNTUK SEMUA ROLE YANG BISA AKSES SHOW) ---
        // Kita hitung untuk semua, nanti di View baru dibatasi link-nya.
        $totalKasbon = CashAdvance::where('user_id', $user->id)
            ->where('status', 'approved')
            ->get()
            ->sum('remaining_amount');

        return view('users.user_show', compact('user', 'stats', 'recentAttendance', 'activeViolations', 'historyViolations', 'activeTargets', 'achievements', 'totalKasbon'));
    }

    public function verifyUser(User $user)
    {
        if (!$user->is_verified) {
            if (empty($user->profile_photo_path) || empty($user->ktp_photo_path)) {
                return back()->with('error', 'Gagal Verifikasi: User harus mengunggah Foto Profil DAN Foto KTP terlebih dahulu!');
            }
            $user->is_verified = true;
            $msg = 'User berhasil diverifikasi (Centang Biru Aktif). Foto profil user sekarang terkunci.';
        } else {
            $user->is_verified = false;
            $msg = 'Verifikasi dicabut (Centang Biru Non-Aktif).';
        }
        $user->save();
        return back()->with('success', $msg);
    }

    public function photoRequests()
    {
        $user = Auth::user();
        $query = User::where('photo_request_status', 'pending')->with(['branch', 'division']);

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if ($user->branch_id)
                $allowedBranchIds[] = $user->branch_id;
            $query->whereIn('branch_id', $allowedBranchIds);
        }

        $requests = $query->latest('updated_at')->paginate(10);
        return view('users.photo_requests', compact('requests'));
    }

    public function ktpRequests()
    {
        $user = Auth::user();
        $query = User::where('ktp_request_status', 'pending')->with('division');

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if ($user->branch_id)
                $allowedBranchIds[] = $user->branch_id;
            $query->whereIn('branch_id', $allowedBranchIds);
        }

        $users = $query->get();
        return view('users.ktp-requests', compact('users'));
    }

    public function approvePhotoRequest(User $user)
    {
        if (!$user->profile_photo_temp_path)
            return back()->with('error', 'Tidak ada file pengajuan foto baru.');
        if ($user->profile_photo_path) if (Storage::disk('public')->exists($user->profile_photo_path))
            Storage::disk('public')->delete($user->profile_photo_path);
        $user->update(['profile_photo_path' => $user->profile_photo_temp_path, 'profile_photo_temp_path' => null, 'photo_request_status' => 'approved']);
        return back()->with('success', 'Izin ganti foto diberikan & foto lama dihapus.');
    }

    public function rejectPhotoRequest(User $user)
    {
        if ($user->profile_photo_temp_path) if (Storage::disk('public')->exists($user->profile_photo_temp_path))
            Storage::disk('public')->delete($user->profile_photo_temp_path);
        $user->update(['profile_photo_temp_path' => null, 'photo_request_status' => 'rejected']);
        return back()->with('success', 'Pengajuan foto ditolak. File pengajuan dihapus.');
    }

    public function approveKtpRequest(User $user)
    {
        if (!$user->ktp_photo_temp_path)
            return back()->with('error', 'Tidak ada file pengajuan KTP baru.');
        if ($user->ktp_photo_path) if (Storage::disk('public')->exists($user->ktp_photo_path))
            Storage::disk('public')->delete($user->ktp_photo_path);
        $user->update(['ktp_photo_path' => $user->ktp_photo_temp_path, 'ktp_photo_temp_path' => null, 'ktp_request_status' => 'none']);
        return back()->with('success', 'Permintaan ganti KTP disetujui & file lama dihapus.');
    }

    public function rejectKtpRequest(User $user)
    {
        if ($user->ktp_photo_temp_path) if (Storage::disk('public')->exists($user->ktp_photo_temp_path))
            Storage::disk('public')->delete($user->ktp_photo_temp_path);
        $user->update(['ktp_photo_temp_path' => null, 'ktp_request_status' => 'rejected']);
        return back()->with('success', 'Permintaan ganti KTP ditolak.');
    }

    public function toggleStatus(User $user)
    {
        // Mencegah admin menonaktifkan diri sendiri
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        // Toggle status aktif/nonaktif
        $user->is_active = !$user->is_active;

        // Logika Otomatis Pindah Cabang jika dinonaktifkan
        if ($user->is_active == false) {
            // Cek apakah cabang ID 83 (EX Karyawan) ada di database
            $exBranchExists = \App\Models\Branch::where('id', 83)->exists();

            if ($exBranchExists) {
                $user->branch_id = 83;
            }
            // Opsional: Jika Anda ingin menghapus relasi multi-cabang (untuk audit/leader) saat nonaktif
            $user->branches()->detach();
        }

        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan dan dipindahkan ke EX Karyawan';
        return back()->with('success', "Status user berhasil $statusText.");
    }

    private function getSpecificUserStats($user_id)
    {
        $presentCount = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->where('status', '!=', 'alpha')
            ->where('attendance_type', '!=', 'system')
            ->whereIn('status', ['verified', 'present', 'late'])
            ->count();
        return ['total' => $presentCount, 'present' => $presentCount, 'alpha' => 0, 'late' => 0, 'early' => 0, 'pending' => 0, 'on_time' => 0, 'on_time_percentage' => 0, 'late_percentage' => 0, 'current_month' => Carbon::now()->format('F Y')];
    }

    public function updateFcmToken(Request $request)
    {
        try {
            $request->validate(['token' => 'required|string']);
            $user = Auth::user();
            $user->fcm_token = $request->token;
            $user->save();
            return response()->json(['success' => true, 'message' => 'Token updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Monitor Upload Dokumen (KTP & Foto Profil)
     * Menampilkan daftar user yang sudah upload foto profil atau KTP
     */
    public function documentUploads(Request $request)
    {
        $user = Auth::user();
        $search = $request->search;
        $filter = $request->filter ?? 'all'; // all, complete, incomplete

        $query = User::with(['branch', 'divisions'])
            ->where('is_active', true)
            ->where('role', '!=', 'admin'); // Exclude admin dari monitoring

        // Filter berdasarkan branch jika admin cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter berdasarkan status upload
        if ($filter == 'complete') {
            $query->whereNotNull('profile_photo_path')
                ->whereNotNull('ktp_photo_path');
        } elseif ($filter == 'incomplete') {
            $query->where(function ($q) {
                $q->whereNull('profile_photo_path')
                    ->orWhereNull('ktp_photo_path');
            });
        }

        // Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Urutkan berdasarkan updated_at (yang baru upload di atas)
        $users = $query->latest('updated_at')->paginate(15)->appends([
            'search' => $search,
            'filter' => $filter
        ]);

        // Hitung statistik
        $statsQuery = User::where('is_active', true)->where('role', '!=', 'admin');
        if ($user->role == 'admin' && $user->branch_id != null) {
            $statsQuery->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'complete' => (clone $statsQuery)->whereNotNull('profile_photo_path')->whereNotNull('ktp_photo_path')->count(),
            'incomplete' => (clone $statsQuery)->where(function ($q) {
                $q->whereNull('profile_photo_path')->orWhereNull('ktp_photo_path');
            })->count(),
        ];

        return view('users.document_uploads', compact('users', 'stats', 'filter'));
    }

    /**
     * Admin download User QR Code as PDF
     */
    public function downloadQrPdf(User $user)
    {
        // Generate QR Code HD
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(500)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($user->qr_code_value);

        return view('qrcode_pdf', [
            'qrSvg' => $qrSvg,
            'userName' => $user->name,
            'branchName' => $user->branch->name ?? 'PStore',
        ]);
    }

    /**
     * Admin updates user photos directly.
     */
    public function adminUpdatePhotos(Request $request, User $user)
    {
        $request->validate([
            'profile_photo' => 'nullable|image|max:2048',
            'ktp_photo' => 'nullable|image|max:2048',
        ]);

        $updated = false;

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->photo_request_status = 'approved';
            $updated = true;
        }

        if ($request->hasFile('ktp_photo')) {
            if ($user->ktp_photo_path) {
                Storage::disk('public')->delete($user->ktp_photo_path);
            }
            $user->ktp_photo_path = $request->file('ktp_photo')->store('ktp-photos', 'public');
            $user->ktp_request_status = 'none';
            $updated = true;
        }

        if ($updated) {
            $user->save();
            return back()->with('success', 'Foto berhasil diperbarui oleh Admin.');
        }

        return back()->with('error', 'Tidak ada file yang dipilih.');
    }
}
