<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\JobTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Exports\BranchAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && in_array(Auth::user()->role, ['admin', 'audit', 'leader'])) {
                return $next($request);
            }
            return abort(403, 'Hanya Admin, Audit, atau Leader yang boleh mengakses halaman ini.');
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Branch::query();

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('id', $user->branch_id);
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) {
                $allowedBranchIds[] = $user->branch_id;
            }
            $allowedBranchIds = array_unique($allowedBranchIds);
            $query->whereIn('id', $allowedBranchIds);
        }

        // Hide special administrative branch from general listing
        $query->where('name', '!=', 'Cabang User Non Karyawan');

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        $branches = $query->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('branch.branch_index', compact('branches'));
    }

    public function show(Branch $branch)
    {
        $user = Auth::user();

        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id)
                abort(403, 'Akses Ditolak.');
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id)
                $allowedBranchIds[] = $user->branch_id;

            if (!in_array($branch->id, $allowedBranchIds))
                abort(403, 'Akses Ditolak. Cabang ini bukan wilayah Anda.');
        }

        // Mengambil data karyawan (termasuk last_login_at secara otomatis dari model User)
        $employees = User::with([
            'division',
            'attendances' => function ($q) {
                $q->whereDate('check_in_time', now());
            }
        ])
            ->where('branch_id', $branch->id)
            ->whereNotIn('role', ['admin', 'super_admin', 'admin_gaji'])
            ->latest()
            ->paginate(10);

        $totalEmployees = User::where('branch_id', $branch->id)->count();

        $assignedAudits = User::where('role', 'audit')
            ->where('is_active', true)
            ->whereHas('branches', function ($q) use ($branch) {
                $q->where('branches.id', $branch->id);
            })
            ->get();

        // --- TAMBAHAN: DATA TARGET & PENCAPAIAN CABANG (TIM) ---

        // 1. Target Tim Aktif (On Going)
        $branchTargets = JobTarget::where('branch_id', $branch->id)
            ->where('type', 'team_target')
            ->where('status', '!=', 'completed')
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Pencapaian Tim & History Target Selesai
        $branchAchievements = JobTarget::where('branch_id', $branch->id)
            ->where(function ($q) {
                $q->where('type', 'team_achievement')
                    ->orWhere(function ($subQ) {
                        $subQ->where('type', 'team_target')
                            ->where('status', 'completed');
                    });
            })
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('branch.branch_show', compact('branch', 'employees', 'totalEmployees', 'assignedAudits', 'branchTargets', 'branchAchievements'));
    }

    public function create()
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403, 'Anda tidak memiliki akses untuk menambah cabang.');
        }
        return view('branch.branch_create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null)
            abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius' => 'nullable|integer|min:10|max:5000',
            'kemenag_city_id' => 'nullable|string',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader']))
            abort(403, 'Anda tidak memiliki akses edit.');

        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id)
                abort(403);
        }

        return view('branch.branch_edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader']))
            abort(403);
        if ($user->role == 'admin' && $user->branch_id != null && $branch->id != $user->branch_id)
            abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius' => 'nullable|integer|min:10|max:5000',
            'kemenag_city_id' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null)
            abort(403);

        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada user yang terhubung.');
        }
    }
    public function exportBranchExcel(Request $request, Branch $branch)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            abort(403);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $branchNameSlug = Str::slug($branch->name);
        $fileName = "Laporan_Absensi_Cabang_{$branchNameSlug}_{$month}-{$year}.xlsx";

        return Excel::download(new BranchAttendanceExport($branch->id, $month, $year), $fileName);
    }

    public function exportBranchPdf(Request $request, Branch $branch)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            abort(403);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $dateObj = Carbon::createFromDate($year, $month, 1);
        $startDate = $dateObj->copy()->subMonth()->day(26)->startOfDay();
        $endDate = $dateObj->copy()->day(25)->endOfDay();
        $monthName = $startDate->translatedFormat('d F Y') . ' - ' . $endDate->translatedFormat('d F Y');

        $employees = User::where('branch_id', $branch->id)
            ->where('role', '!=', 'admin')
            ->orderBy('name', 'asc')
            ->get();

        $branchTimezone = $branch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($branchTimezone)->startOfDay();
        $limitDate = ($endDate->gt($todayInBranch)) ? $todayInBranch : $endDate;
        $period = \Carbon\CarbonPeriod::create($startDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

        $data = [];
        foreach ($employees as $employee) {
            $attendances = \App\Models\Attendance::where('user_id', $employee->id)
                ->whereBetween('check_in_time', [$startDate->copy()->subDay(), $endDate->copy()->addDay()])
                ->get();
                
            $leaves = \App\Models\LeaveRequest::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                        ->where(function ($q) use ($startDate) {
                            $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                        });
                })->get();

            $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'libur' => 0, 'telat' => 0, 'total_jam' => 0];
            
            foreach ($period as $date) {
                $currentDateStr = $date->format('Y-m-d');
                $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                    if ($a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha') return false;
                    if ($a->status === 'rejected') return false;
                    return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') === $currentDateStr;
                })->sortBy(function($a) {
                    return strtolower($a->presence_status) === 'masuk' ? 0 : 1;
                })->first();

                $leave = $leaves->filter(function ($l) use ($date, $branchTimezone, $att) {
                    if ($l->type === 'telat' && !$att) return false;
                    $lStart = Carbon::parse($l->start_date, $branchTimezone)->startOfDay();
                    $lEnd = Carbon::parse($l->end_date ?? $l->start_date, $branchTimezone)->endOfDay();
                    return $date->between($lStart, $lEnd);
                })->first();

                if ($att) {
                    $status = strtolower(trim($att->presence_status));
                    $isHadir = in_array($status, ['hadir', 'tepat waktu', 'masuk', 'wfh', 'work from home', 'dinas luar', 'kunjungan rutin', 'lembur']);
                    if ($isHadir || empty($status)) $summary['hadir']++;
                    elseif ($status == 'sakit') $summary['sakit']++;
                    elseif (in_array($status, ['izin', 'cuti'])) $summary['izin']++;
                    elseif ($status == 'libur') $summary['libur']++;

                    if ($att->is_late_checkin) $summary['telat']++;
                    if ($att->check_in_time && $att->check_out_time) {
                        $summary['total_jam'] += Carbon::parse($att->check_in_time)->diffInHours(Carbon::parse($att->check_out_time));
                    }
                } elseif ($leave) {
                    if ($leave->type == 'sakit') $summary['sakit']++;
                    elseif (in_array($leave->type, ['izin', 'cuti'])) $summary['izin']++;
                    elseif ($leave->type == 'libur') $summary['libur']++;
                    elseif (in_array($leave->type, ['wfh', 'dinas', 'telat'])) $summary['hadir']++;
                } else {
                    $summary['alfa']++;
                }
            }
            $data[] = ['user' => $employee, 'summary' => $summary];
        }

        $pdf = Pdf::loadView('branch.export_pdf', [
            'branch' => $branch,
            'monthName' => $monthName,
            'data' => $data,
            'generatedBy' => $user
        ]);

        $sanitizedBranchName = Str::slug($branch->name);
        return $pdf->download("Laporan_Absensi_{$sanitizedBranchName}_{$month}-{$year}.pdf");
    }

    /**
     * Halaman Khusus Admin: Pengaturan Lokasi, Koordinat GPS, dan Geofencing Cabang
     */
    public function geofenceIndex(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Halaman Pengaturan Lokasi hanya dapat diakses oleh Admin.');
        }

        $branches = Branch::where('name', '!=', 'Cabang User Non Karyawan')
            ->orderBy('name', 'asc')
            ->get();

        $pusatBranches = $branches->filter(fn($b) => $b->is_pusat)->values();
        $cabangBranches = $branches->filter(fn($b) => !$b->is_pusat)->values();

        $totalBranches = $branches->count();
        $activeGeofenceCount = $branches->filter(fn($b) => $b->hasGeofence())->count();
        $missingGeofenceCount = $totalBranches - $activeGeofenceCount;
        $pusatCount = $pusatBranches->count();
        $cabangCount = $cabangBranches->count();
        $pusatActiveCount = $pusatBranches->filter(fn($b) => $b->hasGeofence())->count();
        $cabangActiveCount = $cabangBranches->filter(fn($b) => $b->hasGeofence())->count();

        return view('branch.geofence_setting', compact(
            'branches',
            'pusatBranches',
            'cabangBranches',
            'totalBranches',
            'activeGeofenceCount',
            'missingGeofenceCount',
            'pusatCount',
            'cabangCount',
            'pusatActiveCount',
            'cabangActiveCount'
        ));
    }

    /**
     * Update Titik Koordinat & Radius untuk Cabang Tertentu (Single Update)
     */
    public function updateGeofence(Request $request, Branch $branch)
    {
        if (Auth::user()->role !== 'admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            abort(403);
        }

        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:0|max:5000',
            'address' => 'nullable|string|max:500',
        ]);

        $radiusVal = $request->filled('radius') ? (int)$request->radius : null;
        if ($radiusVal === 0) {
            $radiusVal = null; // Nilai 0 atau kosong berarti geofence dinonaktifkan
        }

        $branch->update([
            'latitude' => $request->filled('latitude') ? (float)$request->latitude : null,
            'longitude' => $request->filled('longitude') ? (float)$request->longitude : null,
            'radius' => $radiusVal,
            'address' => $request->filled('address') ? $request->address : $branch->address,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lokasi dan radius ' . $branch->name . ' berhasil disimpan.',
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'latitude' => $branch->latitude,
                    'longitude' => $branch->longitude,
                    'radius' => $branch->radius,
                    'has_geofence' => $branch->hasGeofence(),
                ]
            ]);
        }

        return back()->with('success', 'Titik lokasi dan radius ' . $branch->name . ' berhasil diperbarui.');
    }

    /**
     * Batch Update Radius Geofence (Semua, Hanya Pusat, atau Hanya Cabang)
     */
    public function batchUpdateRadius(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'target' => 'required|in:all,pusat,cabang',
            'radius' => 'required|integer|min:10|max:5000',
        ]);

        $radius = (int) $request->radius;
        $target = $request->target;

        $query = Branch::where('name', '!=', 'Cabang User Non Karyawan');

        if ($target === 'pusat') {
            $pusatNames = Branch::pusatList();
            $query->where(function($q) use ($pusatNames) {
                $q->whereIn('name', $pusatNames)
                  ->orWhere('name', 'LIKE', '%pusat%')
                  ->orWhere('name', 'LIKE', '%head office%');
            });
            $targetLabel = "Unit Pusat";
        } elseif ($target === 'cabang') {
            $pusatNames = Branch::pusatList();
            $query->whereNotIn('name', $pusatNames)
                  ->where('name', 'NOT LIKE', '%pusat%')
                  ->where('name', 'NOT LIKE', '%head office%');
            $targetLabel = "Toko Cabang Daerah";
        } else {
            $targetLabel = "Semua Cabang & Unit";
        }

        $affected = $query->update(['radius' => $radius]);

        return back()->with('success', "Berhasil menerapkan radius {$radius} meter untuk {$affected} {$targetLabel}.");
    }

    /**
     * Geocode Alamat ke Latitude & Longitude (Server-side proxy untuk Nominatim OSM)
     */
    public function geocodeAddress(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $query = trim($request->input('q', ''));
        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak boleh kosong.'], 400);
        }

        // 1. Cek jika input adalah Link Google Maps (termasuk shortlink maps.app.goo.gl atau goo.gl/maps)
        if (str_contains($query, 'maps.app.goo.gl') || str_contains($query, 'goo.gl/maps')) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $query);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                curl_exec($ch);
                $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                if ($finalUrl) {
                    $query = $finalUrl;
                }
            } catch (\Exception $e) {
                \Log::warning('Error resolving Google Maps shortlink: ' . $e->getMessage());
            }
        }

        // Pola A: !3d<lat>!4d<lng> (Presisi pin Google Maps)
        if (preg_match('/!3d([-+]?[0-9]{1,2}\.[0-9]+)!4d([-+]?[0-9]{1,3}\.[0-9]+)/', $query, $matches)) {
            return response()->json([
                'success' => true,
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2],
                'display_name' => 'Lokasi spesifik dari Google Maps',
            ]);
        }
        // Pola B: @<lat>,<lng>
        if (preg_match('/@([-+]?[0-9]{1,2}\.[0-9]+),([-+]?[0-9]{1,3}\.[0-9]+)/', $query, $matches)) {
            return response()->json([
                'success' => true,
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2],
                'display_name' => 'Titik lokasi dari Google Maps',
            ]);
        }
        // Pola C: ?q=<lat>,<lng> atau &ll=<lat>,<lng>
        if (preg_match('/[?&](?:q|ll|destination)=([-+]?[0-9]{1,2}\.[0-9]+),([-+]?[0-9]{1,3}\.[0-9]+)/', $query, $matches)) {
            return response()->json([
                'success' => true,
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2],
                'display_name' => 'Koordinat dari link Google Maps',
            ]);
        }

        // 2a. Cek format koordinat DMS (Derajat Menit Detik, contoh: 6°16'38.3"S 106°51'18.6"E)
        if (preg_match('/([0-9]{1,2})\s*[\x{00B0}\°]\s*([0-9]{1,2})\s*[\'′]\s*([0-9]{1,2}(?:\.[0-9]+)?)\s*["″]?\s*([NSns])\s*,?\s*([0-9]{1,3})\s*[\x{00B0}\°]\s*([0-9]{1,2})\s*[\'′]\s*([0-9]{1,2}(?:\.[0-9]+)?)\s*["″]?\s*([EWew])/u', $query, $dms)) {
            $lat = (float)$dms[1] + ((float)$dms[2] / 60) + ((float)$dms[3] / 3600);
            if (strtoupper($dms[4]) === 'S') $lat = -$lat;

            $lng = (float)$dms[5] + ((float)$dms[6] / 60) + ((float)$dms[7] / 3600);
            if (strtoupper($dms[8]) === 'W') $lng = -$lng;

            return response()->json([
                'success' => true,
                'lat' => round($lat, 7),
                'lng' => round($lng, 7),
                'display_name' => "Koordinat GPS: " . trim($query),
            ]);
        }

        // 2b. Cek apakah format koordinat numerik langsung ("-6.2088, 106.8456")
        if (preg_match('/^[-+]?[0-9]{1,2}(?:\.[0-9]+)?[,\s]+[-+]?[0-9]{1,3}(?:\.[0-9]+)?$/', trim($query))) {
            $parts = preg_split('/[\s,]+/', trim($query));
            if (count($parts) >= 2) {
                $lat = (float)$parts[0];
                $lng = (float)$parts[1];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                    return response()->json([
                        'success' => true,
                        'lat' => $lat,
                        'lng' => $lng,
                        'display_name' => "Koordinat langsung ({$lat}, {$lng})",
                    ]);
                }
            }
        }

        // 3. Progressive Search Strategy untuk Alamat Indonesia (dari Google Maps / input user)
        $searchCandidates = [];

        // Parsing bagian koma alamat Indonesia
        $rawParts = array_map('trim', explode(',', $query));
        $meaningfulParts = [];
        $streetPart = '';
        $cityPart = '';
        $subdistrictParts = [];

        foreach ($rawParts as $idx => $part) {
            // Lewati RT/RW
            if (preg_match('/RT\.?\s*\d+|RW\.?\s*\d+/i', $part)) {
                continue;
            }
            // Lewati jika hanya kode pos atau Indonesia
            if (preg_match('/^\d{5}$/', $part) || strcasecmp($part, 'Indonesia') === 0) {
                continue;
            }

            // Bersihkan label formal provinsi/kota/wilayah
            $cleanedPart = preg_replace('/\b(Daerah Khusus Ibukota|DKI)\b/i', '', $part);
            $cleanedPart = preg_replace('/\b\d{5}\b/', '', $cleanedPart);
            $cleanedPart = trim($cleanedPart);

            if ($idx === 0) {
                // Bersihkan nomor rumah dari nama jalan agar jalan dikenali OpenStreetMap
                $streetPart = trim(preg_replace('/No\.?\s*[\w\/-]+/i', '', $part));
                if (!empty($streetPart)) {
                    $meaningfulParts[] = $streetPart;
                }
            } else {
                // Deteksi Kota/Kabupaten
                if (preg_match('/\b(Kota|Kabupaten|Kab\.)\b/i', $part) ||
                    stripos($part, 'Jakarta') !== false || stripos($part, 'Surabaya') !== false ||
                    stripos($part, 'Bandung') !== false || stripos($part, 'Medan') !== false ||
                    stripos($part, 'Makassar') !== false || stripos($part, 'Semarang') !== false ||
                    stripos($part, 'Palembang') !== false || stripos($part, 'Batam') !== false ||
                    stripos($part, 'Pekanbaru') !== false || stripos($part, 'Malang') !== false ||
                    stripos($part, 'Yogyakarta') !== false || stripos($part, 'Jogja') !== false ||
                    stripos($part, 'Denpasar') !== false || stripos($part, 'Solo') !== false ||
                    stripos($part, 'Surakarta') !== false || stripos($part, 'Bekasi') !== false ||
                    stripos($part, 'Tangerang') !== false || stripos($part, 'Depok') !== false ||
                    stripos($part, 'Bogor') !== false) {
                    $cityPart = trim(preg_replace('/\b(Kota|Kabupaten|Kab\.|Daerah Khusus Ibukota|DKI)\s*/i', '', $cleanedPart));
                }

                $cleanSub = trim(preg_replace('/\b(Kec\.|Kel\.|Kecamatan|Kelurahan)\s*/i', '', $cleanedPart));
                if (!empty($cleanSub)) {
                    $subdistrictParts[] = $cleanSub;
                    $meaningfulParts[] = $cleanSub;
                }
            }
        }

        // Variasi nama jalan (dengan & tanpa kata "Raya", serta tanpa prefix "Jl.")
        $streetVariants = [];
        if (!empty($streetPart)) {
            $streetVariants[] = $streetPart;

            // Variasi tanpa kata "Raya" (misal: "Jl. Raya Bratang Binangun" -> "Jl. Bratang Binangun")
            $withoutRaya = trim(preg_replace('/\bRaya\s*/i', '', $streetPart));
            if (!empty($withoutRaya) && $withoutRaya !== $streetPart) {
                $streetVariants[] = $withoutRaya;
            }

            // Variasi nama inti jalan saja (misal: "Bratang Binangun")
            $coreRoad = trim(preg_replace('/^(?:Jl\.?|Jalan)\s+/i', '', $streetPart));
            if (!empty($coreRoad)) {
                $streetVariants[] = $coreRoad;
                $coreWithoutRaya = trim(preg_replace('/\bRaya\s*/i', '', $coreRoad));
                if (!empty($coreWithoutRaya) && $coreWithoutRaya !== $coreRoad) {
                    $streetVariants[] = $coreWithoutRaya;
                }
            }
        }
        $streetVariants = array_unique(array_filter($streetVariants));

        // Prioritas 1: Alamat bersih berstruktur koma
        if (count($meaningfulParts) >= 2) {
            $searchCandidates[] = implode(', ', $meaningfulParts);
        }

        // Prioritas 2 & 3: Setiap variasi jalan dipadukan dengan wilayah
        foreach ($streetVariants as $st) {
            if (!empty($subdistrictParts) && !empty($cityPart)) {
                $searchCandidates[] = "{$st}, {$subdistrictParts[0]}, {$cityPart}";
                if (count($subdistrictParts) > 1) {
                    $searchCandidates[] = "{$st}, {$subdistrictParts[1]}, {$cityPart}";
                }
            }
            if (!empty($cityPart)) {
                $searchCandidates[] = "{$st}, {$cityPart}";
            }
            if (!empty($subdistrictParts)) {
                $searchCandidates[] = "{$st}, {$subdistrictParts[0]}";
            }
        }

        // Prioritas 4: Wilayah Kelurahan / Kecamatan + Kota jika nama jalan tidak terindeks
        foreach ($subdistrictParts as $sub) {
            if (!empty($cityPart)) {
                $searchCandidates[] = "{$sub}, {$cityPart}";
            }
        }

        // Prioritas 5: Query mentah asli
        $searchCandidates[] = $query;

        // Prioritas 6: Versi single-line tanpa simbol
        $clean = preg_replace('/RT\.?\s*\d+\s*\/?\s*RW\.?\s*\d+/i', '', $query);
        $clean = preg_replace('/No\.?\s*[\w\/-]+/i', '', $clean);
        $clean = preg_replace('/\b\d{5}\b/', '', $clean);
        $clean = preg_replace('/\b(Daerah Khusus Ibukota|DKI)\b/i', '', $clean);
        $clean = preg_replace('/[,\s]+/', ' ', trim($clean));
        if (!empty($clean)) {
            $searchCandidates[] = $clean;
        }

        $searchCandidates = array_unique(array_filter($searchCandidates));

        // Lakukan pencarian bertingkat hingga menemukan hasil
        foreach ($searchCandidates as $candidate) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'AbsensiPStore/1.0 (' . config('app.url', 'https://absenps.com') . ')',
                    'Accept-Language' => 'id,en',
                ])->timeout(6)->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'json',
                    'q' => $candidate,
                    'limit' => 1,
                    'countrycodes' => 'id',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data) && isset($data[0])) {
                        return response()->json([
                            'success' => true,
                            'lat' => (float)$data[0]['lat'],
                            'lng' => (float)$data[0]['lon'],
                            'display_name' => $data[0]['display_name'] ?? $candidate,
                            'matched_query' => $candidate,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Geocode candidate error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Alamat belum cocok di peta. Silakan geser pin biru langsung pada peta.',
        ]);
    }
}