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
        Schema::create('zikirs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('umum'); // umum, pagi, petang, sholat
            $table->text('arabic_text')->nullable();
            $table->text('latin_text')->nullable();
            $table->text('translation')->nullable();
            $table->integer('default_target')->default(1);
            $table->text('information')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zikirs');
    }
};
