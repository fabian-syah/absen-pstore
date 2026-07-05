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
    public function up()
    {
        Schema::table('employee_evaluations', function (Blueprint $table) {
            $table->date('evaluation_date')->nullable()->after('assessor_id');
        });

        \Illuminate\Support\Facades\DB::statement("UPDATE employee_evaluations SET evaluation_date = CONCAT(year, '-', LPAD(month, 2, '0'), '-01') WHERE evaluation_date IS NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_evaluations', function (Blueprint $table) {
            $table->dropColumn('evaluation_date');
        });
    }
};
