<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Hitung Sisa Hutang
    public function getRemainingAmountAttribute() {
        return $this->amount - $this->total_paid;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function installments() {
        return $this->hasMany(CashAdvanceInstallment::class)->latest();
    }
}