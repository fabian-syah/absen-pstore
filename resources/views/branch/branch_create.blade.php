@extends('layout.master')

@section('title')
    Tambah Cabang
@endsection

@section('heading')
    Tambah Cabang Baru
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Cabang Baru</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('branches.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Cabang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Contoh: PStore Condet" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="timezone">Zona Waktu</label>
                            <select class="form-control form-select" id="timezone" name="timezone" required>
                                <option value="Asia/Jakarta">WIB (Jakarta/Jawa/Sumatera/Kalbar)</option>
                                <option value="Asia/Makassar">WITA (Bali/Sulawesi/NTB/NTT/Kaltim/Kalsel)</option>
                                <option value="Asia/Jayapura">WIT (Papua/Maluku)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="kemenag_city_id">ID Kota Kemenag (Untuk Jadwal Shalat)</label>
                            <input type="text" class="form-control" id="kemenag_city_id" name="kemenag_city_id"
                                placeholder="Contoh: 1301 (Jakarta)" value="{{ old('kemenag_city_id') }}">
                            <small class="form-text text-muted">ID Kota dapat dicari di API MyQuran. Kosongkan jika tidak perlu jadwal shalat spesifik cabang.</small>
                        </div>

                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="address" class="fw-bold mb-0">Alamat Cabang</label>
                                <span id="create-address-sync-status" class="small text-muted" style="font-size: 11px;">
                                    <i class="mdi mdi-auto-fix text-primary me-0.5"></i>Auto-sync koordinat aktif
                                </span>
                            </div>
                            <textarea class="form-control" id="address" name="address"
                                rows="3" placeholder="Ketik alamat lengkap atau tempel link Google Maps cabang..." oninput="handleCreateAddressInputDebounced()" onpaste="setTimeout(() => geocodeCreateAddress(false), 200)">{{ old('address') }}</textarea>
                            <small class="form-text text-muted d-flex justify-content-between align-items-center mt-1">
                                <span>Ketik alamat atau tempel link Google Maps, sistem otomatis mengonversi ke teks alamat dan koordinat GPS.</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold" onclick="geocodeCreateAddress(true)" style="font-size: 11.5px;">
                                    <i class="mdi mdi-crosshairs-gps me-0.5"></i>Sinkronkan Sekarang
                                </button>
                            </small>
                        </div>

                        {{-- GEOFENCING / LOKASI ABSENSI MANDIRI (INTERAKTIF & MAP PICKER) --}}
                        <div class="card border p-3 p-md-4 mb-4 rounded-3" style="background-color: #f8fafc; border-color: #e2e8f0;">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                                        <i class="mdi mdi-map-marker-radius text-primary fs-5"></i>
                                        <span>Pengaturan Geofencing & Radius Absensi</span>
                                    </h6>
                                    <p class="text-secondary small mb-0">
                                        Tentukan titik koordinat GPS cabang dan batas radius toleransi absensi karyawan.
                                    </p>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold rounded-3 px-2.5 py-1" onclick="useCurrentAdminLocationForCreate(this)" style="min-height: 34px;">
                                        <i class="mdi mdi-crosshairs-gps me-1"></i> Kunci Posisi GPS Saya
                                    </button>
                                </div>
                            </div>

                            {{-- Peta Interaktif Leaflet (Google Satellite Hybrid) --}}
                            <div class="mb-3">
                                <div class="position-relative">
                                    <div id="branch-create-map" style="width: 100%; height: 300px; border-radius: 10px; border: 1px solid #cbd5e1; background: #0f172a;"></div>
                                    {{-- Layer Switcher Satelit vs Peta Jalan --}}
                                    <div class="position-absolute top-0 end-0 m-2 z-3 d-flex shadow-sm rounded-pill p-1 bg-white border border-slate-300" style="z-index: 1000;">
                                        <button type="button" class="btn btn-xs rounded-pill fw-semibold py-1 px-2.5 btn-primary" id="btn-create-layer-hybrid" onclick="setCreateMapLayer('hybrid')">
                                            <i class="mdi mdi-satellite-variant me-1"></i>Satelit
                                        </button>
                                        <button type="button" class="btn btn-xs rounded-pill fw-semibold py-1 px-2.5 btn-light text-secondary" id="btn-create-layer-streets" onclick="setCreateMapLayer('streets')">
                                            <i class="mdi mdi-map-outline me-1"></i>Peta Jalan
                                        </button>
                                    </div>
                                </div>
                                <div class="form-text small text-muted mt-1 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <span><i class="mdi mdi-information-outline me-1"></i>Tampilan foto udara satelit aktif. Klik pada peta atau geser pin biru untuk menentukan titik kantor cabang.</span>
                                    <a href="#" id="create-open-gmaps-link" target="_blank" rel="noopener noreferrer" class="text-primary small fw-semibold text-decoration-none d-none">
                                        <i class="mdi mdi-open-in-new me-0.5"></i> Buka di Google Maps
                                    </a>
                                </div>
                            </div>

                            <div class="row g-2 g-md-3">
                                <div class="col-6 col-md-4">
                                    <label for="latitude" class="form-label fw-bold small text-dark mb-1">
                                        <i class="mdi mdi-latitude text-primary me-0.5"></i>Latitude
                                    </label>
                                    <input type="number" step="any" class="form-control" id="latitude" name="latitude"
                                        value="{{ old('latitude') }}" placeholder="Contoh: -6.1952300" onchange="syncCreateMapFromInputs()" style="min-height: 42px;">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label for="longitude" class="form-label fw-bold small text-dark mb-1">
                                        <i class="mdi mdi-longitude text-primary me-0.5"></i>Longitude
                                    </label>
                                    <input type="number" step="any" class="form-control" id="longitude" name="longitude"
                                        value="{{ old('longitude') }}" placeholder="Contoh: 106.7865200" onchange="syncCreateMapFromInputs()" style="min-height: 42px;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="radius" class="form-label fw-bold small text-dark mb-1">
                                        <i class="mdi mdi-map-marker-radius-outline text-danger me-0.5"></i>Radius Absensi (Meter)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="radius" name="radius" min="10" max="5000"
                                            value="{{ old('radius', 100) }}" placeholder="100" oninput="syncCreateRadiusFromInput()" style="min-height: 42px;">
                                        <span class="input-group-text px-2.5 small">Meter</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Radius Presets --}}
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="small fw-semibold text-secondary">
                                    <i class="mdi mdi-tune-variant text-muted me-1"></i>Pilihan Cepat Radius:
                                </span>
                                <div class="d-flex gap-1.5 flex-wrap flex-grow-1 flex-sm-grow-0">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 flex-fill create-radius-preset-btn {{ (old('radius', 100) == 50) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(50)">50m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 flex-fill create-radius-preset-btn {{ (old('radius', 100) == 100) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(100)">100m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 flex-fill create-radius-preset-btn {{ (old('radius', 100) == 150) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(150)">150m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 flex-fill create-radius-preset-btn {{ (old('radius', 100) == 200) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(200)">200m</button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan</button>
                        <a href="{{ route('branches.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let createMap = null;
    let createMarker = null;
    let createCircle = null;

    const DEFAULT_CREATE_LAT = -6.1753924;
    const DEFAULT_CREATE_LNG = 106.8271528;

    document.addEventListener('DOMContentLoaded', function () {
        initBranchCreateMap();
    });

    let createHybridLayer = null;
    let createStreetsLayer = null;

    function setCreateMapLayer(type) {
        if (!createMap || !createHybridLayer || !createStreetsLayer) return;
        const btnHybrid = document.getElementById('btn-create-layer-hybrid');
        const btnStreets = document.getElementById('btn-create-layer-streets');

        if (type === 'hybrid') {
            if (createMap.hasLayer(createStreetsLayer)) createMap.removeLayer(createStreetsLayer);
            if (!createMap.hasLayer(createHybridLayer)) createMap.addLayer(createHybridLayer);
            if (btnHybrid) btnHybrid.className = 'btn btn-xs rounded-pill fw-semibold py-1 px-2.5 btn-primary';
            if (btnStreets) btnStreets.className = 'btn btn-xs rounded-pill fw-semibold py-1 px-2.5 btn-light text-secondary';
        } else {
            if (createMap.hasLayer(createHybridLayer)) createMap.removeLayer(createHybridLayer);
            if (!createMap.hasLayer(createStreetsLayer)) createMap.addLayer(createStreetsLayer);
            if (btnStreets) btnStreets.className = 'btn btn-xs rounded-pill fw-semibold py-1 px-2.5 btn-primary';
            if (btnHybrid) btnHybrid.className = 'btn btn-xs rounded-pill fw-semibold py-1 px-2.5 btn-light text-secondary';
        }
    }

    function initBranchCreateMap() {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const radiusInput = document.getElementById('radius');

        let initialLat = parseFloat(latInput.value);
        let initialLng = parseFloat(lngInput.value);
        let initialRadius = parseInt(radiusInput.value) || 100;

        const hasInitialCoords = !isNaN(initialLat) && !isNaN(initialLng);
        const mapCenterLat = hasInitialCoords ? initialLat : DEFAULT_CREATE_LAT;
        const mapCenterLng = hasInitialCoords ? initialLng : DEFAULT_CREATE_LNG;

        createMap = L.map('branch-create-map', {
            center: [mapCenterLat, mapCenterLng],
            zoom: hasInitialCoords ? 16 : 12,
            maxZoom: 20
        });

        // Layer 1: Google Satellite Hybrid (Foto Udara + Label Jalan & Gedung)
        createHybridLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        // Layer 2: Google Streets
        createStreetsLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        // Default: Satelit Hybrid
        createHybridLayer.addTo(createMap);

        if (hasInitialCoords) {
            createMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(createMap);
            createCircle = L.circle([initialLat, initialLng], {
                radius: initialRadius,
                color: '#38bdf8',
                fillColor: '#0284c7',
                fillOpacity: 0.28,
                weight: 2.5
            }).addTo(createMap);

            createMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateCreateCoords(pos.lat, pos.lng);
            });
        }

        createMap.on('click', function (e) {
            updateCreateCoords(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateCreateCoords(lat, lng) {
        document.getElementById('latitude').value = parseFloat(lat).toFixed(7);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(7);

        const radiusVal = parseInt(document.getElementById('radius').value) || 100;

        if (!createMarker) {
            createMarker = L.marker([lat, lng], { draggable: true }).addTo(createMap);
            createMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateCreateCoords(pos.lat, pos.lng);
            });
        } else {
            createMarker.setLatLng([lat, lng]);
        }

        if (!createCircle) {
            createCircle = L.circle([lat, lng], {
                radius: radiusVal,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.16,
                weight: 2
            }).addTo(createMap);
        } else {
            createCircle.setLatLng([lat, lng]);
            createCircle.setRadius(radiusVal);
        }

        createMap.panTo([lat, lng]);

        const gmapsLink = document.getElementById('create-open-gmaps-link');
        if (gmapsLink) {
            gmapsLink.href = `https://maps.google.com/?q=${lat},${lng}`;
            gmapsLink.classList.remove('d-none');
        }
    }

    function syncCreateMapFromInputs() {
        const latVal = parseFloat(document.getElementById('latitude').value);
        const lngVal = parseFloat(document.getElementById('longitude').value);

        if (!isNaN(latVal) && !isNaN(lngVal) && createMap) {
            updateCreateCoords(latVal, lngVal);
        }
    }

    function syncCreateRadiusFromInput() {
        const radiusVal = parseInt(document.getElementById('radius').value);
        if (!isNaN(radiusVal) && createCircle) {
            createCircle.setRadius(radiusVal);
        }
    }

    function setCreateRadiusPreset(radius) {
        document.getElementById('radius').value = radius;
        syncCreateRadiusFromInput();

        document.querySelectorAll('.create-radius-preset-btn').forEach(btn => {
            if (btn.textContent.trim() === `${radius}m`) {
                btn.classList.add('btn-primary', 'text-white', 'border-primary');
                btn.classList.remove('btn-outline-secondary');
            } else {
                btn.classList.remove('btn-primary', 'text-white', 'border-primary');
                btn.classList.add('btn-outline-secondary');
            }
        });
    }

    let createAddressTimeout = null;

    function handleCreateAddressInputDebounced() {
        clearTimeout(createAddressTimeout);
        const statusEl = document.getElementById('create-address-sync-status');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-primary"><i class="mdi mdi-loading mdi-spin me-0.5"></i>Mengetik alamat...</span>';
        }
        createAddressTimeout = setTimeout(() => {
            geocodeCreateAddress(false);
        }, 850);
    }

    function geocodeCreateAddress(isManual = false) {
        const addrInput = document.getElementById('address');
        if (!addrInput) return;
        const query = addrInput.value.trim();
        const statusEl = document.getElementById('create-address-sync-status');

        if (!query || query.length < 3) {
            if (statusEl) statusEl.innerHTML = '<span class="text-muted"><i class="mdi mdi-auto-fix text-primary me-0.5"></i>Auto-sync koordinat aktif</span>';
            return;
        }

        if (statusEl) {
            statusEl.innerHTML = '<span class="text-primary"><i class="mdi mdi-loading mdi-spin me-0.5"></i>Mencari di peta...</span>';
        }

        fetch(`/branches/geocode?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.lat && data.lng) {
                updateCreateCoords(data.lat, data.lng);
                if (createMap) createMap.flyTo([data.lat, data.lng], 16, { animate: true, duration: 1.0 });

                // Jika input berupa link Google Maps / koordinat dan berhasil dikonversi ke teks alamat
                if (data.resolved_address && (query.startsWith('http') || query.includes('maps') || query.includes('goo.gl') || query.includes('@') || query.includes('!3d') || query.includes('°'))) {
                    addrInput.value = data.resolved_address;
                    if (statusEl) statusEl.innerHTML = '<span class="text-success fw-bold"><i class="mdi mdi-check-circle me-0.5"></i>Link dikonversi ke teks alamat & peta sinkron!</span>';
                } else {
                    if (statusEl) statusEl.innerHTML = '<span class="text-success fw-bold"><i class="mdi mdi-check-circle me-0.5"></i>Peta & koordinat sinkron!</span>';
                }
            } else if (isManual) {
                if (statusEl) statusEl.innerHTML = '<span class="text-warning"><i class="mdi mdi-alert-circle me-0.5"></i>Alamat belum cocok di peta</span>';
                alert('Alamat belum ditemukan di peta. Coba lengkapi nama kota/kecamatan, atau geser pin langsung pada peta.');
            }
        })
        .catch(() => {
            if (statusEl) statusEl.innerHTML = '<span class="text-muted"><i class="mdi mdi-auto-fix text-primary me-0.5"></i>Auto-sync koordinat aktif</span>';
        });
    }

    function useCurrentAdminLocationForCreate(triggerBtn = null) {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung geolokasi GPS.');
            return;
        }

        const btn = triggerBtn || event?.currentTarget;
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunci...';
        }

        const resetBtn = () => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        };

        const handleSuccess = pos => {
            const { latitude, longitude } = pos.coords;
            updateCreateCoords(latitude, longitude);
            resetBtn();
        };

        // Percobaan 1: High Accuracy
        navigator.geolocation.getCurrentPosition(
            handleSuccess,
            errHigh => {
                if (errHigh.code === 1) {
                    resetBtn();
                    alert('Izin lokasi diblokir oleh browser atau sistem operasi. Pastikan izin lokasi aktif di browser & Pengaturan Windows.');
                    return;
                }

                // Percobaan 2: Fallback Akurasi Standar (Sangat cocok untuk Laptop & PC Windows)
                navigator.geolocation.getCurrentPosition(
                    handleSuccess,
                    errLow => {
                        resetBtn();
                        let errMsg = 'Gagal mendeteksi lokasi GPS.';
                        if (errLow.code === 1) {
                            errMsg = 'Izin lokasi tidak diizinkan di browser atau sistem operasi.';
                        } else if (errLow.code === 2) {
                            errMsg = 'Sinyal lokasi tidak tersedia di perangkat ini. Silakan klik langsung pada peta atau ketik koordinat.';
                        } else if (errLow.code === 3) {
                            errMsg = 'Waktu permintaan lokasi habis (timeout). Silakan klik langsung pada peta.';
                        }
                        alert(errMsg);
                    },
                    { enableHighAccuracy: false, timeout: 15000, maximumAge: 300000 }
                );
            },
            { enableHighAccuracy: true, timeout: 6000, maximumAge: 60000 }
        );
    }
</script>
@endpush