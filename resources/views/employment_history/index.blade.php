@extends('layout.master')

@section('title', 'Riwayat Karir')

@section('content')
<div class="row">
    
    {{-- FILTER USER --}}
    @if(in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-1">Daftar Riwayat Karir</h4>
                    <p class="text-muted mb-0 small">
                        @if($canEdit)
                            <span class="text-success fw-bold"><i class="mdi mdi-pencil"></i> MODE EDIT AKTIF</span> - 
                        @endif
                        Pilih pegawai untuk melihat timeline.
                    </p>
                </div>
                
                <form action="{{ route('employment-history.index') }}" method="GET" class="d-flex align-items-center w-50 justify-content-end">
                    @if(request()->get('mode') == 'edit')
                        <input type="hidden" name="mode" value="edit">
                    @endif

                    <select name="user_id" class="form-control w-75" onchange="this.form.submit()" style="border-radius: 8px;">
                        <option value="{{ auth()->user()->id }}" {{ isset($targetUser) && $targetUser->id == auth()->id() ? 'selected' : '' }}>
                            -- Saya Sendiri ({{ auth()->user()->name }}) --
                        </option>
                        @foreach($selectableUsers as $u)
                            @if($u->id != auth()->id())
                                <option value="{{ $u->id }}" {{ isset($targetUser) && $targetUser->id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endif
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
                    
                    @if($canCreate)
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
                    </div>
                @else
                    <ul class="bullet-line-list">
                        @foreach($histories as $history)
                            <li class="mb-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    {{-- HEADER --}}
                                    <div>
                                        <h6 class="text-{{ $history->type_color }} font-weight-bold mb-1" style="font-size: 1.1rem;">
                                            {{ $history->type_label }}
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            <i class="mdi mdi-calendar"></i> 
                                            {{ \Carbon\Carbon::parse($history->event_date)->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                    
                                    {{-- AKSI EDIT & HAPUS --}}
                                    @if($canEdit)
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('employment-history.edit', $history->id) }}" class="btn btn-inverse-warning btn-sm p-2" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('employment-history.destroy', $history->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-sm p-2" title="Hapus">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- DETAIL CONTENT --}}
                                <div class="p-3 bg-light rounded mt-2 border-start border-{{ $history->type_color }}" style="border-left: 4px solid;">
                                    <div class="row">
                                        {{-- FOTO --}}
                                        @if($history->attachment)
                                            <div class="col-md-3 mb-3 mb-md-0">
                                                <div class="position-relative" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#attachmentModal" data-src="{{ asset('storage/' . $history->attachment) }}">
                                                    <img src="{{ asset('storage/' . $history->attachment) }}" 
                                                         class="img-fluid rounded shadow-sm w-100" 
                                                         style="object-fit: cover; height: 150px; min-height: 100%; border: 1px solid #dee2e6;"
                                                         alt="Lampiran">
                                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-25 opacity-0 hover-opacity-100 transition-all rounded">
                                                        <i class="mdi mdi-magnify-plus text-white display-4"></i>
                                                    </div>
                                                </div>
                                                <small class="text-muted text-center d-block mt-1" style="font-size: 10px;">Klik untuk memperbesar</small>
                                            </div>
                                        @endif

                                        {{-- INFORMASI --}}
                                        <div class="{{ $history->attachment ? 'col-md-9' : 'col-12' }}">
                                            
                                            {{-- AUDIT --}}
                                            @if($targetUser->role == 'audit' && $history->type == 'transfer_branch')
                                                <div class="mb-2">
                                                    <span class="text-muted small">{!! $history->audit_change_text !!}</span>
                                                </div>

                                            {{-- PINDAH CABANG (Hanya Show Tujuan) --}}
                                            @elseif($history->type == 'transfer_branch')
                                                <p class="mb-1">
                                                    <i class="mdi mdi-arrow-right-bold-circle text-success me-1"></i>
                                                    Pindah ke Cabang: <strong>{{ $history->branch->name ?? '-' }}</strong>
                                                </p>

                                            {{-- LAINNYA --}}
                                            @elseif($history->type != 'resign')
                                                @if($history->branch)
                                                    <p class="mb-1"><i class="mdi mdi-map-marker me-1"></i> Cabang: <strong>{{ $history->branch->name }}</strong></p>
                                                @endif
                                                @if($history->division)
                                                    <p class="mb-1"><i class="mdi mdi-briefcase me-1"></i> Divisi: <strong>{{ $history->division->name }}</strong></p>
                                                @endif
                                            @endif

                                            {{-- KETERANGAN --}}
                                            @if($history->description)
                                                <div class="mt-2 pt-2 border-top">
                                                    <p class="mb-0 small text-muted font-italic">"{{ $history->description }}"</p>
                                                </div>
                                            @endif

                                            {{-- FOOTER CREATOR/EDITOR --}}
                                            <div class="mt-3 text-end">
                                                @if($history->creator)
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                        <i class="mdi mdi-account-plus me-1"></i> Dibuat: {{ $history->creator->name }}
                                                    </small>
                                                @endif
                                                @if($history->editor)
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                        <i class="mdi mdi-account-edit me-1"></i> Diedit: {{ $history->editor->name }}
                                                    </small>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Lampiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light p-4 rounded m-3">
                <img id="modalImageSrc" src="" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
<style>
    .hover-opacity-100:hover { opacity: 1 !important; }
    .transition-all { transition: all 0.3s ease; }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var attachmentModal = document.getElementById('attachmentModal');
        attachmentModal.addEventListener('show.bs.modal', function(event) {
            var div = event.relatedTarget; 
            var src = div.getAttribute('data-src');
            document.getElementById('modalImageSrc').src = src;
        });
        attachmentModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalImageSrc').src = '';
        });
    });
</script>
@endpush