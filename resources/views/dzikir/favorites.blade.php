@extends('layout.master')

@section('title', 'Favorit Zikir')

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
        min-height: 100vh !important;
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
        background: transparent;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: none;
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
        background: transparent;
        width: 100%;
    }

    .zk-main {
        position: relative;
        z-index: 1;
        padding: calc(75px + env(safe-area-inset-top)) 0 16px 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-width: 800px;
        margin: 0 auto;
    }

    /* List Card */
    .zk-list-card {
        background: var(--zk-card-bg);
        border-radius: 10px;
        padding: 16px;
        margin: 0 8px;
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
        color: #f1c40f; /* Star color active by default on this page */
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
    .zk-list-action:hover {
        opacity: 0.8;
    }

    /* Empty State Card */
    .zk-empty-card {
        background: rgba(18, 20, 24, 0.85); /* Dark background matching the image */
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 32px 24px;
        margin: auto 24px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: 100px;
    }
    
    .zk-empty-icon {
        color: #f39c12; /* Orange star */
        font-size: 48px;
        margin-bottom: 16px;
        font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 48 !important;
    }

    .zk-empty-title {
        color: var(--zk-text-main);
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 16px 0;
    }

    .zk-empty-text {
        color: var(--zk-text-muted);
        font-size: 13px;
        line-height: 1.6;
        margin: 0 0 16px 0;
    }
    .zk-empty-text:last-child {
        margin-bottom: 0;
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
        .zk-main { padding: calc(65px + env(safe-area-inset-top)) 0 16px 0; gap: 6px; }
        .zk-list-card { padding: 12px; margin: 0 6px; gap: 10px; }
        .zk-list-title { font-size: 15px; }
        .zk-list-latin { font-size: 12px; }
        .zk-list-progress { font-size: 11px; }
        .zk-hex-number { width: 26px; height: 30px; font-size: 12px; }
        .zk-list-action { width: 34px; height: 34px; }
        .zk-bottom-nav { padding: 16px 16px; padding-bottom: calc(16px + env(safe-area-inset-bottom)); }
        .zk-nav-btn { width: 38px; height: 38px; }
        .zk-empty-card { margin: auto 16px; padding: 24px 16px; margin-top: 80px; }
    }

    /* ---- RESPONSIVE: Tablets (768px - 1023px) ---- */
    @media (min-width: 768px) {
        .zk-main { padding: calc(90px + env(safe-area-inset-top)) 0 20px 0; gap: 10px; max-width: 600px; }
        .zk-header { padding: 20px 24px 12px 24px; padding-top: calc(20px + env(safe-area-inset-top)); }
        .zk-header-title { font-size: 16px; }
        .zk-list-card { padding: 20px; margin: 0 12px; border-radius: 14px; }
        .zk-list-title { font-size: 20px; }
        .zk-list-latin { font-size: 15px; }
        .zk-hex-number { width: 34px; height: 38px; font-size: 16px; }
        .zk-bottom-nav { padding: 24px 40px; padding-bottom: calc(24px + env(safe-area-inset-bottom)); }
        .zk-nav-btn { width: 48px; height: 48px; }
    }

    /* ---- RESPONSIVE: Desktop (1024px+) ---- */
    @media (min-width: 1024px) {
        .zk-main { padding-top: calc(100px + env(safe-area-inset-top)); max-width: 640px; gap: 12px; }
        .zk-header { padding: 24px 32px 14px 32px; padding-top: calc(24px + env(safe-area-inset-top)); }
        .zk-header-title { font-size: 16px; }
        .zk-list-card { padding: 22px; margin: 0 16px; border-radius: 16px; }
        .zk-list-title { font-size: 20px; }
        .zk-hex-number { width: 36px; height: 40px; font-size: 16px; }
        .zk-bottom-nav { padding: 32px 48px; padding-bottom: calc(32px + env(safe-area-inset-bottom)); }
        .zk-nav-btn { width: 50px; height: 50px; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper zikir-page">
    <div class="zk-bg-fixed"></div>
    <header class="zk-header">
        <h1 class="zk-header-title">ZIKIR FAVORIT</h1>
        <div class="zk-header-line"></div>
    </header>

    <div class="zk-main">
        @if($zikirs->isEmpty())
            <div class="zk-empty-card">
                <span class="material-symbols-outlined zk-empty-icon">star</span>
                <h2 class="zk-empty-title">Tambahkan zikir ke favorit</h2>
                <p class="zk-empty-text">Latihan zikir yang teratur meningkatkan perkembangan dan pertumbuhan spiritual.</p>
                <p class="zk-empty-text">Membaca dzikir membantu menenangkan pikiran dan hati, membawa kedamaian dan kenyamanan batin.</p>
                <p class="zk-empty-text">Tetapkan tujuan harian untuk diri Anda sendiri - berapa kali sehari Anda berusaha untuk mengulangi setiap dzikir.</p>
            </div>
        @else
            {{-- Zikir List --}}
            @foreach($zikirs as $index => $zikir)
                @php
                    $target = $zikir->default_target ?? 33;
                    $progress = isset($activities[$zikir->id]) ? $activities[$zikir->id]->total_count : 0;
                    
                    // Determine play route category (fallback to umum if none found)
                    $playCat = 'umum';
                    if(is_array($zikir->category) && count($zikir->category) > 0) {
                        $playCat = $zikir->category[0];
                    }
                @endphp
                <a href="{{ route('dzikir.play', ['category' => $playCat, 'id' => $zikir->id, 'source' => 'favorites']) }}" class="zk-list-card" id="fav-card-{{ $zikir->id }}">
                    <div class="zk-hex-number">{{ $index + 1 }}</div>
                    <div class="zk-list-content">
                        <h3 class="zk-list-title">{{ $zikir->title }}</h3>
                        <p class="zk-list-latin">{{ $zikir->latin_text }}</p>
                        <p class="zk-list-progress">{{ $progress }} / {{ $target }}</p>
                    </div>
                    <button class="zk-list-action active" onclick="event.preventDefault(); toggleFavorite({{ $zikir->id }}, this)">
                        <span class="material-symbols-outlined filled">star</span>
                    </button>
                </a>
            @endforeach
        @endif
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
                if (!data.is_favorite) {
                    // Remove the card from the UI with an animation
                    const card = document.getElementById('fav-card-' + zikirId);
                    if (card) {
                        card.style.transition = 'opacity 0.3s, transform 0.3s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();
                            // Reload page if no more favorites left to show empty state
                            if (document.querySelectorAll('.zk-list-card').length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
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
</script>
@endpush
@endsection
