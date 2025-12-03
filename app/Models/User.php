<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- TAMBAHAN PENTING

// --- MODEL IMPORTS ---
use App\Models\WorkHistory;
use App\Models\Inventory;
use App\Models\LateNotification;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;
use App\Models\Broadcast;
// ---------------------

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'birth_date',
        'login_id',        // ID Login khusus (wajib)
        'password',
        'role',
        'division_id',     // Primary Division (Homebase)
        'qr_code_value',
        'branch_id',       // Primary Branch (Homebase)
        'work_schedule_id', // <--- TAMBAHKAN INI
        'is_active',
        'profile_photo_path',
        'ktp_photo_path',
        'hire_date',
        'email',           // Opsional, hanya untuk sosmed
        'whatsapp',        // Opsional, hanya untuk sosmed
        'instagram',       // Opsional, hanya untuk sosmed
        'tiktok',          // Opsional, hanya untuk sosmed
        'facebook',
        'linkedin',
        'is_verified',
        'photo_request_status',
        // --- TAMBAHKAN 4 BARIS INI AGAR JAM MANUAL BISA DISIMPAN ---
        'check_in_start',
        'check_out_start',
        'check_in_end',
        'check_out_end',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    // =================================================================
    // RELASI SINGLE (ONE TO MANY) - HOMEBASE / PRIMARY
    // =================================================================

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    // =================================================================
    // RELASI MULTI (MANY TO MANY) - KHUSUS AUDIT & LEADER
    // =================================================================

    /**
     * Relasi Many-to-Many ke Branch.
     * Digunakan oleh role 'audit' untuk mengakses banyak cabang.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id')
            ->withTimestamps();
    }

    /**
     * Relasi Many-to-Many ke Division.
     * Digunakan oleh role 'leader' untuk memimpin banyak divisi.
     */
    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'division_user', 'user_id', 'division_id')
            ->withTimestamps();
    }

    // =================================================================
    // RELASI DATA PENDUKUNG
    // =================================================================

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function activeLateStatus(): HasOne
    {
        return $this->hasOne(LateNotification::class)
            ->where('is_active', true)
            ->whereDate('created_at', today());
    }

    /**
     * Relasi ke Riwayat Pekerjaan
     */
    public function workHistories()
    {
        return $this->hasMany(WorkHistory::class);
    }

    /**
     * Relasi ke Inventory
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function broadcasts()
    {
        return $this->hasMany(Broadcast::class, 'created_by');
    }

    public function leaveRequests(): HasMany
    {
        // Pastikan model LeaveRequest ada di App\Models\LeaveRequest
        return $this->hasMany(\App\Models\LeaveRequest::class, 'user_id', 'id');
    }

    /**
     * Find user by login credentials (whatsapp, instagram, tiktok, or email)
     */
    public function findForLogin($loginId)
    {
        return $this->where('whatsapp', $loginId)
            ->orWhere('instagram', $loginId)
            ->orWhere('tiktok', $loginId)
            ->orWhere('email', $loginId)
            ->first();
    }

    // Tambahkan Relasi di bawah
    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    public function isDataComplete()
    {
        return !empty($this->profile_photo_path) &&
            !empty($this->ktp_photo_path) &&
            !empty($this->whatsapp) &&
            !empty($this->email);
    }
}
