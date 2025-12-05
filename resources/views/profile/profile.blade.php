@extends('layout.master')

@section('title')
    Profil Saya
@endsection

@section('heading')
    Profil Saya
@endsection

@section('content')

    {{-- ALERT MESSAGES --}}
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
        {{-- KOLOM KIRI (FOTO, QR, KTP) --}}
        {{-- ================================================= --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    
                    {{-- 1. BAGIAN FOTO PROFIL --}}
                    <h4 class="card-title d-flex justify-content-center align-items-center gap-2">
                        Foto Profil
                        @if ($user->is_verified)
                            <i class="mdi mdi-check-decagram text-primary" title="Akun Terverifikasi" style="font-size: 1.2rem;"></i>
                        @endif
                    </h4>

                    <div class="position-relative d-inline-block mb-3">
                        @if ($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="foto profil"
                                class="img-lg rounded-circle"
                                style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '4px solid #0d6efd' : 'none' }}">
                        @else
                            <div class="profile-initial-dropdown mx-auto"
                                style="background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold;">
                                {{ getInitials($user->name) }}
                            </div>
                        @endif
                    </div>

                    <p class="card-description fw-bold">
                        {{ $user->name }}
                        @if ($user->is_verified)
                            <br><span class="badge badge-primary badge-pill mt-1">Verified</span>
                        @endif
                    </p>

                    {{-- Form Foto Profil (Sederhana sesuai request lama) --}}
                    @if (!$user->is_verified)
                         <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mb-2">
                            @csrf @method('PUT')
                            <label for="profile_photo" class="btn btn-primary btn-sm w-100 mb-2">Ganti Foto Profil</label>
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>
                    @endif

                    <hr>

                    {{-- 2. BAGIAN QR CODE --}}
                    <div class="text-center">
                        <h4 class="card-title">QR Code Absensi</h4>
                        <div id="qrcode-display" class="d-flex justify-content-center mb-3"></div>
                        <button type="button" class="btn btn-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#qrModal">
                            <i class="mdi mdi-qrcode-scan"></i> Tampilkan Penuh
                        </button>
                    </div>

                    <hr>

                    {{-- 3. BAGIAN DATA KTP (LOGIKA BARU) --}}
                    <div class="text-center">
                        <h4 class="card-title">Data KTP</h4>

                        {{-- KONDISI 1: Belum punya KTP sama sekali (Upload Pertama) --}}
                        @if (!$user->ktp_photo_path)
                            <div class="alert alert-danger py-2 text-small">KTP Belum di-upload!</div>
                            <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <label for="ktp_photo_first" class="btn btn-warning btn-sm w-100">Upload KTP</label>
                                <input type="file" name="ktp_photo" id="ktp_photo_first" class="d-none"
                                    accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                            </form>

                        {{-- KONDISI 2: Sudah punya KTP --}}
                        @else
                            <p class="text-success small"><i class="mdi mdi-check-circle"></i> KTP Ter-upload</p>
                            
                            {{-- Tombol Lihat KTP (Modal) --}}
                            <button type="button" class="btn btn-secondary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#viewKtpModal">
                                <i class="mdi mdi-card-account-details"></i> Lihat KTP Saya
                            </button>

                            {{-- Status Request --}}
                            @if ($user->ktp_request_status == 'pending')
                                <div class="alert alert-warning py-2 text-small">
                                    <i class="mdi mdi-clock"></i> Pengajuan ganti KTP sedang diproses Admin.
                                </div>
                            @elseif ($user->ktp_request_status == 'rejected')
                                <div class="alert alert-danger py-2 text-small">
                                    <i class="mdi mdi-close-circle"></i> Pengajuan ditolak. Silakan ajukan lagi.
                                </div>
                                {{-- Tombol Buka Modal Ganti --}}
                                <button type="button" class="btn btn-outline-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#changeKtpModal">
                                    <i class="mdi mdi-sync"></i> Ajukan Ganti KTP
                                </button>
                            @else
                                {{-- Tombol Buka Modal Ganti --}}
                                <button type="button" class="btn btn-outline-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#changeKtpModal">
                                    <i class="mdi mdi-sync"></i> Ajukan Ganti KTP
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (DATA DIRI) --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form class="forms-sample" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h4 class="card-title">Informasi Akun</h4>
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $user->email) }}" required>
                        </div>
                        
                        <h4 class="card-title mt-4">Keamanan</h4>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password Baru</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengganti.</small>
                                </div>
                             </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation">
                                </div>
                             </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- MODAL - MODAL --}}
    {{-- ================================================= --}}

    {{-- 1. Modal QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div id="qrcode-container-modal" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Modal Lihat KTP --}}
    @if ($user->ktp_photo_path)
    <div class="modal fade" id="viewKtpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">KTP Saya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded" alt="KTP">
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        @if ($user->qr_code_value)
            const qrValue = "{{ $user->qr_code_value }}";
            new QRCode(document.getElementById("qrcode-display"), { text: qrValue, width: 128, height: 128 });
            var qrModal = document.getElementById('qrModal');
            qrModal.addEventListener('show.bs.modal', function(event) {
                var qrContainer = document.getElementById('qrcode-container-modal');
                qrContainer.innerHTML = '';
                new QRCode(qrContainer, { text: qrValue, width: 300, height: 300 });
            });
        @endif
    </script>
@endpush