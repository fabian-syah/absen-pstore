@extends('layout.master')

@section('content')
<style>
    /* UI Customization */
    .card { border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
    .avatar-square { width: 60px; height: 60px; border-radius: 8px; background-color: #f0f2f5; color: #4b49ac; font-weight: bold; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 1px solid #dee2e6; }
    
    /* Timeline */
    .timeline-steps { border-left: 2px solid #e0e0e0; margin-left: 12px; padding-left: 30px; padding-bottom: 30px; position: relative; }
    .timeline-steps:last-child { border-left: none; padding-bottom: 0; }
    .timeline-steps::before { content: ''; position: absolute; left: -7px; top: 0; width: 12px; height: 12px; background: #fff; border: 3px solid #4b49ac; border-radius: 50%; }
    .timeline-steps.active::before { background: #4b49ac; border-color: #4b49ac; }
    
    /* Thumbnail */
    .thumb-img { width: 100%; height: 120px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 1px solid #eee; transition: transform 0.2s; }
    .thumb-img:hover { transform: scale(1.02); border-color: #4b49ac; }
</style>

@php
    $divRaw = json_decode($kasbon->division);
    $divisi = (json_last_error() === JSON_ERROR_NONE && isset($divRaw->name)) ? $divRaw->name : $kasbon->division;
    $branchRaw = json_decode($kasbon->branch);
    $cabang = (json_last_error() === JSON_ERROR_NONE && isset($branchRaw->name)) ? $branchRaw->name : $kasbon->branch;
    $percent = $kasbon->amount > 0 ? ($kasbon->total_paid / $kasbon->amount) * 100 : 0;
@endphp

<div class="container-fluid">
    
    {{-- ALERT ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 mb-4">
            <h5 class="fw-bold mb-1"><i class="mdi mdi-alert-circle-outline me-2"></i>Gagal Memproses Data</h5>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0 mb-4 fw-bold">
            <i class="mdi mdi-check-circle-outline me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('kasbon.index') }}" class="btn btn-white border me-3 rounded-2 px-3"><i class="mdi mdi-arrow-left me-1"></i> Kembali</a>
            <div>
                <h4 class="fw-bold text-dark mb-0">Transaksi #{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h4>
                <small class="text-muted">Diajukan: {{ $kasbon->created_at->format('d F Y, H:i') }}</small>
            </div>
        </div>
        <div>
            @if($kasbon->status == 'pending') <span class="badge bg-warning text-dark px-3 py-2 rounded-2">MENUNGGU APPROVAL</span>
            @elseif($kasbon->status == 'approved') <span class="badge bg-primary px-3 py-2 rounded-2">AKTIF</span>
            @elseif($kasbon->status == 'paid') <span class="badge bg-success px-3 py-2 rounded-2">LUNAS</span>
            @else <span class="badge bg-danger px-3 py-2 rounded-2">DITOLAK</span> @endif
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3 letter-spacing-1">Peminjam</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-square me-3">{{ substr($kasbon->user_name, 0, 1) }}</div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">{{ $kasbon->user_name }}</h5>
                            <small class="text-muted">{{ $divisi }} • {{ $cabang }}</small>
                        </div>
                    </div>
                    
                    <div class="bg-light p-3 rounded-2 border">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span>Total Pinjaman</span> <span class="fw-bold text-dark">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Sudah Dibayar</span> <span>- Rp {{ number_format($kasbon->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small">Sisa Hutang</span> 
                            <span class="fw-bold {{ $kasbon->remaining_amount > 0 ? 'text-danger' : 'text-success' }} fs-5">
                                Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1"><span>Status Pelunasan</span> <span class="fw-bold">{{ number_format($percent, 0) }}%</span></div>
                        <div class="progress" style="height: 6px;"><div class="progress-bar bg-success" style="width: {{ $percent }}%"></div></div>
                    </div>
                </div>
            </div>

            {{-- DOKUMEN --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3 letter-spacing-1">Dokumen & Bukti</h6>
                    <p class="small text-dark mb-3"><i class="mdi mdi-format-quote-close me-1 text-muted"></i> {{ $kasbon->description }}</p>
                    <div class="row g-2">
                        <div class="col-6"><img src="{{ asset('storage/'.$kasbon->photo_1) }}" class="thumb-img" onclick="openModal(this.src)" alt="Foto 1"></div>
                        <div class="col-6"><img src="{{ asset('storage/'.$kasbon->photo_2) }}" class="thumb-img" onclick="openModal(this.src)" alt="Foto 2"></div>
                    </div>
                    @if($kasbon->payment_method == 'transfer')
                        <div class="mt-3 pt-2 border-top">
                            <label class="small fw-bold text-muted d-block">Info Transfer:</label>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-1 p-2 d-block text-start text-truncate">
                                <i class="mdi mdi-bank me-1"></i> {{ $kasbon->account_details }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ACTION ADMIN --}}
            @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']) && $kasbon->status == 'pending')
            <div class="card border-warning bg-warning bg-opacity-10">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3">Konfirmasi Pengajuan</h6>
                    <form action="{{ route('kasbon.status', $kasbon->id) }}" method="POST">
                        @csrf
                        <div class="d-flex gap-2">
                            <button name="status" value="approved" class="btn btn-primary fw-bold flex-fill shadow-sm">SETUJUI</button>
                            <button name="status" value="rejected" class="btn btn-danger fw-bold flex-fill shadow-sm">TOLAK</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- KOLOM KANAN --}}
        <div class="col-md-8">
            
            {{-- FORM PEMBAYARAN (DIPERBAIKI) --}}
            @if($kasbon->status == 'approved' && $kasbon->remaining_amount > 0)
            <div class="card mb-4 border-primary shadow-sm" style="border-width: 2px;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="mdi mdi-cash-plus me-2"></i>Input Pembayaran Cicilan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('kasbon.pay', $kasbon->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted">Nominal Bayar (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white fw-bold">Rp</span>
                                    {{-- Input dibuat Standard Putih agar terbaca --}}
                                    <input type="text" name="amount_paid" id="rupiah" class="form-control form-control-lg fw-bold" placeholder="0" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted">Bukti Transfer</label>
                                <input type="file" name="payment_proof" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">KIRIM</button>
                            </div>
                            <div class="col-12">
                                <input type="text" name="note" class="form-control border-0 border-bottom rounded-0 px-0" placeholder="Tambahkan catatan opsional (cth: Potong Gaji November)...">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- TIMELINE --}}
            <div class="card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Riwayat Transaksi</h6>
                </div>
                <div class="card-body pt-4">
                    @forelse($kasbon->installments as $ins)
                        <div class="timeline-steps active">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Pembayaran Diterima</h6>
                                    <p class="small text-muted mb-1">{{ $ins->created_at->format('d M Y, H:i') }} • Oleh <strong>{{ $ins->user->name }}</strong></p>
                                    @if($ins->note) <span class="badge bg-light text-dark fw-normal border">{{ $ins->note }}</span> @endif
                                </div>
                                <div class="text-end">
                                    <h5 class="fw-bold text-success mb-1">+ Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</h5>
                                    <button class="btn btn-xs btn-outline-secondary rounded-pill px-3" onclick="openModal('{{ asset('storage/'.$ins->payment_proof) }}')">
                                        <i class="mdi mdi-eye me-1"></i> Bukti
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex p-3 mb-3 text-muted"><i class="mdi mdi-receipt-text-remove-outline fs-2"></i></div>
                            <p class="text-muted mb-0">Belum ada riwayat pembayaran.</p>
                        </div>
                    @endforelse

                    <div class="timeline-steps active">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Pengajuan Disetujui</h6>
                                <small class="text-muted">{{ $kasbon->created_at->format('d M Y') }}</small>
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

{{-- MODAL --}}
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark p-2 rounded-circle shadow" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="modalImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
            </div>
        </div>
    </div>
</div>

<script>
    const rupiah = document.getElementById('rupiah');
    if(rupiah){ rupiah.addEventListener('keyup', function(e){ this.value = formatRupiah(this.value); }); }
    function formatRupiah(angka){
        var number_string = angka.replace(/[^,\d]/g, '').toString(), split = number_string.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if(ribuan){ separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }
    function openModal(src) { document.getElementById('modalImage').src = src; var myModal = new bootstrap.Modal(document.getElementById('imgModal')); myModal.show(); }
</script>
@endsection