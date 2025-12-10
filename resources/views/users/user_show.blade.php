@extends('layout.master')

@section('title', 'Detail User')

@section('content')

{{-- TAMPILKAN ERROR JIKA GAGAL VERIFIKASI (KARENA FOTO/KTP KOSONG) --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    {{-- KOLOM KIRI (FOTO & MENU) --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                {{-- Foto Profil --}}
                <div class="mb-3 position-relative d-inline-block">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 class="img-lg rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }}">
                        @else
                            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                style="background-color: #007bff; width: 150px; height: 150px; font-size: 40px;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        @if($user->is_verified)
                            <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="bottom: 5px; right: 5px; width: 45px; height: 45px; border: 3px solid white;">
                                <i class="mdi mdi-check-decagram text-primary" style="font-size: 30px;"></i>
                            </div>
                        @endif
                    </a>
                </div>
                
                <h4 class="fw-bold mt-2">{{ $user->name }}</h4>
                <p class="text-muted mb-1">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                {{-- STATUS AKTIF / NON-AKTIF (BARU DITAMBAHKAN) --}}
                <div class="mb-3">
                    @if($user->is_active)
                        <span class="badge rounded-pill bg-success px-3 py-2">
                            <i class="mdi mdi-account-check me-1"></i> AKTIF
                        </span>
                    @else
                        <span class="badge rounded-pill bg-danger px-3 py-2">
                            <i class="mdi mdi-account-off me-1"></i> NON-AKTIF
                        </span>
                    @endif
                </div>

               {{-- Menu Navigasi --}}
                <div class="text-start mb-4 mt-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">MENU & RIWAYAT</h6>
                    <div class="list-group list-group-flush">
                        {{-- 1. HISTORY ABSEN --}}
                        <a href="{{ route('attendance.history', ['employeeId' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-calendar-clock text-primary me-2"></i> History Absen Full</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        {{-- 2. HISTORY INVENTARIS (BARU DITAMBAHKAN) --}}
                        {{-- Mengirim parameter user_id agar InventoryController bisa memfilter data --}}
                        <a href="{{ route('inventory.index', ['user_id' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-package-variant text-success me-2"></i> History Inventaris</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>

                {{-- AREA VERIFIKASI & KTP --}}
                <div class="d-grid gap-2">
                    <form action="{{ route('users.verify', $user->id) }}" method="POST">
                        @csrf @method('PATCH')
                        
                        @if($user->is_verified)
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Cabut verifikasi?')">
                                <i class="mdi mdi-close-circle"></i> Cabut Verifikasi
                            </button>
                        @else
                            {{-- Tombol Verifikasi --}}
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="mdi mdi-check-decagram"></i> Verifikasi Akun
                            </button>
                        @endif
                    </form>

                    {{-- TOMBOL LIHAT KTP (POP UP) --}}
                    @if($user->ktp_photo_path)
                        <button type="button" class="btn btn-info btn-sm text-white w-100 mt-1" data-bs-toggle="modal" data-bs-target="#ktpPhotoModal">
                            <i class="mdi mdi-card-account-details-outline"></i> Lihat Foto KTP
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm w-100 mt-1" disabled>
                            <i class="mdi mdi-close-circle-outline"></i> KTP Belum Diupload
                        </button>
                    @endif
                </div>
                
                {{-- Indikator Status Kelengkapan --}}
                @if(!$user->is_verified)
                    <div class="mt-2 text-start small">
                        <span class="d-block {{ $user->profile_photo_path ? 'text-success' : 'text-danger' }}">
                            <i class="mdi {{ $user->profile_photo_path ? 'mdi-check' : 'mdi-close' }}"></i> Foto Profil
                        </span>
                        <span class="d-block {{ $user->ktp_photo_path ? 'text-success' : 'text-danger' }}">
                            <i class="mdi {{ $user->ktp_photo_path ? 'mdi-check' : 'mdi-close' }}"></i> Foto KTP
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN (DETAIL & 5 AKTIVITAS) --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Detail Informasi</h4>
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Email</label>
                        <p class="h6">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Cabang</label>
                        <p class="h6">{{ $user->branch->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Divisi</label>
                        <p class="h6">{{ $user->division->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">WhatsApp</label>
                        <p class="h6">{{ $user->whatsapp ?? '-' }}</p>
                    </div>

                    {{-- TANGGAL LAHIR & SOSMED --}}
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tanggal Lahir</label>
                        <p class="h6">
                            <i class="mdi mdi-cake-variant text-warning me-1"></i>
                            {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y') : '-' }}
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Media Sosial</label>
                        <div class="d-flex align-items-center gap-3 mt-1">
                            @if($user->facebook)
                                <a href="{{ $user->facebook }}" target="_blank" class="text-decoration-none" title="Facebook">
                                    <i class="mdi mdi-facebook text-primary" style="font-size: 28px;"></i>
                                </a>
                            @endif
                            @if($user->instagram)
                                <a href="{{ $user->instagram }}" target="_blank" class="text-decoration-none" title="Instagram">
                                    <i class="mdi mdi-instagram text-danger" style="font-size: 28px;"></i>
                                </a>
                            @endif
                            @if($user->tiktok)
                                <a href="{{ $user->tiktok }}" target="_blank" class="text-decoration-none" title="TikTok">
                                    <i class="mdi mdi-music-note text-dark" style="font-size: 28px;"></i> 
                                </a>
                            @endif
                            @if(!$user->facebook && !$user->instagram && !$user->tiktok)
                                <span class="text-muted small">-</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TABEL 5 AKTIVITAS TERAKHIR --}}
                <div class="mt-4">
                    <h5 class="card-title mb-3 border-bottom pb-2">5 Kehadiran Terakhir</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $log)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($log->check_in_time)->format('l') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">
                                                {{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                            </span>
                                            @if($log->is_late_checkin)
                                                <i class="mdi mdi-alert-circle text-warning ms-1" title="Terlambat"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->check_out_time)
                                                <span class="fw-bold text-dark">
                                                    {{ \Carbon\Carbon::parse($log->check_out_time)->format('H:i') }}
                                                </span>
                                                @if($log->is_early_checkout)
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Cepat</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Belum</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->presence_status == 'Izin' || $log->presence_status == 'Sakit' || $log->presence_status == 'Cuti')
                                                <span class="badge bg-info">{{ $log->presence_status }}</span>
                                            @elseif($log->is_late_checkin)
                                                <span class="badge bg-warning text-dark">Telat Hadir</span>
                                            @else
                                                <span class="badge bg-success">Hadir / Tepat Waktu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="mdi mdi-calendar-blank display-4 mb-2 d-block"></i>
                                            Belum ada data kehadiran valid.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('users.index') }}" class="btn btn-light">Kembali</a>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning text-white">Edit Data</a>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MODAL FOTO PROFIL --}}
<div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 text-center">
            @if($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
            @endif
        </div>
    </div>
</div>

{{-- MODAL FOTO KTP --}}
<div class="modal fade" id="ktpPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Foto KTP - {{ $user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                @if($user->ktp_photo_path)
                    <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid" style="width: 100%; object-fit: contain;">
                @else
                    <div class="p-5 text-muted">
                        <i class="mdi mdi-image-off display-1"></i>
                        <p class="mt-2">Tidak ada foto KTP.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection