@extends('layout.master')

@section('title', 'Pelacak Puasa — Ramadhan 1447 H')

@section('content')
    <style>
        .history-page {
            min-height: 100vh;
            background: linear-gradient(165deg, #0a1f14 0%, #112b1c 30%, #1A2E22 60%, #0d2318 100%);
            margin: -20px -25px;
            padding-top: calc(var(--header-height, 70px) + 10px);
            position: relative;
            overflow-x: hidden;
            padding-bottom: 120px;
        }

        /* Hide footer and fix white gap at bottom */
        footer,
        .footer {
            display: none !important;
        }

        .main-panel {
            background: #0d2318 !important;
            min-height: 100vh !important;
        }

        .content-wrapper {
            background: transparent !important;
            padding-bottom: 0 !important;
        }

        .history-page::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-image:
                radial-gradient(2px 2px at 10% 15%, rgba(212, 175, 55, 0.3), transparent),
                radial-gradient(1px 1px at 85% 15%, rgba(255, 255, 255, 0.2), transparent);
            pointer-events: none;
            z-index: 0;
        }

        .history-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 16px;
        }

        .history-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .history-header h2 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        /* Stats summary */
        .summary-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: rgba(0, 105, 62, 0.15);
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        .summary-card .val {
            font-size: 28px;
            font-weight: 700;
            color: #D4AF37;
            line-height: 1;
        }

        .summary-card .label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Log List */
        .log-list {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .log-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            gap: 16px;
        }

        .log-item:last-child {
            border-bottom: none;
        }

        .log-item .day-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(212, 175, 55, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #D4AF37;
            flex-shrink: 0;
        }

        .log-item .day-circle .num {
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
        }

        .log-item .day-circle .unit {
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .log-item .info {
            flex-grow: 1;
        }

        .log-item .info .date {
            font-size: 14px;
            font-weight: 600;
            color: white;
            margin-bottom: 2px;
        }

        .log-item .info .hijri {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
        }

        .log-item .status-badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.fasting {
            background: rgba(0, 105, 62, 0.2);
            color: #4ade80;
            border: 1px solid rgba(0, 105, 62, 0.3);
        }

        .status-badge.missed {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.4);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 12px;
            display: block;
        }

        @media (max-width: 991px) {
            .history-page {
                margin: -16px -16px !important;
            }
        }
    </style>

    <div class="history-page">
        <div class="history-content">
            <div class="history-header">
                <a href="{{ route('ramadhan.index') }}" class="btn-back">
                    <i class="mdi mdi-arrow-left"></i>
                </a>
                <h2>Pelacak Puasa</h2>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <div class="val">{{ $totalFasting }}</div>
                    <div class="label">Berpuasa</div>
                </div>
                <div class="summary-card">
                    <div class="val">{{ $totalMissed }}</div>
                    <div class="label">Terlewat</div>
                </div>
            </div>

            <div class="log-list">
                @forelse($logs as $log)
                    <div class="log-item">
                        <div class="day-circle">
                            <span class="num">{{ $log->ramadan_day }}</span>
                            <span class="unit">Ramadan</span>
                        </div>
                        <div class="info">
                            <div class="date">{{ $log->date->translatedFormat('d F Y') }}</div>
                            <div class="hijri">{{ $log->ramadan_day }} Ramadan {{ $log->hijri_year }} H</div>
                        </div>
                        <div class="status-badge {{ $log->is_fasting ? 'fasting' : 'missed' }}">
                            {{ $log->is_fasting ? 'Berpuasa' : 'Melewatkan' }}
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="mdi mdi-calendar-blank"></i>
                        <p>Belum ada riwayat puasa tersimpan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection