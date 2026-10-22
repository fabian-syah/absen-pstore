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
        // 1. Tabel Utama Unit Device Berdasarkan IMEI
        if (!Schema::hasTable('device_imeis')) {
            Schema::create('device_imeis', function (Blueprint $table) {
                $table->id();
                $table->string('imei', 30)->unique()->index();
                $table->string('brand', 60)->default('Apple')->index();
                $table->string('type', 120)->index(); // Contoh: iPhone 13, iPhone 14
                $table->string('storage', 60)->default('128 GB'); // Contoh: 128 GB, 256 GB
                $table->string('color', 60)->nullable();
                $table->string('last_cs_name', 120)->nullable(); // CS terakhir yang dicatat
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 2. Tabel Riwayat Koreksi & Audit Trail IMEI
        if (!Schema::hasTable('device_imei_histories')) {
            Schema::create('device_imei_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_imei_id')->nullable()->index();
                $table->string('imei', 30)->index();
                $table->string('action_type', 40)->default('koreksi_tipe'); // koreksi_tipe, pendaftaran_awal
                
                // Data Sebelum Koreksi (Data Lama)
                $table->string('old_brand', 60)->nullable();
                $table->string('old_type', 120)->nullable(); // Misal: iPhone 14
                $table->string('old_storage', 60)->nullable(); // Misal: 128 GB
                $table->string('old_color', 60)->nullable();

                // Data Sesudah Koreksi (Data Baru)
                $table->string('new_brand', 60);
                $table->string('new_type', 120); // Misal: iPhone 13
                $table->string('new_storage', 60); // Misal: 128 GB
                $table->string('new_color', 60)->nullable();

                // Identitas CS yang Salah Input & Keterangan
                $table->string('cs_name_mistake', 120)->nullable(); // Nama CS yang salah input
                $table->text('correction_reason')->nullable(); // Alasan perbaikan
                
                // Super Admin yang Melakukan Koreksi
                $table->unsignedBigInteger('corrected_by')->nullable();
                $table->timestamps();

                $table->foreign('device_imei_id')->references('id')->on('device_imeis')->cascadeOnDelete();
                $table->foreign('corrected_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_imei_histories');
        Schema::dropIfExists('device_imeis');
    }
};
