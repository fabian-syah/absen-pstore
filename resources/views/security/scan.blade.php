<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemindai QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container-fluid">
    <!-- Maintenance Overlay -->
    <div class="maintenance-overlay" id="maintenanceOverlay">
        <div class="maintenance-content">
            <i class="fas fa-tools display-1 text-warning mb-3"></i>
            <h2 class="text-white fw-bold mb-2">Sistem Sedang Dirawat</h2>
            <p class="text-white-50 mb-4">Mohon maaf, sistem absensi sedang dalam perbaikan.</p>
            <p class="text-white-50 small">Estimasi waktu selesai: <span id="maintenanceTime">30 menit</span></p>
            <div class="mt-3 mb-3">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <p class="text-white-50 small mb-4" id="statusMessage">Mengecek status sistem...</p>
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
        const statusMessage = document.getElementById('statusMessage');
        const button = event.target;
        
        button.disabled = true;
        statusMessage.textContent = 'Mengecek status sistem...';
        
        setTimeout(() => {
            const isUnderMaintenance = true;
            
            if (isUnderMaintenance) {
                statusMessage.textContent = 'Sistem masih dalam perbaikan. Coba lagi nanti.';
                button.disabled = false;
            }
        }, 1500);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('maintenanceOverlay').style.display = 'flex';
        document.getElementById('qrSection').style.display = 'none';
    });
</script>
</body>
</html>
