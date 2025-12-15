@extends('layout.master')

@section('title', 'Maintenance Mode')
@section('heading', 'Sistem Sedang Diperbaiki')

@section('content')
<style>
    .maintenance-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
    }
    
    .maintenance-card {
        background: white;
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        max-width: 600px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .maintenance-icon {
        font-size: 80px;
        color: #667eea;
        margin-bottom: 30px;
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .maintenance-title {
        font-size: 32px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
    }
    
    .maintenance-subtitle {
        font-size: 16px;
        color: #718096;
        margin-bottom: 40px;
        line-height: 1.6;
    }
    
    .countdown-wrapper {
        background: #f7fafc;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 40px;
        border: 2px solid #e2e8f0;
    }
    
    .countdown-title {
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }
    
    .countdown-display {
        font-size: 48px;
        font-weight: 700;
        color: #667eea;
        font-family: 'Courier New', monospace;
        letter-spacing: 5px;
    }
    
    .countdown-unit {
        font-size: 12px;
        color: #718096;
        margin-top: 10px;
    }
    
    .contact-section {
        background: #edf2f7;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .contact-title {
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .contact-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .contact-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border-radius: 8px;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .contact-link:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .status-badge {
        display: inline-block;
        background: #fef5e7;
        color: #d68910;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .progress-bar-wrapper {
        margin-top: 30px;
    }
    
    .progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        animation: fillProgress 60s linear forwards;
    }
    
    @keyframes fillProgress {
        0% { width: 0%; }
        100% { width: 100%; }
    }
    
    .info-text {
        font-size: 13px;
        color: #718096;
        margin-top: 15px;
        line-height: 1.6;
    }
</style>

<div class="maintenance-container">
    <div class="maintenance-card">
        <div class="status-badge">
            <i class="mdi mdi-wrench me-2"></i> SEDANG DALAM PERBAIKAN
        </div>
        
        <div class="maintenance-icon">
            <i class="mdi mdi-tools"></i>
        </div>
        
        <h1 class="maintenance-title">Fitur Sedang Diperbaiki</h1>
        <p class="maintenance-subtitle">
            Sistem sedang menjalani perbaikan dan pembaruan untuk memberikan pengalaman terbaik. 
            Kami akan kembali dalam waktu singkat!
        </p>
        
        {{-- COUNTDOWN TIMER --}}
        <div class="countdown-wrapper">
            <div class="countdown-title">Estimasi Waktu Selesai</div>
            <div class="countdown-display" id="countdown">01:00:00</div>
            <div class="countdown-unit">Jam : Menit : Detik</div>
        </div>
        
        {{-- PROGRESS BAR --}}
        <div class="progress-bar-wrapper">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p class="info-text">Progres perbaikan sistem sedang berlangsung...</p>
        </div>
        
        {{-- CONTACT SECTION --}}
        <div class="contact-section">
            <div class="contact-title">
                <i class="mdi mdi-information-outline me-2"></i> Butuh Informasi Lebih Lanjut?
            </div>
            <div class="contact-info">
                <a href="https://instagram.com/mcisreal_" target="_blank" class="contact-link">
                    <i class="mdi mdi-instagram"></i>
                    <span>@mcisreal_</span>
                </a>
            </div>
            <p class="info-text mt-3">
                Chat kami di Instagram untuk informasi lengkap atau bantuan teknis
            </p>
        </div>
        
        {{-- BACK BUTTON --}}
        <a href="javascript:history.back()" class="btn btn-outline-primary">
            <i class="mdi mdi-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdownElement = document.getElementById('countdown');
    const endTime = new Date().getTime() + (60 * 60 * 1000); // 1 jam dari sekarang

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            countdownElement.textContent = '00:00:00';
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.textContent = 
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
@endpush
