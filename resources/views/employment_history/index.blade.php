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
                            -- Saya Sendiri ({{ auth()->user()->branch->name ?? 'Pusat/Non-Cabang' }}) --
                        </option>
                        @foreach($selectableUsers as $u)
                            @if($u->id != auth()->id())
                                <option value="{{ $u->id }}" {{ isset($targetUser) && $targetUser->id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->branch->name ?? 'Non-Cabang' }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- KONTEN TIMELINE INTERNAL PSTORE --}}
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="card-title mb-1">Timeline Internal Pstore: {{ $targetUser->name }}</h4>
                        <span class="badge badge-outline-primary">{{ strtoupper($targetUser->role) }} - {{ $targetUser->branch->name ?? 'PUSAT' }}</span>
                    </div>
                    
                    @if($canCreate)
                        <a href="{{ route('employment-history.create', ['user_id' => $targetUser->id]) }}" class="btn btn-primary btn-icon-text">
                            <i class="mdi mdi-plus-circle-outline btn-icon-prepend"></i> Tambah Riwayat
                        </a>
                    @endif
                </div>

                @if($internalHistories->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="mdi mdi-timeline-text-outline text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted">Belum ada riwayat internal Pstore.</h5>
                    </div>
                @else
                    <ul class="bullet-line-list">
                        @foreach($internalHistories as $history)
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
                                    
                                    {{-- AKSI --}}
                                    <div class="d-flex gap-2">
                                        @if($canEdit)
                                            <a href="{{ route('employment-history.edit', $history->id) }}" class="btn btn-inverse-warning btn-sm p-2" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($canDelete)
                                            <form action="{{ route('employment-history.destroy', $history->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-sm p-2" title="Hapus">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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
                                                         style="object-fit: cover; height: 100px;" alt="Lampiran">
                                                </div>
                                            </div>
                                        @endif

                                        {{-- INFORMASI --}}
                                        <div class="{{ $history->attachment ? 'col-md-9' : 'col-12' }}">
                                            @if($history->type == 'transfer_branch')
                                                <p class="mb-1">
                                                    <i class="mdi mdi-arrow-right-bold-circle text-success me-1"></i>
                                                    Pindah ke Cabang: <strong>{{ $history->branch->name ?? '-' }}</strong>
                                                </p>
                                            @elseif($history->type != 'resign')
                                                @if($history->branch)
                                                    <p class="mb-1"><i class="mdi mdi-map-marker me-1"></i> Cabang: <strong>{{ $history->branch->name }}</strong></p>
                                                @endif
                                                @if($history->division)
                                                    <p class="mb-1"><i class="mdi mdi-briefcase me-1"></i> Divisi: <strong>{{ $history->division->name }}</strong></p>
                                                @endif
                                            @endif

                                            @if($history->description)
                                                <div class="mt-2 pt-2 border-top">
                                                    <p class="mb-0 small text-muted font-italic">"{{ $history->description }}"</p>
                                                </div>
                                            @endif
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

    {{-- KONTEN PENGALAMAN LUAR PSTORE (PALING BAWAH) --}}
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Pengalaman di Luar Pstore</h4>
                
                @if($externalHistories->isEmpty())
                    <p class="text-muted">Tidak ada data pengalaman luar.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    {{-- KOLOM TANGGAL DIHAPUS --}}
                                    <th>Judul / Perusahaan</th>
                                    <th>Keterangan</th>
                                    <th>Lampiran</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($externalHistories as $ext)
                                    <tr>
                                        {{-- DATA TANGGAL DIHAPUS --}}
                                        <td class="fw-bold text-primary">{{ $ext->title }}</td>
                                        <td>{{ $ext->description ?? '-' }}</td>
                                        <td>
                                            @if($ext->attachment)
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#attachmentModal" data-src="{{ asset('storage/' . $ext->attachment) }}">
                                                    <i class="mdi mdi-image text-info"></i> Lihat
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if($canEdit)
                                                    <a href="{{ route('employment-history.edit', $ext->id) }}" class="btn btn-sm btn-inverse-warning p-1">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                @endif
                                                @if($canDelete)
                                                    <form action="{{ route('employment-history.destroy', $ext->id) }}" method="POST" onsubmit="return confirm('Hapus?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-inverse-danger p-1">
                                                            <i class="mdi mdi-trash-can"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- MODAL IMAGE --}}
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var attachmentModal = document.getElementById('attachmentModal');
        attachmentModal.addEventListener('show.bs.modal', function(event) {
            var el = event.relatedTarget; 
            var src = el.getAttribute('data-src');
            document.getElementById('modalImageSrc').src = src;
        });
        attachmentModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalImageSrc').src = '';
        });
    });
</script>
@endpush