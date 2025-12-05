@extends('layout.master')

@section('title', 'Permintaan Ganti KTP')

@section('content')

{{-- FIX iOS SAFARI --}}
<style>
    .img-thumbnail {
        pointer-events: auto !important;
        touch-action: manipulation !important;
    }
    .card, .card-body, .table-responsive {
        border-radius: 6px !important; /* kotak, bukan oval */
    }
    .modal-backdrop {
        opacity: .75 !important;
    }
</style>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Permintaan Ganti KTP</h4>
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Divisi</th>
                                <th class="text-center">KTP Lama</th>
                                <th class="text-center">KTP Baru (Draft)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>

                                <td>{{ $user->division->name ?? '-' }}</td>

                                {{-- KTP LAMA --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_path)
                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                             class="img-thumbnail"
                                             style="width: 80px; height: 50px; object-fit: cover; cursor: pointer;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalOldKtp{{ $user->id }}">
                                    @else
                                        <span class="badge badge-secondary">Kosong</span>
                                    @endif
                                </td>

                                {{-- KTP BARU --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_temp_path)
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                                 class="img-thumbnail border-success"
                                                 style="width: 80px; height: 50px; object-fit: cover; cursor: pointer; border-width: 2px;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#modalNewKtp{{ $user->id }}">

                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" 
                                                  style="font-size: 0.6rem;">
                                                Baru
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-danger small">File Hilang</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td>
                                    <div class="d-flex gap-2">

                                        {{-- Approve --}}
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('Setujui penggantian KTP ini? Foto lama akan dihapus permanen.')">
                                                <i class="mdi mdi-check"></i> Setujui
                                            </button>
                                        </form>

                                        {{-- Reject --}}
                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Tolak pengajuan ini?')">
                                                <i class="mdi mdi-close"></i> Tolak
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
                    <div class="alert alert-info mt-3">Tidak ada permintaan ganti KTP saat ini.</div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection


{{-- ======================================================= --}}
{{-- MODALS (DIPISAHKAN, **WAJIB** UNTUK FIX iOS SAFARI) --}}
{{-- ======================================================= --}}
@push('modals')

@foreach($users as $user)

    {{-- MODAL KTP LAMA --}}
    @if($user->ktp_photo_path)
    <div class="modal fade" id="modalOldKtp{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content bg-dark bg-opacity-75 border-0">

                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"
                        style="z-index: 1056;"></button>

                <div class="modal-body d-flex justify-content-center align-items-center p-0">
                    <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                         class="img-fluid"
                         style="max-height: 90vh; object-fit: contain;">
                </div>

            </div>
        </div>
    </div>
    @endif


    {{-- MODAL KTP BARU --}}
    @if($user->ktp_photo_temp_path)
    <div class="modal fade" id="modalNewKtp{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content bg-dark bg-opacity-75 border-0">

                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"
                        style="z-index: 1056;"></button>

                <div class="modal-body d-flex justify-content-center align-items-center p-0">
                    <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                         class="img-fluid"
                         style="max-height: 90vh; object-fit: contain;">
                </div>

            </div>
        </div>
    </div>
    @endif

@endforeach

@endpush
