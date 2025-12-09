@extends('layout.master')

@section('content')
<style>
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
    /* Styling Progress Bar */
    .progress-custom {
        height: 25px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .progress-bar-custom {
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        background: linear-gradient(90deg, #4b49ac 0%, #908ce9 100%);
        transition: width 0.6s ease;
    }
</style>

<div class="row justify-content-center">
    
    {{-- BAGIAN KIRI: DETAIL INVOICE & PROGRESS --}}
    <div class="col-md-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body" id="printableArea">
                {{-- HEADER --}}
                <div class="text-center mb-4 border-bottom pb-3">
                    <h3 class="font-weight-bold text-primary">DETAIL KASBON</h3>
                    <h6 class="text-muted">ID: #KB-{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h6>
                    <div class="mt-2">
                        @if($kasbon->status == 'paid') 
                            <span class="badge badge-success px-3 py-2" style="font-size: 1em">LUNAS SELESAI</span>
                        @elseif($kasbon->status == 'approved') 
                            <span class="badge badge-info px-3 py-2">SEDANG BERJALAN</span>
                        @elseif($kasbon->status == 'rejected')
                            <span class="badge badge-danger px-3 py-2">DITOLAK</span>
                        @else 
                            <span class="badge badge-warning px-3 py-2">MENUNGGU KONFIRMASI</span> 
                        @endif
                    </div>
                </div>

                {{-- INFO UTAMA --}}
                <div class="mb-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Peminjam</span> 
                            <strong class="text-dark">{{ $kasbon->user->name }}</strong>
                        </li>
                        <li class="list-group-item">
                            <span class="text-muted">Keperluan</span><br>
                            <strong>{{ $kasbon->title }}</strong>
                        </li>
                    </ul>
                </div>

                {{-- PROGRESS BAR KEUANGAN (KONSEP BARU) --}}
                <div class="mb-4 p-3 bg-light rounded border">
                    @php
                        $persen = $kasbon->amount > 0 ? ($kasbon->total_paid / $kasbon->amount) * 100 : 0;
                        $persen = min($persen, 100); // Mentok di 100%
                    @endphp
                    
                    <div class="d-flex justify-content-between mb-1">
                        <small>Progress Pelunasan</small>
                        <small class="font-weight-bold">{{ number_format($persen, 0) }}%</small>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: {{ $persen }}%">
                            {{ number_format($persen, 0) }}%
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <div class="text-start">
                            <small class="text-muted d-block">Total Pinjaman</small>
                            <h5 class="font-weight-bold text-dark">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</h5>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Sisa Hutang</small>
                            <h5 class="font-weight-bold text-danger">Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>

                {{-- TABEL ESTIMASI (LEBIH SANTAI) --}}
                <div class="mb-3">
                    <h6 class="text-muted font-weight-bold mb-3"><i class="mdi mdi-calendar-text"></i> Estimasi / Target Rencana</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            <thead class="text-muted border-bottom">
                                <tr>
                                    <th>Cicilan</th>
                                    <th>Target Tanggal</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $accumulated = 0; @endphp
                                @foreach($kasbon->plans as $plan)
                                    @php 
                                        $accumulated += $plan->amount; 
                                        // Cek apakah target ini sudah tertutup oleh total bayar
                                        $isCovered = $kasbon->total_paid >= $accumulated;
                                    @endphp
                                    <tr style="border-bottom: 1px dashed #eee;">
                                        <td class="py-2">Ke-{{ $plan->installment_order }}</td>
                                        <td class="py-2 text-muted">{{ \Carbon\Carbon::parse($plan->due_date)->format('d M Y') }}</td>
                                        <td class="py-2 text-end">Rp {{ number_format($plan->amount, 0, ',', '.') }}</td>
                                        <td class="py-2 text-center">
                                            @if($isCovered) 
                                                <i class="mdi mdi-checkbox-marked-circle text-success" title="Sudah Tertutup"></i>
                                            @else 
                                                <i class="mdi mdi-checkbox-blank-circle-outline text-muted" title="Belum"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2 fst-italic text-center">* Tabel di atas hanya estimasi. Pembayaran bersifat fleksibel selama lunas sebelum tanggal jatuh tempo akhir.</small>
                </div>

                {{-- INFO TAMBAHAN --}}
                <table class="table table-bordered table-sm mb-3 mt-4">
                    <tr>
                        <td class="bg-light" width="40%">Jatuh Tempo Akhir</td>
                        <td class="font-weight-bold text-danger">{{ \Carbon\Carbon::parse($kasbon->due_date)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="bg-light">Metode Terima</td>
                        <td>{{ $kasbon->payment_method == 'transfer' ? 'Transfer Bank' : 'Tunai' }}</td>
                    </tr>
                </table>

            </div>
            <div class="card-footer no-print d-flex justify-content-between align-items-center">
                <a href="{{ route('kasbon.index') }}" class="btn btn-light">Kembali</a>
                <button onclick="window.print()" class="btn btn-secondary"><i class="mdi mdi-printer"></i> Cetak</button>
            </div>
        </div>
    </div>

    {{-- BAGIAN KANAN: INPUT & RIWAYAT REAL --}}
    <div class="col-md-7 grid-margin stretch-card no-print">
        <div class="card">
            <div class="card-body">
                
                {{-- HEADER KANAN --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Catatan Pembayaran Masuk</h4>
                    {{-- TOMBOL BAYAR (Hanya muncul jika belum lunas & User ybs) --}}
                    @if($kasbon->remaining_amount > 0 && auth()->id() == $kasbon->user_id)
                         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModalGlobal">
                            <i class="mdi mdi-plus-circle"></i> Tambah Pembayaran
                        </button>
                    @endif
                </div>
                
                {{-- ALERT --}}
                @if(session('success')) <div class="alert alert-success border-0 shadow-sm"><i class="mdi mdi-check-circle"></i> {{ session('success') }}</div> @endif
                @if(session('error')) <div class="alert alert-danger border-0 shadow-sm"><i class="mdi mdi-alert-circle"></i> {{ session('error') }}</div> @endif

                {{-- TABEL RIWAYAT --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal Bayar</th>
                                <th>Penerima</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                @if(auth()->user()->role == 'admin') <th class="text-center">Aksi</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasbon->installments as $ins)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $ins->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $ins->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>{{ $ins->received_by }}</td>
                                    <td>
                                        <span class="text-success font-weight-bold">+ Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        @if($ins->status == 'pending') 
                                            <span class="badge badge-warning">Verifikasi</span>
                                        @elseif($ins->status == 'approved') 
                                            <span class="badge badge-success">Diterima</span>
                                        @else 
                                            <span class="badge badge-danger">Ditolak</span> 
                                        @endif
                                        
                                        <div class="mt-1">
                                            <a href="{{ asset('storage/'.$ins->payment_proof) }}" target="_blank" class="text-decoration-none text-primary" style="font-size: 0.8em;">
                                                <i class="mdi mdi-image"></i> Lihat Bukti
                                            </a>
                                        </div>
                                    </td>
                                    
                                    @if(auth()->user()->role == 'admin')
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($ins->status == 'pending')
                                                    <form action="{{ route('kasbon.installment.approve', $ins->id) }}" method="POST">
                                                        @csrf 
                                                        <button class="btn btn-success btn-sm" title="Terima"><i class="mdi mdi-check"></i></button>
                                                    </form>
                                                    <form action="{{ route('kasbon.installment.reject', $ins->id) }}" method="POST" class="ms-1">
                                                        @csrf 
                                                        <button class="btn btn-danger btn-sm" title="Tolak"><i class="mdi mdi-close"></i></button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('kasbon.installment.edit', $ins->id) }}" class="btn btn-warning btn-sm ms-1" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-cash-remove" style="font-size: 3em;"></i><br>
                                        Belum ada riwayat pembayaran cicilan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- INCLUDE MODAL BAYAR (Agar tombol Tambah Pembayaran berfungsi) --}}
{{-- Kita gunakan variabel $loan = $kasbon agar kompatibel dengan partial --}}
@include('kasbon.partials.pay_modal', ['loan' => $kasbon])

{{-- SCRIPT FORMAT RUPIAH GLOBAL (Untuk Modal di halaman ini) --}}
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