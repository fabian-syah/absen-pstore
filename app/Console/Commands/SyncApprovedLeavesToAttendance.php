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
        $query = LeaveRequest::with(['user', 'user.branch'])
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
            'telat' => 'Izin Telat',
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

                    // Branch-specific timezone for correct date matching
                    $branchTimezone = $leave->user->branch->timezone ?? 'Asia/Jakarta';
                    $branchOffset = Carbon::now($branchTimezone)->format('P');
                    $appOffset = Carbon::now(config('app.timezone'))->format('P');

                    // Find ALL attendance records for this user on this local date
                    $attendances = Attendance::where('user_id', $leave->user_id)
                        ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$appOffset, $branchOffset, $currentDate])
                        ->orderBy('attendance_type', 'desc') // Prioritize 'self' or 'scan' over 'leave'
                        ->get();

                    if ($attendances->count() > 0) {
                        // DEDUPLICATION: If multiple records exist, we merge them
                        $mainAttendance = $attendances->first();

                        // If we have duplicates, delete the extras
                        if ($attendances->count() > 1 && !$dryRun) {
                            $this->warn("Merging {$attendances->count()} duplicates for User {$leave->user_id} on {$currentDate}");
                            foreach ($attendances->slice(1) as $duplicate) {
                                // Keep photos if they exist and main doesn't
                                if ($duplicate->photo_path && !$mainAttendance->photo_path) {
                                    $mainAttendance->photo_path = $duplicate->photo_path;
                                }
                                if ($duplicate->photo_out_path && !$mainAttendance->photo_out_path) {
                                    $mainAttendance->photo_out_path = $duplicate->photo_out_path;
                                }
                                $duplicate->delete();
                            }
                        }

                        // UPDATE logic: Ensure status is 'Izin Telat' if it was 'Masuk' or 'Alpha'
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
                                    // Don't overwrite actual check_in_time if it's already set by a selfie/scan
                                    if ($mainAttendance->attendance_type === 'leave') {
                                        $updateData['check_in_time'] = Carbon::parse($currentDate . ' ' . $leave->start_time);
                                    }
                                    $updateData['is_late_checkin'] = true;
                                    $updateData['notes'] = ($mainAttendance->notes ? $mainAttendance->notes . " | " : "") . 'Izin Telat: ' . $leave->reason;
                                } else {
                                    $updateData['notes'] = ($mainAttendance->notes ? $mainAttendance->notes . " | " : "") . ucfirst($leave->type) . ': ' . $leave->reason;
                                }

                                $mainAttendance->update($updateData);
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
