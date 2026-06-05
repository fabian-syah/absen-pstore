<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zikir_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('arabic_text')->nullable();
            $table->text('latin_text')->nullable();
            $table->bigInteger('target')->default(1000000);
            $table->bigInteger('current_count')->default(0);
            $table->string('emoji', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zikir_campaigns');
    }
};
