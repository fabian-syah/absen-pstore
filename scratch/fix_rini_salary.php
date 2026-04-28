<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Salary;

$s = Salary::whereHas('user', function($q) { 
    $q->where('name', 'like', '%Rini Rahayu%'); 
})->where('month', '04')->where('year', '2026')->first();

if($s) {
    $old = $s->employee_basic_salary;
    $s->employee_basic_salary = 1500000;
    $s->notes = ($s->notes ?? '') . "\nKalkulasi Freelance: Rp 100.000 x 15 Hari";
    $s->save();
    echo "Fixed Rini Rahayu Salary ID: {$s->id} from {$old} to 1500000\n";
} else {
    echo "Record not found\n";
}
