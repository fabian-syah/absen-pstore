<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\Branch;
use App\Models\User; // Pastikan import User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isLeader = in_array($user->role, ['admin', 'leader', 'audit']);

        // 1. DATA CABANG / TIM
        $teamData = JobTarget::with(['user'])
            ->whereIn('type', ['team_target', 'team_achievement'])
            ->where('branch_id', $user->branch_id)
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. DATA PRIBADI
        $personalData = JobTarget::whereIn('type', ['personal_target', 'personal_achievement'])
            ->where('user_id', $user->id)
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        return view('job_targets.index', compact('teamData', 'personalData', 'isLeader'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $branches = [];
        $branchMembers = []; 
        $canCreateTeam = in_array($user->role, ['admin', 'leader', 'audit']);
        
        // Ambil ID Cabang dari URL (jika ada) atau dari user login
        $selectedBranchId = $request->get('branch_id') ?? $user->branch_id;

        if ($user->role == 'admin') {
            $branches = Branch::all();
        } elseif ($canCreateTeam) {
            $branches = [$user->branch];
        }

        // Logic Ambil Anggota Tim (Jika Leader/Admin dan ada cabang terpilih)
        if ($canCreateTeam && $selectedBranchId) {
            $branchMembers = User::where('branch_id', $selectedBranchId)
                                 ->where('is_active', true)
                                 ->orderBy('name', 'asc')
                                 ->get();
        }

        return view('job_targets.create', compact('branches', 'canCreateTeam', 'branchMembers', 'selectedBranchId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $canCreateTeam = in_array($user->role, ['admin', 'leader', 'audit']);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required',
            'period_type' => 'required',
            'description' => 'required',
        ]);

        // Validasi Role untuk Tipe Tim
        if (Str::contains($request->type, 'team') && !$canCreateTeam) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuat target tim.');
        }

        // --- 1. SET TANGGAL ---
        $startDate = today();
        $deadline = today();

        if ($request->period_type == 'daily') {
            $startDate = $request->daily_start;
            $deadline = $request->daily_end;
        } elseif ($request->period_type == 'monthly') {
            $startDate = $request->monthly_start . '-01'; 
            $deadline = \Carbon\Carbon::parse($request->monthly_end)->endOfMonth()->format('Y-m-d');
        } elseif ($request->period_type == 'yearly') {
            $startDate = $request->yearly_start . '-01-01';
            $deadline = $request->yearly_end . '-12-31';
        }

        // --- 2. SET PENERIMA TARGET (FIXING BUG DISINI) ---
        
        // Default: Target untuk diri sendiri
        $targetUserId = $user->id;
        
        // FIX: Jika ada input 'assign_user_id' (dari dropdown) DAN user adalah Atasan
        if ($request->filled('assign_user_id') && $canCreateTeam) {
            $targetUserId = $request->assign_user_id;
        }

        // --- 3. SET CABANG ---
        $targetBranchId = $user->branch_id;
        
        // Jika parameter branch_id dikirim (dari hidden input), pakai itu
        if ($request->filled('branch_id')) {
            $targetBranchId = $request->branch_id;
        } 
        // Jika target diberikan ke user lain, pastikan cabang target sesuai user tersebut
        elseif ($targetUserId != $user->id) {
            $assignedUser = User::find($targetUserId);
            if ($assignedUser && $assignedUser->branch_id) {
                $targetBranchId = $assignedUser->branch_id;
            }
        }

        // --- 4. SET LEVEL BINTANG ---
        $starLevel = 0;
        if (Str::contains($request->type, 'target')) {
            $starLevel = $request->star_level ?? 1;
        }

        // SIMPAN KE DATABASE
        JobTarget::create([
            'user_id' => $targetUserId,      // <-- Ini sekarang sudah benar (bisa ID orang lain)
            'creator_id' => $user->id,       // <-- Ini yang membuat (Leader yang login)
            'branch_id' => $targetBranchId,
            'division_id' => $user->division_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'star_level' => $starLevel,
            'period' => $request->period_type,
            'start_date' => $startDate,
            'deadline' => $deadline,
            'status' => 'pending',
            'progress' => 0
        ]);

        // Redirect Cerdas: Balik ke halaman Monitoring Cabang jika asal dari sana
        if ($request->filled('redirect_to_branch')) {
            return redirect()->route('branch-targets.show', $request->redirect_to_branch)
                             ->with('success', 'Target berhasil diberikan kepada anggota.');
        }

        return redirect()->route('job-targets.index')->with('success', 'Data berhasil disimpan.');
    }

    public function edit($id)
    {
        $jobTarget = JobTarget::findOrFail($id);
        $user = Auth::user();

        // Validasi: Hanya Pembuat (Creator), Pemilik (Owner), atau Admin yang boleh edit
        if ($user->id != $jobTarget->user_id && $user->id != $jobTarget->creator_id && $user->role != 'admin') {
            return redirect()->route('job-targets.index')->with('error', 'Anda tidak memiliki akses edit.');
        }

        return view('job_targets.edit', compact('jobTarget'));
    }

    public function update(Request $request, $id)
    {
        $jobTarget = JobTarget::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
        ]);

        $startDate = $jobTarget->start_date;
        $deadline = $jobTarget->deadline;

        if ($request->has('daily_start')) {
            $startDate = $request->daily_start;
            $deadline = $request->daily_end;
        } elseif ($request->has('monthly_start')) {
            $startDate = $request->monthly_start . '-01';
            $deadline = \Carbon\Carbon::parse($request->monthly_end)->endOfMonth()->format('Y-m-d');
        } elseif ($request->has('yearly_start')) {
            $startDate = $request->yearly_start . '-01-01';
            $deadline = $request->yearly_end . '-12-31';
        }

        $jobTarget->update([
            'title' => $request->title,
            'description' => $request->description,
            'star_level' => $request->star_level ?? $jobTarget->star_level,
            'start_date' => $startDate,
            'deadline' => $deadline,
        ]);

        // Cek referer untuk redirect back yang tepat
        return back()->with('success', 'Data berhasil diperbarui.');
    }

    public function updateOutcome(Request $request, $id)
    {
        $target = JobTarget::findOrFail($id);
        
        $request->validate([
            'outcome' => 'required',
            'completion_description' => 'required|string',
            'evidence_photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = $target->evidence_photo;
        if ($request->hasFile('evidence_photo')) {
            $photoPath = $request->file('evidence_photo')->store('job_targets', 'public');
        }

        $target->update([
            'status' => 'completed',
            'outcome' => $request->outcome,
            'completion_description' => $request->completion_description,
            'evidence_photo' => $photoPath,
            'completed_at' => now(),
            'progress' => ($request->outcome == 'Gagal Tercapai') ? $target->progress : 100,
        ]);

        return back()->with('success', 'Status target berhasil diperbarui.');
    }
}