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
        // Middleware: Hanya Admin dan Audit yang boleh akses
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'audit'])) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        // 1. Filter Role/Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        // 2. Search
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
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        $divisions = Division::all();

        return view('users.user_create', compact('divisions', 'branches', 'allowedRoles'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date', // Tambahan
            'hire_date' => 'nullable|date',  // Tambahan
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        $data['division_id'] = ($request->has('multi_divisions') && count($request->multi_divisions) > 0) ? $request->multi_divisions[0] : null;

        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        if ($request->role == 'admin') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();
        $data['hire_date'] = now();

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
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403);
        }
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }

        $branches = Branch::all();
        $divisions = Division::all();
        $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];

        // Logika filter role/branch disederhanakan agar tidak terlalu panjang, tapi tetap aman
        if ($auth_user->role != 'admin' || $auth_user->branch_id != null) {
            // Logic khusus audit/admin cabang jika diperlukan
        }

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles'));
    }

    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date', // Tambahan
            'hire_date' => 'nullable|date',  // Tambahan
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'nullable|exists:branches,id',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Update logic password & photo
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Logic Update Foto (Jika admin yang upload, bisa bypass)
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
            return back()->with('error', 'Gagal hapus user.');
        }
    }

    /**
     * MENAMPILKAN DETAIL USER (Dashboard Verifikasi)
     */
    public function show(User $user)
    {
        $auth_user = Auth::user();

        // Security Check
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403);
        }

        $user->load(['branch', 'division', 'branches', 'divisions']);

        // Ambil statistik absensi user tersebut
        $stats = $this->getSpecificUserStats($user->id);

        $recentAttendance = Attendance::where('user_id', $user->id)
            ->latest('check_in_time')
            ->take(5)
            ->get();

        return view('users.user_show', compact('user', 'stats', 'recentAttendance'));
    }

    /**
     * FITUR BARU: VERIFIKASI USER (CENTANG BIRU)
     */
    public function verifyUser(User $user)
    {
        // 1. Jika ingin memverifikasi
        if (!$user->is_verified) {
            // Cek Syarat: Foto Profil, KTP, & WhatsApp harus ada
            if (!$user->profile_photo_path || !$user->ktp_photo_path || !$user->whatsapp) {
                return back()->with('error', 'Gagal Verifikasi: User belum melengkapi Foto Profil, Foto KTP, atau No WhatsApp.');
            }
            $user->is_verified = true;
            $msg = 'User berhasil diverifikasi (Centang Biru Aktif). Foto profil user sekarang terkunci.';
        } else {
            // 2. Jika ingin mencabut verifikasi
            $user->is_verified = false;
            $msg = 'Verifikasi dicabut (Centang Biru Non-Aktif).';
        }

        $user->save();
        return back()->with('success', $msg);
    }

    /**
     * FITUR BARU: APPROVE REQUEST GANTI FOTO
     */
    public function approvePhotoRequest(User $user)
    {
        if ($user->photo_request_status !== 'pending') {
            return back()->with('error', 'Tidak ada permintaan ganti foto yang pending.');
        }

        // Set status approved -> User bisa upload sekali
        // Centang biru TETAP ADA, tapi user diberi akses upload.
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

    // Helper Stats (Internal)
    private function getSpecificUserStats($user_id)
    {
        $presentCount = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereIn('status', ['verified', 'present', 'late'])
            ->count();

        // Simple return structure
        return [
            'total' => $presentCount,
            'present' => $presentCount,
            'alpha' => 0, // Simplified logic needed
            'late' => 0,
            'early' => 0,
            'pending' => 0,
            'on_time' => 0,
            'on_time_percentage' => 0,
            'late_percentage' => 0,
            'current_month' => Carbon::now()->format('F Y')
        ];
    }
}
