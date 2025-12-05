@extends('layout.master')

@section('title', 'Daftar Permintaan Ganti KTP')

@section('content')

{{-- 1. ALERT NOTIFIKASI --}}
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
                                {{-- Kolom 1: User Info --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- FOTO USER (KOTAK) --}}
                                        <div class="me-3">
                                            @if($user->profile_photo_path)
                                                {{-- Menggunakan class 'rounded' agar kotak, bukan 'rounded-circle' --}}
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                     alt="avatar" 
                                                     class="rounded shadow-sm" 
                                                     style="width: 45px; height: 45px; object-fit: cover;">
                                            @else
                                                {{-- Placeholder Kotak --}}
                                                <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center shadow-sm" 
                                                     style="width: 45px; height: 45px; font-weight: bold;">
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

                                {{-- Kolom 2: Divisi --}}
                                <td>{{ $user->division->name ?? '-' }}</td>
                                
                                {{-- Kolom 3: KTP Lama --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_path)
                                        {{-- Thumbnail Old --}}
                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                             alt="Old KTP" 
                                             class="img-thumbnail"
                                             style="width: 80px; height: 50px; cursor: pointer; object-fit: cover;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalOldKtp{{ $user->id }}"
                                             title="Klik untuk memperbesar">

                                        {{-- MODAL OLD KTP --}}
                                        <div class="modal fade" id="modalOldKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">KTP Lama: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center bg-secondary">
                                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded shadow">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary text-white">Kosong</span>
                                    @endif
                                </td>

                                {{-- Kolom 4: KTP Baru (Draft) --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_temp_path)
                                        {{-- Thumbnail New --}}
                                        <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                             alt="New KTP" 
                                             class="img-thumbnail border-success"
                                             style="width: 80px; height: 50px; cursor: pointer; object-fit: cover; border-width: 2px;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalNewKtp{{ $user->id }}"
                                             title="Klik untuk memperbesar">

                                        {{-- MODAL NEW KTP --}}
                                        <div class="modal fade" id="modalNewKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-success">Calon KTP Baru: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center bg-dark">
                                                        <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" class="img-fluid rounded shadow">
                                                    </div>
                                                    <div class="modal-footer justify-content-center">
                                                        <small class="text-muted">Periksa data pada foto ini sebelum menyetujui.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-danger small"><i class="mdi mdi-alert"></i> File Hilang</span>
                                    @endif
                                </td>

                                {{-- Kolom 5: Aksi --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Tombol Approve --}}
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm text-white" 
                                                onclick="return confirm('Apakah Anda yakin ingin menyetujui KTP baru ini? Foto lama akan diganti.')"
                                                title="Setujui Perubahan">
                                                <i class="mdi mdi-check"></i>
                                            </button>
                                        </form>

                                        {{-- Tombol Reject --}}
                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm text-white" 
                                                onclick="return confirm('Tolak permintaan ini? Foto draft akan dihapus.')"
                                                title="Tolak Permintaan">
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
                    <div class="alert alert-light border text-center py-5">
                        <i class="mdi mdi-folder-open-outline d-block mb-2" style="font-size: 30px;"></i>
                        <h6 class="text-muted">Tidak ada permintaan ganti KTP yang pending.</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection