<!-- Tambahkan di dalam <body>, sebelum <div class="scanner-wrapper"> -->

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

<!-- Tambahkan CSS -->
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
</style>

<!-- Tambahkan di dalam <script> -->
<script>
    // Cek maintenance status saat load
    function checkMaintenanceStatus() {
        // Ubah false ke true untuk aktifkan maintenance
        const isUnderMaintenance = false;
        
        if (isUnderMaintenance) {
            document.getElementById('maintenanceOverlay').style.display = 'flex';
            document.getElementById('qrSection').style.display = 'none';
        }
    }

    // Jalankan saat halaman load
    document.addEventListener('DOMContentLoaded', checkMaintenanceStatus);
</script>
