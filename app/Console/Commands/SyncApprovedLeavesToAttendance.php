<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncApprovedLeavesToAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-leaves 
                            {--dry-run : Run without making changes}
                            {--user= : Sync only for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync approved leave requests to attendance records (fix Alpha status for approved leaves)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');

        $this->info('🚀 Starting Leave-to-Attendance Sync...');
        $this->info($dryRun ? '⚠️  DRY RUN MODE - No changes will be made' : '✅ LIVE MODE - Changes will be saved');
        $this->newLine();

        // Query approved leaves
        $query = LeaveRequest::with('user')
            ->where('status', 'approved');

        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("Filtering for User ID: {$userId}");
        }

        $approvedLeaves = $query->get();

        $this->info("Found {$approvedLeaves->count()} approved leave requests");
        $this->newLine();

        $bar = $this->output->createProgressBar($approvedLeaves->count());
        $bar->start();

        $stats = [
            'updated' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        // Map leave type to presence status
        $presenceStatusMap = [
            'telat' => 'Masuk',
            'wfh' => 'WFH',
            'dinas' => 'Dinas Luar',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
        ];

        foreach ($approvedLeaves as $leave) {
            try {
                $startDate = Carbon::parse($leave->start_date);
                $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate;
                $presenceStatus = $presenceStatusMap[$leave->type] ?? ucfirst($leave->type);

                // Loop through each date in the range
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $currentDate = $date->format('Y-m-d');

                    // Find existing attendance
                    $attendance = Attendance::where('user_id', $leave->user_id)
                        ->whereDate('check_in_time', $currentDate)
                        ->first();

                    if ($attendance) {
                        // Update if still Alpha or null
                        if (
                            !$attendance->presence_status ||
                            strtolower($attendance->presence_status) === 'alpha'
                        ) {

                            if (!$dryRun) {
                                $updateData = [
                                    'presence_status' => $presenceStatus,
                                    'status' => 'verified',
                                    'attendance_type' => 'leave',
                                ];

                                if ($leave->type === 'telat' && $leave->start_time) {
                                    $updateData['check_in_time'] = Carbon::parse($currentDate . ' ' . $leave->start_time);
                                    $updateData['is_late_checkin'] = true;
                                    $updateData['notes'] = 'Izin Telat: ' . $leave->reason;
                                } else {
                                    $updateData['notes'] = ucfirst($leave->type) . ': ' . $leave->reason;
                                }

                                $attendance->update($updateData);
                            }

                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } else {
                        // Create new attendance
                        if (!$dryRun) {
                            $attendanceData = [
                                'user_id' => $leave->user_id,
                                'branch_id' => $leave->user->branch_id,
                                'presence_status' => $presenceStatus,
                                'status' => 'verified',
                                'attendance_type' => 'leave',
                                'verified_by_user_id' => $leave->approved_by,
                            ];

                            if ($leave->type === 'telat' && $leave->start_time) {
                                $attendanceData['check_in_time'] = Carbon::parse($currentDate . ' ' . $leave->start_time);
                                $attendanceData['is_late_checkin'] = true;
                                $attendanceData['notes'] = 'Izin Telat: ' . $leave->reason;
                            } else {
                                $attendanceData['check_in_time'] = Carbon::parse($currentDate)->startOfDay();
                                $attendanceData['notes'] = ucfirst($leave->type) . ': ' . $leave->reason;
                            }

                            Attendance::create($attendanceData);
                        }

                        $stats['created']++;
                    }
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("Error processing Leave ID {$leave->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info('📊 Sync Results:');
        $this->table(
            ['Action', 'Count'],
            [
                ['Updated (Alpha → Leave Type)', $stats['updated']],
                ['Created (New Attendance)', $stats['created']],
                ['Skipped (Already synced)', $stats['skipped']],
                ['Errors', $stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('🔍 This was a DRY RUN. Run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info('✅ Sync completed successfully!');
        }

        return Command::SUCCESS;
    }
}
