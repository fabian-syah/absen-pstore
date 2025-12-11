<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'event_date',
        'branch_id',
        'division_id',
        'description',
        'attachment',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relasi ke Division
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    
    // Helper untuk Label Kategori yang rapi
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'join' => 'Awal Masuk Pstore',
            'transfer_branch' => 'Pindah Cabang',
            'transfer_division' => 'Pindah Divisi / Jabatan',
            'resign' => 'Resign / Dirumahkan',
            'rejoin' => 'Masuk Pstore Lagi',
            default => ucfirst($this->type),
        };
    }
    
    // Helper Warna Badge
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'join', 'rejoin' => 'success',
            'transfer_branch' => 'warning',
            'transfer_division' => 'info',
            'resign' => 'danger',
            default => 'secondary',
        };
    }
}