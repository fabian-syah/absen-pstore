<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResetLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset leave balance for all users to 10 and clear leave taken.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting leave balance reset...');

        DB::transaction(function () {
            // Option 1: Reset everyone to default (10)
            // Users might have different limits in future, so we use 'yearly_leave_limit' column if it exists, or default 10.

            // Updates in bulk
            // If we want to use the 'yearly_leave_limit' column we added:
            DB::statement("UPDATE users SET leave_balance = yearly_leave_limit, leave_taken = 0 WHERE is_active = 1");

            // Or if we strictly follow "10 days rule" for now:
            // DB::table('users')->where('is_active', true)->update(['leave_balance' => 10, 'leave_taken' => 0]);
        });

        $this->info('Leave balance reset successfully for all active users.');
    }
}
