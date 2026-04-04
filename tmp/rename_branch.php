<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\App\Models\Branch::where('name', 'Data User (Admin Gaji)')->update([
    'name' => 'Cabang User Non Karyawan',
    'address' => 'Cabang Khusus untuk User Non Karyawan'
]);
echo 'Branch updated.';
