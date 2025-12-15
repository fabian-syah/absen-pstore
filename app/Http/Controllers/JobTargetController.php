<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\User;
use App\Models\Branch; // Tambahkan Model Branch
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
        
        // Jika Leader/Staff punya cabang, tampilkan target cabang mereka
        if ($user->branch_id) {
            $teamData = JobTarget::where('branch_id', $user->branch_id)
                ->whereIn('type', ['team_target', 'team_achievement'])
                ->orderBy('star_level', 'desc')
                ->orderBy('deadline', 'asc')
                ->get();
        }
        // Jika Admin/Audit, mungkin logicnya beda (misal lihat semua), tapi untuk "My Target" biasanya kosong atau ikut logic di atas.

        return view('job_targets.index', compact('personalData', 'teamData'));
    }

    /**
     * Form Buat Target Baru
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchMembers = [];
        $branches = []; // Variabel untuk Admin/Audit memilih cabang

        // --- LOGIKA FILTER DATA ---

        // 1. ADMIN & AUDIT (Akses Full / Global)
        if (in_array($user->role, ['admin', 'audit'])) {
            // Ambil SEMUA user yang aktif (untuk dropdown 'Tugaskan Kepada')
            // Kita load relasi branch & division agar tampilannya rapi
            $branchMembers = User::with(['branch', 'division'])
                ->where('is_active', true)
                ->orderBy('branch_id') // Urutkan berdasarkan cabang biar rapi di optgroup
                ->orderBy('name')
                ->get();

            // Ambil daftar Cabang (untuk dropdown 'Target Tim')
            $branches = Branch::orderBy('name', 'asc')->get();
        } 
        
        // 2. LEADER (Akses Terbatas Cabang Sendiri)
        elseif ($user->role == 'leader' && $user->branch_id) {
            $branchMembers = User::where('branch_id', $user->branch_id)
                ->where('id', '!=', $user->id) // Exclude diri sendiri
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
            
            // Leader tidak butuh variabel $branches karena otomatis ke cabangnya sendiri
        }

        return view('job_targets.create', compact('branchMembers', 'branches'));
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
            // Validasi tambahan jika Admin memilih target tim
            'target_branch_id' => 'nullable|exists:branches,id',
        ]);

        // --- LOGIKA PENENTUAN USER ID & BRANCH ID ---
        
        // Default awal: Target milik diri sendiri
        $targetUserId = $user->id; 
        $branchId     = $user->branch_id;

        // A. JIKA TARGET TIM / CABANG
        if (Str::contains($request->type, 'team')) {
            // User ID tetap si pembuat
            $targetUserId = $user->id; 
            
            // Tentukan Branch ID Targetnya
            if (in_array($user->role, ['admin', 'audit']) && $request->filled('target_branch_id')) {
                // Admin/Audit bisa set manual cabang mana yang ditarget
                $branchId = $request->target_branch_id;
            } else {
                // Leader otomatis ke cabangnya sendiri
                $branchId = $user->branch_id;
            }
        } 
        
        // B. JIKA TARGET PERSONAL (ASSIGN KE ORANG LAIN)
        elseif (in_array($user->role, ['leader', 'admin', 'audit']) && $request->filled('assign_user_id')) {
            $targetUserId = $request->assign_user_id;
            
            // Ambil branch dari user yang dituju agar sinkron
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
        
        // Cek Hak Akses Edit
        if (Auth::id() != $jobTarget->user_id && !in_array(Auth::user()->role, ['admin', 'leader', 'audit'])) {
            return redirect()->route('job-targets.index')->with('error', 'Akses ditolak.');
        }

        return view('job_targets.edit', compact('jobTarget'));
    }

    /**
     * Update Data Target
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
     * Update Status Hasil
     */
    public function updateOutcome(Request $request, $id)
    {
        $request->validate([
            'outcome'                => 'required|string',
            'completion_description' => 'required|string',
            'evidence_photo'         => 'nullable|image|max:2048'
        ]);

        $target = JobTarget::findOrFail($id);

        // Upload Foto Bukti
        $photoPath = $target->evidence_photo_path;
        if ($request->hasFile('evidence_photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('evidence_photo')->store('targets/evidence', 'public');
        }

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