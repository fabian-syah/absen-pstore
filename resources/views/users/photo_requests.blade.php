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
                                    <th class="text-center">Foto Lama</th>
                                    <th class="text-center">Foto Baru</th>
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
                                            <span class="badge badge-outline-primary py-1" style="font-size: 0.7rem;">
                                                {{ $user->branch->name ?? 'Pusat' }} - {{ $user->division->name ?? '-' }}
                                            </span>
                                        </td>

                                        {{-- 2. FOTO LAMA (Thumbnail) --}}
                                        <td class="text-center">
                                            @if ($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                     alt="old" class="rounded-circle border"
                                                     style="width: 45px; height: 45px; object-fit: cover; filter: grayscale(100%); opacity: 0.8;">
                                            @else
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto"
                                                     style="width: 45px; height: 45px;">
                                                    <i class="mdi mdi-account-off"></i>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 3. FOTO BARU (Thumbnail) --}}
                                        <td class="text-center bg-light">
                                            @if ($user->profile_photo_temp_path)
                                                <div class="position-relative d-inline-block">
                                                    <img src="{{ asset('storage/' . $user->profile_photo_temp_path) }}" 
                                                         alt="new" class="rounded-circle border border-3 border-success shadow-sm"
                                                         style="width: 55px; height: 55px; object-fit: cover; cursor: pointer;"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#compareModal{{ $user->id }}">
                                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                                        <span class="visually-hidden">New</span>
                                                    </span>
                                                </div>
                                            @else
                                                <span class="badge badge-danger" style="font-size: 0.7rem;">File Hilang</span>
                                            @endif
                                        </td>

                                        {{-- 4. WAKTU --}}
                                        <td class="text-muted small">
                                            {{ $user->updated_at->diffForHumans() }}
                                        </td>

                                        {{-- 5. AKSI --}}
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#compareModal{{ $user->id }}">
                                                Cek Validasi
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- ======================== --}}
                                    {{-- MODAL VALIDASI (RESPONSIF FIXED) --}}
                                    {{-- ======================== --}}
                                    <div class="modal fade" id="compareModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header py-2">
                                                    <h5 class="modal-title fs-6 fw-bold">Validasi: {{ \Illuminate\Support\Str::limit($user->name, 20) }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                
                                                <div class="modal-body bg-light">
                                                    <div class="row g-3">
                                                        {{-- KIRI: FOTO LAMA --}}
                                                        <div class="col-12 col-md-6 text-center">
                                                            <div class="card shadow-sm h-100 border-0">
                                                                <div class="card-body p-2">
                                                                    <h6 class="text-muted text-uppercase fw-bold mb-2 small">Saat Ini (Lama)</h6>
                                                                    {{-- WRAPPER AGAR GAMBAR TIDAK MELEDAK --}}
                                                                    <div class="ratio ratio-1x1 mx-auto bg-secondary bg-opacity-10 rounded overflow-hidden" style="max-width: 250px;">
                                                                        @if ($user->profile_photo_path)
                                                                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                                                 class="object-fit-cover w-100 h-100" 
                                                                                 alt="Foto Lama">
                                                                        @else
                                                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                                                <i class="mdi mdi-image-off mdi-48px"></i><br>Tidak ada foto
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- KANAN: FOTO BARU --}}
                                                        <div class="col-12 col-md-6 text-center">
                                                            <div class="card shadow-sm h-100 border-success">
                                                                <div class="card-body p-2">
                                                                    <h6 class="text-success text-uppercase fw-bold mb-2 small">Pengajuan (Baru)</h6>
                                                                    {{-- WRAPPER AGAR GAMBAR TIDAK MELEDAK --}}
                                                                    <div class="ratio ratio-1x1 mx-auto bg-dark rounded overflow-hidden position-relative" style="max-width: 250px;">
                                                                        @if ($user->profile_photo_temp_path)
                                                                            <img src="{{ asset('storage/' . $user->profile_photo_temp_path) }}" 
                                                                                 class="object-fit-cover w-100 h-100" 
                                                                                 alt="Foto Baru">
                                                                        @else
                                                                            <div class="d-flex align-items-center justify-content-center h-100 text-danger flex-column">
                                                                                <i class="mdi mdi-alert-circle mdi-48px"></i>
                                                                                <span class="small fw-bold mt-2">File Fisik Hilang</span>
                                                                                <span style="font-size:10px;">(Mohon Tolak & Upload Ulang)</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="modal-footer justify-content-between py-2">
                                                    {{-- FORM TOLAK --}}
                                                    <form action="{{ route('users.reject-photo', $user->id) }}" method="POST" class="flex-fill me-1"
                                                          onsubmit="return confirm('Tolak pengajuan ini?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger w-100">
                                                            <i class="mdi mdi-close"></i> Tolak
                                                        </button>
                                                    </form>

                                                    {{-- FORM SETUJUI --}}
                                                    <form action="{{ route('users.approve-photo', $user->id) }}" method="POST" class="flex-fill ms-1">
                                                        @csrf @method('PATCH')
                                                        @if($user->profile_photo_temp_path)
                                                            <button type="submit" class="btn btn-success text-white w-100">
                                                                <i class="mdi mdi-check"></i> Setujui
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-secondary w-100" disabled>File Error</button>
                                                        @endif
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
                                                <h6>Tidak ada permintaan baru</h6>
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