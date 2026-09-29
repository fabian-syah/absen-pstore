@php
    $totalKaryawan = $branch->users_count ?? 0;
    $sudahDinilai = $branch->evaluated_users_count ?? 0;
    $persen = $totalKaryawan > 0 ? round(($sudahDinilai / $totalKaryawan) * 100) : 0;
    $isPusat = $branch->is_pusat;

    if ($sudahDinilai == 0) {
        $badgeClass = 'bg-light text-muted border';
        $badgeText = $sudahDinilai . ' dari ' . $totalKaryawan . ' Dinilai';
        $progressClass = 'bg-secondary';
    } elseif ($sudahDinilai == $totalKaryawan && $totalKaryawan > 0) {
        $badgeClass = 'bg-success text-white';
        $badgeText = $sudahDinilai . ' dari ' . $totalKaryawan . ' Selesai';
        $progressClass = 'bg-success';
    } else {
        $badgeClass = 'bg-warning text-dark';
        $badgeText = $sudahDinilai . ' dari ' . $totalKaryawan . ' Dinilai';
        $progressClass = 'bg-warning';
    }
@endphp

<div class="col-xl-3 col-md-6">
    <div class="branch-card-item p-4">
        {{-- Header Card --}}
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="branch-icon-box {{ $isPusat ? 'pusat' : 'cabang' }}">
                <i class="mdi {{ $isPusat ? 'mdi-city' : 'mdi-storefront-outline' }}"></i>
            </div>
            <div class="d-flex align-items-center gap-1">
                @if($isPusat)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">
                        Pusat
                    </span>
                @else
                    <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.7rem;">
                        Cabang
                    </span>
                @endif
                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                    #{{ $branch->id }}
                </span>
            </div>
        </div>

        {{-- Nama Cabang --}}
        <h5 class="fw-bold text-dark mb-1" title="{{ $branch->name }}">{{ Str::limit($branch->name, 22) }}</h5>
        <p class="text-muted small mb-3 text-truncate" title="{{ $branch->address ?? 'Alamat belum diatur' }}">
            <i class="mdi mdi-map-marker-outline me-1"></i>
            {{ Str::limit($branch->address ?? 'Alamat belum diatur', 40) }}
        </p>

        {{-- Indikator Progres Penilaian Karyawan --}}
        <div class="evaluation-progress-box mb-3 p-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold" style="font-size: 0.75rem;">
                    <i class="mdi mdi-account-check-outline me-1 text-primary"></i>Penilaian:
                </span>
                <span class="badge {{ $badgeClass }} fw-bold" style="font-size: 0.72rem;">
                    {{ $badgeText }}
                </span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar {{ $progressClass }}" 
                     role="progressbar" 
                     style="width: {{ $persen }}%" 
                     aria-valuenow="{{ $persen }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between mt-1 text-muted" style="font-size: 0.7rem;">
                <span>{{ $sudahDinilai }}/{{ $totalKaryawan }} Karyawan</span>
                <span class="fw-bold">{{ $persen }}%</span>
            </div>
        </div>

        {{-- Footer Card --}}
        <div class="branch-footer mt-auto">
            <div class="small text-muted">
                Total: <strong>{{ $totalKaryawan }}</strong> Karyawan
            </div>
            <a href="{{ route('employee-evaluations.history', ['branch_id' => $branch->id]) }}"
                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                Lihat Riwayat <i class="mdi mdi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
