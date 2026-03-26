{{-- Status Badge Partial --}}
@if($status == 'pending')
    <span class="badge rounded-pill bg-warning text-dark px-2 px-md-3 py-1 py-md-2 fw-bold border border-warning" style="font-size: 0.7rem;">
        <i class="mdi mdi-timer-sand me-1"></i> MENUNGGU
    </span>
@elseif($status == 'approved')
    <span class="badge rounded-pill bg-white text-primary px-2 px-md-3 py-1 py-md-2 fw-bold border border-primary shadow-sm" style="font-size: 0.7rem;">
        <i class="mdi mdi-run me-1"></i> AKTIF
    </span>
@elseif($status == 'paid')
    <span class="badge rounded-pill bg-success text-white px-2 px-md-3 py-1 py-md-2 fw-bold shadow-sm" style="font-size: 0.7rem;">
        <i class="mdi mdi-check-all me-1"></i> LUNAS
    </span>
@else
    <span class="badge rounded-pill bg-danger text-white px-2 px-md-3 py-1 py-md-2 fw-bold shadow-sm" style="font-size: 0.7rem;">
        <i class="mdi mdi-close me-1"></i> DITOLAK
    </span>
@endif
