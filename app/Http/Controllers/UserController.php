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

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
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
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        $divisions = Division::all();

        // Kita tidak perlu load WorkSchedule lagi karena input langsung
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
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
            // VALIDASI JAM KERJA (Nullable artinya boleh kosong/fleksibel)
            'check_in_start' => 'nullable',
            'check_in_end' => 'nullable',
            'check_out_start' => 'nullable',
            'check_out_end' => 'nullable',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Handle jam kerja: jika input kosong string "", set ke null
        $data['check_in_start'] = $request->check_in_start ?: null;
        $data['check_in_end'] = $request->check_in_end ?: null;
        $data['check_out_start'] = $request->check_out_start ?: null;
        $data['check_out_end'] = $request->check_out_end ?: null;

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

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan dengan jam kerja spesifik.');
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

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles'));
    }

    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();

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
            // VALIDASI JAM KERJA EDIT
            'check_in_start' => 'nullable',
            'check_in_end' => 'nullable',
            'check_out_start' => 'nullable',
            'check_out_end' => 'nullable',
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Handle jam kerja: jika input kosong, set ke null
        $data['check_in_start'] = $request->check_in_start ?: null;
        $data['check_in_end'] = $request->check_in_end ?: null;
        $data['check_out_start'] = $request->check_out_start ?: null;
        $data['check_out_end'] = $request->check_out_end ?: null;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $data['hire_date'] = $request->hire_date ?? null;

        if ($request->hasFile('profile_photo_path')) {
            if ($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = $request->file('profile_photo_path')->store('profile-photos', 'public');
        }

        $user->update($data);

        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
        } else {
            $user->divisions()->sync($request->multi_divisions ?? []);
            if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
                 $user->division_id = $request->multi_divisions[0];
                 $user->save();
            }
        }

        return redirect()->route('users.index')->with('success', 'Data user dan jam kerja berhasil diperbarui.');
    }

    // ... Method destroy, show, verifyUser, photoRequests dll TETAP SAMA seperti sebelumnya ...
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

    public function show(User $user) { /* ... kode sama ... */ return view('users.user_show', compact('user')); }
    public function verifyUser(User $user) { /* ... kode sama ... */ return back()->with('success', 'Verifikasi berhasil'); }
    public function photoRequests() { /* ... kode sama ... */ return view('users.photo_requests'); }
    public function approvePhotoRequest(User $user) { /* ... kode sama ... */ return back()->with('success', 'Approved'); }
    public function toggleStatus(User $user) { /* ... kode sama ... */ return back()->with('success', 'Status changed'); }
}