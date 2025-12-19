<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    // =================================================================
    // [PENTING] PASTIKAN KOLOM BARU ADA DI SINI AGAR BISA DISIMPAN
    // =================================================================
    protected $fillable = [
        'user_id',
        'branch_id',
        'check_in_time',
        'check_out_time',
        'scheduled_check_in',  // <--- WAJIB ADA (SNAPSHOT JADWAL MASUK)
        'scheduled_check_out', // <--- WAJIB ADA (SNAPSHOT JADWAL PULANG)
        'status',
        'is_extended_shift',
        'presence_status',
        'photo_path',
        'photo_out_path',
        'audit_photo_path',
        'audit_note',
        'scanned_by_user_id',
        'scanned_out_by_user_id',
        'verified_by_user_id',
        'latitude',
        'longitude',
        'latitude_out',
        'longitude_out',
        'work_schedule_id',
        'is_late_checkin',
        'is_early_checkout',
        'attendance_type',
        'notes',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'is_late_checkin' => 'boolean',
        'is_early_checkout' => 'boolean',
        // Casting agar format jam snapshot terbaca benar
        'scheduled_check_in' => 'datetime:H:i:s',
        'scheduled_check_out' => 'datetime:H:i:s',
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    // Relation Aliases
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', today());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeScannedBy($query, $securityId)
    {
        return $query->where('scanned_by_user_id', $securityId);
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_by_user_id');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late_checkin', true);
    }

    public function scopeEarlyCheckout($query)
    {
        return $query->where('is_early_checkout', true);
    }

    // ==========================================
    // STATIC HELPERS
    // ==========================================

    public static function hasUserAttendedToday($userId)
    {
        return static::forUser($userId)->today()->exists();
    }

    public static function getTodayAttendance($userId)
    {
        return static::forUser($userId)->today()->first();
    }

    // ==========================================
    // INSTANCE METHODS
    // ==========================================

    public function verify($auditUserId)
    {
        $this->verified_by_user_id = $auditUserId;
        return $this->save();
    }

    public function isValidCheckIn()
    {
        if (!$this->workSchedule) {
            return true;
        }
        $checkInTime = Carbon::parse($this->check_in_time);
        $scheduleStart = Carbon::parse($this->workSchedule->check_in_start);
        $scheduleEnd = Carbon::parse($this->workSchedule->check_in_end);

        return $checkInTime->between($scheduleStart, $scheduleEnd);
    }

    public function isValidCheckOut()
    {
        if (!$this->check_out_time || !$this->workSchedule) {
            return true;
        }
        $checkOutTime = Carbon::parse($this->check_out_time);
        $scheduleStart = Carbon::parse($this->workSchedule->check_out_start);
        $scheduleEnd = Carbon::parse($this->workSchedule->check_out_end);

        return $checkOutTime->between($scheduleStart, $scheduleEnd);
    }

    public function markAsLate()
    {
        $this->is_late_checkin = true;
        return $this->save();
    }

    public function markAsEarlyCheckout()
    {
        $this->is_early_checkout = true;
        return $this->save();
    }

    public function applyWorkScheduleValidation()
    {
        $workSchedule = WorkSchedule::getScheduleForUser($this->user_id);

        if ($workSchedule) {
            $this->work_schedule_id = $workSchedule->id;

            if (!$this->isValidCheckIn()) {
                $this->is_late_checkin = true;
                $this->status = 'late';
            }

            if ($this->check_out_time && !$this->isValidCheckOut()) {
                $this->is_early_checkout = true;
            }
        }
        return $this;
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getWorkDurationAttribute()
    {
        if (!$this->check_out_time) {
            return null;
        }
        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);
        return $checkOut->diff($checkIn)->format('%H:%I:%S');
    }

    public function getFormattedCheckInTimeAttribute()
    {
        return $this->check_in_time->format('H:i:s');
    }

    public function getFormattedCheckOutTimeAttribute()
    {
        return $this->check_out_time ? $this->check_out_time->format('H:i:s') : null;
    }

    public function getFormattedCheckInDateAttribute()
    {
        return $this->check_in_time->format('d-m-Y');
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'pending_verification' => 'Menunggu Verifikasi',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getHasCheckedOutAttribute()
    {
        return !is_null($this->check_out_time);
    }

    public function getAttendanceTypeLabelAttribute()
    {
        $labels = [
            'scan' => 'Scan QR',
            'self' => 'Absen Mandiri',
            'manual' => 'Manual'
        ];
        return $labels[$this->attendance_type] ?? $this->attendance_type;
    }

    public function getPresenceStatusBadgeAttribute()
    {
        return match ($this->presence_status) {
            'Masuk' => 'success',
            'WFH / Dinas Luar' => 'info',
            'Izin Telat' => 'warning',
            'Sakit' => 'primary',
            'Cuti' => 'secondary',
            'Alpha' => 'danger',
            'Telat' => 'danger',
            'default' => 'dark',
        };
    }

    public function getVerificationStatusAttribute()
    {
        if ($this->status === 'verified') {
            return 'verified';
        } elseif ($this->status === 'pending_verification') {
            return 'pending';
        } else {
            return 'not_verified';
        }
    }

    public function getVerificationBadgeColorAttribute()
    {
        return match ($this->verification_status) {
            'verified' => 'success',
            'pending' => 'warning',
            'default' => 'secondary'
        };
    }

    public function getVerificationIconAttribute()
    {
        return match ($this->verification_status) {
            'verified' => 'mdi-check-circle',
            'pending' => 'mdi-clock-outline',
            'default' => 'mdi-alert-circle'
        };
    }

    public function getIsVerifiedAttribute()
    {
        return $this->status === 'verified' && !is_null($this->verified_by_user_id);
    }

    public function getIsPendingVerificationAttribute()
    {
        return $this->status === 'pending_verification';
    }

    public function getVerifierNameAttribute()
    {
        return $this->verifiedBy ? $this->verifiedBy->name : null;
    }

    // Attendance.php
    public function getScheduledCheckInLocalAttribute()
    {
        if (!$this->scheduled_check_in) {
            return null;
        }

        // Ambil timezone dari user yang terkait
        $user = $this->user;
        $branchTimezone = $user && $user->branch ? $user->branch->timezone : 'Asia/Jakarta';

        try {
            $time = Carbon::createFromFormat('H:i:s', $this->scheduled_check_in);
            $time->setTimezone($branchTimezone);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return $this->scheduled_check_in;
        }
    }

    public function getScheduledCheckOutLocalAttribute()
    {
        if (!$this->scheduled_check_out) {
            return null;
        }

        // Ambil timezone dari user yang terkait
        $user = $this->user;
        $branchTimezone = $user && $user->branch ? $user->branch->timezone : 'Asia/Jakarta';

        try {
            $time = Carbon::createFromFormat('H:i:s', $this->scheduled_check_out);
            $time->setTimezone($branchTimezone);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return $this->scheduled_check_out;
        }
    }

    // Relasi untuk petugas yang scan pulang (TAMBAHAN)
    public function scannerOut()
    {
        return $this->belongsTo(User::class, 'scanned_out_by_user_id');
    }
}
