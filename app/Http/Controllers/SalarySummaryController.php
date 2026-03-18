<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SalarySummaryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tentukan Tahun & Cabang
        $year = $request->input('year', date('Y'));
        $branchId = $request->input('branch_id');
        $user = Auth::user();

        // [NEW] 1.5 Tentukan Cabang yang Bisa Diakses
        $myBranchIds = [];
        if (in_array($user->role, ['admin', 'admin_gaji', 'owner'])) {
            $branches = \App\Models\Branch::orderBy('name')->get();
            $myBranchIds = $branches->pluck('id')->toArray();
        } else {
            $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if ($user->branch_id)
                $myBranchIds[] = $user->branch_id;
            $myBranchIds = array_filter(array_unique($myBranchIds));
            $branches = \App\Models\Branch::whereIn('id', $myBranchIds)->orderBy('name')->get();
        }

        // 2. Tentukan User ID yang mau dilihat
        // Jika Admin/Audit/Leader: Bisa pilih user via request 'user_id'
        if (in_array($user->role, ['admin', 'admin_gaji', 'owner', 'audit', 'leader'])) {
            $userId = $request->input('user_id'); // Bisa NULL (untuk lihat semua)

            $usersQuery = User::where('is_active', true);
            if ($branchId) {
                $usersQuery->where('branch_id', $branchId);
            } elseif (!in_array($user->role, ['admin', 'admin_gaji', 'owner'])) {
                $usersQuery->whereIn('branch_id', $myBranchIds);
            }
            $users = $usersQuery->orderBy('name', 'asc')->get();
        } else {
            $userId = Auth::id(); // User biasa hanya bisa lihat sendiri
            $users = [];
            $branches = [];
        }

        $targetUser = $userId ? User::find($userId) : null;

        // 3. Loop 12 Bulan untuk menyusun Data
        $summary = [];
        $totalAnnual = 0;

        for ($m = 1; $m <= 12; $m++) {
            // Format 2 Digit Bulan (01, 02, dst)
            $monthStr = sprintf('%02d', $m);

            // LOGIKA PERIODE CUTOFF (26 Bulan Lalu - 25 Bulan Ini)
            $startDate = Carbon::create($year, $m, 26)->subMonth();
            $endDate = Carbon::create($year, $m, 25);

            // Ambil Data Gaji dari Database
            if ($userId) {
                // Spesifik User
                $salary = Salary::where('user_id', $userId)
                    ->where('month', $monthStr)
                    ->where('year', $year)
                    ->first();
                $amount = $salary ? $salary->total_amount : 0;
                $status = $salary ? 'Dibayarkan' : 'Belum Ada Data';
                $salaryData = $salary;

                $bonusRecord = \App\Models\Bonus::where('user_id', $userId)
                    ->where('month', $monthStr)
                    ->where('year', $year)
                    ->first();

                $bonusAmount = $bonusRecord ? $bonusRecord->bonus_amount : 0;
                $thrAmount = $bonusRecord ? $bonusRecord->thr_amount : 0;

            } else {
                // Semua User / Filter Cabang: Sum Total Amount
                $salaryQuery = Salary::where('month', $monthStr)->where('year', $year);
                $bonusQuery = \App\Models\Bonus::where('month', $monthStr)->where('year', $year);

                // Filter Cabang jika ada
                if ($branchId) {
                    $salaryQuery->whereHas('user', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                    $bonusQuery->whereHas('user', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                } elseif (!empty($myBranchIds) && !in_array($user->role, ['admin', 'admin_gaji', 'owner'])) {
                    $salaryQuery->whereHas('user', function ($q) use ($myBranchIds) {
                        $q->whereIn('branch_id', $myBranchIds);
                    });
                    $bonusQuery->whereHas('user', function ($q) use ($myBranchIds) {
                        $q->whereIn('branch_id', $myBranchIds);
                    });
                }

                $amount = $salaryQuery->sum('total_amount');
                $bonusAmount = $bonusQuery->sum('bonus_amount');
                $thrAmount = $bonusQuery->sum('thr_amount');

                $status = ($amount > 0) ? 'Total Kumulatif' : 'Belum Ada Data';
                $salaryData = null;
            }

            $totalAnnual += ($amount + $bonusAmount + $thrAmount);

            $summary[] = [
                'month_num' => $m,
                'month_name' => Carbon::create()->month($m)->locale('id')->isoFormat('MMMM'),
                'period_string' => $startDate->isoFormat('D MMMM') . ' - ' . $endDate->isoFormat('D MMMM Y'),
                'data' => $salaryData,
                'amount' => $amount,
                'bonus_amount' => $bonusAmount,
                'thr_amount' => $thrAmount,
                'status' => $status
            ];
        }

        return view('salary-summary.index', compact('summary', 'year', 'users', 'userId', 'targetUser', 'totalAnnual', 'branches', 'branchId'));
    }
}