<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 
        'user_id', 
        'message', 
        'image_path' // <--- Tambahkan ini
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}