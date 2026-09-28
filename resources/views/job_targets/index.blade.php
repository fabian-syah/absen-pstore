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

{{-- HEADER & BUTTON --}}
<div class="row align-items-center mb-4 g-3">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">Manajemen Target &amp; Pencapaian</h3>
        <p class="text-muted mb-0 small">
            Monitor performa kerja cabang <span class="fw-semibold text-primary">{{ auth()->user()->branch->name ?? 'Pusat' }}</span> serta evaluasi pencapaian target individu.
        </p>
    </div>
    <div class="col-12 col-md-4 text-md-end">
        <a href="{{ route('job-targets.create') }}" class="btn btn-primary shadow-sm rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 text-nowrap w-100 w-md-auto">
            <i class="mdi mdi-plus-circle-outline fs-5"></i>
            <span>Buat Target</span>
        </a>
    </div>
</div>

{{-- KPI SUMMARY METRICS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100 kpi-card kpi-warning">
            <div class="text-muted small fw-semibold mb-2">Target Berjalan</div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalActive }}</h3>
                <span class="text-muted small">Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100 kpi-card kpi-success">
            <div class="text-muted small fw-semibold mb-2">Target Selesai</div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalCompleted }}</h3>
                <span class="text-muted small">Tercapai</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100 kpi-card kpi-primary">
            <div class="text-muted small fw-semibold mb-2">Target Cabang</div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalTeam }}</h3>
                <span class="text-muted small">Total Tim</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100 kpi-card kpi-info">
            <div class="text-muted small fw-semibold mb-2">Target Pribadi</div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalPersonal }}</h3>
                <span class="text-muted small">Personal</span>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 1: CABANG / TIM --}}
<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold text-dark">Target &amp; Pencapaian Cabang</h5>
                <small class="text-muted">Sasaran prioritas tim operasional dan keberhasilan cabang</small>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small fw-semibold">
                Cabang {{ auth()->user()->branch->name ?? 'Pusat' }}
            </span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        @include('job_targets.partials.period_tabs', [
            'idPrefix' => 'branch', 
            'dataCollection' => $teamData,
            'allow_edit_detail' => false, 
            'allow_update_status' => false
        ])
    </div>
</div>

{{-- SECTION 2: PRIBADI --}}
<div class="card shadow-sm border-0 rounded-4 mb-5 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold text-dark">Target &amp; Pencapaian Pribadi</h5>
                <small class="text-muted">Sasaran kerja individual dan portofolio pencapaian Anda</small>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small fw-semibold">
                Personal &bull; {{ auth()->user()->name }}
            </span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
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
    /* Styling Dasar KPI */
    .kpi-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        padding-left: 20px !important;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
    }
    .kpi-card.kpi-warning::before { background-color: #f59e0b; }
    .kpi-card.kpi-success::before { background-color: #10b981; }
    .kpi-card.kpi-primary::before { background-color: #2563eb; }
    .kpi-card.kpi-info::before { background-color: #06b6d4; }
    
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.04) !important;
    }

    /* Modern Segmented Navigation Tabs */
    .custom-period-tabs {
        background-color: #f1f5f9;
        padding: 4px;
        border-radius: 10px;
        display: inline-flex;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        max-width: 100%;
        overflow-x: auto;
    }
    .custom-period-tabs .nav-link {
        border: none;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 8px;
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
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    /* Section Subheaders */
    .target-group-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 16px;
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
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
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
        padding: 4px 8px;
        border-radius: 6px;
    }
    .priority-badge-2 {
        background-color: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .priority-badge-1 {
        background-color: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .priority-badge-achievement {
        background-color: #fefce8;
        color: #854d0e;
        border: 1px solid #fef08a;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
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
            padding: 8px 12px;
            font-size: 12px;
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