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
            .no-print {
                display: none !important;
            }

            .certificate-container {
                box-shadow: none;
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