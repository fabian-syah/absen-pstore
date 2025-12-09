<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'created_by', 'title', 'amount', 'total_paid', 
        'tenor', // BARU
        'payment_method', 'payment_details', 'due_date',
        'photo_1', 'description_1', 'photo_2', 'description_2',
        'status', 'processed_by', 'approved_date', 'repayment_date',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Histori Pembayaran (Uang Masuk)
    public function installments() {
        return $this->hasMany(CashAdvanceInstallment::class)->latest();
    }

    // [BARU] Relasi ke Jadwal Rencana
    public function plans() {
        return $this->hasMany(CashAdvancePlan::class)->orderBy('installment_order', 'asc');
    }

    public function getRemainingAmountAttribute() {
        return $this->amount - $this->total_paid;
    }

    public function getIsOverdueAttribute() {
        return $this->status != 'paid' && 
               $this->status != 'rejected' && 
               Carbon::now()->startOfDay()->gt(Carbon::parse($this->due_date)->startOfDay());
    }
}