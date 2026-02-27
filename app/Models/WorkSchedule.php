<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'division_id',
        'schedule_name',
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'check_in_start' => 'datetime:H:i',
        'check_in_end' => 'datetime:H:i',
        'check_out_start' => 'datetime:H:i',
        'check_out_end' => 'datetime:H:i',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isValidCheckIn($time)
    {
        // Jika settingan jam kosong, berarti BEBAS (selalu return true)
        if ($this->check_in_start == null || $this->check_in_end == null) {
            return true;
        }

        $checkTime = \Carbon\Carbon::parse($time)->format('H:i:s');
        return $checkTime >= $this->check_in_start->format('H:i:s') &&
            $checkTime <= $this->check_in_end->format('H:i:s');
    }

    public function isValidCheckOut($time)
    {
        // Jika settingan jam kosong, berarti BEBAS (selalu return true)
        if ($this->check_out_start == null || $this->check_out_end == null) {
            return true;
        }

        $checkTime = \Carbon\Carbon::parse($time)->format('H:i:s');
        return $checkTime >= $this->check_out_start->format('H:i:s') &&
            $checkTime <= $this->check_out_end->format('H:i:s');
    }

    /**
     * Get formatted time range for display
     */
    public function getCheckInRangeAttribute()
    {
        if ($this->check_in_start == null)
            return 'Fleksibel'; // Tampilan jika kosong
        return $this->check_in_start->format('H:i') . ' - ' . $this->check_in_end->format('H:i');
    }

    public function getCheckOutRangeAttribute()
    {
        if ($this->check_out_start == null)
            return 'Fleksibel'; // Tampilan jika kosong
        return $this->check_out_start->format('H:i') . ' - ' . $this->check_out_end->format('H:i');
    }
    public static function getScheduleForUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return self::default()->active()->first();
        }

        // Cari schedule berdasarkan divisi
        $divisionSchedule = self::where('division_id', $user->division_id)
            ->active()
            ->first();

        if ($divisionSchedule) {
            return $divisionSchedule;
        }

        // Cari schedule berdasarkan branch
        $branchSchedule = self::where('branch_id', $user->branch_id)
            ->active()
            ->first();

        if ($branchSchedule) {
            return $branchSchedule;
        }

        // Return default schedule
        return self::default()->active()->first();
    }
}
