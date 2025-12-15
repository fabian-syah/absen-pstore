<?php

namespace App\Http\Controllers;

use App\Models\JobTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobTargetController extends Controller
{
    /**
     * Menampilkan Form Buat Target
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchMembers = [];

        // LOGIKA FILTER MEMBER:
        // Hanya Leader yang boleh melihat daftar anggota tim (sesuai cabang yang dipegang)
        if ($user->role == 'leader' && $user->branch_id) {
            $branchMembers = User::where('branch_id', $user->branch_id)
                ->where('id', '!=', $user->id) // Exclude diri sendiri (opsional, karena diri sendiri ada opsi terpisah)
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
        }

        // Admin, Audit, Staff -> $branchMembers tetap kosong array []
        // Sehingga di View dropdown tidak akan muncul.

        return view('job_targets.create', compact('branchMembers'));
    }

    /**
     * Menyimpan Data Target Baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi Dasar
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string',
            'star_level' => 'nullable|integer',
            'period_type' => 'required|in:daily,monthly,yearly',
        ]);

        // Tentukan User ID (Siapa pemilik target ini?)
        // Default: Diri Sendiri
        $targetUserId = $user->id; 
        $branchId = $user->branch_id;

        // JIKA LEADER memilih orang lain (Assign To)
        if ($user->role == 'leader' && $request->has('assign_user_id')) {
            $targetUserId = $request->assign_user_id;
            
            // Ambil data user yang ditugaskan untuk memastikan branch_id sinkron
            $assignedUser = User::find($targetUserId);
            if($assignedUser) {
                $branchId = $assignedUser->branch_id;
            }
        }

        // Setup Tanggal Deadline
        $startDate = now();
        $deadline = now();

        if ($request->period_type == 'daily') {
            $startDate = $request->daily_start;
            $deadline = $request->daily_end;
        } elseif ($request->period_type == 'monthly') {
            $startDate = $request->monthly_start . '-01'; // Awal bulan
            $deadline = \Carbon\Carbon::parse($request->monthly_end)->endOfMonth(); // Akhir bulan
        } elseif ($request->period_type == 'yearly') {
            $startDate = $request->yearly_start . '-01-01';
            $deadline = $request->yearly_end . '-12-31';
        }

        // Simpan Data
        JobTarget::create([
            'user_id' => $targetUserId,
            'branch_id' => $branchId, // Otomatis ikut cabang user yg dituju
            'created_by' => $user->id, // Track siapa pembuatnya (Leader/Diri Sendiri)
            'type' => $request->type, // personal_target, team_target, dll
            'title' => $request->title,
            'description' => $request->description,
            'star_level' => $request->input('star_level', 1), // Default level 1
            'period' => $request->period_type,
            'start_date' => $startDate,
            'deadline' => $deadline,
            'status' => 'pending'
        ]);

        // Redirect Logic
        // Jika request datang dari halaman detail cabang, kembalikan ke sana
        if ($request->has('redirect_to_branch')) {
            return redirect()->route('branch-targets.show', $request->redirect_to_branch)
                ->with('success', 'Target berhasil ditambahkan.');
        }

        return redirect()->route('job-targets.index')->with('success', 'Target berhasil dibuat.');
    }

    /**
     * Update data (Edit Judul/Deskripsi)
     */
    public function update(Request $request, $id)
    {
        $target = JobTarget::findOrFail($id);
        
        // Validasi edit (bisa ditambahkan cek permission di sini)
        
        $target->update([
            'title' => $request->title,
            'description' => $request->description,
            'star_level' => $request->input('star_level', $target->star_level),
            // Tambahkan field lain jika perlu diedit
        ]);

        return redirect()->route('job-targets.index')->with('success', 'Data berhasil diperbarui.');
    }
    
    // ... Method update-outcome (Status) bisa ditambahkan di sini
}