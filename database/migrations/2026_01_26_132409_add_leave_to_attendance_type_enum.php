<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify ENUM to add 'leave' option
        DB::statement("ALTER TABLE `attendances` MODIFY COLUMN `attendance_type` ENUM('scan', 'self', 'manual', 'leave', 'system') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original ENUM (remove 'leave' and 'system')
        DB::statement("ALTER TABLE `attendances` MODIFY COLUMN `attendance_type` ENUM('scan', 'self', 'manual') DEFAULT NULL");
    }
};

