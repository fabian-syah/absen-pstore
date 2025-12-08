<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminMonitoringController extends Controller
{
    public function dailyAttendance(Request $request)
    {
        // 1. Ambil Input Filter (Default ke Bulan & Tahun Saat Ini)
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // 2. Query: Siapa yang BELUM PULANG (Sesi Aktif)
        // Mengambil data check_out_time NULL, diurutkan dari yang paling baru check-in
        // Ini mencakup hari ini dan hari sebelumnya jika lupa checkout
        $pendingCheckouts = Attendance::with(['user', 'branch'])
            ->whereNull('check_out_time')
            ->where('status', '!=', 'alpha') // Pastikan bukan status Alpha
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 3. Query: Riwayat Absensi Sesuai Filter Bulan & Tahun
        $attendanceHistory = Attendance::with(['user', 'branch', 'scanner'])
            ->whereYear('check_in_time', $year)
            ->whereMonth('check_in_time', $month)
            ->orderBy('check_in_time', 'desc')
            ->get();

        return view('admin.monitoring.daily_attendance', compact(
            'pendingCheckouts', 
            'attendanceHistory', 
            'month', 
            'year'
        ));
    }
}