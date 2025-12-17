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
        'basic_salary',      // Dipakai untuk Gaji Pokok (Karyawan) & Gaji Bulanan (Promotor)
        'position_allowance', 
        'owner_privilege', 
        'use_privilege_mode', // Mode Privilege: 'fixed' atau 'percentage'
        'daily_salary',      // Dipakai untuk Freelance
        'promotor_bonus',    // Khusus Promotor
        'bank_account_number', 
        'bank_name',
        'updated_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}