<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceImei extends Model
{
    use HasFactory;

    protected $table = 'device_imeis';

    protected $fillable = [
        'imei',
        'brand',
        'type',
        'storage',
        'color',
        'last_cs_name',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Riwayat koreksi dan perubahan data spesifikasi IMEI
     */
    public function histories()
    {
        return $this->hasMany(DeviceImeiHistory::class, 'device_imei_id')->latest();
    }

    /**
     * User / Admin yang pertama kali membuat data
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Super Admin yang terakhir mengubah data
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
