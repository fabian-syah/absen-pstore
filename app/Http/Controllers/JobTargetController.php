<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobTargetController extends Controller
{
    /**
     * Halaman Utama Dashboard Target (My Target)
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil Data Personal (Milik User Sendiri)
        $personalData = JobTarget::where('user_id', $user->id)
            ->whereIn('type', ['personal_target', 'personal_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Ambil Data Tim/Cabang (Target Global Cabang User)
        $teamData = collect(); 
        if ($user->branch_id) {
            $teamData = JobTarget::where('branch_id', $user->branch_id)
                ->whereIn('type', ['team_target', 'team_achievement'])
                ->orderBy('star_level', 'desc')
                ->orderBy('deadline', 'asc')
                ->get();
        }

        return view('job_targets.index', compact('personalData', 'teamData'));
    }

    /**
     * Form Buat Target Baru
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchMembers = [];

        // LOGIKA FILTER MEMBER:
        // Leader, Admin, dan Audit boleh melihat daftar anggota tim (sesuai cabang yang dipegang)
        $allowedRoles = ['leader', 'admin', 'audit'];

        if (in_array($user->role, $allowedRoles) && $user->branch_id) {
            $branchMembers = User::where('branch_id', $user->branch_id)
                ->where('id', '!=', $user->id) // Exclude diri sendiri
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('job_targets.create', compact('branchMembers'));
    }

    /**
     * Simpan Target Baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|string',
            'star_level'  => 'nullable|integer',
            'period_type' => 'required|in:daily,monthly,yearly',
        ]);

        // --- LOGIKA PENENTUAN USER ID & BRANCH ID ---
        
        $allowedRoles = ['leader', 'admin', 'audit'];
        
        // Default: Target milik diri sendiri
        $targetUserId = $user->id; 
        $branchId     = $user->branch_id;

        // Cek apakah ini Target Tim/Cabang?
        if (Str::contains($request->type, 'team')) {
            // Jika Target Tim:
            // User ID tetap si pembuat, tapi type-nya 'team_target'
            // Branch ID dipastikan ambil dari si pembuat (Leader/Admin/Audit)
            $targetUserId = $user->id; 
            $branchId     = $user->branch_id;
        } 
        // Jika Target Personal dan Role Berhak memilih orang lain
        elseif (in_array($user->role, $allowedRoles) && $request->filled('assign_user_id')) {
            $targetUserId = $request->assign_user_id;
            
            // Ambil branch dari user yang dituju (biar sinkron)
            $assignedUser = User::find($targetUserId);
            if($assignedUser) {
                $branchId = $assignedUser->branch_id;
            }
        }

        // Setup Tanggal (Deadline)
        $startDate = now();
        $deadline  = now();

        if ($request->period_type == 'daily') {
            $startDate = $request->daily_start;
            $deadline  = $request->daily_end;
        } elseif ($request->period_type == 'monthly') {
            $startDate = $request->monthly_start . '-01'; 
            $deadline  = \Carbon\Carbon::parse($request->monthly_end)->endOfMonth();
        } elseif ($request->period_type == 'yearly') {
            $startDate = $request->yearly_start . '-01-01';
            $deadline  = $request->yearly_end . '-12-31';
        }

        // Simpan ke Database
        JobTarget::create([
            'user_id'     => $targetUserId,
            'branch_id'   => $branchId, 
            'created_by'  => $user->id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'star_level'  => $request->input('star_level', 1),
            'period'      => $request->period_type,
            'start_date'  => $startDate,
            'deadline'    => $deadline,
            'status'      => 'pending'
        ]);

        // Redirect Logic
        if ($request->has('redirect_to_branch')) {
            return redirect()->route('branch-targets.show', $request->redirect_to_branch)
                ->with('success', 'Target berhasil ditambahkan.');
        }

        return redirect()->route('job-targets.index')->with('success', 'Target berhasil dibuat.');
    }

    /**
     * Form Edit Target
     */
    public function edit($id)
    {
        $jobTarget = JobTarget::findOrFail($id);
        
        // Cek Hak Akses Edit (Pemilik atau Leader/Admin/Audit)
        // Admin & Audit bisa edit punya orang lain jika diperlukan, atau batasi sesuai kebutuhan
        if (Auth::id() != $jobTarget->user_id && !in_array(Auth::user()->role, ['admin', 'leader', 'audit'])) {
            return redirect()->route('job-targets.index')->with('error', 'Akses ditolak.');
        }

        return view('job_targets.edit', compact('jobTarget'));
    }

    /**
     * Update Data Target (Judul/Deskripsi)
     */
    public function update(Request $request, $id)
    {
        $jobTarget = JobTarget::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $jobTarget->update([
            'title'       => $request->title,
            'description' => $request->description,
            'star_level'  => $request->input('star_level', $jobTarget->star_level),
        ]);

        return redirect()->route('job-targets.index')->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Update Status Hasil (Modal Popup)
     */
    public function updateOutcome(Request $request, $id)
    {
        $request->validate([
            'outcome'                => 'required|string',
            'completion_description' => 'required|string',
            'evidence_photo'         => 'nullable|image|max:2048'
        ]);

        $target = JobTarget::findOrFail($id);

        // Upload Foto Bukti jika ada
        $photoPath = $target->evidence_photo_path;
        if ($request->hasFile('evidence_photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('evidence_photo')->store('targets/evidence', 'public');
        }

        // Tentukan Status
        $status = 'completed';
        
        $target->update([
            'outcome'                => $request->outcome,
            'completion_description' => $request->completion_description,
            'evidence_photo_path'    => $photoPath,
            'status'                 => $status,
            'completed_at'           => now(),
        ]);

        return back()->with('success', 'Status target berhasil diperbarui.');
    }
}