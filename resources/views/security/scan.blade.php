@extends('layout.master')

@section('content')
<div class="container-fluid">
    <!-- Maintenance Overlay -->
    <div class="maintenance-overlay" id="maintenanceOverlay">
        <div class="maintenance-content">
            <i class="fas fa-tools display-1 text-warning mb-3"></i>
            <h2 class="text-white fw-bold mb-2">Sistem Sedang Dirawat</h2>
            <p class="text-white-50 mb-4">Mohon maaf, sistem absensi sedang dalam perbaikan.</p>
            <p class="text-white-50 small">Estimasi waktu selesai: <span id="maintenanceTime">30 menit</span></p>
            <button class="btn btn-outline-light mt-4 rounded-pill px-4" onclick="checkMaintenanceStatus()">
                <i class="fas fa-sync-alt me-2"></i> Cek Status
            </button>
        </div>
    </div>

    <!-- Scanner Section -->
    <div class="scanner-wrapper" id="qrSection">
        <div class="scanner-container">
            <h1 class="mb-4">Pemindai QR Code</h1>
            <video id="scanner" width="100%" height="auto"></video>
            <div id="result" class="mt-3"></div>
        </div>
    </div>
</div>

<style>
    .maintenance-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #1e1e1e, #0d0d0d);
        z-index: 200;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .maintenance-content {
        text-align: center;
        padding: 40px 20px;
        max-width: 400px;
    }

    .scanner-wrapper {
        padding: 20px;
    }
</style>

<script>
    function checkMaintenanceStatus() {
        const isUnderMaintenance = false; // Ubah ke true untuk aktifkan
        
        if (isUnderMaintenance) {
            document.getElementById('maintenanceOverlay').style.display = 'flex';
            document.getElementById('qrSection').style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', checkMaintenanceStatus);
</script>
@endsection
