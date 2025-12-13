@extends('layout.master')

@section('title', 'Monitoring Target Cabang')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold text-dark mb-1">Monitoring Target Cabang</h3>
        <p class="text-muted">Pantau progres target tim dan individu di setiap cabang.</p>
    </div>
</div>

<div class="row">
    @foreach($branches as $branch)
        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
            <div class="card card-rounded shadow-sm border-0 h-100 hover-scale">
                <div class="card-body p-4">
                    {{-- Header Cabang --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                            <i class="mdi mdi-storefront text-primary mdi-24px"></i>
                        </div>
                        <span class="badge bg-light text-muted border">ID: {{ $branch->id }}</span>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $branch->name }}</h5>
                    <p class="text-muted small mb-3">
                        <i class="mdi mdi-map-marker-outline me-1"></i> {{ $branch->address ?? 'Alamat belum diatur' }}
                    </p>

                    <hr class="my-3 opacity-25">

                    {{-- STATISTIK TARGET TIM (HARIAN/BULANAN/TAHUNAN) --}}
                    <div class="mb-3">
                        <label class="small fw-bold text-uppercase text-muted mb-2 ls-1" style="font-size: 10px;">Target Cabang (Aktif)</label>
                        <div class="d-flex justify-content-between gap-2 text-center">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-2 flex-fill">
                                <h5 class="fw-bold text-danger mb-0">{{ $branch->team_daily }}</h5>
                                <small class="text-muted" style="font-size: 10px;">Harian</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-3 p-2 flex-fill">
                                <h5 class="fw-bold text-warning mb-0 text-dark">{{ $branch->team_monthly }}</h5>
                                <small class="text-muted" style="font-size: 10px;">Bulanan</small>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-3 p-2 flex-fill">
                                <h5 class="fw-bold text-success mb-0">{{ $branch->team_yearly }}</h5>
                                <small class="text-muted" style="font-size: 10px;">Tahunan</small>
                            </div>
                        </div>
                    </div>

                    {{-- STATISTIK TARGET PRIBADI (QUANTITY SAJA) --}}
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-account-group text-secondary me-2"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark small">Target Personal</span>
                                <small class="text-muted" style="font-size: 10px;">Akumulasi seluruh tim</small>
                            </div>
                        </div>
                        <span class="badge bg-white text-dark border shadow-sm">{{ $branch->personal_count }} Item</span>
                    </div>
                    
                    {{-- Footer: Total User & Tombol Detail --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Total: <strong>{{ $branch->total_users }} User</strong></small>
                        <a href="{{ route('branch-targets.show', $branch->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">
                            Detail <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
    .card-rounded { border-radius: 16px; }
    .ls-1 { letter-spacing: 1px; }
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
</style>
@endsection