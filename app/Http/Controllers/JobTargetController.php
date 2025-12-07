<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // -----------------------------------------------------------
        // 1. DATA TARGET PRIBADI (Harian - Card Atas)
        // -----------------------------------------------------------
        $myDailyTargets = JobTarget::where('user_id', $user->id)
            ->where('type', 'individual')
            ->where('period', 'daily')
            ->whereDate('created_at', today()) // Hanya hari ini
            ->orderBy('created_at', 'desc')
            ->get();

        // -----------------------------------------------------------
        // 2. DATA TARGET TIM / CABANG (Card Bawah)
        // -----------------------------------------------------------
        
        // Query Dasar
        $teamQuery = JobTarget::with(['user', 'creator'])->where('type', 'team');

        // Filter Akses Melihat
        if ($user->role == 'admin') {
            // Admin lihat semua
        } elseif ($user->role == 'audit') {
            // Audit lihat cabang terkait
            $teamQuery->whereIn('branch_id', $user->branches->pluck('id'));
        } elseif ($user->role == 'leader') {
            // Leader lihat target timnya (divisi) ATAU target yang dia buat sendiri
            $teamQuery->where(function($q) use ($user) {
                $q->where('division_id', $user->division_id)
                  ->orWhere('creator_id', $user->id);
            });
        } else {
            // User biasa hanya lihat target yang ditugaskan ke dia
            $teamQuery->where('user_id', $user->id);
        }

        // Pisahkan berdasarkan Periode untuk Tab di Card Bawah
        $teamDaily   = (clone $teamQuery)->where('period', 'daily')
                        ->whereDate('start_date', '<=', today())
                        ->whereDate('deadline', '>=', today())
                        ->orderBy('deadline', 'asc')->get();

        $teamMonthly = (clone $teamQuery)->where('period', 'monthly')
                        ->whereMonth('start_date', now()->month)
                        ->orderBy('deadline', 'asc')->get();

        $teamYearly  = (clone $teamQuery)->where('period', 'yearly')
                        ->whereYear('start_date', now()->year)
                        ->orderBy('deadline', 'asc')->get();

        // Data User untuk Dropdown (Hanya untuk Leader/Admin saat buat target tim)
        $teamMembers = [];
        if (in_array($user->role, ['admin', 'leader', 'audit'])) {
            $memberQuery = User::where('is_active', true)->where('role', '!=', 'admin');
            
            if ($user->role == 'leader') {
                $memberQuery->where('division_id', $user->division_id); 
            }
            // Logic filter cabang admin/audit bisa ditambahkan disini
            
            $teamMembers = $memberQuery->get();
        }

        return view('job_targets.index', compact('myDailyTargets', 'teamDaily', 'teamMonthly', 'teamYearly', 'teamMembers'));
    }

    // CREATE (Menangani Form Individual & Team)
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi Dasar
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:individual,team',
        ]);

        // LOGIKA TARGET PRIBADI (Harian)
        if ($request->type == 'individual') {
            JobTarget::create([
                'user_id' => $user->id,
                'creator_id' => $user->id,
                'branch_id' => $user->branch_id,
                'division_id' => $user->division_id,
                'title' => $request->title,
                'type' => 'individual',
                'period' => 'daily',
                'status' => 'pending',
                'start_date' => today(),
                'deadline' => today(),
            ]);
            return back()->with('success', 'Target harian pribadi ditambahkan.');
        }

        // LOGIKA TARGET TIM (Hanya Leader/Admin)
        if ($request->type == 'team') {
            if (!in_array($user->role, ['admin', 'leader', 'audit'])) {
                abort(403, 'Anda tidak berhak membuat target tim.');
            }

            $request->validate([
                'user_id' => 'required|exists:users,id', 
                'period' => 'required|in:daily,monthly,yearly',
                'deadline' => 'required|date',
            ]);

            $targetUser = User::find($request->user_id);

            JobTarget::create([
                'user_id' => $request->user_id,
                'creator_id' => $user->id,
                'branch_id' => $targetUser->branch_id,
                'division_id' => $targetUser->division_id,
                'title' => $request->title,
                'description' => $request->description,
                'type' => 'team',
                'period' => $request->period,
                'status' => 'pending',
                'start_date' => $request->start_date ?? today(),
                'deadline' => $request->deadline,
                'priority' => $request->priority ?? 'medium',
            ]);

            return back()->with('success', 'Target tim berhasil dibuat.');
        }
    }

    // TOGGLE STATUS (Selesai/Belum)
    public function toggleStatus($id)
    {
        $user = Auth::user();
        $target = JobTarget::findOrFail($id);

        // CEK HAK AKSES
        if ($target->type == 'individual') {
            if ($target->user_id != $user->id) abort(403);
        } else {
            // Tim: Hanya Leader/Admin/Creator yang bisa toggle
            if (!in_array($user->role, ['admin', 'leader', 'audit']) && $target->creator_id != $user->id) {
                return back()->with('error', 'Target tim hanya bisa diselesaikan oleh Leader/Admin.');
            }
        }

        // LAKUKAN TOGGLE
        if ($target->status == 'completed') {
            $target->status = 'pending';
            $target->progress = 0;
            $target->completed_at = null;
        } else {
            $target->status = 'completed';
            $target->progress = 100;
            $target->completed_at = now();
        }
        
        $target->save();

        return back()->with('success', 'Status target diperbarui.');
    }

    // DESTROY (Hapus Target)
    public function destroy($id)
    {
        $user = Auth::user();
        $target = JobTarget::findOrFail($id);

        if ($target->type == 'individual') {
            if ($target->user_id != $user->id) abort(403);
        } else {
            if (!in_array($user->role, ['admin', 'leader', 'audit'])) abort(403);
        }

        $target->delete();
        return back()->with('success', 'Target dihapus.');
    }
}