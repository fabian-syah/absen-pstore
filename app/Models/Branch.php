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

    /**
     * Daftar unit/cabang yang masuk kategori Pusat
     */
    public static function pusatList(): array
    {
        return [
            'AppleLux',
            'Arcis & Debs',
            'Cleaning service',
            'Dokter Pstore',
            'Driver pstore',
            'Finance',
            'Inventory',
            'keluarga Pstore',
            'Managament',
            'Marketing Creative',
            'Masjid abdurrohman bin auf',
            'Mega pstore',
            'Ps arwana',
            'PS bakery',
            'PS big jakarta',
            'PS catering',
            'PS new jakarta',
            'Pskontraktor',
            'Pstore Lenteng Agung',
            'Pstore Peduli',
            'Pstore Qcell jakarta',
            'Shopee',
            'Security Jakarta',
            'Team Audit',
            'Team Creative',
            'Tiktok',
            'Operator',
        ];
    }

    /**
     * Cek apakah cabang ini termasuk kantor/unit Pusat
     */
    public function getIsPusatAttribute(): bool
    {
        $list = self::pusatList();
        $name = strtolower(trim($this->name ?? ''));
        foreach ($list as $p) {
            if ($name === strtolower(trim($p))) {
                return true;
            }
        }
        return false;
    }

    public function isPusat(): bool
    {
        return $this->is_pusat;
    }
}