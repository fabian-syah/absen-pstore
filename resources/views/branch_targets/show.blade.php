@extends('layout.master')

@section('title', 'Detail Target Cabang')
@section('heading', $branch->name)

@section('content')

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <a href="{{ route('branch-targets.index') }}" class="btn btn-light bg-white border shadow-sm btn-sm mb-2 rounded-3 fw-bold">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <h3 class="fw-bold text-dark mb-1">{{ $branch->name }}</h3>
        <p class="text-muted mb-0">
            <i class="mdi mdi-map-marker me-1"></i> {{ $branch->address ?? 'Lokasi belum diatur' }}
        </p>
    </div>
    
    {{-- Tombol Buat (Hanya Leader yg bisa buat target cabang di sini, Admin juga bisa) --}}
    <div>
         <a href="{{ route('job-targets.create') }}" class="btn btn-primary btn-lg shadow-sm rounded-4 px-4 fw-bold">
            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Target
        </a>
    </div>
</div>

{{-- SECTION 1: TARGET CABANG --}}
<div class="card card-rounded shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                <i class="mdi mdi-office-building text-primary mdi-24px"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">🏢 Target & Pencapaian Cabang</h5>
                <small class="text-muted">Fokus utama tim di cabang {{ $branch->name }}</small>
            </div>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        {{-- MENGGUNAKAN PARTIAL YANG SAMA DENGAN INDEX UTAMA --}}
        @include('job_targets.partials.period_tabs', ['idPrefix' => 'branch', 'dataCollection' => $teamData])
    </div>
</div>

{{-- SECTION 2: TARGET PRIBADI (SELURUH KARYAWAN CABANG INI) --}}
<div class="card card-rounded shadow-sm border-0 mb-5">
    <div class="card-header bg-gradient-info text-white border-bottom py-3">
        <div class="d-flex align-items-center">
            <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3">
                <i class="mdi mdi-account-group text-white mdi-24px"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-white">👤 Target Pribadi Karyawan</h5>
                <small class="text-white opacity-75">Monitoring target individu seluruh anggota tim</small>
            </div>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        {{-- MENGGUNAKAN PARTIAL YANG SAMA --}}
        @include('job_targets.partials.period_tabs', ['idPrefix' => 'personal', 'dataCollection' => $personalData])
    </div>
</div>

{{-- MODAL UPDATE (INCLUDE PARTIAL) --}}
@include('job_targets.partials.modal_update')

{{-- CSS KHUSUS (COPY DARI INDEX UTAMA AGAR TAMPILAN SAMA) --}}
<style>
    .card-rounded { border-radius: 16px; overflow: hidden; }
    .bg-gradient-info { background: linear-gradient(45deg, #198ae3, #4b49ac); }
    
    .star-badge-3 { background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); color: #000; box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); border: 1px solid #d4af37; }
    .star-badge-2 { background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%); color: #333; border: 1px solid #b0b0b0; }
    .star-badge-1 { background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
    .star-animation { animation: glow 2s infinite; }
    @keyframes glow { 0% { box-shadow: 0 0 5px #FFD700; } 50% { box-shadow: 0 0 15px #FFD700; } 100% { box-shadow: 0 0 5px #FFD700; } }

    .nav-pills-custom .nav-link { background: #f8f9fa; color: #6c757d; border: 1px solid #e9ecef; margin-right: 5px; margin-bottom: 5px; transition: all 0.3s; }
    .nav-pills-custom .nav-link.active { background: #4b49ac; color: #fff; border-color: #4b49ac; box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2); }
</style>

{{-- JAVASCRIPT FILTER (COPY DARI INDEX UTAMA) --}}
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

        let tables = dataContainer.querySelectorAll('tbody');
        tables.forEach(tbody => {
            let visibleRows = tbody.querySelectorAll('.filterable-item:not(.d-none)');
            let msgRow = tbody.querySelector('.no-data-message');
            if(msgRow) visibleRows.length === 0 ? msgRow.classList.remove('d-none') : msgRow.classList.add('d-none');
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