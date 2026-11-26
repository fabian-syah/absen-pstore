<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hitung ringkasan Alpha dan Terlambat bulanan (cutoff 26-25),
     * sinkron dengan logika Riwayat Absensi di TeamController & AttendanceHistoryController.
     */
    public static function summarizeMonthlyAttendance(User $user, int $month, int $year): array
    {
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $monthStartDate = Carbon::createFromDate($year, $month, 1, $branchTimezone)->subMonth()->day(26)->startOfDay();
        $monthEndDate = Carbon::createFromDate($year, $month, 1, $branchTimezone)->day(25)->endOfDay();
        $now = Carbon::now($branchTimezone);
        $todayInBranch = $now->copy()->startOfDay();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('check_in_time', [
                $monthStartDate->copy()->subDays(2)->startOfDay(),
                $monthEndDate->copy()->addDays(2)->endOfDay(),
            ])->get();

        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('start_date', '<=', $monthEndDate->format('Y-m-d'))
            ->where(function ($q) use ($monthStartDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStartDate->format('Y-m-d'));
            })->get();

        $isCurrentPeriod = $todayInBranch->between($monthStartDate, $monthEndDate);
        if ($isCurrentPeriod) {
            $hasActivityToday = $attendances->first(fn($a) => Carbon::parse($a->check_in_time)->timezone($branchTimezone)->isToday())
                || $leaves->first(fn($l) => Carbon::parse($l->start_date, $branchTimezone)->isToday());

            $limitDate = (!$hasActivityToday && $now->hour < 4)
                ? $todayInBranch->copy()->subDay()
                : $todayInBranch;
        } else {
            $limitDate = $monthEndDate->gt($todayInBranch) ? $todayInBranch : $monthEndDate;
        }

        if ($limitDate->lt($monthStartDate->copy()->startOfDay())) {
            return [
                'alphaCount' => 0,
                'alphaDates' => [],
                'lateCount' => 0,
                'lateDates' => [],
            ];
        }

        $lateDates = [];
        $lateCount = $attendances->filter(function ($a) use ($monthStartDate, $limitDate, $branchTimezone, &$lateDates) {
            if ($a->attendance_type === 'system' && strtolower($a->presence_status ?? '') === 'alpha') {
                return false;
            }
            if ($a->status === 'rejected') {
                return false;
            }
            $attFullDate = Carbon::parse($a->check_in_time)->timezone($branchTimezone);
            $attDate = $attFullDate->copy()->startOfDay();
            $isInRange = $attDate->between($monthStartDate, $limitDate);
            $isTelat = $a->is_late_checkin || $a->status === 'late' || str_contains(strtolower($a->presence_status ?? ''), 'telat');

            if ($isInRange && $isTelat) {
                $lateDates[] = $attFullDate->format('d/m');
                return true;
            }
            return false;
        })->count();

        $alphaCount = 0;
        $alphaDates = [];
        $period = CarbonPeriod::create($monthStartDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                if ($a->attendance_type === 'system' && strtolower($a->presence_status ?? '') === 'alpha') {
                    return false;
                }
                if ($a->status === 'rejected') {
                    return false;
                }
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') === $currentDateStr;
            })->sortBy(fn($a) => strtolower($a->presence_status ?? '') === 'masuk' ? 0 : 1)->first();

            $leave = $leaves->filter(function ($l) use ($date, $branchTimezone) {
                if ($l->type === 'telat') {
                    return false;
                }
                if (str_contains($l->reason ?? '', '[Refunded:' . $date->format('Y-m-d') . ']')) {
                    return false;
                }
                return $date->between(
                    Carbon::parse($l->start_date, $branchTimezone)->startOfDay(),
                    Carbon::parse($l->end_date ?? $l->start_date, $branchTimezone)->endOfDay()
                );
            });

            if (!$att && $leave->isEmpty()) {
                $alphaCount++;
                $alphaDates[] = $date->format('d/m');
            } elseif ($att && strtolower($att->presence_status ?? '') === 'alpha') {
                $alphaCount++;
                $alphaDates[] = $date->format('d/m');
            }
        }

        return [
            'alphaCount' => $alphaCount,
            'alphaDates' => array_values(array_unique($alphaDates)),
            'lateCount' => $lateCount,
            'lateDates' => array_values(array_unique($lateDates)),
        ];
    }

    /**
     * [BARU] Mengambil detail tanggal alpha dan terlambat untuk periode gaji ini
     */
    public function getAttendanceDetails()
    {
        if ($this->category === 'freelance') {
            return [
                'alphaDates' => [],
                'lateDates' => []
            ];
        }

        try {
            $summary = self::summarizeMonthlyAttendance($this->user, (int) $this->month, (int) $this->year);
            return [
                'alphaDates' => $summary['alphaDates'],
                'lateDates' => $summary['lateDates'],
            ];
        } catch (\Exception $e) {
            return [
                'alphaDates' => [],
                'lateDates' => [],
            ];
        }
    }
}