@extends('layout.master')

@section('title', 'Cabang Saya')
@section('heading', 'Monitoring Wilayah')

@push('styles')
    <style>
        .branch-section-title {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
            color: #1e293b;
            font-weight: 700;
        }

        .branch-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 24px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            border-radius: 4px;
        }

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
        }

        /* Styling untuk Statistik dalam Card */
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            min-width: 30px;
            text-align: center;
        }

        .bg-soft-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .bg-soft-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .bg-soft-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .branch-footer {
            margin-top: auto;
            /* Push footer to bottom */
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="branch-section-title">Cabang Kelolaan ({{ count($controlledBranches) }})</h4>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($controlledBranches as $branch)
            <div class="col-xl-3 col-md-6">
                <div class="branch-card-item p-4">
                    {{-- Header Card --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="branch-icon-box">
                            <i class="mdi mdi-storefront-outline"></i>
                        </div>
                        <span class="badge bg-light text-secondary border">
                            ID: {{ $branch->id }}
                        </span>
                    </div>

                    {{-- Nama Cabang --}}
                    <h5 class="fw-bold text-dark mb-1">{{ Str::limit($branch->name, 20) }}</h5>
                    <p class="text-muted small mb-3">
                        <i class="mdi mdi-map-marker-outline me-1"></i>
                        {{ Str::limit($branch->address ?? 'Alamat belum diatur', 40) }}
                    </p>

                    {{-- Statistik Hari Ini --}}
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="mdi mdi-account-check text-success me-1"></i>Hadir</span>
                            <span class="fw-bold text-dark">{{ $branch->stats_today['present'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="mdi mdi-calendar-check text-primary me-1"></i>Libur</span>
                            <span class="fw-bold text-dark">{{ $branch->stats_today['libur'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="mdi mdi-hospital-box text-info me-1"></i>Sakit</span>
                            <span class="fw-bold text-dark">{{ $branch->stats_today['sakit'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i
                                    class="mdi mdi-file-document text-warning me-1"></i>Izin/Cuti</span>
                            <span class="fw-bold text-dark">{{ $branch->stats_today['izin'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class="mdi mdi-account-alert text-danger me-1"></i>Alpha</span>
                            <span class="fw-bold text-dark">{{ $branch->stats_today['alpha'] }}</span>
                        </div>
                    </div>

                    {{-- Footer Card --}}
                    <div class="branch-footer">
                        <div class="small text-muted">
                            Total: <strong>{{ $branch->users_count }}</strong> User
                        </div>
                        <a href="{{ route('team.branch.detail', $branch->id) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Detail <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center border-0 shadow-sm">
                    <div class="text-muted">
                        <i class="mdi mdi-office-building-off" style="font-size: 3rem;"></i>
                        <p class="mt-2">Anda tidak memiliki kontrol cabang khusus.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection