<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmploymentHistory;

$users = User::all();
echo "Total Users: " . $users->count() . "\n";

foreach ($users->take(10) as $user) {
    $history = EmploymentHistory::where('user_id', $user->id)
        ->where('type', 'join')
        ->first();
    
    if ($history) {
        echo "User: " . $user->name . " (ID: " . $user->id . ") has join history created by: " . ($history->created_by ?? 'NULL') . "\n";
    } else {
        echo "User: " . $user->name . " (ID: " . $user->id . ") has NO join history\n";
    }
}
