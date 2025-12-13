{{-- NAV TABS --}}
<ul class="nav nav-pills nav-pills-custom mb-3 small" id="{{ $idPrefix }}Tab" role="tablist">
    <li class="nav-item me-2">
        <a class="nav-link active rounded-pill px-3 fw-bold" data-bs-toggle="pill" href="#{{ $idPrefix }}-daily">Harian</a>
    </li>
    <li class="nav-item me-2">
        <a class="nav-link rounded-pill px-3 fw-bold" data-bs-toggle="pill" href="#{{ $idPrefix }}-monthly">Bulanan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-3 fw-bold" data-bs-toggle="pill" href="#{{ $idPrefix }}-yearly">Tahunan</a>
    </li>
</ul>

<div class="tab-content">
    @foreach(['daily', 'monthly', 'yearly'] as $period)
        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="{{ $idPrefix }}-{{ $period }}">
            
            {{-- Filter Data di PHP View --}}
            @php 
                $filteredData = $dataCollection->where('period', $period);
                // Jika ini adalah 'target', pisahkan yang ongoing dan completed
                $ongoing = $type == 'target' ? $filteredData->where('status', '!=', 'completed') : collect([]);
                $completed = $type == 'target' ? $filteredData->where('status', 'completed') : $filteredData;
            @endphp

            @if($type == 'target')
                {{-- LIST ON GOING --}}
                @if($ongoing->count() > 0)
                    <div class="mb-3">
                        <h6 class="text-uppercase text-primary fw-bold small mb-2"><i class="mdi mdi-fire"></i> Sedang Berjalan</h6>
                        @include('job_targets.partials.item_list', ['items' => $ongoing, 'is_target' => true])
                    </div>
                @endif
            @endif

            {{-- LIST HISTORY / PENCAPAIAN --}}
            <div>
                @if($type == 'target' && $completed->count() > 0)
                    <h6 class="text-uppercase text-success fw-bold small mb-2"><i class="mdi mdi-check-all"></i> Selesai</h6>
                @endif
                
                @include('job_targets.partials.item_list', ['items' => $completed, 'is_target' => ($type == 'target')])
                
                @if($filteredData->count() == 0)
                    <div class="text-center py-4 text-muted bg-light rounded-3">
                        <small>Belum ada data {{ $period }}</small>
                    </div>
                @endif
            </div>

        </div>
    @endforeach
</div>