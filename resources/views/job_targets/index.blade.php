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
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-2 py-1 rounded">PORTAL TARGET & KINERJA</span>
        </div>
        <h3 class="fw-bold text-dark mb-1">Manajemen Target & Pencapaian</h3>
        <p class="text-muted mb-0 small">
            Monitor performa kerja cabang <span class="fw-semibold text-primary">{{ auth()->user()->branch->name ?? 'Pusat' }}</span> serta evaluasi pencapaian target individu.
        </p>
    </div>
    <div class="col-12 col-md-4 text-md-end">
        <a href="{{ route('job-targets.create') }}" class="btn btn-primary shadow-sm rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 w-100 w-md-auto">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>Buat Target Baru</span>
        </a>
    </div>
</div>

{{-- KPI SUMMARY METRICS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Target Berjalan</span>
                <span class="kpi-icon-box bg-warning bg-opacity-10 text-warning">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalActive }}</h3>
                <span class="text-muted small">Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Target Selesai</span>
                <span class="kpi-icon-box bg-success bg-opacity-10 text-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalCompleted }}</h3>
                <span class="text-muted small">Tercapai</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Target Cabang</span>
                <span class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 11h2M9 15h2M13 11h2M13 15h2"/></svg>
                </span>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0">{{ $totalTeam }}</h3>
                <span class="text-muted small">Total Tim</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Target Pribadi</span>
                <span class="kpi-icon-box bg-info bg-opacity-10 text-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
            </div>
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
            <div class="d-flex align-items-center gap-3">
                <div class="target-section-icon bg-primary bg-opacity-10 text-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Target & Pencapaian Cabang</h5>
                    <small class="text-muted">Sasaran prioritas tim operasional dan keberhasilan cabang</small>
                </div>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small fw-semibold">
                {{ auth()->user()->branch->name ?? 'Pusat' }}
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
            <div class="d-flex align-items-center gap-3">
                <div class="target-section-icon bg-success bg-opacity-10 text-success">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <polyline points="16 11 18 13 22 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Target & Pencapaian Pribadi</h5>
                    <small class="text-muted">Sasaran kerja individual dan portofolio pencapaian Anda</small>
                </div>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small fw-semibold">
                {{ auth()->user()->name }}
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
    /* Styling Dasar & Komponen */
    .kpi-card {
        background-color: #ffffff;
        border: 1px solid #eef2f6 !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.05) !important;
    }
    .kpi-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .target-section-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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

    /* Item Card / Table */
    .target-item-card {
        background-color: #ffffff;
        border: 1px solid #eef2f6;
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