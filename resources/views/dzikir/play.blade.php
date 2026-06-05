@extends('layout.master')

@section('title', 'Zikir Player')

@push('styles')
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
        overflow: hidden; /* Lock scroll, app-like */
        background-color: #061c23; /* Dark base */
    }
    
    .content-wrapper.zikir-player {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(180deg, #0d3440 0%, #061c23 100%);
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
</style>
@endpush

@section('content')
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
                    $target = $zikir->default_target ?? 33;
                    $progress = isset($activities[$zikir->id]) ? $activities[$zikir->id]->total_count : 0;
                @endphp
                <div class="zp-slide" 
                     data-id="{{ $zikir->id }}" 
                     data-target="{{ $target }}" 
                     data-count="{{ $progress }}">
                    
                    {{-- Text Card --}}
                    <div class="zp-card-text">
                        <div class="zp-card-icons">
                            <a href="#" class="zp-icon-btn"><span class="material-symbols-outlined" style="font-size: 20px;">more_vert</span></a>
                            <a href="#" class="zp-icon-btn"><span class="material-symbols-outlined" style="font-size: 20px;">info</span></a>
                        </div>
                        
                        <div class="zp-category">{{ strtoupper($zikir->title) }}</div>
                        <div class="zp-arabic">{{ $zikir->arabic_text ?? 'لا إله إلا الله' }}</div>
                        <div class="zp-latin">{{ $zikir->latin_text ?? 'La ilaaha illallaah.' }}</div>
                        <div class="zp-meaning">{{ $zikir->meaning ?? 'Tiada Tuhan (yang berhak disembah) kecuali Allah.' }}</div>
                        
                        <div class="zp-play-btn">
                            <span class="material-symbols-outlined">play_arrow</span>
                        </div>
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
                            <div class="zp-bottom-number"></div>
                            <span class="material-symbols-outlined zp-mic-icon">mic</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="zp-footer">
        <a href="#" class="zp-options-btn">
            <span class="material-symbols-outlined">more_horiz</span>
        </a>
        <a href="{{ route('dzikir.umum') }}" class="zp-close-btn">
            <span class="material-symbols-outlined">close</span>
        </a>
    </div>
</div>

<form id="progressForm" action="{{ route('dzikir.progress') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="zikir_id" id="formZikirId">
    <input type="hidden" name="count" id="formCount">
</form>

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

        window.incrementCounter = function() {
            if(totalSlides === 0) return;
            const slide = slides[currentIndex];
            let count = parseInt(slide.dataset.count) || 0;
            const target = parseInt(slide.dataset.target) || 33;
            
            // Increment
            count++;
            
            // Limit to target? Usually yes, or allow overflow. Let's allow overflow but cap UI if needed.
            // Or reset if it hits target? Let's just increment.
            slide.dataset.count = count;
            
            // Haptic feedback
            if (window.navigator && window.navigator.vibrate) {
                if (count === target) window.navigator.vibrate([100, 50, 100]); // special vibe
                else window.navigator.vibrate(20);
            }

            updateCounterUI();
            debouncedSave(slide.dataset.id, count);
        };

        function updateCounterUI() {
            if(totalSlides === 0) return;
            const slide = slides[currentIndex];
            const count = parseInt(slide.dataset.count) || 0;
            const target = parseInt(slide.dataset.target) || 33;
            
            const countEl = slide.querySelector('.zp-count');
            const fillEl = slide.querySelector('.zp-progress-fill');
            const counterCard = slide.querySelector('.zp-card-counter');
            
            if(countEl) countEl.innerText = count;
            
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

        function debouncedSave(zikirId, count) {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const form = document.getElementById('progressForm');
                document.getElementById('formZikirId').value = zikirId;
                document.getElementById('formCount').value = count;
                
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(res => res.json())
                  .then(data => console.log('Saved:', data))
                  .catch(err => console.error(err));
            }, 1000); // Save to DB 1 sec after last tap
        }

        // Hide browser native UI (if installed as PWA)
        document.body.style.overflow = 'hidden';
        const sidebar = document.querySelector('.sidebar');
        const navbar = document.querySelector('.navbar');
        if(sidebar) sidebar.style.display = 'none';
        if(navbar) navbar.style.display = 'none';
    });
</script>
@endpush
@endsection
