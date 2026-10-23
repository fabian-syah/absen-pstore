<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\Salary;

echo "=========================================================\n";
echo "       PENGECEKAN KASBON & GAJI SEPTEMBER - FAUZI        \n";
echo "=========================================================\n\n";

$user = User::where('name', 'like', '%fauzi%')->first();
if (!$user) {
    echo "[ERROR] User Fauzi tidak ditemukan!\n";
    exit;
}

echo "User: {$user->name} (ID: {$user->id})\n\n";

// 1. Cek SEMUA Kasbon milik Fauzi (termasuk yang sudah PAID / LUNAS)
$kasbons = CashAdvance::where('user_id', $user->id)->get();
echo "--- DAFTAR SEMUA KASBON FAUZI (Total: " . $kasbons->count() . ") ---\n";
foreach ($kasbons as $k) {
    echo "• Kasbon ID #{$k->id}\n";
    echo "  - Keterangan   : {$k->description}\n";
    echo "  - Pinjaman     : Rp " . number_format($k->amount, 0, ',', '.') . "\n";
    echo "  - Sudah Bayar  : Rp " . number_format($k->total_paid, 0, ',', '.') . "\n";
    echo "  - Sisa Hutang  : Rp " . number_format($k->remaining_amount, 0, ',', '.') . "\n";
    echo "  - Potongan/Bln : Rp " . number_format($k->monthly_deduction ?? 0, 0, ',', '.') . "\n";
    echo "  - Status       : {$k->status}\n";
    echo "  - Dibuat Tgl   : {$k->created_at}\n\n";
}

// 2. Cek SEMUA Cicilan Potongan Gaji 09/2026
$installments = CashAdvanceInstallment::where('user_id', $user->id)
    ->where(function($q) {
        $q->where('note', 'like', '%09/2026%')
          ->orWhere('note', 'like', '%9/2026%');
    })
    ->get();

echo "--- DAFTAR CICILAN DARI POTONGAN GAJI 09/2026 (Total: " . $installments->count() . ") ---\n";
$totalCicilan09 = 0;
foreach ($installments as $ins) {
    echo "• Cicilan ID #{$ins->id}\n";
    echo "  - Kasbon ID : #{$ins->cash_advance_id}\n";
    echo "  - Nominal   : Rp " . number_format($ins->amount_paid, 0, ',', '.') . "\n";
    echo "  - Status    : {$ins->status}\n";
    echo "  - Note      : {$ins->note}\n";
    echo "  - Waktu     : {$ins->created_at}\n\n";
    $totalCicilan09 += $ins->amount_paid;
}
echo "Total Seluruh Cicilan Terpotong di 09/2026: Rp " . number_format($totalCicilan09, 0, ',', '.') . "\n\n";

// 3. Cek Slip Gaji Bulan 09/2026
$salary = Salary::where('user_id', $user->id)
    ->where('year', 2026)
    ->where(function($q) {
        $q->where('month', '9')->orWhere('month', '09');
    })
    ->first();

echo "--- DATA SLIP GAJI SEPTEMBER 2026 ---\n";
if ($salary) {
    echo "• Slip ID #{$salary->id}\n";
    echo "  - Potongan Kasbon tercatat di Slip : Rp " . number_format($salary->kasbon_deduction, 0, ',', '.') . "\n";
    echo "  - Take Home Pay (Gaji Bersih)      : Rp " . number_format($salary->total_amount, 0, ',', '.') . "\n";
    echo "  - Tanggal Dibuat                  : {$salary->created_at}\n";
} else {
    echo "Slip gaji September 2026 tidak ditemukan.\n";
}

echo "\n=========================================================\n";
