<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'title',            // BARU
        'amount',
        'total_paid',
        'payment_method',   // BARU
        'payment_details',  // BARU
        'due_date',
        'photo_1',
        'description_1',
        'photo_2',
        'description_2',
        'status',
        'processed_by',
        'approved_date',
        'repayment_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function installments()
    {
        return $this->hasMany(CashAdvanceInstallment::class)->latest();
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->total_paid;
    }

    // Helper: Cek apakah Lewat Jatuh Tempo?
    public function getIsOverdueAttribute()
    {
        // Jika belum lunas DAN hari ini > tanggal jatuh tempo
        return $this->status != 'paid' && 
               $this->status != 'rejected' && 
               Carbon::now()->startOfDay()->gt(Carbon::parse($this->due_date)->startOfDay());
    }
}