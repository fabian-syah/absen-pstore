@extends('layout.master')

@section('content')
<div class="content-wrapper">
    
    {{-- STATS DASHBOARD --}}
    <div class="row mb-4">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-primary text-white card-stats shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1 opacity-75">Total Cabang</p>
                            <h2 class="fw-bold mb-0">{{ $branches->count() }}</h2>
                        </div>
                        <div class="icon-box bg-white text-primary rounded-circle p-3">
                            <i class="mdi mdi-domain fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-info text-white card-stats shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1 opacity-75">Karyawan Aktif</p>
                            <h2 class="fw-bold mb-0">{{ $globalTotalEmployees }}</h2>
                        </div>
                        <div class="icon-box bg-white text-info rounded-circle p-3">
                            <i class="mdi mdi-account-group fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-success text-white card-stats shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1 opacity-75">Total Gaji ({{ date('F') }})</p>
                            <h3 class="fw-bold mb-0">Rp {{ number_format($globalTotalSalary, 0, ',', '.') }}</h3>
                        </div>
                        <div class="icon-box bg-white text-success rounded-circle p-3">
                            <i class="mdi mdi-cash-multiple fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH BAR --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <form action="{{ route('branch-salary.index') }}" method="GET">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-0"><i class="mdi mdi-magnify fs-4"></i></span>
                    <input type="text" name="search" class="form-control border-0 py-3" placeholder="Cari cabang..." value="{{ $search }}">
                    <button class="btn btn-dark" type="submit">Cari Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- GRID CABANG --}}
    <div class="row">
        @forelse($branches as $branch)
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card border-0 shadow-sm card-branch h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start mb-3">
                            <div class="branch-icon bg-light text-primary rounded p-3 me-3">
                                <i class="mdi mdi-office-building fs-2"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $branch->name }}</h5>
                                <p class="text-muted small mb-0"><i class="mdi mdi-map-marker"></i> {{ Str::limit($branch->address, 35) }}</p>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row mt-2 mb-3">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block">Karyawan</small>
                                <span class="fw-bold text-dark fs-5">{{ $branch->employee_count }}</span>
                            </div>
                            <div class="col-6 ps-4">
                                <small class="text-muted d-block">Payroll Bulan Ini</small>
                                <span class="fw-bold {{ $branch->total_salary_expense > 0 ? 'text-success' : 'text-secondary' }}">
                                    Rp {{ number_format($branch->total_salary_expense, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('branch-salary.show', $branch->id) }}" class="btn btn-outline-primary btn-lg w-100 mt-auto fw-bold">
                            Kelola Gaji <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h5 class="text-muted">Data Cabang Tidak Ditemukan</h5>
            </div>
        @endforelse
    </div>
</div>

<style>
    .card-stats { border-radius: 15px; transition: transform 0.2s; }
    .card-stats:hover { transform: translateY(-3px); }
    .card-branch { border-radius: 12px; transition: all 0.3s ease; border: 1px solid #f0f0f0 !important; }
    .card-branch:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; border-color: #bfa15f !important; }
    .branch-icon { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; }
</style>
@endsection