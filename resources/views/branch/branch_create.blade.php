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
                            <label for="address">Alamat Cabang</label>
                            <textarea class="form-control" id="address" name="address"
                                rows="3">{{ old('address') }}</textarea>
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

                            {{-- Peta Interaktif Leaflet --}}
                            <div class="mb-3">
                                <div id="branch-create-map" style="width: 100%; height: 260px; border-radius: 10px; border: 1px solid #cbd5e1; background: #ffffff;"></div>
                                <div class="form-text small text-muted mt-1 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <span><i class="mdi mdi-information-outline me-1"></i>Klik pada peta atau geser pin merah untuk menggeser koordinat titik cabang secara presisi.</span>
                                    <a href="#" id="create-open-gmaps-link" target="_blank" rel="noopener noreferrer" class="text-primary small fw-semibold text-decoration-none d-none">
                                        <i class="mdi mdi-open-in-new me-0.5"></i> Buka di Google Maps
                                    </a>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label for="latitude" class="form-label fw-bold small text-dark mb-1">Latitude</label>
                                    <input type="number" step="any" class="form-control" id="latitude" name="latitude"
                                        value="{{ old('latitude') }}" placeholder="Contoh: -6.1952300" onchange="syncCreateMapFromInputs()" style="min-height: 44px;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="longitude" class="form-label fw-bold small text-dark mb-1">Longitude</label>
                                    <input type="number" step="any" class="form-control" id="longitude" name="longitude"
                                        value="{{ old('longitude') }}" placeholder="Contoh: 106.7865200" onchange="syncCreateMapFromInputs()" style="min-height: 44px;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="radius" class="form-label fw-bold small text-dark mb-1">Radius Absensi (Meter)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="radius" name="radius" min="10" max="5000"
                                            value="{{ old('radius', 100) }}" placeholder="100" oninput="syncCreateRadiusFromInput()" style="min-height: 44px;">
                                        <span class="input-group-text">Meter</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Radius Presets --}}
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="small fw-semibold text-secondary">Pilihan Cepat Radius:</span>
                                <div class="d-flex gap-1.5 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 create-radius-preset-btn {{ (old('radius', 100) == 50) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(50)">50m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 create-radius-preset-btn {{ (old('radius', 100) == 100) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(100)">100m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 create-radius-preset-btn {{ (old('radius', 100) == 150) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(150)">150m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 create-radius-preset-btn {{ (old('radius', 100) == 200) ? 'btn-primary text-white border-primary' : '' }}" onclick="setCreateRadiusPreset(200)">200m</button>
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

        createMap = L.map('branch-create-map').setView([mapCenterLat, mapCenterLng], hasInitialCoords ? 16 : 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(createMap);

        if (hasInitialCoords) {
            createMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(createMap);
            createCircle = L.circle([initialLat, initialLng], {
                radius: initialRadius,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.16,
                weight: 2
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