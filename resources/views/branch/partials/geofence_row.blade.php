@php
    $hasGeofence = $branch->hasGeofence();
    $isPusat = $branch->is_pusat;
    $status = $hasGeofence ? 'active' : 'missing';
@endphp

<tr class="branch-row-item row-branch-{{ $branch->id }}"
    data-name="{{ strtolower($branch->name) }}"
    data-address="{{ strtolower($branch->address ?? '') }}"
    data-status="{{ $status }}">
    {{-- Kolom 1: Nomor Index --}}
    <td class="text-muted fw-semibold" style="font-size: 12px; width: 4%;">
        {{ $index }}
    </td>

    {{-- Kolom 2: Nama Cabang / Unit & Badges --}}
    <td style="width: 26%;">
        <div class="d-flex flex-column gap-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold text-dark" style="font-size: 13.5px;">{{ $branch->name }}</span>
                <span class="col-status-badge">
                    @if($hasGeofence)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 700;">
                            <i class="mdi mdi-check-circle me-1"></i>Aktif
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 700;">
                            <i class="mdi mdi-alert-circle me-1"></i>Belum Disetel
                        </span>
                    @endif
                </span>
            </div>
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
    </td>

    {{-- Kolom 3: Alamat Terdaftar --}}
    <td style="width: 32%;">
        <div class="col-address-text text-secondary" style="font-size: 12.5px; line-height: 1.45;">
            {{ $branch->address ?: '-' }}
        </div>
    </td>

    {{-- Kolom 4: Koordinat GPS --}}
    <td style="width: 18%;">
        <div class="col-coords-container">
            @if($branch->latitude !== null && $branch->longitude !== null)
                <div class="d-flex align-items-center gap-1.5">
                    <span class="coord-badge" title="Latitude: {{ $branch->latitude }}, Longitude: {{ $branch->longitude }}">
                        <i class="mdi mdi-map-marker text-danger"></i> {{ number_format((float)$branch->latitude, 5) }}, {{ number_format((float)$branch->longitude, 5) }}
                    </span>
                    <a href="https://maps.google.com/?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-0 px-1.5" title="Buka di Google Maps" style="font-size: 11px; line-height: 1.5; border-radius: 6px;">
                        <i class="mdi mdi-open-in-new"></i>
                    </a>
                </div>
            @else
                <span class="badge bg-light text-muted border" style="font-size: 11px;">Belum disetel</span>
            @endif
        </div>
    </td>

    {{-- Kolom 5: Radius Toleransi --}}
    <td style="width: 10%;">
        <div class="col-radius-container">
            <span class="radius-pill">
                <i class="mdi mdi-radar"></i> {{ $branch->radius ?? 100 }} m
            </span>
        </div>
    </td>

    {{-- Kolom 6: Aksi --}}
    <td class="text-center" style="width: 10%;">
        <button type="button" 
                class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 btn-edit-geofence d-inline-flex align-items-center gap-1 shadow-sm fw-semibold"
                style="font-size: 12.5px;"
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
            <span>Atur</span>
        </button>
    </td>
</tr>
