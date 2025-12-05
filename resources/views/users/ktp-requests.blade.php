@extends('layout.master')

@section('title', 'Daftar Permintaan Ganti KTP')

@section('content')

{{-- ================================================= --}}
{{-- 1. ALERT NOTIFIKASI --}}
{{-- ================================================= --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                
                {{-- Header Card --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Daftar Permintaan Ganti KTP</h4>
                    <span class="badge bg-warning text-dark">Pending: {{ $users->count() }}</span>
                </div>

                @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>User Info</th>
                                <th>Divisi</th>
                                <th class="text-center">KTP Lama (Saat Ini)</th>
                                <th class="text-center">KTP Baru (Pengajuan)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                {{-- =================================== --}}
                                {{-- KOLOM 1: USER INFO (FOTO KOTAK)     --}}
                                {{-- =================================== --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- Foto Profil Thumbnail --}}
                                        <div class="me-3">
                                            @if($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                     alt="profile" 
                                                     class="rounded shadow-sm" 
                                                     style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; border-radius: 8px !important;"
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

                                    {{-- MODAL FOTO PROFIL --}}
                                    @if($user->profile_photo_path)
                                    <div class="modal fade" id="modalProfile{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-transparent border-0">
                                                <div class="modal-header border-0">
                                                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                         class="img-fluid rounded shadow-lg" 
                                                         style="max-height: 80vh; width: auto; max-width: 100%; object-fit: contain;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
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
                                             class="rounded border shadow-sm"
                                             style="width: 80px; height: 50px; cursor: pointer; object-fit: cover;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalOldKtp{{ $user->id }}">
                                        
                                        {{-- Modal View Old --}}
                                        <div class="modal fade" id="modalOldKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">KTP Lama: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center bg-dark">
                                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Tampilan Kosong (Tanpa Gambar Default) --}}
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

                                        {{-- MODAL KTP BARU --}}
                                        <div class="modal fade" id="modalNewKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-success">Calon KTP Baru: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center bg-dark">
                                                        <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" class="img-fluid rounded">
                                                    </div>
                                                    <div class="modal-footer justify-content-center">
                                                        <small class="text-muted">Pastikan data terlihat jelas sebelum disetujui.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-danger small"><i class="mdi mdi-alert"></i> File Error</span>
                                    @endif
                                </td>

                                {{-- =================================== --}}
                                {{-- KOLOM 5: AKSI                       --}}
                                {{-- =================================== --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Tombol Approve --}}
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm text-white rounded-circle p-2" 
                                                style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;"
                                                onclick="return confirm('Setujui penggantian KTP ini?')"
                                                title="Setujui">
                                                <i class="mdi mdi-check"></i>
                                            </button>
                                        </form>

                                        {{-- Tombol Reject --}}
                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST">
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
                @else
                    {{-- State Kosong --}}
                    <div class="alert alert-light border text-center py-5">
                        <i class="mdi mdi-check-all d-block mb-2 text-success" style="font-size: 40px;"></i>
                        <h6 class="text-muted">Semua permintaan KTP sudah diproses.</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection