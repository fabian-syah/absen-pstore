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
        // Middleware: Hanya Admin dan Audit yang boleh akses manajemen user
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'audit'])) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
            }
            return $next($request);
        });
    }

    /**
     * Menampilkan daftar user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Eager load relasi yang dibutuhkan
        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        // ================================================================
        // 1. FILTER CABANG (Sesuai Role Login)
        // ================================================================
        
        if ($user->role == 'admin' && $user->branch_id != null) {
            // Admin Cabang: Hanya melihat user di cabangnya sendiri
            $query->where('branch_id', $user->branch_id);

        } elseif ($user->role == 'audit') {
            // Audit (Multi Cabang):
            // Ambil semua ID cabang yang dipegang oleh Audit
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();

            // Filter Query: User yang branch_id-nya ada di dalam list cabang Audit
            $query->whereIn('branch_id', $auditBranchIds);
        }

        // ================================================================
        // 2. LOGIKA PENCARIAN (SEARCH)
        // ================================================================
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        // Eksekusi Query
        $users = $query->latest()
            ->paginate(10)
            ->appends(['search' => $request->search]);

        return view('users.user_index', compact('users'));
    }

    /**
     * Menampilkan Form Tambah User
     */
    public function create()
    {
        $user = Auth::user();

        // Filter Dropdown Cabang saat Create User
        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif ($user->role == 'audit') {
            // Audit hanya bisa mendaftarkan user ke cabang yang dia pegang
            $branches = $user->branches; 
            $allowedRoles = ['audit', 'leader', 'security', 'user_biasa'];
        } else {
            // Admin Pusat (Super Admin)
            $branches = Branch::all();
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        $divisions = Division::all();

        return view('users.user_create', compact('divisions', 'branches', 'allowedRoles'));
    }

    /**
     * Menyimpan User Baru ke Database
     */
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
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
            'check_in_start' => 'nullable',
            'check_out_start' => 'nullable',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Logika Jam Kerja
        $data['check_in_start']  = $request->check_in_start ?: null;
        $data['check_out_start'] = $request->check_out_start ?: null;
        $data['check_in_end']    = null;
        $data['check_out_end']   = null;

        // Set Division ID Utama
        $data['division_id'] = ($request->has('multi_divisions') && count($request->multi_divisions) > 0) ? $request->multi_divisions[0] : null;

        // Force Branch ID jika Admin Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        // Admin Pusat tidak wajib punya branch
        if ($request->role == 'admin') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        // Enkripsi Password & Generate QR
        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();
        $data['hire_date'] = $request->hire_date ?? null;

        // Upload Foto
        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // Simpan User
        $newUser = User::create($data);

        // Sync Relasi Many-to-Many
        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }
        if ($request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan Form Edit User
     */
    public function edit(User $user)
    {
        $user->load(['branches', 'divisions']);
        $auth_user = Auth::user();

        // ================================================================
        // SECURITY CHECK (Mencegah Akses ID via URL)
        // ================================================================
        if ($auth_user->role == 'audit') {
            // Cek apakah user yg diedit ada di cabang yg dipegang audit
            $allowedBranchIds = $auth_user->branches()->pluck('branches.id')->toArray();
            
            // Jika user punya branch_id, cek apakah ada di list audit
            if ($user->branch_id && !in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'Akses Ditolak: User ini berada di cabang yang tidak Anda pegang.');
            }
        }
        
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }

        $branches = Branch::all();
        $divisions = Division::all();
        $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles'));
    }

    /**
     * Memperbarui Data User
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'nullable|exists:branches,id',
            'whatsapp' => 'nullable|string|max:20',
            'check_in_start' => 'nullable',
            'check_out_start' => 'nullable',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

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

        $user->update($data);

        // Sync relations
        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
        } else {
            $user->divisions()->sync($request->multi_divisions ?? []);
            if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
                $user->division_id = $request->multi_divisions[0];
                $user->save();
            }
        }

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Menghapus User
     */
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

    /**
     * Detail User
     */
    public function show(User $user)
    {
        $auth_user = Auth::user();

        // Security Check untuk Detail
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

        // =========================================================================
        // FILTER KETAT: HANYA YANG HADIR (Bukan Alpha, Izin, Sakit)
        // =========================================================================
        $recentAttendance = Attendance::where('user_id', $user->id)
            ->whereNotNull('check_in_time') // Wajib ada jam masuk (Bukan NULL)
            ->where('status', '!=', 'alpha') // Wajib bukan Alpha
            ->where('status', '!=', 'absent') // Jaga-jaga jika statusnya 'absent'
            ->whereIn('status', ['present', 'verified', 'late']) // Whitelist status hadir
            ->latest('check_in_time')
            ->take(5)
            ->get();

        return view('users.user_show', compact('user', 'stats', 'recentAttendance'));
    }

    /**
     * Halaman Request Ganti Foto
     */
    public function photoRequests()
    {
        $user = Auth::user();
        $query = User::where('photo_request_status', 'pending')->with(['branch', 'division']);

        // Filter berdasarkan Role
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
            
        } elseif ($user->role == 'audit') {
            // Audit hanya melihat request dari cabang yang dia pegang
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        $requests = $query->latest('updated_at')->paginate(10);
        return view('users.photo_requests', compact('requests'));
    }

    /**
     * Halaman Request Ganti KTP
     */
    public function ktpRequests()
    {
        $user = Auth::user();
        $query = User::where('ktp_request_status', 'pending')->with('division');

        // Tambahkan filter cabang untuk KTP Request juga
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
            
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        $users = $query->get();
        return view('users.ktp-requests', compact('users'));
    }

    // =========================================================================
    // METODE LAIN (Helper & Action Buttons)
    // =========================================================================

    public function verifyUser(User $user)
    {
        if (!$user->is_verified) {
            if (!$user->profile_photo_path || !$user->ktp_photo_path || !$user->whatsapp) {
                return back()->with('error', 'Gagal Verifikasi: User belum melengkapi Foto Profil, Foto KTP, atau No WhatsApp.');
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

    public function approvePhotoRequest(User $user)
    {
        if ($user->photo_request_status !== 'pending') {
            return back()->with('error', 'Tidak ada permintaan ganti foto yang pending.');
        }
        $user->update(['photo_request_status' => 'approved']);
        return back()->with('success', 'Izin ganti foto diberikan. User dapat mengupload foto baru sekarang.');
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

    public function approveKtpRequest(User $user)
    {
        if ($user->ktp_photo_path) {
            Storage::disk('public')->delete($user->ktp_photo_path);
        }
        $newPath = $user->ktp_photo_temp_path;
        $user->update([
            'ktp_photo_path' => $newPath,
            'ktp_photo_temp_path' => null,
            'ktp_request_status' => 'none'
        ]);
        return back()->with('success', 'Permintaan ganti KTP disetujui.');
    }

    public function rejectKtpRequest(User $user)
    {
        if ($user->ktp_photo_temp_path) {
            Storage::disk('public')->delete($user->ktp_photo_temp_path);
        }
        $user->update([
            'ktp_photo_temp_path' => null,
            'ktp_request_status' => 'rejected'
        ]);
        return back()->with('success', 'Permintaan ganti KTP ditolak.');
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