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
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div class="fw-bold text-muted small me-1 mb-1"><i class="mdi mdi-filter"></i> Filter:</div>
                    
                    @if($period == 'daily')
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <span class="input-group-text bg-white">Dari</span>
                            <input type="date" class="form-control border-secondary filter-date-start">
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <span class="input-group-text bg-white">Sampai</span>
                            <input type="date" class="form-control border-secondary filter-date-end">
                        </div>
                    @elseif($period == 'monthly')
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <span class="input-group-text bg-white">Dari</span>
                            <input type="month" class="form-control border-secondary filter-month-start">
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <span class="input-group-text bg-white">Sampai</span>
                            <input type="month" class="form-control border-secondary filter-month-end">
                        </div>
                    @elseif($period == 'yearly')
                        <div class="input-group input-group-sm" style="max-width: 250px;">
                            <span class="input-group-text bg-white">Dari</span>
                            <input type="number" class="form-control border-secondary filter-year-start" placeholder="Tahun" min="2020">
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 250px;">
                            <span class="input-group-text bg-white">Sampai</span>
                            <input type="number" class="form-control border-secondary filter-year-end" placeholder="Tahun" min="2020">
                        </div>
                    @endif

                    <div class="d-flex gap-1 ms-md-2 mt-2 mt-md-0">
                        <button class="btn btn-primary btn-sm px-3 fw-bold" onclick="applyFilter('{{ $idPrefix }}-{{ $period }}', '{{ $period }}')">
                            <i class="mdi mdi-magnify"></i> Cari
                        </button>
                        <button class="btn btn-light btn-sm px-3 border" onclick="resetFilter('{{ $idPrefix }}-{{ $period }}')">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            @php 
                $periodData = $dataCollection->where('period', $period);
                $ongoing = $periodData->filter(function($item) { return $item->status != 'completed' && !Str::contains($item->type, 'achievement'); });
                $history = $periodData->filter(function($item) { return $item->status == 'completed' || Str::contains($item->type, 'achievement'); });
                
                // LOGIC PENTING: Ambil variabel allow_action, jika tidak ada default ke false
                $isEditable = $allow_action ?? false;
            @endphp

            {{-- CONTAINER DATA --}}
            <div id="data-container-{{ $idPrefix }}-{{ $period }}">
                <div class="mb-4">
                    <h6 class="text-uppercase text-primary fw-bold small mb-3 border-bottom pb-2"><i class="mdi mdi-fire text-danger"></i> On Going</h6>
                    @if($ongoing->count() > 0)
                        {{-- PASSING $isEditable ke item_list --}}
                        @include('job_targets.partials.item_list', ['items' => $ongoing, 'allow_action' => $isEditable])
                    @else
                        <div class="alert alert-light border-0 text-muted small"><i class="mdi mdi-information-outline"></i> Tidak ada target aktif.</div>
                    @endif
                </div>

                <div>
                    <h6 class="text-uppercase text-success fw-bold small mb-3 border-bottom pb-2"><i class="mdi mdi-trophy text-warning"></i> Selesai & Pencapaian</h6>
                    @if($history->count() > 0)
                        {{-- History selalu false (tidak bisa diedit) --}}
                        @include('job_targets.partials.item_list', ['items' => $history, 'allow_action' => false])
                    @else
                        <div class="alert alert-light border-0 text-muted small">Belum ada pencapaian.</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>