@extends('layout.master')

@section('title', 'Ramadhan 1447 H')

@section('content')
    <style>
        /* =============================== */
        /* RAMADHAN PAGE — PSTORE THEME    */
        /* =============================== */
        .ramadhan-page {
            min-height: 100vh;
            background: linear-gradient(165deg, #0a1f14 0%, #112b1c 30%, #1A2E22 60%, #0d2318 100%);
            margin: -20px -25px;
            padding-top: calc(var(--header-height, 70px) + 10px);
            position: relative;
            overflow-x: hidden;
            padding-bottom: 120px;
            /* Space for floating nav */
        }

        /* Hide footer and fix white gap at bottom */
        footer,
        .footer {
            display: none !important;
        }

        .main-panel {
            background: #0d2318 !important;
            /* Bottom-most color of our gradient */
            min-height: 100vh !important;
        }

        .content-wrapper {
            background: transparent !important;
            padding-bottom: 0 !important;
        }

        .ramadhan-page * {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            box-sizing: border-box;
        }

        /* Stars / sparkle background */
        .ramadhan-page::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-image:
                radial-gradient(2px 2px at 10% 15%, rgba(212, 175, 55, 0.35), transparent),
                radial-gradient(2px 2px at 25% 35%, rgba(255, 255, 255, 0.2), transparent),
                radial-gradient(1px 1px at 45% 10%, rgba(212, 175, 55, 0.4), transparent),
                radial-gradient(2px 2px at 60% 25%, rgba(255, 215, 0, 0.15), transparent),
                radial-gradient(1px 1px at 75% 45%, rgba(255, 255, 255, 0.25), transparent),
                radial-gradient(2px 2px at 85% 15%, rgba(212, 175, 55, 0.2), transparent),
                radial-gradient(1px 1px at 90% 55%, rgba(255, 255, 255, 0.3), transparent),
                radial-gradient(2px 2px at 15% 65%, rgba(212, 175, 55, 0.15), transparent),
                radial-gradient(1px 1px at 35% 80%, rgba(255, 255, 255, 0.2), transparent),
                radial-gradient(2px 2px at 55% 70%, rgba(212, 175, 55, 0.12), transparent),
                radial-gradient(1px 1px at 70% 85%, rgba(255, 215, 0, 0.25), transparent),
                radial-gradient(2px 2px at 95% 75%, rgba(255, 255, 255, 0.15), transparent);
            pointer-events: none;
            z-index: 0;
        }

        .ramadhan-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px 16px;
        }

        /* === HEADER === */
        .ramadhan-header {
            text-align: left;
            margin-bottom: 20px;
        }

        .ramadhan-header .hijri-date {
            font-size: 20px;
            font-weight: 700;
            color: #D4AF37;
            margin-bottom: 4px;
        }

        .ramadhan-header .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 10px;
            padding: 5px 12px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 12px;
            margin-top: 6px;
            backdrop-filter: blur(8px);
        }

        .ramadhan-header .duration-info {
            color: rgba(255, 255, 255, 0.55);
            font-size: 12px;
            margin-top: 6px;
            line-height: 1.4;
        }

        /* === LOCATION PROMPT === */
        .location-prompt {
            background: rgba(0, 105, 62, 0.12);
            border: 1px dashed rgba(212, 175, 55, 0.3);
            border-radius: 16px;
            padding: 24px 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        .location-prompt i {
            font-size: 36px;
            color: rgba(212, 175, 55, 0.5);
            display: block;
            margin-bottom: 10px;
        }

        .location-prompt p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            margin-bottom: 14px;
            line-height: 1.4;
        }

        .btn-locate {
            background: linear-gradient(135deg, #00693E 0%, #004d2e 100%);
            color: white;
            border: none;
            padding: 11px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(0, 105, 62, 0.35);
        }

        .btn-locate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 105, 62, 0.45);
        }

        .btn-locate:active {
            transform: scale(0.96);
        }

        .btn-locate.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* === COUNTDOWN === */
        .countdown-card {
            background: linear-gradient(135deg, rgba(0, 105, 62, 0.3), rgba(0, 77, 46, 0.2));
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 16px;
            position: relative;
            display: none;
        }

        .countdown-card.visible {
            display: block;
            animation: fadeSlideUp 0.5s ease forwards;
        }

        .countdown-card .label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.65);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .countdown-card .timer {
            font-size: 32px;
            font-weight: 700;
            color: #D4AF37;
            letter-spacing: 1px;
        }

        .countdown-card .timer span {
            font-size: 16px;
            font-weight: 400;
            color: rgba(212, 175, 55, 0.6);
            margin: 0 2px;
        }

        /* === PRAYER TIME CARDS === */
        .prayer-cards {
            display: none;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .prayer-cards.visible {
            display: grid;
            animation: fadeSlideUp 0.5s ease 0.1s forwards;
            opacity: 0;
        }

        .prayer-card {
            background: rgba(0, 105, 62, 0.15);
            border: 1px solid rgba(212, 175, 55, 0.12);
            border-radius: 14px;
            padding: 14px 16px;
        }

        .prayer-card .prayer-label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .prayer-card .prayer-label .tag {
            background: rgba(212, 175, 55, 0.15);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 500;
            color: rgba(212, 175, 55, 0.8);
        }

        .prayer-card .prayer-label .name {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
        }

        .prayer-card .prayer-label .emoji {
            font-size: 14px;
        }

        .prayer-card .time {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        /* === FASTING TRACKER === */
        .fasting-tracker {
            background: rgba(0, 105, 62, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 18px;
            padding: 20px 14px;
            margin-bottom: 20px;
        }

        .week-calendar {
            display: flex;
            justify-content: space-around;
            margin-bottom: 24px;
            gap: 2px;
        }

        .week-day {
            text-align: center;
            flex: 1;
            min-width: 0;
        }

        .week-day .day-number {
            font-size: 12px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.45);
            margin-bottom: 5px;
        }

        .week-day .day-icon {
            font-size: 22px;
            margin-bottom: 3px;
            transition: all 0.3s ease;
            opacity: 0.4;
        }

        .week-day .day-icon.fasted {
            opacity: 1;
            filter: drop-shadow(0 0 6px rgba(212, 175, 55, 0.5));
        }

        .week-day .day-icon.missed {
            opacity: 0.25;
            filter: grayscale(100%);
        }

        .week-day .day-label {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.35);
            font-weight: 500;
        }

        .week-day.today {
            position: relative;
        }

        .week-day.today .day-number {
            background: #D4AF37;
            color: #1A2E22;
            border-radius: 6px;
            padding: 2px 7px;
            font-weight: 700;
            display: inline-block;
        }

        .week-day.today .day-label {
            color: rgba(212, 175, 55, 0.75);
            font-weight: 600;
        }

        /* === FASTING PROMPT === */
        .fasting-prompt {
            text-align: center;
        }

        .fasting-prompt .question {
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .fasting-prompt .btn-fasting {
            display: block;
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 12px;
            -webkit-tap-highlight-color: transparent;
        }

        .fasting-prompt .btn-fasting:active {
            transform: scale(0.96);
        }

        .fasting-prompt .btn-yes {
            background: rgba(0, 105, 62, 0.25);
            color: #ffffff;
            border: 1px solid rgba(0, 105, 62, 0.4);
        }

        .fasting-prompt .btn-yes:hover {
            background: rgba(0, 105, 62, 0.35);
        }

        .fasting-prompt .btn-no {
            background: transparent;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 500;
            font-size: 13px;
        }

        .fasting-prompt .btn-no:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        /* === FASTING RESULT === */
        .fasting-result {
            text-align: center;
            display: none;
        }

        .fasting-result.visible {
            display: block;
            animation: fadeSlideUp 0.5s ease forwards;
        }

        .fasting-result .result-text {
            font-size: 17px;
            font-weight: 700;
            color: #D4AF37;
            margin-bottom: 8px;
        }

        /* === STATS === */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(0, 105, 62, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 14px;
            padding: 14px;
            text-align: center;
        }

        .stat-card .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.45);
            margin-top: 4px;
        }

        .stat-card.fasted .stat-value {
            color: #D4AF37;
        }

        .stat-card.missed .stat-value {
            color: rgba(255, 255, 255, 0.35);
        }

        /* === DOA SECTION === */
        .doa-card {
            background: rgba(0, 105, 62, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .doa-card .doa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 18px;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            gap: 10px;
        }

        .doa-card .doa-header h3 {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            line-height: 1.4;
        }

        .doa-card .doa-header .toggle-icon {
            background: rgba(212, 175, 55, 0.12);
            border: none;
            color: rgba(212, 175, 55, 0.6);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .doa-card.expanded .toggle-icon {
            transform: rotate(180deg);
        }

        .doa-card .doa-body {
            padding: 0 18px 18px;
            display: none;
        }

        .doa-card.expanded .doa-body {
            display: block;
            animation: fadeSlideUp 0.3s ease forwards;
        }

        .doa-card .arabic-text {
            font-family: 'Amiri', 'Traditional Arabic', serif;
            font-size: 22px;
            color: #D4AF37;
            text-align: right;
            direction: rtl;
            line-height: 2;
            margin-bottom: 14px;
            padding: 14px;
            background: rgba(212, 175, 55, 0.05);
            border-radius: 10px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .doa-card .transliteration {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-style: italic;
            margin-bottom: 10px;
            line-height: 1.6;
            word-wrap: break-word;
        }

        .doa-card .translation {
            color: rgba(255, 255, 255, 0.65);
            font-size: 13px;
            line-height: 1.6;
            word-wrap: break-word;
        }

        /* === ANIMATIONS === */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* === RESPONSIVE FIX === */
        @media (max-width: 991px) {
            .ramadhan-page {
                margin: -16px -16px !important;
            }
        }

        @media (max-width: 575px) {
            .ramadhan-page {
                margin: -15px -15px !important;
            }

            .ramadhan-content {
                padding: 16px 14px;
            }

            .ramadhan-header .hijri-date {
                font-size: 18px;
            }

            .countdown-card .timer {
                font-size: 26px;
            }

            .prayer-card .time {
                font-size: 24px;
            }

            .week-day .day-icon {
                font-size: 18px;
            }

            .week-day .day-number {
                font-size: 11px;
            }

            .week-day .day-label {
                font-size: 9px;
            }

            .fasting-prompt .question {
                font-size: 14px;
            }

            .stat-card .stat-value {
                font-size: 22px;
            }

            .doa-card .arabic-text {
                font-size: 18px;
            }
        }

        @media (max-width: 360px) {
            .ramadhan-content {
                padding: 12px 10px;
            }

            .prayer-cards {
                gap: 8px;
            }

            .prayer-card {
                padding: 12px;
            }

            .prayer-card .time {
                font-size: 22px;
            }

            .countdown-card .timer {
                font-size: 22px;
            }

            .week-day .day-icon {
                font-size: 16px;
            }

            .fasting-tracker {
                padding: 16px 10px;
            }
        }

        /* Loading spinner */
        .spinner-small {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <div class="ramadhan-page">
        <div class="ramadhan-content">

            {{-- === HEADER === --}}
            <div class="ramadhan-header">
                <div class="hijri-date" id="hijriDate">{{ $ramadanDay }} Ramadan 1447 H</div>
                <div class="location-badge" id="locationBadge" style="display:none;">
                    🇮🇩 <span id="locationName">Mendeteksi...</span> <span id="timezoneLabel"
                        style="margin-left: 4px; opacity: 0.7;"></span>
                </div>
                <div class="duration-info" id="durationInfo" style="display:none;"></div>
                <button class="btn-reset-location" id="btnResetLocation" onclick="resetLocation()"
                    style="display:none; background:none; border:none; color:#D4AF37; font-size:11px; padding:0; margin-top:8px; cursor:pointer; text-decoration:underline; opacity:0.7;">
                    <i class="mdi mdi-refresh"></i> Reset Lokasi
                </button>
            </div>

            {{-- === LOCATION PROMPT === --}}
            <div class="location-prompt" id="locationPrompt">
                <i class="mdi mdi-map-marker-radius-outline"></i>
                <p>Lacak lokasi Anda untuk mendapatkan jadwal Imsak & Iftar yang akurat</p>
                <button class="btn-locate" id="btnLocate" onclick="detectLocation()">
                    <i class="mdi mdi-crosshairs-gps"></i>
                    Lacak Lokasi Saya
                </button>
            </div>

            {{-- === COUNTDOWN TO IFTAR === --}}
            <div class="countdown-card" id="countdownCard">
                <div class="label">
                    🕌 Hitung mundur ke <strong style="margin-left:4px; color:#D4AF37;">Iftar</strong>
                </div>
                <div class="timer" id="countdownTimer">--h --m --s</div>
            </div>

            {{-- === IMSAK & IFTAR CARDS === --}}
            <div class="prayer-cards" id="prayerCards">
                <div class="prayer-card">
                    <div class="prayer-label">
                        <span class="tag">Hari ini</span>
                        <span class="name">Imsak</span>
                        <span class="emoji">✨</span>
                    </div>
                    <div class="time" id="imsakTime">--:--</div>
                </div>
                <div class="prayer-card">
                    <div class="prayer-label">
                        <span class="tag">Hari ini</span>
                        <span class="name">Iftar</span>
                        <span class="emoji">🌅</span>
                    </div>
                    <div class="time" id="iftarTime">--:--</div>
                </div>
            </div>

            {{-- === FASTING TRACKER CALENDAR === --}}
            <div class="fasting-tracker">
                <div class="week-calendar" id="weekCalendar">
                    @php
                        $ramadanStartDate = \Carbon\Carbon::parse('2026-02-19');
                    @endphp
                    @for ($d = $weekStart; $d <= $weekEnd; $d++)
                        @php
                            $dayDate = $ramadanStartDate->copy()->addDays($d - 1);
                            $dayOfWeek = $dayDate->dayOfWeek;
                            $dayAbbr = ['M', 'S', 'S', 'R', 'K', 'J', 'S'][$dayOfWeek];
                            $log = $fastingLogs->get($d);
                            $isToday = ($d == $ramadanDay);
                            $iconClass = '';
                            if ($log) {
                                $iconClass = $log->is_fasting ? 'fasted' : 'missed';
                            }
                        @endphp
                        <div class="week-day {{ $isToday ? 'today' : '' }}">
                            <div class="day-number">{{ $d }}</div>
                            <div class="day-icon {{ $iconClass }}">🌙</div>
                            <div class="day-label">{{ $dayAbbr }}</div>
                        </div>
                    @endfor
                </div>

                {{-- Fasting Prompt --}}
                <div class="fasting-prompt" id="fastingPrompt" style="{{ $todayLog ? 'display:none;' : '' }}">
                    <div class="question">Apakah Anda berpuasa hari ini?</div>
                    <button class="btn-fasting btn-yes" onclick="logFasting(true)">
                        Ya, Alhamdulillah ✅
                    </button>
                    <button class="btn-fasting btn-no" onclick="logFasting(false)">
                        Tidak, aku melewatkannya. ❌
                    </button>
                </div>

                {{-- Fasting Result --}}
                <div class="fasting-result {{ $todayLog ? 'visible' : '' }}" id="fastingResult">
                    @if($todayLog && $todayLog->is_fasting)
                        <div class="result-text" id="resultText">Mabrouk! Semoga puasa Anda diterima 🤲</div>
                    @elseif($todayLog)
                        <div class="result-text" id="resultText">Semoga bisa berpuasa esok hari 🤲</div>
                    @else
                        <div class="result-text" id="resultText"></div>
                    @endif
                    <a href="{{ route('ramadhan.history') }}" class="tracker-link"
                        style="display: inline-block; margin-top: 10px; color: #D4AF37; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Pergi ke pelacak puasa <i class="mdi mdi-arrow-right"></i>
                    </a>
                </div>
            </div>

            {{-- === STATISTIK === --}}
            <div class="stats-row">
                <div class="stat-card fasted">
                    <div class="stat-value" id="statFasted">{{ $totalFasting }}</div>
                    <div class="stat-label">Hari Berpuasa</div>
                </div>
                <div class="stat-card missed">
                    <div class="stat-value" id="statMissed">{{ $totalMissed }}</div>
                    <div class="stat-label">Hari Terlewat</div>
                </div>
            </div>

            {{-- === DOA RAMADHAN === --}}
            <div class="doa-card" id="doaCard">
                <div class="doa-header" onclick="toggleDoa()">
                    <h3>Doa untuk Kekuatan dan Ketakwaan saat Berpuasa</h3>
                    <span class="toggle-icon">
                        <i class="mdi mdi-chevron-down"></i>
                    </span>
                </div>
                <div class="doa-body">
                    <div class="arabic-text">
                        اللَّهُمَّ أَعِنِّي عَلَى صِيَامِهِ وَقِيَامِهِ وَجَنِّبْنِي فِيهِ مِنْ هَفَوَاتِهِ وَآثَامِهِ
                        وَارْزُقْنِي فِيهِ ذِكْرَكَ وَدَوَامَ شُكْرِكَ لَكَ بِتَوْفِيقِكَ وَاهْدِنِي فِيهِ لِأَقْوَمِ
                        الطَّرِيقِ
                    </div>
                    <div class="transliteration">
                        Allāhumma a'innī 'alā ṣiyāmihi wa qiyāmihi, wa jannibni fīhi min hafawātihi wa āthāmihi, wa arzuqnī
                        fīhi dhikrak wa dawāma ash-shukri laka bi tawfīqik, wa ahdini fīhi li-aqwamit-ṭarīq
                    </div>
                    <div class="translation">
                        Ya Allah, tolonglah aku dalam berpuasa dan berdiri dalam salatku selama bulan puasa, jauhkanlah aku
                        dari kesalahan dan dosa-dosanya, berilah aku dzikir dan rasa syukur yang terus menerus hingga
                        hidayah-Mu, dan arahkanlah aku ke jalan yang lurus.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        (function () {
            let maghribTimeStr = null;
            let countdownInterval = null;

            window.detectLocation = function () {
                const btn = document.getElementById('btnLocate');
                btn.innerHTML = '<span class="spinner-small"></span> Mendeteksi...';
                btn.classList.add('loading');

                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung Geolocation');
                    btn.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Lacak Lokasi Saya';
                    btn.classList.remove('loading');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        fetchPrayerTimes(position.coords.latitude, position.coords.longitude, true);
                    },
                    function (error) {
                        let msg = 'Gagal mendapatkan lokasi: ' + error.message;
                        if (error.code === 1) msg = "Izin lokasi ditolak. Silakan aktifkan izin lokasi di browser Anda.";
                        alert(msg);
                        btn.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Lacak Lokasi Saya';
                        btn.classList.remove('loading');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            };

            window.resetLocation = function () {
                if (confirm("Hapus data lokasi dan ambil ulang?")) {
                    localStorage.removeItem('ramadhan_cached_date');
                    localStorage.removeItem('ramadhan_cached_data');
                    localStorage.removeItem('ramadhan_lat');
                    localStorage.removeItem('ramadhan_lng');
                    location.reload();
                }
            };

            function fetchPrayerTimes(lat, lng, isNew = false) {
                // Cek Cache (Hanya jika bukan pencarian baru)
                if (!isNew) {
                    const cached = localStorage.getItem('pstore_ramadhan_prayer_times');
                    if (cached) {
                        const data = JSON.parse(cached);
                        const today = new Date().toDateString();
                        // Reset jika cache hari berbeda ATAU jika format lokasi masih lama (hanya "Indonesia")
                        if (data.cacheDate === today && data.location && data.location !== 'Indonesia') {
                            renderPrayerData(data);
                            return;
                        }
                    }
                }

                fetch(`{{ route('ramadhan.prayer-times') }}?latitude=${lat}&longitude=${lng}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('locationPrompt').style.display = 'none';

                            const badge = document.getElementById('locationBadge');
                            badge.style.display = 'inline-flex';
                            document.getElementById('locationName').textContent = data.location || 'Indonesia';

                            // Timezone detection
                            const tzLabel = document.getElementById('timezoneLabel');
                            if (data.timezone) {
                                if (data.timezone.includes('Jakarta')) tzLabel.textContent = '(WIB)';
                                else if (data.timezone.includes('Makassar')) tzLabel.textContent = '(WITA)';
                                else if (data.timezone.includes('Jayapura')) tzLabel.textContent = '(WIT)';
                            } else {
                                // Fallback by longitude
                                if (lng < 120) tzLabel.textContent = '(WIB)';
                                else if (lng < 135) tzLabel.textContent = '(WITA)';
                                else tzLabel.textContent = '(WIT)';
                            }

                            const imsak = (data.timings.Imsak || '--:--').replace(/\s*\(.*\)/, '');
                            const maghrib = (data.timings.Maghrib || '--:--').replace(/\s*\(.*\)/, '');
                            document.getElementById('imsakTime').textContent = imsak;
                            document.getElementById('iftarTime').textContent = maghrib;
                            maghribTimeStr = maghrib;

                            document.getElementById('countdownCard').classList.add('visible');
                            document.getElementById('prayerCards').classList.add('visible');

                            if (imsak !== '--:--' && maghrib !== '--:--') {
                                const [ih, im] = imsak.split(':').map(Number);
                                const [mh, mm] = maghrib.split(':').map(Number);
                                const dTotalMin = (mh * 60 + mm) - (ih * 60 + im);
                                const dH = Math.floor(dTotalMin / 60);
                                const dM = dTotalMin % 60;
                                const info = document.getElementById('durationInfo');
                                info.textContent = `Total durasi puasa hari ini: ${dH} jam ${dM} menit`;
                                info.style.display = 'block';
                            }

                            if (data.hijri && data.hijri.day && data.hijri.month) {
                                document.getElementById('hijriDate').textContent =
                                    data.hijri.day + ' ' + data.hijri.month.en + ' ' + data.hijri.year + ' H';
                            }

                            localStorage.setItem('ramadhan_lat', lat);
                            localStorage.setItem('ramadhan_lng', lng);
                            localStorage.setItem('ramadhan_cached_date', new Date().toDateString());
                            localStorage.setItem('ramadhan_cached_data', JSON.stringify(data));

                            startCountdown();
                        } else {
                            alert(data.message || 'Gagal mengambil jadwal sholat');
                            resetButton();
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan saat mengambil jadwal');
                        resetButton();
                    });
            }

            function resetButton() {
                const btn = document.getElementById('btnLocate');
                if (btn) {
                    btn.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Coba Lagi';
                    btn.classList.remove('loading');
                }
            }

            function startCountdown() {
                if (!maghribTimeStr) return;
                if (countdownInterval) clearInterval(countdownInterval);

                function tick() {
                    const now = new Date();
                    const [mH, mM] = maghribTimeStr.split(':').map(Number);
                    const target = new Date();
                    target.setHours(mH, mM, 0, 0);

                    let diff = target - now;
                    if (diff <= 0) {
                        document.getElementById('countdownTimer').innerHTML = '🎉 Waktunya Berbuka!';
                        clearInterval(countdownInterval);
                        return;
                    }

                    const h = Math.floor(diff / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);

                    document.getElementById('countdownTimer').innerHTML =
                        `${h}<span>h</span> ${String(m).padStart(2, '0')}<span>m</span> ${String(s).padStart(2, '0')}<span>s</span>`;
                }

                tick();
                countdownInterval = setInterval(tick, 1000);
            }

            window.logFasting = function (isFasting) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`{{ route('ramadhan.fasting.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ is_fasting: isFasting ? 1 : 0 })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('fastingPrompt').style.display = 'none';

                            const result = document.getElementById('fastingResult');
                            document.getElementById('resultText').textContent = data.message;
                            result.classList.add('visible');

                            if (document.getElementById('statFasted')) {
                                document.getElementById('statFasted').textContent = data.total_fasting;
                            }

                            const todayDayEl = document.querySelector('.week-day.today .day-icon');
                            if (todayDayEl) {
                                todayDayEl.classList.remove('fasted', 'missed');
                                todayDayEl.classList.add(isFasting ? 'fasted' : 'missed');
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal menyimpan data puasa');
                    });
            };

            window.toggleDoa = function () {
                document.getElementById('doaCard').classList.toggle('expanded');
            };

            // AUTO-INIT LOGIC
            const cachedDate = localStorage.getItem('ramadhan_cached_date');
            const cachedData = localStorage.getItem('ramadhan_cached_data');

            // Force update jika data lama hanya berisi "Indonesia"
            let isStaleFormat = false;
            if (cachedData) {
                try {
                    const parsed = JSON.parse(cachedData);
                    if (parsed.location === 'Indonesia') isStaleFormat = true;
                } catch (e) { }
            }

            if (cachedDate === new Date().toDateString() && cachedData && !isStaleFormat) {
                // We could call a render function here, but let's keep the current inline logic
                // and just update the stale check.
                try {
                    const data = JSON.parse(cachedData);
                    if (data.success) {
                        document.getElementById('locationPrompt').style.display = 'none';
                        document.getElementById('locationBadge').style.display = 'inline-flex';
                        document.getElementById('locationName').textContent = data.location || 'Indonesia';
                        document.getElementById('btnResetLocation').style.display = 'inline-block';

                        const imsak = (data.timings.Imsak || '--:--').replace(/\s*\(.*\)/, '');
                        const maghrib = (data.timings.Maghrib || '--:--').replace(/\s*\(.*\)/, '');
                        document.getElementById('imsakTime').textContent = imsak;
                        document.getElementById('iftarTime').textContent = maghrib;
                        maghribTimeStr = maghrib;

                        document.getElementById('countdownCard').classList.add('visible');
                        document.getElementById('prayerCards').classList.add('visible');

                        if (data.hijri && data.hijri.day && data.hijri.month) {
                            document.getElementById('hijriDate').textContent =
                                data.hijri.day + ' ' + data.hijri.month.en + ' ' + data.hijri.year + ' H';
                        }

                        const tzLabel = document.getElementById('timezoneLabel');
                        if (data.timezone) {
                            if (data.timezone.includes('Jakarta')) tzLabel.textContent = '(WIB)';
                            else if (data.timezone.includes('Makassar')) tzLabel.textContent = '(WITA)';
                            else if (data.timezone.includes('Jayapura')) tzLabel.textContent = '(WIT)';
                        }

                        startCountdown();
                    }
                } catch (e) { }
            } else {
                const savedLat = localStorage.getItem('ramadhan_lat');
                const savedLng = localStorage.getItem('ramadhan_lng');
                if (savedLat && savedLng) {
                    document.getElementById('btnLocate').innerHTML = '<span class="spinner-small"></span> Memperbarui...';
                    document.getElementById('btnLocate').classList.add('loading');
                    fetchPrayerTimes(parseFloat(savedLat), parseFloat(savedLng));
                }
            }

        })();
    </script>
@endsection