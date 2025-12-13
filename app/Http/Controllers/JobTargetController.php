<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\Branch;
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

        // --- 1. DATA CABANG / TIM (Gabungan Target & Achievement) ---
        // Mengambil semua data yang tipenya 'team_target' ATAU 'team_achievement'
        $teamData = JobTarget::with(['user'])
            ->whereIn('type', ['team_target', 'team_achievement'])
            ->where('branch_id', $user->branch_id)
            ->orderBy('star_level', 'desc') // Prioritas Bintang
            ->orderBy('deadline', 'asc')    // Deadline terdekat
            ->get();

        // --- 2. DATA PRIBADI (Gabungan Target & Achievement) ---
        // Mengambil semua data yang tipenya 'personal_target' ATAU 'personal_achievement'
        $personalData = JobTarget::whereIn('type', ['personal_target', 'personal_achievement'])
            ->where('user_id', $user->id)
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // Variable lain tidak perlu dikirim karena sudah digabung
        return view('job_targets.index', compact('teamData', 'personalData', 'isLeader'));
    }
    public function create()
    {
        $user = Auth::user();
        $branches = [];
        $canCreateTeam = in_array($user->role, ['admin', 'leader', 'audit']);

        if ($user->role == 'admin') {
            $branches = Branch::all();
        } elseif ($canCreateTeam) {
            $branches = [$user->branch];
        }

        return view('job_targets.create', compact('branches', 'canCreateTeam'));
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
            return back()->with('error', 'Anda tidak memiliki akses untuk membuat target/pencapaian tim.');
        }

        // Hitung Tanggal
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

        // Default Data
        $targetUserId = $user->id;
        $targetBranchId = $user->branch_id;

        // Star Level hanya untuk Target (Bukan Achievement)
        $starLevel = 0;
        if (Str::contains($request->type, 'target')) {
            $starLevel = $request->star_level ?? 1;
        }

        // Logic Override untuk Tipe Team
        if (Str::contains($request->type, 'team')) {
            if ($request->has('branch_id')) {
                $targetBranchId = $request->branch_id;
            }
            // User ID tetap pembuat (PIC)
        }

        JobTarget::create([
            'user_id' => $targetUserId,
            'creator_id' => $user->id,
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

        return redirect()->route('job-targets.index')->with('success', 'Data berhasil disimpan.');
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
            // Simpan foto logic standard laravel...
            $photoPath = $request->file('evidence_photo')->store('job_targets', 'public');
        }

        $target->update([
            'status' => 'completed',
            'outcome' => $request->outcome, // Simpan string Indonesia langsung
            'completion_description' => $request->completion_description,
            'evidence_photo' => $photoPath,
            'completed_at' => now(),
            'progress' => ($request->outcome == 'Gagal Tercapai') ? $target->progress : 100,
        ]);

        return back()->with('success', 'Status target berhasil diperbarui.');
    }
}
