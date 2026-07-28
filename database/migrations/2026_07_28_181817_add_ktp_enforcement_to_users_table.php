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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('ktp_countdown_start_at')->nullable()->after('ktp_photo_path');
            $table->timestamp('ktp_unlock_at')->nullable()->after('ktp_countdown_start_at');
            $table->timestamp('ktp_congrats_until')->nullable()->after('ktp_unlock_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ktp_countdown_start_at', 'ktp_unlock_at', 'ktp_congrats_until']);
        });
    }
};
