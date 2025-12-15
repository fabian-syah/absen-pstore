@extends('layout.master')

@section('title', 'Detail Target Cabang')
@section('heading', $branch->name)

@section('content')

{{-- HEADER & NAVIGATION --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
    <div>
        <a href="{{ route('branch-targets.index') }}" class="btn btn-light bg-white border shadow-sm btn-sm mb-2 rounded-3 fw-bold text-muted hover-scale">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar Cabang
        </a>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center">
            <i class="mdi mdi-storefront text-primary me-2"></i> {{ $branch->name }}
        </h3>
        <p class="text-muted mb-0 d-flex align-items-center">
            <i class="mdi mdi-map-marker-radius text-danger me-1"></i> 
            {{ $branch->address ?? 'Lokasi belum diatur' }}
        </p>
    </div>
    
    {{-- Tombol Tambah Target Global (HANYA LEADER) --}}
    @if(auth()->user()->role == 'leader')
    <div>
         <a href="{{ route('job-targets.create', ['branch_id' => $branch->id, 'type_preselect' => 'team']) }}" class="btn btn-dark btn-lg shadow-lg rounded-4 px-4 fw-bold hover-scale w-100 w-md-auto">
            <i class="mdi mdi-target me-1"></i> Buat Target Global Tim
        </a>
    </div>
    @endif
</div>

{{-- SECTION 1: TARGET GLOBAL CABANG --}}
<div class="card card-rounded shadow-sm border-0 mb-5">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                <i class="mdi mdi-office-building text-primary mdi-24px"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">🏢 Target Global Cabang</h5>
                <small class="text-muted">Target utama yang menjadi tanggung jawab satu tim penuh.</small>
            </div>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        {{-- Allow edit/update status hanya jika Leader (atau Admin jika mau memantau), tapi berdasarkan request, admin hanya diri sendiri. Jadi Leader full control. --}}
        @php
            $isLeader = auth()->user()->role == 'leader';
        @endphp
        @include('job_targets.partials.period_tabs', [
            'idPrefix' => 'branch', 
            'dataCollection' => $teamData, 
            'allow_edit_detail' => $isLeader,
            'allow_update_status' => $isLeader
        ])
    </div>
</div>

{{-- SECTION 2: DAFTAR ANGGOTA TIM --}}
<div class="card card-rounded shadow-sm border-0 mb-5">
    <div class="card-header bg-gradient-info text-white border-bottom py-3">
        <div class="d-flex align-items-center">
            <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3">
                <i class="mdi mdi-account-group text-white mdi-24px"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-white">👥 Daftar Anggota Tim</h5>
                <small class="text-white opacity-75">Kelola target personal untuk setiap karyawan.</small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Nama Karyawan</th>
                        <th>Posisi</th>
                        <th class="text-center">Target Aktif</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchMembers as $member)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">
                                        {{ substr($member->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">{{ $member->name }}</h6>
                                        <small class="text-muted">{{ $member->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $member->role }}</span></td>
                            <td class="text-center">
                                @if($member->active_targets_count > 0)
                                    <span class="badge bg-warning text-dark rounded-pill px-3">{{ $member->active_targets_count }} Pending</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                {{-- BUTTON BERI TARGET: HANYA LEADER --}}
                                @if(auth()->user()->role == 'leader')
                                    <a href="{{ route('job-targets.create', ['assign_user_id' => $member->id, 'branch_id' => $branch->id]) }}" 
                                       class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> Beri Target
                                    </a>
                                @else
                                    <span class="text-muted small fst-italic">View Only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada anggota tim.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL UPDATE & STYLE SAMA SEPERTI SEBELUMNYA --}}
@include('job_targets.partials.modal_update')

<style>
    .card-rounded { border-radius: 16px; overflow: hidden; }
    .bg-gradient-info { background: linear-gradient(45deg, #198ae3, #4b49ac); }
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.02); }
    
    /* Bintang & Tab Style */
    .star-badge-3 { background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); color: #000; box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); border: 1px solid #d4af37; }
    .star-badge-2 { background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%); color: #333; border: 1px solid #b0b0b0; }
    .star-badge-1 { background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
    .star-animation { animation: glow 2s infinite; }
    @keyframes glow { 0% { box-shadow: 0 0 5px #FFD700; } 50% { box-shadow: 0 0 15px #FFD700; } 100% { box-shadow: 0 0 5px #FFD700; } }
    .nav-pills-custom .nav-link { background: #f8f9fa; color: #6c757d; border: 1px solid #e9ecef; margin-right: 5px; margin-bottom: 5px; transition: all 0.3s; }
    .nav-pills-custom .nav-link.active { background: #4b49ac; color: #fff; border-color: #4b49ac; box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2); }
</style>

{{-- JAVASCRIPT FILTER --}}
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