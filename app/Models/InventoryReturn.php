<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReturn extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Barang
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    // Relasi ke User yang mengembalikan
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Admin yang memproses
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}