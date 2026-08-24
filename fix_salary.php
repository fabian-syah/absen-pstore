<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'pikawidaa')->orWhere('id', 563)->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User found: " . $user->name . "\n";

// Update master salary
$master = $user->employeeSalary;
if ($master) {
    $master->basic_salary = 10000000;
    $master->position_allowance = 0;
    $master->owner_privilege = 0;
    $master->save();
    echo "Master salary updated to 10.000.000\n";
} else {
    echo "Master salary not found!\n";
}

// Update existing monthly salaries
$salaries = App\Models\Salary::where('user_id', $user->id)
    ->whereIn('month', ['06', '07', '08'])
    ->where('year', '2026')
    ->get();

foreach ($salaries as $salary) {
    $salary->employee_basic_salary = 10000000;
    $salary->employee_position_allowance = 0;
    $salary->employee_owner_privilege = 0;
    
    // recalculate total
    $income = $salary->employee_basic_salary + $salary->employee_position_allowance + $salary->employee_owner_privilege + $salary->promotor_bonus + $salary->dispensation_amount;
    
    $deduction = $salary->alpha_deduction + $salary->late_deduction + $salary->cuti_lebih_deduction + $salary->kasbon_deduction + $salary->other_deduction;
    
    $salary->total_amount = $income - $deduction;
    $salary->save();
    echo "Updated salary for " . $salary->month . "/" . $salary->year . " to " . $salary->total_amount . "\n";
}
echo "Done!\n";
