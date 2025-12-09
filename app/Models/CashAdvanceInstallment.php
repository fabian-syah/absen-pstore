<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAdvanceInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_advance_id',
        'user_id',
        'amount_paid',
        'payment_proof',
        'status',
        'note'
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}