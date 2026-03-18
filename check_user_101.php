<?php

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 101;
$user = User::with(['division', 'branch'])->find($userId);

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User: {$user->name} (Role: {$user->role}, Branch: {$user->branch->name})\n";

$branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
$localNow = Carbon::now($branchTimezone);
$todayLocal = $localNow->copy()->startOfDay();

echo "Local Time in Branch: {$localNow->toDateTimeString()} ({$branchTimezone})\n";

// Check Attendance Today
$appOffset = Carbon::now(config('app.timezone'))->format('P');
$branchOffset = Carbon::now($branchTimezone)->format('P');

$attendances = Attendance::where('user_id', $userId)
    ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$appOffset, $branchOffset, $todayLocal->format('Y-m-d')])
    ->get();

echo "Attendances today: " . $attendances->count() . "\n";
foreach ($attendances as $a) {
    echo "- ID: {$a->id} | In: {$a->check_in_time} | Out: {$a->check_out_time} | Status: {$a->status} | Type: {$a->attendance_type}\n";
}

// Check Leave Requests Today
$leaves = LeaveRequest::where('user_id', $userId)
    ->where('status', 'approved')
    ->whereDate('start_date', '<=', $todayLocal)
    ->whereDate('end_date', '>=', $todayLocal)
    ->get();

echo "Approved leaves today: " . $leaves->count() . "\n";
foreach ($leaves as $l) {
    echo "- ID: {$l->id} | Type: {$l->type} | Start: {$l->start_date} | End: {$l->end_date} | Status: {$l->status}\n";
}

// Check for recent checkout (within 32h)
$recentCheckout = Attendance::where('user_id', $userId)
    ->whereNotNull('check_out_time')
    ->where('check_out_time', '>=', $localNow->copy()->subHours(32))
    ->latest('check_out_time')
    ->first();

if ($recentCheckout) {
    echo "Recent Checkout: ID {$recentCheckout->id} at {$recentCheckout->check_out_time}\n";
} else {
    echo "No recent checkout (32h)\n";
}

// Check cooldown (4h)
$cooldownSession = Attendance::where('user_id', $userId)
    ->whereNotNull('check_out_time')
    ->where('check_out_time', '>=', $localNow->copy()->subHours(4))
    ->latest('check_out_time')
    ->first();

if ($cooldownSession) {
    echo "Cooldown Session: ID {$cooldownSession->id} at {$cooldownSession->check_out_time}\n";
}
