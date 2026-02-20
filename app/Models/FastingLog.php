<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FastingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'is_fasting',
        'ramadan_day',
        'hijri_year',
    ];

    protected $casts = [
        'date' => 'date',
        'is_fasting' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
