{{-- NAV TABS --}}
<ul class="nav nav-pills nav-pills-custom mb-4 small flex-nowrap overflow-auto" id="{{ $idPrefix }}Tab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="pill" href="#{{ $idPrefix }}-daily">Harian</a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="pill" href="#{{ $idPrefix }}-monthly">Bulanan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="pill" href="#{{ $idPrefix }}-yearly">Tahunan</a>
    </li>
</ul>

<div class="tab-content">
    @foreach(['daily', 'monthly', 'yearly'] as $period)
        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="{{ $idPrefix }}-{{ $period }}">
            
            {{-- AREA FILTERING --}}
            <div class="bg-light p-3 rounded-3 mb-4 border" id="filter-container-{{ $idPrefix }}-{{ $period }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-auto fw-bold text-muted small"><i class="mdi mdi-filter"></i> Filter:</div>
                    
                    @if($period == 'daily')
                        <div class="col-6 col-md-auto"><input type="date" class="form-control form-control-sm border-secondary filter-input-date"></div>
                        <div class="col-6 col-md-auto"><input type="month" class="form-control form-control-sm border-secondary filter-input-month"></div>
                        <div class="col-6 col-md-auto"><input type="number" class="form-control form-control-sm border-secondary filter-input-year" placeholder="Tahun" min="2020"></div>
                    @elseif($period == 'monthly')
                        <div class="col-6 col-md-auto"><input type="month" class="form-control form-control-sm border-secondary filter-input-month"></div>
                        <div class="col-6 col-md-auto"><input type="number" class="form-control form-control-sm border-secondary filter-input-year" placeholder="Tahun" min="2020"></div>
                    @elseif($period == 'yearly')
                        <div class="col-6 col-md-auto"><input type="number" class="form-control form-control-sm border-secondary filter-input-year" placeholder="Tahun" min="2020"></div>
                    @endif

                    <div class="col-6 col-md-auto d-flex gap-1">
                        <button class="btn btn-primary btn-sm px-3 fw-bold flex-fill" onclick="applyFilter('{{ $idPrefix }}-{{ $period }}')">Cari</button>
                        <button class="btn btn-light btn-sm px-3 border flex-fill" onclick="resetFilter('{{ $idPrefix }}-{{ $period }}')">Reset</button>
                    </div>
                </div>
            </div>

            @php 
                $periodData = $dataCollection->where('period', $period);
                $ongoing = $periodData->filter(function($item) { return $item->status != 'completed' && !Str::contains($item->type, 'achievement'); });
                $history = $periodData->filter(function($item) { return $item->status == 'completed' || Str::contains($item->type, 'achievement'); });
            @endphp

            {{-- CONTAINER DATA --}}
            <div id="data-container-{{ $idPrefix }}-{{ $period }}">
                <div class="mb-4">
                    <h6 class="text-uppercase text-primary fw-bold small mb-3 border-bottom pb-2"><i class="mdi mdi-fire text-danger"></i> On Going</h6>
                    @if($ongoing->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $ongoing, 'allow_action' => true])
                    @else
                        <div class="alert alert-light border-0 text-muted small"><i class="mdi mdi-information-outline"></i> Tidak ada target aktif.</div>
                    @endif
                </div>

                <div>
                    <h6 class="text-uppercase text-success fw-bold small mb-3 border-bottom pb-2"><i class="mdi mdi-trophy text-warning"></i> Selesai & Pencapaian</h6>
                    @if($history->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $history, 'allow_action' => false])
                    @else
                        <div class="alert alert-light border-0 text-muted small">Belum ada pencapaian.</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>