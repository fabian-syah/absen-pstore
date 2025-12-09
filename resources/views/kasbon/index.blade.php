@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="card-title">Manajemen Kasbon & Cicilan</h4>
            <a href="{{ route('kasbon.create') }}" class="btn btn-primary text-white">
                <i class="mdi mdi-plus"></i> Ajukan Kasbon
            </a>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="kasbonTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#active" role="tab">Sedang Berjalan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#history" role="tab">Riwayat Lunas / Selesai</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- TAB AKTIF --}}
                    <div class="tab-pane fade show active" id="active">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Total Hutang</th>
                                        <th>Sisa Hutang</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeLoans as $loan)
                                        <tr>
                                            <td>{{ $loan->user->name }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                                                @if(\Carbon\Carbon::parse($loan->due_date)->isPast())
                                                    <span class="badge badge-danger">Lewat</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                                            <td class="text-danger font-weight-bold">
                                                Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                @if($loan->status == 'pending')
                                                    <span class="badge badge-warning">Menunggu Acc</span>
                                                @elseif($loan->status == 'approved')
                                                    <span class="badge badge-info">Sedang Dicicil</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- TOMBOL LIHAT DETAIL --}}
                                                <a href="{{ route('kasbon.show', $loan->id) }}" class="btn btn-inverse-info btn-sm icon-btn" title="Detail & History">
                                                    <i class="mdi mdi-file-document"></i>
                                                </a>

                                                {{-- LOGIKA TOMBOL BAYAR (Muncul untuk Peminjam) --}}
                                                @if($loan->status == 'approved' && auth()->id() == $loan->user_id)
                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $loan->id }}">
                                                        Bayar / Cicil
                                                    </button>
                                                @endif

                                                {{-- LOGIKA ADMIN APPROVE/REJECT PENGAJUAN AWAL --}}
                                                @if(auth()->user()->role == 'admin' && $loan->status == 'pending')
                                                    <form action="{{ route('kasbon.status', $loan->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button class="btn btn-success btn-sm icon-btn" title="Approve Pengajuan"><i class="mdi mdi-check"></i></button>
                                                    </form>
                                                    <form action="{{ route('kasbon.status', $loan->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button class="btn btn-danger btn-sm icon-btn" title="Tolak"><i class="mdi mdi-close"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- MODAL BAYAR CICILAN --}}
                                        <div class="modal fade" id="payModal{{ $loan->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Bayar Cicilan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('kasbon.installment.store', $loan->id) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-info">
                                                                Sisa Hutang: <strong>Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</strong>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Nominal Pembayaran (Rp)</label>
                                                                <input type="number" name="amount_paid" class="form-control" max="{{ $loan->remaining_amount }}" required>
                                                                <small>Bisa bayar sebagian atau lunas.</small>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Bukti Transfer</label>
                                                                <input type="file" name="payment_proof" class="form-control" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Kirim Pembayaran</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="6" class="text-center">Tidak ada kasbon aktif.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB HISTORY (Sama seperti sebelumnya) --}}
                    <div class="tab-pane fade" id="history">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tanggal Lunas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($historyLoans as $loan)
                                        <tr>
                                            <td>{{ $loan->user->name }}</td>
                                            <td>Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                                            <td>
                                                @if($loan->status == 'paid') <span class="badge badge-success">LUNAS</span>
                                                @else <span class="badge badge-danger">DITOLAK</span> @endif
                                            </td>
                                            <td>{{ $loan->repayment_date ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('kasbon.show', $loan->id) }}" class="btn btn-info btn-sm">Detail</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">Kosong.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection