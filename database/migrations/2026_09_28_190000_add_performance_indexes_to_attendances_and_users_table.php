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
        $indexes = [
            'attendances' => [
                'idx_att_check_in_time' => ['check_in_time'],
                'idx_att_user_checkin' => ['user_id', 'check_in_time'],
                'idx_att_branch_checkin' => ['branch_id', 'check_in_time'],
                'idx_att_status_checkin' => ['status', 'check_in_time'],
                'idx_att_presence_status' => ['presence_status'],
                'idx_att_is_late' => ['is_late_checkin'],
            ],
            'users' => [
                'idx_users_is_active' => ['is_active'],
                'idx_users_role' => ['role'],
                'idx_users_branch_active' => ['branch_id', 'is_active'],
            ],
        ];

        if (Schema::hasColumn('attendances', 'scanned_by_user_id')) {
            $indexes['attendances']['idx_att_scanner_checkin'] = ['scanned_by_user_id', 'check_in_time'];
        }
        if (Schema::hasColumn('attendances', 'scanned_out_by_user_id')) {
            $indexes['attendances']['idx_att_scanout_checkin'] = ['scanned_out_by_user_id', 'check_in_time'];
        }

        foreach ($indexes as $tableName => $tableIndexes) {
            foreach ($tableIndexes as $indexName => $columns) {
                try {
                    // Check if columns exist
                    $columnsExist = true;
                    foreach ((array) $columns as $col) {
                        if (!Schema::hasColumn($tableName, $col)) {
                            $columnsExist = false;
                            break;
                        }
                    }
                    if (!$columnsExist) {
                        continue;
                    }

                    // For MySQL, verify index doesn't already exist
                    if (DB::getDriverName() === 'mysql') {
                        $exists = DB::select(
                            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
                            [$tableName, $indexName]
                        );
                        if (!empty($exists)) {
                            continue;
                        }
                    }

                    Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                        $table->index($columns, $indexName);
                    });
                } catch (\Throwable $e) {
                    // Ignore if index already exists
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'attendances' => [
                'idx_att_check_in_time',
                'idx_att_user_checkin',
                'idx_att_branch_checkin',
                'idx_att_status_checkin',
                'idx_att_presence_status',
                'idx_att_is_late',
                'idx_att_scanner_checkin',
                'idx_att_scanout_checkin',
            ],
            'users' => [
                'idx_users_is_active',
                'idx_users_role',
                'idx_users_branch_active',
            ],
        ];

        foreach ($indexes as $tableName => $tableIndexes) {
            foreach ($tableIndexes as $indexName) {
                try {
                    if (DB::getDriverName() === 'mysql') {
                        $exists = DB::select(
                            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
                            [$tableName, $indexName]
                        );
                        if (empty($exists)) {
                            continue;
                        }
                    }

                    Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        }
    }
};
