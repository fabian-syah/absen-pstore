<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'creator_id',
        'branch_id',
        'division_id',
        'title',
        'description',
        'priority',
        'status',          // pending, completed (secara umum)
        'outcome',         // exceeded, achieved, partial, failed, changed
        'completion_description',
        'evidence_photo',
        'progress',
        'type',            // personal, team, achievement
        'period',          // daily, monthly, yearly
        'start_date',
        'deadline',
        'completed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function division() { return $this->belongsTo(Division::class); }
}