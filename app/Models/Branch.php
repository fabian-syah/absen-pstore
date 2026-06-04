<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'address', 
        'is_active', 
        'timezone',
        'kemenag_city_id'
    ];

    // Relasi: Satu cabang punya banyak user
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relasi: Satu cabang punya banyak divisi
    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    // Relasi ke User (Many-to-Many untuk Audit/Leader)
    public function audits()
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id');
    }
}