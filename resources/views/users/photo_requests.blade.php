@extends('layout.master')

@section('title', 'Permintaan Ganti Foto')
@section('heading', 'Verifikasi Ganti Foto')

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Daftar Permintaan Ganti Foto Profil</h4>
                            <p class="card-description text-muted mb-0">
                                Daftar user yang mengajukan foto profil baru untuk diverifikasi.
                            </p>
                        </div>
                    </div>

                    {{-- NOTIFIKASI --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User Info</th>
                                    <th class="text-center">Foto Lama (Saat Ini)</th>
                                    <th class="text-center">Foto Baru (Diajukan)</th>
                                    <th>Waktu Request</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $user)
                                    <tr>
                                        {{-- 1. INFO USER --}}
                                        <td>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <div class="text-muted small mb-1">{{ $user->email }}</div>
                                            <span class="badge badge-outline-primary py-1">
                                                {{ $user->branch->name ?? 'Pusat' }} - {{ $user->division->name ?? '-' }}
                                            </span>
                                        </td>

                                        {{-- 2. FOTO LAMA --}}
                                        <td class="text-center">
                                            @if ($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                     alt="old" class="rounded-circle border"
                                                     style="width: 60px; height: 60px; object-fit: cover; filter: grayscale(100%); opacity: 0.7;"
                                                     title="Foto Lama">
                                            @else
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto"
                                                     style="width: 60px; height: 60px;">
                                                    <i class="mdi mdi-account-off"></i>
                                                </div>
                                                <small class="d-block text-muted mt-1">Kosong</small>
                                            @endif
                                        </td>

                                        {{-- 3. FOTO BARU (TEMP) --}}
                                        <td class="text-center bg-light">
                                            @if ($user->profile_photo_temp_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_temp_path) }}" 
                                                     alt="new" class="rounded-circle border border-3 border-success shadow-sm"
                                                     style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#compareModal{{ $user->id }}"
                                                     title="Klik untuk membandingkan">
                                                <div class="mt-1"><span class="badge badge-success" style="font-size: 10px;">BARU</span></div>
                                            @else
                                                <span class="text-danger small">Error: File Hilang</span>
                                            @endif
                                        </td>

                                        {{-- 4. WAKTU --}}
                                        <td class="text-muted small">
                                            <i class="mdi mdi-clock-outline"></i> {{ $user->updated_at->diffForHumans() }}
                                        </td>

                                        {{-- 5. AKSI --}}
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm btn-icon-text mb-1" 
                                                    data-bs-toggle="modal" data-bs-target="#compareModal{{ $user->id }}">
                                                <i class="mdi mdi-compare btn-icon-prepend"></i> Cek & Validasi
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- ======================== --}}
                                    {{-- MODAL COMPARISON (POPUP) --}}
                                    {{-- ======================== --}}
                                    <div class="modal fade" id="compareModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Validasi Perubahan Foto: {{ $user->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row text-center">
                                                        {{-- KIRI: FOTO LAMA --}}
                                                        <div class="col-md-6 border-end">
                                                            <h6 class="text-muted text-uppercase fw-bold mb-3">Foto Saat Ini (Lama)</h6>
                                                            @if ($user->profile_photo_path)
                                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                                     class="img-fluid rounded mb-2" 
                                                                     style="max-height: 300px; border: 4px solid #e3e3e3;">
                                                            @else
                                                                <div class="alert alert-secondary">User belum memiliki foto profil sebelumnya.</div>
                                                            @endif
                                                        </div>

                                                        {{-- KANAN: FOTO BARU --}}
                                                        <div class="col-md-6 bg-light py-2">
                                                            <h6 class="text-success text-uppercase fw-bold mb-3">Foto Yang Diajukan (Baru)</h6>
                                                            @if ($user->profile_photo_temp_path)
                                                                <img src="{{ asset('storage/' . $user->profile_photo_temp_path) }}" 
                                                                     class="img-fluid rounded mb-2 shadow" 
                                                                     style="max-height: 300px; border: 4px solid #28a745;">
                                                            @else
                                                                <div class="alert alert-danger">File foto baru tidak ditemukan.</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    {{-- FORM TOLAK --}}
                                                    <form action="{{ route('users.reject-photo', $user->id) }}" method="POST" 
                                                          onsubmit="return confirm('Yakin ingin MENOLAK foto ini? Foto baru akan dihapus.');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="mdi mdi-close-circle"></i> Tolak Pengajuan
                                                        </button>
                                                    </form>

                                                    {{-- FORM SETUJUI --}}
                                                    <form action="{{ route('users.approve-photo', $user->id) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-success text-white px-4">
                                                            <i class="mdi mdi-check-circle"></i> Setujui & Ganti Foto
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- END MODAL --}}

                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="mdi mdi-camera-off mdi-48px d-block mb-2"></i>
                                                <h5>Tidak ada permintaan baru</h5>
                                                <p>Semua permintaan ganti foto sudah diproses.</p>
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