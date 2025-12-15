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
        // (Logic index My Target tetap sama seperti sebelumnya)
        $user = Auth::user();
        $personalData = JobTarget::where('user_id', $user->id)
            ->whereIn('type', ['personal_target', 'personal_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

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
     * FORM CREATE (Dimodifikasi untuk Support Admin via Cabang)
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchMembers = [];
        $branches = []; 

        // SKENARIO 1: ADMIN/AUDIT MASUK LEWAT MENU "TARGET CABANG"
        // Kita kunci data hanya untuk cabang tersebut agar tidak salah pilih orang
        if ($request->filled('branch_id')) {
            $targetBranchId = $request->branch_id;

            // Ambil member HANYA dari cabang tersebut
            $branchMembers = User::with(['branch', 'division'])
                ->where('branch_id', $targetBranchId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Dropdown cabang dikunci ke 1 cabang ini saja
            $branches = Branch::where('id', $targetBranchId)->get();
        } 
        
        // SKENARIO 2: MASUK LEWAT MENU BIASA (JOB DESK)
        else {
            // Jika Admin/Audit -> Load Semua
            if (in_array($user->role, ['admin', 'audit'])) {
                $branchMembers = User::with(['branch', 'division'])
                    ->where('is_active', true)
                    ->orderBy('branch_id')
                    ->orderBy('name')
                    ->get();
                $branches = Branch::orderBy('name', 'asc')->get();
            } 
            // Jika Leader -> Load Cabang Sendiri
            elseif ($user->role == 'leader' && $user->branch_id) {
                $branchMembers = User::where('branch_id', $user->branch_id)
                    ->where('id', '!=', $user->id)
                    ->where('is_active', true)
                    ->orderBy('name', 'asc')
                    ->get();
            }
        }

        return view('job_targets.create', compact('branchMembers', 'branches'));
    }

    /**
     * STORE DATA (Simpan Target)
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

        // LOGIKA PENENTUAN SASARAN (USER & CABANG)
        $targetUserId = $user->id; 
        $branchId     = $user->branch_id;

        // A. Jika Target Tim (Global Cabang)
        if (Str::contains($request->type, 'team')) {
            // Jika Admin/Audit input manual cabang, atau sistem inject via hidden input
            if ($request->filled('target_branch_id')) {
                $branchId = $request->target_branch_id;
            }
            // User ID pembuat tetap si admin/leader
            $targetUserId = $user->id; 
        } 
        // B. Jika Target Personal (Ditugaskan ke orang lain)
        elseif ($request->filled('assign_user_id')) {
            $targetUserId = $request->assign_user_id;
            
            // Branch ID menyesuaikan user yang ditarget
            $assignedUser = User::find($targetUserId);
            if($assignedUser) {
                $branchId = $assignedUser->branch_id;
            }
        }

        // LOGIKA TANGGAL
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

        // SIMPAN KE DB
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

        // CEK REDIRECT KHUSUS (PENTING BUAT ADMIN/AUDIT)
        // Jika ada input hidden 'redirect_to_branch', balik ke halaman detail cabang
        if ($request->filled('redirect_to_branch')) {
            return redirect()->route('branch-targets.show', $request->redirect_to_branch)
                ->with('success', 'Target berhasil ditambahkan untuk cabang ini.');
        }

        // Default balik ke My Target
        return redirect()->route('job-targets.index')->with('success', 'Target berhasil dibuat.');
    }

    // Method Edit, Update, UpdateOutcome tetap sama seperti kode sebelumnya...
    public function edit($id) { /* Kode Sama */ }
    public function update(Request $request, $id) { /* Kode Sama */ }
    public function updateOutcome(Request $request, $id) {
         // Pastikan kode update outcome sama seperti yang Anda miliki sebelumnya
         // (Simpan foto, ubah status jadi completed, dll)
         // ...
         return back()->with('success', 'Status target berhasil diperbarui.');
    }
}