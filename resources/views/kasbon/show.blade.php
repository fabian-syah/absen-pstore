@extends('layout.master')

@section('content')
<style>
    .progress-bar-striped { background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent); background-size: 1rem 1rem; }
</style>

<div class="container-fluid">
    <div class="row">
        {{-- PANEL KIRI: Detail Hutang --}}
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body position-relative">
                    {{-- Status Badge Pojok --}}
                    <div class="position-absolute top-0 end-0 m-3">
                        @if($kasbon->status == 'paid') <span class="badge bg-success fs-6">LUNAS</span>
                        @elseif($kasbon->status == 'approved') <span class="badge bg-primary fs-6">AKTIF</span>
                        @elseif($kasbon->status == 'rejected') <span class="badge bg-danger fs-6">DITOLAK</span>
                        @else <span class="badge bg-warning text-dark fs-6">PENDING</span> @endif
                    </div>

                    <h5 class="card-title fw-bold text-muted mb-4">DETAIL KASBON</h5>

                    {{-- Profil User --}}
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width:50px; height:50px; font-size:1.2em">
                            {{ substr($kasbon->user_name, 0, 1) }}
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $kasbon->user_name }}</h5>
                            <small class="text-muted">{{ $kasbon->division }} - {{ $kasbon->branch }}</small>
                        </div>
                    </div>

                    {{-- Angka Keuangan --}}
                    <div class="row text-center mb-4">
                        <div class="col-6 border-end">
                            <small class="text-muted text-uppercase">Total Pinjam</small>
                            <h4 class="fw-bold text-dark">Rp {{ number_format($kasbon->amount, 0,',','.') }}</h4>
                        </div>
                        <div class="col-6">
                            <small class="text-muted text-uppercase">Sisa Hutang</small>
                            <h4 class="fw-bold text-danger">Rp {{ number_format($kasbon->remaining_amount, 0,',','.') }}</h4>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    @php 
                        $percent = ($kasbon->total_paid / $kasbon->amount) * 100;
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Progress Bayar</small>
                            <small class="fw-bold">{{ number_format($percent,0) }}%</small>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    {{-- Detail Lain --}}
                    <div class="bg-light p-3 rounded mb-3">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" width="35%">Tgl Pengajuan</td>
                                <td class="fw-bold">{{ $kasbon->created_at->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Metode Cair</td>
                                <td class="fw-bold text-uppercase">{{ $kasbon->payment_method }}</td>
                            </tr>
                            @if($kasbon->payment_method == 'transfer')
                            <tr>
                                <td class="text-muted">Rekening</td>
                                <td>{{ $kasbon->account_details }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Ket.</td>
                                <td>{{ $kasbon->description }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    {{-- Bukti Foto --}}
                    <div class="d-flex gap-2">
                        <a href="{{ asset('storage/'.$kasbon->photo_1) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-50">Lihat Foto 1</a>
                        <a href="{{ asset('storage/'.$kasbon->photo_2) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-50">Lihat Foto 2</a>
                    </div>

                    {{-- TOMBOL ACTION ADMIN --}}
                    @if(auth()->user()->role == 'admin' && $kasbon->status == 'pending')
                    <div class="mt-4 pt-3 border-top">
                        <form action="{{ route('kasbon.status', $kasbon->id) }}" method="POST">
                            @csrf
                            <button name="status" value="approved" class="btn btn-success w-100 mb-2 fw-bold">APPROVE (Setujui)</button>
                            <button name="status" value="rejected" class="btn btn-danger w-100">REJECT (Tolak)</button>
                        </form>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- PANEL KANAN: Form Bayar & History --}}
        <div class="col-md-7">
            
            {{-- FORM BAYAR (Hanya muncul jika Status Approved & Belum Lunas) --}}
            @if($kasbon->status == 'approved' && $kasbon->remaining_amount > 0)
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="mdi mdi-wallet"></i> FORM BAYAR HUTANG</h5>
                    <form action="{{ route('kasbon.pay', $kasbon->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="small mb-1 text-white-50">Nominal Bayar (Rp)</label>
                                <input type="text" name="amount_paid" class="form-control fw-bold text-dark" placeholder="Contoh: 500.000" id="bayar_rp" required>
                            </div>
                            <div class="col-md-5">
                                <label class="small mb-1 text-white-50">Bukti Transfer / Catatan</label>
                                <input type="file" name="payment_proof" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-light text-primary fw-bold w-100">BAYAR</button>
                            </div>
                        </div>
                        <input type="text" name="note" class="form-control mt-2 form-control-sm bg-primary border-0 text-white placeholder-white" placeholder="Catatan opsional (misal: Potong Gaji Bulan ini)...">
                    </form>
                </div>
            </div>
            @endif

            {{-- HISTORY TABLE --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Riwayat Pembayaran Masuk</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Nominal Masuk</th>
                                    <th>Oleh</th>
                                    <th>Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasbon->installments as $ins)
                                <tr>
                                    <td class="ps-4">{{ $ins->created_at->format('d M Y H:i') }}</td>
                                    <td class="fw-bold text-success">+ Rp {{ number_format($ins->amount_paid, 0,',','.') }}</td>
                                    <td>{{ $ins->user->name }}</td>
                                    <td>
                                        <a href="{{ asset('storage/'.$ins->payment_proof) }}" target="_blank" class="btn btn-xs btn-light border"><i class="mdi mdi-image"></i></a>
                                        @if($ins->note) <i class="mdi mdi-information text-muted" title="{{ $ins->note }}"></i> @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada pembayaran masuk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const inputBayar = document.getElementById('bayar_rp');
    if(inputBayar){
        inputBayar.addEventListener('keyup', function(e){
            this.value = formatRupiah(this.value);
        });
    }
    // Fungsi formatRupiah sama dengan di create
    function formatRupiah(angka){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split   = number_string.split(','),
        sisa    = split[0].length % 3,
        rupiah  = split[0].substr(0, sisa),
        ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }
</script>
@endsection