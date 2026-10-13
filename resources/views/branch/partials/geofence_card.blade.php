@php
    $hasGeofence = $branch->hasGeofence();
    $isPusat = $branch->is_pusat;
    $status = $hasGeofence ? 'active' : 'missing';
@endphp

<div class="branch-row-item row-branch-{{ $branch->id }} card border shadow-xs mb-3 rounded-3"
     data-name="{{ strtolower($branch->name) }}"
     data-address="{{ strtolower($branch->address ?? '') }}"
     data-status="{{ $status }}"
     style="border-color: #e2e8f0; background: #ffffff;">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
            <div>
                <h6 class="fw-bold mb-1 text-dark" style="font-size: 15px;">{{ $branch->name }}</h6>
                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                    @if($isPusat)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px; font-weight: 600;">
                            <i class="mdi mdi-office-building me-0.5"></i> Pusat
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px; font-weight: 600;">
                            <i class="mdi mdi-store me-0.5"></i> Cabang
                        </span>
                    @endif
                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 10.5px; font-weight: 500;">
                        <i class="mdi mdi-clock-outline me-0.5"></i> {{ $branch->timezone ?? 'Asia/Jakarta' }}
                    </span>
                </div>
            </div>
            <span class="col-status-badge">
                @if($hasGeofence)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                        <i class="mdi mdi-check-circle me-1"></i>Aktif
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                        <i class="mdi mdi-alert-circle me-1"></i>Belum Disetel
                    </span>
                @endif
            </span>
        </div>

        <div class="text-secondary small mb-3 col-address-text" style="font-size: 12.5px; line-height: 1.4;">
            <i class="mdi mdi-map-marker-outline text-muted me-1"></i>{{ $branch->address ?: 'Alamat belum diisi' }}
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="col-coords-container">
                    @if($branch->latitude !== null && $branch->longitude !== null)
                        <div class="d-flex align-items-center gap-1">
                            <span class="coord-badge">
                                <i class="mdi mdi-crosshairs-gps text-danger"></i> {{ number_format((float)$branch->latitude, 4) }}, {{ number_format((float)$branch->longitude, 4) }}
                            </span>
                            <a href="https://maps.google.com/?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-1 px-2" title="Buka di Google Maps" style="min-height: 32px; display: inline-flex; align-items: center;">
                                <i class="mdi mdi-open-in-new"></i>
                            </a>
                        </div>
                    @else
                        <span class="badge bg-light text-muted border">Belum disetel</span>
                    @endif
                </div>

                <div class="col-radius-container">
                    <span class="radius-pill">
                        <i class="mdi mdi-radar"></i> {{ $branch->radius ?? 100 }} m
                    </span>
                </div>
            </div>

            <button type="button" 
                    class="btn btn-primary rounded-3 px-3 py-2 btn-edit-geofence d-inline-flex align-items-center gap-1 shadow-sm fw-semibold"
                    style="min-height: 44px; font-size: 13px;"
                    data-branch-json="{{ json_encode([
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'address' => $branch->address,
                        'latitude' => $branch->latitude,
                        'longitude' => $branch->longitude,
                        'radius' => $branch->radius ?? 100,
                        'is_pusat' => $isPusat,
                        'has_geofence' => $hasGeofence
                    ]) }}"
                    onclick="openEditGeofenceModal(JSON.parse(this.getAttribute('data-branch-json')))">
                <i class="mdi mdi-pencil-outline"></i>
                <span>Atur Lokasi</span>
            </button>
        </div>
    </div>
</div>
