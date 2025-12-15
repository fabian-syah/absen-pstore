<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobTargetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil Data Personal
        $personalData = JobTarget::where('user_id', $user->id)
            ->whereIn('type', ['personal_target', 'personal_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Ambil Data Tim/Cabang
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

    public function create(Request $request)
    {
        $user = Auth::user();
        $branchMembers = [];
        $branches = []; 

        // SKENARIO 1: AKSES LEWAT MENU "TARGET CABANG"
        if ($request->filled('branch_id')) {
            $targetBranchId = $request->branch_id;
            if (in_array($user->role, ['admin', 'audit']) || ($user->role == 'leader' && $user->branch_id == $targetBranchId)) {
                $branchMembers = User::with(['branch', 'division'])
                    ->where('branch_id', $targetBranchId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
                $branches = Branch::where('id', $targetBranchId)->get();
            }
        } 
        // SKENARIO 2: AKSES LEWAT MENU UTAMA
        else {
            if ($user->role == 'leader' && $user->branch_id) {
                $branchMembers = User::where('branch_id', $user->branch_id)
                    ->where('id', '!=', $user->id)
                    ->where('is_active', true)
                    ->orderBy('name', 'asc')
                    ->get();
            } else {
                $branchMembers = []; 
            }
        }

        return view('job_targets.create', compact('branchMembers', 'branches'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|string',
            'period_type' => 'required|in:daily,monthly,yearly',
        ]);

        $targetUserId = $user->id; 
        $branchId     = $user->branch_id;

        if (Str::contains($request->type, 'team')) {
            if ($request->filled('target_branch_id')) {
                $branchId = $request->target_branch_id;
            } elseif ($user->role == 'leader') {
                $branchId = $user->branch_id;
            } else {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses membuat target tim dari menu ini.');
            }
            $targetUserId = $user->id; 
        } 
        elseif ($request->filled('assign_user_id') && $request->assign_user_id != $user->id) {
            $targetUserId = $request->assign_user_id;
            $assignedUser = User::find($targetUserId);
            if($assignedUser) {
                $branchId = $assignedUser->branch_id;
            }
        }

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

        JobTarget::create([
            'user_id'     => $targetUserId,
            'branch_id'   => $branchId, 
            'creator_id'  => $user->id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'star_level'  => $request->input('star_level', 1),
            'period'      => $request->period_type,
            'start_date'  => $startDate,
            'deadline'    => $deadline,
            'status'      => 'pending'
        ]);

        if ($request->filled('redirect_to_branch')) {
            return redirect()->route('branch-targets.show', $request->redirect_to_branch)
                ->with('success', 'Target berhasil ditambahkan untuk cabang ini.');
        }

        return redirect()->route('job-targets.index')->with('success', 'Target berhasil dibuat.');
    }

    public function edit($id)
    {
        $jobTarget = JobTarget::findOrFail($id);
        if (Auth::id() != $jobTarget->user_id && !in_array(Auth::user()->role, ['admin', 'leader', 'audit'])) {
            return redirect()->route('job-targets.index')->with('error', 'Akses ditolak.');
        }
        return view('job_targets.edit', compact('jobTarget'));
    }

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

    public function updateOutcome(Request $request, $id)
    {
        $request->validate([
            'outcome'                => 'required|string',
            'completion_description' => 'required|string',
            'evidence_photo'         => 'nullable|image|max:2048'
        ]);

        $target = JobTarget::findOrFail($id);
        $photoPath = $target->evidence_photo_path;
        if ($request->hasFile('evidence_photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('evidence_photo')->store('targets/evidence', 'public');
        }

        $target->update([
            'outcome'                => $request->outcome,
            'completion_description' => $request->completion_description,
            'evidence_photo_path'    => $photoPath,
            'status'                 => 'completed',
            'completed_at'           => now(),
        ]);

        return back()->with('success', 'Status target berhasil diperbarui.');
    }

    /**
     * UPDATE STATUS KHUSUS UNTUK ADMIN/LEADER
     */
    public function adminUpdateStatus(Request $request, $id)
    {
        // Pastikan hanya role tertentu yang bisa akses
        if (!in_array(Auth::user()->role, ['admin', 'audit', 'leader'])) {
            return back()->with('error', 'Unauthorized action.');
        }

        $target = JobTarget::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,rejected',
        ]);

        $target->update([
            'status' => $request->status,
            // Jika status completed, anggap selesai sekarang
            'completed_at' => $request->status == 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Status target berhasil diperbarui oleh admin.');
    }
}