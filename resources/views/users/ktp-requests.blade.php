@extends('layout.master')

@section('title', 'Permintaan Ganti KTP')

@section('content')
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
                                    <strong>{{ $user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>{{ $user->division->name ?? '-' }}</td>
                                
                                {{-- ======================= --}}
                                {{-- KOLOM KTP LAMA --}}
                                {{-- ======================= --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_path)
                                        {{-- Thumbnail Kecil di Tabel --}}
                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                             alt="Old KTP" 
                                             class="img-thumbnail"
                                             style="width: 80px; height: 50px; object-fit: cover; cursor: pointer;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalOldKtp{{ $user->id }}">
                                        
                                        {{-- MODAL POPUP (DIPERBAIKI) --}}
                                        <div class="modal fade" id="modalOldKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- modal-lg biar lebar --}}
                                                <div class="modal-content">
                                                    <div class="modal-header py-2">
                                                        <h5 class="modal-title fs-6">KTP Lama: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    {{-- p-0 biar full tanpa jarak putih --}}
                                                    <div class="modal-body p-0 text-center bg-light d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                                        {{-- w-100 biar dipaksa lebar, max-height biar gak kelebihan layar HP --}}
                                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                                             class="img-fluid w-100"
                                                             style="max-height: 80vh; object-fit: contain;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-secondary">Kosong</span>
                                    @endif
                                </td>

                                {{-- ======================= --}}
                                {{-- KOLOM KTP BARU --}}
                                {{-- ======================= --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_temp_path)
                                        {{-- Thumbnail Kecil --}}
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                                 alt="New KTP" 
                                                 class="img-thumbnail border-success"
                                                 style="width: 80px; height: 50px; object-fit: cover; cursor: pointer; border-width: 2px;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#modalNewKtp{{ $user->id }}">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.6rem;">
                                                Baru
                                            </span>
                                        </div>

                                        {{-- MODAL POPUP (DIPERBAIKI) --}}
                                        <div class="modal fade" id="modalNewKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header py-2">
                                                        <h5 class="modal-title fs-6 text-success fw-bold">Calon KTP Baru: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-0 text-center bg-light d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                                        <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                                             class="img-fluid w-100"
                                                             style="max-height: 80vh; object-fit: contain;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-danger small">File Hilang</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm text-white" 
                                                onclick="return confirm('Setujui penggantian KTP ini? Foto lama akan dihapus permanen.')">
                                                <i class="mdi mdi-check"></i> Setujui
                                            </button>
                                        </form>

                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm text-white" 
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