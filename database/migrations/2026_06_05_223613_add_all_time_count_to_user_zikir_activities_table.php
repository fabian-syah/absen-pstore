<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('user_zikir_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('user_zikir_activities', 'all_time_count')) {
                $table->bigInteger('all_time_count')->default(0)->after('total_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_zikir_activities', function (Blueprint $table) {
            $table->dropColumn('all_time_count');
        });
    }
};
