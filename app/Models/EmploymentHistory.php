<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    protected $fillable = [
        'user_id', 'type', 'event_date', 
        'branch_id', // Target Single Branch
        'division_id', 
        'description', 'attachment',
        'previous_branch_id', // <--- BARU
        'audit_branch_snapshot' // <--- BARU (Array/JSON)
    ];

    protected $casts = [
        'event_date' => 'date',
        'audit_branch_snapshot' => 'array', // Auto convert JSON ke Array
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function division() { return $this->belongsTo(Division::class); }
    
    // Relasi ke Cabang Sebelumnya (Single Branch User)
    public function previousBranch()
    {
        return $this->belongsTo(Branch::class, 'previous_branch_id');
    }

    // Helper: Generate Text untuk History Audit
    public function getAuditChangeTextAttribute()
    {
        if (empty($this->audit_branch_snapshot)) return '-';
        
        $data = $this->audit_branch_snapshot;
        $from = isset($data['from']) ? implode(', ', $data['from']) : '-';
        $to = isset($data['to']) ? implode(', ', $data['to']) : '-';

        return "Dari: [$from] <br> Ke: [$to]";
    }

    // ... (helper type_label dan color tetap sama)
     public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'join' => 'Awal Masuk Pstore',
            'transfer_branch' => 'Pindah Cabang',
            'transfer_division' => 'Pindah Divisi / Jabatan',
            'resign' => 'Resign / Dirumahkan',
            'rejoin' => 'Masuk Pstore Lagi',
            default => ucfirst($this->type),
        };
    }
    
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'join', 'rejoin' => 'success',
            'transfer_branch' => 'warning',
            'transfer_division' => 'info',
            'resign' => 'danger',
            default => 'secondary',
        };
    }
}