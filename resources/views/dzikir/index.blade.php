@extends('layout.master')

@section('title', 'Zikir Online')

@push('styles')
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#0a1f14">
<meta name="mobile-web-app-capable" content="yes">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    /* =============================================
       DZIKIR PAGE - GREEN ISLAMIC THEME
       ============================================= */

    :root {
        --zk-bg: #0a1f14;
        --zk-surface: #122a1c;
        --zk-surface-high: #1a3526;
        --zk-surface-variant: #234030;
        --zk-primary: #6fcf97;
        --zk-primary-container: #0f3d24;
        --zk-secondary: #a8e6cf;
        --zk-secondary-container: #1b5e3a;
        --zk-tertiary: #f0e68c;
        --zk-on-surface: #e8f0e8;
        --zk-on-surface-variant: #b0c8b0;
        --zk-outline: #6e8e6e;
        --zk-outline-variant: #2d4d35;
    }

    html, body, .container-scroller, .page-body-wrapper, .main-panel, .content-wrapper, .zikir-page {
        background-color: var(--zk-bg, #0a1f14) !important;
        background: var(--zk-bg, #0a1f14) !important;
    }

    /* ---- NATIVE SCROLL TO HIDE BROWSER UI ---- */
    html, body {
        overflow-y: auto !important;
        height: auto !important;
    }
    .container-scroller, .page-body-wrapper, .main-panel, .content-wrapper {
        overflow: visible !important;
        height: auto !important;
        transform: none !important;
        -webkit-transform: none !important;
    }

    /* ---- FULLSCREEN: hide header, sidebar, footer, mobile nav ---- */
    .navbar,
    .sidebar,
    .mobile-bottom-nav,
    footer,
    .footer {
        display: none !important;
    }
    .main-panel {
        margin-left: 0 !important;
        width: 100% !important;
        min-height: 100vh;
    }
    .page-body-wrapper {
        padding-top: 0 !important;
    }
    .content-wrapper {
        padding: 0 !important;
    }

    /* Page wrapper */
    .zikir-page {
        font-family: "Manrope", -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
        background: var(--zk-bg);
        min-height: 100vh;
        min-height: 100dvh;
        padding: 0 !important;
        position: relative;
        overflow-x: hidden;
        padding-top: env(safe-area-inset-top) !important;
        padding-bottom: env(safe-area-inset-bottom) !important;
    }

    /* Background image - blurred mosque dome, using absolute wrapper + sticky child to prevent scroll issues */
    .zk-sticky-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 0;
        pointer-events: none;
    }
    .zk-bg-sticky {
        position: -webkit-sticky;
        position: sticky;
        top: -40px;
        left: -40px;
        width: calc(100vw + 80px);
        height: calc(100vh + 80px);
        background-color: var(--zk-bg);
        background-image:
            linear-gradient(180deg, rgba(10, 31, 20, 0.15) 0%, rgba(10, 31, 20, 0.1) 50%, rgba(10, 31, 20, 0.3) 100%),
            url('{{ asset("public/images/mosque-bg.png") }}');
        background-size: cover;
        background-position: center bottom;
        background-repeat: no-repeat;
        filter: blur(6px);
        -webkit-filter: blur(6px);
    }

    /* Material Symbols Config */
    .zikir-page .material-symbols-outlined {
        font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24;
    }

    /* ---- Fixed Header (ZIKIR text, left-aligned) ---- */
    .zk-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10;
        padding: 20px 20px 12px 20px;
        padding-top: max(20px, env(safe-area-inset-top));
        background: linear-gradient(180deg, rgba(10, 31, 20, 0.7) 0%, transparent 100%);
    }
    .zk-header-title {
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.15em;
        color: var(--zk-on-surface);
        text-transform: uppercase;
        margin: 0;
    }

    /* Main Content - pushed down for fixed header */
    .zk-main {
        position: relative;
        z-index: 1;
        padding: 70px 20px 100px 20px;
    }

    /* ---- Bento Grid ---- */
    .zk-bento-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
        width: 100%;
    }

    /* Glass Card */
    .zk-card {
        background: rgba(18, 42, 28, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(111, 207, 151, 0.1);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none !important;
        color: inherit !important;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
        cursor: pointer;
        min-height: 150px;
        width: 100%;
        box-sizing: border-box;
    }
    .zk-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        text-decoration: none !important;
    }
    .zk-card:active { transform: scale(0.97); }

    /* Card Icon */
    .zk-card-icon { font-size: 28px; }
    .zk-card-icon.icon-primary { color: var(--zk-primary); }
    .zk-card-icon.icon-secondary { color: var(--zk-secondary); }
    .zk-card-icon.icon-white { color: rgba(255,255,255,0.9); }

    /* Card Text */
    .zk-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #ffffff;
        margin: 0 0 4px 0;
        line-height: 1.4;
    }
    .zk-card-sub {
        font-size: 12px;
        font-weight: 400;
        color: var(--zk-on-surface-variant);
        margin: 0;
        line-height: 1.3;
    }

    /* Sholat Card - full width */
    .zk-card-sholat {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #0f6b3c 0%, #1a8a5a 60%, #0d9488 100%);
        border: none;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none !important;
        color: inherit !important;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(15, 107, 60, 0.35);
    }
    .zk-card-sholat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(15, 107, 60, 0.45);
        text-decoration: none !important;
    }
    .zk-card-sholat:active { transform: scale(0.97); }
    .zk-sholat-left { display: flex; flex-direction: column; }
    .zk-sholat-left .material-symbols-outlined {
        color: rgba(255,255,255,0.9);
        font-size: 28px;
        margin-bottom: 12px;
    }
    .zk-sholat-left .zk-card-title { color: #fff; }
    .zk-sholat-left .zk-card-sub { color: rgba(255,255,255,0.7); }
    .zk-sholat-arrow {
        width: 48px; height: 48px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .zk-sholat-arrow .material-symbols-outlined { color: #fff; font-size: 20px; }

    /* Pagi Card */
    .zk-card-pagi {
        background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
        border: none;
        box-shadow: 0 4px 16px rgba(124, 58, 237, 0.25);
    }
    .zk-card-pagi .zk-card-sub { color: rgba(255,255,255,0.7); }

    /* Petang Card */
    .zk-card-petang {
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        border: none;
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.25);
    }
    .zk-card-petang .zk-card-sub { color: rgba(255,255,255,0.7); }

    /* ---- Category Pills ---- */
    .zk-section-title {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.08em;
        color: var(--zk-on-surface-variant);
        text-transform: uppercase;
        margin: 0 0 12px 0;
    }

    .zk-pills-row {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 8px;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .zk-pills-row::-webkit-scrollbar { display: none; }

    .zk-pill {
        flex-shrink: 0;
        padding: 10px 20px;
        border-radius: 999px;
        background: rgba(18, 42, 28, 0.75);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(111, 207, 151, 0.08);
        font-size: 12px;
        font-weight: 400;
        color: var(--zk-on-surface-variant);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        font-family: "Manrope", sans-serif;
    }
    .zk-pill:hover, .zk-pill.active {
        border-color: rgba(111, 207, 151, 0.3);
        color: var(--zk-primary);
        background: rgba(15, 61, 36, 0.6);
    }

    /* ---- Activity Cards ---- */
    .zk-activity-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .zk-activity-card {
        background: rgba(18, 42, 28, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(111, 207, 151, 0.1);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .zk-activity-icon {
        width: 40px; height: 40px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .zk-activity-icon.bg-primary { background: rgba(111, 207, 151, 0.15) !important; }
    .zk-activity-icon.bg-secondary { background: rgba(168, 230, 207, 0.15) !important; }
    .zk-activity-icon .material-symbols-outlined.text-primary { color: var(--zk-primary) !important; }
    .zk-activity-icon .material-symbols-outlined.text-secondary { color: var(--zk-secondary) !important; }
    .zk-activity-title {
        font-size: 14px; font-weight: 400;
        color: #ffffff; margin: 0; line-height: 1.4;
    }
    .zk-activity-sub {
        font-size: 12px;
        color: var(--zk-on-surface-variant);
        margin: 4px 0 0 0;
    }

    /* ---- Bottom Nav ---- */
    .zk-bottom-nav {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 28px;
        padding-bottom: max(12px, env(safe-area-inset-bottom));
        background: transparent;
    }
    .zk-bottom-btn {
        width: 44px; height: 44px;
        border-radius: 50%;
        border: 1.5px solid rgba(255,255,255,0.25);
        background: rgba(18, 42, 28, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        color: inherit !important;
    }
    .zk-bottom-btn:hover {
        background: rgba(18, 42, 28, 0.8);
        border-color: rgba(111, 207, 151, 0.4);
        transform: scale(1.05);
    }
    .zk-bottom-btn:active {
        transform: scale(0.93);
    }
    .zk-bottom-btn .material-symbols-outlined {
        color: rgba(255,255,255,0.85);
        font-size: 20px;
    }
    /* Center dot button - slightly bigger */
    .zk-bottom-btn.center {
        width: 52px; height: 52px;
        border-color: rgba(111, 207, 151, 0.3);
        background: rgba(15, 61, 36, 0.6);
    }
    .zk-bottom-btn.center .material-symbols-outlined {
        font-size: 24px;
        color: var(--zk-primary);
    }

    /* ======================
       RESPONSIVE BREAKPOINTS
       ====================== */

    /* Tablet portrait (600px+) */
    @media (min-width: 600px) {
        .zk-main { padding: 70px 28px 100px 28px; }
        .zk-bento-grid { gap: 16px; }
        .zk-card { min-height: 200px; padding: 24px; }
        .zk-card-title { font-size: 18px; }
        .zk-card-icon { font-size: 32px; }
        .zk-card-sholat { padding: 24px 28px; }
        .zk-sholat-left .material-symbols-outlined { font-size: 32px; }
        .zk-activity-row { gap: 16px; }
        .zk-activity-card { padding: 24px; }
        .zk-pill { padding: 12px 24px; font-size: 13px; }
        .zk-section-title { font-size: 14px; }
    }

    /* Tablet landscape & desktop (1024px+) */
    @media (min-width: 1024px) {
        .zk-main { padding: 80px 48px 100px 48px; }
        .zk-bento-grid { gap: 20px; }
        .zk-card { min-height: 220px; padding: 28px; }
        .zk-card-title { font-size: 20px; }
        .zk-card-icon { font-size: 36px; }
        .zk-card-sholat { padding: 28px 36px; }
        .zk-activity-row { gap: 20px; }
        .zk-activity-card { padding: 28px; }
        .zk-activity-title { font-size: 16px; }
        .zk-bg-sticky {
            background-position: center center;
        }
    }

    /* Small mobile (< 400px) */
    @media (max-width: 400px) {
        .zk-main { padding: 60px 12px 90px 12px; }
        .zk-bento-grid { gap: 8px; }
        .zk-card { padding: 14px; min-height: 120px; }
        .zk-card-title { font-size: 14px; }
        .zk-card-icon { font-size: 24px; }
        .zk-card-sholat { padding: 14px; }
        .zk-sholat-arrow { width: 40px; height: 40px; }
        .zk-activity-row { grid-template-columns: 1fr; gap: 8px; }
        .zk-pill { padding: 8px 14px; font-size: 11px; }
        .zk-bottom-nav { padding: 10px 20px; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper zikir-page">
    {{-- Sticky Background Wrapper --}}
    <div class="zk-sticky-wrapper">
        <div class="zk-bg-sticky"></div>
    </div>

    {{-- Fixed Header - ZIKIR aligned left --}}
    <header class="zk-header">
        <h1 class="zk-header-title">ZIKIR</h1>
    </header>

    {{-- Main Content --}}
    <div class="zk-main">
        {{-- Bento Grid --}}
        <div class="zk-bento-grid">
            {{-- Dzikir Umum --}}
            <a href="#" class="zk-card">
                <span class="material-symbols-outlined zk-card-icon icon-primary">history</span>
                <div>
                    <h3 class="zk-card-title">Dzikir umum</h3>
                    <p class="zk-card-sub">{{ $zikirUmum }} dzikir</p>
                </div>
            </a>

            {{-- Kesukaanku --}}
            <a href="#" class="zk-card">
                <span class="material-symbols-outlined zk-card-icon icon-secondary">star</span>
                <div>
                    <h3 class="zk-card-title">Kesukaanku</h3>
                    <p class="zk-card-sub">{{ $totalFavorites > 0 ? $totalFavorites . ' favorit' : 'Tidak ada favorit' }}</p>
                </div>
            </a>

            {{-- Sholat 5 Waktu (full width) --}}
            <a href="#" class="zk-card-sholat">
                <div class="zk-sholat-left">
                    <span class="material-symbols-outlined">auto_awesome</span>
                    <h3 class="zk-card-title">{{ $currentPrayerName }}</h3>
                    <p class="zk-card-sub">{{ $currentPrayerTime ? $currentPrayerTime : ($zikirSholat . ' dzikir') }}</p>
                </div>
                <div class="zk-sholat-arrow">
                    <span class="material-symbols-outlined">arrow_forward_ios</span>
                </div>
            </a>

            {{-- Zikir Pagi --}}
            <a href="#" class="zk-card zk-card-pagi">
                <span class="material-symbols-outlined zk-card-icon icon-white">light_mode</span>
                <div>
                    <h3 class="zk-card-title">Zikir pagi</h3>
                    <p class="zk-card-sub">{{ $zikirPagi }} dzikir</p>
                </div>
            </a>

            {{-- Zikir Petang --}}
            <a href="#" class="zk-card zk-card-petang">
                <span class="material-symbols-outlined zk-card-icon icon-white">dark_mode</span>
                <div>
                    <h3 class="zk-card-title">Zikir petang</h3>
                    <p class="zk-card-sub">{{ $zikirPetang }} dzikir</p>
                </div>
            </a>
        </div>

        {{-- Kolom Kategori --}}
        <div style="margin-bottom: 24px;">
            <h2 class="zk-section-title">Kolom kategori</h2>
            <div class="zk-pills-row">
                <button class="zk-pill active">Dzikir umum</button>
                <button class="zk-pill">Dzikir pagi</button>
                <button class="zk-pill">Dzikir petang</button>
                <button class="zk-pill">Dzikir sholat 5 waktu</button>
            </div>
        </div>

        {{-- Aktivitas Anda --}}
        <div>
            <h2 class="zk-section-title">Aktivitas Anda</h2>
            <div class="zk-activity-row">
                {{-- Recent Activity --}}
                <div class="zk-activity-card">
                    <div class="zk-activity-icon bg-primary">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                    <div>
                        <h3 class="zk-activity-title">{{ $recentActivity ? $recentActivity->zikir->title : 'Belum ada' }}</h3>
                        <p class="zk-activity-sub">{{ $recentActivity ? $recentActivity->last_read_at->diffForHumans() : 'Belum ada aktivitas' }}</p>
                    </div>
                </div>

                {{-- Koleksi --}}
                <div class="zk-activity-card">
                    <div class="zk-activity-icon bg-secondary">
                        <span class="material-symbols-outlined text-secondary">library_books</span>
                    </div>
                    <div>
                        <h3 class="zk-activity-title">Koleksi</h3>
                        <p class="zk-activity-sub">{{ $totalCollection }} / {{ $totalZikir > 0 ? $totalZikir : 454 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Nav: Back Only --}}
    <nav class="zk-bottom-nav" style="justify-content: flex-end;">
        <a href="{{ route('dashboard') }}" class="zk-bottom-btn" title="Kembali">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
    </nav>
</div>
@endsection

@push('scripts')
<script>
    // Category pill interaction
    document.querySelectorAll('.zk-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.zk-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endpush