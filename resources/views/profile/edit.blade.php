@extends('layout.master')

@section('title', 'Profil Saya')

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

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    {{-- ================================================= --}}
    {{-- KOLOM KIRI: FOTO, MENU RIWAYAT & PANEL KTP --}}
    {{-- ================================================= --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                
                {{-- 1. FOTO PROFIL WRAPPER (KLIK UNTUK POPUP) --}}
                <div class="mb-3 position-relative d-inline-block">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" title="Klik untuk memperbesar">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="profile" class="img-lg rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }}; cursor: pointer;">
                        @else
                            <div class="profile-initial-dropdown mx-auto"
                                style="background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                                {{ getInitials($user->name) }}
                            </div>
                        @endif

                        {{-- Ikon Centang Biru Overlay --}}
                        @if($user->is_verified)
                            <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="bottom: 5px; right: 5px; width: 45px; height: 45px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                <i class="mdi mdi-check-decagram text-primary" style="font-size: 30px;"></i>
                            </div>
                        @endif
                    </a>
                </div>
                
                <h4 class="fw-bold mt-2">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                {{-- Status Verifikasi (Badge) --}}
                @if($user->is_verified)
                    <div class="badge badge-primary px-3 py-2 mb-4"><i class="mdi mdi-check-decagram"></i> Akun Terverifikasi</div>
                @else
                    <div class="badge badge-secondary px-3 py-2 mb-2">User Biasa</div>
                    {{-- Form Upload Foto Jika Belum Verified --}}
                    <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf @method('PUT')
                        <label for="profile_photo" class="btn btn-sm btn-outline-primary w-100 mt-2">
                            <i class="mdi mdi-camera"></i> Ganti Foto Profil
                        </label>
                        <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                            accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                    </form>
                @endif

                {{-- Logic Ganti Foto Jika Verified --}}
                @if ($user->is_verified)
                    @if($user->photo_request_status == 'approved')
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                            @csrf @method('PUT')
                            <div class="alert alert-success py-1 small mb-2">Akses dibuka. Silakan upload.</div>
                            <label for="profile_photo_unlock" class="btn btn-sm btn-success w-100">
                                <i class="mdi mdi-upload"></i> Upload Foto Baru
                            </label>
                            <input type="file" name="profile_photo" id="profile_photo_unlock" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>
                    @elseif($user->photo_request_status == 'pending')
                        <div class="alert alert-warning py-1 small mb-4">Request ganti foto sedang diproses.</div>
                    @else
                        <form action="{{ route('profile.photo.request') }}" method="POST" class="mb-4">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-inverse-warning w-100" onclick="return confirm('Ajukan izin ganti foto?')">
                                <i class="mdi mdi-key-variant"></i> Req. Ganti Foto
                            </button>
                        </form>
                    @endif
                @endif

                {{-- ============================================= --}}
                {{-- MENU NAVIGASI RIWAYAT --}}
                {{-- ============================================= --}}
                <div class="text-start mb-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">MENU & RIWAYAT</h6>
                    <div class="list-group list-group-flush">
                        {{-- History Absen --}}
                        <a href="{{ route('attendance.history') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-calendar-clock text-primary me-2"></i> History Absen</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                        
                        {{-- History Inventaris --}}
                        <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-package-variant text-success me-2"></i> History Inventaris</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        {{-- History Job Desk --}}
                        <a href="{{ route('job-targets.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-clipboard-list text-info me-2"></i> History Job Desk</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>

                {{-- ============================================= --}}
                {{-- DOKUMEN PRIBADI (KTP) --}}
                {{-- ============================================= --}}
                <div class="text-start bg-light p-3 rounded border mb-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">DOKUMEN PRIBADI (KTP)</h6>
                    
                    {{-- KONDISI 1: Belum punya KTP (Upload Pertama) --}}
                    @if (!$user->ktp_photo_path)
                        <div class="alert alert-danger py-1 text-small mb-2">Belum Upload</div>
                        <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="ktp_photo_first" class="btn btn-warning btn-sm w-100">
                                <i class="mdi mdi-upload"></i> Upload KTP
                            </label>
                            <input type="file" name="ktp_photo" id="ktp_photo_first" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>

                    {{-- KONDISI 2: Sudah punya KTP --}}
                    @else
                        <div class="mb-2">
                            <button type="button" class="btn btn-inverse-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#ktpModal">
                                <i class="mdi mdi-eye"></i> Lihat KTP Saya
                            </button>
                        </div>

                        {{-- Logic Request Ganti KTP --}}
                        @if ($user->ktp_request_status == 'pending')
                            <div class="badge badge-warning w-100 py-2"><i class="mdi mdi-clock"></i> Menunggu Approval</div>
                        @elseif ($user->ktp_request_status == 'rejected')
                            <div class="text-danger small mb-1 text-center">Request Ditolak.</div>
                            <button type="button" class="btn btn-outline-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#changeKtpModal">
                                <i class="mdi mdi-sync"></i> Ajukan Lagi
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#changeKtpModal">
                                <i class="mdi mdi-sync"></i> Ajukan Ganti KTP
                            </button>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- KOLOM KANAN: FORM EDIT INFORMASI --}}
    {{-- ================================================= --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Informasi Lengkap</h4>
                
                <form class="forms-sample mt-4" action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Nama Lengkap (Sesuai KTP)</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Email Login</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        
                        {{-- Read Only Fields (Data Karyawan) --}}
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Lokasi Cabang</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->branch->name ?? 'Pusat / Semua Cabang' }}" readonly>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Divisi</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->division->name ?? '-' }}" readonly>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Tanggal Bergabung</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->translatedFormat('d F Y') : '-' }}" readonly>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Status Sistem</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->is_active ? 'AKUN AKTIF' : 'NON-AKTIF' }}" readonly>
                        </div>

                        {{-- Kontak & Sosmed --}}
                        <div class="col-12 mb-3"><hr></div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Nomor WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="62812...">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="username">
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">TikTok</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" name="tiktok" value="{{ old('tiktok', $user->tiktok) }}" placeholder="username">
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">LinkedIn</label>
                            <input type="text" class="form-control" name="linkedin" value="{{ old('linkedin', $user->linkedin) }}" placeholder="username">
                        </div>

                        {{-- Keamanan --}}
                        <div class="col-12 mb-3"><hr></div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ubah">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- ================================================= --}}
{{-- MODALS --}}
{{-- ================================================= --}}

{{-- 1. Modal Foto Profil Besar --}}
<div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                @if($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=500" class="img-fluid rounded shadow-lg">
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal KTP Besar (View) --}}
@if($user->ktp_photo_path)
<div class="modal fade" id="ktpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kartu Tanda Penduduk (KTP)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-dark">
                <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
@endif

{{-- 3. Modal Form Ganti KTP --}}
<div class="modal fade" id="changeKtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajukan Ganti KTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.ktp.request') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Upload foto KTP baru Anda. Foto akan direview oleh Admin sebelum diganti secara permanen.
                    </div>
                    <div class="form-group">
                        <label>Foto KTP Baru *</label>
                        <input type="file" name="ktp_photo" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection