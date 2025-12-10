<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        // Middleware: Hanya Admin, Admin Gaji, dan Audit yang boleh akses manajemen user
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'audit', 'admin_gaji'])) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()
            ->paginate(10)
            ->appends(['search' => $request->search]);

        return view('users.user_index', compact('users'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif ($user->role == 'audit') {
            $branches = $user->branches; 
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

        // --- PEMBERSIHAN FORMAT RUPIAH (DICOMMENT SEMENTARA) ---
        // if ($request->has('gaji')) {
        //     $request->merge([
        //         'gaji' => str_replace('.', '', $request->gaji)
        //     ]);
        // }
        // ---------------------------------

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,admin_gaji,audit,leader,security,user_biasa',
            // UPDATE: Validasi branch tidak required jika role admin ATAU admin_gaji
            'branch_id' => 'required_unless:role,admin,admin_gaji|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
            'check_in_start' => 'nullable',
            'check_out_start' => 'nullable',
            // 'gaji' => 'nullable|numeric', // DICOMMENT SEMENTARA
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        $data['check_in_start']  = $request->check_in_start ?: null;
        $data['check_out_start'] = $request->check_out_start ?: null;
        $data['check_in_end']    = null;
        $data['check_out_end']   = null;

        // Pastikan hanya admin/admin_gaji yang bisa input gaji (DICOMMENT SEMENTARA)
        // if (!in_array($user->role, ['admin', 'admin_gaji'])) {
        //     unset($data['gaji']);
        // }

        $data['division_id'] = ($request->has('multi_divisions') && count($request->multi_divisions) > 0) ? $request->multi_divisions[0] : null;

        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        
        // UPDATE: Jika role ADMIN atau ADMIN_GAJI, set branch & division jadi NULL (Global Access)
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

        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }
        if ($request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load(['branches', 'divisions']);
        $auth_user = Auth::user();

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches()->pluck('branches.id')->toArray();
            if ($user->branch_id && !in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'Akses Ditolak: User ini berada di cabang yang tidak Anda pegang.');
            }
        }
        
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }

        $branches = Branch::all();
        $divisions = Division::all();
        $allowedRoles = ['admin', 'admin_gaji', 'audit', 'leader', 'security', 'user_biasa'];

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles'));
    }

    public function update(Request $request, User $user)
    {
        // --- PEMBERSIHAN FORMAT RUPIAH (DICOMMENT SEMENTARA) ---
        // if ($request->has('gaji')) {
        //     $request->merge([
        //         'gaji' => str_replace('.', '', $request->gaji)
        //     ]);
        // }
        // ---------------------------------

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,admin_gaji,audit,leader,security,user_biasa',
            // UPDATE: Validasi branch tidak required jika role admin ATAU admin_gaji
            'branch_id' => 'nullable|exists:branches,id',
            'whatsapp' => 'nullable|string|max:20',
            'check_in_start' => 'nullable',
            'check_out_start' => 'nullable',
            // 'gaji' => 'nullable|numeric', // DICOMMENT SEMENTARA
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Pastikan hanya admin/admin_gaji yang bisa update gaji (DICOMMENT SEMENTARA)
        // if (!in_array(Auth::user()->role, ['admin', 'admin_gaji'])) {
        //     unset($data['gaji']);
        // }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $data['check_in_start']  = $request->check_in_start ?: null;
        $data['check_out_start'] = $request->check_out_start ?: null;
        $data['check_in_end']    = null;
        $data['check_out_end']   = null;
        $data['hire_date'] = $request->hire_date ?? null;

        if ($request->hasFile('profile_photo_path')) {
            if ($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = $request->file('profile_photo_path')->store('profile-photos', 'public');
        }

        // UPDATE: Jika role diubah jadi ADMIN atau ADMIN_GAJI, set branch & division NULL
        if (in_array($request->role, ['admin', 'admin_gaji'])) {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        $user->update($data);

        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
        } else {
            $user->divisions()->sync($request->multi_divisions ?? []);
            // Logika tambahan jika bukan admin/admin_gaji
            if (!in_array($request->role, ['admin', 'admin_gaji'])) {
                if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
                    $user->division_id = $request->multi_divisions[0];
                    $user->save();
                }
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
            if ($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
            if ($user->ktp_photo_path) Storage::disk('public')->delete($user->ktp_photo_path);

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
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches()->pluck('branches.id')->toArray();
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

        return view('users.user_show', compact('user', 'stats', 'recentAttendance'));
    }

    // =========================================================================
    // UPDATE: VERIFIKASI USER WAJIB ADA FOTO PROFIL & KTP
    // =========================================================================
    public function verifyUser(User $user)
    {
        if (!$user->is_verified) {
            // SYARAT: Foto Profil DAN Foto KTP Wajib Ada
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
            
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
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
            
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        $users = $query->get();
        return view('users.ktp-requests', compact('users'));
    }

    // ==========================================================
    // LOGIKA APPROVE & REJECT FOTO PROFIL (Dengan Hapus File)
    // ==========================================================

    public function approvePhotoRequest(User $user)
    {
        if (!$user->profile_photo_temp_path) {
            return back()->with('error', 'Tidak ada file pengajuan foto baru.');
        }

        if ($user->profile_photo_path) {
            if (Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
        }

        $user->update([
            'profile_photo_path' => $user->profile_photo_temp_path,
            'profile_photo_temp_path' => null,
            'photo_request_status' => 'approved'
        ]);

        return back()->with('success', 'Izin ganti foto diberikan & foto lama dihapus.');
    }

    public function rejectPhotoRequest(User $user)
    {
        if ($user->profile_photo_temp_path) {
            if (Storage::disk('public')->exists($user->profile_photo_temp_path)) {
                Storage::disk('public')->delete($user->profile_photo_temp_path);
            }
        }

        $user->update([
            'profile_photo_temp_path' => null,
            'photo_request_status' => 'rejected'
        ]);
        
        return back()->with('success', 'Pengajuan foto ditolak. File pengajuan dihapus.');
    }

    // ==========================================================
    // LOGIKA APPROVE & REJECT KTP (Dengan Hapus File)
    // ==========================================================

    public function approveKtpRequest(User $user)
    {
        if (!$user->ktp_photo_temp_path) {
            return back()->with('error', 'Tidak ada file pengajuan KTP baru.');
        }

        if ($user->ktp_photo_path) {
            if (Storage::disk('public')->exists($user->ktp_photo_path)) {
                Storage::disk('public')->delete($user->ktp_photo_path);
            }
        }
        
        $user->update([
            'ktp_photo_path' => $user->ktp_photo_temp_path,
            'ktp_photo_temp_path' => null,
            'ktp_request_status' => 'none'
        ]);

        return back()->with('success', 'Permintaan ganti KTP disetujui & file lama dihapus.');
    }

    public function rejectKtpRequest(User $user)
    {
        if ($user->ktp_photo_temp_path) {
            if (Storage::disk('public')->exists($user->ktp_photo_temp_path)) {
                Storage::disk('public')->delete($user->ktp_photo_temp_path);
            }
        }

        $user->update([
            'ktp_photo_temp_path' => null,
            'ktp_request_status' => 'rejected'
        ]);

        return back()->with('success', 'Permintaan ganti KTP ditolak.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id == auth()->id()) return back();

        $user->is_active = !$user->is_active;
        $user->save();
        return back()->with('success', 'Status user diperbarui.');
    }

    private function getSpecificUserStats($user_id)
    {
        $presentCount = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->where('status', '!=', 'alpha') 
            ->where('attendance_type', '!=', 'system') 
            ->whereIn('status', ['verified', 'present', 'late'])
            ->count();

        return [
            'total' => $presentCount,
            'present' => $presentCount,
            'alpha' => 0,
            'late' => 0,
            'early' => 0,
            'pending' => 0,
            'on_time' => 0,
            'on_time_percentage' => 0,
            'late_percentage' => 0,
            'current_month' => Carbon::now()->format('F Y')
        ];
    }

    public function updateFcmToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string'
            ]);
            $user = Auth::user();
            $user->fcm_token = $request->token;
            $user->save();
            return response()->json(['success' => true, 'message' => 'Token updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}