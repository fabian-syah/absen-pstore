@extends('layout.master')

@section('title')
    Sertifikat Penghargaan
@endsection

@push('styles')
    <style>
        .certificate-container {
            background: linear-gradient(145deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border-radius: 20px;
            padding: 3rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .certificate-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .certificate-border {
            border: 3px solid;
            border-image: linear-gradient(135deg, #f59e0b, #d97706, #b45309) 1;
            padding: 2.5rem;
            position: relative;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .certificate-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .certificate-title {
            color: #f59e0b;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(245, 158, 11, 0.3);
        }

        .certificate-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }

        .rank-badge {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .rank-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid;
        }

        .rank-badge.rank-1 img {
            border-color: #f59e0b;
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.5);
        }

        .rank-badge.rank-2 img {
            border-color: #9ca3af;
            box-shadow: 0 0 30px rgba(156, 163, 175, 0.5);
        }

        .rank-badge.rank-3 img {
            border-color: #cd7f32;
            box-shadow: 0 0 30px rgba(205, 127, 50, 0.5);
        }

        .rank-number {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: white;
        }

        .rank-1 .rank-number {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .rank-2 .rank-number {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
        }

        .rank-3 .rank-number {
            background: linear-gradient(135deg, #cd7f32, #a5631b);
        }

        .recipient-name {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .recipient-role {
            color: #f59e0b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .certificate-body {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .stats-row {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin: 2rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #f59e0b;
            display: block;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .certificate-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .company-name {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .certificate-date {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.85rem;
        }

        @media print {

            /* Hide everything except certificate */
            .no-print,
            .sidebar,
            .navbar,
            .nav-sidebar,
            .main-panel>.navbar,
            .footer,
            .breadcrumb,
            header,
            nav,
            aside,
            .sidebar-offcanvas,
            .page-body-wrapper>.sidebar,
            .btn,
            button {
                display: none !important;
            }

            /* Reset body and container */
            body,
            html {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                height: auto !important;
                overflow: visible !important;
            }

            .container-scroller,
            .container-fluid,
            .main-panel,
            .content-wrapper,
            .page-body-wrapper,
            .row,
            .col-lg-8 {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }

            .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
            }

            /* Certificate styling for print - FIT IN ONE PAGE */
            .certificate-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 15px !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 100vh !important;
                max-height: 100vh !important;
                border-radius: 0 !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .certificate-border {
                padding: 15px !important;
                height: calc(100vh - 30px) !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-evenly !important;
                page-break-inside: avoid !important;
            }

            /* Reduce sizes to fit one page */
            .certificate-header {
                margin-bottom: 10px !important;
            }

            .certificate-badge {
                padding: 5px 15px !important;
                font-size: 0.7rem !important;
                margin-bottom: 5px !important;
            }

            .certificate-title {
                font-size: 1.8rem !important;
                margin-bottom: 3px !important;
            }

            .certificate-subtitle {
                font-size: 0.8rem !important;
            }

            .rank-badge {
                width: 80px !important;
                height: 80px !important;
                margin: 0 auto 10px !important;
            }

            .rank-number {
                width: 28px !important;
                height: 28px !important;
                font-size: 0.9rem !important;
                top: -5px !important;
                right: -5px !important;
            }

            .recipient-name {
                font-size: 1.4rem !important;
                margin-bottom: 3px !important;
            }

            .recipient-role {
                font-size: 0.75rem !important;
                margin-bottom: 10px !important;
            }

            .certificate-body {
                margin-bottom: 10px !important;
                line-height: 1.4 !important;
            }

            .certificate-body p {
                font-size: 0.8rem !important;
                margin-bottom: 5px !important;
            }

            .certificate-body h3 {
                font-size: 1.1rem !important;
                margin: 5px 0 !important;
            }

            .stats-row {
                margin: 10px 0 !important;
                gap: 2rem !important;
            }

            .stat-value {
                font-size: 1.5rem !important;
            }

            .stat-label {
                font-size: 0.7rem !important;
            }

            .certificate-footer {
                margin-top: 10px !important;
                padding-top: 10px !important;
            }

            .company-name {
                font-size: 1rem !important;
                margin-bottom: 3px !important;
            }

            .certificate-date {
                font-size: 0.75rem !important;
            }

            /* Ensure colors print correctly */
            .certificate-container,
            .certificate-container * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Remove browser headers/footers and make full page */
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Print Button --}}
            <div class="mb-3 text-end no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="mdi mdi-printer me-1"></i> Cetak Sertifikat
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ms-2">
                    <i class="mdi mdi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            {{-- Certificate Card --}}
            <div class="certificate-container">
                <div class="certificate-border">

                    {{-- Header --}}
                    <div class="certificate-header">
                        <span class="certificate-badge">🏆 Sertifikat Penghargaan</span>
                        <h1 class="certificate-title">Pahlawan Absensi</h1>
                        <p class="certificate-subtitle">Apresiasi untuk dedikasi luar biasa</p>
                    </div>

                    {{-- Rank Badge --}}
                    <div class="rank-badge rank-{{ $rank }}">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1a1a2e&color=f59e0b&size=120"
                                alt="{{ $user->name }}">
                        @endif
                        <span class="rank-number">#{{ $rank }}</span>
                    </div>

                    {{-- Recipient Info --}}
                    <h2 class="recipient-name">{{ $user->name }}</h2>
                    <p class="recipient-role">
                        {{ $user->divisions->pluck('name')->join(', ') ?: ucfirst(str_replace('_', ' ', $user->role)) }}
                    </p>

                    {{-- Certificate Body --}}
                    <div class="certificate-body">
                        <p>
                            Dengan bangga kami berikan penghargaan <strong>{{ $rankText }}</strong> atas
                            dedikasi dan konsistensi kehadiran yang luar biasa selama periode
                        </p>
                        <h3 class="text-white mt-3 mb-3">{{ $period }}</h3>
                    </div>

                    {{-- Stats --}}
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-value">{{ $totalAttendance }}</span>
                            <span class="stat-label">Hari Hadir</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $branch->name }}</span>
                            <span class="stat-label">Cabang</span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="certificate-footer">
                        <p class="company-name">PSTORE</p>
                        <p class="certificate-date">Diterbitkan pada {{ now()->translatedFormat('d F Y') }}</p>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection