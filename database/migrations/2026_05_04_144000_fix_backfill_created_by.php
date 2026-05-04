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
        try {
            // Ambil semua user yang 'created_by'-nya adalah diri mereka sendiri
            // atau created_by-nya memiliki role bukan admin/audit/leader
            $users = \App\Models\User::all();
            
            foreach ($users as $user) {
                $currentCreator = \App\Models\User::find($user->created_by);
                
                // Jika creator adalah dirinya sendiri ATAU role creator bukan admin/audit/leader
                if ($user->created_by == $user->id || ($currentCreator && !in_array($currentCreator->role, ['admin', 'audit', 'leader']))) {
                    
                    // Cari ulang riwayat yang diinput oleh ADMIN/AUDIT/LEADER saja
                    $validHistory = \App\Models\EmploymentHistory::where('user_id', $user->id)
                        ->whereHas('creator', function($q) {
                            $q->whereIn('role', ['admin', 'audit', 'leader']);
                        })
                        ->orderBy('event_date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->first();
                    
                    if ($validHistory) {
                        \DB::table('users')->where('id', $user->id)->update(['created_by' => $validHistory->created_by]);
                    } else {
                        // Jika tidak ada riwayat dari admin, kembalikan ke NULL (System)
                        \DB::table('users')->where('id', $user->id)->update(['created_by' => null]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Abaikan error
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
