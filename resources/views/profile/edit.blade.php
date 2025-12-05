@extends('layout.master')

@section('title', 'Profil Saya')

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

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    {{-- ================================================= --}}
    {{-- KOLOM KIRI: FOTO, STATUS, MENU & KTP --}}
    {{-- ================================================= --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                
                {{-- A. TAMPILAN FOTO PROFIL --}}
                <div class="mb-3 position-relative d-inline-block">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" title="Klik untuk memperbesar">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="profile" class="img-lg rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }}; cursor: pointer;">
                        @else
                            <div class="profile-initial-dropdown mx-auto"
                                style="background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif

                        {{-- Ikon Centang Biru Overlay --}}
                        @if($user->is_verified)
                            <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="bottom: 5px; right: 5px; width: 45px; height: 45px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"
                                 title="Akun Terverifikasi">
                                <i class="mdi mdi-check-decagram text-primary" style="font-size: 30px;"></i>
                            </div>
                        @endif
                    </a>
                </div>
                
                <h4 class="fw-bold mt-2">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                {{-- B. BADGE STATUS --}}
                @if($user->is_verified)
                    <div class="badge badge-primary px-3 py-2 mb-4"><i class="mdi mdi-check-decagram"></i> Akun Terverifikasi</div>
                @else
                    <div class="badge badge-secondary px-3 py-2 mb-4">User Biasa</div>
                @endif

                {{-- C. LOGIKA TOMBOL GANTI FOTO --}}
                <div class="mb-4">
                    {{-- KONDISI 1: Belum punya foto sama sekali -> Boleh Upload --}}
                    @if(!$user->profile_photo_path)
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="profile_photo" class="btn btn-sm btn-primary w-100">
                                <i class="mdi mdi-camera"></i> Upload Foto Profil
                            </label>
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>
                        <small class="text-muted d-block mt-1" style="font-size: 10px;">Upload pertama kali gratis akses.</small>
                    
                    {{-- KONDISI 2: Sudah punya foto -> Terkunci / Cek Request --}}
                    @else
                        @if($user->photo_request_status == 'approved')
                            <div class="alert alert-success py-1 small mb-2"><i class="mdi mdi-lock-open"></i> Akses Dibuka (1x Upload)</div>
                            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <label for="profile_photo_unlock" class="btn btn-sm btn-success w-100">
                                    <i class="mdi mdi-upload"></i> Upload Foto Baru
                                </label>
                                <input type="file" name="profile_photo" id="profile_photo_unlock" class="d-none"
                                    accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                            </form>
                        @elseif($user->photo_request_status == 'pending')
                            <div class="alert alert-warning py-1 small mb-2">
                                <i class="mdi mdi-clock"></i> Menunggu Admin
                            </div>
                            <button class="btn btn-sm btn-secondary w-100" disabled>
                                Request Sedang Diproses...
                            </button>
                        @else
                            <form action="{{ route('profile.photo.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-inverse-warning w-100" 
                                        onclick="return confirm('Foto profil terkunci setelah upload pertama. Ajukan izin ganti foto?')">
                                    <i class="mdi mdi-key-variant"></i> Request Ganti Foto
                                </button>
                            </form>
                            <small class="text-muted d-block mt-1" style="font-size: 10px;">Foto Terkunci. Perlu izin untuk mengganti.</small>
                        @endif
                    @endif
                </div>

                {{-- D. MENU NAVIGASI RIWAYAT --}}
                <div class="text-start mb-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">MENU & RIWAYAT</h6>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('attendance.history') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-calendar-clock text-primary me-2"></i> History Absen</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-package-variant text-success me-2"></i> History Inventaris</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('job-targets.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                            <span><i class="mdi mdi-clipboard-list text-info me-2"></i> History Job Desk</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>

                {{-- E. DOKUMEN PRIBADI (KTP) --}}
                <div class="text-start bg-light p-3 rounded border mb-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">DOKUMEN PRIBADI (KTP)</h6>
                    
                    @if (!$user->ktp_photo_path)
                        <div class="alert alert-danger py-1 text-small mb-2 text-center">Belum Upload</div>
                        <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="ktp_photo_first" class="btn btn-warning btn-sm w-100">
                                <i class="mdi mdi-upload"></i> Upload KTP
                            </label>
                            <input type="file" name="ktp_photo" id="ktp_photo_first" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>
                    @else
                        <div class="mb-2">
                            <button type="button" class="btn btn-inverse-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#ktpModal">
                                <i class="mdi mdi-eye"></i> Lihat KTP Saya
                            </button>
                        </div>

                        {{-- Status Request Ganti KTP --}}
                        @if ($user->ktp_request_status == 'pending')
                            <div class="badge badge-warning w-100 py-2"><i class="mdi mdi-clock"></i> Menunggu Approval Admin</div>
                        @elseif ($user->ktp_request_status == 'rejected')
                            <div class="text-danger small mb-1 text-center fw-bold">Request Sebelumnya Ditolak.</div>
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
                        {{-- Data Dasar --}}
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Nama Lengkap (Sesuai KTP)</label>
                            <input type="text" class="form-control bg-light" name="name" value="{{ old('name', $user->name) }}" readonly>
                        </div>
                        
                        {{-- INPUT TANGGAL LAHIR (BARU) --}}
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Tanggal Lahir</label>
                            <input type="date" class="form-control bg-light" name="birth_date" value="{{ old('birth_date', $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '') }}" readonly>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Email Login</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        
                        {{-- Read Only Fields (Info Kantor) --}}
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
                            <label class="fw-bold text-muted small text-uppercase">Status Akun</label>
                            <input type="text" class="form-control bg-lig`ht" 
                                   value="{{ $user->is_active ? 'AKUN AKTIF' : 'NON-AKTIF' }}" 
                                   style="color: white; font-weight: bold; background-color: {{ $user->is_active ? '#28a745' : '#dc3545' }} !important; border-color: {{ $user->is_active ? '#28a745' : '#dc3545' }};" 
                                   readonly>
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

                        {{-- KEAMANAN (GANTI PASSWORD DENGAN BUTTON TOGGLE) --}}
                        <div class="col-12 mb-3"><hr></div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-muted small text-uppercase mb-0">Keamanan</h5>
                                <button type="button" class="btn btn-outline-dark btn-sm" id="btn-toggle-password">
                                    <i class="mdi mdi-lock-reset"></i> Ganti Password
                                </button>
                            </div>
                        </div>

                        {{-- CONTAINER PASSWORD (HIDDEN BY DEFAULT) --}}
                        <div class="col-12 d-none" id="password-container">
                            <div class="row p-3 mb-3 bg-light rounded border mx-1">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold text-muted small text-uppercase">Password Baru</label>
                                    <input type="password" class="form-control" name="password" id="input-password" placeholder="Min. 8 karakter">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold text-muted small text-uppercase">Konfirmasi Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" id="input-password-confirm" placeholder="Ulangi password">
                                </div>
                                <div class="col-12">
                                    <small class="text-danger">* Biarkan kosong jika tidak ingin mengganti password.</small>
                                </div>
                            </div>
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
{{-- MODALS (FOTO & KTP) --}}
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
                    <div class="bg-primary text-white rounded shadow-lg d-flex align-items-center justify-content-center mx-auto" style="width: 300px; height: 300px; font-size: 100px;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal KTP Besar --}}
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

{{-- 3. Modal Form Ganti KTP (Pengajuan) --}}
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
                        <i class="mdi mdi-information-outline"></i> Upload foto KTP baru Anda. Foto akan direview oleh Admin sebelum data diperbarui secara permanen.
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Foto KTP Baru *</label>
                        <input type="file" name="ktp_photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG, JPEG. Maks 5MB.</small>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnToggle = document.getElementById('btn-toggle-password');
        const container = document.getElementById('password-container');
        const inputPass = document.getElementById('input-password');
        const inputConfirm = document.getElementById('input-password-confirm');

        if(btnToggle){
            btnToggle.addEventListener('click', function() {
                if (container.classList.contains('d-none')) {
                    container.classList.remove('d-none');
                    btnToggle.innerHTML = '<i class="mdi mdi-close"></i> Batal Ganti';
                    btnToggle.classList.replace('btn-outline-dark', 'btn-outline-danger');
                } else {
                    container.classList.add('d-none');
                    btnToggle.innerHTML = '<i class="mdi mdi-lock-reset"></i> Ganti Password';
                    btnToggle.classList.replace('btn-outline-danger', 'btn-outline-dark');
                    inputPass.value = '';
                    inputConfirm.value = '';
                }
            });
        }
    });
</script>
@endpush