<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'amount',
        'photo_1',
        'description_1',
        'photo_2',
        'description_2',
        'status',
        'processed_by',
        'approved_date',
        'repayment_date',
        'repayment_proof'
    ];

    // Relasi ke User yang berhutang
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Admin/User yang memproses
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}