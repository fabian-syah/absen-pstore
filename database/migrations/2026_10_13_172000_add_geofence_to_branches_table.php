<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom geofencing (latitude, longitude, radius) pada tabel branches secara aman.
     * Default nullable agar data produksi 1 tahun tidak terganggu.
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
            }
            if (!Schema::hasColumn('branches', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('branches', 'radius')) {
                $table->unsignedInteger('radius')->nullable()->default(50)->comment('Radius absensi dalam meter')->after('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'radius')) {
                $table->dropColumn('radius');
            }
            if (Schema::hasColumn('branches', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('branches', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
