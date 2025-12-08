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
    public function index(Request $request)
    {
        $user = Auth::user();

        // --- 1. TARGET CABANG (TEAM) ---
        // Logic: Admin/Leader lihat semua/divisi, User biasa lihat yang ditugaskan ke dia tapi tipe team
        $teamQuery = JobTarget::with(['user'])->where('type', 'team');

        if ($user->role == 'admin') {
            // All
        } elseif ($user->role == 'audit') {
            $teamQuery->whereIn('branch_id', $user->branches->pluck('id'));
        } elseif ($user->role == 'leader') {
            $teamQuery->where(function($q) use ($user) {
                $q->where('division_id', $user->division_id)
                  ->orWhere('creator_id', $user->id);
            });
        } else {
            // User Biasa / Security
            $teamQuery->where('user_id', $user->id);
        }

        // Get Data & Sortir Status (Ongoing vs History/Completed)
        $teamTargets = $teamQuery->orderBy('deadline', 'asc')->get();

        // --- 2. TARGET PRIBADI (PERSONAL) ---
        // Hanya diri sendiri
        $personalTargets = JobTarget::where('user_id', $user->id)
                            ->where('type', 'personal')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // --- 3. PENCAPAIAN (ACHIEVEMENT) ---
        // Bisa dilihat semua atau logic lain. Asumsi: Sama seperti Personal tapi kategori achievement
        $achievements = JobTarget::where('user_id', $user->id)
                            ->where('type', 'achievement')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // Data User untuk Dropdown Create (Admin/Leader Only)
        $users = [];
        if (in_array($user->role, ['admin', 'leader', 'audit'])) {
            $users = User::where('is_active', true)->where('role', '!=', 'admin')->get();
        }

        return view('job_targets.index', compact('teamTargets', 'personalTargets', 'achievements', 'users'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:personal,team,achievement',
            'period_type' => 'required|in:daily,monthly,yearly',
            'description' => 'required',
        ]);

        // Hitung Tanggal Start & Deadline berdasarkan input Period
        $startDate = today();
        $deadline = today();

        if ($request->period_type == 'daily') {
            $startDate = $request->daily_start;
            $deadline = $request->daily_end;
        } elseif ($request->period_type == 'monthly') {
            // Input format: YYYY-MM
            $startDate = $request->monthly_start . '-01'; 
            $deadline = \Carbon\Carbon::parse($request->monthly_end)->endOfMonth()->format('Y-m-d');
        } elseif ($request->period_type == 'yearly') {
            // Input format: YYYY
            $startDate = $request->yearly_start . '-01-01';
            $deadline = $request->yearly_end . '-12-31';
        }

        // Tentukan User ID Target
        $targetUserId = $user->id; // Default diri sendiri (Personal/Achievement)
        
        // Jika Team, cek hak akses assign
        if ($request->type == 'team') {
            if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
                return back()->with('error', 'Anda tidak memiliki akses membuat target Cabang.');
            }
            // Jika ada input user_id (assign ke orang lain)
            if ($request->has('user_id') && $request->user_id) {
                $targetUserId = $request->user_id;
            }
        }

        $targetUser = User::find($targetUserId);

        JobTarget::create([
            'user_id' => $targetUserId,
            'creator_id' => $user->id,
            'branch_id' => $targetUser->branch_id,
            'division_id' => $targetUser->division_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'period' => $request->period_type, // daily, monthly, yearly
            'start_date' => $startDate,
            'deadline' => $deadline,
            'status' => 'pending',
            'progress' => 0
        ]);

        return back()->with('success', 'Target berhasil dibuat.');
    }

    // Fungsi Update Aksi (5 Tombol)
    public function updateOutcome(Request $request, $id)
    {
        $target = JobTarget::findOrFail($id);
        
        $request->validate([
            'outcome' => 'required|in:exceeded,achieved,partial,failed,changed',
            'completion_description' => 'required|string',
            'evidence_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // Max 5MB sebelum compress
        ]);

        // Upload & Compress Foto (Native PHP)
        $photoPath = null;
        if ($request->hasFile('evidence_photo')) {
            $file = $request->file('evidence_photo');
            $filename = 'evidence_' . time() . '_' . Str::random(10) . '.jpg';
            
            // Kompresi Manual
            $source = imagecreatefromstring(file_get_contents($file));
            ob_start();
            imagejpeg($source, null, 60); // Quality 60%
            $compressedImage = ob_get_clean();
            imagedestroy($source);

            // Simpan ke Storage
            Storage::disk('public')->put('job_targets/' . $filename, $compressedImage);
            $photoPath = 'job_targets/' . $filename;
        }

        // Update Data
        $target->update([
            'status' => 'completed', // Status umum
            'outcome' => $request->outcome,
            'completion_description' => $request->completion_description,
            'evidence_photo' => $photoPath,
            'completed_at' => now(),
            'progress' => ($request->outcome == 'failed') ? $target->progress : 100, // Jika gagal progress tetap, yg lain 100
        ]);

        return back()->with('success', 'Status target berhasil diperbarui.');
    }
}