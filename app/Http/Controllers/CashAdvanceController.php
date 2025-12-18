<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CashAdvanceController extends Controller
{
    // --- 1. HALAMAN UTAMA (INDEX) ---
    // --- 1. HALAMAN UTAMA (INDEX) ---
    public function index()
    {
        $user = auth()->user();

        // Query Dasar
        $query = CashAdvance::with('user')->latest();

        // Jika bukan Admin, hanya lihat punya sendiri
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // GANTI INI:
        // $kasbons = $query->get(); 

        // MENJADI INI (Angka 10 adalah jumlah baris per halaman):
        $kasbons = $query->paginate(10);

        return view('kasbon.index', compact('kasbons'));
    }

    // --- 2. FORM PENGAJUAN (CREATE) ---
    public function create()
    {
        // Ambil data user untuk dropdown (Khusus Admin bisa pilih user lain)
        $users = User::orderBy('name')->get();
        return view('kasbon.create', compact('users'));
    }

    // --- 3. PROSES SIMPAN PENGAJUAN (STORE) ---
    public function store(Request $request)
    {
        // Bersihkan format Rupiah (misal: 1.000.000 -> 1000000)
        $cleanAmount = str_replace('.', '', $request->amount);
        $request->merge(['amount' => $cleanAmount]);

        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'description' => 'required|string',
            'payment_method' => 'required|in:cash,transfer',
            'photo_1' => 'required|image|max:10240',
            'photo_2' => 'required|image|max:10240',
            // Validasi Transfer
            'bank_name' => 'required_if:payment_method,transfer',
            'account_number' => 'required_if:payment_method,transfer',
        ]);

        DB::transaction(function () use ($request, $cleanAmount) {
            // Tentukan User (Jika admin submit buat orang lain, atau user submit sendiri)
            $targetUser = auth()->user()->role == 'admin' ? User::find($request->user_id) : auth()->user();

            // Simpan Data
            $data = [
                'user_id' => $targetUser->id,
                'user_name' => $targetUser->name,
                'division' => $targetUser->division ?? 'Umum', // Asumsi ada kolom division di User, atau default
                'branch' => $targetUser->branch ?? 'Pusat',     // Asumsi ada kolom branch
                'amount' => $cleanAmount,
                'total_paid' => 0,
                'description' => $request->description,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ];

            // Gabungkan Info Rekening
            if ($request->payment_method == 'transfer') {
                $data['account_details'] = $request->bank_name . ' - ' . $request->account_number . ' a.n ' . $request->account_name;
            }

            // Upload Foto
            if ($request->hasFile('photo_1')) $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
            if ($request->hasFile('photo_2')) $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');

            CashAdvance::create($data);
        });

        return redirect()->route('kasbon.index')->with('success', 'Pengajuan Berhasil! Menunggu Approval.');
    }

    // --- 4. DETAIL & HISTORY BAYAR (SHOW) ---
    public function show($id)
    {
        $kasbon = CashAdvance::with('installments')->findOrFail($id);
        return view('kasbon.show', compact('kasbon'));
    }

    // --- 5. APPROVE / REJECT OLEH ADMIN ---
    public function updateStatus(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->update([
            'status' => $request->status, // 'approved' or 'rejected'
            'approved_date' => now()
        ]);

        return back()->with('success', 'Status kasbon diperbarui.');
    }

    // --- 6. PROSES BAYAR HUTANG (STORE INSTALLMENT) ---
    public function storeInstallment(Request $request, $id)
    {
        $cleanAmount = str_replace('.', '', $request->amount_paid);
        $request->merge(['amount_paid' => $cleanAmount]);

        $request->validate([
            'amount_paid' => 'required|numeric|min:1000',
            'payment_proof' => 'required|image|max:2048'
        ]);

        DB::transaction(function () use ($request, $id, $cleanAmount) {
            $kasbon = CashAdvance::findOrFail($id);

            // Simpan Riwayat
            $path = $request->file('payment_proof')->store('kasbon/installments', 'public');

            $ins = CashAdvanceInstallment::create([
                'cash_advance_id' => $kasbon->id,
                'user_id' => auth()->id(),
                'amount_paid' => $cleanAmount,
                'payment_proof' => $path,
                'status' => 'approved', // Langsung approved biar saldo motong (bisa diubah logicnya kalau mau pending dulu)
                'note' => $request->note
            ]);

            // Update Saldo Induk
            $kasbon->total_paid += $cleanAmount;

            // Cek Lunas
            if ($kasbon->total_paid >= $kasbon->amount) {
                $kasbon->status = 'paid';
            }

            $kasbon->save();
        });

        return back()->with('success', 'Pembayaran berhasil diterima. Saldo hutang berkurang.');
    }
}
