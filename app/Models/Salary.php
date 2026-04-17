<?php

namespace App\Models;

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
     * [BARU] Mengambil detail tanggal alpha dan terlambat untuk periode gaji ini
     */
    public function getAttendanceDetails()
    {
        $user = $this->user;
        $alphaDates = [];
        $lateDates = [];

        try {
            $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
            $monthStartDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1, $branchTimezone)->subMonth()->day(26)->startOfDay();
            $monthEndDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1, $branchTimezone)->day(25)->endOfDay();
            
            // Limit Date (Jangan melewati hari ini)
            $limitDate = (now($branchTimezone)->lt($monthEndDate)) ? now($branchTimezone) : $monthEndDate;

            // Ambil data absen & leave dalam range
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('check_in_time', [
                        $monthStartDate->copy()->subDays(2)->startOfDay(),
                        $monthEndDate->copy()->addDays(2)->endOfDay()
                ])->get();

            $leaves = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $monthEndDate->format('Y-m-d'))
                ->where(function ($q) use ($monthStartDate) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStartDate->format('Y-m-d'));
                })->get();

            // 1. Cari Tanggal Terlambat
            $attendances->filter(function ($a) use ($monthStartDate, $monthEndDate, $branchTimezone, &$lateDates) {
                $attFullDate = \Carbon\Carbon::parse($a->check_in_time)->timezone($branchTimezone);
                $attDate = $attFullDate->copy()->startOfDay();
                $isInRange = $attDate->between($monthStartDate, $monthEndDate);
                $isTelat = $a->is_late_checkin || $a->status === 'late' || str_contains(strtolower($a->presence_status ?? ''), 'telat');
                
                if ($isInRange && $isTelat) {
                    $lateDates[] = $attFullDate->format('d/m');
                }
            });

            // 2. Cari Tanggal Alpha
            $period = \Carbon\CarbonPeriod::create($monthStartDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());
            foreach ($period as $date) {
                $currentDateStr = $date->format('Y-m-d');
                $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                    return \Carbon\Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
                })->sortBy(fn($a) => $a->attendance_type == 'system' ? 1 : 0)->first();

                $leave = $leaves->filter(function ($l) use ($date) {
                    return $date->between(\Carbon\Carbon::parse($l->start_date)->startOfDay(), \Carbon\Carbon::parse($l->end_date ?? $l->start_date)->endOfDay());
                });

                if (!$att && $leave->isEmpty()) {
                    $alphaDates[] = $date->format('d/m');
                } elseif ($att && strtolower($att->presence_status ?? '') === 'alpha') {
                    $alphaDates[] = $date->format('d/m');
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        return [
            'alphaDates' => array_unique($alphaDates),
            'lateDates' => array_unique($lateDates)
        ];
    }
}