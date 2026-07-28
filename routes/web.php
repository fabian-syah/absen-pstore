<?php

use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AdminGajiUserController;
use App\Http\Controllers\AdminGajiMasterSalaryController;
use App\Http\Controllers\AdminGajiSalarySummaryController;
use App\Http\Controllers\BranchMessageController;
use App\Http\Controllers\BranchSalaryController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\EmploymentHistoryController;
use App\Http\Controllers\MySalaryController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SalarySummaryController;
use App\Models\User;
use App\Traits\SendFcmNotification;
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
use App\Http\Controllers\PushBroadcastController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryReturnController;
use App\Http\Controllers\AttendanceHistoryController;
use App\Http\Controllers\JobTargetController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\BranchInventoryController;
use App\Http\Controllers\AdminMonitoringController;
use App\Http\Controllers\BranchLeaderboardController;
use App\Http\Controllers\AttendanceSummaryController;
use App\Http\Controllers\CashAdvanceController; // <--- ADD THIS
// use App\Http\Controllers\RamadhanController;

use App\Http\Controllers\BonusController; // <--- ADD THIS
use App\Http\Controllers\AuditMonitoringController;
use App\Http\Controllers\EmployeeEvaluationController;


/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// === ROUTE FINGERPRINT DINONAKTIFKAN (BUG KEAMANAN: hardcoded user ID) ===
// Route::get('/fingerprint-login', [App\Http\Controllers\FingerprintAuthController::class, 'index'])->name('fingerprint.login');
// Route::post('/fingerprint-login/authenticate', [App\Http\Controllers\FingerprintAuthController::class, 'authenticate'])->name('fingerprint.authenticate');

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

    // --- Rute Dzikir Online ---
    Route::get('/dzikir', [App\Http\Controllers\DzikirController::class, 'index'])->name('dzikir.index');
    Route::get('/dzikir/list/{category}', [App\Http\Controllers\DzikirController::class, 'category'])->name('dzikir.list');
    Route::get('/dzikir/favorites', [App\Http\Controllers\DzikirController::class, 'favorites'])->name('dzikir.favorites');
    Route::post('/dzikir/toggle-favorite', [App\Http\Controllers\DzikirController::class, 'toggleFavorite'])->name('dzikir.toggle-favorite');
    Route::get('/dzikir/play/{category}/{id?}', [App\Http\Controllers\DzikirController::class, 'play'])->name('dzikir.play');
    Route::post('/dzikir/progress', [App\Http\Controllers\DzikirController::class, 'saveProgress'])->name('dzikir.progress');
    Route::post('/dzikir/update-target', [App\Http\Controllers\DzikirController::class, 'updateTarget'])->name('dzikir.update-target');
    Route::post('/dzikir/reset-progress', [App\Http\Controllers\DzikirController::class, 'resetProgress'])->name('dzikir.reset-progress');

    // Route Test Notifikasi
    Route::get('/test-notification', [DashboardController::class, 'testNotification'])->name('test.notification');
    
    Route::get('/test-push', function() {
        $user = auth()->user();
        $sender = new class { use \App\Traits\SendWebPushNotification; };
        $result = $sender->sendWebPushToUser($user, "Tes Berhasil", "Ini adalah notifikasi percobaan.");
        return response()->json(['result' => $result]);
    });

    Route::post('/test-push-all', function() {
        if (auth()->user()->role !== 'admin') abort(403);
        $sender = new class { use \App\Traits\SendWebPushNotification; };
        $results = $sender->sendWebPushToBranchRoles(
            ['admin', 'audit', 'leader', 'security', 'user_biasa', 'admin_gaji'],
            null,
            "🔔 Test Notifikasi",
            "Ini adalah notifikasi percobaan dari Admin. Jika Anda melihat ini, berarti push notification berfungsi!",
            url('/')
        );
        return response()->json(['results' => $results]);
    })->middleware('role:admin');

    // Route Sertifikat Penghargaan
    Route::get('/attendance-certificate', [App\Http\Controllers\CertificateController::class, 'show'])->name('certificate.attendance');

    Route::post('/update-fcm-token', [UserController::class, 'updateFcmToken'])->name('update.fcm.token');
    
    // Push Notifications (Web-Push VAPID)
    Route::post('/push-subscription', [App\Http\Controllers\PushSubscriptionController::class, 'update'])->name('push.subscribe');
    Route::delete('/push-subscription', [App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    Route::get('/kasbon/export', [App\Http\Controllers\CashAdvanceController::class, 'export'])->name('kasbon.export');
    Route::post('/kasbon/bulk-approve', [CashAdvanceController::class, 'bulkApprove'])->name('kasbon.bulk-approve'); // <--- NEW ROUTE

    // --- Rute Search Global ---
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    Route::get('/salary-summary', [SalarySummaryController::class, 'index'])->name('salary-summary.index');

    // [FIXED] Order is important! Export must be before {id}
    Route::get('/my-salary/export', [MySalaryController::class, 'export'])->name('my-salary.export');
    Route::get('/my-salary', [MySalaryController::class, 'index'])->name('my-salary.index');
    Route::get('/my-salary/{id}', [MySalaryController::class, 'show'])->name('my-salary.show');

    Route::get('/chat/branches', [BranchMessageController::class, 'getBranchList'])->name('chat.branches');
    // Get Messages (Butuh parameter ?branch_id=...)
    Route::get('/branch-messages', [BranchMessageController::class, 'index'])->name('messages.index');
    // Send Message
    Route::post('/branch-messages', [BranchMessageController::class, 'store'])->name('messages.store');

    Route::post('/attendance/{id}/confirm-overtime', [DashboardController::class, 'confirmOvertime'])->name('attendance.confirm-overtime');

    // === DOWNLOAD QR CODE SEBAGAI PDF ===
    Route::get('/my-qrcode-pdf', [DashboardController::class, 'downloadQrPdf'])->name('qrcode.download');
    // Admin Download User QR
    Route::get('/users/{user}/qrcode-pdf', [UserController::class, 'downloadQrPdf'])->name('users.download-qr-pdf');

    Route::get('/my-wrapped-2025', [App\Http\Controllers\AttendanceRecapController::class, 'index'])->name('attendance.recap');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // Voice Notes
        // Route::get('/voice-notes', [App\Http\Controllers\VoiceNoteController::class, 'index'])->name('voice-notes.index');

        // Halaman Utama Koreksi
        Route::get('/correction', [AttendanceCorrectionController::class, 'index'])->name('correction.index');

        // Reset Jam Pulang (Hanya hapus jam checkout)
        Route::patch('/correction/{id}/reset-checkout', [AttendanceCorrectionController::class, 'resetCheckout'])->name('correction.reset-checkout');

        // Hapus Permanen (Hapus baris data)
        Route::delete('/correction/{id}', [AttendanceCorrectionController::class, 'destroy'])->name('correction.destroy');

        // Monitoring Edit Audit
        Route::get('/audit-monitor', [AuditMonitoringController::class, 'index'])->name('audit-monitor.index');
        Route::delete('/audit-monitor/{id}/revert', [AuditMonitoringController::class, 'revert'])->name('audit-monitor.revert');

        // Artisan Command Web GUI
        Route::get('/artisan', [App\Http\Controllers\ArtisanDashboardController::class, 'index'])->name('artisan.index');
        Route::post('/artisan/run', [App\Http\Controllers\ArtisanDashboardController::class, 'run'])->name('artisan.run');

        // Admin Dzikir
        Route::get('/dzikir-stats', [App\Http\Controllers\AdminDzikirController::class, 'stats'])->name('dzikir.stats');
        Route::resource('dzikir', App\Http\Controllers\AdminDzikirController::class);

        // Admin Dzikir Campaign
        Route::resource('dzikir-campaign', App\Http\Controllers\AdminZikirCampaignController::class);
    });

    // === RUTE RIWAYAT KARIR & MUTASI (Full Resource kecuali show) ===
    // Akses role (Admin, Audit, Leader, User Biasa, Security) sudah diatur di dalam Controller
    Route::resource('employment-history', EmploymentHistoryController::class)
        ->except(['show']);

    // === RUTE RIWAYAT ABSENSI ===
    Route::get('/riwayat-absensi', [AttendanceHistoryController::class, 'index'])->name('attendance.history');

    // === RUTE RINGKASAN TAHUNAN (PENGGANTI RECAP) ===
    // 1. Versi Saya Sendiri (Tanpa ID)
    Route::get('/ringkasan-tahunan', [AttendanceSummaryController::class, 'index'])->name('attendance.summary');
    // 2. Versi Lihat Orang Lain (Dengan ID) - [NEW]
    Route::get('/ringkasan-tahunan/{user_id}', [AttendanceSummaryController::class, 'index'])->name('attendance.summary.user')
        ->middleware('role:admin,audit,leader,admin_gaji');

    Route::get('/attendance/export-pdf', [AttendanceHistoryController::class, 'exportPdf'])->name('attendance.export.pdf');

    // === RUTE JOB TARGETS ===
    Route::get('/job-targets', [JobTargetController::class, 'index'])->name('job-targets.index');
    Route::get('/job-targets/create', [JobTargetController::class, 'create'])->name('job-targets.create');
    Route::post('/job-targets', [JobTargetController::class, 'store'])->name('job-targets.store');

    // --- TAMBAHKAN 2 BARIS INI ---
    Route::get('/job-targets/{id}/edit', [JobTargetController::class, 'edit'])->name('job-targets.edit');
    Route::put('/job-targets/{id}', [JobTargetController::class, 'update'])->name('job-targets.update');
    // -----------------------------

    Route::patch('/job-targets/{id}/update-outcome', [JobTargetController::class, 'updateOutcome'])->name('job-targets.update-outcome');
    Route::patch('/job-targets/{id}/toggle', [JobTargetController::class, 'toggleStatus'])->name('job-targets.toggle');
    Route::delete('/job-targets/{id}', [JobTargetController::class, 'destroy'])->name('job-targets.destroy');
    Route::put('/job-targets/{id}/admin-status', [JobTargetController::class, 'adminUpdateStatus'])
        ->name('job-targets.admin-status');

    // === RUTE RIWAYAT PELANGGARAN ===
    // 1. Route History (Ditaruh sebelum resource/index agar tidak tertimpa)
    Route::get('/violations/history', [App\Http\Controllers\ViolationController::class, 'history'])->name('violations.history');

    // 2. Route Index (Aktif)
    Route::get('/violations', [App\Http\Controllers\ViolationController::class, 'index'])->name('violations.index');

    // 3. Route CRUD (Admin & Audit)
    Route::middleware(['role:admin,audit,admin_gaji'])->group(function () {
        Route::get('/violations/create', [App\Http\Controllers\ViolationController::class, 'create'])->name('violations.create');
        Route::post('/violations', [App\Http\Controllers\ViolationController::class, 'store'])->name('violations.store');
        Route::get('/violations/{violation}/edit', [App\Http\Controllers\ViolationController::class, 'edit'])->name('violations.edit');
        Route::put('/violations/{violation}', [App\Http\Controllers\ViolationController::class, 'update'])->name('violations.update');
        Route::delete('/violations/{violation}', [App\Http\Controllers\ViolationController::class, 'destroy'])->name('violations.destroy');
        // Tambahkan Route ini di dalam group middleware violation
        Route::put('/violations/{violation}/resolve', [App\Http\Controllers\ViolationController::class, 'resolve'])->name('violations.resolve');
    });

    Route::patch('/job-targets/{id}/toggle', [JobTargetController::class, 'toggleStatus'])->name('job-targets.toggle');
    Route::delete('/job-targets/{id}', [JobTargetController::class, 'destroy'])->name('job-targets.destroy');

    // === RUTE KASBON ===
    Route::middleware(['role:admin,audit,security,user_biasa,admin_gaji,leader'])->prefix('kasbon')->name('kasbon.')->group(function () {
        Route::get('/', [App\Http\Controllers\CashAdvanceController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\CashAdvanceController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CashAdvanceController::class, 'store'])->name('store');
        Route::get('/{id}/detail', [App\Http\Controllers\CashAdvanceController::class, 'show'])->name('show');

        // Route Bayar Cicilan
        Route::post('/{id}/cicil', [App\Http\Controllers\CashAdvanceController::class, 'storeInstallment'])->name('pay');

        // Route Kalender Kasbon
        Route::get('/kalender', [App\Http\Controllers\CashAdvanceController::class, 'calendar'])->name('calendar');
        Route::get('/kalender/data', [App\Http\Controllers\CashAdvanceController::class, 'calendarData'])->name('calendar.data');

        // ROUTE EXPORT
        Route::get('/export', [App\Http\Controllers\CashAdvanceController::class, 'export'])->name('export');

        // Middleware Khusus Admin & Admin Gaji
        Route::middleware(['role:admin,admin_gaji'])->group(function () {
            Route::delete('/{id}', [App\Http\Controllers\CashAdvanceController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/status', [App\Http\Controllers\CashAdvanceController::class, 'updateStatus'])->name('status');

            Route::post('/installment/{id}/approve', [App\Http\Controllers\CashAdvanceController::class, 'approveInstallment'])->name('installment.approve');
            Route::post('/installment/{id}/reject', [App\Http\Controllers\CashAdvanceController::class, 'rejectInstallment'])->name('installment.reject');
            Route::get('/installment/{id}/edit', [App\Http\Controllers\CashAdvanceController::class, 'editInstallment'])->name('installment.edit');
            Route::put('/installment/{id}', [App\Http\Controllers\CashAdvanceController::class, 'updateInstallment'])->name('installment.update');
            Route::delete('/installment/{id}', [App\Http\Controllers\CashAdvanceController::class, 'destroyInstallment'])->name('installment.destroy');

            // [FIXED] Hapus 'kasbon.' karena sudah ada di grup utama. Nama akhirnya jadi 'kasbon.verification'
            Route::get('/verifikasi-pembayaran', [App\Http\Controllers\CashAdvanceController::class, 'incomingInstallments'])->name('verification');
        });
    });

    // Rute Khusus Riwayat Pribadi
    Route::get('/riwayat-izin-saya', [LeaveRequestController::class, 'personalHistory'])
        ->name('leave-requests.personal-history');

    // Test FCM Route
    Route::get('/test-fcm', function () {
        $sender = new class {
            use SendFcmNotification;
        };
        $branchId = 2;
        try {
            $sender->sendNotificationToBranchRoles(['audit'], $branchId, "Tes Notifikasi", "Pesan tes server.");
            return "Perintah kirim dijalankan.";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    // === RUTE BROADCAST ===
    Route::prefix('broadcast')->name('broadcast.')->group(function () {
        Route::post('/{broadcast}/mark-read', [BroadcastController::class, 'markAsRead'])->name('mark-read');
        Route::get('/{broadcast}', [BroadcastController::class, 'show'])->name('show');
        
        // Rute API notifikasi (di luar middleware admin agar bisa diakses semua)
        Route::get('/api/notifications', [BroadcastController::class, 'getNotifications'])->name('notifications');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/', [BroadcastController::class, 'index'])->name('index');
            Route::get('/create', [BroadcastController::class, 'create'])->name('create');
            Route::post('/', [BroadcastController::class, 'store'])->name('store');
            Route::get('/{broadcast}/edit', [BroadcastController::class, 'edit'])->name('edit');
            Route::put('/{broadcast}', [BroadcastController::class, 'update'])->name('update');
            Route::delete('/{broadcast}', [BroadcastController::class, 'destroy'])->name('destroy');
        });
        
    });

    // === RUTE PUSH NOTIFICATION BROADCAST (ADMIN ONLY) ===
    Route::prefix('push-broadcast')->name('push-broadcast.')->middleware('role:admin')->group(function () {
        Route::get('/', [PushBroadcastController::class, 'create'])->name('create');
        Route::post('/send', [PushBroadcastController::class, 'send'])->name('send');
        Route::get('/result', [PushBroadcastController::class, 'result'])->name('result');
    });

    // === RUTE WORK SCHEDULES ===
    Route::prefix('work-schedules')->name('work-schedules.')->middleware('role:admin,audit')->group(function () {
        Route::get('/', [WorkScheduleController::class, 'index'])->name('index');
        Route::get('/create', [WorkScheduleController::class, 'create'])->name('create');
        Route::post('/', [WorkScheduleController::class, 'store'])->name('store');
        Route::get('/{workSchedule}/edit', [WorkScheduleController::class, 'edit'])->name('edit');
        Route::put('/{workSchedule}', [WorkScheduleController::class, 'update'])->name('update');
        Route::delete('/{workSchedule}', [WorkScheduleController::class, 'destroy'])->name('destroy');
    });

    // === RUTE PROFILE ===
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');

        Route::delete('/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::put('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
        Route::post('/photo/request', [ProfileController::class, 'requestPhotoChange'])->name('photo.request');
        Route::get('/photo/{user}', [ProfileController::class, 'getProfilePhoto'])->name('photo.get');
        Route::put('/ktp', [ProfileController::class, 'updateKtp'])->name('ktp.update');
        Route::post('/ktp/request', [ProfileController::class, 'requestKtpChange'])->name('ktp.request');
        Route::get('/ktp/{user}', [ProfileController::class, 'getKtpPhoto'])->name('ktp.get');

        Route::post('/work-history', [WorkHistoryController::class, 'store'])->name('work-history.store');
        Route::delete('/work-history/{history}', [WorkHistoryController::class, 'destroy'])->name('work-history.destroy');

        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::get('/inventory', [InventoryController::class, 'showInventory'])->name('inventory.index');
    });

    // ==========================================================
    //  RUTE INVENTARIS
    // ==========================================================

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');

        Route::get('/available', [InventoryController::class, 'available'])
            ->name('available')
            ->middleware('role:admin,audit,leader,security,user_biasa,admin_gaji');

        Route::get('/detail/{inventory}', [InventoryController::class, 'show'])->name('show');

        Route::middleware(['role:admin,audit,leader,security,user_biasa,admin_gaji'])->group(function () {
            Route::get('/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::post('/{id}/return', [InventoryReturnController::class, 'store'])->name('process-return');
        });

        Route::middleware(['role:admin,admin_gaji'])->group(function () {
            Route::get('/all-data', [InventoryController::class, 'adminIndex'])->name('admin.all');
            Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
        });
    });

    // ================= RIWAYAT PENGEMBALIAN (Inventory Returns) =================
    Route::middleware(['role:admin,audit,admin_gaji'])->group(function () {
        Route::get('/inventory-returns', [InventoryReturnController::class, 'index'])->name('inventory-returns.index');
        Route::post('/inventory-returns/{id}/approve', [InventoryReturnController::class, 'approve'])->name('inventory-returns.approve');
        Route::post('/inventory-returns/{id}/reject', [InventoryReturnController::class, 'reject'])->name('inventory-returns.reject');
    });

    // ==========================================================
    //  RUTE MONITORING HARIAN (KHUSUS ADMIN)
    // ==========================================================
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/monitoring/daily-attendance', [AdminMonitoringController::class, 'dailyAttendance'])
            ->name('admin.monitoring.daily');
    });

    // ==========================================================
    //  MANAJEMEN GAJI (SALARY) - URUTAN PENTING!
    // ==========================================================

    // 1. GRUP EKSEKUTOR (KHUSUS ADMIN_GAJI) - WAJIB DI ATAS!
    // Agar /salaries/create terbaca duluan sebelum /salaries/{salary}
    Route::middleware(['auth', 'role:admin_gaji'])->group(function () {

        Route::get('/master-gaji', [EmployeeSalaryController::class, 'index'])->name('employee-salaries.index');
        Route::get('/master-gaji-non-karyawan', [AdminGajiMasterSalaryController::class, 'index'])->name('admin-gaji.employee-salaries.index');
        Route::get('/employee-salaries/export', [App\Http\Controllers\EmployeeSalaryController::class, 'export'])->name('employee-salaries.export');
        Route::get('/master-gaji-non-karyawan/{userId}/edit', [AdminGajiMasterSalaryController::class, 'edit'])->name('admin-gaji.employee-salaries.edit');
        Route::put('/master-gaji-non-karyawan/{userId}', [AdminGajiMasterSalaryController::class, 'update'])->name('admin-gaji.employee-salaries.update');
        Route::get('/master-gaji/{id}/edit', [EmployeeSalaryController::class, 'edit'])->name('employee-salaries.edit');
        Route::put('/master-gaji/{id}', [EmployeeSalaryController::class, 'update'])->name('employee-salaries.update');
        Route::get('/salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
        Route::post('/salaries', [SalaryController::class, 'store'])->name('salaries.store');
        Route::get('/salaries/{salary}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
        Route::put('/salaries/{salary}', [SalaryController::class, 'update'])->name('salaries.update');
        Route::delete('/salaries/{salary}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
        Route::get('/salaries/{id}/cetak', [SalaryController::class, 'cetak'])->name('salaries.cetak');
        Route::get('/salaries/{id}/cetak-thermal', [SalaryController::class, 'cetakThermal'])->name('salaries.cetakThermal');

        // Bonus & THR
        Route::get('/bonuses/create', [BonusController::class, 'create'])->name('bonuses.create');
        Route::post('/bonuses', [BonusController::class, 'store'])->name('bonuses.store');

        // RUTE GAJI PER CABANG
        Route::get('/gaji-cabang', [BranchSalaryController::class, 'index'])->name('branch-salary.index');
        Route::get('/gaji-cabang/{id}', [BranchSalaryController::class, 'show'])->name('branch-salary.show');

        // ADMIN GAJI USER DATA
        Route::get('/admin-gaji/users', [\App\Http\Controllers\AdminGajiUserController::class, 'index'])->name('admin-gaji.users.index');
        Route::post('/admin-gaji/users', [\App\Http\Controllers\AdminGajiUserController::class, 'store'])->name('admin-gaji.users.store');
        Route::put('/admin-gaji/users/{id}', [\App\Http\Controllers\AdminGajiUserController::class, 'update'])->name('admin-gaji.users.update');
        Route::delete('/admin-gaji/users/{id}', [\App\Http\Controllers\AdminGajiUserController::class, 'destroy'])->name('admin-gaji.users.destroy');

        Route::get('/admin-gaji/salary-summary', [AdminGajiSalarySummaryController::class, 'index'])->name('admin-gaji.salary-summary');
        // API Helper
        Route::get('/api/check-attendance', [SalaryController::class, 'checkAttendance'])->name('api.check-attendance');
    });

    // 2. GRUP VIEW ONLY (Bisa Diakses ADMIN & ADMIN_GAJI) - TARUH DI BAWAH
    Route::middleware(['auth', 'role:admin,admin_gaji'])->group(function () {
        Route::get('/salaries', [SalaryController::class, 'index'])->name('salaries.index');

        Route::patch('/salaries/{salary}/toggle-payment-method', [SalaryController::class, 'togglePaymentMethod'])->name('salaries.toggle-payment-method');

        // Route SHOW menangkap semua ID, jadi harus ditaruh paling bawah di section salary
        Route::get('/salaries/{salary}', [SalaryController::class, 'show'])->name('salaries.show');
    });
    // ====================================================================================================
    //  [PERBAIKAN] GRUP 1: ADMIN, AUDIT, & ADMIN GAJI MANAGEMENT (PINDAHKAN KE ATAS!)
    //  Agar route seperti /users/create dibaca LEBIH DULU daripada /users/{user}
    // ====================================================================================================
    Route::middleware(['role:admin,audit,admin_gaji'])->group(function () {
        Route::get('/all-attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.all');
        Route::put('/audit/verify-attendance/{id}', [App\Http\Controllers\AuditController::class, 'verifyAttendance'])->name('audit.verify.attendance');
        Route::put('/attendance/{id}/audit-update', [AttendanceHistoryController::class, 'updateByAudit'])->name('audit.update.attendance');
        // Route untuk input manual absensi baru oleh Audit (untuk tanggal Alpha/Kosong)
        Route::post('/audit/attendance/store', [App\Http\Controllers\AuditController::class, 'storeByAudit'])->name('audit.store.attendance');

        Route::get('/branches/{branch}/export-excel', [BranchController::class, 'exportBranchExcel'])->name('branches.export.excel');
        Route::get('/branches/{branch}/export-pdf', [BranchController::class, 'exportBranchPdf'])->name('branches.export.pdf');

        Route::resource('branches', BranchController::class);
        Route::post('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');

        Route::resource('divisions', DivisionController::class);
        Route::post('/divisions/{division}/toggle-status', [DivisionController::class, 'toggleStatus'])->name('divisions.toggle-status');

        // Approval Requests (Pastikan ini juga di atas Resource User)
        Route::get('/users/photo-requests', [UserController::class, 'photoRequests'])->name('users.photo-requests');
        Route::patch('/users/{user}/approve-photo', [UserController::class, 'approvePhotoRequest'])->name('users.approve-photo');
        Route::delete('/users/{user}/reject-photo', [UserController::class, 'rejectPhotoRequest'])->name('users.reject-photo');

        Route::get('/users/ktp-requests', [UserController::class, 'ktpRequests'])->name('users.ktp-requests');
        Route::patch('/users/{user}/approve-ktp', [UserController::class, 'approveKtpRequest'])->name('users.approve-ktp');
        Route::patch('/users/{user}/reject-ktp', [UserController::class, 'rejectKtpRequest'])->name('users.reject-ktp');

        // MONITOR UPLOAD DOKUMEN (ADMIN ONLY)
        Route::get('/users/document-uploads', [UserController::class, 'documentUploads'])->name('users.document-uploads');

        // USER MANAGEMENT (Resource tanpa show)
        // INI AKAN MEMBUAT ROUTE: GET /users/create
        Route::resource('users', UserController::class)->except(['show']);

        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('/users/{user}/verify', [UserController::class, 'verifyUser'])->name('users.verify');
        Route::patch('/users/{user}/unlock-ktp', [UserController::class, 'unlockKtpAbsensi'])->name('users.unlock-ktp');

        // VERIFIKASI ABSENSI
        Route::prefix('verifikasi')->name('audit.')->group(function () {
            Route::get('/absensi', [AuditController::class, 'showVerificationList'])->name('verify.list');
            Route::get('/absensi/single', [AuditController::class, 'showSingleVerification'])->name('verify.single');
            Route::get('/absensi/ditolak', [AuditController::class, 'showRejectedVerificationList'])->name('verify.rejected.list');
            Route::put('/setujui/{attendance}', [AuditController::class, 'approve'])->name('approve');
            Route::put('/tolak/{attendance}', [AuditController::class, 'reject'])->name('reject');
            Route::get('/laporan', [AuditController::class, 'showReports'])->name('reports');
        });

        // IZIN TELAT
        Route::get('/leave-requests', [AuditController::class, 'showLatePermissions'])->name('leave-requests.index');
        Route::get('/izin-telat/riwayat', [AuditController::class, 'showLatePermissionsHistory'])->name('audit.late.history');
        Route::get('/izin-telat/riwayat-ditolak', [AuditController::class, 'showRejectedLatePermissionsHistory'])->name('audit.late.rejected.history');
        Route::post('/izin-telat/{id}/approve', [AuditController::class, 'approveLatePermission'])->name('late.approve');
        Route::post('/izin-telat/{id}/reject', [AuditController::class, 'rejectLatePermission'])->name('late.reject');

        Route::get('/audit/missed-checkouts', [AuditController::class, 'showMissedCheckouts'])->name('audit.missed-checkout.list');
        Route::put('/audit/missed-checkouts/{id}', [AuditController::class, 'updateMissedCheckout'])->name('audit.missed-checkout.update');
    });

    // ====================================================================================================
    //  [PERBAIKAN] GRUP 2: SHOW USER (WILDCARD /users/{user}) (PINDAHKAN KE BAWAH!)
    //  Ini menangkap semua URL /users/something. Jadi harus paling bawah.
    // ====================================================================================================
    Route::middleware(['role:admin,audit,admin_gaji,leader'])->group(function () {
        // Leader sekarang diizinkan mengakses halaman detail user (show)
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/admin-photo', [UserController::class, 'adminUpdatePhotos'])->name('users.admin-photo');
    });

    // === RUTE SECURITY ===
    Route::middleware(['role:security,admin'])->prefix('security')->name('security.')->group(function () {

        Route::get('/scan', [ScanController::class, 'index'])->name('scan');
        Route::post('/check-user', [ScanController::class, 'checkUser'])->name('check-user');
        Route::post('/store-attendance', [ScanController::class, 'storeAttendance'])->name('store-attendance');
        Route::get('/stats', [ScanController::class, 'getStats'])->name('stats');
        Route::get('/riwayat-scan', [ScanController::class, 'history'])->name('history');

        Route::post('/panic', [ScanController::class, 'sendPanicMessage'])->name('panic');
        Route::get('/user-notes/{id}', [ScanController::class, 'getUserNotes'])->name('user-notes');

        Route::get('/attendance-log', [ScanController::class, 'attendanceLog'])->name('attendance-log');
        Route::get('/today-attendance', [ScanController::class, 'todayAttendance'])->name('today-attendance');
    });

    // === RUTE HADIAH LEADERBOARD ===
    Route::post('/leaderboard/claim-prize', [DashboardController::class, 'claimPrize'])->name('leaderboard.claim-prize');

    // === RUTE TEAM MANAGEMENT ===
    Route::middleware(['role:user_biasa,leader,audit,security,admin,admin_gaji'])->group(function () {
        Route::get('/kalender-tim', [TeamController::class, 'calendar'])->name('team.calendar');
        Route::get('/tim-saya', [TeamController::class, 'index'])->name('team.index');
        Route::get('/tim-saya/{user}', [TeamController::class, 'show'])->name('my.team.show');
        Route::get('/tim-saya/attendance/{user}', [TeamController::class, 'attendance'])->name('my.team.attendance');
        Route::get('/team/branch/{id}', [TeamController::class, 'showBranch'])->name('team.branch.detail');
        Route::get('/team/branch/{branchId}/employee/{employeeId}/history', [TeamController::class, 'showEmployeeHistory'])->name('team.branch.employee.history');
    });

    // === RUTE CABANG SAYA ===
    Route::middleware(['role:admin,audit,leader'])->group(function () {
        Route::get('/cabang-saya', [TeamController::class, 'myBranches'])->name('team.my-branches');
    });

    // === RUTE SELF ATTENDANCE ===
    Route::middleware(['role:user_biasa,leader,audit,security'])->prefix('absen-mandiri')->name('self.attend.')->group(function () {
        Route::get('/', [SelfAttendanceController::class, 'create'])->name('create');
        Route::post('/', [SelfAttendanceController::class, 'store'])->name('store');
        Route::get('/history', [SelfAttendanceController::class, 'history'])->name('history');
        Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');
        Route::post('/skip-checkout/{id}', [SelfAttendanceController::class, 'skipCheckOut'])->name('skip');
        Route::post('/manual-checkout', [SelfAttendanceController::class, 'manualCheckOut'])->name('manual-checkout');
    });

    // === RUTE LEAVE REQUESTS ===
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {

        // [NEW] Monitoring Cuti (Admin, Audit, Admin Gaji)
        Route::get('/monitoring', [LeaveRequestController::class, 'adminSummary'])
            ->name('admin-summary')
            ->middleware('role:admin,admin_gaji,audit');

        // Pastikan middleware mencakup: user_biasa, leader, audit, security, admin
        Route::middleware(['role:user_biasa,leader,audit,security,admin,admin_gaji'])->group(function () {
            // [NEW] Riwayat Cuti
            Route::get('/riwayat-cuti', [LeaveRequestController::class, 'cutiHistory'])->name('cuti-history');

            // [NEW] Form Cuti Terpisah
            Route::get('/create-cuti', [LeaveRequestController::class, 'createCuti'])->name('create-cuti');

            // [NEW] Approval Cuti Only (Khusus Approver)
            Route::get('/approvals-cuti', [LeaveRequestController::class, 'approvalCuti'])
                ->name('approvals')
                ->middleware('role:admin,admin_gaji,audit,leader');

            // [NEW] Monitoring User Aktif Cuti (Admin, Audit, Admin Gaji)
            Route::middleware(['role:admin,admin_gaji,audit'])->group(function () {
                Route::get('/active', [LeaveRequestController::class, 'activeLeaves'])->name('active');
                Route::delete('/{leaveRequest}/destroy-approved', [LeaveRequestController::class, 'destroyApproved'])->name('destroy-approved');
                Route::patch('/{leaveRequest}/finish-early-admin', [LeaveRequestController::class, 'finishEarlyAdmin'])->name('finish-early-admin');
            });

            Route::get('/pengajuan-saya', [LeaveRequestController::class, 'myRequests'])->name('my-requests');
            Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
            Route::post('/store', [LeaveRequestController::class, 'store'])->name('store');

            Route::patch('/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');

            // PERBAIKAN DISINI: Ubah 'finish-early' menjadi 'finishEarly'
            Route::patch('/{leaveRequest}/finish-early', [LeaveRequestController::class, 'finishEarly'])->name('finish-early');
        });
    });

    // === RUTE LAPORAN & ANALYTICS ===
    Route::middleware(['role:admin,audit,leader'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/attendance', [AuditController::class, 'attendanceReport'])->name('attendance');
        Route::get('/performance', [AuditController::class, 'performanceReport'])->name('performance');
        Route::get('/leave', [AuditController::class, 'leaveReport'])->name('leave');
        Route::get('/export/attendance', [AuditController::class, 'exportAttendance'])->name('export.attendance');
    });

    // === RUTE ADMIN DOWNLOAD DATA KTP (PDF) ===
    Route::middleware(['role:admin'])->prefix('admin/ktp')->name('admin.ktp.')->group(function () {
        Route::get('/download-pdf', [App\Http\Controllers\AdminKtpController::class, 'downloadPdf'])->name('download-pdf');
    });

    // ==========================================================
    //  RUTE MONITORING WILAYAH (INVENTARIS CABANG & LEADERBOARD)
    // ==========================================================
    Route::get('/employee-evaluations/my-history', [EmployeeEvaluationController::class, 'myHistory'])->name('employee-evaluations.my-history');
    Route::get('/employee-evaluations/{user_id}/export-pdf', [EmployeeEvaluationController::class, 'exportPdf'])->name('employee-evaluations.export-pdf');
    Route::get('/employee-evaluations/{user_id}/form', [EmployeeEvaluationController::class, 'form'])->name('employee-evaluations.form');

    Route::middleware(['role:admin,audit,leader,admin_gaji'])->group(function () {
        // === RUTE RAPOR KARYAWAN ===
        Route::get('/employee-evaluations', [EmployeeEvaluationController::class, 'index'])->name('employee-evaluations.index');
        Route::get('/employee-evaluations/history', [EmployeeEvaluationController::class, 'history'])->name('employee-evaluations.history');
        Route::get('/employee-evaluations/branch/{id}', [EmployeeEvaluationController::class, 'branchEmployees'])->name('employee-evaluations.branch-employees');
        Route::get('/employee-evaluations/branch/{id}/export-pdf', [EmployeeEvaluationController::class, 'exportBranchPdf'])->name('employee-evaluations.export-branch-pdf');
        Route::post('/employee-evaluations/{user_id}', [EmployeeEvaluationController::class, 'store'])->name('employee-evaluations.store');

        Route::get('/inventaris-cabang', [BranchInventoryController::class, 'index'])
            ->name('inventory.branches');

        Route::get('/inventaris-cabang/{id}', [BranchInventoryController::class, 'show'])
            ->name('inventory.branch.detail');

        Route::get('/inventaris-cabang/{id}/export', [InventoryController::class, 'exportBranchInventory'])
            ->name('inventory.branch.export')
            ->middleware('role:admin,admin_gaji');

        Route::get('/inventory/export/active', [InventoryController::class, 'exportAllActive'])
            ->name('inventory.export.active')
            ->middleware('role:admin,admin_gaji');

        Route::get('/inventory/export/pusat', [InventoryController::class, 'exportPusat'])
            ->name('inventory.export.pusat')
            ->middleware('role:admin,admin_gaji');

        Route::get('/inventory/export/cabang', [InventoryController::class, 'exportCabang'])
            ->name('inventory.export.cabang')
            ->middleware('role:admin,admin_gaji');

        Route::get('/top-absensi-cabang', [BranchLeaderboardController::class, 'index'])
            ->name('branch-leaderboard.index');

        Route::get('/top-absensi-cabang/{id}', [BranchLeaderboardController::class, 'show'])
            ->name('branch-leaderboard.show');

        Route::get('/branch-targets', [App\Http\Controllers\BranchTargetController::class, 'index'])->name('branch-targets.index');
        Route::get('/branch-targets/{id}', [App\Http\Controllers\BranchTargetController::class, 'show'])->name('branch-targets.show');
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

    Route::get('/bersihkan-alpha-salah', function () {
        $tanggalSalah = '2026-01-05';

        // Menghapus SEMUA status Alpha pada tanggal tersebut
        $deleted = \App\Models\Attendance::whereDate('check_in_time', $tanggalSalah)
            ->where('presence_status', 'Alpha')
            ->delete();

        return "Berhasil menghapus $deleted data Alpha pada tanggal $tanggalSalah. Silakan minta karyawan refresh aplikasinya.";
    });

    Route::get('/fix-absen-26', function () {
        try {
            $izin = \App\Models\LeaveRequest::where('type', 'telat')
                ->whereDate('start_date', '2025-12-26')
                ->where('status', 'approved')
                ->get();

            if ($izin->isEmpty()) {
                return "Tidak ada data izin telat.";
            }

            $count = 0;
            foreach ($izin as $i) {
                $user = \App\Models\User::find($i->user_id);

                \App\Models\Attendance::updateOrCreate(
                    [
                        'user_id' => $i->user_id,
                        'check_in_time' => \Carbon\Carbon::parse($i->start_date)->format('Y-m-d') . ' ' . ($i->start_time ?? '08:00:00')
                    ],
                    [
                        'branch_id' => $user->branch_id,
                        'presence_status' => 'Masuk',
                        'status' => 'verified',
                        'is_late_checkin' => true,
                        'notes' => 'Sinkronisasi Izin Telat (System Fix)',
                        'attendance_type' => 'self',
                        'verified_by_user_id' => $i->approved_by,
                        'scheduled_check_in' => $user->check_in_start ?? '08:00:00',
                        'scheduled_check_out' => $user->check_out_start ?? '17:00:00',
                        // TAMBAHKAN BARIS INI UNTUK MEMUNCULKAN FOTO
                        'photo_path' => $i->file_proof
                    ]
                );
                $count++;
            }
            return "Berhasil memperbarui " . $count . " data beserta foto.";
        } catch (\Exception $e) {
            return "Gagal! Error: " . $e->getMessage();
        }
    });

    Route::get('/fix-balance', function () {
        // Recalculate leave balance for ALL users based on approved cuti requests (CURRENT YEAR ONLY)
        $users = \App\Models\User::all();
        $fixed = 0;
        $currentYear = now()->year; // e.g., 2026

        foreach ($users as $u) {
            // [BARU] Update yearly_leave_limit ke 12 untuk semua user
            $u->yearly_leave_limit = 12;

            // Hitung total hari cuti yang DISETUJUI di TAHUN INI saja
            $approvedCutiDays = \App\Models\LeaveRequest::where('user_id', $u->id)
                ->where('type', 'cuti')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear) // Hanya tahun ini
                ->get()
                ->sum(function ($req) {
                    $start = \Carbon\Carbon::parse($req->start_date);
                    $end = $req->end_date ? \Carbon\Carbon::parse($req->end_date) : $start;
                    return $start->diffInDays($end) + 1;
                });

            $oldTaken = $u->leave_taken;
            $u->leave_taken = $approvedCutiDays;
            $u->leave_balance = 12 - $approvedCutiDays; // Pakai 12 langsung
            $u->save();

            if ($oldTaken != $approvedCutiDays || $u->wasChanged('yearly_leave_limit')) {
                $fixed++;
            }
        }

        return "Berhasil update jatah cuti ke 12 hari dan memperbaiki saldo tahun {$currentYear} untuk semua user. Silakan refresh halaman Monitoring Cuti.";
    });

    Route::fallback(function () {
        abort(404);
    });
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

Route::get('/hapus-gaji-jcs', function () {
    $deleted = \App\Models\Salary::whereHas('user', function ($q) {
        $q->where('login_id', 'jcs');
    })->where('month', '12')->where('year', '2025')->delete();

    return "Berhasil dihapus! Jumlah data yang terhapus: " . $deleted;
});

if (app()->environment('local')) {
    Route::get('/debug-session', function () {
        return response()->json(['session' => session()->all(), 'user' => auth()->user()]);
    });

}
