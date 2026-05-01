@extends('layout.master')

@section('title', 'Dzikir Online')

@section('content')
<div class="content-wrapper d-flex flex-column align-items-center justify-content-center" style="min-height: calc(100vh - 150px);">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card glass-effect border-0 shadow-lg text-center p-4 overflow-hidden" style="border-radius: 40px; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(25px); position: relative;">
                {{-- Decorative circles --}}
                <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(13, 110, 253, 0.05); border-radius: 50%; z-index: 0;"></div>
                <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(13, 110, 253, 0.05); border-radius: 50%; z-index: 0;"></div>
                
                <div class="card-body" style="position: relative; z-index: 1;">
                    <div class="d-flex align-items-center justify-content-center mb-4">
                        <i class="mdi mdi-hands-pray mr-2" style="font-size: 2rem; color: var(--pstore-primary);"></i>
                        <h2 class="mb-0 font-weight-bold gradient-text" style="font-size: 2.2rem; letter-spacing: -1px;">Dzikir Online</h2>
                    </div>
                    
                    <div class="counter-container mb-5">
                        <div id="counter" class="display-1 font-weight-bold mb-0" style="font-size: 8rem; color: var(--pstore-primary); text-shadow: 0 15px 35px rgba(13, 110, 253, 0.25); line-height: 1;">0</div>
                        <p class="text-muted font-weight-medium mt-2" style="letter-spacing: 2px; text-transform: uppercase; font-size: 0.8rem;">Jumlah Hitungan</p>
                    </div>

                    <div class="dzikir-button-container mb-5 d-flex justify-content-center">
                        <div class="tap-circle-outer" style="padding: 15px; background: rgba(13, 110, 253, 0.05); border-radius: 50%; box-shadow: inset 0 0 20px rgba(0,0,0,0.02);">
                            <button id="clickBtn" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" 
                                style="width: 180px; height: 180px; font-size: 2.5rem; border: 8px solid #fff; box-shadow: 0 15px 45px rgba(13, 110, 253, 0.4);">
                                <div class="d-flex flex-column align-items-center">
                                    <span style="font-weight: 800;">TAP</span>
                                    <i class="mdi mdi-fingerprint mt-n2" style="font-size: 1.5rem; opacity: 0.6;"></i>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="actions-container d-flex justify-content-center align-items-center gap-4">
                        <button id="resetBtn" class="btn btn-link text-danger p-0" style="text-decoration: none; font-weight: 600; font-size: 1rem;">
                            <i class="mdi mdi-refresh-circle mr-1" style="font-size: 1.5rem; vertical-align: middle;"></i> Reset
                        </button>
                        
                        <div class="haptic-toggle d-flex align-items-center ml-4">
                             <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="vibrateSwitch" checked>
                                <label class="custom-control-label font-weight-bold text-muted" for="vibrateSwitch">Feedback</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="text-center mt-4 text-muted" style="font-size: 0.9rem; opacity: 0.8;">
                <i class="mdi mdi-information-outline"></i> Klik tombol untuk menghitung. Feedback berupa Getar (Android), Suara, dan Animasi.
            </p>
        </div>
    </div>
</div>

{{-- Hidden Audio for Feedback --}}
<audio id="clickSound" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3" type="audio/mpeg">
</audio>

@push('styles')
<style>
    .gradient-text {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    #counter {
        transition: all 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    #clickBtn {
        transition: all 0.05s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        -webkit-tap-highlight-color: transparent;
        position: relative;
        overflow: hidden;
    }

    #clickBtn:active {
        transform: scale(0.9) !important;
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2) !important;
    }

    .ripple {
        position: absolute;
        background: rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        transform: scale(0);
        animation: ripple-animation 0.4s linear;
        pointer-events: none;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    .counter-bump {
        transform: scale(1.2) translateY(-10px);
        color: #0a58ca !important;
    }

    /* Shake Animation for Feedback */
    @keyframes shake {
        0% { transform: translate(1px, 1px) rotate(0deg); }
        10% { transform: translate(-1px, -2px) rotate(-1deg); }
        20% { transform: translate(-3px, 0px) rotate(1deg); }
        30% { transform: translate(3px, 2px) rotate(0deg); }
        40% { transform: translate(1px, -1px) rotate(1deg); }
        50% { transform: translate(-1px, 2px) rotate(-1deg); }
        60% { transform: translate(-3px, 1px) rotate(0deg); }
        70% { transform: translate(3px, 1px) rotate(-1deg); }
        80% { transform: translate(-1px, -1px) rotate(1deg); }
        90% { transform: translate(1px, 2px) rotate(0deg); }
        100% { transform: translate(1px, -2px) rotate(-1deg); }
    }

    .shake-effect {
        animation: shake 0.2s;
    }

    /* Modern Switch Styling */
    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--pstore-primary);
        border-color: var(--pstore-primary);
    }

    @media (max-width: 576px) {
        #counter { font-size: 6rem !important; }
        #clickBtn { width: 150px !important; height: 150px !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const counterEl = document.getElementById('counter');
        const clickBtn = document.getElementById('clickBtn');
        const resetBtn = document.getElementById('resetBtn');
        const vibrateSwitch = document.getElementById('vibrateSwitch');
        const clickSound = document.getElementById('clickSound');
        const cardBody = document.querySelector('.card-body');
        
        // Load initial count
        let count = parseInt(localStorage.getItem('dzikir_count')) || 0;
        counterEl.innerText = count;

        function updateCounter(newCount) {
            count = newCount;
            counterEl.innerText = count;
            localStorage.setItem('dzikir_count', count);
            
            if (!vibrateSwitch.checked) return;

            // 1. Animation bump for counter
            counterEl.classList.add('counter-bump');
            setTimeout(() => counterEl.classList.remove('counter-bump'), 100);

            // 2. Shake effect for whole card (Haptic alternative for iOS/Desktop)
            cardBody.classList.add('shake-effect');
            setTimeout(() => cardBody.classList.remove('shake-effect'), 200);

            // 3. Audio Feedback (Works on all devices including iOS)
            clickSound.currentTime = 0;
            clickSound.play().catch(e => console.log('Audio play blocked'));

            // 4. Actual Vibration (Android Only, ignored by iOS)
            if ("vibrate" in navigator) {
                navigator.vibrate(50);
            }
        }

        // Handle Tap/Click
        const handleTap = (e) => {
            updateCounter(count + 1);
            
            // Ripple Effect
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            clickBtn.appendChild(ripple);
            
            const rect = clickBtn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = `${size}px`;
            
            // Check if it's a touch event or mouse event
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            
            if (clientX && clientY) {
                const x = clientX - rect.left - size / 2;
                const y = clientY - rect.top - size / 2;
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
            } else {
                ripple.style.left = '50%';
                ripple.style.top = '50%';
                ripple.style.transform = 'translate(-50%, -50%) scale(0)';
            }
            
            setTimeout(() => ripple.remove(), 600);
        };

        // Standard click for desktop
        clickBtn.addEventListener('mousedown', handleTap);

        // Touch event for mobile (faster response)
        clickBtn.addEventListener('touchstart', function(e) {
            e.preventDefault(); // Prevent ghost clicks
            handleTap(e);
        }, { passive: false });

        // Reset Logic
        resetBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Reset Hitungan?',
                text: "Hitungan akan kembali ke nol.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateCounter(0);
                }
            });
        });
    });
</script>
@endpush
@endsection
