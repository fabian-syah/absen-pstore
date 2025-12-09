@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="card-title">Manajemen Kasbon</h4>
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
                        <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#active" role="tab">Belum Lunas / Aktif</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history" role="tab">Riwayat Lunas / Ditolak</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="kasbonTabContent">
                    {{-- TAB 1: BELUM LUNAS --}}
                    <div class="tab-pane fade show active" id="active" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Tanggal</th>
                                        <th>Nominal</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeLoans as $loan)
                                        <tr>
                                            <td>{{ $loan->user->name }}</td>
                                            <td>{{ $loan->created_at->format('d M Y') }}</td>
                                            <td class="font-weight-bold text-danger">Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                                            <td>
                                                <small>{{ Str::limit($loan->description_1, 30) }}</small><br>
                                                <small class="text-muted">{{ Str::limit($loan->description_2, 30) }}</small>
                                            </td>
                                            <td>
                                                @if($loan->status == 'pending')
                                                    <span class="badge badge-warning">Menunggu Approval</span>
                                                @elseif($loan->status == 'approved')
                                                    <span class="badge badge-primary">Disetujui (Belum Bayar)</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('kasbon.show', $loan->id) }}" class="btn btn-info btn-sm icon-btn" title="Lihat Detail">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>

                                                @if(auth()->user()->role == 'admin')
                                                    <a href="{{ route('kasbon.edit', $loan->id) }}" class="btn btn-warning btn-sm icon-btn" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    
                                                    @if($loan->status == 'pending')
                                                        <form action="{{ route('kasbon.status', $loan->id) }}" method="POST" class="d-inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="approved">
                                                            <button class="btn btn-success btn-sm icon-btn" title="Approve"><i class="mdi mdi-check"></i></button>
                                                        </form>
                                                        <form action="{{ route('kasbon.status', $loan->id) }}" method="POST" class="d-inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button class="btn btn-danger btn-sm icon-btn" title="Reject"><i class="mdi mdi-close"></i></button>
                                                        </form>
                                                    @endif

                                                    @if($loan->status == 'approved')
                                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $loan->id }}">
                                                            Lunasi
                                                        </button>
                                                        
                                                        {{-- MODAL PELUNASAN --}}
                                                        <div class="modal fade" id="payModal{{ $loan->id }}" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Pelunasan Kasbon</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form action="{{ route('kasbon.pay', $loan->id) }}" method="POST" enctype="multipart/form-data">
                                                                        @csrf
                                                                        <div class="modal-body">
                                                                            <p>User: <strong>{{ $loan->user->name }}</strong></p>
                                                                            <p>Nominal: <strong>Rp {{ number_format($loan->amount, 0, ',', '.') }}</strong></p>
                                                                            <div class="form-group">
                                                                                <label>Upload Bukti Pelunasan (Transfer/Tunai)</label>
                                                                                <input type="file" name="repayment_proof" class="form-control" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="submit" class="btn btn-primary">Konfirmasi Lunas</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <form action="{{ route('kasbon.destroy', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger btn-sm icon-btn"><i class="mdi mdi-delete"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">Tidak ada tagihan aktif.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 2: HISTORY --}}
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Nominal</th>
                                        <th>Status</th>
                                        <th>Tgl Lunas/Reject</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($historyLoans as $loan)
                                        <tr>
                                            <td>{{ $loan->user->name }}</td>
                                            <td>Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                                            <td>
                                                @if($loan->status == 'paid')
                                                    <span class="badge badge-success">LUNAS</span>
                                                @else
                                                    <span class="badge badge-danger">DITOLAK</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($loan->repayment_date)
                                                    {{ \Carbon\Carbon::parse($loan->repayment_date)->format('d M Y') }}
                                                @else
                                                    {{ $loan->updated_at->format('d M Y') }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('kasbon.show', $loan->id) }}" class="btn btn-info btn-sm">Lihat Struk</a>
                                                @if(auth()->user()->role == 'admin')
                                                     <form action="{{ route('kasbon.destroy', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus history?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger btn-sm icon-btn"><i class="mdi mdi-delete"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">Belum ada riwayat.</td></tr>
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