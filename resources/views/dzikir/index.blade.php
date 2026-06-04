@extends('layout.master')

@section('title', 'Zikir Online')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    /* =============================================
       DZIKIR PAGE - DARK SPIRITUAL THEME
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

    .zikir-page {
        font-family: "Manrope", -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
        background: var(--zk-bg);
        min-height: 100vh;
        padding: 0 !important;
        position: relative;
        overflow-x: hidden;
    }

    .zikir-page::before {
        content: '';
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image:
            linear-gradient(180deg, rgba(10, 31, 20, 0.6) 0%, rgba(10, 31, 20, 0.4) 40%, rgba(10, 31, 20, 0.3) 100%),
            url('{{ asset("public/images/mosque_dome_bg.png") }}');
        background-size: cover, cover;
        background-position: center, center;
        background-attachment: fixed;
        pointer-events: none;
        z-index: 0;
    }

    .zikir-page::after {
        content: '';
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background:
            radial-gradient(ellipse at 20% 0%, rgba(15, 61, 36, 0.5) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 100%, rgba(27, 94, 58, 0.2) 0%, transparent 50%);
        pointer-events: none;
        z-index: 0;
    }

    /* Header */
    .zk-header {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 20px 8px 20px;
    }
    .zk-header-title {
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.15em;
        color: var(--zk-on-surface);
        text-transform: uppercase;
        margin: 0;
    }
    .zk-header .material-symbols-outlined {
        color: var(--zk-on-surface);
        cursor: pointer;
        font-size: 24px;
    }

    /* Main Content */
    .zk-main {
        position: relative;
        z-index: 1;
        padding: 16px 20px 140px 20px;
    }

    /* ---- Bento Grid ---- */
    .zk-bento-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
    }

    /* Glass Card (base) */
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
        min-height: 140px;
    }
    .zk-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .zk-card:active {
        transform: scale(0.97);
    }

    /* Card Icon */
    .zk-card-icon {
        font-size: 28px;
        margin-bottom: auto;
    }
    .zk-card-icon.icon-primary { color: var(--zk-primary); }
    .zk-card-icon.icon-secondary { color: var(--zk-secondary); }
    .zk-card-icon.icon-white { color: rgba(255,255,255,0.9); }

    /* Card Text */
    .zk-card-title {
        font-size: 16px;
        font-weight: 500;
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

    /* Sholat Card - Spans 2 columns */
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
        min-height: 80px;
    }
    .zk-card-sholat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(15, 107, 60, 0.45);
    }
    .zk-card-sholat:active {
        transform: scale(0.97);
    }
    .zk-card-sholat .zk-sholat-left {
        display: flex;
        flex-direction: column;
    }
    .zk-card-sholat .zk-sholat-left .material-symbols-outlined {
        color: rgba(255,255,255,0.9);
        font-size: 28px;
        margin-bottom: 12px;
    }
    .zk-card-sholat .zk-sholat-left .zk-card-title {
        color: #fff;
    }
    .zk-card-sholat .zk-sholat-left .zk-card-sub {
        color: rgba(255,255,255,0.7);
    }
    .zk-sholat-arrow {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .zk-sholat-arrow .material-symbols-outlined {
        color: #fff;
        font-size: 20px;
    }

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
        margin-bottom: 24px;
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
        background: rgba(15, 38, 38, 0.6);
    }

    /* ---- Activity Cards ---- */
    .zk-activity-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
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
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .zk-activity-icon.bg-primary {
        background: rgba(111, 207, 151, 0.15) !important;
    }
    .zk-activity-icon.bg-secondary {
        background: rgba(168, 230, 207, 0.15) !important;
    }
    .zk-activity-icon .material-symbols-outlined.text-primary {
        color: var(--zk-primary) !important;
    }
    .zk-activity-icon .material-symbols-outlined.text-secondary {
        color: var(--zk-secondary) !important;
    }
    .zk-activity-title {
        font-size: 14px;
        font-weight: 400;
        color: #ffffff;
        margin: 0;
        line-height: 1.4;
    }
    .zk-activity-sub {
        font-size: 12px;
        color: var(--zk-on-surface-variant);
        margin: 4px 0 0 0;
    }

    /* ---- Bottom Nav ---- */
    .zk-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 50;
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 12px 24px;
        padding-bottom: max(12px, env(safe-area-inset-bottom));
        background: rgba(10, 31, 20, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid rgba(111, 207, 151, 0.08);
    }
    .zk-nav-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none !important;
        transition: transform 0.2s ease;
    }
    .zk-nav-btn:hover {
        transform: translateY(-2px);
    }
    .zk-nav-btn .material-symbols-outlined {
        font-size: 24px;
        color: var(--zk-on-surface-variant);
    }
    .zk-nav-btn.active .material-symbols-outlined {
        color: var(--zk-primary);
    }
    .zk-nav-center {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--zk-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        margin-top: -28px;
        box-shadow: 0 4px 20px rgba(111, 207, 151, 0.35);
        transition: transform 0.2s ease;
    }
    .zk-nav-center:hover {
        transform: scale(1.08);
    }
    .zk-nav-center:active {
        transform: scale(0.95);
    }
    .zk-nav-center .material-symbols-outlined {
        color: #1e3434;
        font-size: 26px;
    }

    /* Material Symbols Config */
    .zk-main .material-symbols-outlined,
    .zk-header .material-symbols-outlined,
    .zk-bottom-nav .material-symbols-outlined {
        font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24;
    }

    /* ---- Responsive ---- */
    @media (max-width: 400px) {
        .zk-card {
            padding: 16px;
            min-height: 120px;
        }
        .zk-bento-grid { gap: 8px; }
        .zk-activity-row { gap: 8px; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper zikir-page">
    {{-- Header --}}
    <header class="zk-header">
        <div data-widget="pushmenu" role="button">
            <span class="material-symbols-outlined">menu</span>
        </div>
        <h1 class="zk-header-title">ZIKIR</h1>
        <div style="width:24px;"></div>
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

    {{-- Bottom Navigation --}}
    <nav class="zk-bottom-nav">
        <a href="{{ route('dashboard') }}" class="zk-nav-btn active">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">home</span>
        </a>
        <button class="zk-nav-center">
            <span class="material-symbols-outlined">more_horiz</span>
        </button>
        <button class="zk-nav-btn" onclick="history.back()">
            <span class="material-symbols-outlined">close</span>
        </button>
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