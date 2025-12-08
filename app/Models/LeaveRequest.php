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
        'start_time',       // Jam mulai (Wajib untuk Telat)
        'end_time',         // Jam selesai (Opsional)
        'reason',
        'file_proof',
        'status',
        'rejection_reason',
        'is_active',
        'approved_by',      // ID Penyetuju
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
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

    // --- PERBAIKAN UTAMA DISINI ---
    // Tambahkan relasi 'verifier' yang mengarah ke 'approved_by'
    // Ini agar konsisten dengan controller yang memanggil 'with("verifier")'
    public function verifier()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Alias 'approver' (jika masih dipakai di tempat lain)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActiveLatePermissions($query)
    {
        return $query->where('type', 'telat')
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereDate('start_date', today());
    }
}