@extends('layout.master')

@section('title', 'Riwayat Karir')

@section('content')
<div class="row">
    
    {{-- FILTER USER (ADMIN & AUDIT) --}}
    @if(in_array(auth()->user()->role, ['admin', 'audit']))
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-1">Daftar Riwayat Karir</h4>
                    <p class="text-muted mb-0 small">Pilih pegawai untuk melihat timeline mereka.</p>
                </div>
                
                <form action="{{ route('employment-history.index') }}" method="GET" class="d-flex align-items-center w-50 justify-content-end">
                    <select name="user_id" class="form-control w-75" onchange="this.form.submit()" style="border-radius: 8px;">
                        <option value="{{ auth()->user()->id }}">-- Saya Sendiri --</option>
                        @foreach($selectableUsers as $u)
                            <option value="{{ $u->id }}" {{ isset($targetUser) && $targetUser->id == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ ucfirst($u->role) }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- KONTEN TIMELINE --}}
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="card-title mb-1">Timeline: {{ $targetUser->name }}</h4>
                        <span class="badge badge-outline-primary">{{ strtoupper($targetUser->role) }}</span>
                    </div>
                    
                    {{-- TOMBOL TAMBAH DATA --}}
                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                        <a href="{{ route('employment-history.create', ['user_id' => $targetUser->id]) }}" class="btn btn-primary btn-icon-text">
                            <i class="mdi mdi-plus-circle-outline btn-icon-prepend"></i> Tambah Riwayat
                        </a>
                    @endif
                </div>

                @if($histories->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="mdi mdi-timeline-text-outline text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted">Belum ada riwayat tercatat.</h5>
                        <p class="text-muted small">Klik tombol tambah untuk membuat catatan baru.</p>
                    </div>
                @else
                    <ul class="bullet-line-list">
                        @foreach($histories as $history)
                            <li class="mb-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    {{-- HEADER ITEM --}}
                                    <div>
                                        <h6 class="text-{{ $history->type_color }} font-weight-bold mb-1" style="font-size: 1.1rem;">
                                            {{ $history->type_label }}
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            <i class="mdi mdi-calendar"></i> 
                                            {{ \Carbon\Carbon::parse($history->event_date)->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                    
                                    {{-- TOMBOL EDIT --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                        <a href="{{ route('employment-history.edit', $history->id) }}" class="btn btn-inverse-warning btn-sm p-2" title="Edit Data">
                                            <i class="mdi mdi-pencil"></i> Edit
                                        </a>
                                    @endif
                                </div>
                                
                                {{-- DETAIL CONTENT --}}
                                <div class="p-3 bg-light rounded mt-2 border-start border-{{ $history->type_color }}" style="border-left: 4px solid;">
                                    
                                    {{-- 1. Mutasi Cabang Audit --}}
                                    @if($targetUser->role == 'audit' && $history->type == 'transfer_branch')
                                        <div class="mb-2">
                                            <strong class="d-block text-dark small mb-1">Catatan Wilayah Audit:</strong>
                                            <span class="text-muted small">{!! $history->audit_change_text !!}</span>
                                        </div>

                                    {{-- 2. Mutasi Cabang User Biasa --}}
                                    @elseif($history->type == 'transfer_branch')
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-md-5">
                                                <small class="text-muted d-block">Dari:</small>
                                                <span class="text-danger text-decoration-line-through">
                                                    {{ $history->previousBranch->name ?? '?' }}
                                                </span>
                                            </div>
                                            <div class="col-md-1 text-center"><i class="mdi mdi-arrow-right"></i></div>
                                            <div class="col-md-5">
                                                <small class="text-muted d-block">Ke:</small>
                                                <span class="text-success font-weight-bold">
                                                    {{ $history->branch->name ?? '-' }}
                                                </span>
                                            </div>
                                        </div>

                                    {{-- 3. Tipe Lain (Join/Resign/Divisi) --}}
                                    @elseif($history->type != 'resign')
                                        @if($history->branch)
                                            <small class="d-block text-dark"><i class="mdi mdi-map-marker me-1"></i> Cabang: <strong>{{ $history->branch->name }}</strong></small>
                                        @endif
                                        @if($history->division)
                                            <small class="d-block text-dark"><i class="mdi mdi-briefcase me-1"></i> Divisi: <strong>{{ $history->division->name }}</strong></small>
                                        @endif
                                    @endif

                                    {{-- DESKRIPSI & LAMPIRAN --}}
                                    @if($history->description)
                                        <div class="mt-2 pt-2 border-top">
                                            <p class="mb-0 small text-muted font-italic">"{{ $history->description }}"</p>
                                        </div>
                                    @endif

                                    {{-- [MODIFIKASI] TOMBOL POPUP LAMPIRAN --}}
                                    @if($history->attachment)
                                        <div class="mt-2">
                                            <button type="button" 
                                                    class="badge badge-info text-white border-0 text-decoration-none" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#attachmentModal"
                                                    data-src="{{ asset('storage/' . $history->attachment) }}"
                                                    style="cursor: pointer;">
                                                <i class="mdi mdi-attachment"></i> Lihat Lampiran
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL POPUP LAMPIRAN --}}
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Lampiran Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light p-4 rounded m-3">
                <img id="modalImageSrc" src="" class="img-fluid rounded shadow-sm" alt="Lampiran" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika untuk menampilkan gambar di modal secara dinamis
        var attachmentModal = document.getElementById('attachmentModal');
        
        attachmentModal.addEventListener('show.bs.modal', function(event) {
            // Tombol yang diklik
            var button = event.relatedTarget;
            // Ambil data-src dari tombol
            var src = button.getAttribute('data-src');
            
            // Update src gambar di dalam modal
            var modalImg = document.getElementById('modalImageSrc');
            modalImg.src = src;
        });
        
        // Reset src saat modal ditutup (opsional, untuk membersihkan cache visual)
        attachmentModal.addEventListener('hidden.bs.modal', function() {
            var modalImg = document.getElementById('modalImageSrc');
            modalImg.src = '';
        });
    });
</script>
@endpush