<?php

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $users = User::where('name', 'like', '%Herlina%')
        ->orWhere('name', 'like', '%Eva%')
        ->orWhere('name', 'like', '%Agung%')
        ->get(['id', 'name', 'login_id', 'role']);

    echo "Found " . $users->count() . " users:\n";
    foreach ($users as $u) {
        echo "ID: " . $u->id . " | Name: " . $u->name . " | LoginID: " . $u->login_id . " | Role: " . $u->role . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
