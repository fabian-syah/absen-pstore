<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZikirCampaign extends Model
{
    protected $table = 'zikir_campaigns';

    protected $fillable = [
        'title',
        'arabic_text',
        'latin_text',
        'target',
        'current_count',
        'emoji',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target' => 'integer',
        'current_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentAttribute(): float
    {
        if ($this->target <= 0) return 0;
        return min(100, round(($this->current_count / $this->target) * 100, 1));
    }
}
