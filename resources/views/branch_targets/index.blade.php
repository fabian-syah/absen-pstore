@extends('layout.master')

@section('title', 'Monitoring Target Cabang')

@section('content')

{{-- 1. HEADER & GLOBAL SUMMARY --}}
<div class="row align-items-center mb-5">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark display-6 mb-2">Monitoring Target Cabang</h3>
        <p class="text-muted fs-5 mb-0">
            Overview performa dan pencapaian target seluruh cabang.
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <div class="d-inline-flex align-items-center bg-white p-3 rounded-4 shadow-sm border">
            <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                <i class="mdi mdi-office-building-marker text-primary mdi-24px"></i>
            </div>
            <div class="text-start">
                <h4 class="fw-bold mb-0 text-dark">{{ $branches->count() }}</h4>
                <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 10px;">Total Cabang</small>
            </div>
        </div>
    </div>
</div>

{{-- 2. GRID CABANG --}}
<div class="row g-4">
    @foreach($branches as $branch)
        <div class="col-md-6 col-lg-4 col-xl-3">
            {{-- CARD UTAMA --}}
            <a href="{{ route('branch-targets.show', $branch->id) }}" class="text-decoration-none">
                <div class="card card-branch h-100 border-0 shadow-sm position-relative overflow-hidden">
                    
                    {{-- Status Indicator (Opsional: Misal jika ada target gagal banyak, warnanya merah) --}}
                    <div class="status-indicator bg-primary"></div>

                    <div class="card-body p-4">
                        {{-- Header Card --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-light text-primary rounded-3">
                                <i class="mdi mdi-storefront-outline mdi-24px"></i>
                            </div>
                            <span class="badge bg-light text-secondary border fw-normal px-2 py-1">ID: {{ $branch->id }}</span>
                        </div>

                        {{-- Nama & Alamat --}}
                        <h5 class="fw-bold text-dark mb-1 text-truncate" title="{{ $branch->name }}">{{ $branch->name }}</h5>
                        <p class="text-muted small mb-4 text-truncate">
                            <i class="mdi mdi-map-marker-radius-outline me-1"></i> {{ $branch->address ?? 'Alamat belum diatur' }}
                        </p>

                        {{-- STATISTIK TARGET (GRID LAYOUT) --}}
                        <div class="bg-light rounded-3 p-3 mb-3 border border-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-uppercase text-muted ls-1" style="font-size: 10px;">Target Tim Aktif</span>
                                <i class="mdi mdi-target text-muted"></i>
                            </div>
                            
                            <div class="row g-2 text-center">
                                <div class="col-4 border-end">
                                    <div class="fw-bold text-dark h6 mb-0">{{ $branch->team_daily }}</div>
                                    <div class="text-muted" style="font-size: 9px;">Harian</div>
                                </div>
                                <div class="col-4 border-end">
                                    <div class="fw-bold text-dark h6 mb-0">{{ $branch->team_monthly }}</div>
                                    <div class="text-muted" style="font-size: 9px;">Bulanan</div>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-dark h6 mb-0">{{ $branch->team_yearly }}</div>
                                    <div class="text-muted" style="font-size: 9px;">Tahunan</div>
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER: PERSONAL TARGET & USER COUNT --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar-group me-2">
                                    {{-- Visualisasi User (Dummy Icon) --}}
                                    <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center border border-white" style="width: 24px; height: 24px; font-size: 10px;">
                                        <i class="mdi mdi-account"></i>
                                    </div>
                                </div>
                                <small class="text-muted fw-bold" style="font-size: 11px;">{{ $branch->total_users }} Karyawan</small>
                            </div>
                            
                            {{-- Badge Target Personal --}}
                            <div class="badge bg-primary bg-opacity-10 text-primary px-2 py-1" style="font-size: 10px;">
                                {{ $branch->personal_count }} Target Individu
                            </div>
                        </div>
                    </div>
                    
                    {{-- Hover Effect Overlay --}}
                    <div class="hover-overlay d-flex align-items-center justify-content-center">
                        <span class="btn btn-light fw-bold shadow-sm rounded-pill px-4">
                            Lihat Detail <i class="mdi mdi-arrow-right ms-1"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

{{-- CSS KHUSUS --}}
<style>
    /* Dasar Card */
    .card-branch {
        border-radius: 16px;
        transition: all 0.3s ease;
        background: #fff;
    }

    /* Icon Box */
    .icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    /* Status Indicator Bar (Kiri) */
    .status-indicator {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
    }

    /* Hover Effects */
    .card-branch:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }

    .card-branch:hover .icon-box {
        background-color: #4b49ac !important; /* Warna Primary Pstore */
        color: #fff !important;
    }

    /* Hover Overlay (Muncul tombol saat hover) */
    .hover-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(75, 73, 172, 0.85); /* Primary Color with Opacity */
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(2px);
    }

    .card-branch:hover .hover-overlay {
        opacity: 1;
    }

    .ls-1 { letter-spacing: 0.5px; }
</style>
@endsection