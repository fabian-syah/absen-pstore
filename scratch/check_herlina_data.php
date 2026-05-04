<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Branch;

$herlina = User::where('name', 'like', '%Herlina%')->first();
if ($herlina) {
    echo "Herlina ID: " . $herlina->id . "\n";
    echo "Herlina Name: " . $herlina->name . "\n";
    echo "Herlina Login ID: " . $herlina->login_id . "\n";
    echo "Herlina Role: " . $herlina->role . "\n";
    echo "Herlina Branch ID: " . $herlina->branch_id . "\n";
    echo "Herlina Branch Name: " . ($herlina->branch->name ?? 'N/A') . "\n";
    echo "Herlina Is Active: " . ($herlina->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "Herlina not found.\n";
}

$exBranch = Branch::where('name', 'like', '%EX Karyawan%')->first();
if ($exBranch) {
    echo "EX Branch ID: " . $exBranch->id . "\n";
} else {
    echo "EX Branch not found.\n";
}

$nonKaryawanBranch = Branch::where('name', 'like', '%Non Karyawan%')->first();
if ($nonKaryawanBranch) {
    echo "Non Karyawan Branch ID: " . $nonKaryawanBranch->id . "\n";
} else {
    echo "Non Karyawan Branch not found.\n";
}

$exUsersCount = User::where('is_active', false)->count();
echo "Total Inactive Users: " . $exUsersCount . "\n";

$exBranchUsersCount = User::where('branch_id', $exBranch->id ?? 0)->count();
echo "Users in EX Branch: " . $exBranchUsersCount . "\n";
