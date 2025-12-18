<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class CashAdvanceController extends Controller
{
    // --- 1. HALAMAN UTAMA (INDEX) ---
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Base Query
        $query = CashAdvance::with('user')->latest();

        // 2. Filter Role (Sama seperti sebelumnya)
        if ($user->role !== 'admin,admin_gaji') {
            $query->where('user_id', $user->id);
        }

        // 3. LOGIKA FILTER (Baru)
        // Filter Search (Nama Karyawan / Keterangan)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Tanggal (Start Date)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        // Filter Tanggal (End Date)
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 4. Hitung Statistik (Untuk Kartu di Atas) - Dihitung sebelum paginate
        // Clone query agar filter tetap berlaku pada statistik
        $statsQuery = clone $query;

        // Jika user memfilter, statistik akan mengikuti hasil filter. 
        // Jika ingin statistik global (abaikan filter), ganti $statsQuery dengan CashAdvance::query()

        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'active' => (clone $statsQuery)->where('status', 'approved')->where('total_paid', '<', \DB::raw('amount'))->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
            'total_active_amount' => (clone $statsQuery)->where('status', 'approved')->get()->sum('remaining_amount'),
        ];

        // 5. Eksekusi Pagination (Append query string agar filter tidak hilang saat pindah halaman)
        $kasbons = $query->paginate(10)->withQueryString();

        return view('kasbon.index', compact('kasbons', 'stats'));
    }

    // --- FUNGSI EXPORT KE EXCEL (CSV) ---
    public function export(Request $request)
    {
        $fileName = 'laporan-kasbon-' . date('Y-m-d') . '.csv';

        // Ambil data sesuai filter yang dikirim dari Index
        $query = CashAdvance::with('user')->latest();

        // (Copy logika filter dari index ke sini agar hasil export sesuai tampilan)
        if (auth()->user()->role !== 'admin,admin_gaji') {
            $query->where('user_id', auth()->user()->id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);

        $kasbons = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Tanggal', 'Nama Karyawan', 'Divisi', 'Cabang', 'Nominal Pinjam', 'Sudah Bayar', 'Sisa Hutang', 'Status', 'Keterangan'];

        $callback = function () use ($kasbons, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($kasbons as $k) {
                // Parsing JSON manual seperti di View
                $div = json_decode($k->division)->name ?? $k->division;
                $branch = json_decode($k->branch)->name ?? $k->branch;

                fputcsv($file, [
                    $k->created_at->format('Y-m-d'),
                    $k->user_name,
                    $div,
                    $branch,
                    $k->amount,
                    $k->total_paid,
                    $k->remaining_amount,
                    strtoupper($k->status),
                    $k->description
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
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
            $targetUser = auth()->user()->role == 'admin,admin_gaji' ? User::find($request->user_id) : auth()->user();

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
        if (auth()->user()->role !== 'admin,admin_gaji') abort(403);

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
