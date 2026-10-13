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
        'latitude',
        'longitude',
        'radius',
        'is_active', 
        'timezone',
        'kemenag_city_id'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Cek apakah cabang ini memiliki pembatasan radius aktif (koordinat dan radius terisi)
     */
    public function hasGeofence(): bool
    {
        return !is_null($this->latitude) 
            && !is_null($this->longitude) 
            && !is_null($this->radius) 
            && $this->radius > 0;
    }

    /**
     * Hitung jarak dari titik cabang ke koordinat user (dalam meter)
     * Menggunakan Haversine Formula
     */
    public function calculateDistanceTo(float $userLat, float $userLng): float
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return 0.0;
        }

        $earthRadius = 6371000; // Radius rata-rata bumi dalam meter

        $latFrom = deg2rad((float) $this->latitude);
        $lonFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($userLat);
        $lonTo = deg2rad($userLng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

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
            'Development',
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
            'Tim Elite',
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
        if (str_contains($name, 'pusat') || str_contains($name, 'head office') || str_contains($name, 'holding') || str_contains($name, 'kantor pusat')) {
            return true;
        }
        return false;
    }

    public function isPusat(): bool
    {
        return $this->is_pusat;
    }
}