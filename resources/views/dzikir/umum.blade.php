@extends('layout.master')

@section('title', 'Semua Zikir')

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

    .zikir-page::before {
        content: '';
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
        padding: 16px 16px 10px 16px;
        padding-top: calc(16px + env(safe-area-inset-top));
        z-index: 10;
        background: transparent;
    }

    .zk-header-title {
        color: var(--zk-text-main);
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 1px;
        margin: 0;
        text-transform: uppercase;
    }

    .zk-main {
        position: relative;
        z-index: 1;
        padding: 60px 0 16px 0; /* No side padding - cards edge to edge */
        display: flex;
        flex-direction: column;
        gap: 4px; /* Very tight gap */
        max-width: 800px;
        margin: 0 auto;
    }

    /* Featured Card */
    .zk-featured {
        background: var(--zk-card-bg);
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        margin-bottom: 8px;
    }
    .zk-featured-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .zk-featured-icon {
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
    .zk-featured-title {
        color: var(--zk-text-main);
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }
    .zk-featured-number {
        color: var(--zk-primary);
        font-size: 40px;
        font-weight: 800;
        margin: 0;
        line-height: 1.1;
        letter-spacing: -1px;
    }
    .zk-featured-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 10px;
    }
    .zk-featured-label {
        color: var(--zk-text-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .zk-featured-percent {
        color: var(--zk-text-main);
        font-size: 14px;
        font-weight: 600;
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
        justify-content: space-between;
        padding: 8px 24px;
        padding-bottom: env(safe-area-inset-bottom);
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
        .zk-header { padding: 12px 12px 8px 12px; }
        .zk-header-title { font-size: 12px; }
        .zk-main { padding: 50px 0 16px 0; gap: 3px; }
        .zk-list-card { padding: 12px; margin: 0 6px; gap: 10px; }
        .zk-list-title { font-size: 15px; }
        .zk-list-latin { font-size: 12px; }
        .zk-list-progress { font-size: 11px; }
        .zk-hex-number { width: 26px; height: 30px; font-size: 12px; }
        .zk-list-action { width: 34px; height: 34px; }
        .zk-featured { padding: 14px; border-radius: 14px; margin: 0 6px; }
        .zk-featured-title { font-size: 14px; }
        .zk-featured-number { font-size: 32px; }
        .zk-bottom-nav { padding: 6px 16px; }
        .zk-nav-btn { width: 38px; height: 38px; }
    }

    /* ---- RESPONSIVE: Regular phones (381px - 767px) ---- */
    @media (min-width: 381px) and (max-width: 767px) {
        .zk-main { padding: 60px 0 16px 0; gap: 4px; }
        .zk-list-card { margin: 0 8px; }
        .zk-featured { margin: 0 8px; }
    }

    /* ---- RESPONSIVE: Tablets (768px - 1023px) ---- */
    @media (min-width: 768px) {
        .zk-main { padding: 80px 0 20px 0; gap: 6px; max-width: 600px; }
        .zk-header { padding: 20px 24px 12px 24px; }
        .zk-header-title { font-size: 16px; }
        .zk-list-card { padding: 20px; margin: 0 12px; border-radius: 14px; }
        .zk-list-title { font-size: 20px; }
        .zk-list-latin { font-size: 15px; }
        .zk-hex-number { width: 34px; height: 38px; font-size: 16px; }
        .zk-featured { padding: 24px; border-radius: 22px; margin: 0 12px; }
        .zk-featured-title { font-size: 18px; }
        .zk-featured-number { font-size: 48px; }
        .zk-bottom-nav { padding: 12px 40px; }
        .zk-nav-btn { width: 48px; height: 48px; }
    }

    /* ---- RESPONSIVE: Desktop (1024px+) ---- */
    @media (min-width: 1024px) {
        .zk-main { padding-top: 100px; max-width: 640px; gap: 8px; }
        .zk-header { padding: 24px 32px 14px 32px; }
        .zk-header-title { font-size: 16px; }
        .zk-list-card { padding: 22px; margin: 0 16px; border-radius: 16px; }
        .zk-list-title { font-size: 20px; }
        .zk-hex-number { width: 36px; height: 40px; font-size: 16px; }
        .zk-featured { padding: 28px; border-radius: 24px; margin: 0 16px; }
        .zk-featured-number { font-size: 52px; }
        .zk-bottom-nav { padding: 14px 48px; }
        .zk-nav-btn { width: 50px; height: 50px; }
    }

    /* ---- RESPONSIVE: Large desktop (1440px+) ---- */
    @media (min-width: 1440px) {
        .zk-main { max-width: 720px; }
        .zk-list-card { padding: 24px; margin: 0 20px; }
        .zk-featured { padding: 32px; margin: 0 20px; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper zikir-page">
    <header class="zk-header">
        <h1 class="zk-header-title">SEMUA ZIKIR</h1>
    </header>

    <div class="zk-main">
        {{-- Featured Card --}}
        @if($featuredZikir)
        <div class="zk-featured">
            <div class="zk-featured-header">
                <div class="zk-featured-icon">
                    <span class="material-symbols-outlined">group</span>
                </div>
                <h3 class="zk-featured-title">{{ $featuredZikir->title }} ☝️</h3>
            </div>
            <h2 class="zk-featured-number">{{ number_format($globalCount, 0, ',', ' ') }}</h2>
            <div class="zk-featured-footer">
                <span class="zk-featured-label">LAGI</span>
                <span class="zk-featured-percent">3.5%</span>
            </div>
            {{-- Carousel Dots Placeholder --}}
            <div style="display: flex; justify-content: center; gap: 6px; margin-top: 15px;">
                <div style="width: 6px; height: 6px; border-radius: 50%; background: #fff;"></div>
                <div style="width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.3);"></div>
                <div style="width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.3);"></div>
                <div style="width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.3);"></div>
            </div>
        </div>
        @endif

        {{-- Zikir List --}}
        @foreach($zikirs as $index => $zikir)
            @php
                $target = $zikir->default_target ?? 33;
                $progress = isset($activities[$zikir->id]) ? $activities[$zikir->id]->total_count : 0;
                $isFavorite = in_array($zikir->id, $favorites);
            @endphp
            <a href="#" class="zk-list-card">
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
        <a href="{{ route('dzikir.index') }}" class="zk-nav-btn">
            <span class="material-symbols-outlined">close</span>
        </a>
    </div>
</div>

@push('scripts')
<script>
    function toggleFavorite(zikirId, btn) {
        // Simple toggle visual effect (logic for saving to DB should be added here via AJAX)
        btn.classList.toggle('active');
        const icon = btn.querySelector('.material-symbols-outlined');
        if (btn.classList.contains('active')) {
            icon.classList.add('filled');
        } else {
            icon.classList.remove('filled');
        }
        
        // TODO: Send AJAX request to save favorite
        /*
        fetch('/dzikir/favorite/' + zikirId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
        */
    }
</script>
@endpush
@endsection
