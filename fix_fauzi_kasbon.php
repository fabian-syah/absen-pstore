<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "       UPDATE CICILAN KASBON FAUZI 09/2026 JADI 500K     \n";
echo "=========================================================\n\n";

$user = User::where('name', 'like', '%fauzi%')->first();
if (!$user) {
    echo "[ERROR] User Fauzi tidak ditemukan!\n";
    exit;
}

// Cari cicilan potongan gaji 09/2026 yang nominalnya 150.000
$ins = CashAdvanceInstallment::where('user_id', $user->id)
    ->where('amount_paid', 150000)
    ->where(function($q) {
        $q->where('note', 'like', '%09/2026%')
          ->orWhere('note', 'like', '%9/2026%');
    })
    ->first();

if (!$ins) {
    echo "[INFO] Cicilan Rp 150.000 untuk 09/2026 tidak ditemukan. Mungkin sudah diperbaiki.\n";
    exit;
}

echo "Ditemukan Cicilan ID #{$ins->id} pada Kasbon ID #{$ins->cash_advance_id}\n";
echo "Nominal saat ini: Rp " . number_format($ins->amount_paid, 0, ',', '.') . "\n";

DB::transaction(function() use ($ins) {
    $kasbon = CashAdvance::find($ins->cash_advance_id);
    $selisih = 500000 - $ins->amount_paid; // +350.000

    echo "\nKasbon ID #{$kasbon->id} SEBELUM UPDATE:\n";
    echo "  - Total Pinjaman : Rp " . number_format($kasbon->amount, 0, ',', '.') . "\n";
    echo "  - Sudah Bayar    : Rp " . number_format($kasbon->total_paid, 0, ',', '.') . "\n";
    echo "  - Sisa Hutang    : Rp " . number_format($kasbon->remaining_amount, 0, ',', '.') . "\n";

    // 1. Update Cicilan jadi 500.000
    $ins->amount_paid = 500000;
    $ins->save();

    // 2. Sesuaikan total_paid kasbon (+350.000)
    $kasbon->total_paid += $selisih;
    if ($kasbon->total_paid >= $kasbon->amount) {
        $kasbon->status = 'paid';
        $kasbon->repayment_date = now();
    }
    $kasbon->save();

    echo "\nKasbon ID #{$kasbon->id} SESUDAH UPDATE:\n";
    echo "  - Sudah Bayar    : Rp " . number_format($kasbon->total_paid, 0, ',', '.') . "\n";
    echo "  - Sisa Hutang    : Rp " . number_format($kasbon->remaining_amount, 0, ',', '.') . "\n";
    echo "\n[SUKSES] Cicilan kasbon berhasil diubah menjadi Rp 500.000!\n";
});
