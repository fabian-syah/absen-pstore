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
        Schema::table('employee_evaluations', function (Blueprint $table) {
            $table->text('final_remark')->nullable()->after('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_evaluations', function (Blueprint $table) {
            $table->dropColumn('final_remark');
        });
    }
};
