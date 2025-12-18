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

        // 2. Filter Role: Izinkan Admin DAN Admin Gaji melihat semua
        if (!in_array($user->role, ['admin', 'admin_gaji'])) {
            $query->where('user_id', $user->id);
        }

        // 3. LOGIKA FILTER
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 4. Hitung Statistik
        $statsQuery = clone $query;

        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'active' => (clone $statsQuery)->where('status', 'approved')->where('total_paid', '<', DB::raw('amount'))->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
            'total_active_amount' => (clone $statsQuery)->where('status', 'approved')->get()->sum('remaining_amount'),
        ];

        // 5. Pagination
        $kasbons = $query->paginate(10)->withQueryString();

        return view('kasbon.index', compact('kasbons', 'stats'));
    }

    // --- FUNGSI EXPORT KE EXCEL (CSV) ---
    public function export(Request $request)
    {
        $fileName = 'laporan-kasbon-' . date('Y-m-d') . '.csv';

        $query = CashAdvance::with('user')->latest();

        // Filter Role untuk Export
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) {
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
        $users = User::orderBy('name')->get();
        return view('kasbon.create', compact('users'));
    }

    // --- 3. PROSES SIMPAN PENGAJUAN (STORE) ---
    public function store(Request $request)
    {
        $cleanAmount = str_replace('.', '', $request->amount);
        $request->merge(['amount' => $cleanAmount]);

        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'description' => 'required|string',
            'payment_method' => 'required|in:cash,transfer',
            'photo_1' => 'required|image|max:10240',
            'photo_2' => 'required|image|max:10240',
            'bank_name' => 'required_if:payment_method,transfer',
            'account_number' => 'required_if:payment_method,transfer',
        ]);

        DB::transaction(function () use ($request, $cleanAmount) {
            // Admin & Admin Gaji bisa pilih user lain
            $isAdmin = in_array(auth()->user()->role, ['admin', 'admin_gaji']);
            $targetUser = $isAdmin ? User::find($request->user_id) : auth()->user();

            $data = [
                'user_id' => $targetUser->id,
                'user_name' => $targetUser->name,
                'division' => $targetUser->division ?? 'Umum',
                'branch' => $targetUser->branch ?? 'Pusat',
                'amount' => $cleanAmount,
                'total_paid' => 0,
                'description' => $request->description,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ];

            if ($request->payment_method == 'transfer') {
                $data['account_details'] = $request->bank_name . ' - ' . $request->account_number . ' a.n ' . $request->account_name;
            }

            if ($request->hasFile('photo_1')) $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
            if ($request->hasFile('photo_2')) $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');

            CashAdvance::create($data);
        });

        return redirect()->route('kasbon.index')->with('success', 'Pengajuan Berhasil! Menunggu Approval.');
    }

    // --- 4. DETAIL (SHOW) ---
    public function show($id)
    {
        $kasbon = CashAdvance::with('installments')->findOrFail($id);
        return view('kasbon.show', compact('kasbon'));
    }

    // --- 5. APPROVE / REJECT (ADMIN & ADMIN GAJI) ---
    public function updateStatus(Request $request, $id)
    {
        // Cek Hak Akses
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->update([
            'status' => $request->status,
            'approved_date' => now()
        ]);

        return back()->with('success', 'Status kasbon diperbarui.');
    }

    // --- 6. BAYAR CICILAN ---
    public function storeInstallment(Request $request, $id)
    {
        // 1. BERSIHKAN DATA
        $input = $request->all();
        if ($request->has('amount_paid')) {
            $input['amount_paid'] = str_replace('.', '', $request->amount_paid);
        }
        $request->replace($input);

        $kasbon = CashAdvance::findOrFail($id);

        // 2. VALIDASI (MAX PAYMENT CHECK)
        $request->validate([
            'amount_paid' => 'required|numeric|min:1000|max:' . $kasbon->remaining_amount, // GABOLEH LEBIH DARI SISA
            'payment_proof' => 'required|image|max:10240'
        ], [
            'amount_paid.max' => 'Nominal pembayaran melebihi sisa hutang (Maks: Rp ' . number_format($kasbon->remaining_amount, 0, ',', '.') . ').',
            'amount_paid.min' => 'Nominal pembayaran minimal Rp 1.000.',
            'payment_proof.required' => 'Bukti transfer wajib diupload.',
        ]);

        // 3. PROSES SIMPAN (STATUS PENDING)
        DB::transaction(function () use ($request, $id) {
            $path = $request->file('payment_proof')->store('kasbon/installments', 'public');

            CashAdvanceInstallment::create([
                'cash_advance_id' => $id,
                'user_id' => auth()->id(),
                'amount_paid' => $request->amount_paid,
                'payment_proof' => $path,
                'status' => 'pending', // <--- PENDING DULU (Tunggu Admin)
                'note' => $request->note
            ]);
        });

        return back()->with('success', 'Pembayaran berhasil dikirim. Menunggu verifikasi Admin.');
    }

    // --- 7. APPROVE CICILAN (ADMIN ONLY) ---
    public function approveInstallment($installmentId)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);

        DB::transaction(function () use ($installmentId) {
            $ins = CashAdvanceInstallment::with('cashAdvance')->findOrFail($installmentId);

            // Cek Double Approve
            if ($ins->status == 'approved') return;

            // Update Status Cicilan
            $ins->update(['status' => 'approved']);

            // Update Saldo Induk
            $kasbon = $ins->cashAdvance;
            $kasbon->total_paid += $ins->amount_paid;

            // Cek Lunas
            if ($kasbon->total_paid >= $kasbon->amount) {
                $kasbon->status = 'paid';
            }
            $kasbon->save();
        });

        return back()->with('success', 'Pembayaran telah disetujui. Saldo hutang berkurang.');
    }

    // --- 8. REJECT CICILAN (ADMIN ONLY) ---
    public function rejectInstallment(Request $request, $installmentId)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);

        $ins = CashAdvanceInstallment::findOrFail($installmentId);
        $ins->update([
            'status' => 'rejected',
            'note' => $ins->note . ' [DITOLAK ADMIN: ' . $request->reason . ']' // Tambahkan alasan ke note
        ]);

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
