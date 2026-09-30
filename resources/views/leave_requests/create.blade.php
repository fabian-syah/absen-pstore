@extends('layout.master')

@section('title')
    Ajukan Izin / Cuti / WFH
@endsection

@section('heading')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light btn-icon rounded-circle shadow-sm" title="Kembali ke Dashboard">
                <i class="mdi mdi-arrow-left text-primary"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-bold text-slate-900">Form Pengajuan Izin / Cuti / WFH</h4>
                <div class="header-page-meta d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light text-slate-600 border border-slate-200 rounded-pill px-2.5 py-1">
                        <i class="mdi mdi-calendar-clock me-1 text-primary"></i>Perizinan Kerja
                    </span>
                    <a href="{{ route('leave-requests.personal-history') }}" class="text-decoration-none text-primary fw-semibold small d-inline-flex align-items-center">
                        <i class="mdi mdi-history me-1"></i>Riwayat Saya &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* ==========================================================
           MODERN AESTHETIC FORM PENGAJUAN DESIGN SYSTEM
           - Spacious layout (no dempet-dempet)
           - Elegant white surface with crisp slate-200 borders
           - Interactive Type Selection Cards (1-tap on mobile)
           - Dynamic Cuti Bento Balance Widget
           - Aesthetic Drag & Drop File Upload with live preview
           - Fully responsive across all devices
           ========================================================== */

        :root {
            --form-slate-50: #f8fafc;
            --form-slate-100: #f1f5f9;
            --form-slate-200: #e2e8f0;
            --form-slate-300: #cbd5e1;
            --form-slate-400: #94a3b8;
            --form-slate-500: #64748b;
            --form-slate-600: #475569;
            --form-slate-700: #334155;
            --form-slate-800: #1e293b;
            --form-slate-900: #0f172a;
            --form-primary: #2563eb;
            --form-primary-hover: #1d4ed8;
            --form-primary-light: #eff6ff;
            --form-emerald: #059669;
            --form-emerald-light: #ecfdf5;
            --form-rose: #e11d48;
            --form-rose-light: #fff1f2;
            --form-amber: #d97706;
            --form-amber-light: #fffbeb;
            --form-purple: #7c3aed;
            --form-purple-light: #f5f3ff;
            --form-teal: #0d9488;
            --form-teal-light: #f0fdfa;
        }

        /* --- HERO INTRO BAR --- */
        .form-hero-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--form-slate-200);
            border-radius: 20px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .form-hero-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--form-primary-light);
            color: var(--form-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
        }

        /* --- MAIN FORM CARD --- */
        .leave-form-card {
            background: #ffffff;
            border: 1px solid var(--form-slate-200);
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 4px 10px -2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
            margin-bottom: 3rem;
            transition: all 0.25s ease;
        }

        .leave-form-card .card-body {
            padding: 2.25rem;
        }

        @media (max-width: 767.98px) {
            .leave-form-card .card-body {
                padding: 1.25rem 1rem;
            }
            .form-hero-card {
                padding: 1.15rem 1.25rem;
            }
        }

        /* --- SECTION DIVIDERS --- */
        .form-section-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.25rem;
            margin-top: 0.5rem;
        }

        .form-section-step {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--form-primary-light);
            color: var(--form-primary);
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--form-slate-800);
            margin: 0;
            letter-spacing: -0.01em;
        }

        .form-section-subtitle {
            font-size: 0.8125rem;
            color: var(--form-slate-500);
            margin: 0;
        }

        /* --- PENDING ALERTS & NOTICES --- */
        .pending-alert-card {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 5px solid #f59e0b;
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
            color: #92400e;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .pending-chip-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #f59e0b;
        }

        .validation-error-card {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 5px solid #e11d48;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.75rem;
        }

        /* --- TYPE SELECTION CARDS GRID --- */
        .type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 10px;
            margin-bottom: 1rem;
        }

        @media (max-width: 575.98px) {
            .type-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
        }

        .type-card-btn {
            background: #ffffff;
            border: 1.5px solid var(--form-slate-200);
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
            min-height: 98px;
        }

        .type-card-btn:hover {
            border-color: var(--form-slate-400);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .type-card-btn.active {
            border-color: var(--form-primary);
            background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 100%);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15), 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .type-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .type-card-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--form-slate-800);
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .type-card-desc {
            font-size: 10.5px;
            color: var(--form-slate-500);
            line-height: 1.15;
        }

        /* Specific Type Color Themes */
        .type-card-btn[data-type="izin"] .type-card-icon { background: #eff6ff; color: #2563eb; }
        .type-card-btn[data-type="sakit"] .type-card-icon { background: #fff1f2; color: #e11d48; }
        .type-card-btn[data-type="wfh"] .type-card-icon { background: #f0f9ff; color: #0284c7; }
        .type-card-btn[data-type="dinas"] .type-card-icon { background: #fffbeb; color: #d97706; }
        .type-card-btn[data-type="telat"] .type-card-icon { background: #f5f3ff; color: #7c3aed; }
        .type-card-btn[data-type="cuti"] .type-card-icon { background: #ecfdf5; color: #059669; }
        .type-card-btn[data-type="libur"] .type-card-icon { background: #f0fdfa; color: #0d9488; }

        .type-card-btn.active .type-card-title {
            color: var(--form-primary);
        }

        .type-check-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--form-primary);
            color: #ffffff;
            font-size: 11px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .type-card-btn.active .type-check-badge {
            display: flex;
        }

        /* --- CUTI BENTO BALANCE CARD --- */
        .leave-balance-bento {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1.5px solid #86efac;
            border-radius: 18px;
            padding: 1.25rem 1.5rem;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bento-balance-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #ffffff;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.15);
        }

        .bento-balance-number {
            font-size: 26px;
            font-weight: 800;
            color: #065f46;
            line-height: 1;
        }

        /* --- MODERN FORM CONTROLS --- */
        .form-label-custom {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--form-slate-700);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1.5px solid var(--form-slate-300);
            border-radius: 14px;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            color: var(--form-slate-800);
            background-color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            width: 100%;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--form-primary);
            box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.15);
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
            color: var(--form-slate-400);
            font-size: 19px;
            pointer-events: none;
            z-index: 4;
        }

        .form-control-custom.has-icon-left {
            padding-left: 44px;
        }

        .form-helper-text {
            font-size: 0.8125rem;
            color: var(--form-slate-500);
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* --- DRAG & DROP UPLOAD ZONE --- */
        .upload-dropzone {
            border: 2px dashed var(--form-slate-300);
            border-radius: 18px;
            background: var(--form-slate-50);
            padding: 1.75rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .upload-dropzone:hover,
        .upload-dropzone.dragover {
            border-color: var(--form-primary);
            background: var(--form-primary-light);
        }

        .upload-icon-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #ffffff;
            color: var(--form-primary);
            font-size: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease;
        }

        .upload-dropzone:hover .upload-icon-circle {
            transform: scale(1.08);
        }

        .upload-main-text {
            font-weight: 700;
            color: var(--form-slate-800);
            font-size: 0.9375rem;
            margin-bottom: 4px;
        }

        .upload-sub-text {
            color: var(--form-slate-500);
            font-size: 0.8125rem;
            margin-bottom: 0;
        }

        /* Live Preview Box */
        .upload-preview-box {
            display: none;
            background: #ffffff;
            border: 1px solid var(--form-slate-200);
            border-radius: 16px;
            padding: 12px 16px;
            margin-top: 12px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .preview-file-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--form-slate-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--form-primary);
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
            font-weight: 600;
            font-size: 13.5px;
            color: var(--form-slate-800);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .preview-file-size {
            font-size: 12px;
            color: var(--form-slate-500);
        }

        .btn-remove-preview {
            background: transparent;
            border: none;
            color: var(--form-rose);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-remove-preview:hover {
            background: var(--form-rose-light);
        }

        /* --- ACTION BUTTONS BAR --- */
        .form-actions-bar {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--form-slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-cancel-custom {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: 1.5px solid var(--form-slate-300);
            background: #ffffff;
            color: var(--form-slate-700);
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-cancel-custom:hover {
            background: var(--form-slate-100);
            color: var(--form-slate-900);
            border-color: var(--form-slate-400);
        }

        .btn-submit-custom {
            padding: 0.8rem 1.85rem;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--form-primary) 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.9375rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-submit-custom:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-submit-custom:active {
            transform: translateY(0);
        }

        @media (max-width: 575.98px) {
            .form-actions-bar {
                flex-direction: column-reverse;
                gap: 10px;
            }
            .btn-cancel-custom,
            .btn-submit-custom {
                width: 100%;
                justify-content: center;
            }
        }

        /* Modern Character Counter */
        .char-counter {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--form-slate-400);
            transition: color 0.2s ease;
        }
        .char-counter.warning {
            color: var(--form-rose);
        }

        /* Duration Pill */
        .duration-pill {
            background: var(--form-primary-light);
            color: var(--form-primary);
            border: 1px solid #bfdbfe;
            border-radius: 9999px;
            padding: 4px 10px;
            font-size: 11.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">

            {{-- 1. Hero Intro Card --}}
            <div class="form-hero-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-hero-icon">
                        <i class="mdi mdi-file-document-edit-outline"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-slate-900 mb-0">Formulir Perizinan Kerja</h5>
                        <p class="text-slate-500 mb-0 small">
                            Lengkapi rincian pengajuan izin, cuti, atau WFH Anda secara akurat.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('leave-requests.personal-history') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-slate-700 shadow-sm">
                        <i class="mdi mdi-history me-1 text-primary"></i>Riwayat Pengajuan
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
                        <span class="badge bg-amber-100 text-amber-900 border border-amber-300 rounded-pill px-2.5 py-1">
                            {{ $pendingLeaves->count() }} Pengajuan Aktif
                        </span>
                    </div>
                    <p class="mb-2 text-slate-700 small">
                        Anda saat ini memiliki <strong>{{ $pendingLeaves->count() }} pengajuan</strong> yang masih dalam antrean review oleh tim audit:
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($pendingLeaves as $pending)
                            <div class="pending-chip">
                                <span class="pending-chip-dot"></span>
                                <strong class="text-capitalize">{{ $pending->type }}</strong>
                                <span class="text-muted">({{ \Carbon\Carbon::parse($pending->start_date)->format('d M Y') }})</span>
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
                            <ul class="mb-0 text-rose-800 small ps-3">
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

                        {{-- ======================================================== --}}
                        {{-- SECTION 1: PILIH JENIS PENGAJUAN --}}
                        {{-- ======================================================== --}}
                        <div class="mb-4 pb-2">
                            <div class="form-section-head">
                                <div class="form-section-step">1</div>
                                <div>
                                    <h6 class="form-section-title">Jenis Pengajuan <span class="text-danger">*</span></h6>
                                    <p class="form-section-subtitle">Pilih kategori perizinan kerja yang ingin Anda ajukan</p>
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

                            {{-- Synchronized Hidden/Dropdown Select for 100% Form Compatibility --}}
                            <div class="mt-2">
                                <select name="type" id="type" class="form-select-custom" required onchange="onTypeSelectChange(this.value)">
                                    <option value="" disabled {{ old('type') ? '' : 'selected' }}>-- Atau pilih dari daftar opsi --</option>
                                    <option value="izin" {{ old('type') == 'izin' ? 'selected' : '' }}>Izin / Keperluan Pribadi</option>
                                    <option value="sakit" {{ old('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="wfh" {{ old('type') == 'wfh' ? 'selected' : '' }}>WFH (Work From Home)</option>
                                    <option value="dinas" {{ old('type') == 'dinas' ? 'selected' : '' }}>Dinas Luar</option>
                                    <option value="telat" {{ old('type') == 'telat' ? 'selected' : '' }}>Izin Terlambat</option>
                                    <option value="cuti" {{ old('type') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                    <option value="libur" {{ old('type') == 'libur' ? 'selected' : '' }}>Izin Libur</option>
                                </select>
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
                                        <span class="text-uppercase fw-bold text-emerald-800" style="font-size: 11px; letter-spacing: 0.05em;">Informasi Kuota Cuti</span>
                                        <div class="d-flex align-items-baseline gap-2 mt-0.5">
                                            <span class="bento-balance-number">{{ auth()->user()->leave_balance ?? 0 }}</span>
                                            <span class="fw-bold text-emerald-900" style="font-size: 14px;">Hari Tersisa</span>
                                            <span class="text-slate-500 small">(Limit: {{ auth()->user()->yearly_leave_limit ?? 12 }} Hari / Tahun)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="badge bg-white text-emerald-800 border border-emerald-300 rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center">
                                    <i class="mdi mdi-information-outline me-1.5 text-emerald-600 fs-6"></i>
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
                                        <i class="mdi mdi-information-outline"></i>
                                        <span>Tanggal awal Anda mengajukan izin.</span>
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
                                        <i class="mdi mdi-information-outline"></i>
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
                                        <i class="mdi mdi-information-outline"></i>
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
                                <i class="mdi mdi-information-outline"></i>
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
                                    <p class="form-section-subtitle">Lampirkan surat dokter, tiket, foto kegiatan dinas, atau foto kendala</p>
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

    {{-- Interactive Form Logic --}}
    <script>
        // 1. Sinkronisasi Type Selection Cards & Select Dropdown
        function selectType(typeKey) {
            let selectEl = document.getElementById('type');
            selectEl.value = typeKey;
            syncActiveTypeCards(typeKey);
            toggleInputs();
        }

        function onTypeSelectChange(value) {
            syncActiveTypeCards(value);
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
                // Tipe TELAT: Hanya 1 hari (hari ini), input waktu kedatangan wajib
                endDateBox.classList.add('d-none');
                timeBox.classList.remove('d-none');
                timeInput.setAttribute('required', 'required');
                
                labelDate.querySelector('span').innerText = "Tanggal Hari Ini";
                startDateHelper.innerHTML = '<i class="mdi mdi-clock-alert-outline text-purple"></i><span>Izin terlambat berlaku untuk hari ini.</span>';
                durationIndicator.classList.add('d-none');
            } else {
                // Tipe selain telat: Tanggal selesai aktif, jam disembunyikan
                endDateBox.classList.remove('d-none');
                timeBox.classList.add('d-none');
                timeInput.removeAttribute('required');
                
                labelDate.querySelector('span').innerText = "Tanggal Mulai";
                startDateHelper.innerHTML = '<i class="mdi mdi-information-outline"></i><span>Tanggal awal Anda mengajukan izin.</span>';
                durationIndicator.classList.remove('d-none');
                calculateDuration();
            }

            // Tampilkan Info Saldo Cuti jika memilih cuti
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

                // Validasi Maksimal 10MB (10485760 bytes)
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

                // Preview Image or Document Icon
                if (file.type.startsWith('image/')) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        previewIcon.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    previewIcon.innerHTML = `<i class="mdi mdi-file-pdf-box text-danger" style="font-size: 30px;"></i>`;
                } else {
                    previewIcon.innerHTML = `<i class="mdi mdi-file-document-outline text-primary" style="font-size: 26px;"></i>`;
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

        // Drag and Drop Effects on Dropzone
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

        // Prevent Double Submit
        document.getElementById('leaveForm').addEventListener('submit', function() {
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