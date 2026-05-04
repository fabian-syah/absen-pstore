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
        // Backfill data lama dari EmploymentHistory
        try {
            $users = \App\Models\User::whereNull('created_by')->get();
            foreach ($users as $user) {
                // Cari riwayat 'join' dulu
                $history = \App\Models\EmploymentHistory::where('user_id', $user->id)
                    ->where('type', 'join')
                    ->first();
                
                // Jika tidak ada 'join', ambil riwayat paling awal apa saja
                if (!$history) {
                    $history = \App\Models\EmploymentHistory::where('user_id', $user->id)
                        ->orderBy('event_date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->first();
                }
                
                if ($history && $history->created_by) {
                    // Update langsung ke database untuk menghindari masalah fillable/timestamps jika ada
                    \DB::table('users')->where('id', $user->id)->update(['created_by' => $history->created_by]);
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika terjadi error database
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Tidak ada rollback untuk backfill data
    }
};
