<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSummaryController extends Controller
{
   public function index(Request $request)
    {
        $currentUser = Auth::user();
        $selectedYear = $request->get('year', date('Y'));
        $targetUserId = $request->get('user_id');

        // --- 1. SEARCH & SCOPE KARYAWAN ---
        $employees = collect([]);
        $targetUser = $currentUser; // Default ke diri sendiri

        // Tentukan Role apa saja yang boleh mengintip/melihat data orang lain
        $allowedRoles = ['admin', 'audit', 'leader', 'admin_gaji']; 

        if (in_array($currentUser->role, $allowedRoles)) {
            
            // A. Logic Mengisi Dropdown List (Filter Karyawan)
            if ($currentUser->role === 'audit' || $currentUser->role === 'leader') {
                // Audit & Leader hanya lihat user di cabang mereka
                $handledBranchIds = $currentUser->branches ? $currentUser->branches->pluck('id')->toArray() : [];
                // Tambahkan branch_id utama user jika ada
                if($currentUser->branch_id) {
                    $handledBranchIds[] = $currentUser->branch_id;
                }
                
                $employees = User::whereIn('branch_id', array_unique($handledBranchIds))
                                 ->orderBy('name', 'asc')
                                 ->get();
            } else {
                // Admin & Admin Gaji lihat semua
                $employees = User::orderBy('name', 'asc')->get();
            }

            // B. Logic Menentukan Target User (User yang dipilih)
            if ($targetUserId) {
                // Cek apakah user target ada di dalam list employees yang diizinkan
                $foundUser = $employees->where('id', $targetUserId)->first();
                
                if ($foundUser) {
                    $targetUser = $foundUser;
                } else {
                    // Fallback: Jika admin scan barcode/klik link langsung tapi user tidak muncul di dropdown (misal beda cabang tapi admin pusat)
                    // Kita coba cari direct ke DB jika role adalah admin/admin_gaji (akses penuh)
                    if (in_array($currentUser->role, ['admin', 'admin_gaji'])) {
                        $directFind = User::find($targetUserId);
                        if($directFind) $targetUser = $directFind;
                    }
                }
            }
        }
    }
}