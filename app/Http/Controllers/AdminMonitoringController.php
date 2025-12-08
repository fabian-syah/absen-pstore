<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminMonitoringController extends Controller
{
    public function dailyAttendance()
    {
        $today = Carbon::today();

        // Ambil data absensi hari ini
        // Include relasi user, branch, dan scanner (security)
        $attendances = Attendance::with(['user', 'branch', 'scanner'])
            ->whereDate('check_in_time', $today)
            ->orderBy('check_in_time', 'desc')
            ->get();

        return view('admin.monitoring.daily_attendance', compact('attendances'));
    }
}