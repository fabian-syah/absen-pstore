<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\Salary;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "   SCRIPT FIX DUPLIKAT CICILAN KASBON GRAVRILLA          \n";
echo "=========================================================\n\n";

$user = User::where('name', 'like', '%gravrilla%')->first();
if (!$user) {
    echo "[ERROR] User dengan nama Gravrilla tidak ditemukan di database!\n";
    exit;
}

echo "User Ditemukan: {$user->name} (ID: {$user->id})\n";

// 1. Cari cicilan potongan gaji 06/2026 tanggal 28 Juni 2026
$installments = CashAdvanceInstallment::where('user_id', $user->id)
    ->where('note', 'like', '%06/2026%')
    ->orderBy('created_at', 'asc')
    ->get();

echo "\nDaftar Riwayat Cicilan Terkait Potongan Gaji 06/2026:\n";
foreach ($installments as $ins) {
    echo "- ID #{$ins->id} | Rp " . number_format($ins->amount_paid, 0, ',', '.') . 
         " | Status: {$ins->status} | Note: {$ins->note} | Waktu: {$ins->created_at}\n";
}

// 2. Cari cicilan ganda yang nominalnya 2.000.000
$dup = $installments->firstWhere('amount_paid', 2000000);

if (!$dup) {
    echo "\n[INFO] Record cicilan Rp 2.000.000 tidak ditemukan. Mungkin sudah diperbaiki sebelumnya.\n";
} else {
    echo "\n>>> Menghapus cicilan ID #{$dup->id} (Rp 2.000.000) dan mengembalikan saldo kasbon...\n";

    DB::transaction(function () use ($dup) {
        $kasbon = CashAdvance::find($dup->cash_advance_id);
        if ($kasbon) {
            echo "Kasbon ID #{$kasbon->id} (SEBELUM PERBAIKAN):\n";
            echo "  - Total Pinjaman : Rp " . number_format($kasbon->amount, 0, ',', '.') . "\n";
            echo "  - Total Terbayar : Rp " . number_format($kasbon->total_paid, 0, ',', '.') . "\n";
            echo "  - Sisa Hutang    : Rp " . number_format($kasbon->remaining_amount, 0, ',', '.') . "\n";
            echo "  - Status         : {$kasbon->status}\n";

            // Kurangi total_paid sebesar 2.000.000
            $kasbon->total_paid = max(0, $kasbon->total_paid - $dup->amount_paid);

            // Jika statusnya 'paid' tapi sekarang total_paid < amount, kembalikan ke 'approved'
            if ($kasbon->total_paid < $kasbon->amount && $kasbon->status === 'paid') {
                $kasbon->status = 'approved';
                $kasbon->repayment_date = null;
            }
            $kasbon->save();

            echo "Kasbon ID #{$kasbon->id} (SETELAH PERBAIKAN):\n";
            echo "  - Total Terbayar : Rp " . number_format($kasbon->total_paid, 0, ',', '.') . "\n";
            echo "  - Sisa Hutang    : Rp " . number_format($kasbon->remaining_amount, 0, ',', '.') . "\n";
            echo "  - Status         : {$kasbon->status}\n";
        }

        // Hapus record cicilan 2 jt
        $dup->delete();
        echo "\n[SUKSES] Cicilan Rp 2.000.000 berhasil dihapus!\n";
    });
}

// 3. Pengecekan Record Slip Gaji (Salaries)
echo "\n=========================================================\n";
echo "   PENGECEKAN RECORD SLIP GAJI BULAN 06/2026             \n";
echo "=========================================================\n";

$salaries = Salary::where('user_id', $user->id)
    ->where('year', 2026)
    ->where(function ($q) {
        $q->where('month', '6')->orWhere('month', '06');
    })
    ->get();

if ($salaries->isEmpty()) {
    echo "[INFO] Tidak ada slip gaji 06/2026 untuk user ini.\n";
} else {
    echo "Jumlah slip gaji ditemukan: " . $salaries->count() . "\n";
    foreach ($salaries as $sal) {
        echo "- Slip ID #{$sal->id} | Potongan Kasbon: Rp " . number_format($sal->kasbon_deduction, 0, ',', '.') . 
             " | Total Gaji Bersih: Rp " . number_format($sal->total_amount, 0, ',', '.') . 
             " | Tanggal Input: {$sal->created_at}\n";
    }
    if ($salaries->count() > 1) {
        echo "\n[PERINGATAN] Ada lebih dari 1 slip gaji untuk periode yang sama. Pastikan slip gaji yang salah/duplikat dihapus dari web.\n";
    }
}

echo "\nSemua proses selesai dengan sukses!\n";
