<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Mulai Query dengan relasi User dan Branch/Division (untuk performa)
        $query = Attendance::with(['user.branch', 'user.division']);

        // 1. Filter Berdasarkan Tanggal (Default: Hari ini jika kosong, atau semua)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        // 2. Filter Berdasarkan Status Kehadiran (Hadir, Sakit, Izin, Alpha, dll)
        if ($request->filled('presence_status')) {
            $query->where('presence_status', $request->presence_status);
        }

        // 3. Filter Search Nama User
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // 4. Urutkan dari yang terbaru
        $attendances = $query->latest()->paginate(15)->withQueryString();

        return view('admin.attendance.index', compact('attendances'));
    }
}