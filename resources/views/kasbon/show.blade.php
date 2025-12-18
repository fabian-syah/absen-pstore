@extends('layout.master')

@section('content')
<style>
    /* Styling Timeline & Progress */
    .timeline { border-left: 2px solid #e9ecef; margin-left: 10px; padding-left: 20px; position: relative; }
    .timeline-item { position: relative; margin-bottom: 25px; }
    .timeline-item::before {
        content: ''; position: absolute; left: -26px; top: 5px; width: 12px; height: 12px;
        background: #fff; border: 2px solid #4b49ac; border-radius: 50%;
    }
    .timeline-item.success::before { border-color: #198754; background: #198754; }
    
    /* Styling Foto Thumbnail */
    .img-thumb-box {
        width: 100%; height: 120px; border-radius: 8px; overflow: hidden; 
        border: 1px solid #dee2e6; cursor: pointer; position: relative;
    }
    .img-thumb-box img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .img-thumb-box:hover img { transform: scale(1.05); }
    .overlay-zoom {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.3s;
    }
    .img-thumb-box:hover .overlay-zoom { opacity: 1; }
</style>

<div class="container-fluid">
    {{-- HEADER & BACK BUTTON --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('kasbon.index') }}" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
            <i class="mdi mdi-arrow-left fs-5"></i>
        </a>
        <div>
            <h3 class="fw-bold mb-0 text-dark">Detail Kasbon #{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h3>
            <span class="text-muted small">Diajukan pada {{ $kasbon->created_at->format('d F Y, H:i') }}</span>
        </div>
        <div class="ms-auto">
            @if($kasbon->status == 'pending')
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6"><i class="mdi mdi-clock-outline me-1"></i> Menunggu Approval</span>
            @elseif($kasbon->status == 'approved')
                <span class="badge bg-primary px-3 py-2 rounded-pill fs-6"><i class="mdi mdi-run me-1"></i> Aktif Berjalan</span>
            @elseif($kasbon->status == 'paid')
                <span class="badge bg-success px-3 py-2 rounded-pill fs-6"><i class="mdi mdi-check-circle-outline me-1"></i> Lunas Selesai</span>
            @else
                <span class="badge bg-danger px-3 py-2 rounded-pill fs-6"><i class="mdi mdi-close-circle-outline me-1"></i> Ditolak</span>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: INFO UTAMA & FORM BAYAR --}}
        <div class="col-lg-4">
            
            {{-- CARD 1: INFORMASI PEMINJAM & KEUANGAN --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-initials rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; font-size: 1.8rem;">
                            {{ substr($kasbon->user_name, 0, 2) }}
                        </div>
                        <h5 class="fw-bold text-dark mb-0">{{ $kasbon->user_name }}</h5>
                        <p class="text-muted small mb-0">{{ $kasbon->division }} • {{ $kasbon->branch }}</p>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total Pinjaman</span>
                            <span class="fw-bold text-dark">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Sudah Dibayar</span>
                            <span class="fw-bold text-success">- Rp {{ number_format($kasbon->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2 border-dashed">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Sisa Hutang</span>
                            <span class="fs-5 fw-bold {{ $kasbon->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- PROGRESS BAR --}}
                    @php $percent = $kasbon->amount > 0 ? ($kasbon->total_paid / $kasbon->amount) * 100 : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Progress Pelunasan</span>
                            <span class="fw-bold text-primary">{{ number_format($percent, 0) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: BUKTI & KETERANGAN --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="mdi mdi-file-document-outline me-2 text-muted"></i>Detail Pengajuan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted fw-bold">Keterangan:</label>
                        <p class="text-dark bg-light p-3 rounded-3 small mb-0">{{ $kasbon->description }}</p>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small text-muted fw-bold mb-1">Bukti 1</label>
                            <div class="img-thumb-box" onclick="showImage('{{ asset('storage/'.$kasbon->photo_1) }}')">
                                <img src="{{ asset('storage/'.$kasbon->photo_1) }}" alt="Bukti 1">
                                <div class="overlay-zoom"><i class="mdi mdi-magnify-plus text-white fs-3"></i></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted fw-bold mb-1">Bukti 2</label>
                            <div class="img-thumb-box" onclick="showImage('{{ asset('storage/'.$kasbon->photo_2) }}')">
                                <img src="{{ asset('storage/'.$kasbon->photo_2) }}" alt="Bukti 2">
                                <div class="overlay-zoom"><i class="mdi mdi-magnify-plus text-white fs-3"></i></div>
                            </div>
                        </div>
                    </div>

                    @if($kasbon->payment_method == 'transfer')
                    <div class="mt-3 pt-3 border-top">
                        <label class="small text-muted fw-bold mb-1">Info Rekening Tujuan:</label>
                        <div class="d-flex align-items-center bg-info bg-opacity-10 p-2 rounded text-primary">
                            <i class="mdi mdi-bank me-2 fs-5"></i>
                            <span class="small fw-bold">{{ $kasbon->account_details }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ACTION BUTTON ADMIN --}}
            @if(auth()->user()->role == 'admin' && $kasbon->status == 'pending')
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary bg-opacity-10">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Tindakan Admin</h6>
                    <form action="{{ route('kasbon.status', $kasbon->id) }}" method="POST">
                        @csrf
                        <div class="d-grid gap-2">
                            <button name="status" value="approved" class="btn btn-primary fw-bold shadow-sm">
                                <i class="mdi mdi-check me-1"></i> Setujui Pengajuan
                            </button>
                            <button name="status" value="rejected" class="btn btn-danger fw-bold shadow-sm bg-white text-danger border-0">
                                <i class="mdi mdi-close me-1"></i> Tolak Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>

        {{-- KOLOM KANAN: FORM INPUT & HISTORY TIMELINE --}}
        <div class="col-lg-8">
            
            {{-- FORM BAYAR (Hanya muncul jika Status Approved & Belum Lunas) --}}
            @if($kasbon->status == 'approved' && $kasbon->remaining_amount > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-dark"><i class="mdi mdi-cash-plus me-2 text-success"></i>Input Pembayaran Baru</h5>
                    
                    <form action="{{ route('kasbon.pay', $kasbon->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="small text-muted fw-bold mb-1">Nominal Bayar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white fw-bold border-0">Rp</span>
                                    <input type="text" name="amount_paid" class="form-control form-control-lg fw-bold text-dark bg-light border-0" 
                                           placeholder="0" id="bayar_rp" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="small text-muted fw-bold mb-1">Upload Bukti Transfer</label>
                                <input type="file" name="payment_proof" class="form-control form-control-lg bg-light border-0" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
                                    <i class="mdi mdi-send"></i>
                                </button>
                            </div>
                            <div class="col-12">
                                <input type="text" name="note" class="form-control bg-white border-bottom border-0 rounded-0 ps-0" placeholder="Catatan opsional (Contoh: Potong Gaji Bulan November)...">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- HISTORY TIMELINE --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Riwayat Transaksi</h5>
                    <span class="badge bg-light text-dark">{{ $kasbon->installments->count() }} Transaksi</span>
                </div>
                <div class="card-body p-4">
                    <div class="timeline mt-2">
                        {{-- Data Cicilan --}}
                        @forelse($kasbon->installments as $ins)
                            <div class="timeline-item success">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Pembayaran Diterima</h6>
                                        <p class="small text-muted mb-1">
                                            <i class="mdi mdi-calendar-blank me-1"></i> {{ $ins->created_at->format('d M Y, H:i') }}
                                            <span class="mx-2">•</span>
                                            Oleh: <strong>{{ $ins->user->name }}</strong>
                                        </p>
                                        @if($ins->note)
                                            <div class="bg-light p-2 rounded small text-muted d-inline-block mt-1">
                                                <i class="mdi mdi-note-text-outline me-1"></i> {{ $ins->note }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <h5 class="fw-bold text-success mb-1">+ Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</h5>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 mt-1" onclick="showImage('{{ asset('storage/'.$ins->payment_proof) }}')">
                                            <i class="mdi mdi-image-outline me-1"></i> Bukti
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="mdi mdi-receipt-text-remove-outline fs-3 text-muted"></i>
                                </div>
                                <p class="text-muted fw-bold">Belum ada riwayat pembayaran.</p>
                            </div>
                        @endforelse

                        {{-- Titik Awal Pengajuan --}}
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Pengajuan Dibuat</h6>
                                    <p class="small text-muted mb-0">{{ $kasbon->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="text-end">
                                    <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL POPUP IMAGE (LIGHTBOX) --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 p-2 bg-dark rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="modalImageSrc" src="" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh;">
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi Format Rupiah Input
    const inputBayar = document.getElementById('bayar_rp');
    if(inputBayar){
        inputBayar.addEventListener('keyup', function(e){
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

    // Fungsi Show Image Modal
    function showImage(src) {
        document.getElementById('modalImageSrc').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }
</script>
@endsection