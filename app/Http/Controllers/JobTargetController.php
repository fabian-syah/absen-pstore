<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\User;
use App\Models\Branch; // Jangan lupa import ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // --- 1. TARGET CABANG (TEAM) ---
        $teamQuery = JobTarget::with(['user', 'branch'])->where('type', 'team');

        if ($user->role == 'admin') {
            // Admin lihat semua
        } elseif ($user->role == 'audit') {
            // Audit lihat cabang yang dipegang
            $teamQuery->whereIn('branch_id', $user->branches->pluck('id'));
        } elseif ($user->role == 'leader') {
            // Leader lihat divisi dia atau yang dia buat
            $teamQuery->where(function($q) use ($user) {
                $q->where('division_id', $user->division_id)
                  ->orWhere('creator_id', $user->id)
                  ->orWhere('branch_id', $user->branch_id);
            });
        } else {
            // User Biasa: lihat yang di assign ke dia (jika ada logic assign user) 
            // atau target di cabang dia
            $teamQuery->where('branch_id', $user->branch_id);
        }

        $teamTargets = $teamQuery->orderBy('deadline', 'asc')->get();

        // --- 2. TARGET PRIBADI ---
        $personalTargets = JobTarget::where('user_id', $user->id)
                            ->where('type', 'personal')
                            ->orderBy('created_at', 'desc')->get();

        // --- 3. PENCAPAIAN ---
        $achievements = JobTarget::where('user_id', $user->id)
                            ->where('type', 'achievement')
                            ->orderBy('created_at', 'desc')->get();

        return view('job_targets.index', compact('teamTargets', 'personalTargets', 'achievements'));
    }

    // --- METHOD CREATE (DIPERBARUI) ---
    public function create()
    {
        $user = Auth::user();
        $branches = [];
        
        // Logic ambil data Cabang
        if ($user->role == 'admin') {
            $branches = Branch::all();
        } elseif ($user->role == 'audit') {
            // Asumsi ada relasi $user->branches (Audit pegang banyak cabang)
            $branches = $user->branches; 
        } elseif ($user->role == 'leader') {
            // Leader cuma punya 1 cabang
            if($user->branch) {
                $branches = [$user->branch];
            }
        }

        return view('job_targets.create', compact('branches'));
    }

    // --- METHOD STORE (DIPERBARUI) ---
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:personal,team,achievement',
            'period_type' => 'required|in:daily,monthly,yearly',
            'description' => 'required',
        ]);

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
        $targetUserId = $user->id; // Default ke diri sendiri
        $targetBranchId = $user->branch_id; // Default cabang sendiri

        // LOGIC TARGET TIM / CABANG
        if ($request->type == 'team') {
            if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
                return back()->with('error', 'Akses ditolak.');
            }

            // Validasi Branch ID harus dipilih
            $request->validate(['branch_id' => 'required|exists:branches,id']);
            
            // Set Target Cabang
            $targetBranchId = $request->branch_id;
            
            // Untuk target cabang, user_id kita set ke Pembuatnya saja (Leader/Audit/Admin)
            // Karena ini target 1 gedung, bukan 1 orang.
            $targetUserId = $user->id; 
        }

        JobTarget::create([
            'user_id' => $targetUserId,
            'creator_id' => $user->id,
            'branch_id' => $targetBranchId,
            'division_id' => $user->division_id, // Opsional: sesuaikan jika perlu
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'period' => $request->period_type,
            'start_date' => $startDate,
            'deadline' => $deadline,
            'status' => 'pending',
            'progress' => 0
        ]);

        return redirect()->route('job-targets.index')->with('success', 'Target berhasil dibuat.');
    }

    public function updateOutcome(Request $request, $id)
    {
        $target = JobTarget::findOrFail($id);
        
        $request->validate([
            'outcome' => 'required|in:exceeded,achieved,partial,failed,changed',
            'completion_description' => 'required|string',
            'evidence_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('evidence_photo')) {
            $file = $request->file('evidence_photo');
            $filename = 'evidence_' . time() . '_' . Str::random(10) . '.jpg';
            
            $source = imagecreatefromstring(file_get_contents($file));
            ob_start();
            imagejpeg($source, null, 60);
            $compressedImage = ob_get_clean();
            imagedestroy($source);

            Storage::disk('public')->put('job_targets/' . $filename, $compressedImage);
            $photoPath = 'job_targets/' . $filename;
        }

        $target->update([
            'status' => 'completed',
            'outcome' => $request->outcome,
            'completion_description' => $request->completion_description,
            'evidence_photo' => $photoPath,
            'completed_at' => now(),
            'progress' => ($request->outcome == 'failed') ? $target->progress : 100,
        ]);

        return back()->with('success', 'Status target berhasil diperbarui.');
    }
}