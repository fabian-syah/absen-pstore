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
        'total_paid',   // BARU
        'due_date',     // BARU
        'photo_1',
        'description_1',
        'photo_2',
        'description_2',
        'status',
        'processed_by',
        'approved_date',
        'repayment_date', // Ini jadi tanggal LUNAS total
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Cicilan
    public function installments()
    {
        return $this->hasMany(CashAdvanceInstallment::class)->latest();
    }

    // Helper untuk cek sisa hutang
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->total_paid;
    }
}