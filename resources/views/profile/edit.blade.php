@extends('layout.master')

@section('title', 'Profil Ramadhan - Full Emerald & Gold Edition')

@section('content')

    {{-- CSS KHUSUS TEMA FULL RAMADHAN --}}
    <style>
        :root {
            --ramadhan-primary: #064e3b;
            --ramadhan-secondary: #065f46;
            --ramadhan-gold: #fbbf24;
            --ramadhan-bg: #042f2e;
        }

        /* Full Background Card */
        .ramadhan-card {
            border: 2px solid var(--ramadhan-gold);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            background: var(--ramadhan-primary) !important;
            color: white !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            transition: transform 0.3s ease;
        }

        /* Animasi Lampion Ikon */
        .lantern-icon {
            position: absolute;
            top: 15px;
            left: 25px;
            color: var(--ramadhan-gold);
            font-size: 3rem;
            z-index: 10;
            filter: drop-shadow(0 0 10px var(--ramadhan-gold));
            animation: lanternSwing 3.5s ease-in-out infinite;
            transform-origin: top center;
        }

        @keyframes lanternSwing {
            0% {
                transform: rotate(-10deg);
            }

            50% {
                transform: rotate(10deg);
            }

            100% {
                transform: rotate(-10deg);
            }
        }

        /* Efek Sparkle Stars (Interaktif) */
        .sparkle {
            position: absolute;
            pointer-events: none;
            background: var(--ramadhan-gold);
            border-radius: 50%;
            animation: sparkleFade 1s linear forwards;
        }

        @keyframes sparkleFade {
            0% {
                transform: scale(0);
                opacity: 1;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        /* Input Styling Emerald */
        .ramadhan-input {
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(251, 191, 36, 0.4) !important;
            color: #fff !important;
            border-radius: 10px !important;
        }

        .ramadhan-input:focus {
            border-color: var(--ramadhan-gold) !important;
            box-shadow: 0 0 8px rgba(251, 191, 36, 0.3) !important;
        }

        /* Section Header Gold */
        .section-header-gold {
            background: var(--ramadhan-gold);
            color: var(--ramadhan-primary);
            padding: 12px 15px;
            border-radius: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Award Card */
        .award-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .award-item:hover {
            background: rgba(251, 191, 36, 0.1);
            transform: scale(1.02);
        }

        /* KTP Area */
        .ktp-area {
            background: rgba(0, 0, 0, 0.15);
            border: 2px dashed var(--ramadhan-gold);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
    </style>

    <div class="row">
        {{-- KOLOM KIRI --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card ramadhan-card" id="profileCard">
                <i class="mdi mdi-lamp lantern-icon"></i>

                <div class="card-body text-center mt-4">
                    {{-- FOTO PROFIL --}}
                    <div class="mb-4 position-relative d-inline-block">
                        <div
                            style="padding: 7px; background: linear-gradient(135deg, #fbbf24, #b45309); border-radius: 50%;">
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="rounded-circle shadow"
                                    style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--ramadhan-primary);">
                            @else
                                <div
                                    style="background: var(--ramadhan-secondary); width: 150px; height: 150px; border-radius: 50%; color: var(--ramadhan-gold); font-size: 55px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 4px solid var(--ramadhan-primary);">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <h3 class="fw-bold mb-1" style="color: var(--ramadhan-gold);">{{ $user->name }}</h3>
                    <p style="color: #cbd5e1;" class="small mb-4 text-uppercase fw-bold">
                        {{ str_replace('_', ' ', $user->role) }}</p>

                    <div class="badge px-4 py-2 mb-4 w-100"
                        style="background: var(--ramadhan-gold); color: var(--ramadhan-primary); font-weight: 800; border-radius: 30px;">
                        <i class="mdi mdi-star-face me-1"></i> RAMADHAN KAREEM 1447 H
                    </div>

                    {{-- BUTTONS --}}
                    <div class="d-grid gap-2 mb-5">
                        <button type="button" class="btn btn-sm fw-bold"
                            style="background: white; color: var(--ramadhan-primary);" data-bs-toggle="modal"
                            data-bs-target="#changeProfilePhotoModal">
                            <i class="mdi mdi-camera-plus me-1"></i> GANTI FOTO PROFIL
                        </button>
                        <a href="{{ route('attendance.history') }}"
                            class="btn btn-outline-warning btn-sm fw-bold text-white">
                            <i class="mdi mdi-history me-1"></i> RIWAYAT ABSENSI
                        </a>
                    </div>

                    {{-- PENGHARGAAN --}}
                    <div class="text-start">
                        <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--ramadhan-gold);">PIALA KEJUARAAN
                        </h6>
                        @if(isset($achievements) && $achievements->count() > 0)
                            @foreach($achievements as $year => $items)
                                @foreach($items as $award)
                                    <div class="award-item">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-trophy-award text-warning me-3" style="font-size: 28px;"></i>
                                            <div>
                                                <p class="mb-0 fw-bold" style="font-size: 13px;">Juara {{ $award->rank }}
                                                    ({{ \Carbon\Carbon::create()->month($award->month)->translatedFormat('F') }})</p>
                                                <small class="opacity-75">{{ $award->total_attendance }} Kehadiran</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card ramadhan-card">
                <div class="card-body">
                    <div class="section-header-gold mb-4">
                        <i class="mdi mdi-moon-waning-crescent me-2"></i> PENGATURAN PROFIL BAROKAH
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row">
                            {{-- IDENTITAS --}}
                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold text-warning">NAMA SESUAI KTP</label>
                                <input type="text" class="form-control ramadhan-input" value="{{ $user->name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold text-warning">TANGGAL LAHIR</label>
                                <input type="text" class="form-control ramadhan-input"
                                    value="{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d F Y') : '14 January 2007' }}"
                                    readonly>
                            </div>
                            <div class="col-12 mb-4">
                                <label class="small fw-bold text-warning">EMAIL LOGIN</label>
                                <input type="email" class="form-control ramadhan-input" name="email"
                                    value="{{ old('email', $user->email) }}">
                            </div>

                            {{-- DOKUMEN KTP --}}
                            <div class="col-12 mb-4">
                                <label class="small fw-bold text-warning mb-2">VERIFIKASI IDENTITAS (KTP)</label>
                                <div class="ktp-area">
                                    @if($user->ktp_photo_path)
                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}"
                                            class="img-fluid rounded mb-3 shadow" style="max-height: 150px;">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#ktpModal">LIHAT</button>
                                            <button type="button" class="btn btn-warning btn-sm text-dark fw-bold"
                                                data-bs-toggle="modal" data-bs-target="#changeKtpModal">GANTI KTP</button>
                                        </div>
                                    @else
                                        <i class="mdi mdi-card-account-details-outline d-block mb-2"
                                            style="font-size: 40px; color: var(--ramadhan-gold);"></i>
                                        <p class="small mb-3">KTP belum diunggah. Silakan upload untuk verifikasi akun.</p>
                                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold"
                                            data-bs-toggle="modal" data-bs-target="#changeKtpModal">UPLOAD SEKARANG</button>
                                    @endif
                                </div>
                            </div>

                            {{-- GANTI PASSWORD --}}
                            <div class="col-12 mb-4">
                                <div class="p-3 rounded border border-warning" style="background: rgba(255,255,255,0.05);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold text-warning"><i class="mdi mdi-lock-open-variant me-1"></i>
                                            KEAMANAN KATA SANDI</h6>
                                        <button type="button" class="btn btn-xs btn-outline-warning"
                                            id="btnTogglePass">AKTIFKAN EDIT</button>
                                    </div>
                                    <div id="passContainer" class="d-none pt-2">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <input type="password" class="form-control ramadhan-input" name="password"
                                                    placeholder="Password Baru">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <input type="password" class="form-control ramadhan-input"
                                                    name="password_confirmation" placeholder="Ulangi Password">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn px-5 fw-bold"
                                    style="background: var(--ramadhan-gold); color: var(--ramadhan-primary); border-radius: 30px;">SIMPAN
                                    PERUBAHAN</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    <div class="modal fade" id="ktpModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-body text-center"><img src="{{ asset('storage/' . $user->ktp_photo_path) }}"
                        class="img-fluid rounded"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeKtpModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: var(--ramadhan-primary); border: 2px solid var(--ramadhan-gold); color: white;">
                <div class="modal-header border-bottom border-warning">
                    <h5 class="modal-title fw-bold text-warning">UPLOAD KTP</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.ktp.request') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="file" name="ktp_photo" class="form-control ramadhan-input" required>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-warning w-100 text-dark fw-bold">KIRIM DOKUMEN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // 1. Logic Toggle Password
        document.getElementById('btnTogglePass').addEventListener('click', function () {
            const container = document.getElementById('passContainer');
            container.classList.toggle('d-none');
            this.textContent = container.classList.contains('d-none') ? 'AKTIFKAN EDIT' : 'BATAL';
        });

        // 2. Animasi Sparkle Interaktif (Mengikuti Kursor)
        const profileCard = document.getElementById('profileCard');
        profileCard.addEventListener('mousemove', function (e) {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';

            // Posisi sparkle relatif terhadap card
            const rect = profileCard.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const size = Math.random() * 8 + 2;
            sparkle.style.width = size + 'px';
            sparkle.style.height = size + 'px';
            sparkle.style.left = x + 'px';
            sparkle.style.top = y + 'px';

            profileCard.appendChild(sparkle);

            setTimeout(() => sparkle.remove(), 1000);
        });
    </script>
@endpush