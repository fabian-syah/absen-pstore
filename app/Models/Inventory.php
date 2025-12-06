<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_name',
        'category',
        'serial_number',
        'received_date',
        'condition',
        'description',
        'item_photo_path',      // Foto Barang
        'user_item_photo_path', // Foto User + Barang (BARU)
        'document_path'
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor untuk item_photo URL
     */
    public function getItemPhotoAttribute()
    {
        return $this->item_photo_path ? Storage::url($this->item_photo_path) : null;
    }

    /**
     * Accessor untuk user_item_photo URL (BARU)
     */
    public function getUserItemPhotoAttribute()
    {
        return $this->user_item_photo_path ? Storage::url($this->user_item_photo_path) : null;
    }

    /**
     * Accessor untuk document URL
     */
    public function getDocumentAttribute()
    {
        return $this->document_path ? Storage::url($this->document_path) : null;
    }
}