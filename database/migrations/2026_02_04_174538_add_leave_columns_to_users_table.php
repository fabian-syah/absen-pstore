<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('leave_balance')->default(10)->after('is_active');
            $table->integer('leave_taken')->default(0)->after('leave_balance');
            $table->integer('yearly_leave_limit')->default(10)->after('leave_taken');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['leave_balance', 'leave_taken', 'yearly_leave_limit']);
        });
    }
};
