@extends('layout.master')

@section('content')
<style>
    /* UI Customization: Kotak Tegas (Professional Look) */
    .card {
        border-radius: 6px; /* Sudut sedikit melengkung tapi tegas */
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .avatar-square {
        width: 60px; height: 60px;
        border-radius: 6px; /* Kotak */
        background-color: #f8f9fa;
        color: #4b49ac;
        font-weight: bold;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        border: 1px solid #dee2e6;
    }
    
    /* Timeline Vertical Lurus */
    .timeline-steps {
        border-left: 2px solid #e0e0e0;
        margin-left: 10px; padding-left: 25px;
        padding-bottom: 25px; position: relative;
    }
    .timeline-steps:last-child { border-left: none; padding-bottom: 0; }
    .timeline-steps::before {
        content: ''; position: absolute; left: -6px; top: 0;
        width: 10px; height: 10px; background: #fff;
        border: 2px solid #4b49ac; border-radius: 2px; /* Kotak kecil */
    }
    .timeline-steps.active::before { background: #4b49ac; }

    /* Thumbnail Foto */
    .thumb-img {
        width: 100%; height: 100px; 
        object-fit: cover; border-radius: 4px;
        cursor: pointer; border: 1px solid #ddd;
        transition: opacity 0.3s;
    }
    .thumb-img:hover { opacity: 0.8; }
</style>

{{-- LOGIC PARSING JSON (Agar tidak muncul kode aneh) --}}
@php
    // Coba Decode Divisi
    $divRaw = json_decode($kasbon->division);
    $divisi = (json_last_error() === JSON_ERROR_NONE && isset($divRaw->name)) ? $divRaw->name : $kasbon->division;

    // Coba Decode Cabang
    $branchRaw = json_decode($kasbon->branch);
    $cabang = (json_last_error() === JSON_ERROR_NONE && isset($branchRaw->name)) ? $branchRaw->name : $kasbon->branch;
    
    // Progress
    $percent = $kasbon->amount > 0 ? ($kasbon->total_paid / $kasbon->amount) * 100 : 0;
@endphp

<div class="container-fluid">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('kasbon.index') }}" class="btn btn-white border me-3 rounded-1"><i class="mdi mdi-arrow-left"></i></a>
            <div>
                <h4 class="fw-bold text-dark mb-0">Detail Transaksi #{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h4>
                <small class="text-muted">Diajukan tanggal {{ $kasbon->created_at->format('d M Y, H:i') }}</small>
            </div>
        </div>
        <div>
            @if($kasbon->status == 'pending')
                <span class="badge bg-warning text-dark rounded-1 px-3 py-2">MENUNGGU APPROVAL</span>
            @elseif($kasbon->status == 'approved')
                <span class="badge bg-primary rounded-1 px-3 py-2">AKTIF / BERJALAN</span>
            @elseif($kasbon->status == 'paid')
                <span class="badge bg-success rounded-1 px-3 py-2">LUNAS</span>
            @else
                <span class="badge bg-danger rounded-1 px-3 py-2">DITOLAK</span>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: INFO UTAMA --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Informasi Peminjam</h6>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-square me-3">
                            {{ substr($kasbon->user_name, 0, 1) }}
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">{{ $kasbon->user_name }}</h5>
                            <div class="text-muted small">
                                <i class="mdi mdi-briefcase me-1"></i> {{ $divisi }} <br>
                                <i class="mdi mdi-map-marker me-1"></i> {{ $cabang }}
                            </div>
                        </div>
                    </div>

                    <hr class="border-light">

                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Rincian Keuangan</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Pinjaman</span>
                        <span class="fw-bold">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Sudah Bayar</span>
                        <span>- Rp {{ number_format($kasbon->total_paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2 bg-light border rounded-1 d-flex justify-content-between align-items-center mt-2">
                        <span class="fw-bold">Sisa Kewajiban</span>
                        <span class="fw-bold text-danger fs-5">Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    {{-- Progress Bar Kotak --}}
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Progress</span>
                            <span class="fw-bold">{{ number_format($percent, 0) }}%</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 0;">
                            <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Dokumen & Bukti</h6>
                    <p class="small bg-light p-2 border rounded-1 mb-3">{{ $kasbon->description }}</p>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <img src="{{ asset('storage/'.$kasbon->photo_1) }}" class="thumb-img" onclick="openModal(this.src)" alt="Foto 1">
                            <small class="d-block text-center text-muted mt-1" style="font-size: 10px">Bukti 1</small>
                        </div>
                        <div class="col-6">
                            <img src="{{ asset('storage/'.$kasbon->photo_2) }}" class="thumb-img" onclick="openModal(this.src)" alt="Foto 2">
                            <small class="d-block text-center text-muted mt-1" style="font-size: 10px">Bukti 2</small>
                        </div>
                    </div>

                    @if($kasbon->payment_method == 'transfer')
                        <div class="mt-3 pt-2 border-top">
                            <label class="small fw-bold">Transfer Ke:</label>
                            <div class="d-flex align-items-center text-primary">
                                <i class="mdi mdi-credit-card me-2"></i> {{ $kasbon->account_details }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ADMIN ACTIONS --}}
            @if(auth()->user()->role == 'admin,admin_gaji' && $kasbon->status == 'pending')
            <div class="card mt-3 border-warning">
                <div class="card-body">
                    <h6 class="fw-bold text-warning mb-3">Tindakan Admin</h6>
                    <form action="{{ route('kasbon.status', $kasbon->id) }}" method="POST">
                        @csrf
                        <div class="d-flex gap-2">
                            <button name="status" value="approved" class="btn btn-primary flex-fill rounded-1 fw-bold">SETUJUI</button>
                            <button name="status" value="rejected" class="btn btn-outline-danger flex-fill rounded-1 fw-bold">TOLAK</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- KOLOM KANAN: HISTORY & FORM --}}
        <div class="col-md-8">
            
            {{-- FORM BAYAR --}}
            @if($kasbon->status == 'approved' && $kasbon->remaining_amount > 0)
            <div class="card mb-4 bg-primary text-white border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="mdi mdi-wallet-plus me-2"></i>Input Pembayaran Cicilan</h5>
                    <form action="{{ route('kasbon.pay', $kasbon->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="small text-white-50">Nominal (Rp)</label>
                                <input type="text" name="amount_paid" id="rupiah" class="form-control rounded-1 fw-bold text-dark border-0" placeholder="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-white-50">Bukti Transfer</label>
                                <input type="file" name="payment_proof" class="form-control rounded-1 border-0" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-light text-primary fw-bold w-100 rounded-1">KIRIM PEMBAYARAN</button>
                            </div>
                            <div class="col-12 mt-2">
                                <input type="text" name="note" class="form-control bg-white bg-opacity-10 border-0 text-white placeholder-white rounded-1" placeholder="Catatan Tambahan (Opsional)...">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- TIMELINE HISTORY --}}
            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0">Riwayat Transaksi</h6>
                </div>
                <div class="card-body">
                    @forelse($kasbon->installments as $ins)
                        <div class="timeline-steps active">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Pembayaran Diterima</h6>
                                    <p class="small text-muted mb-1">
                                        {{ $ins->created_at->format('d M Y, H:i') }} • Oleh {{ $ins->user->name }}
                                    </p>
                                    @if($ins->note)
                                        <div class="p-2 bg-light border rounded-1 small d-inline-block">{{ $ins->note }}</div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="d-block fw-bold text-success">+ Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</span>
                                    <button class="btn btn-xs btn-outline-secondary rounded-1 mt-1" onclick="openModal('{{ asset('storage/'.$ins->payment_proof) }}')">
                                        Lihat Bukti
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="mdi mdi-clipboard-text-off text-muted fs-1"></i>
                            <p class="text-muted mt-2">Belum ada riwayat pembayaran.</p>
                        </div>
                    @endforelse

                    {{-- START POINT --}}
                    <div class="timeline-steps active">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Pengajuan Dibuat</h6>
                                <small class="text-muted">{{ $kasbon->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-primary">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL POPUP GAMBAR (LIGHTBOX) --}}
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center position-relative p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="modalImage" src="" class="img-fluid rounded-1 shadow-lg" style="max-height: 90vh;">
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi Format Rupiah
    const rupiah = document.getElementById('rupiah');
    if(rupiah){
        rupiah.addEventListener('keyup', function(e){
            this.value = formatRupiah(this.value);
        });
    }
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

    // Fungsi Buka Modal Gambar
    function openModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imgModal'));
        myModal.show();
    }
</script>
@endsection