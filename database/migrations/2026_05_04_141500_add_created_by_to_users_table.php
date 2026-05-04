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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('remember_token');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Backfill data lama dari EmploymentHistory (Tipe 'join')
        try {
            $users = \App\Models\User::whereNull('created_by')->get();
            foreach ($users as $user) {
                $history = \App\Models\EmploymentHistory::where('user_id', $user->id)
                    ->where('type', 'join')
                    ->first();
                
                if ($history && $history->created_by) {
                    $user->update(['created_by' => $history->created_by]);
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika tabel atau data belum siap
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
