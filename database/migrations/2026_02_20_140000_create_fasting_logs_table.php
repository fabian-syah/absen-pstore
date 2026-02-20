<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fasting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_fasting')->default(true);
            $table->unsignedTinyInteger('ramadan_day')->nullable(); // 1-30
            $table->unsignedSmallInteger('hijri_year')->nullable(); // e.g. 1447
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'hijri_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasting_logs');
    }
};
