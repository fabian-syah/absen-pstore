<ul class="nav custom-period-tabs" id="{{ $idPrefix }}Tab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#{{ $idPrefix }}-daily" type="button" role="tab">
            Harian
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $idPrefix }}-monthly" type="button" role="tab">
            Bulanan
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $idPrefix }}-yearly" type="button" role="tab">
            Tahunan
        </button>
    </li>
</ul>

<div class="tab-content">
    @foreach (['daily', 'monthly', 'yearly'] as $period)
        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="{{ $idPrefix }}-{{ $period }}">
            
            {{-- AREA FILTERING MODERN & RESPONSIVE --}}
            <div class="filter-panel-box" id="filter-container-{{ $idPrefix }}-{{ $period }}">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-auto me-md-2">
                        <div class="d-flex align-items-center gap-2 text-dark fw-bold small">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span>Filter Periode:</span>
                        </div>
                    </div>
                    
                    @if ($period == 'daily')
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted border-end-0">Dari</span>
                                <input type="date" class="form-control border-start-0 filter-date-start" placeholder="Pilih tanggal">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted border-end-0">Sampai</span>
                                <input type="date" class="form-control border-start-0 filter-date-end" placeholder="Pilih tanggal">
                            </div>
                        </div>
                    @elseif($period == 'monthly')
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted border-end-0">Dari</span>
                                <input type="month" class="form-control border-start-0 filter-month-start">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted border-end-0">Sampai</span>
                                <input type="month" class="form-control border-start-0 filter-month-end">
                            </div>
                        </div>
                    @elseif($period == 'yearly')
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted border-end-0">Dari</span>
                                <input type="number" class="form-control border-start-0 filter-year-start" placeholder="Tahun (e.g. 2026)" min="2020">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted border-end-0">Sampai</span>
                                <input type="number" class="form-control border-start-0 filter-year-end" placeholder="Tahun (e.g. 2026)" min="2020">
                            </div>
                        </div>
                    @endif

                    <div class="col-12 col-md-auto ms-md-auto d-flex gap-2">
                        <button class="btn btn-primary btn-sm px-3 fw-semibold flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1" onclick="applyFilter('{{ $idPrefix }}-{{ $period }}', '{{ $period }}')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <span>Terapkan</span>
                        </button>
                        <button class="btn btn-light border btn-sm px-3 text-secondary fw-semibold flex-fill flex-md-grow-0" onclick="resetFilter('{{ $idPrefix }}-{{ $period }}')">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            @php
                $periodData = $dataCollection->where('period', $period);
                $ongoing = $periodData->filter(function ($item) { return $item->status != 'completed' && !Str::contains($item->type, 'achievement'); });
                $history = $periodData->filter(function ($item) { return $item->status == 'completed' || Str::contains($item->type, 'achievement'); });
                $canEdit = $allow_edit_detail ?? false;
                $canUpdate = $allow_update_status ?? false;
            @endphp

            <div id="data-container-{{ $idPrefix }}-{{ $period }}">
                
                {{-- SUBSECTION 1: ON GOING --}}
                <div class="target-group-section mb-4">
                    <div class="target-group-title">
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-indicator-dot bg-warning"></span>
                            <span class="fw-bold text-dark text-uppercase small" style="letter-spacing: 0.04em;">Target Berjalan</span>
                        </div>
                        <span class="badge-count-active">
                            {{ $ongoing->count() }} Aktif
                        </span>
                    </div>

                    @if ($ongoing->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $ongoing, 'allow_edit_detail' => $canEdit, 'allow_update_status' => $canUpdate])
                    @else
                        <div class="empty-state-box">
                            <p>Belum ada target aktif pada periode ini.</p>
                        </div>
                    @endif
                    <div class="no-data-message alert alert-light border text-muted small py-3 text-center d-none mt-2">
                        Tidak ada target yang sesuai dengan filter tanggal.
                    </div>
                </div>

                {{-- SUBSECTION 2: SELESAI & PENCAPAIAN --}}
                <div class="target-group-section">
                    <div class="target-group-title">
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-indicator-dot bg-success"></span>
                            <span class="fw-bold text-dark text-uppercase small" style="letter-spacing: 0.04em;">Selesai &amp; Pencapaian</span>
                        </div>
                        <span class="badge-count-completed">
                            {{ $history->count() }} Selesai
                        </span>
                    </div>

                    @if ($history->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $history, 'allow_edit_detail' => false, 'allow_update_status' => false])
                    @else
                        <div class="empty-state-box">
                            <p>Belum ada riwayat target selesai atau pencapaian pada periode ini.</p>
                        </div>
                    @endif
                    <div class="no-data-message alert alert-light border text-muted small py-3 text-center d-none mt-2">
                        Tidak ada data yang sesuai dengan filter tanggal.
                    </div>
                </div>

            </div>
        </div>
    @endforeach
</div>