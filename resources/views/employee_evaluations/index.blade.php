@extends('layout.master')

@section('title', 'Rapor Karyawan')
@section('heading', 'Evaluasi Performa Karyawan')

@push('styles')
<style>
    .branch-card-item {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .branch-card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        border-color: #c7d2fe;
    }

    .branch-icon-box {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 20px;
        overflow: hidden;
    }
    
    .branch-icon-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .branch-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('employee-evaluations.index') }}" class="btn btn-light shadow-sm rounded-circle p-2 me-3">
            <i class="mdi mdi-arrow-left fs-5"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">Evaluasi: {{ $branch->name }}</h4>
            <p class="text-muted mb-0 small">Pilih karyawan yang ingin dinilai</p>
        </div>
    </div>
    <div>
        <a href="{{ route('employee-evaluations.export-branch-pdf', ['id' => $branch->id, 'date' => request('date', now()->format('Y-m-d'))]) }}" class="btn btn-danger shadow-sm d-flex align-items-center">
            <i class="mdi mdi-file-pdf-box fs-5 me-1"></i> Print PDF Cabang
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 16px;">
            <div class="card-body">
                <form action="{{ route('employee-evaluations.branch-employees', $branch->id) }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <label class="form-label text-muted small fw-bold">Cari Nama Karyawan</label>
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

<div class="row g-4">
    @forelse ($users as $employee)
        @php
            $latestEval = \App\Models\EmployeeEvaluation::where('user_id', $employee->id)->orderByDesc('evaluation_date')->first();
        @endphp
        <div class="col-xl-3 col-md-6">
            <div class="branch-card-item p-4">
                {{-- Header Card --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="branch-icon-box">
                        @if ($employee->profile_photo_path)
                            <img src="{{ asset('storage/' . $employee->profile_photo_path) }}" alt="Foto">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=random" alt="Foto">
                        @endif
                    </div>
                    <span class="badge bg-light text-secondary border">
                        ID: {{ $employee->id }}
                    </span>
                </div>

                {{-- Nama --}}
                <h5 class="fw-bold text-dark mb-1">{{ Str::limit($employee->name, 20) }}</h5>
                <p class="text-muted small mb-3">
                    <i class="mdi mdi-office-building-marker-outline me-1"></i>
                    {{ Str::limit($employee->branch->name ?? 'Belum ada cabang', 40) }}
                </p>

                {{-- Statistik Terakhir --}}
                <div class="bg-light rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small"><i class="mdi mdi-star text-warning me-1"></i>Skor Rata-rata</span>
                        <span class="fw-bold text-dark">{{ $latestEval ? number_format($latestEval->average_score, 1) : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small"><i class="mdi mdi-medal text-primary me-1"></i>Grade</span>
                        <span class="fw-bold {{ $latestEval && $latestEval->grade == 'A+' ? 'text-success' : 'text-dark' }}">{{ $latestEval ? $latestEval->grade : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small"><i class="mdi mdi-calendar-check text-info me-1"></i>Tanggal Terakhir</span>
                        <span class="fw-bold text-dark">{{ $latestEval && $latestEval->evaluation_date ? \Carbon\Carbon::parse($latestEval->evaluation_date)->translatedFormat('d M Y') : 'Belum Ada' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="mdi mdi-account-edit text-success me-1"></i>Dinilai Oleh</span>
                        <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $latestEval && $latestEval->assessor ? Str::limit($latestEval->assessor->name, 12) : '-' }}</span>
                    </div>
                </div>

                {{-- Footer Card --}}
                <div class="branch-footer">
                    <div class="small text-muted">
                        Role: <strong class="text-capitalize">{{ str_replace('_', ' ', $employee->role) }}</strong>
                    </div>
                    <div class="d-flex gap-2">
                        @if($latestEval)
                        <a href="{{ route('employee-evaluations.export-pdf', ['user_id' => $employee->id, 'date' => $latestEval->evaluation_date ? \Carbon\Carbon::parse($latestEval->evaluation_date)->format('Y-m-d') : now()->format('Y-m-d')]) }}"
                            class="btn btn-sm btn-outline-danger rounded-pill px-2 d-flex align-items-center justify-content-center" title="Download PDF Rapor" style="width: 32px; height: 32px;">
                            <i class="mdi mdi-file-pdf-box fs-5"></i>
                        </a>
                        @endif
                        <a href="{{ route('employee-evaluations.form', $employee->id) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3 d-flex align-items-center">
                            Isi Rapor <i class="mdi mdi-arrow-right ms-1"></i>
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
