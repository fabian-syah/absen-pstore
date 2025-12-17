<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'category', 
        'basic_salary', 
        'position_allowance', 
        'owner_privilege', 
        'use_privilege_mode', 
        'daily_salary', 
        'promotor_bonus', 
        'bank_account_number', 
        'bank_name',
        'updated_by',
        'notes' // <--- TAMBAHKAN INI (Supaya catatan bisa disimpan)
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}