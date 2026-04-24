<?php

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = User::where('name', 'like', '%TUBAGUS%')->first();
    if (!$user) {
        echo "User TUBAGUS not found\n";
        exit;
    }

    echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Branch: " . ($user->branch->name ?? 'N/A') . " (TZ: " . ($user->branch->timezone ?? 'N/A') . ")\n";

    $attendances = Attendance::where('user_id', $user->id)
        ->where('check_in_time', '>=', '2026-04-01')
        ->orderBy('check_in_time', 'desc')
        ->get();

    echo "Total records found since April: " . $attendances->count() . "\n";

    foreach ($attendances as $a) {
        $tz = $user->branch->timezone ?? 'Asia/Jakarta';
        $in = Carbon::parse($a->check_in_time)->timezone($tz)->format('Y-m-d H:i:s');
        $out = $a->check_out_time ? Carbon::parse($a->check_out_time)->timezone($tz)->format('Y-m-d H:i:s') : 'N/A';
        echo "Date: " . Carbon::parse($a->check_in_time)->timezone($tz)->format('Y-m-d') . " | In: $in | Out: $out | Status: " . $a->presence_status . " | Type: " . $a->attendance_type . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
