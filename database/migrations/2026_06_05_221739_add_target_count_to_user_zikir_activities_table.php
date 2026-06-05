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
            $table->integer('target_count')->nullable()->after('total_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('user_zikir_activities', function (Blueprint $table) {
            $table->dropColumn('target_count');
        });
    }
};
