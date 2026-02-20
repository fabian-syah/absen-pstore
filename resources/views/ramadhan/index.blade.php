@extends('layout.master')

@section('title', 'Ramadhan 1447 H')

@section('content')
    <style>
        /* ==================== */
        /* RAMADHAN PAGE STYLES */
        /* ==================== */
        .ramadhan-page {
            min-height: 100vh;
            background: linear-gradient(165deg, #0f0b2e 0%, #1a1340 30%, #2d1b69 60%, #1a1340 100%);
            margin: -20px -25px;
            padding: 0;
            position: relative;
            overflow: hidden;
            padding-bottom: 100px;
        }

        .ramadhan-page * {
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        /* Stars background */
        .ramadhan-page::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-image:
                radial-gradient(2px 2px at 10% 15%, rgba(255, 255, 255, 0.4), transparent),
                radial-gradient(2px 2px at 25% 35%, rgba(255, 255, 255, 0.3), transparent),
                radial-gradient(1px 1px at 45% 10%, rgba(255, 255, 255, 0.5), transparent),
                radial-gradient(2px 2px at 60% 25%, rgba(255, 255, 255, 0.2), transparent),
                radial-gradient(1px 1px at 75% 45%, rgba(255, 255, 255, 0.4), transparent),
                radial-gradient(2px 2px at 85% 15%, rgba(255, 255, 255, 0.3), transparent),
                radial-gradient(1px 1px at 90% 55%, rgba(255, 255, 255, 0.4), transparent),
                radial-gradient(2px 2px at 15% 65%, rgba(255, 255, 255, 0.2), transparent),
                radial-gradient(1px 1px at 35% 80%, rgba(255, 255, 255, 0.3), transparent),
                radial-gradient(2px 2px at 55% 70%, rgba(255, 255, 255, 0.2), transparent),
                radial-gradient(1px 1px at 70% 85%, rgba(255, 255, 255, 0.4), transparent),
                radial-gradient(2px 2px at 95% 75%, rgba(255, 255, 255, 0.3), transparent);
            pointer-events: none;
            z-index: 0;
        }

        .ramadhan-content {
            position: relative;
            z-index: 1;
            max-width: 500px;
            margin: 0 auto;
            padding: 24px 16px;
        }

        /* === HEADER === */
        .ramadhan-header {
            text-align: left;
            margin-bottom: 24px;
        }

        .ramadhan-header .hijri-date {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .ramadhan-header .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 6px 14px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            margin-top: 8px;
            backdrop-filter: blur(10px);
        }

        .ramadhan-header .duration-info {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            margin-top: 6px;
        }

        /* === LOCATION PROMPT === */
        .location-prompt {
            background: rgba(255, 255, 255, 0.08);
            border: 1px dashed rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .location-prompt i {
            font-size: 40px;
            color: rgba(255, 255, 255, 0.5);
            display: block;
            margin-bottom: 12px;
        }

        .location-prompt p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 16px;
        }

        .btn-locate {
            background: linear-gradient(135deg, #6c3ce0 0%, #a855f7 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(108, 60, 224, 0.4);
        }

        .btn-locate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(108, 60, 224, 0.5);
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
            background: linear-gradient(135deg, rgba(108, 60, 224, 0.35), rgba(168, 85, 247, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 20px 24px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            display: none;
        }

        .countdown-card.visible {
            display: block;
            animation: fadeSlideUp 0.5s ease forwards;
        }

        .countdown-card .label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            margin-bottom: 8px;
        }

        .countdown-card .label i {
            font-size: 18px;
        }

        .countdown-card .expand-btn {
            position: absolute;
            top: 18px;
            right: 18px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
        }

        .countdown-card .timer {
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 1px;
        }

        .countdown-card .timer span {
            font-size: 18px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.6);
            margin: 0 2px;
        }

        /* === PRAYER TIME CARDS === */
        .prayer-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
            display: none;
        }

        .prayer-cards.visible {
            display: grid;
            animation: fadeSlideUp 0.5s ease 0.1s forwards;
            opacity: 0;
        }

        .prayer-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 16px 18px;
            position: relative;
        }

        .prayer-card .prayer-label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
        }

        .prayer-card .prayer-label .tag {
            background: rgba(255, 255, 255, 0.12);
            padding: 3px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
        }

        .prayer-card .prayer-label .name {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .prayer-card .prayer-label .emoji {
            font-size: 16px;
        }

        .prayer-card .time {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        /* === FASTING TRACKER === */
        .fasting-tracker {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 24px 18px;
            margin-bottom: 24px;
        }

        .week-calendar {
            display: flex;
            justify-content: space-around;
            margin-bottom: 28px;
        }

        .week-day {
            text-align: center;
            flex: 1;
        }

        .week-day .day-number {
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 6px;
        }

        .week-day .day-icon {
            font-size: 24px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
            opacity: 0.5;
        }

        .week-day .day-icon.fasted {
            opacity: 1;
            filter: drop-shadow(0 0 6px rgba(255, 200, 50, 0.5));
        }

        .week-day .day-icon.missed {
            opacity: 0.35;
            filter: grayscale(100%);
        }

        .week-day .day-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 500;
        }

        .week-day.today {
            position: relative;
        }

        .week-day.today .day-number {
            background: rgba(255, 255, 255, 0.9);
            color: #1a1340;
            border-radius: 8px;
            padding: 2px 8px;
            font-weight: 700;
        }

        .week-day.today .day-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
        }

        /* === FASTING PROMPT === */
        .fasting-prompt {
            text-align: center;
        }

        .fasting-prompt .question {
            color: #ffffff;
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .fasting-prompt .btn-fasting {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 14px;
            -webkit-tap-highlight-color: transparent;
        }

        .fasting-prompt .btn-fasting:active {
            transform: scale(0.96);
        }

        .fasting-prompt .btn-yes {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .fasting-prompt .btn-yes:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .fasting-prompt .btn-no {
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
            font-size: 14px;
        }

        .fasting-prompt .btn-no:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        /* === FASTING RESULT (setelah jawab) === */
        .fasting-result {
            text-align: center;
            display: none;
        }

        .fasting-result.visible {
            display: block;
            animation: fadeSlideUp 0.5s ease forwards;
        }

        .fasting-result .result-text {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 18px;
            font-style: italic;
        }

        .fasting-result .btn-share {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
            text-decoration: none;
        }

        .fasting-result .btn-share:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .fasting-result .tracker-link {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-top: 16px;
            text-decoration: none;
        }

        .fasting-result .tracker-link:hover {
            color: #ffffff;
        }

        /* === DOA SECTION === */
        .doa-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .doa-card .doa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        .doa-card .doa-header h3 {
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            line-height: 1.4;
        }

        .doa-card .doa-header .toggle-icon {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: rgba(255, 255, 255, 0.6);
            width: 34px;
            height: 34px;
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
            padding: 0 20px 20px;
            display: none;
        }

        .doa-card.expanded .doa-body {
            display: block;
            animation: fadeSlideUp 0.3s ease forwards;
        }

        .doa-card .arabic-text {
            font-family: 'Amiri', 'Traditional Arabic', serif;
            font-size: 24px;
            color: #ffffff;
            text-align: right;
            direction: rtl;
            line-height: 2;
            margin-bottom: 16px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
        }

        .doa-card .transliteration {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            font-style: italic;
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .doa-card .translation {
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            line-height: 1.6;
        }

        /* === STATS === */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 6px;
        }

        .stat-card.fasted .stat-value {
            color: #f5c842;
        }

        .stat-card.missed .stat-value {
            color: rgba(255, 255, 255, 0.4);
        }

        /* === ANIMATIONS === */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulseGlow {

            0%,
            100% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }
        }

        /* === RESPONSIVE === */
        @media (max-width: 400px) {
            .ramadhan-content {
                padding: 16px 12px;
            }

            .countdown-card .timer {
                font-size: 28px;
            }

            .prayer-card .time {
                font-size: 26px;
            }

            .week-day .day-icon {
                font-size: 20px;
            }

            .ramadhan-header .hijri-date {
                font-size: 18px;
            }

            .doa-card .arabic-text {
                font-size: 20px;
            }
        }

        @media (min-width: 768px) {
            .ramadhan-page {
                margin: -20px -25px;
            }

            .ramadhan-content {
                padding: 32px 24px;
            }
        }

        /* Fix content wrapper override */
        @media (max-width: 991px) {
            .content-wrapper {
                padding: 0 !important;
            }
        }

        /* Loading spinner */
        .spinner-small {
            width: 18px;
            height: 18px;
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
                    🇮🇩 <span id="locationName">Mendeteksi...</span>
                </div>
                <div class="duration-info" id="durationInfo" style="display:none;"></div>
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
                    🕌 Hitung mundur ke <strong style="margin-left:4px;">Iftar</strong>
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
                {{-- Week Calendar --}}
                <div class="week-calendar" id="weekCalendar">
                    @php
                        $dayNames = ['K', 'S', 'S', 'M', 'S', 'S', 'R']; // Day abbreviations starting from Ramadan start
                        $ramadanStartDate = \Carbon\Carbon::parse('2026-02-20');
                    @endphp
                    @for ($d = $weekStart; $d <= $weekEnd; $d++)
                        @php
                            $dayDate = $ramadanStartDate->copy()->addDays($d - 1);
                            $dayOfWeek = $dayDate->dayOfWeek; // 0=Sun
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

                {{-- Fasting Prompt (belum jawab hari ini) --}}
                <div class="fasting-prompt" id="fastingPrompt" style="{{ $todayLog ? 'display:none;' : '' }}">
                    <div class="question">Apakah Anda berpuasa hari ini?</div>
                    <button class="btn-fasting btn-yes" onclick="logFasting(true)">
                        Ya, Alhamdulillah ✅
                    </button>
                    <button class="btn-fasting btn-no" onclick="logFasting(false)">
                        Tidak, aku melewatkannya. ❌
                    </button>
                </div>

                {{-- Fasting Result (sudah jawab) --}}
                <div class="fasting-result {{ $todayLog ? 'visible' : '' }}" id="fastingResult">
                    @if($todayLog && $todayLog->is_fasting)
                        <div class="result-text" id="resultText">Mabrouk! 🤲</div>
                    @elseif($todayLog)
                        <div class="result-text" id="resultText">Semoga bisa berpuasa esok hari 🤲</div>
                    @else
                        <div class="result-text" id="resultText"></div>
                    @endif
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
                    <h3>Doa untuk Kekuatan dan Ketakwaan<br>saat Berpuasa</h3>
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
            // ===========================
            // VARIABLES
            // ===========================
            let maghribTimeStr = null; // HH:MM format
            let countdownInterval = null;

            // ===========================
            // DETECT LOCATION
            // ===========================
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
                        fetchPrayerTimes(position.coords.latitude, position.coords.longitude);
                    },
                    function (error) {
                        alert('Gagal mendapatkan lokasi: ' + error.message);
                        btn.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Lacak Lokasi Saya';
                        btn.classList.remove('loading');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            };

            // ===========================
            // FETCH PRAYER TIMES
            // ===========================
            function fetchPrayerTimes(lat, lng) {
                fetch(`{{ route('ramadhan.prayer-times') }}?latitude=${lat}&longitude=${lng}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // Hide location prompt
                            document.getElementById('locationPrompt').style.display = 'none';

                            // Show location badge
                            const badge = document.getElementById('locationBadge');
                            badge.style.display = 'inline-flex';
                            document.getElementById('locationName').textContent = data.location || 'Indonesia';

                            // Fill times
                            const imsak = data.timings.Imsak || '--:--';
                            const maghrib = data.timings.Maghrib || '--:--';
                            document.getElementById('imsakTime').textContent = imsak.replace(/\s*\(.*\)/, '');
                            document.getElementById('iftarTime').textContent = maghrib.replace(/\s*\(.*\)/, '');
                            maghribTimeStr = maghrib.replace(/\s*\(.*\)/, '');

                            // Show countdown & cards
                            document.getElementById('countdownCard').classList.add('visible');
                            document.getElementById('prayerCards').classList.add('visible');

                            // Duration info
                            if (imsak !== '--:--' && maghrib !== '--:--') {
                                const imsakClean = imsak.replace(/\s*\(.*\)/, '');
                                const maghribClean = maghrib.replace(/\s*\(.*\)/, '');
                                const [ih, im] = imsakClean.split(':').map(Number);
                                const [mh, mm] = maghribClean.split(':').map(Number);
                                const dTotalMin = (mh * 60 + mm) - (ih * 60 + im);
                                const dH = Math.floor(dTotalMin / 60);
                                const dM = dTotalMin % 60;
                                const info = document.getElementById('durationInfo');
                                info.textContent = `Total durasi puasa hari ini adalah ${dH} jam dan ${dM} menit.`;
                                info.style.display = 'block';
                            }

                            // Update Hijri date from API
                            if (data.hijri && data.hijri.day && data.hijri.month) {
                                document.getElementById('hijriDate').textContent =
                                    data.hijri.day + ' ' + data.hijri.month.en + ' ' + data.hijri.year + ' H';
                            }

                            // Save to localStorage
                            localStorage.setItem('ramadhan_lat', lat);
                            localStorage.setItem('ramadhan_lng', lng);
                            localStorage.setItem('ramadhan_cached_date', new Date().toDateString());
                            localStorage.setItem('ramadhan_cached_data', JSON.stringify(data));

                            // Start countdown
                            startCountdown();
                        } else {
                            alert(data.message || 'Gagal mengambil jadwal sholat');
                            document.getElementById('btnLocate').innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Coba Lagi';
                            document.getElementById('btnLocate').classList.remove('loading');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan saat mengambil jadwal sholat');
                        document.getElementById('btnLocate').innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Coba Lagi';
                        document.getElementById('btnLocate').classList.remove('loading');
                    });
            }

            // ===========================
            // COUNTDOWN TIMER
            // ===========================
            function startCountdown() {
                if (!maghribTimeStr) return;
                if (countdownInterval) clearInterval(countdownInterval);

                function updateCountdown() {
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

                updateCountdown();
                countdownInterval = setInterval(updateCountdown, 1000);
            }

            // ===========================
            // FASTING LOG
            // ===========================
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
                            // Hide prompt
                            document.getElementById('fastingPrompt').style.display = 'none';

                            // Show result
                            const result = document.getElementById('fastingResult');
                            document.getElementById('resultText').textContent = data.message;
                            result.classList.add('visible');

                            // Update stats
                            if (document.getElementById('statFasted')) {
                                document.getElementById('statFasted').textContent = data.total_fasting;
                            }

                            // Update today's calendar icon
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

            // ===========================
            // DOA TOGGLE
            // ===========================
            window.toggleDoa = function () {
                document.getElementById('doaCard').classList.toggle('expanded');
            };

            // ===========================
            // AUTO-LOAD CACHED LOCATION
            // ===========================
            const cachedDate = localStorage.getItem('ramadhan_cached_date');
            const cachedData = localStorage.getItem('ramadhan_cached_data');

            if (cachedDate === new Date().toDateString() && cachedData) {
                // Use cached data for today
                try {
                    const data = JSON.parse(cachedData);
                    if (data.success) {
                        document.getElementById('locationPrompt').style.display = 'none';

                        const badge = document.getElementById('locationBadge');
                        badge.style.display = 'inline-flex';
                        document.getElementById('locationName').textContent = data.location || 'Indonesia';

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
                            info.textContent = `Total durasi puasa hari ini adalah ${dH} jam dan ${dM} menit.`;
                            info.style.display = 'block';
                        }

                        if (data.hijri && data.hijri.day && data.hijri.month) {
                            document.getElementById('hijriDate').textContent =
                                data.hijri.day + ' ' + data.hijri.month.en + ' ' + data.hijri.year + ' H';
                        }

                        startCountdown();
                    }
                } catch (e) { }
            } else {
                // Check if we have saved coordinates to auto-fetch
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