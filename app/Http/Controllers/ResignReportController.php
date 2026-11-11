<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\EmploymentHistory;
use App\Models\AdminGajiUser;
use App\Exports\ResignNonKaryawanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResignReportController extends Controller
{
    /**
     * Helper untuk mengambil data Resign & Non Karyawan
     */
    public static function fetchReportData($startDate = null, $endDate = null, $category = 'all')
    {
        $startDate = $startDate ?: '2026-09-01';
        $endDate = $endDate ?: '2026-10-31';
        $category = $category ?: 'all';

        $results = collect();

        // 1. DATA RESIGN / EX KARYAWAN
        if ($category === 'all' || $category === 'resign') {
            $histories = EmploymentHistory::with(['user', 'previousBranch', 'branch', 'division'])
                ->where('type', 'resign')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('event_date', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->whereNull('event_date')
                              ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                      });
                })
                ->get();

            $handledUserIds = [];

            foreach ($histories as $h) {
                $user = $h->user;
                if (!$user) continue;
                $handledUserIds[] = $user->id;

                $cabang = $h->previousBranch?->name 
                    ?? $h->branch?->name 
                    ?? $user->branch?->name 
                    ?? '-';
                
                $divisi = $h->division?->name 
                    ?? $user->division?->name 
                    ?? strtoupper(str_replace('_', ' ', $user->role ?? '-'));

                $date = $h->event_date 
                    ? Carbon::parse($h->event_date)->format('Y-m-d') 
                    : Carbon::parse($h->created_at)->format('Y-m-d');

                $results->push([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'login_id' => $user->login_id ?? '-',
                    'category' => 'Resign / EX Karyawan',
                    'branch' => $cabang,
                    'division' => $divisi,
                    'role' => strtoupper(str_replace('_', ' ', $user->role ?? '-')),
                    'date' => $date,
                    'notes' => $h->description ?: 'Resign / Nonaktif'
                ]);
            }

            // User yang dinonaktifkan di rentang waktu tsb tapi belum tercatat di employment_histories
            $exBranch = Branch::where('name', 'like', '%EX Karyawan%')->first();
            $otherInactive = User::with(['branch', 'division'])
                ->where(function ($q) use ($exBranch) {
                    $q->where('is_active', false);
                    if ($exBranch) {
                        $q->orWhere('branch_id', $exBranch->id);
                    }
                })
                ->whereNotIn('id', $handledUserIds)
                ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();

            foreach ($otherInactive as $u) {
                $results->push([
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'login_id' => $u->login_id ?? '-',
                    'category' => 'Resign / EX Karyawan',
                    'branch' => $u->branch?->name ?? 'EX Karyawan',
                    'division' => $u->division?->name ?? '-',
                    'role' => strtoupper(str_replace('_', ' ', $u->role ?? '-')),
                    'date' => Carbon::parse($u->updated_at)->format('Y-m-d'),
                    'notes' => 'Status Nonaktif (EX Karyawan)'
                ]);
            }
        }

        // 2. DATA NON KARYAWAN
        if ($category === 'all' || $category === 'non_karyawan') {
            $nonKaryawanBranch = Branch::where('name', 'like', '%Non Karyawan%')->first();

            $nonKaryawanUsers = User::with(['branch', 'division'])
                ->where(function ($q) use ($nonKaryawanBranch) {
                    if ($nonKaryawanBranch) {
                        $q->where('branch_id', $nonKaryawanBranch->id);
                    }
                    $q->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('admin_gaji_users')
                            ->whereColumn('admin_gaji_users.user_id', 'users.id');
                    });
                })
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();

            $handledNkUserIds = $nonKaryawanUsers->pluck('id')->toArray();
            $adminGajiUsers = AdminGajiUser::with('user.division')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->whereNotIn('user_id', $handledNkUserIds)
                ->get();

            foreach ($nonKaryawanUsers as $u) {
                $location = AdminGajiUser::where('user_id', $u->id)->value('location');
                $results->push([
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'login_id' => $u->login_id ?? '-',
                    'category' => 'Non Karyawan',
                    'branch' => $location ?: ($u->branch?->name ?? 'Cabang Non Karyawan'),
                    'division' => $u->division?->name ?? 'Non Karyawan',
                    'role' => strtoupper(str_replace('_', ' ', $u->role ?? '-')),
                    'date' => Carbon::parse($u->created_at)->format('Y-m-d'),
                    'notes' => 'User Non Karyawan Baru'
                ]);
            }

            foreach ($adminGajiUsers as $agu) {
                $u = $agu->user;
                $results->push([
                    'user_id' => $agu->user_id,
                    'name' => $agu->name ?: ($u?->name ?? '-'),
                    'login_id' => $u?->login_id ?? '-',
                    'category' => 'Non Karyawan',
                    'branch' => $agu->location ?: 'Cabang Non Karyawan',
                    'division' => $u?->division?->name ?? 'Non Karyawan',
                    'role' => strtoupper(str_replace('_', ' ', $u?->role ?? '-')),
                    'date' => Carbon::parse($agu->created_at)->format('Y-m-d'),
                    'notes' => 'User Non Karyawan'
                ]);
            }
        }

        return $results->sortByDesc('date')->values();
    }

    /**
     * Download Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', '2026-09-01');
        $endDate = $request->input('end_date', '2026-10-31');
        $category = $request->input('category', 'all');

        $data = self::fetchReportData($startDate, $endDate, $category);

        $catName = match($category) {
            'resign' => 'Resign',
            'non_karyawan' => 'Non_Karyawan',
            default => 'Resign_dan_Non_Karyawan',
        };

        $fileName = "Laporan_{$catName}_{$startDate}_sd_{$endDate}.xlsx";

        return Excel::download(new ResignNonKaryawanExport($startDate, $endDate, $category, $data), $fileName);
    }

    /**
     * Download PDF (.pdf)
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', '2026-09-01');
        $endDate = $request->input('end_date', '2026-10-31');
        $category = $request->input('category', 'all');

        $data = self::fetchReportData($startDate, $endDate, $category);
        $generatedBy = Auth::user();

        $catLabel = match($category) {
            'resign' => 'Karyawan Resign / EX Karyawan',
            'non_karyawan' => 'User Non Karyawan',
            default => 'Karyawan Resign & Non Karyawan',
        };

        $periodLabel = Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y');

        $pdf = Pdf::loadView('reports.resign_non_karyawan_pdf', [
            'data' => $data,
            'catLabel' => $catLabel,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedBy' => $generatedBy,
        ])->setPaper('a4', 'landscape');

        $catName = match($category) {
            'resign' => 'Resign',
            'non_karyawan' => 'Non_Karyawan',
            default => 'Resign_dan_Non_Karyawan',
        };

        $fileName = "Laporan_{$catName}_{$startDate}_sd_{$endDate}.pdf";

        return $pdf->download($fileName);
    }
}
