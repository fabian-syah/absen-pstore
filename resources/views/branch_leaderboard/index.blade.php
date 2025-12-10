@extends('layout.master')

@section('title')
    Top Absensi Cabang
@endsection

@section('heading')
    <h3 class="fw-bold mb-0">Pilih Cabang</h3>
    <p class="text-muted small">Lihat peringkat kerajinan karyawan per cabang</p>
@endsection

@section('content')
<div class="row">
    @forelse($branches as $branch)
    <div class="col-md-4 col-sm-6 grid-margin stretch-card">
        <div class="card card-action hover-float h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-rounded bg-light text-primary p-3 rounded-circle me-3">
                        <i class="mdi mdi-office-building fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">{{ $branch->name }}</h5>
                        <p class="text-muted small mb-0">
                            <i class="mdi mdi-account-group me-1"></i>{{ $branch->total_employees }} Karyawan
                        </p>
                    </div>
                </div>
                
                <hr class="my-3 opacity-25">

                <a href="{{ route('branch-leaderboard.show', $branch->id) }}" class="btn btn-primary btn-sm w-100 shadow-sm">
                    <i class="mdi mdi-trophy-outline me-2"></i>Lihat Top Absen
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-warning text-center">
            <i class="mdi mdi-alert-circle-outline me-2"></i> Tidak ada cabang yang tersedia.
        </div>
    </div>
    @endforelse
</div>
@endsection

@push('styles')
<style>
    .hover-float { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .hover-float:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .icon-rounded { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; }
</style>
@endpush