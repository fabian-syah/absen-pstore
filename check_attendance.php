<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user_id = 47;

$attendances = \App\Models\Attendance::where('user_id', $user_id)
    ->whereYear('check_in_time', 2026)
    ->whereMonth('check_in_time', 2)
    ->get();

foreach ($attendances as $att) {
    if ($att->audit_photo_path || $att->presence_status == 'Sakit') {
        echo "ID: {$att->id} | Date: {$att->check_in_time} | Status: {$att->presence_status} | Audit Photo: " . ($att->audit_photo_path ?? 'NULL') . " | Attendance Type: {$att->attendance_type} \n";

        $leave = \App\Models\LeaveRequest::where('user_id', $user_id)
            ->where('start_date', '<=', $att->check_in_time->format('Y-m-d'))
            ->where(function ($q) use ($att) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $att->check_in_time->format('Y-m-d'));
            })
            ->first();

        if ($leave) {
            echo "   -> Leave Req: ID {$leave->id} | Proof: " . ($leave->file_proof ?? 'NULL') . "\n";
        } else {
            echo "   -> No matching leave request found\n";
        }
    }
}
