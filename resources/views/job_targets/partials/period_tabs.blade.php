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
            
            {{-- AREA FILTERING (BARU) --}}
            <div class="bg-light p-3 rounded-3 mb-4 border d-flex flex-wrap gap-2 align-items-end" id="filter-container-{{ $idPrefix }}-{{ $period }}">
                <div class="fw-bold text-muted small me-2 mb-2"><i class="mdi mdi-filter"></i> Filter:</div>
                
                {{-- Logic Input Berdasarkan Periode --}}
                @if($period == 'daily')
                    {{-- Harian: Bisa filter Tanggal Spesifik, Bulan, atau Tahun --}}
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm border-secondary filter-input-date" placeholder="Tanggal">
                    </div>
                    <div class="form-group">
                        <input type="month" class="form-control form-control-sm border-secondary filter-input-month" placeholder="Bulan">
                    </div>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm border-secondary filter-input-year" placeholder="Tahun (YYYY)" min="2020" max="2030" style="width: 100px;">
                    </div>

                @elseif($period == 'monthly')
                    {{-- Bulanan: Hanya Bulan dan Tahun --}}
                    <div class="form-group">
                        <input type="month" class="form-control form-control-sm border-secondary filter-input-month" placeholder="Bulan">
                    </div>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm border-secondary filter-input-year" placeholder="Tahun (YYYY)" min="2020" max="2030" style="width: 100px;">
                    </div>

                @elseif($period == 'yearly')
                    {{-- Tahunan: Hanya Tahun --}}
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm border-secondary filter-input-year" placeholder="Tahun (YYYY)" min="2020" max="2030" style="width: 100px;">
                    </div>
                @endif

                <button class="btn btn-primary btn-sm px-3 fw-bold" onclick="applyFilter('{{ $idPrefix }}-{{ $period }}')">
                    <i class="mdi mdi-magnify"></i> Cari
                </button>
                <button class="btn btn-light btn-sm px-3 border" onclick="resetFilter('{{ $idPrefix }}-{{ $period }}')">
                    Reset
                </button>
            </div>

            {{-- LOGIC PHP UNTUK DATA --}}
            @php 
                $periodData = $dataCollection->where('period', $period);
                $ongoing = $periodData->filter(function($item) {
                    return $item->status != 'completed' && !Str::contains($item->type, 'achievement');
                });
                $history = $periodData->filter(function($item) {
                    return $item->status == 'completed' || Str::contains($item->type, 'achievement');
                });
            @endphp

            {{-- CONTAINER DATA (Diberi ID agar JS bisa mencari di dalamnya) --}}
            <div id="data-container-{{ $idPrefix }}-{{ $period }}">
                
                {{-- A. ON GOING --}}
                <div class="mb-4">
                    <h6 class="text-uppercase text-primary fw-bold small mb-3 border-bottom pb-2">
                        <i class="mdi mdi-fire text-danger"></i> On Going
                    </h6>
                    @if($ongoing->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $ongoing, 'allow_action' => true])
                    @else
                        <div class="alert alert-light border-0 text-muted small">Tidak ada target aktif.</div>
                    @endif
                </div>

                {{-- B. HISTORY --}}
                <div>
                    <h6 class="text-uppercase text-success fw-bold small mb-3 border-bottom pb-2">
                        <i class="mdi mdi-trophy text-warning"></i> Selesai & Pencapaian
                    </h6>
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