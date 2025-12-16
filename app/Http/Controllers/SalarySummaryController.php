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
        // 1. Tentukan Tahun (Default Tahun Ini)
        $year = $request->input('year', date('Y'));
        
        // 2. Tentukan User ID yang mau dilihat
        // Jika Admin/Admin Gaji: Bisa pilih user via request 'user_id'
        // Jika User Biasa: Paksa lihat punya sendiri
        if (in_array(Auth::user()->role, ['admin', 'admin_gaji'])) {
            $userId = $request->input('user_id', Auth::id());
            $users = User::where('is_active', true)->orderBy('name', 'asc')->get(); // Untuk dropdown
        } else {
            $userId = Auth::id();
            $users = [];
        }

        $targetUser = User::find($userId);

        // 3. Loop 12 Bulan untuk menyusun Data
        $summary = [];
        $totalAnnual = 0;

        for ($m = 1; $m <= 12; $m++) {
            // Format 2 Digit Bulan (01, 02, dst)
            $monthStr = sprintf('%02d', $m);

            // LOGIKA PERIODE CUTOFF (26 Bulan Lalu - 25 Bulan Ini)
            // Contoh Bulan 12 (Desember): 26 Nov - 25 Des
            $startDate = Carbon::create($year, $m, 26)->subMonth(); 
            $endDate = Carbon::create($year, $m, 25);

            // Ambil Data Gaji dari Database
            $salary = Salary::where('user_id', $userId)
                ->where('month', $monthStr)
                ->where('year', $year)
                ->first();

            $amount = $salary ? $salary->total_amount : 0;
            $totalAnnual += $amount;

            $summary[] = [
                'month_num' => $m,
                'month_name' => Carbon::create()->month($m)->locale('id')->isoFormat('MMMM'),
                'period_string' => $startDate->isoFormat('D MMMM') . ' - ' . $endDate->isoFormat('D MMMM Y'),
                'data' => $salary, // Objek Salary (bisa null)
                'amount' => $amount,
                'status' => $salary ? 'Dibayarkan' : 'Belum Ada Data'
            ];
        }

        return view('salary-summary.index', compact('summary', 'year', 'users', 'userId', 'targetUser', 'totalAnnual'));
    }
}