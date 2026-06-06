<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        $zikirs = DB::table('zikirs')->get();
        foreach ($zikirs as $zikir) {
            $cat = $zikir->category;
            if ($cat && !str_starts_with($cat, '[')) {
                DB::table('zikirs')->where('id', $zikir->id)->update([
                    'category' => json_encode([$cat])
                ]);
            }
        }

        DB::statement('ALTER TABLE zikirs MODIFY category TEXT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE zikirs MODIFY category VARCHAR(255) DEFAULT \'umum\'');
    }
};
