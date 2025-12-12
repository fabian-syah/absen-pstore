<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reported_by',
        'title',
        'description',
        'category',
        'notes',
        'photo_path',
        'expires_at',
        // [BARU] Kolom Penyelesaian Manual
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    // [BARU] Relasi ke User yang menyelesaikan (Admin/Audit)
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}