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
    ];

    // Relasi ke User (Pelaku)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke User (Pelapor/Admin/Audit)
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}