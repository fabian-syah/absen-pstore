<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelfAttendanceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WorkHistoryController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AttendanceHistoryController;

/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Rute Aplikasi (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active.user'])->group(function () {

    // --- Rute Utama ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // --- Rute Export PDF ---
    Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportAttendancePDF'])->name('dashboard.export-pdf');

    // --- Rute Search Global ---
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    // === RUTE RIWAYAT ABSENSI ===
    Route::get('/riwayat-absensi', [AttendanceHistoryController::class, 'index'])->name('attendance.history');

    // === RUTE BROADCAST ===
    Route::prefix('broadcast')->name('broadcast.')->group(function () {
        Route::get('/notifications', [BroadcastController::class, 'getNotifications'])->name('notifications');
        Route::post('/{broadcast}/mark-read', [BroadcastController::class, 'markAsRead'])->name('mark-read');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/', [BroadcastController::class, 'index'])->name('index');
            Route::get('/create', [BroadcastController::class, 'create'])->name('create');
            Route::post('/', [BroadcastController::class, 'store'])->name('store');
            Route::get('/{broadcast}/edit', [BroadcastController::class, 'edit'])->name('edit');
            Route::put('/{broadcast}', [BroadcastController::class, 'update'])->name('update');
            Route::delete('/{broadcast}', [BroadcastController::class, 'destroy'])->name('destroy');
        });

        Route::get('/{broadcast}', [BroadcastController::class, 'show'])->name('show');
    });

    // === RUTE WORK SCHEDULES ===
    Route::prefix('work-schedules')->name('work-schedules.')->group(function () {
        Route::get('/', [WorkScheduleController::class, 'index'])->name('index');
        Route::get('/create', [WorkScheduleController::class, 'create'])->name('create');
        Route::post('/', [WorkScheduleController::class, 'store'])->name('store');
        Route::get('/{workSchedule}/edit', [WorkScheduleController::class, 'edit'])->name('edit');
        Route::put('/{workSchedule}', [WorkScheduleController::class, 'update'])->name('update');
        Route::delete('/{workSchedule}', [WorkScheduleController::class, 'destroy'])->name('destroy');
        Route::patch('/{workSchedule}/toggle-status', [WorkScheduleController::class, 'toggleStatus'])->name('toggle-status');
    })->middleware('role:admin,audit');

    // === RUTE PROFILE (UNTUK SEMUA ROLE) ===
    Route::prefix('profile')->name('profile.')->group(function () {
        // Edit Profile Utama
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        
        // Foto & KTP
        Route::delete('/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::put('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
        
        // [BARU] Request Ganti Foto (Jika User Verified)
        Route::post('/photo/request', [ProfileController::class, 'requestPhotoChange'])->name('photo.request');

        Route::get('/photo/{user}', [ProfileController::class, 'getProfilePhoto'])->name('photo.get');
        Route::put('/ktp', [ProfileController::class, 'updateKtp'])->name('ktp.update');
        Route::get('/ktp/{user}', [ProfileController::class, 'getKtpPhoto'])->name('ktp.get');
        
        // Riwayat Pekerjaan
        Route::post('/work-history', [WorkHistoryController::class, 'store'])->name('work-history.store');
        Route::delete('/work-history/{history}', [WorkHistoryController::class, 'destroy'])->name('work-history.destroy');

        // --- INVENTORY KHUSUS PROFILE ---
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store'); 
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::get('/inventory', [InventoryController::class, 'showInventory'])->name('inventory.index');
    });

    // === RUTE INVENTORY (SIDEBAR MENU) ===
    // 1. Semua Role BISA MELIHAT (Read)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index'); 
        Route::get('/detail/{inventory}', [InventoryController::class, 'show'])->name('show');
    });

    // 2. Hanya ADMIN & AUDIT BISA MENGELOLA
    Route::prefix('inventory')->name('inventory.')->middleware(['role:admin,audit'])->group(function () {
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
    });

    // === RUTE ADMIN & AUDIT MANAGEMENT ===
    Route::middleware(['role:admin,audit'])->group(function () {
        Route::get('/all-attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.all');
        Route::put('/audit/verify-attendance/{attendance}', [AuditController::class, 'verifyAttendance'])->name('audit.verify.attendance');
        Route::put('/attendance/{id}/audit-update', [AttendanceHistoryController::class, 'updateByAudit'])->name('audit.update.attendance');

        Route::resource('branches', BranchController::class);
        Route::post('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');

        Route::resource('divisions', DivisionController::class);
        Route::post('/divisions/{division}/toggle-status', [DivisionController::class, 'toggleStatus'])->name('divisions.toggle-status');

        // USER MANAGEMENT
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        
        // [BARU] Verifikasi User & Approval Foto
        Route::patch('/users/{user}/verify', [UserController::class, 'verifyUser'])->name('users.verify');
        Route::patch('/users/{user}/approve-photo', [UserController::class, 'approvePhotoRequest'])->name('users.approve-photo');

        Route::prefix('verifikasi')->name('audit.')->group(function () {
            Route::get('/absensi', [AuditController::class, 'showVerificationList'])->name('verify.list');
            Route::put('/setujui/{attendance}', [AuditController::class, 'approve'])->name('approve');
            Route::delete('/tolak/{attendance}', [AuditController::class, 'reject'])->name('reject');
            Route::get('/laporan', [AuditController::class, 'showReports'])->name('reports');
        });

        Route::get('/izin-telat', [AuditController::class, 'showLatePermissions'])->name('audit.late.list');
        Route::post('/izin-telat/{lateNotification}/approve', [AuditController::class, 'approveLatePermission'])->name('late.approve');
        Route::post('/izin-telat/{lateNotification}/reject', [AuditController::class, 'rejectLatePermission'])->name('late.reject');

        Route::get('/audit/missed-checkouts', [AuditController::class, 'showMissedCheckouts'])->name('audit.missed-checkout.list');
        Route::put('/audit/missed-checkouts/{id}', [AuditController::class, 'updateMissedCheckout'])->name('audit.missed-checkout.update');
    });

    // === RUTE SECURITY ===
    Route::middleware(['role:security'])->prefix('security')->name('security.')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan');
        Route::post('/check-user', [ScanController::class, 'checkUser'])->name('check-user');
        Route::post('/store-attendance', [ScanController::class, 'storeAttendance'])->name('store-attendance');
        Route::get('/stats', [ScanController::class, 'getStats'])->name('stats');
        Route::get('/attendance-log', [ScanController::class, 'attendanceLog'])->name('attendance-log');
        Route::get('/today-attendance', [ScanController::class, 'todayAttendance'])->name('today-attendance');
    });

    // === RUTE TEAM MANAGEMENT ===
    Route::middleware(['role:user_biasa,leader,audit'])->group(function () {
        Route::get('/tim-saya', [TeamController::class, 'index'])->name('team.index');
        Route::get('/tim-saya/{user}', [TeamController::class, 'show'])->name('my.team.show');
        Route::get('/tim-saya/attendance/{user}', [TeamController::class, 'attendance'])->name('my.team.attendance');
        Route::get('/team/branch/{id}', [TeamController::class, 'showBranch'])->name('team.branch.detail');
        Route::get('/team/branch/{branchId}/employee/{employeeId}/history', [TeamController::class, 'showEmployeeHistory'])->name('team.branch.employee.history');
    });

    Route::middleware(['role:audit'])->group(function () {
        Route::get('/cabang-saya', [TeamController::class, 'myBranches'])->name('team.my-branches');
    });

    // === RUTE SELF ATTENDANCE ===
    Route::middleware(['role:user_biasa,leader,audit,security'])->prefix('absen-mandiri')->name('self.attend.')->group(function () {
        Route::get('/', [SelfAttendanceController::class, 'create'])->name('create');
        Route::post('/', [SelfAttendanceController::class, 'store'])->name('store');
        Route::get('/history', [SelfAttendanceController::class, 'history'])->name('history');
        Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');
        Route::post('/skip-checkout/{id}', [SelfAttendanceController::class, 'skipCheckOut'])->name('skip');
    });

    // === RUTE LEAVE REQUESTS ===
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');

        Route::middleware(['role:user_biasa,leader,audit,security'])->group(function () {
            Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
            Route::post('/store', [LeaveRequestController::class, 'store'])->name('store');
            Route::patch('/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
            Route::patch('/{leaveRequest}/finish-early', [LeaveRequestController::class, 'finishEarly'])->name('finish-early');
        });

        Route::middleware(['role:admin,audit'])->group(function () {
            Route::patch('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
            Route::patch('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        });
    });

    // === RUTE LAPORAN & ANALYTICS ===
    Route::middleware(['role:admin,audit,leader'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/attendance', [AuditController::class, 'attendanceReport'])->name('attendance');
        Route::get('/performance', [AuditController::class, 'performanceReport'])->name('performance');
        Route::get('/leave', [AuditController::class, 'leaveReport'])->name('leave');
        Route::get('/export/attendance', [AuditController::class, 'exportAttendance'])->name('export.attendance');
    });

    // === RUTE API ===
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard-stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('recent.activities');
        Route::get('/attendance-chart', [DashboardController::class, 'getAttendanceChart'])->name('attendance.chart');
    });

    Route::get('/test-role-middleware', function () {
        return response()->json(['message' => 'Middleware test berhasil!']);
    })->middleware(['role:admin,audit,security,leader,user_biasa']);

    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

if (app()->environment('local')) {
    Route::get('/debug-session', function () {
        return response()->json(['session' => session()->all(), 'user' => auth()->user()]);
    });
}