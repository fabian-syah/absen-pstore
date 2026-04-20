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
                            {--cleanup : Delete attendance records that no longer have a matching approved leave request}
                            {--user= : Sync only for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync approved leave requests to attendance records and optionally cleanup cancelled/orphaned records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $cleanup = $this->option('cleanup');
        $userId = $this->option('user');

        $this->info('🚀 Starting Leave-to-Attendance Sync...');
        $this->info($dryRun ? '⚠️  DRY RUN MODE - No changes will be made' : '✅ LIVE MODE - Changes will be saved');
        if ($cleanup) {
            $this->info('🧹 Cleanup mode is ENABLED');
        }
        $this->newLine();

        $stats = [
            'updated' => 0,
            'created' => 0,
            'skipped' => 0,
            'deleted' => 0, // <--- New stat
            'errors' => 0
        ];

        // --- STEP 1: CLEANUP (If requested) ---
        if ($cleanup) {
            $this->info('Step 1: Cleaning up orphaned leave attendance records...');

            $orphanQuery = Attendance::where('attendance_type', 'leave')
                ->with(['user', 'user.branch']);

            if ($userId) {
                $orphanQuery->where('user_id', $userId);
            }

            $potentialOrphans = $orphanQuery->get();
            $this->info("Found {$potentialOrphans->count()} attendance records of type 'leave' to verify.");

            $cleanBar = $this->output->createProgressBar($potentialOrphans->count());
            $cleanBar->start();

            foreach ($potentialOrphans as $att) {
                try {
                    $user = $att->user;
                    if (!$user) {
                        if (!$dryRun)
                            $att->delete();
                        $stats['deleted']++;
                        $cleanBar->advance();
                        continue;
                    }

                    $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
                    // We need to know what LOCAL DATE this attendance represents
                    $localDateString = Carbon::parse($att->check_in_time)
                        ->timezone($branchTimezone)
                        ->format('Y-m-d');

                    // Check if an approved leave exists for this user covering this date
                    $hasApprovedLeave = LeaveRequest::where('user_id', $att->user_id)
                        ->where('status', 'approved')
                        ->where('start_date', '<=', $localDateString)
                        ->where(function ($q) use ($localDateString) {
                            $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', $localDateString);
                        })
                        ->exists();

                    if (!$hasApprovedLeave) {
                        if (!$dryRun) {
                            $att->delete();
                        }
                        $stats['deleted']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                }
                $cleanBar->advance();
            }
            $cleanBar->finish();
            $this->newLine(2);
        }

        // --- STEP 2: SYNC (Original logic) ---
        $this->info('Step 2: Syncing approved leave requests...');

        $query = LeaveRequest::with(['user', 'user.branch'])
            ->where('status', 'approved');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $approvedLeaves = $query->get();
        $this->info("Found {$approvedLeaves->count()} approved leave requests to process.");

        $bar = $this->output->createProgressBar($approvedLeaves->count());
        $bar->start();

        // Map leave type to presence status
        $presenceStatusMap = [
            'telat' => 'Izin Telat',
            'wfh' => 'WFH',
            'dinas' => 'Dinas Luar',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'libur' => 'Libur',
        ];

        foreach ($approvedLeaves as $leave) {
            try {
                if (!$leave->user) {
                    $bar->advance();
                    continue;
                }

                $startDate = Carbon::parse($leave->start_date);
                $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate;
                $presenceStatus = $presenceStatusMap[$leave->type] ?? ucfirst($leave->type);

                // Loop through each date in the range
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $currentDate = $date->format('Y-m-d');

                    $branchTimezone = $leave->user->branch->timezone ?? 'Asia/Jakarta';
                    $branchOffset = Carbon::now($branchTimezone)->format('P');
                    // Storage is UTC, so source offset must be '+00:00'
                    $storageOffset = '+00:00';

                    $attendances = Attendance::where('user_id', $leave->user_id)
                        ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $currentDate])
                        ->orderBy('attendance_type', 'desc')
                        ->get();

                    if ($attendances->count() > 0) {
                        $mainAttendance = $attendances->first();

                        if ($attendances->count() > 1 && !$dryRun) {
                            foreach ($attendances->slice(1) as $duplicate) {
                                if ($duplicate->photo_path && !$mainAttendance->photo_path) {
                                    $mainAttendance->photo_path = $duplicate->photo_path;
                                }
                                $duplicate->delete();
                            }
                        }

                        $shouldUpdate = (
                            !$mainAttendance->presence_status ||
                            in_array(strtolower($mainAttendance->presence_status), ['alpha', 'masuk', 'hadir']) ||
                            ($leave->type === 'telat' && $mainAttendance->presence_status !== 'Izin Telat')
                        );

                        if ($shouldUpdate) {
                            if (!$dryRun) {
                                $updateData = [
                                    'presence_status' => $presenceStatus,
                                    'status' => 'verified',
                                    'attendance_type' => $mainAttendance->attendance_type === 'leave' ? 'leave' : $mainAttendance->attendance_type,
                                ];

                                if ($leave->type === 'telat' && $leave->start_time) {
                                    if ($mainAttendance->attendance_type === 'leave') {
                                        $updateData['check_in_time'] = Carbon::parse($currentDate . ' ' . $leave->start_time);
                                    }
                                    $updateData['is_late_checkin'] = true;
                                }

                                $mainAttendance->update($updateData);
                            }
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } else {
                        if (!$dryRun) {
                            $attendanceData = [
                                'user_id' => $leave->user_id,
                                'branch_id' => $leave->user->branch_id,
                                'presence_status' => $presenceStatus,
                                'status' => 'verified',
                                'attendance_type' => 'leave',
                                'verified_by_user_id' => $leave->approved_by,
                                'check_in_time' => ($leave->type === 'telat' && $leave->start_time)
                                    ? Carbon::parse($currentDate . ' ' . $leave->start_time)
                                    : Carbon::parse($currentDate)->startOfDay()
                            ];
                            Attendance::create($attendanceData);
                        }
                        $stats['created']++;
                    }
                }
            } catch (\Exception $e) {
                $stats['errors']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('📊 Sync Results:');
        $this->table(
            ['Action', 'Count'],
            [
                ['Updated (Alpha → Leave Type)', $stats['updated']],
                ['Created (New Attendance)', $stats['created']],
                ['Skipped (Already synced)', $stats['skipped']],
                ['Deleted (Orphaned/Cancelled)', $stats['deleted']],
                ['Errors', $stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('🔍 This was a DRY RUN. Run with --cleanup (and without --dry-run) to apply.');
        } else {
            $this->newLine();
            $this->info('✅ Sync and Cleanup completed successfully!');
        }

        return Command::SUCCESS;
    }
}
