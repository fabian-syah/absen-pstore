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
    
    @if(session('error'))
        <div class="col-12 mb-3">
            <div class="alert alert-danger font-weight-bold">
                <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            </div>
        </div>
    @endif
    @if(session('success'))
        <div class="col-12 mb-3">
            <div class="alert alert-success font-weight-bold">
                <i class="mdi mdi-check"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#active">Sedang Berjalan</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">Riwayat Selesai</a></li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- TAB AKTIF --}}
                    <div class="tab-pane fade show active" id="active">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama & Judul</th>
                                        <th>Metode Penerimaan</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Sisa Hutang</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeLoans as $loan)
                                        <tr class="{{ $loan->is_overdue ? 'table-danger' : '' }}">
                                            <td>
                                                <strong>{{ $loan->user->name }}</strong><br>
                                                <small class="text-muted">{{ $loan->title }}</small>
                                            </td>
                                            <td>
                                                @if($loan->payment_method == 'transfer')
                                                    <span class="badge badge-outline-primary btn-sm">Transfer</span><br>
                                                    <small style="font-size: 0.75rem;">{{ $loan->payment_details }}</small>
                                                @else
                                                    <span class="badge badge-outline-success btn-sm">Tunai (Cash)</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                                                @if($loan->is_overdue)
                                                    <br><span class="badge badge-danger mt-1">LEWAT JATUH TEMPO!</span>
                                                @endif
                                            </td>
                                            <td class="text-danger font-weight-bold">
                                                Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                @if($loan->status == 'pending') <span class="badge badge-warning">Menunggu</span>
                                                @elseif($loan->status == 'approved') <span class="badge badge-info">Dicicil</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('kasbon.show', $loan->id) }}" class="btn btn-inverse-info btn-sm icon-btn"><i class="mdi mdi-eye"></i></a>
                                                
                                                {{-- Tombol Bayar untuk User --}}
                                                @if($loan->status == 'approved' && auth()->id() == $loan->user_id)
                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $loan->id }}">
                                                        Bayar
                                                    </button>
                                                @endif

                                                {{-- Tombol Approve untuk Admin --}}
                                                @if(auth()->user()->role == 'admin' && $loan->status == 'pending')
                                                    <form action="{{ route('kasbon.status', $loan->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH') <input type="hidden" name="status" value="approved">
                                                        <button class="btn btn-success btn-sm icon-btn"><i class="mdi mdi-check"></i></button>
                                                    </form>
                                                    <form action="{{ route('kasbon.status', $loan->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH') <input type="hidden" name="status" value="rejected">
                                                        <button class="btn btn-danger btn-sm icon-btn"><i class="mdi mdi-close"></i></button>
                                                    </form>
                                                    <form action="{{ route('kasbon.destroy', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger btn-sm icon-btn"><i class="mdi mdi-delete"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- INCLUDE MODAL --}}
                                        @include('kasbon.partials.pay_modal', ['loan' => $loan])
                                    @empty
                                        <tr><td colspan="6" class="text-center">Tidak ada tagihan aktif.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- TAB HISTORY --}}
                    <div class="tab-pane fade" id="history">
                         <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tgl Selesai</th>
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

<script>
    function formatRupiahModal(input) {
        var angka = input.value.replace(/[^,\d]/g, '').toString();
        var split = angka.split(',');
        var sisa = split[0].length % 3;
        var rupiah = split[0].substr(0, sisa);
        var ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        input.value = rupiah;
    }
</script>
@endsection