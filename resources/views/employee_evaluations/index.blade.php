@extends('layout.master')

@section('title', 'Rapor Karyawan')
@section('heading', 'Evaluasi Performa Karyawan')

@push('styles')
<style>
    .card-employee {
        border-radius: 16px;
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
        overflow: hidden;
    }
    .card-employee:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        border-color: #c7d2fe;
    }
    .avatar-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e2e8f0;
        margin: 0 auto;
    }
    .avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 16px;">
            <div class="card-body">
                <form action="{{ route('employee-evaluations.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">Pilih Cabang</label>
                        <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Cabang --</option>
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">Cari Nama</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Masukkan nama..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-4"><i class="mdi mdi-magnify"></i> Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @forelse ($users as $employee)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card card-employee shadow-sm h-100">
                <div class="card-body text-center d-flex flex-column">
                    <div class="avatar-wrapper mb-3">
                        @if ($employee->profile_photo_path)
                            <img src="{{ asset('storage/' . $employee->profile_photo_path) }}" alt="Foto">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=random" alt="Foto">
                        @endif
                    </div>
                    <h6 class="fw-bold mb-1 text-truncate" title="{{ $employee->name }}">{{ $employee->name }}</h6>
                    <p class="text-muted small mb-2 text-truncate" title="{{ $employee->division->name ?? 'Belum ada divisi' }}">
                        {{ $employee->division->name ?? 'Belum ada divisi' }}
                    </p>
                    <span class="badge bg-light text-secondary border mx-auto mb-3">
                        <i class="mdi mdi-storefront-outline me-1"></i>
                        {{ $employee->branch->name ?? 'Semua Cabang' }}
                    </span>
                    
                    <div class="mt-auto">
                        <a href="{{ route('employee-evaluations.form', $employee->id) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold">
                            <i class="mdi mdi-pencil-box-outline me-1"></i> Isi Rapor Bulanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card p-5 text-center border-0 shadow-sm">
                <div class="text-muted">
                    <i class="mdi mdi-account-search" style="font-size: 3rem;"></i>
                    <p class="mt-2">Tidak ada karyawan yang ditemukan.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $users->links() }}
</div>
@endsection
