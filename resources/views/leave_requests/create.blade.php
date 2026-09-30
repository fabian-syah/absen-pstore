@extends('layout.master')

@section('title')
    Ajukan Izin / Cuti / WFH
@endsection

@section('heading')
    Form Pengajuan Izin / Cuti / WFH
@endsection

@push('styles')
    <style>
        /* ==========================================================
           MODERN AESTHETIC FORM PENGAJUAN DESIGN SYSTEM (HIGH CONTRAST)
           - No dull grey backgrounds (fresh, vibrant, clean surface)
           - Sharp, high-contrast typography (anti-nyaru)
           - Spacious layout with top breathing room (anti-mepet)
           - 100% responsive across mobile, tablet, and desktop
           ========================================================== */

        .form-page-wrapper {
            padding-top: 1rem;
            padding-bottom: 3.5rem;
        }

        /* --- TOP PAGE HEADER BLOCK --- */
        .form-top-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 4px 16px -4px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-circle-back {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05);
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-circle-back:hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #1d4ed8;
            transform: translateX(-2px);
        }

        .form-top-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .form-top-subtitle {
            font-size: 0.875rem;
            color: #475569;
            margin: 0;
            margin-top: 2px;
        }

        .btn-history-link {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 9999px;
            padding: 0.5rem 1.15rem;
            font-size: 0.875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
        }

        .btn-history-link:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        /* --- MAIN FORM CARD --- */
        .leave-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.06), 0 4px 10px -2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .leave-form-card .card-body {
            padding: 2.25rem 2rem;
        }

        @media (max-width: 767.98px) {
            .form-page-wrapper {
                padding-top: 0.5rem;
                padding-bottom: 4rem;
            }
            .form-top-bar {
                padding: 1rem 1.15rem;
                border-radius: 16px;
            }
            .leave-form-card {
                border-radius: 18px;
            }
            .leave-form-card .card-body {
                padding: 1.35rem 1rem;
            }
            .form-top-title {
                font-size: 1.1rem;
            }
        }

        /* --- SECTION STEP HEADERS --- */
        .form-section-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.25rem;
            margin-top: 0.25rem;
        }

        .form-section-step {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }

        .form-section-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .form-section-subtitle {
            font-size: 0.8125rem;
            color: #475569;
            margin: 0;
            font-weight: 500;
        }

        /* --- NOTICES --- */
        .pending-alert-card {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-left: 5px solid #d97706;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.75rem;
        }

        .pending-chip {
            background: #ffffff;
            border: 1px solid #fcd34d;
            border-radius: 9999px;
            padding: 5px 12px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #78350f;
            font-weight: 600;
        }

        .pending-chip-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #d97706;
        }

        .validation-error-card {
            background: #fff1f2;
            border: 1.5px solid #fecdd3;
            border-left: 5px solid #e11d48;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.75rem;
        }

        /* --- TYPE SELECTION CARDS GRID --- */
        .type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
            margin-bottom: 1rem;
        }

        @media (max-width: 575.98px) {
            .type-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 9px;
            }
        }

        .type-card-btn {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 16px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 102px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .type-card-btn:hover {
            border-color: #94a3b8;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        }

        .type-card-btn.active {
            border-color: #2563eb;
            border-width: 2px;
            background: #f0f7ff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18), 0 6px 16px rgba(37, 99, 235, 0.12);
        }

        .type-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .type-card-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .type-card-desc {
            font-size: 11px;
            color: #475569;
            font-weight: 600;
            line-height: 1.15;
        }

        /* Specific Type Color Accents */
        .type-card-btn[data-type="izin"] .type-card-icon { background: #dbeafe; color: #1d4ed8; }
        .type-card-btn[data-type="sakit"] .type-card-icon { background: #ffe4e6; color: #be123c; }
        .type-card-btn[data-type="wfh"] .type-card-icon { background: #e0f2fe; color: #0369a1; }
        .type-card-btn[data-type="dinas"] .type-card-icon { background: #fef3c7; color: #b45309; }
        .type-card-btn[data-type="telat"] .type-card-icon { background: #ede9fe; color: #6d28d9; }
        .type-card-btn[data-type="cuti"] .type-card-icon { background: #d1fae5; color: #047857; }
        .type-card-btn[data-type="libur"] .type-card-icon { background: #ccfbf1; color: #0f766e; }

        .type-card-btn.active .type-card-title {
            color: #1d4ed8;
        }

        .type-check-badge {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            font-size: 12px;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3);
        }

        .type-card-btn.active .type-check-badge {
            display: flex;
        }

        /* --- CUTI BENTO BALANCE CARD --- */
        .leave-balance-bento {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 2px solid #6ee7b7;
            border-radius: 18px;
            padding: 1.25rem 1.5rem;
            animation: fadeIn 0.25s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bento-balance-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #ffffff;
            color: #047857;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 2px 8px rgba(4, 120, 87, 0.2);
            flex-shrink: 0;
        }

        .bento-balance-number {
            font-size: 28px;
            font-weight: 900;
            color: #064e3b;
            line-height: 1;
        }

        /* --- HIGH-CONTRAST FORM CONTROLS --- */
        .form-label-custom {
            font-size: 0.9375rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-control-custom {
            border: 1.5px solid #cbd5e1;
            border-radius: 14px;
            padding: 0.8rem 1rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #0f172a;
            background-color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            width: 100%;
        }

        .form-control-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.18);
            outline: none;
            background-color: #ffffff;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #2563eb;
            font-size: 20px;
            pointer-events: none;
            z-index: 4;
        }

        .form-control-custom.has-icon-left {
            padding-left: 46px;
        }

        .form-helper-text {
            font-size: 0.8125rem;
            color: #475569;
            font-weight: 500;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* --- FRESH DROPZONE (NOT DULL GREY) --- */
        .upload-dropzone {
            border: 2px dashed #93c5fd;
            border-radius: 18px;
            background: #f8fbff;
            padding: 2rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .upload-dropzone:hover,
        .upload-dropzone.dragover {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.08);
        }

        .upload-icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #ffffff;
            color: #2563eb;
            font-size: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
            transition: transform 0.2s ease;
        }

        .upload-dropzone:hover .upload-icon-circle {
            transform: scale(1.08);
        }

        .upload-main-text {
            font-weight: 800;
            color: #0f172a;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .upload-sub-text {
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 0;
        }

        /* Live Preview Box */
        .upload-preview-box {
            display: none;
            background: #ffffff;
            border: 1.5px solid #bfdbfe;
            border-radius: 16px;
            padding: 12px 16px;
            margin-top: 12px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .preview-file-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #2563eb;
            overflow: hidden;
            flex-shrink: 0;
        }

        .preview-file-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-file-info {
            flex-grow: 1;
            min-width: 0;
            text-align: left;
        }

        .preview-file-name {
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .preview-file-size {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .btn-remove-preview {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #e11d48;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-remove-preview:hover {
            background: #e11d48;
            color: #ffffff;
        }

        /* --- ACTION BUTTONS BAR --- */
        .form-actions-bar {
            margin-top: 2.25rem;
            padding-top: 1.5rem;
            border-top: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-cancel-custom {
            padding: 0.85rem 1.65rem;
            border-radius: 14px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .btn-cancel-custom:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-submit-custom {
            padding: 0.85rem 2rem;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.95rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-submit-custom:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.5);
            transform: translateY(-1px);
            color: #ffffff;
        }

        @media (max-width: 575.98px) {
            .form-actions-bar {
                flex-direction: column-reverse;
                gap: 12px;
            }
            .btn-cancel-custom,
            .btn-submit-custom {
                width: 100%;
                justify-content: center;
                padding: 0.9rem 1.5rem;
            }
        }

        .char-counter {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
        }
        .char-counter.warning {
            color: #e11d48;
        }

        .duration-pill {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1.5px solid #bfdbfe;
            border-radius: 9999px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.08);
        }

        .type-error-feedback {
            display: none;
            color: #e11d48;
            font-size: 12.5px;
            font-weight: 700;
            margin-top: 6px;
        }
    </style>
@endpush

@section('content')
    <div class="form-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">

                {{-- 1. Top Page Navigation Bar --}}
                <div class="form-top-bar">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="btn-circle-back" title="Kembali ke Dashboard">
                            <i class="mdi mdi-arrow-left"></i>
                        </a>
                        <div>
                            <h5 class="form-top-title">Formulir Pengajuan Izin / Cuti / WFH</h5>
                            <p class="form-top-subtitle">Silakan pilih jenis perizinan dan lengkapi data di bawah ini.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('leave-requests.personal-history') }}" class="btn-history-link">
                            <i class="mdi mdi-history fs-6"></i>
                            <span>Riwayat Saya</span>
                        </a>
                    </div>
                </div>

                {{-- 2. Pending Leaves Warning Notification --}}
                @if(isset($pendingLeaves) && $pendingLeaves->count() > 0)
                    <div class="pending-alert-card" role="alert">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-clock-alert-outline fs-5 text-amber-600"></i>
                                <h6 class="fw-bold mb-0 text-slate-900">Menunggu Verifikasi Audit</h6>
                            </div>
                            <span class="badge bg-amber-100 text-amber-900 border border-amber-300 rounded-pill px-2.5 py-1 fw-bold">
                                {{ $pendingLeaves->count() }} Pengajuan Aktif
                            </span>
                        </div>
                        <p class="mb-2 text-slate-800 small font-medium">
                            Anda saat ini memiliki <strong>{{ $pendingLeaves->count() }} pengajuan</strong> yang masih dalam antrean review oleh tim audit:
                        </p>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($pendingLeaves as $pending)
                                <div class="pending-chip">
                                    <span class="pending-chip-dot"></span>
                                    <strong class="text-capitalize">{{ $pending->type }}</strong>
                                    <span>({{ \Carbon\Carbon::parse($pending->start_date)->format('d M Y') }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 3. Validation Errors Notice --}}
                @if ($errors->any())
                    <div class="validation-error-card" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <div class="text-rose-600 fs-4 mt-n1">
                                <i class="mdi mdi-alert-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-rose-900 mb-1">Pengajuan Belum Dapat Dikirim</h6>
                                <ul class="mb-0 text-rose-800 small ps-3 fw-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 4. Main Form Card --}}
                <div class="leave-form-card">
                    <div class="card-body">
                        <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data" id="leaveForm">
                            @csrf

                            {{-- Hidden Type Input that syncs with Card Clicks --}}
                            <input type="hidden" name="type" id="type" value="{{ old('type', '') }}">

                            {{-- ======================================================== --}}
                            {{-- SECTION 1: PILIH JENIS PENGAJUAN --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-2">
                                <div class="form-section-head">
                                    <div class="form-section-step">1</div>
                                    <div>
                                        <h6 class="form-section-title">Jenis Pengajuan <span class="text-danger">*</span></h6>
                                        <p class="form-section-subtitle">Klik salah satu kategori di bawah ini</p>
                                    </div>
                                </div>

                                {{-- Visual Type Selection Cards --}}
                                <div class="type-grid" id="typeGrid">
                                    <div class="type-card-btn {{ old('type') == 'izin' ? 'active' : '' }}" data-type="izin" onclick="selectType('izin')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-account-clock-outline"></i></div>
                                        <div class="type-card-title">Izin Pribadi</div>
                                        <div class="type-card-desc">Keperluan Pribadi</div>
                                    </div>

                                    <div class="type-card-btn {{ old('type') == 'sakit' ? 'active' : '' }}" data-type="sakit" onclick="selectType('sakit')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-hospital-box-outline"></i></div>
                                        <div class="type-card-title">Sakit</div>
                                        <div class="type-card-desc">Kondisi Sakit</div>
                                    </div>

                                    <div class="type-card-btn {{ old('type') == 'wfh' ? 'active' : '' }}" data-type="wfh" onclick="selectType('wfh')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-laptop-account"></i></div>
                                        <div class="type-card-title">WFH</div>
                                        <div class="type-card-desc">Work From Home</div>
                                    </div>

                                    <div class="type-card-btn {{ old('type') == 'dinas' ? 'active' : '' }}" data-type="dinas" onclick="selectType('dinas')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-briefcase-check-outline"></i></div>
                                        <div class="type-card-title">Dinas Luar</div>
                                        <div class="type-card-desc">Tugas Luar Kantor</div>
                                    </div>

                                    <div class="type-card-btn {{ old('type') == 'telat' ? 'active' : '' }}" data-type="telat" onclick="selectType('telat')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-clock-alert-outline"></i></div>
                                        <div class="type-card-title">Izin Terlambat</div>
                                        <div class="type-card-desc">Kendala / Macet</div>
                                    </div>

                                    <div class="type-card-btn {{ old('type') == 'cuti' ? 'active' : '' }}" data-type="cuti" onclick="selectType('cuti')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-palm-tree"></i></div>
                                        <div class="type-card-title">Cuti Tahunan</div>
                                        <div class="type-card-desc">Cuti Karyawan</div>
                                    </div>

                                    <div class="type-card-btn {{ old('type') == 'libur' ? 'active' : '' }}" data-type="libur" onclick="selectType('libur')">
                                        <span class="type-check-badge"><i class="mdi mdi-check"></i></span>
                                        <div class="type-card-icon"><i class="mdi mdi-calendar-weekend-outline"></i></div>
                                        <div class="type-card-title">Izin Libur</div>
                                        <div class="type-card-desc">Libur Pengganti</div>
                                    </div>
                                </div>
                                <div class="type-error-feedback" id="typeErrorMsg">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i>Harap pilih salah satu jenis pengajuan di atas.
                                </div>
                            </div>

                            {{-- BENTO CARD SALDO CUTI (Muncul Dinamis Saat Cuti Dipilih) --}}
                            <div id="leave_balance_info" class="leave-balance-bento d-none mb-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bento-balance-icon">
                                            <i class="mdi mdi-palm-tree"></i>
                                        </div>
                                        <div>
                                            <span class="text-uppercase fw-bold text-emerald-900" style="font-size: 11px; letter-spacing: 0.05em;">Informasi Kuota Cuti</span>
                                            <div class="d-flex align-items-baseline gap-2 mt-0.5">
                                                <span class="bento-balance-number">{{ auth()->user()->leave_balance ?? 0 }}</span>
                                                <span class="fw-bold text-emerald-950" style="font-size: 15px;">Hari Tersisa</span>
                                                <span class="text-emerald-800 small fw-semibold">(Limit: {{ auth()->user()->yearly_leave_limit ?? 12 }} Hari / Tahun)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="badge bg-white text-emerald-900 border border-emerald-400 rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center fw-bold">
                                        <i class="mdi mdi-information-outline me-1.5 text-emerald-700 fs-6"></i>
                                        <span style="font-size: 12px;">Pengajuan melebihi kuota akan diproses via penyesuaian payroll.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- SECTION 2: WAKTU & PERIODE PENGAJUAN --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-2">
                                <div class="form-section-head">
                                    <div class="form-section-step">2</div>
                                    <div class="d-flex align-items-center justify-content-between flex-grow-1 flex-wrap gap-2">
                                        <div>
                                            <h6 class="form-section-title">Waktu & Periode <span class="text-danger">*</span></h6>
                                            <p class="form-section-subtitle">Tentukan tanggal atau waktu pelaksanaan perizinan</p>
                                        </div>
                                        <div id="duration_indicator" class="duration-pill d-none">
                                            <i class="mdi mdi-calendar-range"></i>
                                            <span id="duration_text">1 Hari</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    {{-- Tanggal Mulai --}}
                                    <div class="col-12 col-md-6" id="start_date_box">
                                        <label id="label_start_date" for="start_date" class="form-label-custom">
                                            <i class="mdi mdi-calendar text-primary"></i>
                                            <span>Tanggal Mulai</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="mdi mdi-calendar-today input-icon-left"></i>
                                            <input type="date" name="start_date" id="start_date" class="form-control-custom has-icon-left"
                                                value="{{ old('start_date', date('Y-m-d')) }}" required onchange="calculateDuration()">
                                        </div>
                                        <div class="form-helper-text" id="start_date_helper">
                                            <i class="mdi mdi-information-outline text-primary"></i>
                                            <span>Tanggal awal Anda mengajukan perizinan.</span>
                                        </div>
                                    </div>

                                    {{-- Input Tanggal Selesai (Untuk WFH, Izin, Sakit, Cuti, dsb) --}}
                                    <div class="col-12 col-md-6" id="end_date_box">
                                        <label for="end_date" class="form-label-custom">
                                            <i class="mdi mdi-calendar-check text-primary"></i>
                                            <span>Sampai Tanggal</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="mdi mdi-calendar-month input-icon-left"></i>
                                            <input type="date" name="end_date" id="end_date" class="form-control-custom has-icon-left"
                                                value="{{ old('end_date') }}" onchange="calculateDuration()">
                                        </div>
                                        <div class="form-helper-text">
                                            <i class="mdi mdi-information-outline text-primary"></i>
                                            <span>Pilih tanggal yang sama jika hanya 1 hari kerja.</span>
                                        </div>
                                    </div>

                                    {{-- Input Jam (Khusus Izin Terlambat) --}}
                                    <div class="col-12 col-md-6 d-none" id="time_box">
                                        <label for="start_time" class="form-label-custom">
                                            <i class="mdi mdi-clock-outline text-primary"></i>
                                            <span>Perkiraan Jam Sampai di Kantor</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="mdi mdi-clock-time-four-outline input-icon-left"></i>
                                            <input type="time" name="start_time" id="start_time" class="form-control-custom has-icon-left"
                                                value="{{ old('start_time') }}">
                                        </div>
                                        <div class="form-helper-text">
                                            <i class="mdi mdi-information-outline text-primary"></i>
                                            <span>Contoh: 09:30 (Format 24 Jam WIB).</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- SECTION 3: ALASAN & KETERANGAN --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-2">
                                <div class="form-section-head">
                                    <div class="form-section-step">3</div>
                                    <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                        <div>
                                            <h6 class="form-section-title">Alasan / Keterangan <span class="text-danger">*</span></h6>
                                            <p class="form-section-subtitle">Berikan penjelasan ringkas dan jelas mengenai permohonan Anda</p>
                                        </div>
                                        <span id="charCount" class="char-counter">0 / 255</span>
                                    </div>
                                </div>

                                <textarea name="reason" id="reason" class="form-control-custom" rows="3" required maxlength="255"
                                    placeholder="Jelaskan alasan atau kronologi keperluan perizinan Anda secara jelas..."
                                    oninput="updateCharCount(this)">{{ old('reason') }}</textarea>
                                <div class="form-helper-text">
                                    <i class="mdi mdi-shield-check text-primary"></i>
                                    <span>Informasi ini akan ditinjau langsung oleh pimpinan cabang dan tim audit.</span>
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- SECTION 4: BUKTI FOTO / DOKUMEN --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-1">
                                <div class="form-section-head">
                                    <div class="form-section-step">4</div>
                                    <div>
                                        <h6 class="form-section-title">Bukti Foto / Dokumen <span class="text-danger">*</span></h6>
                                        <p class="form-section-subtitle">Lampirkan surat dokter, tiket, foto kegiatan dinas, atau kendala</p>
                                    </div>
                                </div>

                                {{-- Hidden native file input --}}
                                <input type="file" name="file_proof" id="file_proof" class="d-none" accept="image/jpeg,image/png,image/webp,application/pdf" required onchange="handleFileSelected(this)">

                                {{-- Aesthetic Dropzone --}}
                                <div class="upload-dropzone" id="dropzoneBox" onclick="document.getElementById('file_proof').click()">
                                    <div class="upload-icon-circle">
                                        <i class="mdi mdi-cloud-upload-outline"></i>
                                    </div>
                                    <div class="upload-main-text">Klik di sini untuk memilih file dokumen / foto</div>
                                    <p class="upload-sub-text">Mendukung format JPG, PNG, WEBP, atau PDF (Maksimal 10 MB)</p>
                                </div>

                                {{-- Selected File Live Preview Box --}}
                                <div class="upload-preview-box" id="previewBox">
                                    <div class="preview-file-icon" id="previewIcon">
                                        <i class="mdi mdi-file-document-outline"></i>
                                    </div>
                                    <div class="preview-file-info">
                                        <div class="preview-file-name" id="previewName">-</div>
                                        <div class="preview-file-size" id="previewSize">-</div>
                                    </div>
                                    <button type="button" class="btn-remove-preview" title="Hapus File" onclick="clearSelectedFile(event)">
                                        <i class="mdi mdi-trash-can-outline fs-5"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- ACTION BUTTONS --}}
                            {{-- ======================================================== --}}
                            <div class="form-actions-bar">
                                <a href="{{ route('dashboard') }}" class="btn-cancel-custom">
                                    <i class="mdi mdi-arrow-left"></i>
                                    <span>Batal & Kembali</span>
                                </a>
                                <button type="submit" class="btn-submit-custom" id="submitBtn">
                                    <i class="mdi mdi-send"></i>
                                    <span>Kirim Pengajuan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Interactive Form Logic --}}
    <script>
        // 1. Sinkronisasi Type Selection Cards & Hidden Input
        function selectType(typeKey) {
            let hiddenInput = document.getElementById('type');
            hiddenInput.value = typeKey;
            
            document.getElementById('typeErrorMsg').style.display = 'none';
            syncActiveTypeCards(typeKey);
            toggleInputs();
        }

        function syncActiveTypeCards(activeType) {
            let buttons = document.querySelectorAll('.type-card-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('data-type') === activeType) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        // 2. Dynamic Input Toggle based on Type
        function toggleInputs() {
            let type = document.getElementById('type').value;
            let endDateBox = document.getElementById('end_date_box');
            let timeBox = document.getElementById('time_box');
            let labelDate = document.getElementById('label_start_date');
            let startDateHelper = document.getElementById('start_date_helper');
            let leaveInfo = document.getElementById('leave_balance_info');
            let timeInput = document.getElementById('start_time');
            let durationIndicator = document.getElementById('duration_indicator');

            if (type === 'telat') {
                endDateBox.classList.add('d-none');
                timeBox.classList.remove('d-none');
                timeInput.setAttribute('required', 'required');
                
                labelDate.querySelector('span').innerText = "Tanggal Hari Ini";
                startDateHelper.innerHTML = '<i class="mdi mdi-clock-alert-outline text-purple"></i><span>Izin terlambat hanya berlaku untuk hari ini.</span>';
                durationIndicator.classList.add('d-none');
            } else {
                endDateBox.classList.remove('d-none');
                timeBox.classList.add('d-none');
                timeInput.removeAttribute('required');
                
                labelDate.querySelector('span').innerText = "Tanggal Mulai";
                startDateHelper.innerHTML = '<i class="mdi mdi-information-outline text-primary"></i><span>Tanggal awal Anda mengajukan perizinan.</span>';
                durationIndicator.classList.remove('d-none');
                calculateDuration();
            }

            if (type === 'cuti') {
                leaveInfo.classList.remove('d-none');
            } else {
                leaveInfo.classList.add('d-none');
            }
        }

        // 3. Hitung Durasi Hari Otomatis
        function calculateDuration() {
            let type = document.getElementById('type').value;
            let indicator = document.getElementById('duration_indicator');
            let text = document.getElementById('duration_text');
            let start = document.getElementById('start_date').value;
            let end = document.getElementById('end_date').value;

            if (type === 'telat' || !start) {
                indicator.classList.add('d-none');
                return;
            }

            if (!end) {
                indicator.classList.remove('d-none');
                text.innerText = "1 Hari";
                return;
            }

            let startDate = new Date(start);
            let endDate = new Date(end);

            if (endDate >= startDate) {
                let diffTime = Math.abs(endDate - startDate);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                indicator.classList.remove('d-none');
                text.innerText = diffDays + " Hari";
            } else {
                indicator.classList.add('d-none');
            }
        }

        // 4. Character Counter for Reason
        function updateCharCount(el) {
            let current = el.value.length;
            let counter = document.getElementById('charCount');
            counter.innerText = current + " / 255";
            if (current >= 240) {
                counter.classList.add('warning');
            } else {
                counter.classList.remove('warning');
            }
        }

        // 5. File Upload & Live Preview
        function handleFileSelected(input) {
            if (input.files && input.files[0]) {
                let file = input.files[0];

                if (file.size > 10485760) {
                    alert('Ukuran file melebihi batas 10 MB! Silakan pilih file yang lebih kecil.');
                    input.value = '';
                    clearSelectedFile();
                    return;
                }

                let previewBox = document.getElementById('previewBox');
                let dropzoneBox = document.getElementById('dropzoneBox');
                let previewName = document.getElementById('previewName');
                let previewSize = document.getElementById('previewSize');
                let previewIcon = document.getElementById('previewIcon');

                previewName.innerText = file.name;
                previewSize.innerText = formatFileSize(file.size);

                if (file.type.startsWith('image/')) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        previewIcon.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    previewIcon.innerHTML = `<i class="mdi mdi-file-pdf-box text-danger" style="font-size: 32px;"></i>`;
                } else {
                    previewIcon.innerHTML = `<i class="mdi mdi-file-document-outline text-primary" style="font-size: 28px;"></i>`;
                }

                previewBox.style.display = 'flex';
                dropzoneBox.style.display = 'none';
            }
        }

        function clearSelectedFile(e) {
            if (e) e.stopPropagation();
            let input = document.getElementById('file_proof');
            input.value = '';
            document.getElementById('previewBox').style.display = 'none';
            document.getElementById('dropzoneBox').style.display = 'block';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            let k = 1024;
            let sizes = ['Bytes', 'KB', 'MB'];
            let i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Drag and Drop
        let dropzone = document.getElementById('dropzoneBox');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', function(e) {
                let dt = e.dataTransfer;
                let files = dt.files;
                if (files.length > 0) {
                    let input = document.getElementById('file_proof');
                    input.files = files;
                    handleFileSelected(input);
                }
            }, false);
        }

        // Form Validation & Submit handling
        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            let type = document.getElementById('type').value;
            if (!type) {
                e.preventDefault();
                let errorMsg = document.getElementById('typeErrorMsg');
                errorMsg.style.display = 'block';
                document.getElementById('typeGrid').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            let submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim Pengajuan...';
        });

        // Initialize state on DOM ready
        document.addEventListener('DOMContentLoaded', function () {
            let currentType = document.getElementById('type').value;
            if (currentType) {
                syncActiveTypeCards(currentType);
            }
            toggleInputs();
            let reasonInput = document.getElementById('reason');
            if (reasonInput) updateCharCount(reasonInput);
        });
    </script>
@endsection