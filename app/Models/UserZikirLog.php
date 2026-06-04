<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserZikirLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'zikir_id', 'count', 'read_date'
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
