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
        Schema::create('employee_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assessor_id')->constrained('users')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            
            // Fixed Criteria
            $table->integer('kecerdasan_score')->nullable();
            $table->string('kecerdasan_note')->nullable();
            
            $table->integer('amanah_score')->nullable();
            $table->string('amanah_note')->nullable();
            
            $table->integer('sosial_media_score')->nullable();
            $table->string('sosial_media_note')->nullable();
            
            $table->integer('kepemimpinan_score')->nullable();
            $table->string('kepemimpinan_note')->nullable();
            
            $table->integer('data_ketelitian_score')->nullable();
            $table->string('data_ketelitian_note')->nullable();
            
            $table->integer('komunikasi_score')->nullable();
            $table->string('komunikasi_note')->nullable();
            
            $table->integer('kedisiplinan_score')->nullable();
            $table->string('kedisiplinan_note')->nullable();
            
            // Custom Criteria
            $table->string('custom_title')->nullable();
            $table->integer('custom_score')->nullable();
            $table->string('custom_note')->nullable();
            
            // Results
            $table->decimal('average_score', 5, 2)->nullable();
            $table->string('grade', 5)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_evaluations');
    }
};
