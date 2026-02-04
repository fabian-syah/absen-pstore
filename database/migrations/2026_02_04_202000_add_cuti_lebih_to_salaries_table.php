<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('cuti_lebih_days')->default(0)->after('late_deduction');
            $table->decimal('cuti_lebih_deduction', 15, 2)->default(0)->after('cuti_lebih_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['cuti_lebih_days', 'cuti_lebih_deduction']);
        });
    }
};
