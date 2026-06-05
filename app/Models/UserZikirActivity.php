<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserZikirActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'zikir_id', 'total_count', 'all_time_count', 'target_count', 'last_read_at'
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zikir()
    {
        return $this->belongsTo(Zikir::class);
    }
}
