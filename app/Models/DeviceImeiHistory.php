<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceImeiHistory extends Model
{
    use HasFactory;

    protected $table = 'device_imei_histories';

    protected $fillable = [
        'device_imei_id',
        'imei',
        'action_type',
        'old_brand',
        'old_type',
        'old_storage',
        'old_color',
        'new_brand',
        'new_type',
        'new_storage',
        'new_color',
        'cs_name_mistake',
        'correction_reason',
        'corrected_by',
    ];

    /**
     * Unit IMEI terkait
     */
    public function device()
    {
        return $this->belongsTo(DeviceImei::class, 'device_imei_id');
    }

    /**
     * Super Admin yang melakukan koreksi
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
