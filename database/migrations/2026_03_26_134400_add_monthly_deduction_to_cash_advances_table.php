<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Menambahkan kolom potongan per bulan dan jumlah bulan cicilan
     */
    public function up(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->decimal('monthly_deduction', 15, 2)->nullable()->default(0)->after('amount')
                ->comment('Nominal potongan gaji per bulan');
            $table->integer('installment_months')->nullable()->default(0)->after('monthly_deduction')
                ->comment('Jumlah bulan cicilan yang direncanakan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->dropColumn(['monthly_deduction', 'installment_months']);
        });
    }
};
