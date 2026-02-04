<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateLeaveBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:recalculate {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate leave balance and taken days based on approved leave requests for the specified year.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?: now()->year;
        $this->info("Recalculating leave balances for year: $year");

        $users = User::where('is_active', 1)->get();
        $bar = $this->output->createProgressBar(count($users));

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                // 1. Ambil semua cuti yang APPROVED di tahun ini
                $approvedLeaves = LeaveRequest::where('user_id', $user->id)
                    ->where('type', 'cuti')
                    ->where('status', 'approved')
                    ->whereYear('start_date', $year)
                    ->get();

                $totalTaken = 0;

                foreach ($approvedLeaves as $leave) {
                    $start = Carbon::parse($leave->start_date);
                    $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start;
                    $days = $start->diffInDays($end) + 1;
                    $totalTaken += $days;
                }

                // 2. Update user data
                // Reset limit jika belum ada (opsional, jaga-jaga)
                $limit = $user->yearly_leave_limit ?: 10;

                $user->leave_taken = $totalTaken;
                $user->leave_balance = $limit - $totalTaken;
                $user->save();

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine();
            $this->info("Successfully recalculated leave balances for " . count($users) . " users.");

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("Error: " . $e->getMessage());
        }
    }
}
