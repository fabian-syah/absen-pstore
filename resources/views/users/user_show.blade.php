@extends('layout.master')

@section('title', 'Detail User')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    {{-- KOLOM KIRI: FOTO & PANEL KONTROL ADMIN --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                
                {{-- Foto Profil Wrapper (Besar) --}}
                <div class="mb-4 position-relative d-inline-block">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                             alt="profile" class="img-lg rounded-circle"
                             style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }};">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150" 
                             class="img-lg rounded-circle" style="width: 150px; height: 150px;">
                    @endif

                    {{-- Ikon Centang Biru Overlay --}}
                    @if($user->is_verified)
                        <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="bottom: 5px; right: 5px; width: 45px; height: 45px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                            <i class="mdi mdi-check-decagram text-primary" style="font-size: 30px;"></i>
                        </div>
                    @endif
                </div>
                
                <h4 class="fw-bold mt-2">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                {{-- Status Verifikasi (Badge) --}}
                @if($user->is_verified)
                    <div class="badge badge-primary px-3 py-2 mb-3"><i class="mdi mdi-check-decagram"></i> Akun Terverifikasi</div>
                @else
                    <div class="badge badge-secondary px-3 py-2 mb-3">Belum Verifikasi</div>
                @endif

                <hr>

                {{-- ============================================= --}}
                {{-- AREA NOTIFIKASI REQUEST GANTI FOTO (PENTING) --}}
                {{-- ============================================= --}}
                @if($user->photo_request_status == 'pending')
                    <div class="alert alert-warning border-warning text-start shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <i class="mdi mdi-camera-retake mdi-24px me-2"></i>
                            <strong>Request Ganti Foto</strong>
                        </div>
                        <p class="small mb-2 lh-sm">User ini meminta izin untuk mengganti foto profilnya yang terkunci.</p>
                        
                        <form action="{{ route('users.approve-photo', $user->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning text-white btn-sm w-100 fw-bold">
                                <i class="mdi mdi-check"></i> Setujui Permintaan
                            </button>
                        </form>
                    </div>
                @endif

                {{-- ============================================= --}}
                {{-- PANEL TOMBOL VERIFIKASI (ADMIN/AUDIT ONLY) --}}
                {{-- ============================================= --}}
                <div class="d-grid gap-2 mb-3">
                    <form action="{{ route('users.verify', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        @if($user->is_verified)
                            {{-- Jika sudah verified, tombolnya merah (Cabut) --}}
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                onclick="return confirm('Yakin ingin mencabut verifikasi? Centang biru akan hilang dan user bisa ganti foto lagi.')">
                                <i class="mdi mdi-close-circle"></i> Cabut Verifikasi
                            </button>
                        @else
                            {{-- Jika belum verified, Cek Kelengkapan Data --}}
                            @if($user->profile_photo_path && $user->ktp_photo_path && $user->whatsapp)
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-2">
                                    <i class="mdi mdi-check-decagram"></i> Verifikasi Akun (Beri Centang Biru)
                                </button>
                                <small class="text-muted d-block mt-1 text-small">
                                    Ini akan mengunci foto profil user.
                                </small>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                    <i class="mdi mdi-alert-circle"></i> Data Belum Lengkap
                                </button>
                                <div class="text-start mt-2 px-2">
                                    <small class="text-danger fw-bold d-block">Kekurangan:</small>
                                    <ul class="text-danger small ps-3 mb-0">
                                        @if(!$user->profile_photo_path) <li>Foto Profil kosong</li> @endif
                                        @if(!$user->ktp_photo_path) <li>Foto KTP kosong</li> @endif
                                        @if(!$user->birth_date) <li>Tanggal Lahir kosong</li> @endif
                                    </ul>
                                </div>
                            @endif
                        @endif
                    </form>
                </div>

                {{-- TOMBOL LIHAT KTP --}}
                <div class="text-start bg-light p-3 rounded border">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">DOKUMEN PRIBADI</h6>
                    
                    <div class="mb-2">
                        <small class="d-block text-muted">Foto KTP:</small>
                        @if($user->ktp_photo_path)
                            <a href="{{ asset('storage/' . $user->ktp_photo_path) }}" target="_blank" 
                               class="btn btn-inverse-info btn-sm w-100 mt-1">
                                <i class="mdi mdi-card-account-details"></i> Lihat KTP Asli
                            </a>
                        @else
                            <span class="badge badge-danger">Belum Upload</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: DETAIL INFO TEKS --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Detail Informasi Lengkap</h4>
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <label class="fw-bold text-muted small text-uppercase">Email Login</label>
                        <p class="h5">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="fw-bold text-muted small text-uppercase">Nomor WhatsApp</label>
                        <p class="h5">{{ $user->whatsapp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="fw-bold text-muted small text-uppercase">Lokasi Cabang</label>
                        <p class="h5">{{ $user->branch->name ?? 'Pusat / Semua Cabang' }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="fw-bold text-muted small text-uppercase">Divisi</label>
                        <p class="h5">{{ $user->division->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="fw-bold text-muted small text-uppercase">Tanggal Bergabung</label>
                        <p class="h5">{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->translatedFormat('d F Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="fw-bold text-muted small text-uppercase">Status Sistem</label>
                        <p>
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} p-2">
                                {{ $user->is_active ? 'AKUN AKTIF' : 'AKUN NON-AKTIF' }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Tabel 5 Absensi Terakhir (Preview) --}}
                <div class="mt-4">
                    <h5 class="card-title mb-3">5 Aktivitas Absensi Terakhir</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $log)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}</td>
                                        <td>{{ $log->check_out_time ? \Carbon\Carbon::parse($log->check_out_time)->format('H:i') : '-' }}</td>
                                        <td>
                                            @if($log->is_late_checkin) <span class="badge badge-danger">Telat</span> @endif
                                            @if($log->status == 'present' || $log->status == 'verified') <span class="badge badge-success">Hadir</span> @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada data absensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                    <a href="{{ route('users.index') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar User
                    </a>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning text-white">
                        <i class="mdi mdi-pencil"></i> Edit Data User
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection