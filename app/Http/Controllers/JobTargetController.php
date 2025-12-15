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

        // 1. Ambil Data Personal (Milik User Sendiri)
        $personalData = JobTarget::where('user_id', $user->id)
            ->whereIn('type', ['personal_target', 'personal_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Ambil Data Tim/Cabang (Jika ada)
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
     * FORM CREATE
     * Logika: 
     * - Jika tidak ada branch_id (Menu Utama), Admin/Audit hanya bisa Personal.
     * - Jika ada branch_id (Menu Cabang), Admin/Audit bisa Team/Assign ke member.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchMembers = [];
        $branches = []; 

        // SKENARIO 1: AKSES LEWAT MENU "TARGET CABANG" (Ada request branch_id)
        // Admin/Audit bisa assign ke orang lain dan buat target tim DI CABANG INI.
        if ($request->filled('branch_id')) {
            $targetBranchId = $request->branch_id;

            // Cek Izin: Hanya Admin, Audit, atau Leader cabang tsb
            if (in_array($user->role, ['admin', 'audit']) || ($user->role == 'leader' && $user->branch_id == $targetBranchId)) {
                
                // Ambil member HANYA dari cabang tersebut
                $branchMembers = User::with(['branch', 'division'])
                    ->where('branch_id', $targetBranchId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                // Dropdown cabang dikunci ke 1 cabang ini saja (untuk keperluan hidden input/validasi)
                $branches = Branch::where('id', $targetBranchId)->get();
            }
        } 
        
        // SKENARIO 2: AKSES LEWAT MENU UTAMA "JOB DESK" (Tidak ada branch_id)
        else {
            // LEADER: Masih boleh melihat anggota timnya sendiri
            if ($user->role == 'leader' && $user->branch_id) {
                $branchMembers = User::where('branch_id', $user->branch_id)
                    ->where('id', '!=', $user->id)
                    ->where('is_active', true)
                    ->orderBy('name', 'asc')
                    ->get();
            }
            
            // ADMIN, AUDIT, SECURITY, USER BIASA:
            // Kosongkan $branchMembers. 
            // Akibatnya, di View kolom "Tugaskan Kepada" akan hilang, 
            // dan user dipaksa membuat untuk diri sendiri (Personal).
            else {
                $branchMembers = []; 
            }
        }

        return view('job_targets.create', compact('branchMembers', 'branches'));
    }

    /**
     * STORE DATA
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|string',
            'period_type' => 'required|in:daily,monthly,yearly',
        ]);

        // LOGIKA DEFAULT: Target untuk diri sendiri
        $targetUserId = $user->id; 
        $branchId     = $user->branch_id;

        // A. JIKA TARGET TIM / CABANG (Hanya valid jika akses via Menu Cabang / Leader)
        if (Str::contains($request->type, 'team')) {
            // Jika ada inject hidden target_branch_id (dari menu cabang)
            if ($request->filled('target_branch_id')) {
                $branchId = $request->target_branch_id;
            } elseif ($user->role == 'leader') {
                $branchId = $user->branch_id;
            } else {
                // Security guard: jika user biasa coba inspect element ganti value jadi team
                return redirect()->back()->with('error', 'Anda tidak memiliki akses membuat target tim dari menu ini.');
            }
            // User ID pembuat tetap si admin/leader
            $targetUserId = $user->id; 
        } 
        
        // B. JIKA ASSIGN KE ORANG LAIN
        elseif ($request->filled('assign_user_id') && $request->assign_user_id != $user->id) {
            // Validasi: Apakah boleh assign?
            // Boleh jika ada di menu cabang (filled branch_id di url sebelumnya) atau Leader
            $targetUserId = $request->assign_user_id;
            
            $assignedUser = User::find($targetUserId);
            if($assignedUser) {
                $branchId = $assignedUser->branch_id;
            }
        }

        // LOGIKA TANGGAL
        $startDate = now();
        $deadline  = now(); // Default

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

        // SIMPAN KE DB
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

        // REDIRECT LOGIC
        // Jika dari menu cabang, kembalikan ke cabang
        if ($request->filled('redirect_to_branch')) {
            return redirect()->route('branch-targets.show', $request->redirect_to_branch)
                ->with('success', 'Target berhasil ditambahkan untuk cabang ini.');
        }

        // Default balik ke My Target
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
}