<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    protected $fillable = [
        'user_id', 'type', 'event_date', 
        'branch_id', 
        'division_id', 
        'description', 'attachment',
        'previous_branch_id', 
        'audit_branch_snapshot',
        'created_by', // <--- BARU
        'updated_by'  // <--- BARU
    ];

    protected $casts = [
        'event_date' => 'date',
        'audit_branch_snapshot' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function division() { return $this->belongsTo(Division::class); }
    
    public function previousBranch()
    {
        return $this->belongsTo(Branch::class, 'previous_branch_id');
    }

    // Relasi Pencatat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi Pengedit Terakhir
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getAuditChangeTextAttribute()
    {
        if (empty($this->audit_branch_snapshot)) return '-';
        
        $data = $this->audit_branch_snapshot;
        // Hanya tampilkan 'To' (Yang baru) sesuai request
        $to = isset($data['to']) ? implode(', ', $data['to']) : '-';

        return "Wilayah Audit Baru: <br><strong>$to</strong>";
    }

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