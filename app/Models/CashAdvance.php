<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'monthly_deduction' => 'decimal:0',
        'amount' => 'decimal:0',
        'total_paid' => 'decimal:0',
        'approved_date' => 'datetime',
        'repayment_date' => 'date',
    ];

    // Hitung Sisa Hutang
    public function getRemainingAmountAttribute() {
        return $this->amount - $this->total_paid;
    }

    // Hitung estimasi bulan lunas
    public function getEstimatedPayoffMonthsAttribute() {
        if ($this->monthly_deduction > 0 && $this->remaining_amount > 0) {
            return ceil($this->remaining_amount / $this->monthly_deduction);
        }
        return 0;
    }

    // Hitung estimasi tanggal lunas
    public function getEstimatedPayoffDateAttribute() {
        if ($this->monthly_deduction > 0 && $this->remaining_amount > 0) {
            $months = $this->estimated_payoff_months;
            return now()->addMonths($months);
        }
        return null;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function installments() {
        return $this->hasMany(CashAdvanceInstallment::class)->latest();
    }

    public function plans() {
        return $this->hasMany(CashAdvancePlan::class);
    }
}