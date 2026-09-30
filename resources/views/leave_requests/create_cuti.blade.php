@extends('layout.master')

@section('title')
    Form Pengajuan Cuti
@endsection

@section('heading')
    Form Pengajuan Cuti Tahunan
@endsection

@push('styles')
    <style>
        /* ==========================================================
           MODERN AESTHETIC FORM PENGAJUAN CUTI (HIGH CONTRAST)
           - No dull grey backgrounds (fresh, vibrant, clean surface)
           - Sharp, high-contrast typography (anti-nyaru)
           - Spacious layout with top breathing room (anti-mepet)
           - 100% responsive across mobile, tablet, and desktop
           ========================================================== */

        .form-page-wrapper {
            padding-top: 1rem;
            padding-bottom: 3.5rem;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        @media (max-width: 991.98px) {
            .form-page-wrapper {
                padding-top: 0.5rem;
                padding-bottom: 110px !important;
            }
        }

        /* --- TOP PAGE HEADER BLOCK --- */
        .form-top-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 1.15rem 1.35rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 10px -2px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .btn-circle-back {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #059669;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.05);
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-circle-back:hover {
            background: #ecfdf5;
            border-color: #059669;
            color: #047857;
            transform: translateX(-2px);
        }

        .form-top-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a !important;
            margin: 0;
            line-height: 1.2;
        }

        .form-top-subtitle {
            font-size: 0.8125rem;
            color: #334155 !important;
            font-weight: 500;
            margin: 0;
            margin-top: 2px;
        }

        .btn-history-link-cuti {
            background: #ecfdf5;
            border: 1.5px solid #a7f3d0;
            color: #047857 !important;
            border-radius: 9999px;
            padding: 0.45rem 1rem;
            font-size: 0.8125rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(5, 150, 105, 0.08);
            white-space: nowrap;
        }

        .btn-history-link-cuti:hover {
            background: #059669;
            color: #ffffff !important;
            border-color: #059669;
        }

        @media (max-width: 575.98px) {
            .form-top-bar {
                padding: 10px 12px;
                border-radius: 14px;
                margin-bottom: 12px;
            }
            .btn-circle-back {
                width: 36px;
                height: 36px;
                font-size: 17px;
            }
            .form-top-title {
                font-size: 1rem;
            }
            .form-top-subtitle {
                display: none;
            }
            .btn-history-link-cuti {
                padding: 4px 10px;
                font-size: 11.5px;
            }
        }

        /* --- CUTI HERO BENTO BALANCE CARD --- */
        .cuti-bento-hero {
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
            border-radius: 20px;
            padding: 1.5rem 1.75rem;
            color: #ffffff !important;
            margin-bottom: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.3), 0 4px 10px -2px rgba(5, 150, 105, 0.15);
            position: relative;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }

        .cuti-year-tag {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff !important;
            border-radius: 9999px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
        }

        .cuti-balance-val {
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.02em;
            color: #ffffff !important;
        }

        /* --- MAIN FORM CARD --- */
        .leave-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 4px 10px -2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
            margin-bottom: 2rem;
            width: 100%;
        }

        .leave-form-card .card-body {
            padding: 2rem;
        }

        @media (max-width: 767.98px) {
            .leave-form-card {
                border-radius: 18px;
            }
            .leave-form-card .card-body {
                padding: 1.25rem 1rem;
            }
            .cuti-bento-hero {
                padding: 1.25rem 1.15rem;
                border-radius: 16px;
            }
            .cuti-balance-val {
                font-size: 2.15rem;
            }
        }

        /* --- SECTION STEP HEADERS --- */
        .form-section-head {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 1rem;
            margin-top: 0.25rem;
        }

        .form-section-step {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: #059669;
            color: #ffffff !important;
            font-size: 13.5px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
            margin-top: 1px;
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a !important;
            margin: 0;
            line-height: 1.3;
        }

        .form-section-subtitle {
            font-size: 0.8125rem;
            color: #475569 !important;
            margin: 0;
            font-weight: 500;
            line-height: 1.3;
        }

        /* --- NOTICES --- */
        .pending-alert-card {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-left: 5px solid #d97706;
            border-radius: 16px;
            padding: 1.15rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .pending-status-pill {
            background: #fef3c7;
            color: #92400e !important;
            border: 1px solid #fcd34d;
            border-radius: 9999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 800;
        }

        .pending-chip {
            background: #ffffff;
            border: 1px solid #fcd34d;
            border-radius: 9999px;
            padding: 4px 10px;
            font-size: 11.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #78350f !important;
            font-weight: 700;
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
            padding: 1.15rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        /* --- HIGH-CONTRAST FORM CONTROLS --- */
        .form-label-custom {
            font-size: 0.9rem;
            font-weight: 800;
            color: #0f172a !important;
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-control-custom {
            border: 1.5px solid #cbd5e1;
            border-radius: 13px;
            padding: 0.75rem 1rem;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            width: 100%;
            box-sizing: border-box;
        }

        .form-control-custom:focus {
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.18) !important;
            outline: none;
            background-color: #ffffff !important;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #059669 !important;
            font-size: 19px;
            pointer-events: none;
            z-index: 4;
        }

        .form-control-custom.has-icon-left {
            padding-left: 44px;
        }

        .form-helper-text {
            font-size: 0.8125rem;
            color: #334155 !important;
            font-weight: 500;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 5px;
            line-height: 1.3;
        }

        /* --- FRESH DROPZONE --- */
        .upload-dropzone {
            border: 2px dashed #6ee7b7;
            border-radius: 16px;
            background: #f0fdf4;
            padding: 1.75rem 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            box-sizing: border-box;
            width: 100%;
        }

        .upload-dropzone:hover,
        .upload-dropzone.dragover {
            border-color: #059669;
            background: #ecfdf5;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.08);
        }

        .upload-icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #ffffff;
            color: #059669;
            font-size: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.65rem;
            box-shadow: 0 3px 10px rgba(5, 150, 105, 0.15);
            transition: transform 0.2s ease;
        }

        .upload-dropzone:hover .upload-icon-circle {
            transform: scale(1.08);
        }

        .upload-main-text {
            font-weight: 800;
            color: #0f172a !important;
            font-size: 0.9375rem;
            margin-bottom: 3px;
        }

        .upload-sub-text {
            color: #334155 !important;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 0;
        }

        /* Live Preview Box */
        .upload-preview-box {
            display: none;
            background: #ffffff;
            border: 1.5px solid #a7f3d0;
            border-radius: 14px;
            padding: 10px 14px;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(5, 150, 105, 0.06);
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        .preview-file-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #ecfdf5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #059669;
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
            font-size: 13.5px;
            color: #0f172a !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .preview-file-size {
            font-size: 12px;
            font-weight: 600;
            color: #334155 !important;
        }

        .btn-remove-preview {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #e11d48;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .btn-remove-preview:hover {
            background: #e11d48;
            color: #ffffff;
        }

        /* --- ACTION BUTTONS BAR --- */
        .form-actions-bar {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-cancel-custom {
            padding: 0.8rem 1.5rem;
            border-radius: 13px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155 !important;
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
            color: #0f172a !important;
            border-color: #94a3b8;
        }

        .btn-submit-cuti {
            padding: 0.8rem 1.85rem;
            border-radius: 13px;
            border: none;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff !important;
            font-weight: 800;
            font-size: 0.95rem;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.4);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-submit-cuti:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            box-shadow: 0 6px 18px rgba(5, 150, 105, 0.5);
            transform: translateY(-1px);
            color: #ffffff !important;
        }

        @media (max-width: 575.98px) {
            .form-actions-bar {
                flex-direction: column-reverse;
                gap: 10px;
            }
            .btn-cancel-custom,
            .btn-submit-cuti {
                width: 100%;
                justify-content: center;
                padding: 0.85rem 1.25rem;
            }
        }

        .char-counter {
            font-size: 12px;
            font-weight: 700;
            color: #334155 !important;
        }
        .char-counter.warning {
            color: #e11d48 !important;
        }

        .duration-pill {
            background: #ecfdf5 !important;
            color: #047857 !important;
            border: 1.5px solid #a7f3d0 !important;
            border-radius: 9999px;
            padding: 3px 10px;
            font-size: 11.5px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.08);
            white-space: nowrap;
        }

        .duration-pill span {
            color: #047857 !important;
            font-weight: 800;
        }
    </style>
@endpush

@section('content')
    <div class="form-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">

                {{-- 1. Top Page Navigation Bar --}}
                <div class="form-top-bar">
                    <div class="d-flex align-items-center gap-2.5">
                        <a href="{{ route('dashboard') }}" class="btn-circle-back" title="Kembali ke Dashboard">
                            <i class="mdi mdi-arrow-left"></i>
                        </a>
                        <div>
                            <h5 class="form-top-title">Formulir Pengajuan Cuti Tahunan</h5>
                            <p class="form-top-subtitle">Tentukan periode cuti dan lampirkan formulir yang diperlukan.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('leave-requests.cuti-history') }}" class="btn-history-link-cuti">
                            <i class="mdi mdi-history fs-6"></i>
                            <span>Riwayat Cuti</span>
                        </a>
                    </div>
                </div>

                {{-- 2. Hero Cuti Bento Balance Card --}}
                <div class="cuti-bento-hero">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 position-relative" style="z-index: 2;">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="cuti-year-tag font-monospace">
                                    <i class="mdi mdi-calendar-star me-1"></i>TAHUN {{ date('Y') }}
                                </span>
                            </div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 12px; letter-spacing: 0.05em;">Sisa Saldo Cuti Anda</h6>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="cuti-balance-val">{{ $user->leave_balance ?? 12 }}</span>
                                <span class="fs-5 fw-bold text-white">Hari Kerja</span>
                            </div>
                            <p class="mb-0 text-white-50 small mt-2 fw-medium">
                                Dari total alokasi <strong>{{ $user->yearly_leave_limit ?? 12 }} Hari / Tahun</strong>. Pengajuan melebihi saldo dapat dilakukan via penyesuaian payroll.
                            </p>
                        </div>
                        <div class="d-none d-sm-block text-white text-end">
                            <i class="mdi mdi-palm-tree display-3 opacity-50"></i>
                        </div>
                    </div>
                </div>

                {{-- 3. Pending Leaves Warning Notification --}}
                @if(isset($pendingLeaves) && $pendingLeaves->count() > 0)
                    <div class="pending-alert-card" role="alert">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-clock-alert-outline fs-5 text-amber-600"></i>
                                <h6 class="fw-bold mb-0 text-slate-900">Menunggu Verifikasi Audit</h6>
                            </div>
                            <span class="pending-status-pill">
                                {{ $pendingLeaves->count() }} Pengajuan Aktif
                            </span>
                        </div>
                        <p class="mb-2 text-slate-800 small fw-medium">
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

                {{-- 4. Validation Errors Notice --}}
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

                {{-- 5. Main Form Card --}}
                <div class="leave-form-card">
                    <div class="card-body">
                        <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data" id="cutiForm">
                            @csrf
                            <input type="hidden" name="type" value="cuti">

                            {{-- ======================================================== --}}
                            {{-- SECTION 1: PERIODE TANGGAL CUTI --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-2">
                                <div class="form-section-head">
                                    <div class="form-section-step">1</div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                            <h6 class="form-section-title mb-0">Periode Tanggal Cuti <span class="text-danger">*</span></h6>
                                            <span id="duration_indicator" class="duration-pill d-none">
                                                <i class="mdi mdi-calendar-range"></i>
                                                <span id="duration_text">1 Hari</span>
                                            </span>
                                        </div>
                                        <p class="form-section-subtitle mb-0">Tentukan tanggal mulai dan selesai pengambilan cuti</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    {{-- Mulai Tanggal Cuti --}}
                                    <div class="col-12 col-md-6">
                                        <label for="start_date" class="form-label-custom">
                                            <i class="mdi mdi-calendar text-emerald-600"></i>
                                            <span>Mulai Tanggal Cuti</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="mdi mdi-calendar-today input-icon-left"></i>
                                            <input type="date" name="start_date" id="start_date" class="form-control-custom has-icon-left"
                                                value="{{ old('start_date', date('Y-m-d')) }}" required onchange="calculateDuration()">
                                        </div>
                                        <div class="form-helper-text">
                                            <i class="mdi mdi-information-outline text-emerald-600"></i>
                                            <span>Hari pertama Anda tidak masuk kantor.</span>
                                        </div>
                                    </div>

                                    {{-- Sampai Tanggal Cuti --}}
                                    <div class="col-12 col-md-6">
                                        <label for="end_date" class="form-label-custom">
                                            <i class="mdi mdi-calendar-check text-emerald-600"></i>
                                            <span>Sampai Tanggal (Opsional)</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="mdi mdi-calendar-month input-icon-left"></i>
                                            <input type="date" name="end_date" id="end_date" class="form-control-custom has-icon-left"
                                                value="{{ old('end_date') }}" onchange="calculateDuration()">
                                        </div>
                                        <div class="form-helper-text">
                                            <i class="mdi mdi-information-outline text-emerald-600"></i>
                                            <span>Kosongkan atau samakan jika hanya mengambil cuti 1 hari.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- SECTION 2: ALASAN CUTI --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-2">
                                <div class="form-section-head">
                                    <div class="form-section-step">2</div>
                                    <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                        <div>
                                            <h6 class="form-section-title">Alasan / Keperluan Cuti <span class="text-danger">*</span></h6>
                                            <p class="form-section-subtitle">Tuliskan keterangan keperluan cuti yang akan dijalani</p>
                                        </div>
                                        <span id="charCount" class="char-counter">0 / 255</span>
                                    </div>
                                </div>

                                <textarea name="reason" id="reason" class="form-control-custom" rows="3" required maxlength="255"
                                    placeholder="Contoh: Acara keluarga di luar kota, keperluan mendesak, dsb..."
                                    oninput="updateCharCount(this)">{{ old('reason') }}</textarea>
                                <div class="form-helper-text">
                                    <i class="mdi mdi-shield-check text-emerald-600"></i>
                                    <span>Informasi ini akan ditinjau oleh leader dan tim audit sebelum disetujui.</span>
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- SECTION 3: BUKTI DOKUMEN / SURAT PENDUKUNG --}}
                            {{-- ======================================================== --}}
                            <div class="mb-4 pb-1">
                                <div class="form-section-head">
                                    <div class="form-section-step">3</div>
                                    <div>
                                        <h6 class="form-section-title">Bukti Dokumen / Foto Pendukung <span class="text-danger">*</span></h6>
                                        <p class="form-section-subtitle">Unggah formulir izin cuti bertandatangan atau dokumen pendukung</p>
                                    </div>
                                </div>

                                {{-- Hidden native file input --}}
                                <input type="file" name="file_proof" id="file_proof" class="d-none" accept="image/jpeg,image/png,image/webp,application/pdf" required onchange="handleFileSelected(this)">

                                {{-- Aesthetic Dropzone --}}
                                <div class="upload-dropzone" id="dropzoneBox" onclick="document.getElementById('file_proof').click()">
                                    <div class="upload-icon-circle">
                                        <i class="mdi mdi-cloud-upload-outline"></i>
                                    </div>
                                    <div class="upload-main-text">Klik di sini untuk memilih dokumen bukti / foto</div>
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
                                <button type="submit" class="btn-submit-cuti" id="submitBtn">
                                    <i class="mdi mdi-send-check"></i>
                                    <span>Kirim Pengajuan Cuti</span>
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
        // 1. Hitung Durasi Hari Otomatis
        function calculateDuration() {
            let indicator = document.getElementById('duration_indicator');
            let text = document.getElementById('duration_text');
            let start = document.getElementById('start_date').value;
            let end = document.getElementById('end_date').value;

            if (!indicator || !text) return;

            if (!start) {
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

        // 2. Character Counter for Reason
        function updateCharCount(el) {
            let current = el.value.length;
            let counter = document.getElementById('charCount');
            if (counter) {
                counter.innerText = current + " / 255";
                if (current >= 240) {
                    counter.classList.add('warning');
                } else {
                    counter.classList.remove('warning');
                }
            }
        }

        // 3. File Upload & Live Preview
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
                    previewIcon.innerHTML = `<i class="mdi mdi-file-pdf-box text-danger" style="font-size: 30px;"></i>`;
                } else {
                    previewIcon.innerHTML = `<i class="mdi mdi-file-document-outline text-emerald-600" style="font-size: 26px;"></i>`;
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
        document.getElementById('cutiForm').addEventListener('submit', function() {
            let submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim Pengajuan...';
        });

        // Initialize state on DOM ready
        document.addEventListener('DOMContentLoaded', function () {
            calculateDuration();
            let reasonInput = document.getElementById('reason');
            if (reasonInput) updateCharCount(reasonInput);
        });
    </script>
@endsection