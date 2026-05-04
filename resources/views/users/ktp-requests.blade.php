@extends('layout.master')

@section('title', 'Daftar Permintaan Ganti KTP')

@push('styles')
<style>
    /* 1. FIX GERAK-GERAK (Disable Global Row Scale) */
    .table tbody tr:hover {
        transform: none !important;
        background-color: rgba(0,0,0,0.02) !important;
    }

    /* 2. RESPONSIVE CARD STYLES */
    .ktp-card-mobile {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        background: #fff;
    }
    
    .ktp-card-mobile:active {
        transform: scale(0.98);
    }

    .photo-preview-mobile {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 10px;
        cursor: pointer;
        border: 1px solid #eee;
    }

    .badge-pending-premium {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        color: #d81b60;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(216, 27, 96, 0.2);
    }
</style>
@endpush

@section('content')

{{-- ================================================= --}}
{{-- 1. ALERT NOTIFIKASI --}}
{{-- ================================================= --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
        <div class="d-flex align-items-center">
            <i class="mdi mdi-check-circle fs-4 me-2"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        {{-- Header Page --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Permintaan Ganti KTP</h3>
                <p class="text-muted small mb-0">Kelola perubahan data identitas karyawan secara aman.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge-pending-premium">
                    <i class="mdi mdi-clock-outline me-1"></i> {{ $users->count() }} Permintaan Menunggu
                </span>
            </div>
        </div>

        @if($users->count() > 0)
            {{-- ================================================= --}}
            {{-- TAMPILAN DESKTOP (TABLE)                          --}}
            {{-- ================================================= --}}
            <div class="card d-none d-md-block border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                                <tr>
                                    <th class="ps-4">User Info</th>
                                    <th>Divisi</th>
                                    <th class="text-center">KTP Lama</th>
                                    <th class="text-center">KTP Baru</th>
                                    <th class="pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center py-2">
                                            <div class="me-3">
                                                @if($user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                         alt="profile" 
                                                         class="rounded-circle shadow-sm" 
                                                         style="width: 45px; height: 45px; object-fit: cover; cursor: pointer; border: 2px solid #fff;"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#modalProfile{{ $user->id }}">
                                                @else
                                                    <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                                         style="width: 45px; height: 45px; font-size: 18px; background: rgba(13, 110, 253, 0.1);">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                                                <small class="text-muted" style="font-size: 0.7rem;">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#modalProfile{{ $user->id }}"
                                                     title="Lihat Foto Profil">
                                            @else
                                                <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center shadow-sm" 
                                                     style="width: 50px; height: 50px; font-weight: bold; font-size: 20px; border-radius: 8px !important;">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>

                                {{-- KOLOM 2: DIVISI --}}
                                <td>{{ $user->division->name ?? '-' }}</td>
                                
                                {{-- =================================== --}}
                                {{-- KOLOM 3: KTP LAMA (SESUAI REQUEST)  --}}
                                {{-- =================================== --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_path)
                                        {{-- Thumbnail --}}
                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                             alt="Old KTP" 
                                             class="shadow-sm"
                                             style="width: 60px; height: auto; cursor: pointer; border-radius: 4px;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalOldKtp{{ $user->id }}">
                                    @else
                                        <span class="badge bg-secondary">Kosong</span>
                                    @endif
                                </td>

                                {{-- =================================== --}}
                                {{-- KOLOM 4: KTP BARU (PENGAJUAN)       --}}
                                {{-- =================================== --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_temp_path)
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                                 alt="New KTP" 
                                                 class="rounded border border-success shadow-sm"
                                                 style="width: 80px; height: 50px; cursor: pointer; object-fit: cover;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#modalNewKtp{{ $user->id }}">
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                                <span class="visually-hidden">New</span>
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-danger small"><i class="mdi mdi-alert"></i> File Error</span>
                                    @endif
                                </td>

                                {{-- KOLOM 5: AKSI --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'mdi mdi-loading mdi-spin\'></i>';">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm text-white rounded-circle p-2" 
                                                style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;"
                                                onclick="return confirm('Setujui penggantian KTP ini?')"
                                                title="Setujui">
                                                <i class="mdi mdi-check"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'mdi mdi-loading mdi-spin\'></i>';">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm text-white rounded-circle p-2" 
                                                style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;"
                                                onclick="return confirm('Tolak permintaan ini?')"
                                                title="Tolak">
                                                <i class="mdi mdi-close"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- TAMPILAN MOBILE (CARDS)                           --}}
            {{-- ================================================= --}}
            <div class="d-md-none">
                @foreach($users as $user)
                <div class="ktp-card-mobile p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                     class="rounded-circle shadow-sm" 
                                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #fff;"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalProfile{{ $user->id }}">
                            @else
                                <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" 
                                     style="width: 50px; height: 50px; font-size: 20px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                            <span class="badge bg-soft-info text-info rounded-pill px-2 py-1" style="font-size: 0.65rem; background: rgba(0, 184, 212, 0.1);">
                                {{ $user->division->name ?? 'Staff' }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block fw-bold">KTP LAMA</label>
                            @if($user->ktp_photo_path)
                                <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                     class="photo-preview-mobile"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalOldKtp{{ $user->id }}">
                            @else
                                <div class="photo-preview-mobile bg-light d-flex align-items-center justify-content-center text-muted small">Kosong</div>
                            @endif
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block fw-bold">KTP BARU</label>
                            @if($user->ktp_photo_temp_path)
                                <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                     class="photo-preview-mobile border-success"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalNewKtp{{ $user->id }}">
                            @else
                                <div class="photo-preview-mobile bg-light d-flex align-items-center justify-content-center text-danger small">Error</div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST" class="flex-grow-1" onsubmit="this.querySelector('button').disabled=true;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill fw-bold py-2" onclick="return confirm('Setujui?')">
                                <i class="mdi mdi-check me-1"></i> SETUJUI
                            </button>
                        </form>
                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST" class="flex-grow-1" onsubmit="this.querySelector('button').disabled=true;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold py-2" onclick="return confirm('Tolak?')">
                                <i class="mdi mdi-close me-1"></i> TOLAK
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            {{-- State Kosong --}}
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <div class="card-body">
                    <i class="mdi mdi-check-circle-outline text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 fw-bold">Semua Permintaan Sudah Diproses</h5>
                    <p class="text-muted">Tidak ada pengajuan ganti KTP yang perlu ditinjau saat ini.</p>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- MODAL SECTION --}}
@foreach($users as $user)
    {{-- 1. Modal Profil --}}
    @if($user->profile_photo_path)
    <div class="modal fade" id="modalProfile{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Foto Profil: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                         class="img-fluid rounded" 
                         style="width: 100%; height: auto; max-height: 85vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. Modal KTP Lama --}}
    @if($user->ktp_photo_path)
    <div class="modal fade" id="modalOldKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">KTP Lama: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                         class="img-fluid rounded"
                         style="width: 100%; height: auto; max-height: 85vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 3. Modal KTP Baru --}}
    @if($user->ktp_photo_temp_path)
    <div class="modal fade" id="modalNewKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success fs-4">Calon KTP Baru: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                         class="img-fluid rounded shadow"
                         style="width: 100%; height: auto; max-height: 85vh; object-fit: contain;">
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <small class="text-muted fs-6">Pastikan data terlihat jelas sebelum disetujui.</small>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection