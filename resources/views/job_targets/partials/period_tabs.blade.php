{{-- NAV TABS --}}
<ul class="nav nav-pills nav-pills-custom mb-4 small" id="{{ $idPrefix }}Tab" role="tablist">
    <li class="nav-item me-2">
        <a class="nav-link active rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="pill" href="#{{ $idPrefix }}-daily">Harian</a>
    </li>
    <li class="nav-item me-2">
        <a class="nav-link rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="pill" href="#{{ $idPrefix }}-monthly">Bulanan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="pill" href="#{{ $idPrefix }}-yearly">Tahunan</a>
    </li>
</ul>

<div class="tab-content">
    @foreach(['daily', 'monthly', 'yearly'] as $period)
        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="{{ $idPrefix }}-{{ $period }}">
            
            @php 
                // Filter data berdasarkan periode tab
                $periodData = $dataCollection->where('period', $period);

                // 1. ON GOING: Target yang belum selesai DAN bukan tipe achievement murni
                // (Tipe achievement murni biasanya dibuat langsung completed)
                $ongoing = $periodData->filter(function($item) {
                    return $item->status != 'completed' && !Str::contains($item->type, 'achievement');
                });

                // 2. HISTORY / PENCAPAIAN: Target Selesai ATAU Tipe Achievement
                $history = $periodData->filter(function($item) {
                    return $item->status == 'completed' || Str::contains($item->type, 'achievement');
                });
            @endphp

            {{-- A. LIST ON GOING --}}
            <div class="mb-4">
                <h6 class="text-uppercase text-primary fw-bold small mb-3 border-bottom pb-2">
                    <i class="mdi mdi-fire text-danger"></i> Target Sedang Berjalan (On Going)
                </h6>
                
                @if($ongoing->count() > 0)
                    @include('job_targets.partials.item_list', ['items' => $ongoing, 'allow_action' => true])
                @else
                    <div class="alert alert-light border-0 text-muted small">
                        <i class="mdi mdi-check-circle-outline me-1"></i> Tidak ada target aktif untuk periode ini.
                    </div>
                @endif
            </div>

            {{-- B. LIST PENCAPAIAN (HISTORY) --}}
            <div>
                <h6 class="text-uppercase text-success fw-bold small mb-3 border-bottom pb-2">
                    <i class="mdi mdi-trophy text-warning"></i> Riwayat Pencapaian & Selesai
                </h6>

                @if($history->count() > 0)
                    @include('job_targets.partials.item_list', ['items' => $history, 'allow_action' => false])
                @else
                    <div class="alert alert-light border-0 text-muted small">
                        Belum ada pencapaian di periode ini.
                    </div>
                @endif
            </div>

        </div>
    @endforeach
</div>

{{-- CSS Tab Custom --}}
<style>
    .nav-pills-custom .nav-link { background: #f8f9fa; color: #6c757d; border: 1px solid #e9ecef; }
    .nav-pills-custom .nav-link.active { background: #4b49ac; color: #fff; border-color: #4b49ac; }
</style>