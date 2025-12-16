<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category', 'basic_salary', 
        'position_allowance', 'owner_privilege', 
        'daily_salary', 'updated_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}