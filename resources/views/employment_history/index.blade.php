@extends('layout.master')

@section('title', 'Riwayat Karir')

@push('styles')
<style>
    /* ============================================================
       RIWAYAT KARIR — MODERN REDESIGN
    ============================================================ */

    /* Hero Header */
    .career-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 60%, #084298 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
        color: #fff;
        box-shadow: 0 8px 32px rgba(13, 110, 253, 0.35);
    }
    .career-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .career-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -30px;
        width: 280px; height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .career-hero-title {
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.3px;
        margin: 0 0 .25rem;
    }
    .career-hero-sub {
        font-size: 0.88rem;
        opacity: 0.82;
        margin: 0;
    }
    .career-hero-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,0.18);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        backdrop-filter: blur(8px);
        flex-shrink: 0;
    }
    .mode-edit-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: 999px;
        padding: 4px 14px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        backdrop-filter: blur(6px);
        color: #fff;
    }

    /* Global reset inside search area — prevent letter-spacing inheritance from master CSS */
    .employee-search-card *,
    .employee-dropdown *,
    .selected-employee-strip * {
        letter-spacing: normal !important;
        text-transform: none !important;
    }
    /* Re-apply uppercase only where intended */
    .employee-search-card .search-label {
        letter-spacing: 1px !important;
        text-transform: uppercase !important;
    }

    /* Employee Search / Select Panel */
    .employee-search-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.75rem;
        border: 1px solid rgba(13,110,253,0.08);
        transition: box-shadow .3s;
    }
    .employee-search-card:hover {
        box-shadow: 0 8px 28px rgba(13,110,253,0.12);
    }
    .search-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6c757d;
        margin-bottom: .6rem;
    }
    .custom-select-wrap {
        position: relative;
    }
    .custom-select-wrap .search-input {
        width: 100%;
        padding: .65rem 1rem .65rem 2.6rem;
        border: 1.5px solid #dee2e6;
        border-radius: 12px;
        font-size: .9rem;
        transition: border-color .25s, box-shadow .25s;
        background: #f8f9fa;
        outline: none;
    }
    .custom-select-wrap .search-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,.15);
        background: #fff;
    }
    .custom-select-wrap .search-icon {
        position: absolute; left: .85rem; top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.05rem;
        pointer-events: none;
    }
    .employee-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0; right: 0;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.07);
        max-height: 320px;
        overflow-y: auto;
        z-index: 1050;
        display: none;
        animation: dropIn .2s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes dropIn {
        from { opacity: 0; transform: translateY(-8px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .employee-dropdown.show { display: block; }
    .employee-dropdown-item {
        display: flex; align-items: center; gap: .75rem;
        padding: .7rem 1.1rem;
        cursor: pointer;
        transition: background .15s;
        border-radius: 0;
        font-size: .875rem;
        letter-spacing: normal;
        text-transform: none;
    }
    .employee-dropdown-item:first-child { border-radius: 14px 14px 0 0; }
    .employee-dropdown-item:last-child  { border-radius: 0 0 14px 14px; }
    .employee-dropdown-item:hover, .employee-dropdown-item.highlighted {
        background: rgba(13,110,253,.06);
    }
    .employee-dropdown-item.active-item {
        background: rgba(13,110,253,.1);
        color: #0d6efd;
        font-weight: 700;
    }
    .employee-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .employee-avatar.self-avatar {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .employee-name { font-weight: 600; line-height: 1.2; letter-spacing: normal; text-transform: none; }
    .employee-branch { font-size: .75rem; color: #9ca3af; margin-top: 1px; letter-spacing: normal; }
    .no-results-msg {
        padding: 1.5rem;
        text-align: center;
        color: #9ca3af;
        font-size: .875rem;
    }

    /* Selected Employee Profile Strip */
    .selected-employee-strip {
        display: flex; align-items: center; gap: 1rem;
        background: linear-gradient(135deg, rgba(13,110,253,.06) 0%, rgba(10,88,202,.04) 100%);
        border: 1px solid rgba(13,110,253,.15);
        border-radius: 14px;
        padding: .9rem 1.2rem;
        margin-top: .75rem;
        transition: all .3s;
    }
    .selected-avatar-lg {
        width: 46px; height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(13,110,253,.3);
    }
    .selected-employee-name {
        font-weight: 700;
        font-size: 1rem;
        color: #1b2620;
        line-height: 1.2;
        letter-spacing: normal;
        text-transform: none;
    }
    .selected-employee-meta {
        font-size: .78rem;
        color: #6c757d;
        margin-top: 2px;
        letter-spacing: normal;
        text-transform: none;
    }
    .role-pill {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(13,110,253,.1);
        color: #0d6efd;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    /* Timeline Section */
    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1.5px solid rgba(0,0,0,0.06);
    }
    .section-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1b2620;
        letter-spacing: -0.2px;
        margin: 0;
    }
    .section-subtitle {
        font-size: .8rem;
        color: #9ca3af;
        margin: 2px 0 0;
    }

    /* Timeline Items */
    .timeline-wrap {
        position: relative;
        padding-left: 2rem;
    }
    .timeline-wrap::before {
        content: '';
        position: absolute;
        left: 10px; top: 8px; bottom: 8px;
        width: 2px;
        background: linear-gradient(to bottom, #0d6efd, rgba(13,110,253,.1));
        border-radius: 999px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
        animation: fadeSlideIn .4s ease both;
    }
    .timeline-item:last-child { margin-bottom: 0; }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateX(-12px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .timeline-dot {
        position: absolute;
        left: -2rem;
        top: .9rem;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #0d6efd;
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        display: flex; align-items: center; justify-content: center;
        font-size: .55rem;
        color: #0d6efd;
        transition: transform .25s;
    }
    .timeline-item:hover .timeline-dot { transform: scale(1.2); }
    .timeline-dot.dot-success  { border-color: #10b981; color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,.12); }
    .timeline-dot.dot-danger   { border-color: #ef4444; color: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.12); }
    .timeline-dot.dot-warning  { border-color: #f59e0b; color: #f59e0b; box-shadow: 0 0 0 4px rgba(245,158,11,.12); }
    .timeline-dot.dot-info     { border-color: #06b6d4; color: #06b6d4; box-shadow: 0 0 0 4px rgba(6,182,212,.12); }
    .timeline-dot.dot-secondary{ border-color: #6c757d; color: #6c757d; box-shadow: 0 0 0 4px rgba(108,117,125,.12); }

    .timeline-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.1rem 1.4rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid rgba(0,0,0,.06);
        transition: box-shadow .25s, transform .25s;
    }
    .timeline-card:hover {
        box-shadow: 0 6px 24px rgba(13,110,253,.12);
        transform: translateY(-2px);
    }
    .timeline-event-type {
        font-size: .9rem;
        font-weight: 800;
        margin: 0 0 .3rem;
    }
    .timeline-event-type.text-primary   { color: #0d6efd !important; }
    .timeline-event-type.text-success   { color: #10b981 !important; }
    .timeline-event-type.text-danger    { color: #ef4444 !important; }
    .timeline-event-type.text-warning   { color: #f59e0b !important; }
    .timeline-event-type.text-info      { color: #06b6d4 !important; }
    .timeline-event-type.text-secondary { color: #6c757d !important; }
    .timeline-date-chip {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .75rem; color: #9ca3af;
        background: #f8f9fa;
        border-radius: 999px;
        padding: 3px 10px;
        margin-top: 2px;
    }
    .timeline-detail-box {
        margin-top: .85rem;
        padding: .85rem 1rem;
        border-radius: 10px;
        background: #f8f9ff;
        border-left: 3px solid #0d6efd;
        font-size: .86rem;
    }
    .timeline-detail-box.border-success { border-left-color: #10b981 !important; }
    .timeline-detail-box.border-danger  { border-left-color: #ef4444 !important; }
    .timeline-detail-box.border-warning { border-left-color: #f59e0b !important; }
    .timeline-detail-box.border-info    { border-left-color: #06b6d4 !important; }
    .timeline-detail-box.border-secondary { border-left-color: #6c757d !important; }
    .timeline-detail-row {
        display: flex; align-items: baseline; gap: .5rem;
        margin-bottom: .35rem;
        color: #4b5563;
    }
    .timeline-detail-row:last-child { margin-bottom: 0; }
    .timeline-detail-row i { color: #9ca3af; font-size: .9rem; flex-shrink: 0; }
    .timeline-desc {
        font-style: italic;
        color: #6c757d;
        font-size: .82rem;
        border-top: 1px dashed #e5e7eb;
        padding-top: .55rem;
        margin-top: .55rem;
        line-height: 1.5;
    }
    .timeline-attachment {
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        max-width: 120px;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
        transition: transform .2s;
        flex-shrink: 0;
    }
    .timeline-attachment:hover { transform: scale(1.04); }
    .timeline-attachment img { width: 100%; height: 80px; object-fit: cover; display: block; }
    .action-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .9rem;
        border: none;
        cursor: pointer;
        transition: all .2s;
        text-decoration: none;
    }
    .action-btn-edit {
        background: rgba(245,158,11,.1);
        color: #f59e0b;
    }
    .action-btn-edit:hover {
        background: #f59e0b;
        color: #fff;
        box-shadow: 0 4px 10px rgba(245,158,11,.35);
    }
    .action-btn-delete {
        background: rgba(239,68,68,.1);
        color: #ef4444;
    }
    .action-btn-delete:hover {
        background: #ef4444;
        color: #fff;
        box-shadow: 0 4px 10px rgba(239,68,68,.35);
    }
    .action-btn-upload {
        background: rgba(99,102,241,.1);
        color: #6366f1;
    }
    .action-btn-upload:hover {
        background: #6366f1;
        color: #fff;
        box-shadow: 0 4px 10px rgba(99,102,241,.35);
    }

    /* Upload Modal */
    .upload-dropzone {
        border: 2px dashed rgba(99,102,241,.35);
        border-radius: 14px;
        padding: 2rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all .25s;
        background: rgba(99,102,241,.03);
        position: relative;
    }
    .upload-dropzone:hover, .upload-dropzone.dragover {
        border-color: #6366f1;
        background: rgba(99,102,241,.08);
    }
    .upload-dropzone input[type=file] {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .upload-preview {
        display: none;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,.1);
        position: relative;
    }
    .upload-preview img {
        width: 100%; max-height: 200px; object-fit: contain;
        background: #f8f9fa;
    }
    .upload-preview-pdf {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
        display: none;
        align-items: center;
        gap: 1rem;
    }
    .file-info-chips {
        display: flex; flex-wrap: wrap; gap: .5rem;
        margin-top: 1rem;
    }
    .file-info-chip {
        display: inline-flex; align-items: center; gap: 4px;
        background: #f1f5f9;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: .75rem;
        color: #64748b;
        font-weight: 600;
    }
    .file-info-chip.chip-ok { background: rgba(16,185,129,.1); color: #059669; }
    .file-info-chip.chip-warn { background: rgba(245,158,11,.1); color: #d97706; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3.5rem 1rem;
    }
    .empty-state-icon {
        width: 72px; height: 72px;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(13,110,253,.08), rgba(13,110,253,.04));
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
        font-size: 2rem;
        color: #cbd5e1;
    }
    .empty-state h5 { font-weight: 700; color: #9ca3af; font-size: .95rem; margin: 0 0 .35rem; }
    .empty-state p  { font-size: .82rem; color: #c4cbd4; margin: 0; }

    /* External Experience Table */
    .ext-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .ext-table thead th {
        background: linear-gradient(135deg, rgba(13,110,253,.05), rgba(13,110,253,.02)) !important;
        color: #6c757d !important;
        font-size: .75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: .8px;
        padding: .75rem 1rem !important;
        border-bottom: 1.5px solid rgba(0,0,0,.06) !important;
        border-top: none !important;
    }
    .ext-table tbody tr {
        transition: background .15s;
    }
    .ext-table tbody tr:hover td {
        background: rgba(13,110,253,.03) !important;
    }
    .ext-table tbody td {
        padding: .85rem 1rem !important;
        border-bottom: 1px solid rgba(0,0,0,.04) !important;
        font-size: .875rem;
        vertical-align: middle;
    }
    .ext-table tbody tr:last-child td {
        border-bottom: none !important;
    }
    .ext-title-cell { font-weight: 700; color: #0d6efd; }

    /* Add button */
    .btn-add-history {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff !important;
        border: none;
        border-radius: 10px;
        padding: .5rem 1.1rem;
        font-size: .85rem;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(13,110,253,.3);
        transition: all .25s;
        text-decoration: none;
    }
    .btn-add-history:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13,110,253,.4);
        color: #fff;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .career-hero { padding: 1.4rem 1.25rem; }
        .career-hero-title { font-size: 1.3rem; }
        .employee-search-card { padding: 1.1rem 1rem; }
        .timeline-card { padding: .9rem 1rem; }
    }

    /* Hidden select (for form submission) */
    #hiddenUserSelect { display: none; }
</style>
@endpush

@section('content')

{{-- HARD RESET: override any global letter-spacing / word-spacing injected by vendor/master CSS --}}
<style>
    .employee-search-card *,
    .employee-dropdown *,
    .selected-employee-strip *,
    .employee-dropdown-item,
    .employee-name,
    .selected-employee-name {
        letter-spacing: 0 !important;
        word-spacing: 0 !important;
        word-spacing: normal !important;
        text-transform: none !important;
        font-variant: normal !important;
        font-variant-caps: normal !important;
        font-variant-ligatures: normal !important;
        font-feature-settings: normal !important;
    }
    .employee-search-card .search-label {
        letter-spacing: 1px !important;
        text-transform: uppercase !important;
    }
</style>

{{-- ========================================================
     HERO HEADER
     ======================================================== --}}
<div class="career-hero mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-3">
            <div class="career-hero-icon">
                <i class="mdi mdi-timeline-clock-outline"></i>
            </div>
            <div>
                <h1 class="career-hero-title">Riwayat Karir</h1>
                <p class="career-hero-sub">
                    @if($canEdit)
                        <span class="mode-edit-badge">
                            <i class="mdi mdi-pencil" style="font-size:.85rem;"></i> Mode Edit Aktif
                        </span>
                    @else
                        Timeline perjalanan karir karyawan Pstore
                    @endif
                </p>
            </div>
        </div>
        @if($canCreate)
            <a href="{{ route('employment-history.create', ['user_id' => $targetUser->id]) }}" class="btn-add-history">
                <i class="mdi mdi-plus-circle-outline"></i> Tambah Riwayat
            </a>
        @endif
    </div>
</div>

{{-- ========================================================
     EMPLOYEE SEARCH — Only for Admin / Audit / Leader
     ======================================================== --}}
@if(in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
<div class="employee-search-card">
    <p class="search-label"><i class="mdi mdi-account-search me-1"></i> Pilih Karyawan</p>

    {{-- Hidden real form that will be submitted --}}
    <form id="switchUserForm" action="{{ route('employment-history.index') }}" method="GET">
        @if(request()->get('mode') == 'edit')
            <input type="hidden" name="mode" value="edit">
        @endif
        <select name="user_id" id="hiddenUserSelect">
            <option value="{{ auth()->user()->id }}" {{ isset($targetUser) && $targetUser->id == auth()->id() ? 'selected' : '' }}>
                Saya Sendiri
            </option>
            @foreach($selectableUsers as $u)
                @if($u->id != auth()->id())
                    <option value="{{ $u->id }}" {{ isset($targetUser) && $targetUser->id == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endif
            @endforeach
        </select>
    </form>

    {{-- Custom search UI --}}
    <div class="custom-select-wrap" id="employeeSearchWrap">
        <i class="mdi mdi-magnify search-icon"></i>
        <input
            type="text"
            class="search-input"
            id="employeeSearchInput"
            placeholder="Cari nama karyawan..."
            autocomplete="off"
        >
        <div class="employee-dropdown" id="employeeDropdown">
            {{-- Injected by JS --}}
        </div>
    </div>

    {{-- Selected Employee Strip --}}
    <div class="selected-employee-strip" id="selectedEmployeeStrip">
        {{-- Avatar: foto profil atau inisial --}}
        @if($targetUser->profile_photo_path)
            <img src="{{ asset('storage/' . $targetUser->profile_photo_path) }}"
                 class="selected-avatar-lg"
                 style="object-fit:cover;padding:0;"
                 alt="{{ $targetUser->name }}"
                 onerror="this.outerHTML='<div class=&quot;selected-avatar-lg&quot; id=&quot;selectedAvatar&quot;>{{ strtoupper(substr($targetUser->name, 0, 2)) }}</div>'">
        @else
            <div class="selected-avatar-lg" id="selectedAvatar">
                {{ strtoupper(substr($targetUser->name, 0, 2)) }}
            </div>
        @endif
        <div style="flex:1; min-width:0;">
            <div class="selected-employee-name" id="selectedName">{{ $targetUser->name }}</div>
            <div class="selected-employee-meta">
                <span class="role-pill">
                    <i class="mdi mdi-badge-account-outline" style="font-size:.8rem;"></i>
                    {{ strtoupper($targetUser->role) }}
                </span>
                &nbsp;{{ $targetUser->branch->name ?? 'Pusat / Non-Cabang' }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            {{-- Tombol Mode Edit: hanya admin --}}
            @if(auth()->user()->role === 'admin')
                @if(request()->get('mode') == 'edit')
                    <a href="{{ route('employment-history.index', ['user_id' => $targetUser->id]) }}"
                       class="action-btn" style="background:rgba(16,185,129,.12);color:#10b981;" title="Keluar Mode Edit">
                        <i class="mdi mdi-pencil-off-outline"></i>
                    </a>
                @else
                    <a href="{{ route('employment-history.index', ['user_id' => $targetUser->id, 'mode' => 'edit']) }}"
                       class="action-btn action-btn-edit" title="Aktifkan Mode Edit Riwayat">
                        <i class="mdi mdi-pencil-outline"></i>
                    </a>
                @endif
            @endif
            <i class="mdi mdi-check-circle text-success" style="font-size:1.4rem;opacity:.7;"></i>
        </div>
    </div>
</div>
@endif

{{-- ========================================================
     TIMELINE — INTERNAL PSTORE
     ======================================================== --}}
<div class="card mb-4" style="border-radius:20px!important;">
    <div class="card-body" style="padding:1.75rem!important;">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="mdi mdi-office-building-outline me-2 text-primary" style="font-size:1.1rem;"></i>
                    Timeline Internal Pstore
                </h2>
                <p class="section-subtitle">{{ $targetUser->name }} &bull; {{ strtoupper($targetUser->role) }} — {{ $targetUser->branch->name ?? 'PUSAT' }}</p>
            </div>
            <span class="badge" style="background:rgba(13,110,253,.1);color:#0d6efd;font-size:.75rem;padding:.4rem .9rem;border-radius:999px;">
                {{ $internalHistories->count() }} entri
            </span>
        </div>

        @if($internalHistories->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="mdi mdi-timeline-text-outline"></i>
                </div>
                <h5>Belum ada riwayat</h5>
                <p>Riwayat karir internal Pstore akan muncul di sini.</p>
            </div>
        @else
            <div class="timeline-wrap">
                @foreach($internalHistories as $i => $history)
                    @php
                        $dotClass = match($history->type_color) {
                            'success'   => 'dot-success',
                            'danger'    => 'dot-danger',
                            'warning'   => 'dot-warning',
                            'info'      => 'dot-info',
                            'secondary' => 'dot-secondary',
                            default     => '',
                        };
                        $borderClass = match($history->type_color) {
                            'success'   => 'border-success',
                            'danger'    => 'border-danger',
                            'warning'   => 'border-warning',
                            'info'      => 'border-info',
                            'secondary' => 'border-secondary',
                            default     => '',
                        };
                    @endphp
                    <div class="timeline-item" style="animation-delay: {{ $i * 0.06 }}s;">
                        <div class="timeline-dot {{ $dotClass }}">
                            <i class="mdi mdi-circle" style="font-size:.45rem;"></i>
                        </div>
                        <div class="timeline-card">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div style="min-width:0;">
                                    <p class="timeline-event-type text-{{ $history->type_color }}">
                                        {{ $history->type_label }}
                                    </p>
                                    <span class="timeline-date-chip">
                                        <i class="mdi mdi-calendar-month-outline"></i>
                                        {{ \Carbon\Carbon::parse($history->event_date)->translatedFormat('d F Y') }}
                                    </span>
                                </div>
                                {{-- Actions --}}
                                <div class="d-flex gap-1 flex-shrink-0">
                                    {{-- Tombol Upload Lampiran: khusus admin --}}
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button"
                                                class="action-btn action-btn-upload"
                                                title="Upload / Ganti Lampiran"
                                                @php $isPdf = str_ends_with(strtolower($history->attachment ?? ''), '.pdf'); @endphp
                                                onclick="openUploadModal({{ $history->id }}, '{{ $history->attachment ? asset('storage/' . $history->attachment) : '' }}', {{ $isPdf ? 'true' : 'false' }})"
                                        >
                                            <i class="mdi mdi-image-edit-outline"></i>
                                        </button>
                                    @endif
                                    @if($canEdit)
                                        <a href="{{ route('employment-history.edit', $history->id) }}"
                                           class="action-btn action-btn-edit" title="Edit Data">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                    @endif
                                    @if($canDelete)
                                        <form action="{{ route('employment-history.destroy', $history->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus riwayat ini? Tindakan tidak bisa dibatalkan.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="action-btn action-btn-delete" title="Hapus">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Detail box --}}
                            @php $hasDetail = $history->type == 'transfer_branch' || ($history->type != 'resign' && ($history->branch || $history->division)) || $history->attachment || $history->description; @endphp
                            @if($hasDetail)
                                <div class="timeline-detail-box {{ $borderClass }}">
                                    <div class="d-flex gap-3 align-items-start">
                                        {{-- Attachment thumbnail --}}
                                        @if($history->attachment)
                                            @php $isTimelinePdf = str_ends_with(strtolower($history->attachment), '.pdf'); @endphp
                                            <div class="timeline-attachment"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#attachmentModal"
                                                 data-src="{{ asset('storage/' . $history->attachment) }}"
                                                 data-is-pdf="{{ $isTimelinePdf ? 'true' : 'false' }}"
                                                 title="Lihat lampiran">
                                                @if($isTimelinePdf)
                                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#fee2e2;">
                                                        <i class="mdi mdi-file-pdf-box" style="font-size:2rem;color:#ef4444;"></i>
                                                    </div>
                                                @else
                                                    <img src="{{ asset('storage/' . $history->attachment) }}" alt="Lampiran">
                                                @endif
                                            </div>
                                        @endif
                                        <div style="flex:1;min-width:0;">
                                            @if($history->type == 'transfer_branch')
                                                <div class="timeline-detail-row">
                                                    <i class="mdi mdi-arrow-right-bold-circle text-success"></i>
                                                    <span>Pindah ke: <strong>{{ $history->branch->name ?? '-' }}</strong></span>
                                                </div>
                                            @elseif($history->type != 'resign')
                                                @if($history->branch)
                                                    <div class="timeline-detail-row">
                                                        <i class="mdi mdi-map-marker-outline"></i>
                                                        <span>Cabang: <strong>{{ $history->branch->name }}</strong></span>
                                                    </div>
                                                @endif
                                                @if($history->division)
                                                    <div class="timeline-detail-row">
                                                        <i class="mdi mdi-briefcase-outline"></i>
                                                        <span>Divisi: <strong>{{ $history->division->name }}</strong></span>
                                                    </div>
                                                @endif
                                            @endif
                                            @if($history->description)
                                                <div class="timeline-desc">
                                                    <i class="mdi mdi-comment-quote-outline me-1"></i>
                                                    "{{ $history->description }}"
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ========================================================
     PENGALAMAN DI LUAR PSTORE
     ======================================================== --}}
<div class="card" style="border-radius:20px!important;">
    <div class="card-body" style="padding:1.75rem!important;">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="mdi mdi-earth me-2 text-success" style="font-size:1.1rem;"></i>
                    Pengalaman di Luar Pstore
                </h2>
                <p class="section-subtitle">Riwayat kerja / pengalaman sebelum bergabung</p>
            </div>
            <span class="badge" style="background:rgba(16,185,129,.1);color:#10b981;font-size:.75rem;padding:.4rem .9rem;border-radius:999px;">
                {{ $externalHistories->count() }} entri
            </span>
        </div>

        @if($externalHistories->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon" style="background:linear-gradient(135deg,rgba(16,185,129,.08),rgba(16,185,129,.03));">
                    <i class="mdi mdi-domain" style="color:#a7f3d0;"></i>
                </div>
                <h5>Tidak ada data</h5>
                <p>Pengalaman kerja di luar Pstore akan muncul di sini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="ext-table">
                    <thead>
                        <tr>
                            <th>Judul / Perusahaan</th>
                            <th>Keterangan</th>
                            <th>Lampiran</th>
                            @if($canEdit || $canDelete)
                                <th class="text-end">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($externalHistories as $ext)
                            <tr>
                                <td><span class="ext-title-cell">{{ $ext->title }}</span></td>
                                <td class="text-muted" style="max-width:300px;">{{ $ext->description ?? '-' }}</td>
                                <td>
                                    @if($ext->attachment)
                                        @php $isExtPdf = str_ends_with(strtolower($ext->attachment), '.pdf'); @endphp
                                        <a href="#"
                                           data-bs-toggle="modal"
                                           data-bs-target="#attachmentModal"
                                           data-src="{{ asset('storage/' . $ext->attachment) }}"
                                           data-is-pdf="{{ $isExtPdf ? 'true' : 'false' }}"
                                           style="display:inline-flex;align-items:center;gap:5px;color:{{ $isExtPdf ? '#ef4444' : '#0d6efd' }};font-size:.82rem;font-weight:600;text-decoration:none;">
                                            <i class="mdi {{ $isExtPdf ? 'mdi-file-pdf-box' : 'mdi-image-outline' }}" style="font-size:1rem;"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @if($canEdit || $canDelete)
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if($canEdit)
                                                <a href="{{ route('employment-history.edit', $ext->id) }}"
                                                   class="action-btn action-btn-edit" title="Edit">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </a>
                                            @endif
                                            @if($canDelete)
                                                <form action="{{ route('employment-history.destroy', $ext->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Hapus pengalaman ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="action-btn action-btn-delete" title="Hapus">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ========================================================
     MODAL — ATTACHMENT VIEWER
     ======================================================== --}}
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:20px;border:none;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);padding:1.25rem 1.5rem;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="mdi mdi-image-outline me-2"></i>Lampiran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background:#f8f9fa;">
                <div style="border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.12);">
                    <img id="modalImageSrc" src="" class="img-fluid" style="max-height:75vh;width:100%;object-fit:contain;display:none;" alt="Lampiran">
                    <iframe id="modalPdfSrc" src="" style="width:100%;height:75vh;border:none;display:none;background:#fff;"></iframe>
                </div>
                <div id="modalPdfFallback" style="display:none;text-align:center;margin-top:1rem;">
                    <a id="modalPdfLink" href="#" target="_blank" class="btn btn-outline-primary" style="border-radius:10px;font-weight:600;">
                        <i class="mdi mdi-open-in-new me-1"></i> Buka PDF di Tab Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================
     MODAL — UPLOAD LAMPIRAN (Admin Only)
     ======================================================== --}}
@if(auth()->user()->role === 'admin')
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content" style="border-radius:20px;border:none;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#6366f1,#4f46e5);padding:1.25rem 1.5rem;">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0">
                        <i class="mdi mdi-image-edit-outline me-2"></i>Upload Lampiran
                    </h5>
                    <p class="text-white mb-0" style="font-size:.78rem;opacity:.8;">Foto atau dokumen pendukung riwayat ini</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                {{-- File info chips --}}
                <div class="file-info-chips mb-3">
                    <span class="file-info-chip chip-ok"><i class="mdi mdi-image-outline"></i> JPG, PNG, WebP</span>
                    <span class="file-info-chip chip-ok"><i class="mdi mdi-file-pdf-box"></i> PDF</span>
                    <span class="file-info-chip chip-warn"><i class="mdi mdi-weight"></i> Maks 5 MB</span>
                </div>

                {{-- Existing attachment preview --}}
                <div id="existingAttachmentWrap" style="display:none;margin-bottom:1rem;">
                    <p style="font-size:.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">
                        Lampiran Saat Ini
                    </p>
                    <div style="border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;">
                        <img id="existingAttachmentImg" src="" class="img-fluid" style="max-height:120px;width:100%;object-fit:contain;background:#f8f9fa;">
                        <iframe id="existingAttachmentPdf" src="" style="width:100%;height:150px;border:none;display:none;background:#f8f9fa;"></iframe>
                    </div>
                    <div id="existingAttachmentFallback" style="display:none;text-align:center;margin-top:6px;">
                        <a id="existingAttachmentLink" href="#" target="_blank" style="font-size:0.85rem;font-weight:600;text-decoration:none;"><i class="mdi mdi-open-in-new"></i> Buka File Asli</a>
                    </div>
                </div>

                {{-- Upload form — action diset oleh JS via openUploadModal() --}}
                <form id="uploadAttachmentForm"
                      action="#"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Drop zone --}}
                    <div class="upload-dropzone" id="uploadDropzone">
                        <input type="file" name="attachment" id="uploadFileInput"
                               accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <i class="mdi mdi-cloud-upload-outline" style="font-size:2.5rem;color:#a5b4fc;display:block;margin-bottom:.5rem;"></i>
                        <p style="font-weight:700;color:#6366f1;margin:.25rem 0 0;font-size:.9rem;">Klik atau drag & drop file di sini</p>
                        <p style="font-size:.78rem;color:#9ca3af;margin:.3rem 0 0;">JPG · PNG · WebP · PDF — maks 5 MB</p>
                    </div>

                    {{-- Image preview --}}
                    <div class="upload-preview" id="uploadImgPreview">
                        <img id="uploadPreviewImg" src="" alt="Preview">
                        <div style="position:absolute;top:8px;right:8px;">
                            <button type="button" onclick="clearUploadPreview()"
                                    style="width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,.5);border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                <i class="mdi mdi-close" style="font-size:.85rem;"></i>
                            </button>
                        </div>
                    </div>

                    {{-- PDF preview --}}
                    <div class="upload-preview-pdf" id="uploadPdfPreview" style="display:none;flex-direction:column;gap:.5rem;">
                        <div style="display:flex;align-items:center;gap:1rem;">
                            <i class="mdi mdi-file-pdf-box" style="font-size:2.5rem;color:#ef4444;flex-shrink:0;"></i>
                            <div style="flex:1;">
                                <p id="uploadPdfName" style="font-weight:700;color:#1b2620;margin:0 0 2px;font-size:.9rem;"></p>
                                <p id="uploadPdfSize" style="font-size:.78rem;color:#9ca3af;margin:0;"></p>
                            </div>
                            <button type="button" onclick="clearUploadPreview()"
                                    style="width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,.1);border:none;color:#4b5563;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                <i class="mdi mdi-close" style="font-size:.85rem;"></i>
                            </button>
                        </div>
                        <iframe id="uploadPdfIframe" src="" style="width:100%;height:200px;border:none;border-radius:8px;background:#fff;border:1px solid #e2e8f0;"></iframe>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-light flex-1" data-bs-dismiss="modal"
                                style="flex:1;border-radius:10px;font-weight:600;font-size:.875rem;">
                            Batal
                        </button>
                        <button type="submit" id="uploadSubmitBtn" class="btn" disabled
                                style="flex:2;border-radius:10px;font-weight:700;font-size:.875rem;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;box-shadow:0 4px 14px rgba(99,102,241,.35);">
                            <i class="mdi mdi-upload me-1"></i> Upload Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Modal image handler ── */
    var attachmentModal = document.getElementById('attachmentModal');
    if (attachmentModal) {
        attachmentModal.addEventListener('show.bs.modal', function (e) {
            var src = e.relatedTarget.getAttribute('data-src');
            var isPdf = e.relatedTarget.getAttribute('data-is-pdf') === 'true';
            var img = document.getElementById('modalImageSrc');
            var pdf = document.getElementById('modalPdfSrc');
            var fallback = document.getElementById('modalPdfFallback');
            var link = document.getElementById('modalPdfLink');
            
            if (src && isPdf) {
                img.style.display = 'none';
                pdf.src = src;
                pdf.style.display = 'block';
                fallback.style.display = 'block';
                link.href = src;
            } else {
                pdf.style.display = 'none';
                fallback.style.display = 'none';
                img.src = src;
                img.style.display = 'block';
            }
        });
        attachmentModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalImageSrc').src = '';
            document.getElementById('modalPdfSrc').src = '';
            document.getElementById('modalImageSrc').style.display = 'none';
            document.getElementById('modalPdfSrc').style.display = 'none';
            document.getElementById('modalPdfFallback').style.display = 'none';
        });
    }

    /* ── Employee Search UI ── */
    var searchInput   = document.getElementById('employeeSearchInput');
    var dropdown      = document.getElementById('employeeDropdown');
    var hiddenSelect  = document.getElementById('hiddenUserSelect');
    var switchForm    = document.getElementById('switchUserForm');
    var selectedName  = document.getElementById('selectedName');
    var selectedAvatar= document.getElementById('selectedAvatar');

    if (!searchInput) return; // not admin/leader, skip

    // Build employees list from hidden select
    var employees = [];
    Array.from(hiddenSelect.options).forEach(function (opt) {
        employees.push({
            id      : opt.value,
            name    : opt.text.trim(),
            selected: opt.selected,
        });
    });

    // Determine current selected label (from hidden select)
    var currentSelected = employees.find(e => e.selected) || employees[0];

    function getInitials(name) {
        var parts = name.trim().split(' ');
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function renderDropdown(query) {
        var q = query.toLowerCase().trim();
        var filtered = employees.filter(function (e) {
            return e.name.toLowerCase().includes(q);
        });

        dropdown.innerHTML = '';
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="no-results-msg"><i class="mdi mdi-account-search" style="font-size:1.5rem;display:block;margin-bottom:6px;"></i>Tidak ditemukan "' + query + '"</div>';
            dropdown.classList.add('show');
            return;
        }

        filtered.forEach(function (emp) {
            var item = document.createElement('div');
            item.className = 'employee-dropdown-item' + (emp.selected ? ' active-item' : '');

            var isFirst = (emp.id === employees[0].id); // "Saya Sendiri"
            var avatarClass = isFirst ? 'employee-avatar self-avatar' : 'employee-avatar';
            var displayName = isFirst ? 'Saya Sendiri' : emp.name;

            // Highlight matched text — only when query is not empty
            var highlighted;
            if (q.length > 0) {
                var re = new RegExp('(' + q.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&') + ')', 'gi');
                highlighted = displayName.replace(re, '<mark style="background:rgba(13,110,253,.15);color:#0d6efd;border-radius:3px;padding:0 2px;">$1</mark>');
            } else {
                highlighted = displayName;
            }

            item.innerHTML =
                '<div class="' + avatarClass + '">' + getInitials(isFirst ? 'Saya Sendiri' : emp.name) + '</div>' +
                '<div>' +
                    '<div class="employee-name">' + highlighted + '</div>' +
                '</div>';

            item.addEventListener('click', function () {
                selectEmployee(emp);
            });
            dropdown.appendChild(item);
        });
        dropdown.classList.add('show');
    }

    function selectEmployee(emp) {
        currentSelected = emp;
        hiddenSelect.value = emp.id;

        var isFirst = (emp.id === employees[0].id);
        var displayName = isFirst ? 'Saya Sendiri' : emp.name;

        searchInput.value = '';
        dropdown.classList.remove('show');

        if (selectedName)  selectedName.textContent = displayName;
        if (selectedAvatar) selectedAvatar.textContent = getInitials(displayName);

        // Submit form to switch user
        switchForm.submit();
    }

    searchInput.addEventListener('focus', function () {
        renderDropdown(this.value);
    });

    searchInput.addEventListener('input', function () {
        renderDropdown(this.value);
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!document.getElementById('employeeSearchWrap').contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function (e) {
        var items = dropdown.querySelectorAll('.employee-dropdown-item');
        var highlighted = dropdown.querySelector('.highlighted');
        var idx = Array.from(items).indexOf(highlighted);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (highlighted) highlighted.classList.remove('highlighted');
            var next = items[idx + 1] || items[0];
            if (next) next.classList.add('highlighted');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (highlighted) highlighted.classList.remove('highlighted');
            var prev = items[idx - 1] || items[items.length - 1];
            if (prev) prev.classList.add('highlighted');
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlighted) highlighted.click();
        } else if (e.key === 'Escape') {
            dropdown.classList.remove('show');
        }
    });
});
</script>

@if(auth()->user()->role === 'admin')
<script>
/* ── Upload Lampiran Modal ── */
function openUploadModal(historyId, existingUrl, isPdf) {
    // Set form action ke route attachment yang benar
    var form = document.getElementById('uploadAttachmentForm');
    form.action = '/employment-history/' + historyId + '/attachment';

    // Show existing attachment if any
    var existingWrap = document.getElementById('existingAttachmentWrap');
    var existingImg  = document.getElementById('existingAttachmentImg');
    var existingPdf  = document.getElementById('existingAttachmentPdf');
    var existingFallback = document.getElementById('existingAttachmentFallback');
    var existingLink = document.getElementById('existingAttachmentLink');
    if (existingUrl) {
        existingWrap.style.display = 'block';
        if (isPdf || existingUrl.toLowerCase().endsWith('.pdf')) {
            existingImg.style.display = 'none';
            existingPdf.src = existingUrl;
            existingPdf.style.display = 'block';
            existingFallback.style.display = 'block';
            existingLink.href = existingUrl;
        } else {
            existingPdf.style.display = 'none';
            existingFallback.style.display = 'none';
            existingImg.src = existingUrl;
            existingImg.style.display = 'block';
        }
    } else {
        existingWrap.style.display = 'none';
        existingImg.src = '';
        existingPdf.src = '';
    }

    // Reset upload state
    clearUploadPreview();

    var modal = new bootstrap.Modal(document.getElementById('uploadModal'));
    modal.show();
}

function clearUploadPreview() {
    document.getElementById('uploadFileInput').value = '';
    document.getElementById('uploadImgPreview').style.display = 'none';
    document.getElementById('uploadPreviewImg').src = '';
    document.getElementById('uploadPdfPreview').style.display = 'none';
    document.getElementById('uploadPdfIframe').src = '';
    document.getElementById('uploadSubmitBtn').disabled = true;
}

document.addEventListener('DOMContentLoaded', function () {
    var fileInput  = document.getElementById('uploadFileInput');
    var dropzone   = document.getElementById('uploadDropzone');
    var submitBtn  = document.getElementById('uploadSubmitBtn');

    if (!fileInput) return;

    function handleFile(file) {
        if (!file) return;

        // Validate size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File terlalu besar! Maksimal 5 MB.');
            clearUploadPreview();
            return;
        }

        // Validate type
        var allowed = ['image/jpeg','image/png','image/webp','application/pdf'];
        if (!allowed.includes(file.type)) {
            alert('Format tidak didukung. Gunakan JPG, PNG, WebP, atau PDF.');
            clearUploadPreview();
            return;
        }

        if (file.type === 'application/pdf') {
            document.getElementById('uploadImgPreview').style.display = 'none';
            var pdfPreview = document.getElementById('uploadPdfPreview');
            pdfPreview.style.display = 'flex';
            document.getElementById('uploadPdfName').textContent = file.name;
            document.getElementById('uploadPdfSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
            // Preview PDF via Object URL
            document.getElementById('uploadPdfIframe').src = URL.createObjectURL(file);
        } else {
            document.getElementById('uploadPdfPreview').style.display = 'none';
            document.getElementById('uploadPdfIframe').src = '';
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('uploadPreviewImg').src = e.target.result;
                document.getElementById('uploadImgPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        submitBtn.disabled = false;
    }

    fileInput.addEventListener('change', function () {
        handleFile(this.files[0]);
    });

    // Drag & Drop
    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    dropzone.addEventListener('dragleave', function () {
        this.classList.remove('dragover');
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('dragover');
        var file = e.dataTransfer.files[0];
        if (file) {
            // Set to input
            var dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            handleFile(file);
        }
    });
});
</script>
@endif
@endpush