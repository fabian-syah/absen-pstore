<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAdvancePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_advance_id',
        'installment_order',
        'due_date',
        'amount',
        'is_paid'
    ];
}