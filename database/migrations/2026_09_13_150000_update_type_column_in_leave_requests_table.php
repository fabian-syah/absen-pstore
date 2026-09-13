<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah kolom 'type' menjadi VARCHAR(50) agar mendukung semua jenis izin & cuti:
        // 'sakit', 'izin', 'telat', 'cuti', 'wfh', 'libur', 'dinas'
        // Hal ini mengatasi error MySQL 1265 Data truncated for column 'type'
        DB::statement("ALTER TABLE `leave_requests` MODIFY COLUMN `type` VARCHAR(50) NOT NULL");

        // 2. Pastikan kolom pendukung seperti 'end_time' dan 'approved_by' ada
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `leave_requests` MODIFY COLUMN `type` ENUM('sakit', 'izin', 'telat') NOT NULL");

        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('leave_requests', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }
};
