@extends('layout.master')

@section('title', 'Profil Saya')

@section('content')

{{-- ALERT NOTIFIKASI (SAMA SEPERTI SEBELUMNYA) --}}
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
    {{-- KOLOM KIRI: FOTO, STATUS, MENU & PENGHARGAAN --}}
    {{-- ================================================= --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                
                {{-- A. FOTO PROFIL (CODE SAMA SEPERTI SEBELUMNYA) --}}
                <div class="mb-3 position-relative d-inline-block">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="profile" class="img-lg rounded-circle shadow-sm"
                                 style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }};">
                        @else
                            <div class="profile-initial-dropdown mx-auto shadow-sm"
                                style="background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif

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

                @if($user->is_verified)
                    <div class="badge badge-primary px-3 py-2 mb-4"><i class="mdi mdi-check-decagram"></i> Akun Terverifikasi</div>
                @else
                    <div class="badge badge-secondary px-3 py-2 mb-4">User Biasa</div>
                @endif

                {{-- TOMBOL GANTI FOTO (SAMA SEPERTI SEBELUMNYA - DI PERSINGKAT UNTUK HEMAT TEMPAT DI JAWABAN) --}}
                <div class="mb-4">
                     @if(!$user->profile_photo_path)
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="profile_photo" class="btn btn-sm btn-primary w-100"><i class="mdi mdi-camera"></i> Upload Foto Profil</label>
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none" accept="image/*" onchange="this.form.submit()">
                        </form>
                    @else
                        @if($user->photo_request_status == 'pending')
                            <button class="btn btn-sm btn-secondary w-100" disabled>Menunggu Approval</button>
                        @else
                            <button type="button" class="btn btn-sm btn-inverse-warning w-100" data-bs-toggle="modal" data-bs-target="#changeProfilePhotoModal">
                                <i class="mdi mdi-camera-retake"></i> Ganti Foto Profil
                            </button>
                        @endif
                    @endif
                </div>

                {{-- B. MENU NAVIGASI --}}
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
                    </div>
                </div>
                
                {{-- C. [BARU] HALL OF FAME (PENGHARGAAN) --}}
                @if(isset($achievements) && $achievements->count() > 0)
                <div class="text-start mb-4">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                        <h6 class="text-warning fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">
                            <i class="mdi mdi-trophy me-1"></i> Penghargaan
                        </h6>
                        <span class="badge bg-warning text-dark">{{ $achievements->flatten()->count() }} Piala</span>
                    </div>

                    <div class="award-container" style="max-height: 300px; overflow-y: auto;">
                        @foreach($achievements as $year => $items)
                            <div class="year-label mb-2 mt-1">
                                <span class="badge bg-light text-muted border">{{ $year }}</span>
                            </div>
                            @foreach($items as $award)
                                <div class="award-card rank-{{ $award->rank }} mb-2 p-2 rounded d-flex align-items-center position-relative overflow-hidden">
                                    {{-- Glowing Effect Background --}}
                                    <div class="glow-bg"></div>
                                    
                                    {{-- Icon Piala/Medali --}}
                                    <div class="award-icon me-3">
                                        @if($award->rank == 1)
                                            <i class="mdi mdi-trophy text-white" style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                                        @elseif($award->rank == 2)
                                            <i class="mdi mdi-medal text-white" style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                                        @else
                                            <i class="mdi mdi-medal-outline text-white" style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                                        @endif
                                    </div>

                                    {{-- Text Detail --}}
                                    <div class="flex-grow-1 position-relative z-index-1 text-white">
                                        <h6 class="mb-0 fw-bold" style="font-size: 14px;">
                                            Juara {{ $award->rank }}
                                            <span class="opacity-75 font-weight-normal ms-1" style="font-size: 11px;">(Bulan {{ \Carbon\Carbon::create()->month($award->month)->translatedFormat('F') }})</span>
                                        </h6>
                                        <small class="opacity-75" style="font-size: 11px;">
                                            <i class="mdi mdi-check-circle-outline me-1"></i>{{ $award->total_attendance }} Kehadiran Tepat Waktu
                                        </small>
                                    </div>

                                    {{-- Shine Effect --}}
                                    <div class="shine"></div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- D. DOKUMEN PRIBADI (KTP) --}}
                <div class="text-start bg-light p-3 rounded border mb-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">DOKUMEN PRIBADI (KTP)</h6>
                    @if (!$user->ktp_photo_path)
                        <div class="alert alert-danger py-1 text-small mb-2 text-center">Belum Upload</div>
                        <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="ktp_photo_first" class="btn btn-warning btn-sm w-100">
                                <i class="mdi mdi-upload"></i> Upload KTP
                            </label>
                            <input type="file" name="ktp_photo" id="ktp_photo_first" class="d-none" accept="image/*" onchange="this.form.submit()">
                        </form>
                    @else
                        <div class="mb-2">
                            <button type="button" class="btn btn-inverse-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#ktpModal">
                                <i class="mdi mdi-eye"></i> Lihat KTP Saya
                            </button>
                        </div>
                        {{-- Status Request Ganti KTP (SAMA SEPERTI SEBELUMNYA) --}}
                        @if ($user->ktp_request_status == 'pending')
                            <div class="badge badge-warning w-100 py-2"><i class="mdi mdi-clock"></i> Menunggu Approval</div>
                        @elseif ($user->ktp_request_status == 'rejected')
                            <div class="text-danger small mb-1 text-center fw-bold">Request Ditolak.</div>
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
    {{-- KOLOM KANAN: FORM EDIT INFORMASI (TIDAK BERUBAH)  --}}
    {{-- ================================================= --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Informasi Lengkap</h4>
                <form class="forms-sample mt-4" action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    
                    {{-- FORM INPUT SAMA PERSIS DENGAN KODE SEBELUMNYA --}}
                    {{-- SAYA RINGKAS AGAR FOKUS PADA BAGIAN PENGHARGAAN --}}
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly>
                        </div>
                         <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                        {{-- ... INPUT LAINNYA SAMA ... --}}
                    </div>

                    {{-- PASSWORD FIELD (SAMA) --}}
                    
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODALS (SAMA SEPERTI SEBELUMNYA) --}}
{{-- SAYA SERTAKAN KEMBALI AGAR FULL CODE --}}

<div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content bg-transparent border-0"><div class="modal-body text-center">
        @if($user->profile_photo_path) <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded shadow-lg" style="max-height: 80vh;"> @endif
    </div></div></div>
</div>

<div class="modal fade" id="changeProfilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ganti Foto Profil</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('profile.photo.request') }}" method="POST" enctype="multipart/form-data">@csrf
                <div class="modal-body"><input type="file" name="profile_photo" class="form-control" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

@if($user->ktp_photo_path)
<div class="modal fade" id="ktpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center bg-dark"><img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded" style="max-height: 80vh;"></div></div></div>
</div>
@endif

<div class="modal fade" id="changeKtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ganti KTP</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('profile.ktp.request') }}" method="POST" enctype="multipart/form-data">@csrf
                <div class="modal-body"><input type="file" name="ktp_photo" class="form-control" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* AWARD CARD STYLES */
    .award-card {
        transition: transform 0.2s;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .award-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    /* Rank 1 (Gold) */
    .award-card.rank-1 {
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
        border: 1px solid #E6C200;
    }
    
    /* Rank 2 (Silver) */
    .award-card.rank-2 {
        background: linear-gradient(135deg, #E0E0E0 0%, #BDBDBD 100%);
        border: 1px solid #A9A9A9;
    }

    /* Rank 3 (Bronze) */
    .award-card.rank-3 {
        background: linear-gradient(135deg, #CD7F32 0%, #A0522D 100%);
        border: 1px solid #8B4513;
    }

    /* Shine Animation */
    .shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        100% { left: 200%; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnToggle = document.getElementById('btn-toggle-password');
        const container = document.getElementById('password-container');
        if(btnToggle){
            btnToggle.addEventListener('click', function() {
                container.classList.toggle('d-none');
            });
        }
    });
</script>
@endpush@extends('layout.master')

@section('title', 'Profil Saya')

@section('content')

{{-- ALERT NOTIFIKASI (SAMA SEPERTI SEBELUMNYA) --}}
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
    {{-- KOLOM KIRI: FOTO, STATUS, MENU & PENGHARGAAN --}}
    {{-- ================================================= --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                
                {{-- A. FOTO PROFIL (CODE SAMA SEPERTI SEBELUMNYA) --}}
                <div class="mb-3 position-relative d-inline-block">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="profile" class="img-lg rounded-circle shadow-sm"
                                 style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }};">
                        @else
                            <div class="profile-initial-dropdown mx-auto shadow-sm"
                                style="background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif

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

                @if($user->is_verified)
                    <div class="badge badge-primary px-3 py-2 mb-4"><i class="mdi mdi-check-decagram"></i> Akun Terverifikasi</div>
                @else
                    <div class="badge badge-secondary px-3 py-2 mb-4">User Biasa</div>
                @endif

                {{-- TOMBOL GANTI FOTO (SAMA SEPERTI SEBELUMNYA - DI PERSINGKAT UNTUK HEMAT TEMPAT DI JAWABAN) --}}
                <div class="mb-4">
                     @if(!$user->profile_photo_path)
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="profile_photo" class="btn btn-sm btn-primary w-100"><i class="mdi mdi-camera"></i> Upload Foto Profil</label>
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none" accept="image/*" onchange="this.form.submit()">
                        </form>
                    @else
                        @if($user->photo_request_status == 'pending')
                            <button class="btn btn-sm btn-secondary w-100" disabled>Menunggu Approval</button>
                        @else
                            <button type="button" class="btn btn-sm btn-inverse-warning w-100" data-bs-toggle="modal" data-bs-target="#changeProfilePhotoModal">
                                <i class="mdi mdi-camera-retake"></i> Ganti Foto Profil
                            </button>
                        @endif
                    @endif
                </div>

                {{-- B. MENU NAVIGASI --}}
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
                    </div>
                </div>
                
                {{-- C. [BARU] HALL OF FAME (PENGHARGAAN) --}}
                @if(isset($achievements) && $achievements->count() > 0)
                <div class="text-start mb-4">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                        <h6 class="text-warning fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">
                            <i class="mdi mdi-trophy me-1"></i> Penghargaan
                        </h6>
                        <span class="badge bg-warning text-dark">{{ $achievements->flatten()->count() }} Piala</span>
                    </div>

                    <div class="award-container" style="max-height: 300px; overflow-y: auto;">
                        @foreach($achievements as $year => $items)
                            <div class="year-label mb-2 mt-1">
                                <span class="badge bg-light text-muted border">{{ $year }}</span>
                            </div>
                            @foreach($items as $award)
                                <div class="award-card rank-{{ $award->rank }} mb-2 p-2 rounded d-flex align-items-center position-relative overflow-hidden">
                                    {{-- Glowing Effect Background --}}
                                    <div class="glow-bg"></div>
                                    
                                    {{-- Icon Piala/Medali --}}
                                    <div class="award-icon me-3">
                                        @if($award->rank == 1)
                                            <i class="mdi mdi-trophy text-white" style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                                        @elseif($award->rank == 2)
                                            <i class="mdi mdi-medal text-white" style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                                        @else
                                            <i class="mdi mdi-medal-outline text-white" style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                                        @endif
                                    </div>

                                    {{-- Text Detail --}}
                                    <div class="flex-grow-1 position-relative z-index-1 text-white">
                                        <h6 class="mb-0 fw-bold" style="font-size: 14px;">
                                            Juara {{ $award->rank }}
                                            <span class="opacity-75 font-weight-normal ms-1" style="font-size: 11px;">(Bulan {{ \Carbon\Carbon::create()->month($award->month)->translatedFormat('F') }})</span>
                                        </h6>
                                        <small class="opacity-75" style="font-size: 11px;">
                                            <i class="mdi mdi-check-circle-outline me-1"></i>{{ $award->total_attendance }} Kehadiran Tepat Waktu
                                        </small>
                                    </div>

                                    {{-- Shine Effect --}}
                                    <div class="shine"></div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- D. DOKUMEN PRIBADI (KTP) --}}
                <div class="text-start bg-light p-3 rounded border mb-4">
                    <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">DOKUMEN PRIBADI (KTP)</h6>
                    @if (!$user->ktp_photo_path)
                        <div class="alert alert-danger py-1 text-small mb-2 text-center">Belum Upload</div>
                        <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label for="ktp_photo_first" class="btn btn-warning btn-sm w-100">
                                <i class="mdi mdi-upload"></i> Upload KTP
                            </label>
                            <input type="file" name="ktp_photo" id="ktp_photo_first" class="d-none" accept="image/*" onchange="this.form.submit()">
                        </form>
                    @else
                        <div class="mb-2">
                            <button type="button" class="btn btn-inverse-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#ktpModal">
                                <i class="mdi mdi-eye"></i> Lihat KTP Saya
                            </button>
                        </div>
                        {{-- Status Request Ganti KTP (SAMA SEPERTI SEBELUMNYA) --}}
                        @if ($user->ktp_request_status == 'pending')
                            <div class="badge badge-warning w-100 py-2"><i class="mdi mdi-clock"></i> Menunggu Approval</div>
                        @elseif ($user->ktp_request_status == 'rejected')
                            <div class="text-danger small mb-1 text-center fw-bold">Request Ditolak.</div>
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
    {{-- KOLOM KANAN: FORM EDIT INFORMASI (TIDAK BERUBAH)  --}}
    {{-- ================================================= --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Informasi Lengkap</h4>
                <form class="forms-sample mt-4" action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    
                    {{-- FORM INPUT SAMA PERSIS DENGAN KODE SEBELUMNYA --}}
                    {{-- SAYA RINGKAS AGAR FOKUS PADA BAGIAN PENGHARGAAN --}}
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly>
                        </div>
                         <div class="col-md-6 mb-4">
                            <label class="fw-bold text-muted small text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                        {{-- ... INPUT LAINNYA SAMA ... --}}
                    </div>

                    {{-- PASSWORD FIELD (SAMA) --}}
                    
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODALS (SAMA SEPERTI SEBELUMNYA) --}}
{{-- SAYA SERTAKAN KEMBALI AGAR FULL CODE --}}

<div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content bg-transparent border-0"><div class="modal-body text-center">
        @if($user->profile_photo_path) <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded shadow-lg" style="max-height: 80vh;"> @endif
    </div></div></div>
</div>

<div class="modal fade" id="changeProfilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ganti Foto Profil</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('profile.photo.request') }}" method="POST" enctype="multipart/form-data">@csrf
                <div class="modal-body"><input type="file" name="profile_photo" class="form-control" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

@if($user->ktp_photo_path)
<div class="modal fade" id="ktpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center bg-dark"><img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded" style="max-height: 80vh;"></div></div></div>
</div>
@endif

<div class="modal fade" id="changeKtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ganti KTP</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('profile.ktp.request') }}" method="POST" enctype="multipart/form-data">@csrf
                <div class="modal-body"><input type="file" name="ktp_photo" class="form-control" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* AWARD CARD STYLES */
    .award-card {
        transition: transform 0.2s;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .award-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    /* Rank 1 (Gold) */
    .award-card.rank-1 {
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
        border: 1px solid #E6C200;
    }
    
    /* Rank 2 (Silver) */
    .award-card.rank-2 {
        background: linear-gradient(135deg, #E0E0E0 0%, #BDBDBD 100%);
        border: 1px solid #A9A9A9;
    }

    /* Rank 3 (Bronze) */
    .award-card.rank-3 {
        background: linear-gradient(135deg, #CD7F32 0%, #A0522D 100%);
        border: 1px solid #8B4513;
    }

    /* Shine Animation */
    .shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        100% { left: 200%; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnToggle = document.getElementById('btn-toggle-password');
        const container = document.getElementById('password-container');
        if(btnToggle){
            btnToggle.addEventListener('click', function() {
                container.classList.toggle('d-none');
            });
        }
    });
</script>
@endpush