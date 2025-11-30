@extends('layout.master')

@section('title', 'Permintaan Ganti Foto')
@section('heading', 'Verifikasi Ganti Foto')

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Permintaan Ganti Foto Profil</h4>
                    <p class="card-description">
                        Daftar user verified yang mengajukan izin untuk mengganti foto profil mereka.
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Cabang & Divisi</th>
                                    <th>Foto Saat Ini</th>
                                    <th>Waktu Request</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- Foto Kecil --}}
                                                <div class="me-3">
                                                    @if ($user->profile_photo_path)
                                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                             alt="profile" class="img-sm rounded-circle"
                                                             style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #0d6efd;">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px; font-weight: bold;">
                                                            {{ substr($user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $user->name }}</div>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $user->branch->name ?? 'Semua Cabang' }}</div>
                                            <small class="text-muted">{{ $user->division->name ?? '-' }}</small>
                                        </td>
                                        <td>
                                            {{-- Tombol Lihat Foto --}}
                                            @if ($user->profile_photo_path)
                                                <button type="button" class="btn btn-inverse-info btn-sm btn-icon" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewPhotoModal{{ $user->id }}"
                                                        title="Lihat Foto Lama">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>

                                                {{-- Modal Foto --}}
                                                <div class="modal fade" id="viewPhotoModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Foto Profil Saat Ini: {{ $user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                                     class="img-fluid rounded" style="max-height: 400px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">Belum ada foto</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $user->updated_at->diffForHumans() }}
                                        </td>
                                        <td>
                                            {{-- Tombol Setujui --}}
                                            <form action="{{ route('users.approve-photo', $user->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Setujui permintaan ganti foto untuk {{ $user->name }}?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm text-white">
                                                    <i class="mdi mdi-check-circle-outline me-1"></i> Beri Izin
                                                </button>
                                            </form>

                                            {{-- Tombol Tolak (Opsional - Reset status ke 'none') --}}
                                            {{-- Jika mau fitur tolak, perlu buat route/method reject --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="mdi mdi-camera-off mdi-48px d-block mb-2"></i>
                                                Tidak ada permintaan ganti foto saat ini.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection