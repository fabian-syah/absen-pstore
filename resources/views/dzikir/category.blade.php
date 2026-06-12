@extends('layout.master')

@section('title', 'Zikir ' . $categoryName)

@push('styles')
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#0a1f14">
<meta name="mobile-web-app-capable" content="yes">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    /* ---- NATIVE SCROLL TO HIDE BROWSER UI ---- */
    html, body {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #0a1f14 !important;
    }
    .container-scroller, .page-body-wrapper, .main-panel, .content-wrapper {
        overflow: visible !important;
        height: auto !important;
        min-height: 0 !important;
        transform: none !important;
        -webkit-transform: none !important;
        filter: none !important;
        -webkit-filter: none !important;
        perspective: none !important;
        backdrop-filter: none !important;
        will-change: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }

    .navbar, .sidebar, .mobile-bottom-nav, footer, .footer {
        display: none !important;
    }
    .main-panel {
        margin-left: 0 !important;
        width: 100% !important;
        min-height: 0 !important;
    }
    .page-body-wrapper { padding-top: 0 !important; }
    .content-wrapper { padding: 0 !important; }

    .zikir-page {
        font-family: "Manrope", -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
        position: relative;
        min-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        --zk-bg: #0a1f14;
        --zk-card-bg: #14281d;
        --zk-primary: #1ed760;
        --zk-text-main: #ffffff;
        --zk-text-muted: #a0aab2;
    }

    .zk-bg-fixed {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        transform: scale(1.1) !important;
        -webkit-transform: scale(1.1) !important;
        background-color: var(--zk-bg);
        background-image:
            linear-gradient(180deg, rgba(10, 31, 20, 0.15) 0%, rgba(10, 31, 20, 0.1) 50%, rgba(10, 31, 20, 0.3) 100%),
            url('{{ asset("public/images/mosque-bg.png") }}');
        background-size: cover;
        background-position: center bottom;
        background-repeat: no-repeat;
        filter: blur(6px);
        -webkit-filter: blur(6px);
        pointer-events: none;
        z-index: 0;
    }

    .zikir-page .material-symbols-outlined {
        font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24;
    }
    .zikir-page .material-symbols-outlined.filled {
        font-variation-settings: "FILL" 1, "wght" 300, "GRAD" 0, "opsz" 24;
    }

    .zk-header {
        position: fixed;
        top: 0; left: 0; right: 0;
        padding: 16px 16px 12px 16px;
        padding-top: calc(16px + env(safe-area-inset-top));
        z-index: 10;
        background: rgba(10, 31, 20, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
    }

    .zk-header-title {
        color: var(--zk-text-main);
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 1px;
        margin: 0 0 10px 0;
        text-transform: uppercase;
    }

    .zk-header-line {
        height: 1px;
        background: rgba(255, 255, 255, 0.2);
        width: 100%;
    }

    .zk-main {
        position: relative;
        z-index: 1;
        padding: calc(75px + env(safe-area-inset-top)) 0 16px 0; /* No side padding - cards edge to edge */
        display: flex;
        flex-direction: column;
        gap: 4px; /* Very tight gap */
        max-width: 800px;
        margin: 0 auto;
    }

    /* ===== CAMPAIGN CAROUSEL ===== */
    .zk-carousel-wrapper {
        position: relative;
        margin: 0 8px 8px 8px;
        overflow: hidden;
        border-radius: 20px;
    }
    .zk-carousel-track {
        display: flex;
        transition: transform 0.4s ease;
        will-change: transform;
    }
    .zk-carousel-slide {
        flex: 0 0 100%;
        min-width: 100%;
        background: var(--zk-card-bg);
        border-radius: 20px;
        padding: 20px;
        box-sizing: border-box;
    }
    .zk-campaign-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    .zk-campaign-icon {
        background: rgba(30, 215, 96, 0.2);
        color: var(--zk-primary);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .zk-campaign-title {
        color: var(--zk-text-main);
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }
    .zk-campaign-number {
        color: var(--zk-primary);
        font-size: 36px;
        font-weight: 800;
        margin: 4px 0;
        line-height: 1.1;
        letter-spacing: -1px;
    }
    .zk-campaign-target {
        color: var(--zk-text-muted);
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 10px;
    }
    /* Progress bar */
    .zk-progress-bar-bg {
        background: rgba(255, 255, 255, 0.1);
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .zk-progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #1ed760, #4ade80);
        border-radius: 4px;
        transition: width 0.6s ease;
    }
    .zk-campaign-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .zk-campaign-label {
        color: var(--zk-text-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .zk-campaign-percent {
        color: var(--zk-text-main);
        font-size: 14px;
        font-weight: 700;
    }
    /* Carousel dots */
    .zk-carousel-dots {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 14px;
    }
    .zk-carousel-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        transition: background 0.3s, width 0.3s;
    }
    .zk-carousel-dot.active {
        width: 18px;
        border-radius: 3px;
        background: #fff;
    }

    /* List Card */
    .zk-list-card {
        background: var(--zk-card-bg);
        border-radius: 10px;
        padding: 16px;
        margin: 0 8px; /* Small side margin for breathing room */
        display: flex;
        align-items: flex-start;
        gap: 16px;
        text-decoration: none;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .zk-list-card:active {
        transform: scale(0.98);
        background: rgba(20, 40, 29, 0.8);
    }
    
    /* Hexagon Number */
    .zk-hex-number {
        background-color: var(--zk-primary);
        color: var(--zk-bg);
        width: 30px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        position: relative;
        clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .zk-list-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .zk-list-title {
        color: var(--zk-text-main);
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }
    .zk-list-latin {
        color: var(--zk-text-muted);
        font-size: 14px;
        line-height: 1.4;
        margin: 0;
    }
    .zk-list-progress {
        color: var(--zk-text-muted);
        font-size: 12px;
        font-weight: 500;
        margin: 0;
        margin-top: 4px;
    }

    .zk-list-action {
        color: var(--zk-text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: none;
        background: transparent;
        cursor: pointer;
        outline: none;
        transition: color 0.2s;
    }
    .zk-list-action:hover, .zk-list-action.active {
        color: #f1c40f; /* Star color */
    }

    /* Bottom Nav */
    .zk-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: none;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 24px 24px;
        padding-bottom: calc(24px + env(safe-area-inset-bottom));
        z-index: 10;
        pointer-events: none;
    }
    .zk-nav-btn {
        pointer-events: auto;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: background 0.2s;
    }
    .zk-nav-btn:active {
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* ---- RESPONSIVE: Small phones (< 380px) ---- */
    @media (max-width: 380px) {
        .zk-header { padding: 12px 12px 8px 12px; padding-top: calc(12px + env(safe-area-inset-top)); }
        .zk-header-title { font-size: 12px; }
        .zk-main { padding: calc(65px + env(safe-area-inset-top)) 0 16px 0; gap: 3px; }
        .zk-list-card { padding: 12px; margin: 0 6px; gap: 10px; }
        .zk-list-title { font-size: 15px; }
        .zk-list-latin { font-size: 12px; }
        .zk-list-progress { font-size: 11px; }
        .zk-hex-number { width: 26px; height: 30px; font-size: 12px; }
        .zk-list-action { width: 34px; height: 34px; }
        .zk-carousel-wrapper { margin: 0 6px 6px 6px; border-radius: 14px; }
        .zk-carousel-slide { padding: 14px; border-radius: 14px; }
        .zk-campaign-title { font-size: 14px; }
        .zk-campaign-number { font-size: 28px; }
        .zk-bottom-nav { padding: 16px 16px; padding-bottom: calc(16px + env(safe-area-inset-bottom)); }
        .zk-nav-btn { width: 38px; height: 38px; }
    }

    /* ---- RESPONSIVE: Regular phones (381px - 767px) ---- */
    @media (min-width: 381px) and (max-width: 767px) {
        .zk-main { padding: calc(75px + env(safe-area-inset-top)) 0 16px 0; gap: 4px; }
        .zk-list-card { margin: 0 8px; }
        .zk-carousel-wrapper { margin: 0 8px 8px 8px; }
    }

    /* ---- RESPONSIVE: Tablets (768px - 1023px) ---- */
    @media (min-width: 768px) {
        .zk-main { padding: calc(90px + env(safe-area-inset-top)) 0 20px 0; gap: 6px; max-width: 600px; }
        .zk-header { padding: 20px 24px 12px 24px; padding-top: calc(20px + env(safe-area-inset-top)); }
        .zk-header-title { font-size: 16px; }
        .zk-list-card { padding: 20px; margin: 0 12px; border-radius: 14px; }
        .zk-list-title { font-size: 20px; }
        .zk-list-latin { font-size: 15px; }
        .zk-hex-number { width: 34px; height: 38px; font-size: 16px; }
        .zk-carousel-wrapper { margin: 0 12px 10px 12px; border-radius: 22px; }
        .zk-carousel-slide { padding: 24px; border-radius: 22px; }
        .zk-campaign-title { font-size: 18px; }
        .zk-campaign-number { font-size: 44px; }
        .zk-bottom-nav { padding: 24px 40px; padding-bottom: calc(24px + env(safe-area-inset-bottom)); }
        .zk-nav-btn { width: 48px; height: 48px; }
    }

    /* ---- RESPONSIVE: Desktop (1024px+) ---- */
    @media (min-width: 1024px) {
        .zk-main { padding-top: calc(100px + env(safe-area-inset-top)); max-width: 640px; gap: 8px; }
        .zk-header { padding: 24px 32px 14px 32px; padding-top: calc(24px + env(safe-area-inset-top)); }
        .zk-header-title { font-size: 16px; }
        .zk-list-card { padding: 22px; margin: 0 16px; border-radius: 16px; }
        .zk-list-title { font-size: 20px; }
        .zk-hex-number { width: 36px; height: 40px; font-size: 16px; }
        .zk-carousel-wrapper { margin: 0 16px 12px 16px; border-radius: 24px; }
        .zk-carousel-slide { padding: 28px; border-radius: 24px; }
        .zk-campaign-number { font-size: 48px; }
        .zk-bottom-nav { padding: 32px 48px; padding-bottom: calc(32px + env(safe-area-inset-bottom)); }
        .zk-nav-btn { width: 50px; height: 50px; }
    }

    /* ---- RESPONSIVE: Large desktop (1440px+) ---- */
    @media (min-width: 1440px) {
        .zk-main { max-width: 720px; }
        .zk-list-card { padding: 24px; margin: 0 20px; }
        .zk-carousel-wrapper { margin: 0 20px 14px 20px; }
        .zk-carousel-slide { padding: 32px; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper zikir-page">
    <div class="zk-bg-fixed"></div>
    <header class="zk-header">
        <h1 class="zk-header-title">ZIKIR {{ strtoupper($categoryName) }}</h1>
        <div class="zk-header-line"></div>
    </header>

    <div class="zk-main">
        {{-- Campaign Carousel --}}
        @if($campaigns->count() > 0)
        <div class="zk-carousel-wrapper" id="campaignCarousel">
            <div class="zk-carousel-track" id="carouselTrack">
                @foreach($campaigns as $campaign)
                <div class="zk-carousel-slide">
                    @if($campaign->zikir_id)
                        @php
                            $playCat = 'umum';
                            if($campaign->zikir && is_array($campaign->zikir->category) && count($campaign->zikir->category) > 0) {
                                $playCat = $campaign->zikir->category[0];
                            }
                        @endphp
                        <a href="{{ route('dzikir.play', ['category' => $playCat, 'id' => $campaign->zikir_id]) }}" style="text-decoration: none; color: inherit; display: block; width: 100%; height: 100%;">
                    @endif

                    <div class="zk-campaign-header">
                        <div class="zk-campaign-icon">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <h3 class="zk-campaign-title">{{ $campaign->title }} {{ $campaign->emoji }}</h3>
                    </div>
                    <h2 class="zk-campaign-number">{{ number_format($campaign->current_count, 0, ',', '.') }}</h2>
                    <div class="zk-campaign-target">TARGET: {{ number_format($campaign->target, 0, ',', '.') }}</div>
                    <div class="zk-progress-bar-bg">
                        <div class="zk-progress-bar-fill" style="width: {{ $campaign->progress_percent }}%"></div>
                    </div>
                    <div class="zk-campaign-footer">
                        <span class="zk-campaign-label">LAGI</span>
                        <span class="zk-campaign-percent">{{ $campaign->progress_percent }}%</span>
                    </div>

                    @if($campaign->zikir_id)
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
            @if($campaigns->count() > 1)
            <div class="zk-carousel-dots" id="carouselDots">
                @foreach($campaigns as $i => $c)
                <div class="zk-carousel-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- Zikir List --}}
        @foreach($zikirs as $index => $zikir)
            @php
                $target = $zikir->default_target ?? 33;
                $progress = isset($activities[$zikir->id]) ? $activities[$zikir->id]->total_count : 0;
                $isFavorite = in_array($zikir->id, $favorites);
            @endphp
            <a href="{{ route('dzikir.play', ['category' => $category, 'id' => $zikir->id]) }}" class="zk-list-card">
                <div class="zk-hex-number">{{ $index + 1 }}</div>
                <div class="zk-list-content">
                    <h3 class="zk-list-title">{{ $zikir->title }}</h3>
                    <p class="zk-list-latin">{{ $zikir->latin_text }}</p>
                    <p class="zk-list-progress">{{ $progress }} / {{ $target }}</p>
                </div>
                <button class="zk-list-action {{ $isFavorite ? 'active' : '' }}" onclick="event.preventDefault(); toggleFavorite({{ $zikir->id }}, this)">
                    <span class="material-symbols-outlined {{ $isFavorite ? 'filled' : '' }}">star</span>
                </button>
            </a>
        @endforeach
    </div>

    {{-- Bottom Nav Controls --}}
    <div class="zk-bottom-nav">
        <a href="{{ route('dzikir.index') }}" class="zk-nav-btn">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
    </div>
</div>

@push('scripts')
<script>
    function toggleFavorite(zikirId, btn) {
        // Prevent multiple clicks
        if (btn.classList.contains('loading')) return;
        btn.classList.add('loading');
        
        fetch('{{ route('dzikir.toggle-favorite') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ zikir_id: zikirId })
        })
        .then(response => response.json())
        .then(data => {
            btn.classList.remove('loading');
            if (data.success) {
                const icon = btn.querySelector('.material-symbols-outlined');
                if (data.is_favorite) {
                    btn.classList.add('active');
                    icon.classList.add('filled');
                } else {
                    btn.classList.remove('active');
                    icon.classList.remove('filled');
                }
            } else {
                alert('Gagal memperbarui favorit.');
            }
        })
        .catch(error => {
            btn.classList.remove('loading');
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem.');
        });
    }

    // ===== CAMPAIGN CAROUSEL =====
    (function() {
        const track = document.getElementById('carouselTrack');
        const dotsContainer = document.getElementById('carouselDots');
        if (!track) return;

        const slides = track.querySelectorAll('.zk-carousel-slide');
        const totalSlides = slides.length;
        if (totalSlides <= 1) return;

        let currentIndex = 0;
        let autoSlideTimer = null;
        let startX = 0;
        let isDragging = false;

        function goToSlide(index) {
            currentIndex = ((index % totalSlides) + totalSlides) % totalSlides;
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateDots();
        }

        function updateDots() {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('.zk-carousel-dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        function startAutoSlide() {
            stopAutoSlide();
            autoSlideTimer = setInterval(() => goToSlide(currentIndex + 1), 4000);
        }

        function stopAutoSlide() {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
        }

        // Touch events for swipe
        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            stopAutoSlide();
        }, { passive: true });

        track.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) goToSlide(currentIndex + 1);
                else goToSlide(currentIndex - 1);
            }
            startAutoSlide();
        }, { passive: true });

        // Dot click
        if (dotsContainer) {
            dotsContainer.addEventListener('click', (e) => {
                const dot = e.target.closest('.zk-carousel-dot');
                if (!dot) return;
                goToSlide(parseInt(dot.dataset.index));
                startAutoSlide();
            });
        }

        // Start auto-slide
        startAutoSlide();
    })();
</script>
@endpush
@endsection
