<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminGajiUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'user_id',
    ];

    /**
     * Relasi ke User (real user untuk payroll)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
