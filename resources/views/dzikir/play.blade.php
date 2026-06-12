<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1, user-scalable=0">
    <title>Zikir Player</title>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#061c23">
    <meta name="mobile-web-app-capable" content="yes">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Amiri&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        /* Reset & Base */
    html, body {
        margin: 0; padding: 0;
        height: 100%;
        overflow: hidden !important; /* Lock scroll, app-like */
        background-color: #061c23; /* Dark base */
    }


    
    .content-wrapper.zikir-player {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #061c23;
        background-image: linear-gradient(to bottom, rgba(6, 28, 35, 0.4) 0%, rgba(6, 28, 35, 0.95) 100%),
                          url('{{ asset("public/images/mosque-bg.png") }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        flex-direction: column;
        padding: 0;
        margin: 0;
        z-index: 9999;
        font-family: 'Inter', sans-serif;
    }

    /* Top Navigation */
    .zp-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        padding-top: calc(16px + env(safe-area-inset-top));
        color: rgba(255, 255, 255, 0.7);
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 1px;
    }
    .zp-dots {
        display: flex;
        gap: 6px;
    }
    .zp-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transition: background 0.3s;
    }
    .zp-dot.active {
        background: #fff;
    }

    /* Main Area (Carousel Container) */
    .zp-main {
        flex: 1;
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .zp-track {
        display: flex;
        width: 100%;
        height: 100%;
        transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        will-change: transform;
    }

    .zp-slide {
        flex: 0 0 100%;
        min-width: 100%;
        display: flex;
        flex-direction: column;
        padding: 16px;
        box-sizing: border-box;
    }

    /* Top Card: Text */
    .zp-card-text {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        /* Hide scrollbar */
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
        background: rgba(10, 35, 45, 0.8);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    .zp-card-text::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    .zp-card-icons {
        position: absolute;
        top: 20px; left: 20px; right: 20px;
        display: flex;
        justify-content: space-between;
    }
    .zp-icon-btn {
        width: 36px; height: 36px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
    }
    
    .zp-category {
        color: rgba(255, 255, 255, 0.4);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 24px;
    }
    
    .zp-arabic {
        font-family: 'Amiri', serif;
        color: #fff;
        font-size: 36px;
        line-height: 1.6;
        margin-bottom: 16px;
        direction: rtl;
    }
    
    .zp-latin {
        color: #e0e0e0;
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 24px;
        line-height: 1.4;
    }
    
    .zp-meaning {
        color: rgba(255, 255, 255, 0.6);
        font-size: 15px;
        line-height: 1.5;
        max-width: 90%;
    }

    .zp-play-btn {
        position: absolute;
        bottom: 20px; right: 20px;
        width: 44px; height: 44px;
        background: #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #061c23;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    /* Bottom Card: Counter */
    .zp-card-counter {
        height: 40%;
        min-height: 280px;
        background: rgba(15, 30, 25, 0.9);
        border-radius: 20px;
        margin-top: 8px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        /* Make the whole card clickable for counter */
        cursor: pointer;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }
    
    .zp-progress-wrapper {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        margin-bottom: 30px;
    }
    .zp-progress-bg {
        width: 100%;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .zp-progress-fill {
        height: 100%;
        background: #5a7350; /* Muted green */
        width: 0%;
        transition: width 0.3s ease;
    }
    .zp-target-text {
        color: rgba(255, 255, 255, 0.5);
        font-size: 13px;
        font-weight: 500;
    }

    /* Big Circle Counter */
    .zp-circle {
        width: 140px; height: 140px;
        border-radius: 50%;
        border: 4px solid #5a7350;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.1s;
    }
    .zp-circle:active {
        transform: scale(0.95);
        background: rgba(90, 115, 80, 0.1);
    }
    .zp-count {
        color: #5a7350;
        font-size: 64px;
        font-weight: 300;
    }

    .zp-bottom-icons {
        position: absolute;
        bottom: 24px; left: 24px; right: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .zp-bottom-number {
        color: rgba(255, 255, 255, 0.4);
        font-size: 16px;
        font-weight: 600;
    }
    .zp-mic-icon {
        color: rgba(255, 255, 255, 0.4);
        font-size: 24px;
    }

    /* Bottom Nav (Exit / Options) */
    .zp-footer {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 16px 24px;
        padding-bottom: calc(24px + env(safe-area-inset-bottom));
        gap: 16px;
        position: relative;
    }
    .zp-close-btn {
        position: absolute;
        right: 24px;
        width: 44px; height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
        backdrop-filter: blur(10px);
    }
    .zp-options-btn {
        width: 44px; height: 44px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
    }
    
    /* Vibrate animation for reaching target */
    @keyframes pulse-success {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(90, 115, 80, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(90, 115, 80, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(90, 115, 80, 0); }
    }
    .target-reached .zp-circle {
        animation: pulse-success 0.5s;
        border-color: #1ed760;
    }
    .target-reached .zp-count {
        color: #1ed760;
        font-weight: 600;
    }

    /* Options Dropdown */
    .zp-options-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }
    .zp-options-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    .zp-options-menu {
        position: absolute;
        top: 80px; left: 24px;
        background: #fdf5f5; /* Light reddish white like screenshot */
        border-radius: 12px;
        width: 250px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        transform: translateY(-10px);
        transition: transform 0.2s;
        overflow: hidden;
    }
    .zp-options-overlay.active .zp-options-menu {
        transform: translateY(0);
    }
    .zp-option-title {
        padding: 16px;
        font-size: 14px;
        color: rgba(0,0,0,0.4);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .zp-option-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        font-size: 15px;
        color: rgba(0,0,0,0.7);
        text-decoration: none;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .zp-option-item:active {
        background: rgba(0,0,0,0.05);
    }
    .zp-option-item.text-red {
        color: #e53935;
    }
    .zp-option-value {
        font-weight: 600;
    }

    /* Target Modal */
    .zp-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }
    .zp-modal-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    .zp-modal {
        background: #fdf5f5;
        border-radius: 16px;
        width: 80%;
        max-width: 300px;
        padding: 24px;
        text-align: center;
        transform: scale(0.95);
        transition: transform 0.2s;
    }
    .zp-modal-overlay.active .zp-modal {
        transform: scale(1);
    }
    .zp-modal-title {
        color: rgba(0,0,0,0.6);
        font-size: 18px;
        margin-bottom: 24px;
    }
    .zp-modal-input {
        width: 60px;
        font-size: 24px;
        text-align: center;
        border: none;
        border-bottom: 2px solid #2e7d32;
        background: transparent;
        color: rgba(0,0,0,0.7);
        padding-bottom: 4px;
        outline: none;
        margin-bottom: 32px;
    }
    .zp-modal-actions {
        display: flex;
        justify-content: space-around;
    }
    .zp-modal-btn {
        background: none;
        border: none;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        padding: 8px 16px;
    }
    .zp-modal-btn.cancel {
        color: rgba(0,0,0,0.4);
    }
    .zp-modal-btn.save {
        color: #2e7d32;
    }

    /* Share Overlay */
    .zp-share-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 30000;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        padding: 20px;
        box-sizing: border-box;
    }
    .zp-share-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    .zp-share-card {
        width: 100%;
        max-width: 360px;
        aspect-ratio: 3/4;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        background-color: #000;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .zp-share-bg {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        object-fit: cover;
        opacity: 0.5;
    }
    .zp-share-content {
        position: relative;
        z-index: 2;
        padding: 30px;
        text-align: center;
    }
    .zp-share-arabic {
        font-family: 'Amiri', serif;
        font-size: 32px;
        color: #fff;
        margin-bottom: 16px;
        line-height: 1.6;
    }
    .zp-share-meaning {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.5;
    }
    .zp-share-actions {
        width: 100%;
        max-width: 360px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .zp-share-btn-primary {
        background: #1976d2;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 14px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        text-transform: uppercase;
    }
    .zp-share-btn-cancel {
        background: transparent;
        color: rgba(255, 255, 255, 0.6);
        border: none;
        padding: 10px;
        font-size: 14px;
        cursor: pointer;
        width: 100%;
    }

    /* Responsive adjustments for short screens (Landscape / Tablet) */
    @media (max-height: 750px) {
        .zp-card-counter {
            min-height: 200px;
            padding: 16px;
        }
        .zp-circle {
            width: 100px; height: 100px;
        }
        .zp-count {
            font-size: 48px;
        }
        .zp-progress-wrapper {
            margin-bottom: 16px;
        }
        .zp-arabic {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .zp-latin {
            font-size: 14px;
            margin-bottom: 16px;
        }
        .zp-meaning {
            font-size: 13px;
        }
        .zp-category {
            margin-bottom: 16px;
        }
        .zp-bottom-icons {
            bottom: 16px; left: 16px; right: 16px;
        }
    }
</style>
</head>
<body>
<div class="content-wrapper zikir-player">
    {{-- Top Info --}}
    <div class="zp-top">
        <div id="slideIndicator">1 / {{ count($zikirs) }}</div>
        <div class="zp-dots">
            {{-- Just 4 dots as visual --}}
            <div class="zp-dot active"></div>
            <div class="zp-dot"></div>
            <div class="zp-dot"></div>
            <div class="zp-dot"></div>
        </div>
        <div style="width: 40px;"></div> {{-- Spacer to balance --}}
    </div>

    {{-- Main Swiper Area --}}
    <div class="zp-main" id="touchArea">
        <div class="zp-track" id="sliderTrack">
            @foreach($zikirs as $index => $zikir)
                @php
                    $activity = $activities[$zikir->id] ?? null;
                    $target = $activity->target_count ?? $zikir->default_target ?? 33;
                    $progress = $activity->total_count ?? 0;
                    $allTime = $activity->all_time_count ?? 0;
                    $isFavorite = in_array($zikir->id, $favorites);
                @endphp
                <div class="zp-slide" 
                     data-id="{{ $zikir->id }}" 
                     data-title="{{ $zikir->title }}"
                     data-target="{{ $target }}" 
                     data-count="{{ $progress }}"
                     data-all-time="{{ $allTime }}"
                     data-favorite="{{ $isFavorite ? '1' : '0' }}">
                    
                    {{-- Text Card --}}
                    <div class="zp-card-text">
                        <div class="zp-card-icons">
                            <a href="#" class="zp-icon-btn btn-more-options"><span class="material-symbols-outlined" style="font-size: 20px;">more_vert</span></a>
                            <a href="#" class="zp-icon-btn"><span class="material-symbols-outlined" style="font-size: 20px;">info</span></a>
                        </div>
                        
                        <div class="zp-category">{{ strtoupper($zikir->title) }}</div>
                        <div class="zp-arabic">{{ $zikir->arabic_text ?? 'لا إله إلا الله' }}</div>
                        <div class="zp-latin">{{ $zikir->latin_text ?? 'La ilaaha illallaah.' }}</div>
                        <div class="zp-meaning">{{ $zikir->meaning ?? 'Tiada Tuhan (yang berhak disembah) kecuali Allah.' }}</div>
                        
                    </div>

                    {{-- Counter Card --}}
                    <div class="zp-card-counter" onclick="incrementCounter()">
                        <div class="zp-progress-wrapper">
                            <div class="zp-progress-bg">
                                <div class="zp-progress-fill" style="width: {{ $target > 0 ? min(100, ($progress / $target) * 100) : 0 }}%"></div>
                            </div>
                            <div class="zp-target-text">{{ $target }}</div>
                        </div>

                        <div class="zp-circle">
                            <div class="zp-count">{{ $progress }}</div>
                        </div>

                        <div class="zp-bottom-icons">
                            <div style="width:24px"></div>
                            <div class="zp-bottom-number">{{ $allTime }}</div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="zp-footer">

        <a href="{{ request('source') === 'favorites' ? route('dzikir.favorites') : route('dzikir.list', ['category' => $category]) }}" class="zp-close-btn">
            <span class="material-symbols-outlined">close</span>
        </a>
    </div>
    </div>
</div>

{{-- Options Dropdown Menu --}}
<div class="zp-options-overlay" id="optionsOverlay">
    <div class="zp-options-menu">
        <div class="zp-option-title" id="optTitle">Kalimat Tauhid</div>
        <a href="#" class="zp-option-item" id="optShare">Berbagi</a>
        <a href="#" class="zp-option-item" id="optFavorite">Tambahkan ke Favorit</a>
        <a href="#" class="zp-option-item" id="optTarget">
            Tujuan harian <span class="zp-option-value" id="optTargetValue">33</span>
        </a>
        <a href="#" class="zp-option-item text-red" id="optReset">Mengatur ulang penghitung</a>
    </div>
</div>

{{-- Target Modal --}}
<div class="zp-modal-overlay" id="targetModalOverlay">
    <div class="zp-modal">
        <div class="zp-modal-title">Tujuan harian</div>
        <input type="number" class="zp-modal-input" id="targetInput" inputmode="numeric" min="1">
        <div class="zp-modal-actions">
            <button class="zp-modal-btn cancel" id="btnCancelTarget">BATAL</button>
            <button class="zp-modal-btn save" id="btnSaveTarget">SIMPAN</button>
        </div>
    </div>
</div>

<form id="progressForm" action="{{ route('dzikir.progress') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="zikir_id" id="formZikirId">
    <input type="hidden" name="count" id="formCount">
    <input type="hidden" name="all_time" id="formAllTime">
</form>

{{-- Share Overlay --}}
<div class="zp-share-overlay" id="shareOverlay">
    <div class="zp-share-card" id="shareCard">
        <img src="" class="zp-share-bg" id="shareBg" crossorigin="anonymous">
        <div class="zp-share-content">
            <div class="zp-share-arabic" id="shareArabic"></div>
            <div class="zp-share-meaning" id="shareMeaning"></div>
        </div>
    </div>
    
    <div class="zp-share-actions">
        <button class="zp-share-btn-primary" id="btnShareConfirm">BERBAGI</button>
        <button class="zp-share-btn-cancel" id="btnShareCancel">Batal</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('sliderTrack');
        const slides = document.querySelectorAll('.zp-slide');
        const indicator = document.getElementById('slideIndicator');
        const totalSlides = slides.length;
        
        let currentIndex = {{ $initialIndex ?? 0 }};
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;
        let animationID;
        
        // --- SLIDER LOGIC ---
        function setPositionByIndex() {
            currentTranslate = currentIndex * -100;
            prevTranslate = currentTranslate;
            track.style.transform = `translateX(${currentTranslate}%)`;
            
            // Update indicator
            indicator.innerText = `${currentIndex + 1} / ${totalSlides}`;
            
            // Update dots (visual only)
            const dots = document.querySelectorAll('.zp-dot');
            dots.forEach((dot, i) => {
                if (i === currentIndex % dots.length) dot.classList.add('active');
                else dot.classList.remove('active');
            });
            
            // Re-render counter for active slide
            updateCounterUI();
        }

        // Initialize slider position
        if(totalSlides > 0) {
            setPositionByIndex();
        }

        // Touch events
        track.addEventListener('touchstart', touchStart);
        track.addEventListener('touchend', touchEnd);
        track.addEventListener('touchmove', touchMove);

        function touchStart(event) {
            // Prevent swipe if touching the counter card, let user tap it instead
            if (event.target.closest('.zp-card-counter')) {
                // We still want to be able to swipe on counter, but let's just make it slightly less sensitive
                // Or let's just allow swipe anywhere.
            }
            startPos = getPositionX(event);
            isDragging = true;
            animationID = requestAnimationFrame(animation);
            track.style.transition = 'none';
        }

        function touchMove(event) {
            if (isDragging) {
                const currentPosition = getPositionX(event);
                const diff = currentPosition - startPos;
                // convert px to % roughly
                const diffPercent = (diff / window.innerWidth) * 100;
                currentTranslate = prevTranslate + diffPercent;
            }
        }

        function touchEnd() {
            isDragging = false;
            cancelAnimationFrame(animationID);
            track.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)';
            
            const movedBy = currentTranslate - prevTranslate;
            if (movedBy < -15 && currentIndex < totalSlides - 1) currentIndex += 1;
            if (movedBy > 15 && currentIndex > 0) currentIndex -= 1;
            
            setPositionByIndex();
        }

        function getPositionX(event) {
            return event.touches[0].clientX;
        }

        function animation() {
            if(isDragging) {
                track.style.transform = `translateX(${currentTranslate}%)`;
                requestAnimationFrame(animation);
            }
        }

        // --- COUNTER LOGIC ---
        let saveTimeout;
        let unsavedCounts = {};

        window.incrementCounter = function() {
            if(totalSlides === 0) return;
            const slide = slides[currentIndex];
            let count = parseInt(slide.dataset.count) || 0;
            let allTime = parseInt(slide.dataset.allTime) || 0;
            const target = parseInt(slide.dataset.target) || 33;
            const zikirId = slide.dataset.id;
            
            // Increment
            count++;
            allTime++;
            
            if (!unsavedCounts[zikirId]) unsavedCounts[zikirId] = 0;
            unsavedCounts[zikirId]++;
            
            slide.dataset.count = count;
            slide.dataset.allTime = allTime;
            
            // Haptic feedback
            if (window.navigator && window.navigator.vibrate) {
                if (count === target) window.navigator.vibrate([100, 50, 100]); // special vibe
                else window.navigator.vibrate(20);
            }

            updateCounterUI();
            debouncedSave(zikirId);
        };

        function updateCounterUI() {
            if(totalSlides === 0) return;
            const slide = slides[currentIndex];
            const count = parseInt(slide.dataset.count) || 0;
            const allTime = parseInt(slide.dataset.allTime) || 0;
            const target = parseInt(slide.dataset.target) || 33;
            
            const countEl = slide.querySelector('.zp-count');
            const fillEl = slide.querySelector('.zp-progress-fill');
            const counterCard = slide.querySelector('.zp-card-counter');
            const allTimeEl = slide.querySelector('.zp-bottom-number');
            
            if(countEl) countEl.innerText = count;
            if(allTimeEl) allTimeEl.innerText = allTime;
            
            if(fillEl) {
                const percent = target > 0 ? Math.min(100, (count / target) * 100) : 0;
                fillEl.style.width = `${percent}%`;
            }

            if(count >= target && target > 0) {
                counterCard.classList.add('target-reached');
            } else {
                counterCard.classList.remove('target-reached');
            }
        }

        function debouncedSave(zikirId) {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const incrementToSave = unsavedCounts[zikirId] || 0;
                if (incrementToSave === 0) return;
                
                unsavedCounts[zikirId] = 0; // reset locally immediately

                const form = document.getElementById('progressForm');
                document.getElementById('formZikirId').value = zikirId;
                document.getElementById('formCount').value = incrementToSave; // send increment amount
                
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' 
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const slide = document.querySelector(`.zp-slide[data-id="${zikirId}"]`);
                        if (slide) {
                            let trueTotal = data.activity.total_count;
                            let trueAllTime = data.activity.all_time_count;
                            
                            // add back any clicks that happened WHILE the request was flying
                            let pending = unsavedCounts[zikirId] || 0;
                            slide.dataset.count = trueTotal + pending;
                            slide.dataset.allTime = trueAllTime + pending;
                            
                            // Only update UI if we are currently looking at this slide
                            if (slides[currentIndex] === slide) {
                                updateCounterUI();
                            }
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    // if failed, add back the unsaved count so it tries again next time
                    unsavedCounts[zikirId] = (unsavedCounts[zikirId] || 0) + incrementToSave;
                });
            }, 1000);
        }

        // --- OPTIONS MENU LOGIC ---
        const overlay = document.getElementById('optionsOverlay');
        const optTitle = document.getElementById('optTitle');
        const optTargetValue = document.getElementById('optTargetValue');
        const optFavorite = document.getElementById('optFavorite');
        
        document.querySelectorAll('.btn-more-options').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const slide = slides[currentIndex];
                optTitle.innerText = slide.dataset.title;
                optTargetValue.innerText = slide.dataset.target;
                optFavorite.innerText = slide.dataset.favorite === '1' ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit';
                overlay.classList.add('active');
            });
        });

        overlay.addEventListener('click', function(e) {
            if(e.target === overlay) overlay.classList.remove('active');
        });

        // Favorite
        optFavorite.addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.remove('active');
            const slide = slides[currentIndex];
            const zikirId = slide.dataset.id;
            
            fetch(`{{ url('dzikir/favorite/toggle') }}/${zikirId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                slide.dataset.favorite = slide.dataset.favorite === '1' ? '0' : '1';
                // optional alert
            });
        });


        // --- RESET LOGIC ---
        document.getElementById('optReset').addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.remove('active');
            
            const slide = slides[currentIndex];
            const zikirId = slide.dataset.id;
            
            fetch('{{ route("dzikir.reset-progress") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ zikir_id: zikirId })
            }).then(r => r.json()).then(data => {
                if(data.success) {
                    slide.dataset.count = 0;
                    updateCounterUI();
                }
            });
        });

        // --- TARGET MODAL LOGIC ---
        const modalOverlay = document.getElementById('targetModalOverlay');
        const targetInput = document.getElementById('targetInput');

        document.getElementById('optTarget').addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.remove('active');
            const currentTarget = slides[currentIndex].dataset.target;
            targetInput.value = currentTarget;
            modalOverlay.classList.add('active');
            targetInput.focus();
        });

        document.getElementById('btnCancelTarget').addEventListener('click', function() {
            modalOverlay.classList.remove('active');
        });

        document.getElementById('btnSaveTarget').addEventListener('click', function() {
            const newTarget = parseInt(targetInput.value);
            if(isNaN(newTarget) || newTarget < 1) return;

            modalOverlay.classList.remove('active');
            
            const slide = slides[currentIndex];
            const zikirId = slide.dataset.id;
            
            fetch('{{ route("dzikir.update-target") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ zikir_id: zikirId, target_count: newTarget })
            }).then(r => r.json()).then(data => {
                if(data.success) {
                    slide.dataset.target = newTarget;
                    updateCounterUI();
                }
            });
        });

        // --- SHARE LOGIC ---
        const shareOverlay = document.getElementById('shareOverlay');
        const shareArabic = document.getElementById('shareArabic');
        const shareMeaning = document.getElementById('shareMeaning');
        const shareBg = document.getElementById('shareBg');
        const shareImages = [
            'https://images.unsplash.com/photo-1564121211835-e88c852648ab?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1563203369-26f2e4a5ccf7?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1585036156171-384164a8c675?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1572949645841-094f3a9c4c94?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1590077976868-468e21e6955a?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1501622329368-45e0f7fce367?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1603525143398-052441da00ba?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1580133318304-45b630e61899?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1565552636-e04db142eaeb?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1551041777-628f8f117c4f?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1523425553185-30fae7cf57e6?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1548232982-f3fbf238f983?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1594916893635-cba0686bc9a0?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1553556943-4e31464303d3?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1579707172777-d4f1cdbc21a4?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1566807810-0a56654b0365?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1519408469771-25860931d536?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1532767153582-b1a0e5145009?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1507608616785-3e0e150820f1?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1493246507139-91e8fad9978e?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1501862700950-18382cb914b4?q=80&w=800&auto=format&fit=crop'
        ];

        let imageErrorCount = 0;
        shareBg.onerror = function() {
            imageErrorCount++;
            if(imageErrorCount < 3) {
                // Retry with another random image
                this.src = shareImages[Math.floor(Math.random() * shareImages.length)];
            } else {
                // Fallback to elegant dark gradient if images keep failing
                this.style.display = 'none';
                document.getElementById('shareCard').style.background = 'linear-gradient(135deg, #0f1e19 0%, #061c23 100%)';
            }
        };

        shareBg.onload = function() {
            this.style.display = 'block';
            document.getElementById('shareCard').style.background = '#000';
        };

        document.getElementById('optShare').addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.remove('active');
            
            const slide = slides[currentIndex];
            const arabicText = slide.querySelector('.zp-arabic').innerText;
            const meaningText = slide.querySelector('.zp-meaning').innerText;
            
            shareArabic.innerText = arabicText;
            shareMeaning.innerText = meaningText;
            
            imageErrorCount = 0;
            shareBg.src = shareImages[Math.floor(Math.random() * shareImages.length)];
            
            shareOverlay.classList.add('active');
        });

        document.getElementById('btnShareCancel').addEventListener('click', function() {
            shareOverlay.classList.remove('active');
        });

        document.getElementById('btnShareConfirm').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerText;
            btn.innerText = 'MEMPROSES...';
            btn.disabled = true;

            html2canvas(document.getElementById('shareCard'), { 
                useCORS: true, 
                backgroundColor: '#000',
                scale: 2 // High resolution
            }).then(canvas => {
                canvas.toBlob(function(blob) {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    
                    const file = new File([blob], 'zikir-share.png', { type: 'image/png' });
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        navigator.share({
                            files: [file],
                            title: 'Bagikan Zikir',
                        }).then(() => {
                            shareOverlay.classList.remove('active');
                        }).catch(console.error);
                    } else {
                        // Fallback to download if browser doesn't support sharing files
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'Zikir-Harian.png';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        
                        shareOverlay.classList.remove('active');
                        alert('Gambar berhasil diunduh karena browser ini tidak mendukung fitur Berbagi Langsung.');
                    }
                });
            });
        });

    });
</script>
</body>
</html>
