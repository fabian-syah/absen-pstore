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
        overflow: hidden;
        height: 100%;
    }

    .branch-card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        border-color: #c7d2fe;
    }

    .branch-icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 24px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .branch-card-item:hover .branch-icon-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .branch-stat {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .branch-stat strong {
        display: block;
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 0.1rem;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="branch-section-title">Cabang Kelolaan ({{ count($controlledBranches) }})</h4>
        </div>
    </div>

    <div class="row">
        @forelse ($controlledBranches as $branch)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="branch-card-item p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="branch-icon-box">
                            <i class="mdi mdi-storefront-outline"></i>
                        </div>
                        <span class="badge bg-light text-secondary border">
                            ID: {{ $branch->id }}
                        </span>
                    </div>

                    <h5 class="fw-bold mb-2">{{ $branch->name }}</h5>
                    <p class="text-muted small mb-3" style="min-height: 40px;">
                        <i class="mdi mdi-map-marker-outline me-1"></i>
                        {{ Str::limit($branch->address ?? 'Alamat belum diatur', 50) }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="branch-stat">
                            <strong>{{ $branch->users_count }}</strong>
                            Karyawan
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