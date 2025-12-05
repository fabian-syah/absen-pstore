<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReturn extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Barang yang dikembalikan
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    // User pemilik barang (yang mengembalikan)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Admin yang memproses (Approved By)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}