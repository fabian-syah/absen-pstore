<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zikir extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'category', 'arabic_text', 'latin_text', 'translation', 'default_target', 'information'
    ];

    public function favorites()
    {
        return $this->hasMany(UserZikirFavorite::class);
    }

    public function activities()
    {
        return $this->hasMany(UserZikirActivity::class);
    }
}
