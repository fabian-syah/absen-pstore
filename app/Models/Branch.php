<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'is_active'];

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
}
