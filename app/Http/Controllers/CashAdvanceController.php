<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\CashAdvancePlan;
use App\Models\User;
use Carbon\Carbon;
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
        $isAdmin = in_array($user->role, ['admin', 'admin_gaji']);

        // 1. Base Query
        $query = CashAdvance::with('user')->latest();

        // 2. Filter Role (User Biasa cuma lihat punya sendiri)
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // 3. FILTER TAMPILAN: AKTIF vs RIWAYAT (LUNAS/TOLAK)
        $viewType = $request->get('view_type', 'active'); // Default ke Active

        if ($viewType == 'history') {
            // Tampilkan HANYA yang Lunas atau Ditolak
            $query->whereIn('status', ['paid', 'rejected']);
        } else {
            // Tampilkan yang Pending atau Approved (Masih Punya Hutang)
            $query->whereIn('status', ['pending', 'approved']);
        }

        // 4. FILTER PENCARIAN & TANGGAL
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

        // 5. HITUNG STATISTIK (GLOBAL - Tidak terpengaruh filter tab)
        // Kita buat query baru agar angkanya tetap total keseluruhan
        $globalQuery = CashAdvance::query();
        if (!$isAdmin) {
            $globalQuery->where('user_id', $user->id);
        }

        $stats = [
            'pending' => (clone $globalQuery)->where('status', 'pending')->count(),
            'active' => (clone $globalQuery)->where('status', 'approved')->where('total_paid', '<', DB::raw('amount'))->count(),
            'paid' => (clone $globalQuery)->where('status', 'paid')->count(),
            'total_active_amount' => (clone $globalQuery)->where('status', 'approved')->get()->sum('remaining_amount'),
        ];

        // 6. Pagination
        $kasbons = $query->paginate(10)->withQueryString();

        return view('kasbon.index', compact('kasbons', 'stats', 'viewType'));
    }

    // --- FUNGSI EXPORT KE EXCEL (CSV) ---
    public function export(Request $request)
    {
        $fileName = 'laporan-kasbon-' . date('Y-m-d') . '.csv';
        $query = CashAdvance::with('user')->latest();

        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) {
            $query->where('user_id', auth()->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('start_date'))
            $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date'))
            $query->whereDate('created_at', '<=', $request->end_date);

        $kasbons = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Tanggal', 'Nama Karyawan', 'Divisi', 'Cabang', 'Nominal Pinjam', 'Potongan/Bulan', 'Lama Cicilan', 'Sudah Bayar', 'Sisa Hutang', 'Status', 'Keterangan'];

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
                    $k->monthly_deduction ?? 0,
                    ($k->installment_months ?? 0) . ' Bulan',
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
        $cleanDeduction = str_replace('.', '', $request->monthly_deduction ?? '0');
        $request->merge(['amount' => $cleanAmount, 'monthly_deduction' => $cleanDeduction]);

        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'monthly_deduction' => 'nullable|numeric|min:0',
            'installment_months' => 'nullable|integer|min:1|max:60',
            'description' => 'required|string',
            'payment_method' => 'required|in:cash,transfer',
            'photo_1' => 'required|image|max:10240',
            'photo_2' => 'required|image|max:10240',
            'bank_name' => 'required_if:payment_method,transfer',
            'account_number' => 'required_if:payment_method,transfer',
        ]);

        DB::transaction(function () use ($request, $cleanAmount, $cleanDeduction) {
            $isAdmin = in_array(auth()->user()->role, ['admin', 'admin_gaji']);
            $targetUser = $isAdmin ? User::find($request->user_id) : auth()->user();

            // Auto-hitung installment_months jika monthly_deduction diisi
            $installmentMonths = $request->installment_months;
            if ($cleanDeduction > 0 && !$installmentMonths) {
                $installmentMonths = ceil($cleanAmount / $cleanDeduction);
            }

            $data = [
                'user_id' => $targetUser->id,
                'user_name' => $targetUser->name,
                'division' => $targetUser->division->name ?? 'Umum',
                'branch' => $targetUser->branch->name ?? 'Pusat',
                'amount' => $cleanAmount,
                'monthly_deduction' => $cleanDeduction > 0 ? $cleanDeduction : null,
                'installment_months' => $installmentMonths,
                'total_paid' => 0,
                'description' => $request->description,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ];

            if ($request->payment_method == 'transfer') {
                $data['account_details'] = $request->bank_name . ' - ' . $request->account_number . ' a.n ' . $request->account_name;
            }

            if ($request->hasFile('photo_1'))
                $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
            if ($request->hasFile('photo_2'))
                $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');

            $kasbon = CashAdvance::create($data);

            // Generate jadwal cicilan otomatis jika ada potongan per bulan
            if ($cleanDeduction > 0 && $installmentMonths > 0) {
                $remaining = $cleanAmount;
                for ($i = 1; $i <= $installmentMonths; $i++) {
                    $deductionAmount = min($cleanDeduction, $remaining);
                    CashAdvancePlan::create([
                        'cash_advance_id' => $kasbon->id,
                        'installment_order' => $i,
                        'due_date' => Carbon::now()->addMonths($i)->startOfMonth()->format('Y-m-d'),
                        'amount' => $deductionAmount,
                        'is_paid' => false,
                    ]);
                    $remaining -= $deductionAmount;
                    if ($remaining <= 0) break;
                }
            }
        });

        return redirect()->route('kasbon.index')->with('success', 'Pengajuan Berhasil! Menunggu Approval.');
    }

    // --- 4. DETAIL (SHOW) ---
    public function show($id)
    {
        $kasbon = CashAdvance::with('installments')->findOrFail($id);

        // Cari kasbon aktif lain milik user ini (yang statusnya approved dan belum lunas)
        $outstandingKasbons = CashAdvance::where('user_id', $kasbon->user_id)
            ->where('id', '!=', $kasbon->id)
            ->where('status', 'approved')
            ->whereRaw('amount > total_paid')
            ->get();

        $totalOutstanding = $outstandingKasbons->sum('remaining_amount');

        return view('kasbon.show', compact('kasbon', 'totalOutstanding'));
    }

    // --- 5. UPDATE STATUS (APPROVE/REJECT) ---
    public function updateStatus(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji']))
            abort(403);

        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->update([
            'status' => $request->status,
            'approved_date' => now()
        ]);

        return back()->with('success', 'Status kasbon diperbarui.');
    }

    // --- BULK APPROVE (NEW) ---
    public function bulkApprove(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji']))
            abort(403);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:cash_advances,id'
        ]);

        $count = CashAdvance::whereIn('id', $request->ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_date' => now()
            ]);

        return back()->with('success', "$count Pengajuan kasbon berhasil disetujui sekaligus.");
    }

    // --- 6. BAYAR CICILAN ---
    public function storeInstallment(Request $request, $id)
    {
        $input = $request->all();
        if ($request->has('amount_paid')) {
            $input['amount_paid'] = str_replace('.', '', $request->amount_paid);
        }
        $request->replace($input);

        $kasbon = CashAdvance::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:1000|max:' . $kasbon->remaining_amount,
            'payment_proof' => 'required|image|max:10240'
        ]);

        DB::transaction(function () use ($request, $id) {
            $path = $request->file('payment_proof')->store('kasbon/installments', 'public');

            CashAdvanceInstallment::create([
                'cash_advance_id' => $id,
                'user_id' => auth()->id(),
                'amount_paid' => $request->amount_paid,
                'payment_proof' => $path,
                'status' => 'pending',
                'note' => $request->note
            ]);
        });

        return back()->with('success', 'Pembayaran berhasil dikirim. Menunggu verifikasi Admin.');
    }

    // --- 7. APPROVE CICILAN ---
    public function approveInstallment($installmentId)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji']))
            abort(403);

        DB::transaction(function () use ($installmentId) {
            $ins = CashAdvanceInstallment::with('cashAdvance')->findOrFail($installmentId);
            if ($ins->status == 'approved')
                return;

            $ins->update(['status' => 'approved']);

            $kasbon = $ins->cashAdvance;
            $kasbon->total_paid += $ins->amount_paid;

            // Jika lunas, update status induk jadi 'paid'
            if ($kasbon->total_paid >= $kasbon->amount) {
                $kasbon->status = 'paid';
            }
            $kasbon->save();
        });

        return back()->with('success', 'Pembayaran telah disetujui. Saldo hutang berkurang.');
    }

    // --- 8. REJECT CICILAN ---
    public function rejectInstallment(Request $request, $installmentId)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji']))
            abort(403);

        $ins = CashAdvanceInstallment::findOrFail($installmentId);
        $ins->update([
            'status' => 'rejected',
            'note' => $ins->note . ' [DITOLAK: ' . $request->reason . ']'
        ]);

        return back()->with('success', 'Pembayaran ditolak.');
    }

    // --- HALAMAN VERIFIKASI PEMBAYARAN MASUK ---
    public function incomingInstallments()
    {
        // Hanya Admin & Admin Gaji
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji']))
            abort(403);

        // Ambil semua cicilan yang statusnya PENDING
        $pendingInstallments = CashAdvanceInstallment::with(['user', 'cashAdvance'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('kasbon.verification', compact('pendingInstallments'));
    }

    // --- KALENDER JADWAL CICILAN (API JSON) ---
    public function calendarData(Request $request)
    {
        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'admin_gaji']);

        $query = CashAdvance::with('plans')
            ->where('status', 'approved')
            ->whereRaw('amount > total_paid');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $kasbons = $query->get();
        $events = [];

        foreach ($kasbons as $kasbon) {
            // Kasbon DENGAN jadwal cicilan (CashAdvancePlan)
            if ($kasbon->plans->count() > 0) {
                foreach ($kasbon->plans as $plan) {
                    $events[] = [
                        'id' => 'plan_' . $plan->id,
                        'title' => ($isAdmin ? $kasbon->user_name . ' - ' : '') . 'Rp ' . number_format($plan->amount, 0, ',', '.'),
                        'start' => $plan->due_date,
                        'color' => $plan->is_paid ? '#10b981' : '#f59e0b',
                        'textColor' => $plan->is_paid ? '#fff' : '#000',
                        'extendedProps' => [
                            'kasbon_id' => $kasbon->id,
                            'user_name' => $kasbon->user_name,
                            'installment_order' => $plan->installment_order,
                            'amount' => $plan->amount,
                            'is_paid' => $plan->is_paid,
                            'total_pinjaman' => $kasbon->amount,
                            'sisa_hutang' => $kasbon->remaining_amount,
                        ],
                    ];
                }
            } else {
                // Kasbon TANPA jadwal cicilan — tampilkan sisa hutang langsung
                $remaining = $kasbon->remaining_amount;

                // Cek apakah ada riwayat cicilan untuk menentukan potongan nyata
                $avgPayment = $kasbon->installments()
                    ->where('status', 'approved')
                    ->avg('amount_paid');

                $deduction = $kasbon->monthly_deduction;
                if ((!$deduction || $deduction <= 0) && $avgPayment > 0) {
                    // Gunakan rata-rata pembayaran nyata
                    $deduction = round($avgPayment);
                }

                if ($deduction > 0) {
                    // Ada potongan nyata — generate jadwal berdasarkan data asli
                    $startDate = Carbon::now()->addMonth()->startOfMonth();
                    $tempRemaining = $remaining;
                    $order = 1;

                    while ($tempRemaining > 0) {
                        $cicilan = min($deduction, $tempRemaining);
                        $dueDate = $startDate->copy()->addMonths($order - 1);

                        $events[] = [
                            'id' => 'virtual_' . $kasbon->id . '_' . $order,
                            'title' => ($isAdmin ? $kasbon->user_name . ' - ' : '') . 'Rp ' . number_format($cicilan, 0, ',', '.'),
                            'start' => $dueDate->format('Y-m-d'),
                            'color' => '#f59e0b',
                            'textColor' => '#000',
                            'extendedProps' => [
                                'kasbon_id' => $kasbon->id,
                                'user_name' => $kasbon->user_name,
                                'installment_order' => $order,
                                'amount' => $cicilan,
                                'is_paid' => false,
                                'total_pinjaman' => $kasbon->amount,
                                'sisa_hutang' => $kasbon->remaining_amount,
                            ],
                        ];

                        $tempRemaining -= $cicilan;
                        $order++;
                        if ($order > 60) break;
                    }
                } else {
                    // Tidak ada data potongan — tampilkan 1 event sisa hutang per bulan
                    $events[] = [
                        'id' => 'sisa_' . $kasbon->id,
                        'title' => ($isAdmin ? $kasbon->user_name . ' - ' : '') . 'Sisa Rp ' . number_format($remaining, 0, ',', '.'),
                        'start' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                        'color' => '#6366f1',
                        'textColor' => '#fff',
                        'extendedProps' => [
                            'kasbon_id' => $kasbon->id,
                            'user_name' => $kasbon->user_name,
                            'installment_order' => 0,
                            'amount' => $remaining,
                            'is_paid' => false,
                            'total_pinjaman' => $kasbon->amount,
                            'sisa_hutang' => $remaining,
                        ],
                    ];
                }
            }
        }

        return response()->json($events);
    }

    // --- HALAMAN KALENDER KASBON ---
    public function calendar()
    {
        return view('kasbon.calendar');
    }
}
