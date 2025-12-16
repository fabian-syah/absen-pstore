@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title mb-0">Pilih Cabang Pembayaran</h4>
        </div>

        {{-- Search Cabang --}}
        <form action="{{ route('branch-salary.index') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari nama cabang..." value="{{ $search }}">
                <button class="btn btn-primary text-white" type="submit">Cari</button>
            </div>
        </form>

        <div class="row">
            @forelse($branches as $branch)
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card shadow-sm border-0 hover-shadow cursor-pointer" onclick="window.location='{{ route('branch-salary.show', $branch->id) }}'" style="cursor: pointer; transition: 0.3s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary p-3 rounded-circle text-white me-3">
                                    <i class="mdi mdi-office-building fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">{{ $branch->name }}</h5>
                                    <small class="text-muted"><i class="mdi mdi-map-marker"></i> {{ Str::limit($branch->address, 30) }}</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-outline-primary">
                                    <i class="mdi mdi-account-group"></i> {{ $branch->users_count }} Karyawan
                                </span>
                                <a href="{{ route('branch-salary.show', $branch->id) }}" class="btn btn-sm btn-primary text-white">
                                    Lihat Detail <i class="mdi mdi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="mdi mdi-office-building-remove text-muted" style="font-size: 5rem;"></i>
                    <p class="mt-3 text-muted">Cabang tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection