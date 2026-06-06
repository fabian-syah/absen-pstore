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
        // Convert existing data to JSON first
        $zikirs = \DB::table('zikirs')->get();
        foreach ($zikirs as $zikir) {
            $cat = $zikir->category;
            if ($cat && !str_starts_with($cat, '[')) {
                \DB::table('zikirs')->where('id', $zikir->id)->update([
                    'category' => json_encode([$cat])
                ]);
            }
        }

        Schema::table('zikirs', function (Blueprint $table) {
            $table->json('category')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zikirs', function (Blueprint $table) {
            $table->string('category')->default('umum')->change();
        });
    }
};
