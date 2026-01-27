@extends('layout.master')

@section('title')
    Absen Mandiri - {{ ucfirst($mode) }}
@endsection

@section('heading')
    Absen Mandiri
@endsection

@section('content')
    <div class="attendance-page">
        {{-- BACKGROUND ELEMENTS --}}
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>

        {{-- MAIN CONTAINER --}}
        <div class="attendance-container">
            {{-- HEADER --}}
            <div class="attendance-header">
                <div class="header-left">
                    <a href="{{ route('dashboard') }}" class="back-btn">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                </div>
                <div class="header-center">
                    <h1>{{ ucfirst($mode) }}</h1>
                    <p>{{ \Carbon\Carbon::now($branchTimezone ?? 'Asia/Jakarta')->translatedFormat('l, d M Y') }}</p>
                </div>
                <div class="header-right">
                    @if(Auth::user()->use_face_recognition)
                        <div class="ai-badge" id="ai-status-badge">
                            <span class="pulse-dot"></span>
                            <span class="badge-text">AI</span>
                        </div>
                    @else
                        <div class="ai-badge manual">
                            <i class="mdi mdi-camera"></i>
                        </div>
                    @endif
                </div>
            </div>

            {{-- CLOCK IN INFO (PULANG MODE) --}}
            @if ($mode == 'pulang' && isset($attendance))
                <div class="checkin-info">
                    <i class="mdi mdi-login"></i>
                    <span>Masuk pukul
                        <strong>{{ \Carbon\Carbon::parse($attendance->check_in_time)->timezone($branchTimezone ?? 'Asia/Jakarta')->format('H:i') }}</strong></span>
                </div>
            @endif

            {{-- CAMERA CARD --}}
            <div class="camera-card">
                <form action="{{ route('self.attend.store') }}" method="POST" enctype="multipart/form-data"
                    id="attendance-form">
                    @csrf
                    @if (isset($attendance) && $attendance)
                        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                    @endif
                    <input type="file" name="photo" id="photo-input-hidden" class="d-none" accept="image/jpeg">

                    {{-- CAMERA VIEW --}}
                    <div class="camera-wrapper">
                        <video id="video-feed" autoplay muted playsinline></video>

                        {{-- AI SCAN LINE --}}
                        @if(Auth::user()->use_face_recognition)
                            <div class="scan-effect d-none" id="ai-scan-line"></div>
                        @endif

                        {{-- FACE FRAME --}}
                        <div class="face-frame" id="face-frame">
                            <svg viewBox="0 0 200 260" class="frame-svg">
                                <path class="frame-path"
                                    d="M40,10 L10,10 L10,50 M160,10 L190,10 L190,50 M10,210 L10,250 L40,250 M190,210 L190,250 L160,250"
                                    fill="none" stroke-width="4" stroke-linecap="round" />
                            </svg>
                        </div>

                        {{-- STATUS OVERLAY --}}
                        <div class="status-overlay">
                            <div class="status-badge" id="face-status">
                                <i class="mdi mdi-camera-off"></i>
                                <span>Ketuk untuk mulai</span>
                            </div>
                        </div>

                        {{-- START OVERLAY --}}
                        <div class="start-overlay" id="start-screen">
                            <div class="start-content">
                                <div class="camera-icon-wrapper">
                                    <i class="mdi mdi-camera-iris"></i>
                                </div>
                                <button type="button" id="start-camera-btn" class="start-btn">
                                    <i class="mdi mdi-play"></i>
                                    <span>Buka Kamera</span>
                                </button>
                                <div class="loading-state d-none" id="loading-state">
                                    <div class="spinner"></div>
                                    <span>Menyiapkan...</span>
                                </div>
                            </div>
                        </div>

                        {{-- PREVIEW OVERLAY --}}
                        <div class="preview-overlay d-none" id="result-screen">
                            <img id="preview-image" src="" alt="Preview">
                            <div class="preview-actions">
                                <button type="button" id="retake-btn" class="retake-btn">
                                    <i class="mdi mdi-refresh"></i>
                                    <span>Ulangi</span>
                                </button>
                                <div class="success-badge">
                                    <i class="mdi mdi-check-circle"></i>
                                    <span>Siap</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CAPTURE BUTTON --}}
                    <div class="capture-section">
                        <button type="button" id="capture-btn" class="capture-btn" disabled>
                            <div class="capture-inner"></div>
                        </button>
                    </div>

                    {{-- LOCATION CARD --}}
                    <div class="info-card location-card" onclick="getLocation(true)">
                        <div class="info-icon">
                            <i class="mdi mdi-map-marker"></i>
                        </div>
                        <div class="info-content">
                            <label>Lokasi</label>
                            <span id="coordinates-display">Mencari...</span>
                        </div>
                        <div class="info-badge" id="gps-accuracy-badge">
                            <i class="mdi mdi-loading mdi-spin"></i>
                        </div>
                    </div>

                    {{-- NOTES CARD --}}
                    <div class="info-card notes-card">
                        <div class="info-icon">
                            <i class="mdi mdi-text"></i>
                        </div>
                        <div class="info-content full">
                            <label>Catatan</label>
                            <textarea name="notes" id="notes-input" placeholder="Opsional..."></textarea>
                        </div>
                    </div>

                    {{-- HIDDEN INPUTS --}}
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" id="accuracy" name="accuracy">

                    {{-- SUBMIT SLIDER --}}
                    <div class="submit-section">
                        <div class="slide-track disabled" id="slide-track">
                            <div class="slide-text" id="slide-text">GESER UNTUK ABSEN</div>
                            <div class="slide-progress" id="slide-progress"></div>
                            <div class="slide-thumb" id="slide-thumb">
                                <i class="mdi mdi-chevron-double-right"></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <canvas id="capture-canvas" class="d-none"></canvas>
@endsection

@push('styles')
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.15);
        }

        /* === PAGE LAYOUT === */
        .attendance-page {
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-bottom: 2rem;
        }

        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            z-index: -2;
        }

        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            z-index: -1;
        }

        .attendance-container {
            max-width: 420px;
            margin: 0 auto;
            padding: 1rem;
        }

        /* === HEADER === */
        .attendance-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            margin-bottom: 1rem;
        }

        .back-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: white;
            font-size: 1.25rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .header-center {
            text-align: center;
            flex: 1;
        }

        .header-center h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .header-center p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            margin: 0.25rem 0 0;
        }

        .ai-badge {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 14px;
            position: relative;
        }

        .ai-badge .pulse-dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .ai-badge .badge-text {
            position: absolute;
            bottom: -18px;
            font-size: 0.65rem;
            color: var(--success);
            font-weight: 600;
        }

        .ai-badge.manual {
            background: rgba(245, 158, 11, 0.2);
            border-color: rgba(245, 158, 11, 0.3);
            color: var(--warning);
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        /* === CHECKIN INFO === */
        .checkin-info {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--primary-light);
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .checkin-info i {
            font-size: 1.1rem;
        }

        .checkin-info strong {
            color: white;
        }

        /* === CAMERA CARD === */
        .camera-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* === CAMERA WRAPPER === */
        .camera-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 3/4;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        #video-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
            opacity: 0;
            transition: opacity 0.4s;
        }

        #video-feed.ready {
            opacity: 1;
        }

        /* === SCAN EFFECT === */
        .scan-effect {
            position: absolute;
            left: 10%;
            right: 10%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--success), transparent);
            box-shadow: 0 0 15px var(--success);
            animation: scan 2.5s ease-in-out infinite;
            z-index: 5;
        }

        @keyframes scan {

            0%,
            100% {
                top: 15%;
                opacity: 0;
            }

            10%,
            90% {
                opacity: 1;
            }

            50% {
                top: 85%;
            }
        }

        /* === FACE FRAME === */
        .face-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 65%;
            aspect-ratio: 10/13;
            z-index: 4;
            transition: all 0.3s;
        }

        .frame-svg {
            width: 100%;
            height: 100%;
        }

        .frame-path {
            stroke: rgba(255, 255, 255, 0.4);
            transition: all 0.3s;
        }

        .face-frame.active .frame-path {
            stroke: var(--success);
            filter: drop-shadow(0 0 8px var(--success));
        }

        /* === STATUS OVERLAY === */
        .status-overlay {
            position: absolute;
            top: 1rem;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            z-index: 6;
        }

        .status-badge {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            padding: 0.5rem 1rem;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-badge i {
            font-size: 1rem;
        }

        .status-badge.success {
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-badge.warning {
            color: var(--warning);
            border-color: rgba(245, 158, 11, 0.3);
        }

        /* === START OVERLAY === */
        .start-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .start-content {
            text-align: center;
        }

        .camera-icon-wrapper {
            width: 80px;
            height: 80px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: float 3s ease-in-out infinite;
        }

        .camera-icon-wrapper i {
            font-size: 2.5rem;
            color: var(--primary-light);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .start-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
            border-radius: 16px;
            padding: 1rem 2rem;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .start-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.5);
        }

        .start-btn:active {
            transform: scale(0.98);
        }

        .loading-state {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* === PREVIEW OVERLAY === */
        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #000;
            z-index: 10;
        }

        .preview-overlay img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .preview-actions {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .retake-btn {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            padding: 0.6rem 1.25rem;
            color: white;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .retake-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .success-badge {
            background: var(--success);
            border-radius: 30px;
            padding: 0.6rem 1.25rem;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        /* === CAPTURE BUTTON === */
        .capture-section {
            display: flex;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .capture-btn {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            background: transparent;
            padding: 4px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .capture-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .capture-inner {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transition: all 0.3s;
        }

        .capture-btn.ready {
            border-color: var(--primary-light);
        }

        .capture-btn.ready .capture-inner {
            background: var(--primary);
        }

        .capture-btn.ready:active {
            transform: scale(0.95);
        }

        .capture-btn.ready:active .capture-inner {
            transform: scale(0.85);
        }

        /* === INFO CARDS === */
        .info-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .info-card:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: rgba(99, 102, 241, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
            min-width: 0;
        }

        .info-content.full {
            flex: 1;
        }

        .info-content label {
            display: block;
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.15rem;
        }

        .info-content span {
            display: block;
            font-size: 0.9rem;
            color: white;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-badge {
            padding: 0.35rem 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .info-badge.success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .info-badge.error {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* === NOTES === */
        .notes-card {
            cursor: default;
        }

        .notes-card textarea {
            width: 100%;
            background: transparent;
            border: none;
            color: white;
            font-size: 0.9rem;
            resize: none;
            height: 2.5rem;
            outline: none;
        }

        .notes-card textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        /* === SUBMIT SLIDER === */
        .submit-section {
            margin-top: 1rem;
        }

        .slide-track {
            position: relative;
            height: 56px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .slide-track.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .slide-track.active {
            background: rgba(99, 102, 241, 0.2);
        }

        .slide-track.submitted {
            background: var(--success);
        }

        .slide-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s;
            white-space: nowrap;
        }

        .slide-track.active .slide-text {
            color: var(--primary-light);
        }

        .slide-progress {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 28px;
            width: 0;
            transition: width 0.1s;
            opacity: 0.3;
        }

        .slide-thumb {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            cursor: grab;
            touch-action: none;
            transition: transform 0.1s;
            z-index: 2;
        }

        .slide-track.submitted .slide-thumb {
            background: white;
            color: var(--success);
        }

        /* === RESPONSIVE === */
        @media (max-width: 380px) {
            .attendance-container {
                padding: 0.75rem;
            }

            .camera-card {
                padding: 1rem;
                border-radius: 20px;
            }

            .camera-wrapper {
                border-radius: 16px;
            }

            .capture-btn {
                width: 64px;
                height: 64px;
            }

            .capture-inner {
                transform: scale(0.9);
            }
        }
    </style>
@endpush

@push('scripts')
    {{-- FACE API --}}
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === OPTIMIZED CONFIG FOR LOW-END DEVICES ===
            const AI_INPUT_SIZE = 160;  // Reduced from 224 for faster detection
            const AI_SCORE_THRESHOLD = 0.45;  // Lower threshold for easier detection
            const DETECTION_INTERVAL = 200;  // Faster response

            const useAI = {{ Auth::user()->use_face_recognition ? 'true' : 'false' }};
            const assetPath = "{{ asset('public/models') }}";

            // DOM Elements
            const videoFeed = document.getElementById('video-feed');
            const startScreen = document.getElementById('start-screen');
            const resultScreen = document.getElementById('result-screen');
            const startBtn = document.getElementById('start-camera-btn');
            const loadingState = document.getElementById('loading-state');
            const captureBtn = document.getElementById('capture-btn');
            const retakeBtn = document.getElementById('retake-btn');
            const previewImage = document.getElementById('preview-image');
            const photoInputHidden = document.getElementById('photo-input-hidden');
            const canvas = document.getElementById('capture-canvas');

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const accInput = document.getElementById('accuracy');
            const coordDisplay = document.getElementById('coordinates-display');
            const accBadge = document.getElementById('gps-accuracy-badge');

            const faceStatus = document.getElementById('face-status');
            const faceFrame = document.getElementById('face-frame');
            const aiScanLine = document.getElementById('ai-scan-line');
            const slideTrack = document.getElementById('slide-track');
            const slideThumb = document.getElementById('slide-thumb');
            const slideText = document.getElementById('slide-text');
            const slideProgress = document.getElementById('slide-progress');
            const form = document.getElementById('attendance-form');

            // State
            let streamRef = null;
            let isProcessingAI = false;
            let isFaceValid = false;
            let modelsLoaded = false;
            let animationFrameId = null;

            // === PRE-LOAD AI MODEL ON PAGE LOAD ===
            if (useAI && typeof faceapi !== 'undefined') {
                console.log('Pre-loading AI model in background...');
                faceapi.nets.tinyFaceDetector.loadFromUri(assetPath)
                    .then(() => {
                        modelsLoaded = true;
                        console.log('AI model pre-loaded successfully');
                    })
                    .catch(err => console.warn('Background model load failed:', err));
            } else if (useAI) {
                // Wait for face-api script to load
                const checkFaceApi = setInterval(() => {
                    if (typeof faceapi !== 'undefined') {
                        clearInterval(checkFaceApi);
                        faceapi.nets.tinyFaceDetector.loadFromUri(assetPath)
                            .then(() => { modelsLoaded = true; console.log('AI model pre-loaded'); })
                            .catch(console.warn);
                    }
                }, 100);
            }

            // === START CAMERA ===
            startBtn.addEventListener('click', async () => {
                startBtn.classList.add('d-none');
                loadingState.classList.remove('d-none');
                getLocation(true);

                if (useAI && !modelsLoaded) {
                    try {
                        await faceapi.nets.tinyFaceDetector.loadFromUri(assetPath);
                        modelsLoaded = true;
                    } catch (err) {
                        console.error('AI load failed:', err);
                        alert('Gagal memuat AI. Beralih ke mode manual.');
                        initCamera(false);
                        return;
                    }
                }

                initCamera(useAI && modelsLoaded);
            });

            function initCamera(enableAI = true) {
                // Lower resolution for faster AI processing
                const constraints = {
                    video: {
                        facingMode: 'user',
                        width: { ideal: enableAI ? 320 : 640 },
                        height: { ideal: enableAI ? 240 : 480 },
                        frameRate: { ideal: 24 }
                    },
                    audio: false
                };

                navigator.mediaDevices.getUserMedia(constraints)
                    .then(stream => {
                        streamRef = stream;
                        videoFeed.srcObject = stream;

                        videoFeed.onloadeddata = () => {
                            videoFeed.play();
                            startScreen.classList.add('d-none');
                            videoFeed.classList.add('ready');

                            if (enableAI) {
                                if (aiScanLine) aiScanLine.classList.remove('d-none');
                                startFaceDetectionLoop();
                            } else {
                                setReadyToCapture(true, 'Mode Manual Siap', 'success');
                            }
                        };
                    })
                    .catch(err => {
                        alert('Gagal akses kamera: ' + err.message);
                        startBtn.classList.remove('d-none');
                        loadingState.classList.add('d-none');
                    });
            }

            // === OPTIMIZED AI DETECTION ===
            function startFaceDetectionLoop() {
                const options = new faceapi.TinyFaceDetectorOptions({
                    inputSize: AI_INPUT_SIZE,
                    scoreThreshold: AI_SCORE_THRESHOLD
                });

                const runDetection = async () => {
                    if (!streamRef || videoFeed.paused || videoFeed.ended) return;
                    if (isProcessingAI) {
                        scheduleNextDetection();
                        return;
                    }

                    isProcessingAI = true;

                    try {
                        const detections = await faceapi.detectAllFaces(videoFeed, options);

                        if (detections.length > 0 && detections[0].score > AI_SCORE_THRESHOLD) {
                            if (!isFaceValid) {
                                isFaceValid = true;
                                faceFrame.classList.add('active');
                                setReadyToCapture(true, 'Wajah Terdeteksi', 'success');
                            }
                        } else {
                            if (isFaceValid) {
                                isFaceValid = false;
                                faceFrame.classList.remove('active');
                                setReadyToCapture(false, 'Posisikan Wajah', 'warning');
                            }
                        }
                    } catch (e) {
                        console.log('Detection skipped');
                    }

                    isProcessingAI = false;
                    scheduleNextDetection();
                };

                const scheduleNextDetection = () => {
                    if ('requestIdleCallback' in window) {
                        requestIdleCallback(() => {
                            setTimeout(() => {
                                animationFrameId = requestAnimationFrame(runDetection);
                            }, DETECTION_INTERVAL);
                        }, { timeout: 500 });
                    } else {
                        setTimeout(() => {
                            animationFrameId = requestAnimationFrame(runDetection);
                        }, DETECTION_INTERVAL);
                    }
                };

                runDetection();
            }

            function setReadyToCapture(ready, message, type) {
                if (ready) {
                    captureBtn.disabled = false;
                    captureBtn.classList.add('ready');
                    faceStatus.innerHTML = `<i class="mdi mdi-check-circle"></i><span>${message}</span>`;
                    faceStatus.className = 'status-badge success';
                } else {
                    captureBtn.disabled = true;
                    captureBtn.classList.remove('ready');
                    faceStatus.innerHTML = `<i class="mdi mdi-face-recognition"></i><span>${message}</span>`;
                    faceStatus.className = 'status-badge ' + type;
                }
            }

            // === CAPTURE ===
            captureBtn.addEventListener('click', () => {
                if (useAI && !isFaceValid) return;
                getLocation(true);

                captureBtn.style.transform = 'scale(0.9)';
                setTimeout(() => captureBtn.style.transform = '', 100);

                const width = videoFeed.videoWidth;
                const height = videoFeed.videoHeight;
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(videoFeed, -width, 0, width, height);
                ctx.restore();

                canvas.toBlob(blob => {
                    const file = new File([blob], 'attendance_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    photoInputHidden.files = dt.files;

                    previewImage.src = URL.createObjectURL(blob);
                    stopCamera();
                    resultScreen.classList.remove('d-none');
                    checkGlobalValidity();
                }, 'image/jpeg', 0.85);
            });

            function stopCamera() {
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
                if (streamRef) {
                    streamRef.getTracks().forEach(t => t.stop());
                    streamRef = null;
                }
            }

            retakeBtn.addEventListener('click', () => {
                resultScreen.classList.add('d-none');
                photoInputHidden.value = '';
                isFaceValid = false;
                initCamera(useAI && modelsLoaded);
                checkGlobalValidity();
            });

            // === GPS ===
            function getLocation(highAccuracy = true) {
                if (!navigator.geolocation) {
                    coordDisplay.textContent = 'GPS tidak didukung';
                    return;
                }

                coordDisplay.textContent = 'Mencari...';
                accBadge.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i>';
                accBadge.className = 'info-badge';

                navigator.geolocation.getCurrentPosition(
                    pos => {
                        const { latitude, longitude, accuracy } = pos.coords;
                        latInput.value = latitude;
                        lngInput.value = longitude;
                        accInput.value = accuracy;

                        coordDisplay.textContent = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                        accBadge.textContent = `±${Math.round(accuracy)}m`;
                        accBadge.className = 'info-badge success';
                        checkGlobalValidity();
                    },
                    err => {
                        if (highAccuracy && err.code === 3) {
                            getLocation(false);
                            return;
                        }
                        coordDisplay.textContent = err.code === 1 ? 'Izin ditolak' : 'Gagal GPS';
                        accBadge.textContent = 'Error';
                        accBadge.className = 'info-badge error';
                    },
                    { enableHighAccuracy: highAccuracy, timeout: 15000, maximumAge: 0 }
                );
            }

            getLocation(true);

            function checkGlobalValidity() {
                const hasPhoto = photoInputHidden.files.length > 0;
                const hasLoc = latInput.value !== '';

                if (hasPhoto && hasLoc) {
                    slideTrack.classList.remove('disabled');
                    slideTrack.classList.add('active');
                    slideText.textContent = 'GESER UNTUK ABSEN';
                } else {
                    slideTrack.classList.add('disabled');
                    slideTrack.classList.remove('active');
                    slideText.textContent = hasPhoto ? 'TUNGGU GPS...' : 'AMBIL FOTO DULU';
                }
            }

            // === SLIDER ===
            let isDragging = false, startX, currentX, maxSlide;
            const updateSliderWidth = () => { maxSlide = slideTrack.offsetWidth - slideThumb.offsetWidth - 8; };
            window.addEventListener('resize', () => setTimeout(updateSliderWidth, 100));
            updateSliderWidth();

            const handleStart = e => {
                if (slideTrack.classList.contains('disabled')) return;
                isDragging = true;
                startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                slideThumb.style.transition = 'none';
            };

            const handleMove = e => {
                if (!isDragging) return;
                const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                let move = clientX - startX;
                move = Math.max(0, Math.min(move, maxSlide));
                currentX = move;
                slideThumb.style.transform = `translateX(${move}px)`;
                slideProgress.style.width = `${move + 52}px`;
                slideText.style.opacity = 1 - (move / maxSlide);
            };

            const handleEnd = () => {
                if (!isDragging) return;
                isDragging = false;

                if (currentX >= maxSlide * 0.85) {
                    slideThumb.style.transform = `translateX(${maxSlide}px)`;
                    slideProgress.style.width = '100%';
                    slideTrack.classList.add('submitted');
                    slideThumb.innerHTML = '<i class="mdi mdi-check"></i>';
                    slideText.textContent = 'MENGIRIM...';
                    slideText.style.opacity = '1';

                    if (!form.classList.contains('submitting')) {
                        form.classList.add('submitting');
                        if (latInput.value === '') {
                            alert('Lokasi belum ditemukan');
                            resetSlider();
                        } else {
                            form.submit();
                        }
                    }
                } else {
                    resetSlider();
                }
            };

            const resetSlider = () => {
                slideThumb.style.transition = 'transform 0.3s';
                slideThumb.style.transform = 'translateX(0)';
                slideProgress.style.width = '0';
                slideText.style.opacity = '1';
                slideTrack.classList.remove('submitted');
                slideThumb.innerHTML = '<i class="mdi mdi-chevron-double-right"></i>';
                form.classList.remove('submitting');
            };

            slideThumb.addEventListener('mousedown', handleStart);
            slideThumb.addEventListener('touchstart', handleStart, { passive: true });
            window.addEventListener('mousemove', handleMove);
            window.addEventListener('touchmove', handleMove, { passive: true });
            window.addEventListener('mouseup', handleEnd);
            window.addEventListener('touchend', handleEnd);
        });
    </script>
@endpush