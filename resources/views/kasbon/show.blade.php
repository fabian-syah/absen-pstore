@extends('layout.master')

@section('content')
<style>
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>

<div class="row justify-content-center">
    
    {{-- BAGIAN KIRI: INVOICE --}}
    <div class="col-md-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body" id="printableArea">
                <div class="text-center mb-4 border-bottom pb-3">
                    <h3 class="font-weight-bold text-primary">INVOICE KASBON</h3>
                    <h6 class="text-muted">ID: #KB-{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h6>
                    <div class="mt-2">
                        @if($kasbon->status == 'paid')
                            <h2 class="text-success border border-success d-inline-block p-2 rounded" style="transform: rotate(-5deg);">LUNAS</h2>
                        @elseif($kasbon->status == 'approved')
                            <h4 class="badge badge-outline-primary p-2">DICICIL / BELUM LUNAS</h4>
                        @else
                            <h4 class="badge badge-outline-warning p-2">{{ strtoupper($kasbon->status) }}</h4>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Peminjam</span> <strong>{{ $kasbon->user->name }}</strong>
                        </li>
                        <li class="list-group-item">
                            <span>Judul</span><br><strong>{{ $kasbon->title }}</strong>
                        </li>
                    </ul>
                </div>

                <div class="mb-4 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Pinjaman</span> <strong>Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sudah Dibayar</span> <strong class="text-success">Rp {{ number_format($kasbon->total_paid, 0, ',', '.') }}</strong>
                    </div>
                    <div class="border-top my-2"></div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Sisa Kewajiban</span>
                        <h4 class="text-danger font-weight-bold mb-0">Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</h4>
                    </div>
                </div>

                <table class="table table-bordered table-sm mb-3">
                    <tr><td class="bg-light">Jatuh Tempo</td><td>{{ \Carbon\Carbon::parse($kasbon->due_date)->format('d F Y') }}</td></tr>
                    <tr><td class="bg-light">Metode</td><td>{{ $kasbon->payment_method }} {{ $kasbon->payment_details }}</td></tr>
                </table>
            </div>
            <div class="card-footer no-print">
                <a href="{{ route('kasbon.index') }}" class="btn btn-light">Kembali</a>
                <button onclick="window.print()" class="btn btn-info text-white">Cetak</button>
            </div>
        </div>
    </div>

    {{-- BAGIAN KANAN: RIWAYAT --}}
    <div class="col-md-7 grid-margin stretch-card no-print">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Riwayat Pembayaran</h4>
                
                @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Diterima Oleh</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                @if(auth()->user()->role == 'admin') <th>Aksi</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasbon->installments as $ins)
                                <tr>
                                    <td>{{ $ins->created_at->format('d/m/y') }}</td>
                                    <td><span class="font-weight-bold">{{ $ins->received_by ?? '-' }}</span></td>
                                    <td class="text-primary font-weight-bold">Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</td>
                                    <td>
                                        @if($ins->status == 'pending') <span class="badge badge-warning">Pending</span>
                                        @elseif($ins->status == 'approved') <span class="badge badge-success">Diterima</span>
                                        @else <span class="badge badge-danger">Ditolak</span> @endif
                                        <br><a href="{{ asset('storage/'.$ins->payment_proof) }}" target="_blank" class="text-small">Bukti</a>
                                    </td>
                                    @if(auth()->user()->role == 'admin')
                                        <td>
                                            @if($ins->status == 'pending')
                                                <form action="{{ route('kasbon.installment.approve', $ins->id) }}" method="POST" class="d-inline">
                                                    @csrf <button class="btn btn-success btn-sm p-1"><i class="mdi mdi-check"></i></button>
                                                </form>
                                                <form action="{{ route('kasbon.installment.reject', $ins->id) }}" method="POST" class="d-inline">
                                                    @csrf <button class="btn btn-danger btn-sm p-1"><i class="mdi mdi-close"></i></button>
                                                </form>
                                            @endif
                                            <a href="{{ route('kasbon.installment.edit', $ins->id) }}" class="btn btn-warning btn-sm p-1"><i class="mdi mdi-pencil"></i></a>
                                            <form action="{{ route('kasbon.installment.destroy', $ins->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE') <button class="btn btn-dark btn-sm p-1" onclick="return confirm('Hapus?')"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection