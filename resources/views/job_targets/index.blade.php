@extends('layout.master')

@section('title', 'Target & Pencapaian')
@section('heading', 'Dashboard Target')

@section('content')

@php
    $allTargets = $personalData->concat($teamData);
    $totalActive = $allTargets->where('status', '!=', 'completed')->reject(fn($i) => Str::contains($i->type, 'achievement'))->count();
    $totalCompleted = $allTargets->filter(fn($i) => $i->status == 'completed' || Str::contains($i->type, 'achievement'))->count();
    $totalTeam = $teamData->count();
    $totalPersonal = $personalData->count();
@endphp

{{-- HEADER & BUTTON COMPACT RESPONSIVE --}}
<div class="target-page-header mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center gap-2">
        <div class="min-w-0">
            <h4 class="page-main-title fw-bold text-dark mb-0 text-truncate">Target &amp; Pencapaian</h4>
            <p class="text-muted mb-0 page-sub-desc text-truncate">
                Monitor target <span class="fw-semibold text-primary">{{ auth()->user()->branch->name ?? 'Pusat' }}</span> &amp; pencapaian individu.
            </p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('job-targets.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 text-nowrap shadow-sm">
                <i class="mdi mdi-plus-circle-outline"></i>
                <span class="d-none d-sm-inline">Buat Target</span>
                <span class="d-inline d-sm-none">Buat</span>
            </a>
        </div>
    </div>
</div>

{{-- KPI SUMMARY METRICS COMPACT --}}
<div class="row g-2 g-md-3 mb-3 mb-md-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-2 p-md-3 h-100 kpi-card kpi-warning">
            <div class="text-muted kpi-label mb-1">Target Berjalan</div>
            <div class="d-flex align-items-baseline gap-1 gap-md-2">
                <span class="kpi-value text-dark">{{ $totalActive }}</span>
                <span class="text-muted kpi-unit">Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-2 p-md-3 h-100 kpi-card kpi-success">
            <div class="text-muted kpi-label mb-1">Target Selesai</div>
            <div class="d-flex align-items-baseline gap-1 gap-md-2">
                <span class="kpi-value text-dark">{{ $totalCompleted }}</span>
                <span class="text-muted kpi-unit">Tercapai</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-2 p-md-3 h-100 kpi-card kpi-primary">
            <div class="text-muted kpi-label mb-1">Target Cabang</div>
            <div class="d-flex align-items-baseline gap-1 gap-md-2">
                <span class="kpi-value text-dark">{{ $totalTeam }}</span>
                <span class="text-muted kpi-unit">Total Tim</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-2 p-md-3 h-100 kpi-card kpi-info">
            <div class="text-muted kpi-label mb-1">Target Pribadi</div>
            <div class="d-flex align-items-baseline gap-1 gap-md-2">
                <span class="kpi-value text-dark">{{ $totalPersonal }}</span>
                <span class="text-muted kpi-unit">Personal</span>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 1: CABANG / TIM --}}
<div class="card shadow-sm border-0 rounded-4 mb-3 mb-md-4 overflow-hidden">
    <div class="card-header bg-white border-bottom py-2 py-md-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="min-w-0">
                <h6 class="mb-0 fw-bold text-dark section-card-title text-truncate">Target Cabang</h6>
                <small class="text-muted section-card-sub text-truncate d-none d-sm-block">Sasaran prioritas tim operasional dan keberhasilan cabang</small>
            </div>
            <span class="badge-header-pill flex-shrink-0">
                {{ auth()->user()->branch->name ?? 'Pusat' }}
            </span>
        </div>
    </div>
    <div class="card-body p-2 p-sm-3 p-md-4">
        @include('job_targets.partials.period_tabs', [
            'idPrefix' => 'branch', 
            'dataCollection' => $teamData,
            'allow_edit_detail' => false, 
            'allow_update_status' => false
        ])
    </div>
</div>

{{-- SECTION 2: PRIBADI --}}
<div class="card shadow-sm border-0 rounded-4 mb-4 mb-md-5 overflow-hidden">
    <div class="card-header bg-white border-bottom py-2 py-md-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="min-w-0">
                <h6 class="mb-0 fw-bold text-dark section-card-title text-truncate">Target Pribadi</h6>
                <small class="text-muted section-card-sub text-truncate d-none d-sm-block">Sasaran kerja individual dan portofolio pencapaian Anda</small>
            </div>
            <span class="badge-header-pill flex-shrink-0">
                {{ auth()->user()->name }}
            </span>
        </div>
    </div>
    <div class="card-body p-2 p-sm-3 p-md-4">
        @include('job_targets.partials.period_tabs', [
            'idPrefix' => 'personal', 
            'dataCollection' => $personalData,
            'allow_edit_detail' => true, 
            'allow_update_status' => true
        ])
    </div>
</div>

{{-- MODAL UPDATE STATUS --}}
@include('job_targets.partials.modal_update')

<style>
    /* Header Responsive Typography */
    .page-main-title {
        font-size: 1.15rem;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    .page-sub-desc {
        font-size: 11px;
        line-height: 1.35;
    }
    .section-card-title {
        font-size: 0.95rem;
    }
    .section-card-sub {
        font-size: 11px;
    }

    /* Styling Dasar KPI */
    .kpi-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        padding-left: 14px !important;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 3px;
    }
    .kpi-card.kpi-warning::before { background-color: #f59e0b; }
    .kpi-card.kpi-success::before { background-color: #10b981; }
    .kpi-card.kpi-primary::before { background-color: #2563eb; }
    .kpi-card.kpi-info::before { background-color: #06b6d4; }
    
    .kpi-label {
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kpi-value {
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1;
    }
    .kpi-unit {
        font-size: 10.5px;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.04) !important;
    }

    @media (min-width: 768px) {
        .page-main-title { font-size: 1.5rem; }
        .page-sub-desc { font-size: 13px; }
        .section-card-title { font-size: 1.05rem; }
        .section-card-sub { font-size: 12px; }
        .kpi-card {
            border-radius: 12px !important;
            padding-left: 18px !important;
        }
        .kpi-card::before { width: 4px; }
        .kpi-label { font-size: 12px; }
        .kpi-value { font-size: 1.6rem; }
        .kpi-unit { font-size: 12px; }
    }

    /* Modern Segmented Navigation Tabs */
    .custom-period-tabs {
        background-color: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
        display: inline-flex;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
        max-width: 100%;
        overflow-x: auto;
    }
    .custom-period-tabs .nav-link {
        border: none;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 6px;
        background: transparent;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .custom-period-tabs .nav-link.active {
        background-color: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    /* Filter Box */
    .filter-panel-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 18px;
    }

    /* Section Subheaders */
    .target-group-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 14px;
    }
    .status-indicator-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Item Card */
    .target-item-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
        transition: all 0.15s ease;
    }
    .target-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    /* Priority / Level Badges */
    .priority-badge-3 {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 7px;
        border-radius: 5px;
    }
    .priority-badge-2 {
        background-color: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 7px;
        border-radius: 5px;
    }
    .priority-badge-1 {
        background-color: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 7px;
        border-radius: 5px;
    }
    .priority-badge-achievement {
        background-color: #fefce8;
        color: #854d0e;
        border: 1px solid #fef08a;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 7px;
        border-radius: 5px;
    }

    /* Badges Status & Counter Anti-Nyaru (High Contrast) */
    .badge-count-active {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 1px solid #fcd34d !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 3px 10px !important;
        border-radius: 20px !important;
        display: inline-block;
        letter-spacing: 0.02em;
    }
    .badge-count-completed {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 3px 10px !important;
        border-radius: 20px !important;
        display: inline-block;
        letter-spacing: 0.02em;
    }
    .badge-header-pill {
        background-color: #f8fafc !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        padding: 3px 10px !important;
        border-radius: 20px !important;
        display: inline-block;
    }
    .empty-state-box {
        background-color: #f8fafc !important;
        border: 1px dashed #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 20px 14px !important;
        text-align: center;
    }
    .empty-state-box p {
        color: #64748b !important;
        font-size: 12px !important;
        margin: 0 !important;
        font-weight: 500 !important;
    }
    .meta-chip {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        padding: 2px 8px !important;
        border-radius: 20px !important;
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 768px) {
        .custom-period-tabs {
            width: 100%;
            display: flex;
        }
        .custom-period-tabs .nav-item {
            flex: 1;
            text-align: center;
        }
        .custom-period-tabs .nav-link {
            width: 100%;
            padding: 6px 8px;
            font-size: 11px;
        }
    }
</style>

<script>
    function applyFilter(containerId, periodType) {
        let filterBox = document.getElementById('filter-container-' + containerId);
        let dataContainer = document.getElementById('data-container-' + containerId);
        if (!filterBox || !dataContainer) return;

        let startVal = '', endVal = '';
        if (periodType === 'daily') {
            startVal = filterBox.querySelector('.filter-date-start').value;
            endVal = filterBox.querySelector('.filter-date-end').value;
        } else if (periodType === 'monthly') {
            startVal = filterBox.querySelector('.filter-month-start').value;
            endVal = filterBox.querySelector('.filter-month-end').value;
        } else if (periodType === 'yearly') {
            startVal = filterBox.querySelector('.filter-year-start').value;
            endVal = filterBox.querySelector('.filter-year-end').value;
        }

        let items = dataContainer.querySelectorAll('.filterable-item');
        items.forEach(item => {
            let itemVal = '';
            if (periodType === 'daily') itemVal = item.getAttribute('data-date');   
            else if (periodType === 'monthly') itemVal = item.getAttribute('data-month'); 
            else if (periodType === 'yearly') itemVal = item.getAttribute('data-year');   

            let show = true;
            if (startVal && itemVal < startVal) show = false;
            if (endVal && itemVal > endVal) show = false;

            show ? item.classList.remove('d-none') : item.classList.add('d-none');
        });

        // Update no-data state
        let sections = dataContainer.querySelectorAll('.target-group-section');
        sections.forEach(sec => {
            let visibleItems = sec.querySelectorAll('.filterable-item:not(.d-none)');
            let emptyMsg = sec.querySelector('.no-data-message');
            if (emptyMsg) {
                visibleItems.length === 0 ? emptyMsg.classList.remove('d-none') : emptyMsg.classList.add('d-none');
            }
        });
    }

    function resetFilter(containerId) {
        let filterBox = document.getElementById('filter-container-' + containerId);
        let dataContainer = document.getElementById('data-container-' + containerId);
        if (!filterBox || !dataContainer) return;

        filterBox.querySelectorAll('input').forEach(input => input.value = '');
        dataContainer.querySelectorAll('.filterable-item').forEach(item => item.classList.remove('d-none'));
        dataContainer.querySelectorAll('.no-data-message').forEach(msg => msg.classList.add('d-none'));
    }
</script>
@endsection