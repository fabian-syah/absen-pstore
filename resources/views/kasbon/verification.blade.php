@extends('layout.master')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1" style="font-size: clamp(1.1rem, 3vw, 1.5rem);">Verifikasi Pembayaran</h3>
            <p class="text-muted mb-0 small">Daftar pembayaran cicilan yang menunggu konfirmasi.</p>
        </div>
    </div>

    {{-- DESKTOP TABLE (Hidden on Mobile) --}}
    <div class="card border-0 shadow-sm rounded-4 d-none d-lg-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small text-muted">
                            <th class="ps-4 py-3">Tanggal & User</th>
                            <th class="py-3">Info Kasbon</th>
                            <th class="py-3 text-end">Nominal Bayar</th>
                            <th class="py-3 text-center">Bukti</th>
                            <th class="py-3">Catatan User</th>
                            <th class="py-3 text-center pe-4" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingInstallments as $ins)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $ins->created_at->format('d M Y, H:i') }}</div>
                                    <div class="text-primary small fw-bold">
                                        <i class="mdi mdi-account me-1"></i> {{ $ins->user->name }}
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted d-block">ID Kasbon: #{{ str_pad($ins->cash_advance_id, 5, '0', STR_PAD_LEFT) }}</small>
                                    <span class="badge bg-light text-dark border">
                                        Sisa: Rp {{ number_format($ins->cashAdvance->remaining_amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <h5 class="mb-0 fw-bold text-success">+ Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</h5>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-light border rounded-pill px-3" onclick="openModal('{{ asset('storage/'.$ins->payment_proof) }}')">
                                        <i class="mdi mdi-image-outline text-primary"></i> Lihat
                                    </button>
                                </td>
                                <td>
                                    @if($ins->note)
                                        <span class="text-muted small fst-italic">"{{ $ins->note }}"</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <form action="{{ route('kasbon.installment.approve', $ins->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-success btn-sm fw-bold px-3 shadow-sm rounded-pill" title="Terima">
                                                <i class="mdi mdi-check"></i>
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $ins->id }}" title="Tolak">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </div>

                                    @include('kasbon.partials._reject_modal', ['ins' => $ins])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="mdi mdi-check-circle-outline fs-1 text-success mb-2"></i>
                                        <h6 class="fw-bold text-muted">Semua bersih!</h6>
                                        <p class="small text-muted">Tidak ada pembayaran yang perlu diverifikasi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MOBILE CARD LAYOUT --}}
    <div class="d-lg-none">
        @forelse($pendingInstallments as $ins)
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-3">
                    {{-- Header: User + Date --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">
                                <i class="mdi mdi-account text-primary me-1"></i> {{ $ins->user->name }}
                            </h6>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ $ins->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">
                            #{{ str_pad($ins->cash_advance_id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    {{-- Nominal + Sisa --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2 text-center">
                                <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">NOMINAL BAYAR</small>
                                <span class="fw-bold text-success" style="font-size: 0.85rem;">
                                    + Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2 text-center">
                                <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">SISA HUTANG</small>
                                <span class="fw-bold text-danger" style="font-size: 0.85rem;">
                                    Rp {{ number_format($ins->cashAdvance->remaining_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    @if($ins->note)
                        <p class="text-muted small fst-italic bg-light rounded-3 p-2 mb-3" style="font-size: 0.75rem;">"{{ $ins->note }}"</p>
                    @endif

                    {{-- Actions --}}
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-light border rounded-pill px-3 flex-shrink-0" onclick="openModal('{{ asset('storage/'.$ins->payment_proof) }}')" style="font-size: 0.75rem;">
                            <i class="mdi mdi-image-outline text-primary"></i> Bukti
                        </button>
                        <div class="ms-auto d-flex gap-2">
                            <form action="{{ route('kasbon.installment.approve', $ins->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm fw-bold px-3 shadow-sm rounded-pill">
                                    <i class="mdi mdi-check me-1"></i> Terima
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $ins->id }}">
                                <i class="mdi mdi-close me-1"></i> Tolak
                            </button>
                        </div>
                    </div>

                    @include('kasbon.partials._reject_modal', ['ins' => $ins])
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="opacity-50">
                        <i class="mdi mdi-check-circle-outline fs-1 text-success mb-2 d-block"></i>
                        <h6 class="fw-bold text-muted">Semua bersih!</h6>
                        <p class="small text-muted mb-0">Tidak ada pembayaran yang perlu diverifikasi.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>

{{-- MODAL LIGHTBOX --}}
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark rounded-circle p-2 shadow" data-bs-dismiss="modal"></button>
                <img id="modalImage" src="" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh;">
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(src) { 
        document.getElementById('modalImage').src = src; 
        var myModal = new bootstrap.Modal(document.getElementById('imgModal')); 
        myModal.show(); 
    }
</script>
@endsection