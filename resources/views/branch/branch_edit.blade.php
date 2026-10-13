@extends('layout.master')

@section('title')
    Edit Cabang
@endsection

@section('heading')
    Edit Cabang: {{ $branch->name }}
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Form Edit Cabang</h4>
                        <span class="badge {{ $branch->is_active ? 'bg-success' : 'bg-danger' }}">
                            Current Status: {{ $branch->is_active ? 'BUKA' : 'TUTUP' }}
                        </span>
                    </div>

                    {{-- Error Validation --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('branches.update', $branch->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama Cabang --}}
                        <div class="form-group mb-3">
                            <label for="name" class="fw-bold mb-1">Nama Cabang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $branch->name) }}" placeholder="Contoh: Pstore Jakarta Selatan" required>
                        </div>

                        {{-- Zona Waktu (BARU) --}}
                        <div class="form-group mb-3">
                            <label for="timezone" class="fw-bold mb-1">Zona Waktu</label>
                            <select class="form-control form-select" id="timezone" name="timezone" required>
                                <option value="Asia/Jakarta" {{ $branch->timezone == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Jakarta)</option>
                                <option value="Asia/Makassar" {{ $branch->timezone == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Makassar/Bali)</option>
                                <option value="Asia/Jayapura" {{ $branch->timezone == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Jayapura)</option>
                            </select>
                        </div>

                        {{-- Kemenag City ID --}}
                        <div class="form-group mb-3">
                            <label for="kemenag_city_id" class="fw-bold mb-1">ID Kota Kemenag (Untuk Jadwal Shalat)</label>
                            <input type="text" class="form-control" id="kemenag_city_id" name="kemenag_city_id"
                                value="{{ old('kemenag_city_id', $branch->kemenag_city_id) }}" placeholder="Contoh: 1301 (Jakarta)">
                            <small class="form-text text-muted">ID Kota dapat dicari di API MyQuran. Kosongkan jika tidak perlu jadwal shalat spesifik cabang.</small>
                        </div>

                        {{-- Alamat Cabang --}}
                        <div class="form-group mb-3">
                            <label for="address" class="fw-bold mb-1">Alamat Cabang</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                placeholder="Masukkan alamat lengkap cabang">{{ old('address', $branch->address) }}</textarea>
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
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold rounded-3 px-2.5 py-1" onclick="useCurrentAdminLocationForEdit()" style="min-height: 34px;">
                                        <i class="mdi mdi-crosshairs-gps me-1"></i> Kunci Posisi GPS Saya
                                    </button>
                                    @if(auth()->user()->role == 'admin')
                                        <a href="{{ route('branches.geofence.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-2.5 py-1" title="Buka Console Pengaturan Semua Cabang" style="min-height: 34px;">
                                            <i class="mdi mdi-tune-vertical me-1"></i> Console Semua Cabang
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Peta Interaktif Leaflet --}}
                            <div class="mb-3">
                                <div id="branch-edit-map" style="width: 100%; height: 260px; border-radius: 10px; border: 1px solid #cbd5e1; background: #ffffff;"></div>
                                <div class="form-text small text-muted mt-1 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <span><i class="mdi mdi-information-outline me-1"></i>Klik pada peta atau geser pin merah untuk menggeser koordinat titik cabang secara presisi.</span>
                                    <a href="#" id="edit-open-gmaps-link" target="_blank" rel="noopener noreferrer" class="text-primary small fw-semibold text-decoration-none {{ ($branch->latitude && $branch->longitude) ? '' : 'd-none' }}">
                                        <i class="mdi mdi-open-in-new me-0.5"></i> Buka di Google Maps
                                    </a>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label for="latitude" class="form-label fw-bold small text-dark mb-1">Latitude</label>
                                    <input type="number" step="any" class="form-control" id="latitude" name="latitude"
                                        value="{{ old('latitude', $branch->latitude) }}" placeholder="Contoh: -6.1952300" onchange="syncEditMapFromInputs()" style="min-height: 44px;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="longitude" class="form-label fw-bold small text-dark mb-1">Longitude</label>
                                    <input type="number" step="any" class="form-control" id="longitude" name="longitude"
                                        value="{{ old('longitude', $branch->longitude) }}" placeholder="Contoh: 106.7865200" onchange="syncEditMapFromInputs()" style="min-height: 44px;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="radius" class="form-label fw-bold small text-dark mb-1">Radius Absensi (Meter)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="radius" name="radius" min="10" max="5000"
                                            value="{{ old('radius', $branch->radius ?? 100) }}" placeholder="100" oninput="syncEditRadiusFromInput()" style="min-height: 44px;">
                                        <span class="input-group-text">Meter</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Radius Presets --}}
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="small fw-semibold text-secondary">Pilihan Cepat Radius:</span>
                                <div class="d-flex gap-1.5 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 edit-radius-preset-btn {{ (old('radius', $branch->radius ?? 100) == 50) ? 'btn-primary text-white border-primary' : '' }}" onclick="setEditRadiusPreset(50)">50m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 edit-radius-preset-btn {{ (old('radius', $branch->radius ?? 100) == 100) ? 'btn-primary text-white border-primary' : '' }}" onclick="setEditRadiusPreset(100)">100m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 edit-radius-preset-btn {{ (old('radius', $branch->radius ?? 100) == 150) ? 'btn-primary text-white border-primary' : '' }}" onclick="setEditRadiusPreset(150)">150m</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 edit-radius-preset-btn {{ (old('radius', $branch->radius ?? 100) == 200) ? 'btn-primary text-white border-primary' : '' }}" onclick="setEditRadiusPreset(200)">200m</button>
                                </div>
                            </div>
                        </div>

                        {{-- STATUS OPERASIONAL --}}
                        <div class="form-group mb-4">
                            <label for="is_active" class="fw-bold mb-1">Status Operasional</label>
                            <select class="form-control form-select" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $branch->is_active) == 1 ? 'selected' : '' }}>
                                    🟢 Buka (Aktif)
                                </option>
                                <option value="0" {{ old('is_active', $branch->is_active) == 0 ? 'selected' : '' }}>
                                    🔴 Tutup (Non-Aktif)
                                </option>
                            </select>
                            <small class="text-muted">
                                *Jika status diubah menjadi <strong>Tutup</strong>, cabang akan ditandai merah dan dicoret pada daftar utama.
                            </small>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="mdi mdi-content-save me-1"></i> Update Data
                            </button>
                            <a href="{{ route('branches.index') }}" class="btn btn-light px-4">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let editMap = null;
    let editMarker = null;
    let editCircle = null;

    const DEFAULT_EDIT_LAT = -6.1753924;
    const DEFAULT_EDIT_LNG = 106.8271528;

    document.addEventListener('DOMContentLoaded', function () {
        initBranchEditMap();
    });

    function initBranchEditMap() {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const radiusInput = document.getElementById('radius');

        let initialLat = parseFloat(latInput.value);
        let initialLng = parseFloat(lngInput.value);
        let initialRadius = parseInt(radiusInput.value) || 100;

        const hasInitialCoords = !isNaN(initialLat) && !isNaN(initialLng);
        const mapCenterLat = hasInitialCoords ? initialLat : DEFAULT_EDIT_LAT;
        const mapCenterLng = hasInitialCoords ? initialLng : DEFAULT_EDIT_LNG;

        editMap = L.map('branch-edit-map').setView([mapCenterLat, mapCenterLng], hasInitialCoords ? 16 : 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(editMap);

        if (hasInitialCoords) {
            editMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(editMap);
            editCircle = L.circle([initialLat, initialLng], {
                radius: initialRadius,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.16,
                weight: 2
            }).addTo(editMap);

            editMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateEditCoords(pos.lat, pos.lng);
            });
        }

        editMap.on('click', function (e) {
            updateEditCoords(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateEditCoords(lat, lng) {
        document.getElementById('latitude').value = parseFloat(lat).toFixed(7);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(7);

        const radiusVal = parseInt(document.getElementById('radius').value) || 100;

        if (!editMarker) {
            editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
            editMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateEditCoords(pos.lat, pos.lng);
            });
        } else {
            editMarker.setLatLng([lat, lng]);
        }

        if (!editCircle) {
            editCircle = L.circle([lat, lng], {
                radius: radiusVal,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.16,
                weight: 2
            }).addTo(editMap);
        } else {
            editCircle.setLatLng([lat, lng]);
            editCircle.setRadius(radiusVal);
        }

        editMap.panTo([lat, lng]);

        const gmapsLink = document.getElementById('edit-open-gmaps-link');
        if (gmapsLink) {
            gmapsLink.href = `https://maps.google.com/?q=${lat},${lng}`;
            gmapsLink.classList.remove('d-none');
        }
    }

    function syncEditMapFromInputs() {
        const latVal = parseFloat(document.getElementById('latitude').value);
        const lngVal = parseFloat(document.getElementById('longitude').value);

        if (!isNaN(latVal) && !isNaN(lngVal) && editMap) {
            updateEditCoords(latVal, lngVal);
        }
    }

    function syncEditRadiusFromInput() {
        const radiusVal = parseInt(document.getElementById('radius').value);
        if (!isNaN(radiusVal) && editCircle) {
            editCircle.setRadius(radiusVal);
        }
    }

    function setEditRadiusPreset(radius) {
        document.getElementById('radius').value = radius;
        syncEditRadiusFromInput();

        document.querySelectorAll('.edit-radius-preset-btn').forEach(btn => {
            if (btn.textContent.trim() === `${radius}m`) {
                btn.classList.add('btn-primary', 'text-white', 'border-primary');
                btn.classList.remove('btn-outline-secondary');
            } else {
                btn.classList.remove('btn-primary', 'text-white', 'border-primary');
                btn.classList.add('btn-outline-secondary');
            }
        });
    }

    function useCurrentAdminLocationForEdit() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung geolokasi GPS.');
            return;
        }

        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunci...';

        navigator.geolocation.getCurrentPosition(
            pos => {
                const { latitude, longitude } = pos.coords;
                updateEditCoords(latitude, longitude);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            },
            err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Gagal mendeteksi lokasi GPS. Pastikan izin lokasi browser aktif.');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
</script>
@endpush