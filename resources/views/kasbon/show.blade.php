@extends('layouts.app')

@section('content')
<div class="row">
    {{-- DETAIL KASBON --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Detail Pinjaman</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Peminjam</span> <strong>{{ $kasbon->user->name }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total Pinjaman</span> <strong>Rp {{ number_format($kasbon->amount, 0) }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between bg-light">
                        <span>Sudah Dibayar</span> <strong class="text-success">Rp {{ number_format($kasbon->total_paid, 0) }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Sisa Hutang</span> <strong class="text-danger">Rp {{ number_format($kasbon->remaining_amount, 0) }}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>Jatuh Tempo:</span> <br>
                        <strong>{{ \Carbon\Carbon::parse($kasbon->due_date)->format('d F Y') }}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>Ket:</span> <br> {{ $kasbon->description_1 }}
                    </li>
                </ul>
                <div class="mt-3">
                    <p class="mb-1">Bukti Pengajuan:</p>
                    <a href="{{ asset('storage/'.$kasbon->photo_1) }}" target="_blank">
                        <img src="{{ asset('storage/'.$kasbon->photo_1) }}" class="img-thumbnail" style="width: 80px">
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- RIWAYAT CICILAN & APPROVAL --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Riwayat Cicilan / Pembayaran</h4>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tgl Bayar</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                @if(auth()->user()->role == 'admin') <th>Aksi Admin</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasbon->installments as $ins)
                                <tr>
                                    <td>{{ $ins->created_at->format('d/m/Y') }}</td>
                                    <td>Rp {{ number_format($ins->amount_paid, 0) }}</td>
                                    <td>
                                        <a href="{{ asset('storage/'.$ins->payment_proof) }}" target="_blank">Lihat Foto</a>
                                    </td>
                                    <td>
                                        @if($ins->status == 'pending') <span class="badge badge-warning">Menunggu Verifikasi</span>
                                        @elseif($ins->status == 'approved') <span class="badge badge-success">Diterima</span>
                                        @else <span class="badge badge-danger">Ditolak</span> @endif
                                    </td>
                                    
                                    {{-- AKSI APPROVE CICILAN OLEH ADMIN --}}
                                    @if(auth()->user()->role == 'admin' && $ins->status == 'pending')
                                        <td>
                                            <form action="{{ route('kasbon.installment.approve', $ins->id) }}" method="POST" class="d-inline">
                                                @csrf 
                                                <button class="btn btn-success btn-xs" onclick="return confirm('Terima pembayaran ini?')">Terima</button>
                                            </form>
                                            <form action="{{ route('kasbon.installment.reject', $ins->id) }}" method="POST" class="d-inline">
                                                @csrf 
                                                <button class="btn btn-danger btn-xs" onclick="return confirm('Tolak pembayaran ini?')">Tolak</button>
                                            </form>
                                        </td>
                                    @elseif(auth()->user()->role == 'admin')
                                        <td><small class="text-muted">Selesai</small></td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Belum ada pembayaran cicilan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection