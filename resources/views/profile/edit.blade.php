@extends('layout.master')

@section('title', 'Profil Saya - Ramadhan Edition')

@section('content')

    {{-- CSS KHUSUS TEMA RAMADHAN --}}
    <style>
        :root {
            --ramadhan-primary: #064e3b; /* Hijau Emerald Gelap */
            --ramadhan-secondary: #059669; /* Hijau Emerald Terang */
            --ramadhan-gold: #fbbf24; /* Gold */
            --ramadhan-light-gold: #fef3c7;
        }

        /* Background Ornamen Islami */
        .ramadhan-card {
            border: 2px solid var(--ramadhan-gold);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
            background: #fff;
        }

        .ramadhan-card::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background-image: url('https://www.transparentpng.com/download/ramadan/ramadan-ornament-free-png-17.png');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.2;
            pointer-events: none;
        }

        /* Efek Lampion Gantung */
        .lantern-decor {
            position: absolute;
            top: -10px;
            left: 20px;
            width: 40px;
            z-index: 10;
            filter: drop-shadow(0 0 5px var(--ramadhan-gold));
            animation: swing 3s ease-in-out infinite;
            transform-origin: top center;
        }

        @keyframes swing {
            0% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
            100% { transform: rotate(-5deg); }
        }

        /* Profile Frame Gold */
        .profile-frame {
            padding: 8px;
            background: linear-gradient(135deg, #fbbf24, #d97706);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
        }

        /* Badge Ramadhan */
        .badge-ramadhan {
            background: var(--ramadhan-primary);
            color: var(--ramadhan-gold);
            border: 1px solid var(--ramadhan-gold);
            font-weight: bold;
        }

        /* Header Form Emerald */
        .section-header-ramadhan {
            background: linear-gradient(to right, var(--ramadhan-primary), var(--ramadhan-secondary));
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-left: 5px solid var(--ramadhan-gold);
        }

        .section-header-ramadhan i {
            color: var(--ramadhan-gold);
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Input Styling */
        .ramadhan-input {
            border: 1px solid #d1d5db;
            transition: all 0.3s;
        }

        .ramadhan-input:focus {
            border-color: var(--ramadhan-secondary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .ramadhan-input[readonly] {
            background-color: #f9fafb;
        }

        /* Award Styling (Ramadhan Edition) */
        .award-card {
            border-radius: 12px;
            transition: transform 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .award-card:hover { transform: scale(1.02); }
        .rank-1 { background: linear-gradient(135deg, #064e3b, #065f46); border-left: 5px solid #fbbf24; }
        .rank-2 { background: linear-gradient(135deg, #1e293b, #334155); border-left: 5px solid #cbd5e1; }
        .rank-3 { background: linear-gradient(135deg, #78350f, #92400e); border-left: 5px solid #f97316; }

        /* Button Emerald */
        .btn-emerald {
            background-color: var(--ramadhan-primary);
            color: var(--ramadhan-gold);
            border: 1px solid var(--ramadhan-gold);
            transition: all 0.3s;
        }
        .btn-emerald:hover {
            background-color: var(--ramadhan-secondary);
            color: white;
            transform: translateY(-2px);
        }
    </style>

    {{-- ALERT NOTIFIKASI --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-left: 5px solid #28a745;">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- ================================================= --}}
        {{-- KOLOM KIRI: PROFILE & PENGHARGAAN --}}
        {{-- ================================================= --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card ramadhan-card">
                <img src="https://www.transparentpng.com/download/ramadan/lantern-ramadan-transparent-background-5.png" class="lantern-decor" alt="Lantern">

                <div class="card-body text-center">
                    {{-- FOTO PROFIL --}}
                    <div class="mb-4 mt-3 position-relative d-inline-block">
                        <div class="profile-frame">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                                @if($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                         class="rounded-circle shadow-sm"
                                         style="width: 140px; height: 140px; object-fit: cover; border: 3px solid white;">
                                @else
                                    <div class="mx-auto" style="background-color: var(--ramadhan-primary); width: 140px; height: 140px; border-radius: 50%; color: var(--ramadhan-gold); font-size: 45px; display: flex; align-items: center; justify-content: center; border: 3px solid white; font-weight: bold;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </a>
                        </div>
                        @if($user->is_verified)
                            <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center shadow" 
                                 style="bottom: 10px; right: 10px; width: 40px; height: 40px;">
                                <i class="mdi mdi-check-decagram text-primary" style="font-size: 26px;"></i>
                            </div>
                        @endif
                    </div>

                    <h4 class="fw-bold mb-1" style="color: var(--ramadhan-primary);">{{ $user->name }}</h4>
                    <p class="text-muted small mb-3"><i class="mdi mdi-briefcase-outline me-1"></i>{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                    <div class="badge badge-ramadhan px-4 py-2 mb-4">
                        <i class="mdi mdi-moon-waning-crescent me-1"></i> Ramadhan Kareem 1447 H
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="mb-4 px-3">
                        @if(!$user->profile_photo_path)
                            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <label for="profile_photo" class="btn btn-emerald w-100 btn-sm"><i class="mdi mdi-camera"></i> Upload Foto Profil</label>
                                <input type="file" name="profile_photo" id="profile_photo" class="d-none" accept="image/*" onchange="this.form.submit()">
                            </form>
                        @else
                            <button type="button" class="btn btn-emerald w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#changeProfilePhotoModal">
                                <i class="mdi mdi-camera-retake"></i> Ganti Foto Profil
                            </button>
                        @endif
                    </div>

                    {{-- MENU QUICK LINKS --}}
                    <div class="text-start mb-4 px-2">
                        <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--ramadhan-primary); font-size: 13px;">RIWAYAT & AKTIVITAS</h6>
                        <div class="list-group list-group-flush">
                            <a href="{{ route('attendance.history') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-0 bg-transparent">
                                <span><i class="mdi mdi-calendar-check text-success me-2"></i> History Absensi</span>
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </a>
                            <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-0 bg-transparent">
                                <span><i class="mdi mdi-cube-outline text-success me-2"></i> Inventaris Tim</span>
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </a>
                        </div>
                    </div>

                    {{-- HALL OF FAME RAMADHAN --}}
                    @if(isset($achievements) && $achievements->count() > 0)
                        <div class="text-start px-2">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--ramadhan-gold); font-size: 13px;">PENCAPAIAN TERBAIK</h6>
                            @foreach($achievements as $year => $items)
                                @foreach($items as $award)
                                    <div class="award-card rank-{{ $award->rank }} p-3 mb-2 text-white">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="mdi mdi-trophy-variant" style="font-size: 28px; color: var(--ramadhan-gold);"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-bold" style="font-size: 14px;">Juara {{ $award->rank }} ({{ \Carbon\Carbon::create()->month($award->month)->translatedFormat('F') }})</p>
                                                <p class="mb-0 small opacity-75">{{ $award->total_attendance }} Kehadiran Tepat Waktu</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- KOLOM KANAN: FORM DATA DIRI --}}
        {{-- ================================================= --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card ramadhan-card">
                <div class="card-body">
                    <div class="section-header-ramadhan">
                        <i class="mdi mdi-account-edit"></i>
                        <h5 class="mb-0 fw-bold">PENGATURAN PROFIL RAMADHAN</h5>
                    </div>

                    <form class="forms-sample" action="{{ route('profile.update') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="fw-bold text-muted small"><i class="mdi mdi-star text-warning"></i> DATA PRIBADI</p>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control ramadhan-input" value="{{ $user->name }}" readonly>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold">Tanggal Lahir</label>
                                <input type="text" class="form-control ramadhan-input" value="{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d F Y') : '-' }}" readonly>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="small fw-bold">Alamat Email</label>
                                <input type="email" class="form-control ramadhan-input" name="email" value="{{ old('email', $user->email) }}">
                            </div>

                            <div class="col-12 mb-3 mt-2">
                                <p class="fw-bold text-muted small"><i class="mdi mdi-office-building text-warning"></i> DATA PEKERJAAN</p>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold">Cabang / Lokasi</label>
                                <input type="text" class="form-control ramadhan-input" value="{{ $user->branch->name ?? 'Pusat' }}" readonly>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold">Divisi</label>
                                <input type="text" class="form-control ramadhan-input" value="{{ $user->division->name ?? '-' }}" readonly>
                            </div>

                            <div class="col-12 mb-3 mt-2">
                                <p class="fw-bold text-muted small"><i class="mdi mdi-phone text-warning"></i> MEDIA SOSIAL</p>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold">WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white"><i class="mdi mdi-whatsapp"></i></span>
                                    <input type="text" class="form-control ramadhan-input" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}">
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white"><i class="mdi mdi-instagram"></i></span>
                                    <input type="text" class="form-control ramadhan-input" name="instagram" value="{{ old('instagram', $user->instagram) }}">
                                </div>
                            </div>

                            {{-- KEAMANAN --}}
                            <div class="col-12 mt-3">
                                <div class="p-3 rounded border bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 fw-bold">Keamanan Akun</h6>
                                        <small class="text-muted">Ganti password secara berkala untuk menjaga keamanan.</small>
                                    </div>
                                    <button type="button" class="btn btn-dark btn-sm" id="btn-toggle-password">Ganti Password</button>
                                </div>
                            </div>

                            <div class="col-12 d-none mt-3" id="password-container">
                                <div class="row p-3 bg-white border rounded shadow-sm mx-1">
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold">Password Baru</label>
                                        <input type="password" class="form-control ramadhan-input" name="password">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold">Konfirmasi Password</label>
                                        <input type="password" class="form-control ramadhan-input" name="password_confirmation">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex justify-content-end gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-light px-4">Kembali</a>
                            <button type="submit" class="btn btn-emerald px-4 shadow-sm">Simpan Profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded shadow-lg border border-white" style="max-height: 80vh; border-width: 5px !important;">
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeProfilePhotoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border-top: 5px solid var(--ramadhan-gold);">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Ganti Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.photo.request') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">Foto baru akan ditinjau oleh tim Audit terlebih dahulu.</div>
                        <input type="file" name="profile_photo" class="form-control" required accept="image/*">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-emerald w-100">Ajukan Perubahan</button>
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
            if(btnToggle) {
                btnToggle.addEventListener('click', function() {
                    container.classList.toggle('d-none');
                    this.textContent = container.classList.contains('d-none') ? 'Ganti Password' : 'Batal';
                    this.classList.toggle('btn-dark');
                    this.classList.toggle('btn-outline-danger');
                });
            }
        });
    </script>
@endpush