<ul class="nav nav-tabs mb-4 flex-nowrap border-bottom-0 pb-2" style="overflow-x: auto; overflow-y: hidden; white-space: nowrap; gap: 0.5rem;" id="{{ $idPrefix }}Tab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 fw-bold rounded border" data-bs-toggle="tab" data-bs-target="#{{ $idPrefix }}-daily" type="button" role="tab">Harian</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 fw-bold rounded border text-muted" data-bs-toggle="tab" data-bs-target="#{{ $idPrefix }}-monthly" type="button" role="tab">Bulanan</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 fw-bold rounded border text-muted" data-bs-toggle="tab" data-bs-target="#{{ $idPrefix }}-yearly" type="button" role="tab">Tahunan</button>
    </li>
</ul>

<div class="tab-content">
    @foreach (['daily', 'monthly', 'yearly'] as $period)
        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="{{ $idPrefix }}-{{ $period }}">
            {{-- AREA FILTERING --}}
            <div class="bg-light p-3 rounded mb-4 border" id="filter-container-{{ $idPrefix }}-{{ $period }}">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-auto">
                        <div class="fw-bold text-muted small"><i class="mdi mdi-filter"></i> Filter:</div>
                    </div>
                    @if ($period == 'daily')
                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Dari</span>
                                <input type="date" class="form-control border-secondary filter-date-start">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Sampai</span>
                                <input type="date" class="form-control border-secondary filter-date-end">
                            </div>
                        </div>
                    @elseif($period == 'monthly')
                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Dari</span>
                                <input type="month" class="form-control border-secondary filter-month-start">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Sampai</span>
                                <input type="month" class="form-control border-secondary filter-month-end">
                            </div>
                        </div>
                    @elseif($period == 'yearly')
                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Dari</span>
                                <input type="number" class="form-control border-secondary filter-year-start" placeholder="Tahun" min="2020">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Sampai</span>
                                <input type="number" class="form-control border-secondary filter-year-end" placeholder="Tahun" min="2020">
                            </div>
                        </div>
                    @endif
                    <div class="col-12 col-md-auto ms-md-auto d-flex gap-2 mt-2 mt-md-0">
                        <button class="btn btn-primary btn-sm px-4 fw-bold w-100" onclick="applyFilter('{{ $idPrefix }}-{{ $period }}', '{{ $period }}')"><i class="mdi mdi-magnify"></i> Cari</button>
                        <button class="btn btn-light btn-sm px-4 border w-100" onclick="resetFilter('{{ $idPrefix }}-{{ $period }}')">Reset</button>
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
                <div class="mb-4">
                    <h6 class="text-uppercase text-primary fw-bold small mb-3 border-bottom pb-2"><i class="mdi mdi-fire text-danger"></i> On Going</h6>
                    @if ($ongoing->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $ongoing, 'allow_edit_detail' => $canEdit, 'allow_update_status' => $canUpdate])
                    @else
                        <div class="alert alert-light border-0 text-muted small"><i class="mdi mdi-information-outline"></i> Tidak ada target aktif.</div>
                    @endif
                </div>
                <div>
                    <h6 class="text-uppercase text-success fw-bold small mb-3 border-bottom pb-2"><i class="mdi mdi-trophy text-warning"></i> Selesai & Pencapaian</h6>
                    @if ($history->count() > 0)
                        @include('job_targets.partials.item_list', ['items' => $history, 'allow_edit_detail' => false, 'allow_update_status' => false])
                    @else
                        <div class="alert alert-light border-0 text-muted small">Belum ada pencapaian.</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>