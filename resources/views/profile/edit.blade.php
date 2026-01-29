@extends('layout.master')

@section('title', 'Profil Saya - Ramadhan Edition Full Emerald')

@section('content')

    {{-- CSS KHUSUS TEMA FULL RAMADHAN EMERALD --}}
    <style>
        :root {
            --ramadhan-primary: #064e3b;
            /* Hijau Emerald Gelap */
            --ramadhan-secondary: #065f46;
            /* Hijau Emerald Terang */
            --ramadhan-gold: #fbbf24;
            /* Gold */
            --ramadhan-light-gold: #fef3c7;
            --ramadhan-bg: #042f2e;
            /* Background sangat gelap untuk kontras */
        }

        /* Full Background Card */
        .ramadhan-card {
            border: 2px solid var(--ramadhan-gold);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
            background: var(--ramadhan-primary) !important;
            /* Paksa jadi Emerald */
            color: white !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* Ornamen Lampion Menggunakan Ikon */
        .lantern-icon {
            position: absolute;
            top: 10px;
            left: 20px;
            color: var(--ramadhan-gold);
            font-size: 2.5rem;
            z-index: 10;
            filter: drop-shadow(0 0 8px var(--ramadhan-gold));
            animation: swing 3s ease-in-out infinite;
            transform-origin: top center;
        }

        @keyframes swing {
            0% {
                transform: rotate(-8deg);
            }

            50% {
                transform: rotate(8deg);
            }

            100% {
                transform: rotate(-8deg);
            }
        }

        /* Mengubah semua input agar gelap/emerald */
        .ramadhan-input {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid var(--ramadhan-gold) !important;
            color: white !important;
        }

        .ramadhan-input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .ramadhan-input[readonly] {
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(251, 191, 36, 0.3) !important;
        }

        /* Badge & Label */
        .text-muted-ramadhan {
            color: var(--ramadhan-light-gold) !important;
            font-weight: bold;
        }

        .badge-gold {
            background: var(--ramadhan-gold);
            color: var(--ramadhan-primary);
            font-weight: bold;
        }

        /* Custom Header */
        .section-header-ramadhan {
            background: var(--ramadhan-gold);
            color: var(--ramadhan-primary);
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 800;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* List Group Custom */
        .list-group-item-ramadhan {
            background: transparent !important;
            border-bottom: 1px solid rgba(251, 191, 36, 0.2) !important;
            color: white !important;
            transition: 0.3s;
        }

        .list-group-item-ramadhan:hover {
            background: rgba(251, 191, 36, 0.1) !important;
        }

        /* Tombol Ganti Foto */
        .btn-gold-action {
            background: var(--ramadhan-gold);
            color: var(--ramadhan-primary);
            border: none;
            font-weight: bold;
        }

        .btn-gold-action:hover {
            background: white;
            color: var(--ramadhan-primary);
        }
    </style>

    <div class="row">
        {{-- KOLOM KIRI --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card ramadhan-card">
                {{-- IKON LANTEN --}}
                <i class="mdi mdi-lamp lantern-icon"></i>

                <div class="card-body text-center">
                    <div class="mb-4 mt-5 position-relative d-inline-block">
                        <div style="padding: 6px; background: var(--ramadhan-gold); border-radius: 50%;">
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="rounded-circle"
                                    style="width: 140px; height: 140px; object-fit: cover; border: 4px solid var(--ramadhan-primary);">
                            @else
                                <div
                                    style="background: var(--ramadhan-secondary); width: 140px; height: 140px; border-radius: 50%; color: var(--ramadhan-gold); font-size: 50px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <h4 class="fw-bold mb-1" style="color: var(--ramadhan-gold);">{{ $user->name }}</h4>
                    <p class="text-muted-ramadhan small mb-4">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                    <div class="badge badge-gold px-4 py-2 mb-4 w-100 shadow-sm">
                        <i class="mdi mdi-moon-waning-crescent me-1"></i> RAMADHAN KAREEM 1447 H
                    </div>

                    <div class="mb-5">
                        <button type="button" class="btn btn-gold-action btn-sm w-100" data-bs-toggle="modal"
                            data-bs-target="#changeProfilePhotoModal">
                            <i class="mdi mdi-camera-plus"></i> GANTI FOTO PROFIL
                        </button>
                    </div>

                    <div class="text-start mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--ramadhan-gold);">MENU & AKTIVITAS
                        </h6>
                        <div class="list-group list-group-flush">
                            <a href="{{ route('attendance.history') }}"
                                class="list-group-item list-group-item-ramadhan d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-history me-2"></i> History Absensi</span>
                                <i class="mdi mdi-chevron-right"></i>
                            </a>
                            <a href="{{ route('inventory.index') }}"
                                class="list-group-item list-group-item-ramadhan d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-archive-outline me-2"></i> Inventaris Tim</span>
                                <i class="mdi mdi-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    {{-- PENGHARGAAN --}}
                    @if(isset($achievements) && $achievements->count() > 0)
                        <div class="text-start">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--ramadhan-gold);">PENCAPAIAN TERBAIK
                            </h6>
                            @foreach($achievements as $year => $items)
                                @foreach($items as $award)
                                    <div style="background: rgba(255,255,255,0.1); border-radius: 10px; border-left: 4px solid var(--ramadhan-gold);"
                                        class="p-3 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-trophy text-warning me-3" style="font-size: 24px;"></i>
                                            <div>
                                                <p class="mb-0 fw-bold small">Juara {{ $award->rank }}
                                                    ({{ \Carbon\Carbon::create()->month($award->month)->translatedFormat('F') }})</p>
                                                <p class="mb-0" style="font-size: 10px; color: var(--ramadhan-light-gold);">
                                                    {{ $award->total_attendance }} Kehadiran Tepat Waktu</p>
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

        {{-- KOLOM KANAN --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card ramadhan-card">
                <div class="card-body">
                    <div class="section-header-ramadhan d-flex align-items-center">
                        <i class="mdi mdi-cog-outline me-2" style="font-size: 22px;"></i>
                        <span class="mb-0">PENGATURAN PROFIL RAMADHAN</span>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 style="color: var(--ramadhan-gold); letter-spacing: 1px;"
                                    class="fw-bold small text-uppercase">
                                    <i class="mdi mdi-star-circle me-1"></i> Data Identitas
                                </h6>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold opacity-75">Nama Sesuai KTP</label>
                                <input type="text" class="form-control ramadhan-input" value="{{ $user->name }}" readonly>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold opacity-75">Hari Kelahiran</label>
                                <input type="text" class="form-control ramadhan-input"
                                    value="{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d F Y') : '-' }}"
                                    readonly>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="small fw-bold opacity-75">Email Login Utama</label>
                                <input type="email" class="form-control ramadhan-input" name="email"
                                    value="{{ old('email', $user->email) }}">
                            </div>

                            <div class="col-12 mb-3 mt-2">
                                <h6 style="color: var(--ramadhan-gold); letter-spacing: 1px;"
                                    class="fw-bold small text-uppercase">
                                    <i class="mdi mdi-briefcase-check me-1"></i> Karir & Pekerjaan
                                </h6>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold opacity-75">Cabang Penempatan</label>
                                <input type="text" class="form-control ramadhan-input"
                                    value="{{ $user->branch->name ?? 'PStore Big Jakarta' }}" readonly>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold opacity-75">Divisi / Posisi</label>
                                <input type="text" class="form-control ramadhan-input"
                                    value="{{ $user->division->name ?? 'Team IT' }}" readonly>
                            </div>

                            <div class="col-12 mb-3 mt-2">
                                <h6 style="color: var(--ramadhan-gold); letter-spacing: 1px;"
                                    class="fw-bold small text-uppercase">
                                    <i class="mdi mdi-cellphone-link me-1"></i> Kontak Sosial
                                </h6>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold opacity-75">WhatsApp Aktif</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-warning text-warning"><i
                                            class="mdi mdi-whatsapp"></i></span>
                                    <input type="text" class="form-control ramadhan-input" name="whatsapp"
                                        value="{{ old('whatsapp', $user->whatsapp) }}">
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="small fw-bold opacity-75">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-warning text-warning"><i
                                            class="mdi mdi-instagram"></i></span>
                                    <input type="text" class="form-control ramadhan-input" name="instagram"
                                        value="{{ old('instagram', $user->instagram) }}">
                                </div>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <a href="{{ route('dashboard') }}"
                                    class="btn btn-outline-warning px-4 me-2 btn-sm">KEMBALI</a>
                                <button type="submit" class="btn btn-gold-action px-5 btn-sm shadow">SIMPAN
                                    PERUBAHAN</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL GANTI FOTO --}}
    <div class="modal fade" id="changeProfilePhotoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: var(--ramadhan-primary); border: 2px solid var(--ramadhan-gold); color: white;">
                <div class="modal-header border-bottom border-warning">
                    <h5 class="modal-title fw-bold" style="color: var(--ramadhan-gold);">PENGAJUAN FOTO PROFIL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.photo.request') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="small opacity-75">Silakan pilih foto terbaik Anda. Foto akan melewati proses verifikasi
                            oleh Admin.</p>
                        <input type="file" name="profile_photo" class="form-control ramadhan-input" required
                            accept="image/*">
                    </div>
                    <div class="modal-footer border-top border-warning">
                        <button type="submit" class="btn btn-gold-action w-100">KIRIM PENGAJUAN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection