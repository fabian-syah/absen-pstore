<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Attendance;

$name = 'TUBAGUS FARIS';
$user = User::where('name', 'like', "%$name%")->first();

if (!$user) {
    die("User TUBAGUS FARIS tidak ditemukan di database.\n");
}

echo "========================================================\n";
echo "MENAMPILKAN DATA RAW DATABASE - " . $user->name . "\n";
echo "========================================================\n";

$attendances = Attendance::where('user_id', $user->id)
    ->whereYear('check_in_time', 2026)
    ->whereMonth('check_in_time', 4)
    ->orderBy('check_in_time', 'desc')
    ->get();

if ($attendances->isEmpty()) {
    echo "Tidak ada data absen untuk bulan April 2026.\n";
}

foreach ($attendances as $a) {
    echo "ID ABSEN      : " . $a->id . "\n";
    echo "CHECK-IN      : " . $a->check_in_time . " (UTC)\n";
    echo "CHECK-OUT     : " . ($a->check_out_time ?: 'BELUM PULANG') . " (UTC)\n";
    echo "PHOTO PULANG  : " . ($a->photo_out_path ? "ADA (" . $a->photo_out_path . ")" : "KOSONG") . "\n";
    echo "LOKASI PULANG : " . ($a->check_out_location ?: "KOSONG") . "\n";
    echo "STATUS        : " . $a->presence_status . "\n";
    echo "--------------------------------------------------------\n";
}
