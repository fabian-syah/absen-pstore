@extends('layout.master')

@section('title')
    Pengaturan Lokasi & Radius Cabang
@endsection

@section('heading')
    Pengaturan Lokasi & Radius Cabang
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* =========================================================================
       MODERN GEOFENCING SETTING CONSOLE DESIGN SYSTEM
       ========================================================================= */
    :root {
        --geo-primary: #0d6efd;
        --geo-primary-soft: rgba(13, 110, 253, 0.08);
        --geo-success: #10b981;
        --geo-success-soft: rgba(16, 185, 129, 0.1);
        --geo-warning: #f59e0b;
        --geo-warning-soft: rgba(245, 158, 11, 0.1);
        --geo-slate-800: #1e293b;
        --geo-slate-500: #64748b;
        --geo-border: #e2e8f0;
    }

    .geo-header-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #0d6efd 50%, #0284c7 100%);
        border-radius: 18px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .geo-header-card::after {
        content: "";
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid var(--geo-border);
        border-radius: 16px;
        padding: 20px;
        transition: all 0.25s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }

    .metric-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    /* Tabs Styling */
    .nav-tabs-custom {
        border-bottom: 2px solid var(--geo-border);
        gap: 8px;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: var(--geo-slate-500);
        font-weight: 600;
        font-size: 14.5px;
        padding: 12px 20px;
        border-radius: 12px 12px 0 0;
        position: relative;
        background: transparent;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .nav-tabs-custom .nav-link:hover {
        color: var(--geo-primary);
        background: rgba(13, 110, 253, 0.04);
    }

    .nav-tabs-custom .nav-link.active {
        color: var(--geo-primary);
        background: #ffffff;
        border-bottom: 3px solid var(--geo-primary);
    }

    .nav-tabs-custom .nav-link .badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 9999px;
    }

    /* Table & Rows */
    .branch-table th {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #475569;
        background-color: #f8fafc;
        border-top: none;
        padding: 14px 16px;
    }

    .branch-table td {
        padding: 16px;
        vertical-align: middle;
        font-size: 13px;
        border-color: #f1f5f9;
    }

    .branch-table tr:hover td {
        background-color: #fbfcfe;
    }

    .coord-badge {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 11px;
        background: #f1f5f9;
        color: #334155;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .radius-pill {
        font-weight: 700;
        font-size: 11.5px;
        padding: 4px 10px;
        border-radius: 9999px;
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Map Box in Modal */
    #modal-map {
        width: 100%;
        height: 280px;
        border-radius: 14px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        z-index: 1;
    }

    .radius-preset-btn {
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        transition: all 0.15s ease;
    }

    .radius-preset-btn:hover,
    .radius-preset-btn.active {
        background: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
    }

    .search-input-box {
        position: relative;
    }

    .search-input-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 17px;
    }

    .search-input-box input {
        padding-left: 42px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        font-size: 13.5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle-outline fs-4 me-2 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER CARD --}}
    <div class="geo-header-card p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-12 col-lg-8">
                <div class="d-inline-flex align-items-center bg-white bg-opacity-20 px-3 py-1 rounded-pill mb-3" style="font-size: 11.5px; font-weight: 700; letter-spacing: 0.5px;">
                    <i class="mdi mdi-shield-check me-1.5"></i> KHUSUS ROLE ADMINISTRATOR
                </div>
                <h3 class="fw-bold mb-2 text-white" style="font-size: 26px;">
                    Pengaturan Lokasi & Radius Absensi Cabang
                </h3>
                <p class="text-white text-opacity-85 mb-0" style="font-size: 13.5px; max-width: 650px;">
                    Kelola koordinat GPS (Latitude/Longitude), alamat resmi, dan batas radius toleransi absensi untuk seluruh 
                    <strong>Kantor Pusat</strong> dan <strong>Store Cabang Daerah</strong> di seluruh Indonesia.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="button" class="btn btn-light shadow-sm fw-bold px-3 py-2 rounded-3 text-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#batchRadiusModal">
                        <i class="mdi mdi-tune-vertical me-1.5 fs-5"></i>
                        <span>Atur Radius Massal</span>
                    </button>
                    <a href="{{ route('branches.index') }}" class="btn btn-outline-light px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center">
                        <i class="mdi mdi-format-list-bulleted me-1.5"></i>
                        <span>Data Cabang</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS METRIC ROW --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Unit Pusat --}}
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-box" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="mdi mdi-office-building"></i>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                        PUSAT
                    </span>
                </div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Unit & Kantor Pusat</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold text-dark fs-3">{{ $pusatCount }}</span>
                    <span class="text-muted small" style="font-size: 11.5px;">Unit</span>
                </div>
                <div class="text-success small fw-semibold mt-1" style="font-size: 11px;">
                    <i class="mdi mdi-check-circle me-1"></i>{{ $pusatActiveCount }} Geofence Terpasang
                </div>
            </div>
        </div>

        {{-- Card 2: Store Cabang Daerah --}}
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="mdi mdi-store"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                        STORE DAERAH
                    </span>
                </div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Toko Cabang Daerah</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold text-dark fs-3">{{ $cabangCount }}</span>
                    <span class="text-muted small" style="font-size: 11.5px;">Toko</span>
                </div>
                <div class="text-success small fw-semibold mt-1" style="font-size: 11px;">
                    <i class="mdi mdi-check-circle me-1"></i>{{ $cabangActiveCount }} Geofence Terpasang
                </div>
            </div>
        </div>

        {{-- Card 3: Total Geofence Aktif --}}
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-box" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
                        <i class="mdi mdi-crosshairs-gps"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                        TOTAL AKTIF
                    </span>
                </div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Kelengkapan GPS</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold text-dark fs-3">{{ $activeGeofenceCount }}</span>
                    <span class="text-muted small" style="font-size: 11.5px;">/ {{ $totalBranches }} Cabang</span>
                </div>
                <div class="text-muted small mt-1" style="font-size: 11px;">
                    {{ $missingGeofenceCount > 0 ? $missingGeofenceCount . ' belum disetel' : 'Semua lokasi sudah terpasang' }}
                </div>
            </div>
        </div>

        {{-- Card 4: Radius Default --}}
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="mdi mdi-radar"></i>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                        RADIUS DEFAULT
                    </span>
                </div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Toleransi Absensi</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold text-dark fs-3">100</span>
                    <span class="text-muted small" style="font-size: 11.5px;">Meter</span>
                </div>
                <div class="text-muted small mt-1" style="font-size: 11px;">
                    Aman dari fluktuasi sinyal HP
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT CARD WITH TABS --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-3 p-md-4">

            {{-- TOOLBAR: FILTER & INSTANT SEARCH --}}
            <div class="row g-3 align-items-center justify-content-between mb-4">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="search-input-box">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="live-search-input" class="form-control" placeholder="Cari nama cabang, kota, atau alamat..." onkeyup="filterBranchTables()">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-5 text-md-end">
                    <div class="d-flex align-items-center justify-content-md-end gap-2">
                        <label class="form-label mb-0 small text-muted text-nowrap fw-semibold">Status:</label>
                        <select id="status-filter-select" class="form-select form-select-sm border-slate-300 rounded-3" style="max-width: 190px;" onchange="filterBranchTables()">
                            <option value="all">Semua Status</option>
                            <option value="active">🟢 Geofence Aktif</option>
                            <option value="missing">⚠️ Belum Disetel</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" onclick="resetFilters()" title="Reset Filter">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- NAVIGATION TABS --}}
            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="branchLocationTabs" role="tablist">
                {{-- TAB 1: KANTOR & UNIT PUSAT --}}
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-pusat-btn" data-bs-toggle="tab" data-bs-target="#tab-pusat-pane" type="button" role="tab">
                        <i class="mdi mdi-office-building me-1.5 text-primary fs-5"></i>
                        <span>Kantor & Unit Pusat</span>
                        <span class="badge bg-primary ms-2">{{ $pusatCount }}</span>
                    </button>
                </li>

                {{-- TAB 2: STORE / CABANG DAERAH --}}
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cabang-btn" data-bs-toggle="tab" data-bs-target="#tab-cabang-pane" type="button" role="tab">
                        <i class="mdi mdi-store me-1.5 text-success fs-5"></i>
                        <span>Store / Cabang Daerah</span>
                        <span class="badge bg-success ms-2">{{ $cabangCount }}</span>
                    </button>
                </li>

                {{-- TAB 3: SEMUA CABANG --}}
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-all-btn" data-bs-toggle="tab" data-bs-target="#tab-all-pane" type="button" role="tab">
                        <i class="mdi mdi-earth me-1.5 text-secondary fs-5"></i>
                        <span>Semua Lokasi</span>
                        <span class="badge bg-secondary ms-2">{{ $totalBranches }}</span>
                    </button>
                </li>
            </ul>

            {{-- TABS CONTENT PANES --}}
            <div class="tab-content" id="branchLocationTabContent">

                {{-- ========================================================= --}}
                {{-- PANE 1: UNIT PUSAT --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade show active" id="tab-pusat-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table branch-table align-middle mb-0" id="table-pusat">
                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="26%">Unit / Divisi Pusat</th>
                                    <th width="32%">Alamat Terdaftar</th>
                                    <th width="18%">Koordinat GPS</th>
                                    <th width="10%">Radius</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pusatBranches as $idx => $b)
                                    @include('branch.partials.geofence_row', ['branch' => $b, 'index' => $idx + 1, 'type' => 'pusat'])
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada unit pusat ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- PANE 2: CABANG DAERAH --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade" id="tab-cabang-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table branch-table align-middle mb-0" id="table-cabang">
                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="26%">Toko / Cabang Daerah</th>
                                    <th width="32%">Alamat Toko</th>
                                    <th width="18%">Koordinat GPS</th>
                                    <th width="10%">Radius</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cabangBranches as $idx => $b)
                                    @include('branch.partials.geofence_row', ['branch' => $b, 'index' => $idx + 1, 'type' => 'cabang'])
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada toko cabang ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- PANE 3: SEMUA LOKASI --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade" id="tab-all-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table branch-table align-middle mb-0" id="table-all">
                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="26%">Nama Cabang / Unit</th>
                                    <th width="32%">Alamat Terdaftar</th>
                                    <th width="18%">Koordinat GPS</th>
                                    <th width="10%">Radius</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branches as $idx => $b)
                                    @include('branch.partials.geofence_row', ['branch' => $b, 'index' => $idx + 1, 'type' => $b->is_pusat ? 'pusat' : 'cabang'])
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada data cabang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: EDIT GEOFENCE & TITIK LOKASI (INTERAKTIF & LEAFLET MAP)           --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="editGeofenceModal" tabindex="-1" aria-labelledby="editGeofenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white p-3 p-md-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-white bg-opacity-20 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="mdi mdi-crosshairs-gps fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modal-branch-name">Atur Lokasi Cabang</h5>
                        <small class="text-white text-opacity-80" id="modal-branch-subtitle">Perbarui koordinat dan batas radius absensi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editGeofenceForm" method="POST" onsubmit="submitGeofenceForm(event)">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    
                    {{-- Alert Error jika ada --}}
                    <div class="alert alert-danger d-none" id="modal-error-alert"></div>

                    {{-- Section 1: Alamat Toko --}}
                    <div class="mb-3">
                        <label for="modal-input-address" class="form-label fw-bold small text-slate-700">
                            <i class="mdi mdi-map-marker-outline text-primary me-1"></i> Alamat Lengkap Toko / Kantor
                        </label>
                        <textarea class="form-control" id="modal-input-address" name="address" rows="2" placeholder="Masukkan alamat lengkap cabang..."></textarea>
                    </div>

                    {{-- Section 2: Ambil Lokasi GPS Cepat & Google Maps Link --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between p-2.5 mb-3 bg-light rounded-3 border gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center fw-semibold rounded-pill px-3" onclick="useCurrentAdminLocation()">
                            <i class="mdi mdi-crosshairs-gps me-1.5 fs-6"></i>
                            <span>📍 Gunakan Posisi Saya Saat Ini</span>
                        </button>
                        <a href="#" id="modal-open-gmaps" target="_blank" class="btn btn-sm btn-link text-decoration-none fw-semibold d-inline-flex align-items-center text-primary">
                            <i class="mdi mdi-open-in-new me-1"></i>
                            <span>Uji di Google Maps</span>
                        </a>
                    </div>

                    {{-- Section 3: Koordinat Latitude & Longitude --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="modal-input-lat" class="form-label fw-bold small text-slate-700">
                                Latitude
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-latitude text-muted"></i></span>
                                <input type="number" step="any" class="form-control font-monospace" id="modal-input-lat" name="latitude" placeholder="Contoh: -6.1952300" onchange="syncMapFromInputs()" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="modal-input-lng" class="form-label fw-bold small text-slate-700">
                                Longitude
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-longitude text-muted"></i></span>
                                <input type="number" step="any" class="form-control font-monospace" id="modal-input-lng" name="longitude" placeholder="Contoh: 106.7865200" onchange="syncMapFromInputs()" required>
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: Radius Geofence (Presets & Custom Input) --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="modal-input-radius" class="form-label fw-bold small text-slate-700 mb-0">
                                <i class="mdi mdi-radar text-primary me-1"></i> Radius Toleransi Absensi (Meter)
                            </label>
                            <span class="text-muted small" style="font-size: 11px;">Rekomendasi toko: <strong>100 meter</strong></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(50)">50m (Kecil/Ruko)</button>
                            <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(100)">100m (Standar PStore)</button>
                            <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(150)">150m (Gedung Luas)</button>
                            <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(200)">200m (Area Besar)</button>
                        </div>
                        <div class="input-group" style="max-width: 250px;">
                            <input type="number" class="form-control fw-bold" id="modal-input-radius" name="radius" min="10" max="5000" step="5" onchange="syncRadiusFromInput()" required>
                            <span class="input-group-text bg-light fw-semibold">Meter</span>
                        </div>
                    </div>

                    {{-- Section 5: Interactive Leaflet Map Preview --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-slate-700 mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="mdi mdi-map-search-outline text-primary me-1"></i> Preview Peta Interaktif</span>
                            <small class="text-muted fw-normal" style="font-size: 11px;">Klik peta atau geser pin merah untuk koreksi titik</small>
                        </label>
                        <div id="modal-map"></div>
                    </div>

                </div>
                <div class="modal-footer bg-light p-3 border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="modal-submit-btn">
                        <i class="mdi mdi-content-save me-1"></i> Simpan Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: BATCH UPDATE RADIUS MASSAL                                       --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="batchRadiusModal" tabindex="-1" aria-labelledby="batchRadiusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light p-3 p-md-4">
                <h5 class="modal-title fw-bold text-dark mb-0">
                    <i class="mdi mdi-tune-vertical text-primary me-1.5"></i> Atur Radius Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('branches.geofence.batch') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Gunakan fitur ini untuk menyeragamkan batas radius absensi ke seluruh cabang sekaligus tanpa perlu mengedit satu per satu.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Target Cabang</label>
                        <select name="target" class="form-select rounded-3" required>
                            <option value="all">Semua Lokasi (Pusat & Cabang)</option>
                            <option value="cabang" selected>Hanya Toko Cabang Daerah</option>
                            <option value="pusat">Hanya Kantor & Unit Pusat</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Radius Toleransi</label>
                        <div class="input-group">
                            <input type="number" name="radius" class="form-control fw-bold" value="100" min="10" max="5000" step="10" required>
                            <span class="input-group-text bg-light fw-bold">Meter</span>
                        </div>
                        <small class="text-muted d-block mt-1">Standar toleransi aman GPS smartphone: <strong>100 Meter</strong>.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 p-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Terapkan Radius</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Global Map References
    let modalMap = null;
    let modalMarker = null;
    let modalCircle = null;
    let activeBranchData = null;

    // Default Fallback Coordinates (Jakarta Monas / PStore Condet)
    const DEFAULT_LAT = -6.1753924;
    const DEFAULT_LNG = 106.8271528;

    /**
     * Buka Modal Edit Geofence untuk cabang tertentu
     */
    function openEditGeofenceModal(branch) {
        activeBranchData = branch;

        // Set Form Action
        const form = document.getElementById('editGeofenceForm');
        form.action = `/branches/${branch.id}/geofence`;

        // Update Text Labels
        document.getElementById('modal-branch-name').textContent = `Atur Lokasi: ${branch.name}`;
        document.getElementById('modal-branch-subtitle').textContent = branch.is_pusat ? 'Kategori: Kantor / Unit Pusat' : 'Kategori: Store Cabang Daerah';

        // Fill Inputs
        document.getElementById('modal-input-address').value = branch.address || '';
        document.getElementById('modal-input-lat').value = branch.latitude !== null ? branch.latitude : '';
        document.getElementById('modal-input-lng').value = branch.longitude !== null ? branch.longitude : '';
        document.getElementById('modal-input-radius').value = branch.radius || 100;

        // Reset Error Alert
        const errAlert = document.getElementById('modal-error-alert');
        errAlert.classList.add('d-none');
        errAlert.textContent = '';

        // Update GMaps link
        updateGoogleMapsLink(branch.latitude, branch.longitude);

        // Highlight Active Radius Preset Button
        highlightActiveRadiusPreset(branch.radius || 100);

        // Show Bootstrap Modal
        const modalEl = document.getElementById('editGeofenceModal');
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();

        // Initialize / Refresh Leaflet Map after modal is shown
        modalEl.addEventListener('shown.bs.modal', function onShown() {
            initModalMap(
                branch.latitude !== null ? parseFloat(branch.latitude) : DEFAULT_LAT,
                branch.longitude !== null ? parseFloat(branch.longitude) : DEFAULT_LNG,
                branch.radius || 100
            );
            modalEl.removeEventListener('shown.bs.modal', onShown);
        });
    }

    /**
     * Inisialisasi atau Update Peta Leaflet di dalam Modal
     */
    function initModalMap(lat, lng, radius) {
        const mapContainer = document.getElementById('modal-map');

        if (!modalMap) {
            modalMap = L.map('modal-map').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(modalMap);

            // Draggable Marker
            modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);

            // Radius Circle
            modalCircle = L.circle([lat, lng], {
                radius: radius,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.15,
                weight: 2
            }).addTo(modalMap);

            // Event Marker Drag
            modalMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                setCoordsInput(pos.lat, pos.lng);
            });

            // Event Map Click
            modalMap.on('click', function (e) {
                setCoordsInput(e.latlng.lat, e.latlng.lng);
            });
        } else {
            modalMap.invalidateSize();
            modalMap.setView([lat, lng], 16);
            modalMarker.setLatLng([lat, lng]);
            modalCircle.setLatLng([lat, lng]);
            modalCircle.setRadius(radius);
        }
    }

    /**
     * Set Koordinat ke Input dan update Map
     */
    function setCoordsInput(lat, lng) {
        document.getElementById('modal-input-lat').value = parseFloat(lat).toFixed(7);
        document.getElementById('modal-input-lng').value = parseFloat(lng).toFixed(7);
        
        if (modalMarker) modalMarker.setLatLng([lat, lng]);
        if (modalCircle) modalCircle.setLatLng([lat, lng]);
        if (modalMap) modalMap.panTo([lat, lng]);

        updateGoogleMapsLink(lat, lng);
    }

    /**
     * Sinkronisasi Map jika Admin mengetik manual di input Lat/Lng
     */
    function syncMapFromInputs() {
        const latVal = parseFloat(document.getElementById('modal-input-lat').value);
        const lngVal = parseFloat(document.getElementById('modal-input-lng').value);

        if (!isNaN(latVal) && !isNaN(lngVal)) {
            setCoordsInput(latVal, lngVal);
        }
    }

    /**
     * Pilihan Cepat Radius Preset (50m, 100m, 150m, 200m)
     */
    function setRadiusPreset(radius) {
        document.getElementById('modal-input-radius').value = radius;
        highlightActiveRadiusPreset(radius);
        syncRadiusFromInput();
    }

    function highlightActiveRadiusPreset(radius) {
        document.querySelectorAll('.radius-preset-btn').forEach(btn => {
            if (btn.textContent.includes(radius + 'm')) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    function syncRadiusFromInput() {
        const radiusVal = parseInt(document.getElementById('modal-input-radius').value);
        if (!isNaN(radiusVal) && modalCircle) {
            modalCircle.setRadius(radiusVal);
        }
    }

    function updateGoogleMapsLink(lat, lng) {
        const link = document.getElementById('modal-open-gmaps');
        if (lat && lng) {
            link.href = `https://maps.google.com/?q=${lat},${lng}`;
            link.classList.remove('disabled');
        } else {
            link.href = '#';
            link.classList.add('disabled');
        }
    }

    /**
     * Ambil Posisi GPS Browser Admin Saat Ini
     */
    function useCurrentAdminLocation() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung geolokasi GPS.');
            return;
        }

        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunci GPS...';

        navigator.geolocation.getCurrentPosition(
            pos => {
                const { latitude, longitude } = pos.coords;
                setCoordsInput(latitude, longitude);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            },
            err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Gagal mendeteksi lokasi GPS. Pastikan izin lokasi browser diaktifkan.');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    /**
     * Submit Form via AJAX untuk kecepatan tanpa reload
     */
    function submitGeofenceForm(e) {
        e.preventDefault();

        const form = document.getElementById('editGeofenceForm');
        const submitBtn = document.getElementById('modal-submit-btn');
        const errAlert = document.getElementById('modal-error-alert');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> Menyimpan...';
        errAlert.classList.add('d-none');

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-content-save me-1"></i> Simpan Lokasi';

            if (data.success) {
                // Update Row di Tabel secara Realtime
                updateRowDOM(data.branch);

                // Tutup Modal
                const modalEl = document.getElementById('editGeofenceModal');
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();

                // Notifikasi Sukses
                showFloatingToast(data.message || 'Lokasi berhasil disimpan!');
            } else {
                errAlert.textContent = data.message || 'Terjadi kesalahan saat menyimpan.';
                errAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-content-save me-1"></i> Simpan Lokasi';
            // Fallback submit standar jika response bukan JSON
            form.submit();
        });
    }

    /**
     * Update Tampilan Baris Tabel setelah Simpan Berhasil
     */
    function updateRowDOM(branch) {
        document.querySelectorAll(`.row-branch-${branch.id}`).forEach(row => {
            // Update Alamat
            const addrEl = row.querySelector('.col-address-text');
            if (addrEl) addrEl.textContent = branch.address || '-';

            // Update Coords & Maps
            const coordsContainer = row.querySelector('.col-coords-container');
            if (coordsContainer) {
                if (branch.latitude && branch.longitude) {
                    coordsContainer.innerHTML = `
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="coord-badge">
                                <i class="mdi mdi-map-marker text-rose-600"></i> ${parseFloat(branch.latitude).toFixed(5)}, ${parseFloat(branch.longitude).toFixed(5)}
                            </span>
                            <a href="https://maps.google.com/?q=${branch.latitude},${branch.longitude}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1.5" title="Buka Google Maps">
                                <i class="mdi mdi-open-in-new" style="font-size: 11px;"></i>
                            </a>
                        </div>
                    `;
                } else {
                    coordsContainer.innerHTML = '<span class="badge bg-light text-muted border">Belum disetel</span>';
                }
            }

            // Update Radius
            const radiusEl = row.querySelector('.col-radius-container');
            if (radiusEl) {
                radiusEl.innerHTML = `<span class="radius-pill"><i class="mdi mdi-radar"></i> ${branch.radius} m</span>`;
            }

            // Update Status Badge
            const statusBadge = row.querySelector('.col-status-badge');
            if (statusBadge) {
                if (branch.has_geofence) {
                    statusBadge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 700;"><i class="mdi mdi-check-circle me-1"></i>Aktif</span>';
                    row.setAttribute('data-status', 'active');
                } else {
                    statusBadge.innerHTML = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 700;"><i class="mdi mdi-alert-circle me-1"></i>Belum Disetel</span>';
                    row.setAttribute('data-status', 'missing');
                }
            }

            // Update onclick data
            const actionBtn = row.querySelector('.btn-edit-geofence');
            if (actionBtn) {
                actionBtn.setAttribute('data-branch-json', JSON.stringify(branch));
            }
        });
    }

    /**
     * Live Search & Filter Baris Tabel
     */
    function filterBranchTables() {
        const query = document.getElementById('live-search-input').value.toLowerCase().trim();
        const statusFilter = document.getElementById('status-filter-select').value;

        const allRows = document.querySelectorAll('.branch-row-item');

        allRows.forEach(row => {
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            const address = (row.getAttribute('data-address') || '').toLowerCase();
            const status = row.getAttribute('data-status');

            const matchesSearch = query === '' || name.includes(query) || address.includes(query);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetFilters() {
        document.getElementById('live-search-input').value = '';
        document.getElementById('status-filter-select').value = 'all';
        filterBranchTables();
    }

    /**
     * Floating Toast Notification
     */
    function showFloatingToast(message) {
        let toastEl = document.getElementById('floating-geo-toast');
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = 'floating-geo-toast';
            toastEl.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 9999; background: #0f172a; color: #fff; padding: 14px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px; font-size: 13.5px; font-weight: 600; opacity: 0; transition: opacity 0.3s ease;';
            document.body.appendChild(toastEl);
        }

        toastEl.innerHTML = `<i class="mdi mdi-check-circle text-success fs-5"></i> <span>${message}</span>`;
        toastEl.style.opacity = '1';

        setTimeout(() => {
            toastEl.style.opacity = '0';
        }, 3500);
    }
</script>
@endpush
