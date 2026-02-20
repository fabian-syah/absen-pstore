<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fasting_logs', function (Blueprint $table) {
            $table->text('notes')->after('is_fasting')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fasting_logs', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
