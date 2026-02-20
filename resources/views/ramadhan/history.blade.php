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
            display: flex;
            flex-direction: column;
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

        .history-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 16px;
            flex-grow: 1;
        }

        .history-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .history-header h2 {
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            text-align: center;
            flex-grow: 1;
        }

        .history-header .subtitle {
            display: block;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 400;
        }

        .btn-nav-icon {
            background: rgba(255, 255, 255, 0.05);
            border: none;
            color: #00ca72;
            font-size: 20px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
        }

        /* Calendar Grid */
        .calendar-section {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 20px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .calendar-days-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            margin-bottom: 12px;
        }

        .day-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .day-label.green {
            color: #00ca72;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .cal-day {
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .cal-day.active {
            color: rgba(255, 255, 255, 0.8);
        }

        .cal-day.selected {
            border: 2px solid #D4AF37;
        }

        .cal-day.fasted {
            background: #008a4e !important;
            color: white !important;
            opacity: 1 !important;
        }

        .cal-day.missed {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            opacity: 1 !important;
        }

        .cal-day.today::after {
            content: '';
            position: absolute;
            bottom: 4px;
            width: 4px;
            height: 4px;
            background: #D4AF37;
            border-radius: 50%;
        }

        /* Interactive Card */
        .interactive-card {
            background: #1a1a1a;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-top h3 {
            color: white;
            font-size: 16px;
            margin: 0;
            font-weight: 600;
        }

        .card-top .date-info {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-action:active {
            transform: scale(0.9);
        }

        .btn-action .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .btn-action.no .icon-circle {
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #f87171;
        }

        .btn-action.yes .icon-circle {
            background: #008a4e;
            color: white;
        }

        .btn-action.active.no .icon-circle {
            background: #ef4444;
            color: white;
            border: none;
        }

        .btn-action.active.yes .icon-circle {
            background: #00ca72;
            border: 2px solid #D4AF37;
        }

        .btn-action span {
            font-size: 9px;
            font-weight: 600;
        }

        .btn-action.no span {
            color: #f87171;
        }

        .btn-action.yes span {
            color: #00ca72;
        }

        .notes-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px;
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .notes-box i {
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            margin-top: 2px;
        }

        .notes-box textarea {
            background: none;
            border: none;
            color: white;
            font-size: 13px;
            width: 100%;
            height: 50px;
            resize: none;
            outline: none;
        }

        .notes-box textarea::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* Ramadan Ini Stats */
        .ramadan-this-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            padding: 20px;
            margin-top: 32px;
        }

        .ramadan-this-section h4 {
            color: white;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .progress-container {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            width: 100%;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #D4AF37;
            width: 0%;
            transition: width 0.5s ease;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1px 1fr 1px 1fr;
            align-items: center;
        }

        .stats-item .val {
            font-size: 20px;
            font-weight: 700;
            color: white;
            margin-bottom: 0;
        }

        .stats-item .label {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: lowercase;
        }

        .stats-item b {
            display: block;
            font-size: 11px;
            margin-top: 2px;
        }

        .stats-item.success b {
            color: #00ca72;
        }

        .stats-item.danger b {
            color: #ef4444;
        }

        .divider {
            width: 1px;
            height: 24px;
            background: rgba(255, 255, 255, 0.1);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        @media (max-width: 480px) {
            .history-page {
                margin: -16px -16px !important;
            }

            .cal-day {
                font-size: 12px;
            }

            .calendar-grid {
                gap: 2px;
            }

            .interactive-card {
                padding: 16px;
            }

            .stats-item .val {
                font-size: 18px;
            }
        }
    </style>

    <div class="history-page">
        <div class="history-content">
            {{-- Header --}}
            <div class="history-header">
                <a href="{{ route('ramadhan.index') }}" class="btn-nav-icon">
                    <i class="mdi mdi-chevron-left"></i>
                </a>
                <div style="text-align: center;">
                    <h2>Ramadan 1447 H</h2>
                    <span class="subtitle">{{ now()->translatedFormat('d M Y') }}</span>
                </div>
                <div style="width: 36px;"></div> {{-- Spacer --}}
            </div>

            {{-- Calendar Section --}}
            <div class="calendar-section">
                <div class="calendar-days-header">
                    <div class="day-label">M</div>
                    <div class="day-label green">S</div>
                    <div class="day-label green">S</div>
                    <div class="day-label green">R</div>
                    <div class="day-label green">K</div>
                    <div class="day-label green">J</div>
                    <div class="day-label">S</div>
                </div>
                <div class="calendar-grid">
                    @php
                        // Ramadan 1 ≈ 19 Feb 2026 (Kamis)
                        // M S S R K J S
                        // 1 2 3 4 5 6 7
                        // Kamis adalah hari ke-5. Jadi 4 hari pertama kosong.
                    @endphp
                    @for($i = 1; $i <= 4; $i++)
                    <div></div> @endfor
                    @for($d = 1; $d <= 30; $d++)
                        @php
                            $log = $logs->get($d);
                            $isFasted = $log && $log->is_fasting;
                            $isMissed = $log && !$log->is_fasting;
                            $isToday = ($d == $currentRamadanDay);
                            $dateObj = $ramadanStart->copy()->addDays($d - 1);
                        @endphp
                        <div class="cal-day {{ $isToday ? 'today' : '' }} {{ $isFasted ? 'fasted' : '' }} {{ $isMissed ? 'missed' : '' }} {{ $d <= $currentRamadanDay ? 'active' : '' }}"
                            data-day="{{ $d }}" data-date="{{ $dateObj->toDateString() }}"
                            data-formatted-date="{{ $dateObj->translatedFormat('D, M d') }} ({{ $d }} Ramadan)"
                            data-notes="{{ $log ? $log->notes : '' }}"
                            data-status="{{ $log ? ($log->is_fasting ? '1' : '0') : '' }}" onclick="selectDay(this)">
                            {{ $d }}
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Interactive Confirmation Card --}}
            <div class="interactive-card" id="confirmCard" style="display: none;">
                <div class="card-top">
                    <div>
                        <h3 id="confirmTitle">Apakah Anda puasa?</h3>
                        <div class="date-info" id="confirmDateInfo" style="font-size: 11px; color: rgba(255,255,255,0.5);">
                        </div>
                    </div>
                    <div class="action-buttons">
                        <div class="btn-action no" id="btnNo" onclick="submitFasting(0)">
                            <div class="icon-circle">
                                <i class="mdi mdi-close"></i>
                            </div>
                            <span>Tidak Puasa</span>
                        </div>
                        <div class="btn-action yes" id="btnYes" onclick="submitFasting(1)">
                            <div class="icon-circle">
                                <i class="mdi mdi-check"></i>
                            </div>
                            <span>Ya</span>
                        </div>
                    </div>
                </div>

                <div class="notes-box">
                    <i class="mdi mdi-menu"></i>
                    <textarea id="fastingNotes" placeholder="Catatan..."></textarea>
                </div>
            </div>

            {{-- Stats Section --}}
            <div class="ramadan-this-section">
                <h4>Ramadan ini</h4>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar" style="width: {{ ($totalFasting / 30) * 100 }}%"></div>
                </div>
                <div class="stats-grid" style="text-align: center;">
                    <div class="stats-item success">
                        <div class="val" id="statFasting">{{ $totalFasting }}</div>
                        <div class="label">hari</div>
                        <b>Berpuasa</b>
                    </div>
                    <div class="divider"></div>
                    <div class="stats-item danger">
                        <div class="val" id="statMissed">{{ $totalMissed }}</div>
                        <div class="label">hari</div>
                        <b>Tidak Puasa</b>
                    </div>
                    <div class="divider"></div>
                    <div class="stats-item">
                        <div class="val" id="statRemaining">{{ $remaining }}</div>
                        <div class="label">hari</div>
                        <b>Tersisa</b>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-success" role="status"></div>
    </div>

    <script>
        let selectedDay = {{ $currentRamadanDay }};
        let selectedDate = "{{ now()->toDateString() }}";

        function selectDay(el) {
            // Unselect all
            document.querySelectorAll('.cal-day').forEach(d => d.classList.remove('selected'));

            // Select this
            el.classList.add('selected');

            selectedDay = el.dataset.day;
            selectedDate = el.dataset.date;

            // Update Card
            const title = document.getElementById('confirmTitle');
            const dateInfo = document.getElementById('confirmDateInfo');
            const notesField = document.getElementById('fastingNotes');
            const confirmCard = document.getElementById('confirmCard');

            confirmCard.style.display = 'block';

            const isToday = (selectedDay == {{ $currentRamadanDay }});
            title.textContent = isToday ? 'Apakah Anda puasa hari ini?' : 'Apakah Anda puasa?';
            dateInfo.textContent = el.dataset.formattedDate;
            notesField.value = el.dataset.notes || '';

            // Update active state of buttons
            const status = el.dataset.status;
            document.getElementById('btnNo').classList.remove('active');
            document.getElementById('btnYes').classList.remove('active');

            if (status === '1') {
                document.getElementById('btnYes').classList.add('active');
            } else if (status === '0') {
                document.getElementById('btnNo').classList.add('active');
            }
        }

        function submitFasting(isFasting) {
            const notes = document.getElementById('fastingNotes').value;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            document.getElementById('loadingOverlay').style.display = 'flex';

            fetch(`{{ route('ramadhan.fasting.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    is_fasting: isFasting,
                    date: selectedDate,
                    notes: notes
                })
            })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    if (data.success) {
                        // Update stats
                        document.getElementById('statFasting').textContent = data.total_fasting;
                        document.getElementById('statMissed').textContent = data.total_missed;
                        document.getElementById('statRemaining').textContent = 30 - (data.total_fasting + data.total_missed);
                        document.getElementById('progressBar').style.width = (data.total_fasting / 30 * 100) + '%';

                        // Update current element in grid
                        const el = document.querySelector(`.cal-day[data-day="${selectedDay}"]`);
                        if (el) {
                            el.classList.remove('fasted', 'missed');
                            el.classList.add(isFasting ? 'fasted' : 'missed');
                            el.dataset.status = isFasting ? '1' : '0';
                            el.dataset.notes = notes;
                        }

                        // Update button active state
                        document.getElementById('btnNo').classList.toggle('active', isFasting === 0);
                        document.getElementById('btnYes').classList.toggle('active', isFasting === 1);
                    }
                })
                .catch(err => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    console.error(err);
                    alert('Gagal menyimpan data');
                });
        }

        // Initialize today selection
        document.addEventListener('DOMContentLoaded', function () {
            const todayEl = document.querySelector('.cal-day.today');
            if (todayEl) selectDay(todayEl);
        });
    </script>
@endsection