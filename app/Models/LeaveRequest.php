<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',             // sakit, izin, telat
        'start_date',       // Tanggal mulai (Wajib untuk Sakit/Telat)
        'end_date',         // Tanggal selesai (Wajib untuk Sakit)
        'start_time',       // Jam mulai (Wajib untuk Telat) -> BARU
        'end_time',         // Jam selesai (Opsional) -> BARU
        'reason',
        'file_proof',
        'status',
        'rejection_reason',
        'is_active',
        'approved_by',      // <--- 1. TAMBAHKAN INI
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        // start_time dan end_time biasanya string (H:i:s), 
        // tapi bisa dicast ke 'datetime' format jam jika perlu
        'is_active'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function division()
    {
        return $this->hasOneThrough(Division::class, User::class, 'id', 'id', 'user_id', 'division_id');
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, User::class, 'id', 'id', 'user_id', 'branch_id');
    }

    // RELASI KE PENYETUJU (PENTING UNTUK MENAMPILKAN NAMA BIAN)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scope Update: Izin Telat Aktif
    public function scopeActiveLatePermissions($query)
    {
        return $query->where('type', 'telat')
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereDate('start_date', today()); // Cek tanggal hari ini
    }
}
