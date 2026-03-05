<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 166; // From URL: absnps.com/team/branch/52/employee/166/history?month=1&year=2026
$dateStr = '2026-01-31';

$attendances = \App\Models\Attendance::where('user_id', $userId)
    ->whereDate('check_in_time', $dateStr)
    ->get();

echo "Attendances for User $userId on $dateStr:\n";
foreach ($attendances as $att) {
    echo "ID: {$att->id}\n";
    echo "Check In: {$att->check_in_time}\n";
    echo "Status: {$att->presence_status}\n";
    echo "Attendance Type: {$att->attendance_type}\n";
    echo "Verified By: {$att->verified_by_user_id}\n";
    echo "--------------------------\n";
}

if ($attendances->isEmpty()) {
    echo "No attendance record found for this date.\n";
}
