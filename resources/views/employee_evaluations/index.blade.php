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

<div class="row g-4">
    @forelse ($users as $employee)
        @php
            $latestEval = \App\Models\EmployeeEvaluation::where('user_id', $employee->id)->orderByDesc('year')->orderByDesc('month')->first();
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
                        <span class="text-muted small"><i class="mdi mdi-calendar-check text-info me-1"></i>Periode Terakhir</span>
                        <span class="fw-bold text-dark">{{ $latestEval ? \Carbon\Carbon::create()->month($latestEval->month)->translatedFormat('M') . ' ' . $latestEval->year : 'Belum Ada' }}</span>
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
                    <a href="{{ route('employee-evaluations.form', $employee->id) }}"
                        class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Isi Rapor <i class="mdi mdi-arrow-right ms-1"></i>
                    </a>
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
