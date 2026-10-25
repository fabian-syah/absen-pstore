<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "       CEK & FIX PENGAJUAN CUTI GANDA 16-18 JULI 2026     \n";
echo "=========================================================\n\n";

// 1. Cari pengajuan cuti tanggal 16 Juli - 18 Juli 2026
$leaves = LeaveRequest::with('user.branch')
    ->where('type', 'cuti')
    ->whereDate('start_date', '2026-07-16')
    ->whereDate('end_date', '2026-07-18')
    ->orderBy('created_at', 'asc')
    ->get();

if ($leaves->isEmpty()) {
    echo "[INFO] Tidak ditemukan data cuti dengan tanggal 16 - 18 Juli 2026.\n";
    exit;
}

echo "Ditemukan " . $leaves->count() . " pengajuan cuti:\n";
foreach ($leaves as $l) {
    echo "• ID #{$l->id} | User: {$l->user->name} (ID: {$l->user_id}, Cabang: " . ($l->user->branch->name ?? '-') . ")\n";
    echo "  - Periode   : {$l->start_date} s/d {$l->end_date}\n";
    echo "  - Status    : {$l->status}\n";
    echo "  - Alasan    : {$l->reason}\n";
    echo "  - Tgl Input : {$l->created_at}\n\n";
}

if ($leaves->count() > 1) {
    // Pengajuan pertama (asli) vs pengajuan kedua (duplikat)
    $firstLeave = $leaves->first();
    $duplicateLeave = $leaves->last();

    echo ">>> Pengajuan asli   : ID #{$firstLeave->id} (Dibuat: {$firstLeave->created_at})\n";
    echo ">>> Pengajuan ganda  : ID #{$duplicateLeave->id} (Dibuat: {$duplicateLeave->created_at})\n\n";

    DB::transaction(function () use ($duplicateLeave) {
        $user = $duplicateLeave->user;
        $days = Carbon::parse($duplicateLeave->start_date)->diffInDays(Carbon::parse($duplicateLeave->end_date)) + 1;

        echo "User {$user->name} SEBELUM PERBAIKAN:\n";
        echo "  - Cuti Terpakai : {$user->leave_taken} Hari\n";
        echo "  - Sisa Cuti     : {$user->leave_balance} Hari\n\n";

        // 1. Kembalikan saldo di tabel users jika statusnya approved
        if ($duplicateLeave->status === 'approved') {
            $user->leave_taken = max(0, $user->leave_taken - $days);
            $user->leave_balance = $user->leave_balance + $days;
            $user->save();
        }

        // 2. Hapus pengajuan yang ganda
        $duplicateLeave->delete();

        echo "User {$user->name} SESUDAH PERBAIKAN:\n";
        echo "  - Cuti Terpakai : {$user->leave_taken} Hari\n";
        echo "  - Sisa Cuti     : {$user->leave_balance} Hari\n";
        echo "\n[SUKSES] Pengajuan duplikat ID #{$duplicateLeave->id} berhasil dihapus & saldo cuti dikembalikan {$days} hari!\n";
    });
} else {
    echo "[INFO] Hanya ada 1 pengajuan cuti untuk tanggal ini. Tidak ada duplikat.\n";
}

echo "\nSelesai!\n";
