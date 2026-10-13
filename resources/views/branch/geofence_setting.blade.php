@extends('layout.master')

@section('title')
    Pengaturan Radius & Lokasi Cabang
@endsection

@section('heading')
    Pengaturan Radius & Lokasi Cabang
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* =========================================================================
       GEOFENCING CONSOLE DESIGN SYSTEM - ANTI SLOP & FULLY RESPONSIVE
       ========================================================================= */
    :root {
        --geo-primary: #0d6efd;
        --geo-primary-dark: #0b5ed7;
        --geo-slate-900: #0f172a;
        --geo-slate-800: #1e293b;
        --geo-slate-600: #475569;
        --geo-slate-500: #64748b;
        --geo-slate-200: #e2e8f0;
        --geo-slate-100: #f1f5f9;
        --geo-slate-50: #f8fafc;
    }

    /* Top Console Header */
    .geo-console-header {
        background: #ffffff;
        border: 1px solid var(--geo-slate-200);
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    /* Metric Summary Cards */
    .metric-summary-card {
        background: #ffffff;
        border: 1px solid var(--geo-slate-200);
        border-radius: 12px;
        padding: 16px 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .metric-summary-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .metric-summary-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    /* Tabs Styling & Horizontal Scroll on Mobile */
    .geo-tabs-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        border-bottom: 2px solid var(--geo-slate-200);
        margin-bottom: 20px;
    }

    .geo-tabs-wrapper::-webkit-scrollbar {
        height: 4px;
    }

    .geo-tabs-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .nav-tabs-custom {
        display: inline-flex;
        border-bottom: none;
        gap: 6px;
        padding-bottom: 0;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: var(--geo-slate-600);
        font-weight: 600;
        font-size: 14px;
        padding: 12px 18px;
        border-radius: 10px 10px 0 0;
        background: transparent;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        min-height: 44px;
    }

    .nav-tabs-custom .nav-link:hover {
        color: var(--geo-primary);
        background: var(--geo-slate-100);
    }

    .nav-tabs-custom .nav-link.active {
        color: var(--geo-primary);
        background: #ffffff;
        border-bottom: 3px solid var(--geo-primary);
    }

    /* Desktop Table */
    .branch-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: var(--geo-slate-600);
        background-color: var(--geo-slate-50);
        border-top: none;
        padding: 14px 16px;
    }

    .branch-table td {
        padding: 16px;
        vertical-align: middle;
        font-size: 13.5px;
        border-color: var(--geo-slate-100);
    }

    .branch-table tr:hover td {
        background-color: #fbfcfe;
    }

    /* Badges & Pills */
    .coord-badge {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 11.5px;
        background: var(--geo-slate-100);
        color: var(--geo-slate-800);
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid var(--geo-slate-200);
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

    /* Search & Filter Toolbar */
    .search-input-box {
        position: relative;
    }

    .search-input-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--geo-slate-500);
        font-size: 18px;
    }

    .search-input-box input {
        padding-left: 42px;
        border-radius: 10px;
        border: 1px solid var(--geo-slate-200);
        font-size: 14px;
        min-height: 44px;
    }

    .search-input-box input:focus {
        border-color: var(--geo-primary);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }

    /* Interactive Leaflet Map */
    #modal-map {
        width: 100%;
        height: 290px;
        border-radius: 12px;
        border: 1px solid var(--geo-slate-200);
        background: var(--geo-slate-50);
        z-index: 1;
    }

    .radius-preset-btn {
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border: 1px solid var(--geo-slate-200);
        background: #ffffff;
        color: var(--geo-slate-800);
        min-height: 38px;
        transition: all 0.15s ease;
    }

    .radius-preset-btn:hover,
    .radius-preset-btn.active {
        background: var(--geo-primary);
        color: #ffffff;
        border-color: var(--geo-primary);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Alert Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle-outline fs-4 me-2 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- HEADER CONSOLE (CLEAN, PROPORTIONAL, ACCESSIBLE) --}}
    <div class="geo-console-header mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0 text-dark">
                        Pengaturan Lokasi & Radius Cabang
                    </h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                        <i class="mdi mdi-shield-check me-1"></i>Role Admin
                    </span>
                </div>
                <p class="text-secondary mb-0 small" style="max-width: 720px; line-height: 1.5;">
                    Kelola koordinat GPS (Latitude/Longitude), alamat terdaftar, dan batas toleransi radius absensi untuk seluruh kantor pusat dan toko cabang daerah.
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#batchRadiusModal" style="min-height: 44px;">
                    <i class="mdi mdi-tune-vertical me-1.5 fs-5"></i>
                    <span>Atur Radius Massal</span>
                </button>
                <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center" style="min-height: 44px;">
                    <i class="mdi mdi-format-list-bulleted me-1.5"></i>
                    <span>Data Cabang</span>
                </a>
            </div>
        </div>
    </div>

    {{-- METRIC SUMMARY (CLEAR, BALANCED, ZERO AI SLOP) --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Unit Pusat --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-summary-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase">Kantor & Unit Pusat</span>
                    <div class="metric-summary-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="mdi mdi-office-building"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold fs-3 text-dark">{{ $pusatCount }}</span>
                        <span class="text-muted small">Divisi / Unit</span>
                    </div>
                    <div class="text-success small fw-semibold mt-1">
                        <i class="mdi mdi-check-circle me-1"></i>{{ $pusatActiveCount }} Geofence Terpasang
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Toko Cabang Daerah --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-summary-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase">Toko Cabang Daerah</span>
                    <div class="metric-summary-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="mdi mdi-store"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold fs-3 text-dark">{{ $cabangCount }}</span>
                        <span class="text-muted small">Gerai Toko</span>
                    </div>
                    <div class="text-success small fw-semibold mt-1">
                        <i class="mdi mdi-check-circle me-1"></i>{{ $cabangActiveCount }} Geofence Terpasang
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Kelengkapan GPS --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-summary-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase">Kelengkapan Titik GPS</span>
                    <div class="metric-summary-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
                        <i class="mdi mdi-crosshairs-gps"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold fs-3 text-dark">{{ $activeGeofenceCount }}</span>
                        <span class="text-muted small">/ {{ $totalBranches }} Cabang</span>
                    </div>
                    <div class="text-secondary small mt-1">
                        {{ $missingGeofenceCount > 0 ? $missingGeofenceCount . ' belum memiliki koordinat' : 'Semua cabang sudah lengkap' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Radius Acuan Default --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-summary-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase">Radius Default</span>
                    <div class="metric-summary-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="mdi mdi-radar"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold fs-3 text-dark">100</span>
                        <span class="text-muted small">Meter</span>
                    </div>
                    <div class="text-secondary small mt-1">
                        Toleransi standar akurasi GPS HP
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT CARD --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-3 p-md-4">

            {{-- TOOLBAR: FILTER & INSTANT SEARCH (RESPONSIVE STACK) --}}
            <div class="row g-3 align-items-center justify-content-between mb-3">
                <div class="col-12 col-md-7 col-lg-6">
                    <div class="search-input-box">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="live-search-input" class="form-control" placeholder="Cari nama cabang, unit, atau alamat..." onkeyup="filterBranchTables()">
                    </div>
                </div>
                <div class="col-12 col-md-5 col-lg-5 text-md-end">
                    <div class="d-flex align-items-center justify-content-md-end gap-2">
                        <select id="status-filter-select" class="form-select border-slate-300 rounded-3" style="min-height: 44px;" onchange="filterBranchTables()">
                            <option value="all">Semua Status Lokasi</option>
                            <option value="active">🟢 Geofence Aktif</option>
                            <option value="missing">⚠️ Belum Disetel</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary rounded-3" style="min-height: 44px; min-width: 44px;" onclick="resetFilters()" title="Reset Filter">
                            <i class="mdi mdi-refresh fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABS WRAPPER (HORIZONTAL SCROLL ON MOBILE) --}}
            <div class="geo-tabs-wrapper">
                <ul class="nav nav-tabs nav-tabs-custom" id="branchLocationTabs" role="tablist">
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

                    {{-- TAB 3: SEMUA LOKASI --}}
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-all-btn" data-bs-toggle="tab" data-bs-target="#tab-all-pane" type="button" role="tab">
                            <i class="mdi mdi-earth me-1.5 text-secondary fs-5"></i>
                            <span>Semua Lokasi</span>
                            <span class="badge bg-secondary ms-2">{{ $totalBranches }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- TABS CONTENT PANES --}}
            <div class="tab-content" id="branchLocationTabContent">

                {{-- ========================================================= --}}
                {{-- PANE 1: UNIT PUSAT --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade show active" id="tab-pusat-pane" role="tabpanel">
                    {{-- Desktop Table View --}}
                    <div class="d-none d-lg-block table-responsive">
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

                    {{-- Mobile / Tablet Card View --}}
                    <div class="d-lg-none">
                        @forelse($pusatBranches as $idx => $b)
                            @include('branch.partials.geofence_card', ['branch' => $b, 'index' => $idx + 1, 'type' => 'pusat'])
                        @empty
                            <div class="text-center py-4 text-muted">Tidak ada unit pusat ditemukan.</div>
                        @endforelse
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- PANE 2: CABANG DAERAH --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade" id="tab-cabang-pane" role="tabpanel">
                    {{-- Desktop Table View --}}
                    <div class="d-none d-lg-block table-responsive">
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

                    {{-- Mobile / Tablet Card View --}}
                    <div class="d-lg-none">
                        @forelse($cabangBranches as $idx => $b)
                            @include('branch.partials.geofence_card', ['branch' => $b, 'index' => $idx + 1, 'type' => 'cabang'])
                        @empty
                            <div class="text-center py-4 text-muted">Tidak ada toko cabang ditemukan.</div>
                        @endforelse
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- PANE 3: SEMUA LOKASI --}}
                {{-- ========================================================= --}}
                <div class="tab-pane fade" id="tab-all-pane" role="tabpanel">
                    {{-- Desktop Table View --}}
                    <div class="d-none d-lg-block table-responsive">
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

                    {{-- Mobile / Tablet Card View --}}
                    <div class="d-lg-none">
                        @forelse($branches as $idx => $b)
                            @include('branch.partials.geofence_card', ['branch' => $b, 'index' => $idx + 1, 'type' => $b->is_pusat ? 'pusat' : 'cabang'])
                        @empty
                            <div class="text-center py-4 text-muted">Tidak ada data cabang.</div>
                        @endforelse
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
                        <small class="text-white text-opacity-85" id="modal-branch-subtitle">Perbarui koordinat dan batas radius absensi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editGeofenceForm" method="POST" onsubmit="submitGeofenceForm(event)">
                @csrf
                @method('PUT')

                <div class="modal-body p-3 p-md-4">
                    <div class="alert alert-danger d-none mb-3" id="modal-error-alert" role="alert"></div>

                    {{-- Alamat --}}
                    <div class="mb-3">
                        <label for="modal-input-address" class="form-label fw-bold small text-dark mb-1">
                            Alamat Resmi Cabang
                        </label>
                        <textarea class="form-control" id="modal-input-address" name="address" rows="2" placeholder="Masukkan alamat lengkap cabang..."></textarea>
                    </div>

                    {{-- MAP PICKER CONTAINER --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <label class="form-label fw-bold small text-dark mb-0">
                                <i class="mdi mdi-map-marker-radius text-danger me-1"></i>Peta Lokasi & Geofence
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-2.5 py-1 fw-semibold" onclick="useCurrentAdminLocation()" style="min-height: 34px;">
                                    <i class="mdi mdi-crosshairs-gps me-1"></i>Gunakan Posisi Saya
                                </button>
                                <a href="#" id="modal-open-gmaps" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" title="Buka di Google Maps" style="min-height: 34px;">
                                    <i class="mdi mdi-open-in-new me-1"></i>Google Maps
                                </a>
                            </div>
                        </div>

                        <div id="modal-map"></div>
                        <div class="form-text small text-muted mt-1">
                            <i class="mdi mdi-information-outline me-1"></i>Klik pada peta atau geser pin merah untuk menggeser koordinat secara presisi.
                        </div>
                    </div>

                    {{-- INPUT COORDINATES & RADIUS --}}
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="modal-input-lat" class="form-label fw-bold small text-dark mb-1">Latitude</label>
                            <input type="number" step="any" class="form-control" id="modal-input-lat" name="latitude" placeholder="-6.2000000" onchange="syncMapFromInputs()" style="min-height: 44px;">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="modal-input-lng" class="form-label fw-bold small text-dark mb-1">Longitude</label>
                            <input type="number" step="any" class="form-control" id="modal-input-lng" name="longitude" placeholder="106.8166660" onchange="syncMapFromInputs()" style="min-height: 44px;">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="modal-input-radius" class="form-label fw-bold small text-dark mb-1">Radius (Meter)</label>
                            <div class="input-group">
                                <input type="number" min="10" max="5000" class="form-control" id="modal-input-radius" name="radius" placeholder="100" oninput="syncRadiusFromInput()" style="min-height: 44px;">
                                <span class="input-group-text">Meter</span>
                            </div>
                        </div>
                    </div>

                    {{-- PRESET RADIUS BUTTONS --}}
                    <div class="mt-3 pt-2 border-top">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="small fw-semibold text-secondary">Pilihan Cepat Radius:</span>
                            <div class="d-flex gap-1.5 flex-wrap">
                                <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(50)">50m</button>
                                <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(100)">100m</button>
                                <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(150)">150m</button>
                                <button type="button" class="radius-preset-btn" onclick="setRadiusPreset(200)">200m</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal" style="min-height: 44px;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" id="modal-submit-btn" style="min-height: 44px;">
                        <i class="mdi mdi-content-save me-1"></i> Simpan Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: BATCH UPDATE RADIUS UNTUK BANYAK CABANG                           --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="batchRadiusModal" tabindex="-1" aria-labelledby="batchRadiusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-3 p-md-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-white bg-opacity-10 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="mdi mdi-tune-vertical fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Atur Radius Geofence Massal</h5>
                        <small class="text-white text-opacity-80">Terapkan batasan radius ke banyak cabang sekaligus</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('branches.geofence.batch') }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">
                    <div class="mb-3">
                        <label for="batch-target-select" class="form-label fw-bold small text-dark mb-1">
                            Pilih Target Cabang
                        </label>
                        <select name="target" id="batch-target-select" class="form-select border-slate-300 rounded-3" style="min-height: 44px;" required>
                            <option value="all">Semua Cabang & Unit ({{ $totalBranches }} Lokasi)</option>
                            <option value="cabang" selected>Hanya Toko Cabang Daerah ({{ $cabangCount }} Toko)</option>
                            <option value="pusat">Hanya Kantor & Unit Pusat ({{ $pusatCount }} Unit)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="batch-radius-input" class="form-label fw-bold small text-dark mb-1">
                            Radius Baru (Meter)
                        </label>
                        <div class="input-group">
                            <input type="number" min="10" max="5000" class="form-control" id="batch-radius-input" name="radius" value="100" style="min-height: 44px;" required>
                            <span class="input-group-text">Meter</span>
                        </div>
                        <div class="form-text small text-muted">
                            Rekomendasi standar: 100 meter agar absensi karyawan tidak terganggu deviasi sinyal GPS handphone.
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal" style="min-height: 44px;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" style="min-height: 44px;">
                        Terapkan Massal
                    </button>
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

    // Default Fallback Coordinates (PStore Condet / Jakarta Pusat)
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
        document.getElementById('modal-branch-subtitle').textContent = branch.is_pusat ? 'Kategori: Kantor / Unit Pusat' : 'Kategori: Toko Cabang Daerah';

        // Fill Inputs
        document.getElementById('modal-input-address').value = branch.address || '';
        document.getElementById('modal-input-lat').value = (branch.latitude !== null && branch.latitude !== undefined) ? branch.latitude : '';
        document.getElementById('modal-input-lng').value = (branch.longitude !== null && branch.longitude !== undefined) ? branch.longitude : '';
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

        // Initialize / Refresh Leaflet Map after modal is fully shown
        modalEl.addEventListener('shown.bs.modal', function onShown() {
            initModalMap(
                (branch.latitude !== null && branch.latitude !== undefined && branch.latitude !== '') ? parseFloat(branch.latitude) : DEFAULT_LAT,
                (branch.longitude !== null && branch.longitude !== undefined && branch.longitude !== '') ? parseFloat(branch.longitude) : DEFAULT_LNG,
                branch.radius || 100
            );
            modalEl.removeEventListener('shown.bs.modal', onShown);
        });
    }

    /**
     * Inisialisasi atau Update Peta Leaflet di dalam Modal
     */
    function initModalMap(lat, lng, radius) {
        if (!modalMap) {
            modalMap = L.map('modal-map').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(modalMap);

            // Draggable Marker
            modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);

            // Radius Circle
            modalCircle = L.circle([lat, lng], {
                radius: radius,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.16,
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
            if (btn.textContent.trim() === `${radius}m`) {
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
                alert('Gagal mendeteksi lokasi GPS. Pastikan izin lokasi browser aktif.');
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
                // Update Row & Card di Tabel / Card secara Realtime
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
            form.submit();
        });
    }

    /**
     * Update Tampilan Baris Tabel & Card setelah Simpan Berhasil
     */
    function updateRowDOM(branch) {
        document.querySelectorAll(`.row-branch-${branch.id}`).forEach(el => {
            // Update Alamat
            const addrEl = el.querySelector('.col-address-text');
            if (addrEl) {
                if (el.tagName === 'TR') {
                    addrEl.textContent = branch.address || '-';
                } else {
                    addrEl.innerHTML = `<i class="mdi mdi-map-marker-outline text-muted me-1"></i>${branch.address || 'Alamat belum diisi'}`;
                }
            }

            // Update Coords & Maps Link
            const coordsContainer = el.querySelector('.col-coords-container');
            if (coordsContainer) {
                if (branch.latitude && branch.longitude) {
                    coordsContainer.innerHTML = `
                        <div class="d-flex align-items-center gap-1">
                            <span class="coord-badge">
                                <i class="mdi mdi-crosshairs-gps text-danger"></i> ${parseFloat(branch.latitude).toFixed(4)}, ${parseFloat(branch.longitude).toFixed(4)}
                            </span>
                            <a href="https://maps.google.com/?q=${branch.latitude},${branch.longitude}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-0 px-1.5" title="Buka di Google Maps" style="font-size: 11px; line-height: 1.5; border-radius: 6px;">
                                <i class="mdi mdi-open-in-new"></i>
                            </a>
                        </div>
                    `;
                } else {
                    coordsContainer.innerHTML = '<span class="badge bg-light text-muted border">Belum disetel</span>';
                }
            }

            // Update Radius
            const radiusEl = el.querySelector('.col-radius-container');
            if (radiusEl) {
                radiusEl.innerHTML = `<span class="radius-pill"><i class="mdi mdi-radar"></i> ${branch.radius} m</span>`;
            }

            // Update Status Badge
            const statusBadge = el.querySelector('.col-status-badge');
            if (statusBadge) {
                if (branch.has_geofence) {
                    statusBadge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 10px; font-weight: 700;"><i class="mdi mdi-check-circle me-1"></i>Aktif</span>';
                    el.setAttribute('data-status', 'active');
                } else {
                    statusBadge.innerHTML = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 10px; font-weight: 700;"><i class="mdi mdi-alert-circle me-1"></i>Belum Disetel</span>';
                    el.setAttribute('data-status', 'missing');
                }
            }

            // Update onclick data JSON pada tombol edit
            const actionBtn = el.querySelector('.btn-edit-geofence');
            if (actionBtn) {
                actionBtn.setAttribute('data-branch-json', JSON.stringify(branch));
            }
        });
    }

    /**
     * Live Search & Filter Baris Tabel & Card
     */
    function filterBranchTables() {
        const query = document.getElementById('live-search-input').value.toLowerCase().trim();
        const statusFilter = document.getElementById('status-filter-select').value;

        const allItems = document.querySelectorAll('.branch-row-item');

        allItems.forEach(item => {
            const name = (item.getAttribute('data-name') || '').toLowerCase();
            const address = (item.getAttribute('data-address') || '').toLowerCase();
            const status = item.getAttribute('data-status');

            const matchesSearch = query === '' || name.includes(query) || address.includes(query);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
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
