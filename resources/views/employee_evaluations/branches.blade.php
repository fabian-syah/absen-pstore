@extends('layout.master')

@section('title', 'Rapor Karyawan')
@section('heading', 'Evaluasi Performa Karyawan')

@push('styles')
    <style>
        .branch-section-title {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
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

        .stat-card-widget {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
            padding: 1.25rem;
            height: 100%;
            transition: all 0.25s ease;
        }

        .stat-card-widget:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
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
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 20px;
        }

        .branch-icon-box.pusat {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
        }

        .branch-icon-box.cabang {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            color: #0284c7;
        }

        .evaluation-progress-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 0.75rem;
        }

        .branch-footer {
            margin-top: auto;
            padding-top: 0.9rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-pills-custom .nav-link {
            color: #475569;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .nav-pills-custom .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .nav-pills-custom .nav-link:hover:not(.active) {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
    </style>
@endpush

@section('content')
    {{-- Header & Download All Pusat Action --}}
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-7">
            <h4 class="branch-section-title mb-1">Rapor Karyawan</h4>
            <p class="text-muted small ms-4 mb-0">Pilih cabang atau unit untuk menilai performa karyawan. Terbagi atas unit Pusat dan Cabang.</p>
        </div>
        <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
            <a href="{{ route('employee-evaluations.index', ['download_pusat_pdf' => 1]) }}" target="_blank" 
               class="btn btn-danger shadow-sm d-flex align-items-center py-2 px-3 rounded-pill"
               title="Download satu dokumen PDF berisi seluruh karyawan Pusat yang sudah dinilai">
                <i class="mdi mdi-file-pdf-box fs-5 me-2"></i>
                <span class="fw-semibold">Download All Pusat PDF Rapor</span>
                <span class="badge bg-white text-danger ms-2 fw-bold" style="font-size: 0.75rem;">
                    {{ $evaluatedPusatUsers }} Dinilai
                </span>
            </a>
        </div>
    </div>

    {{-- Widget Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        {{-- Card Pusat --}}
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-widget border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-uppercase text-muted fw-bold small">Kantor & Unit Pusat</span>
                        <h4 class="fw-bolder text-primary mb-0 mt-1">{{ count($pusatBranches) }} <small class="text-muted fs-6 fw-normal">Unit</small></h4>
                    </div>
                    <div class="branch-icon-box pusat">
                        <i class="mdi mdi-city"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-2 border-top">
                    <span>Total Karyawan: <strong>{{ $totalPusatUsers }}</strong></span>
                    <span class="badge {{ $evaluatedPusatUsers > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' }}">
                        <i class="mdi mdi-account-check-outline me-1"></i>{{ $evaluatedPusatUsers }} / {{ $totalPusatUsers }} Dinilai
                    </span>
                </div>
            </div>
        </div>

        {{-- Card Cabang --}}
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-widget border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-uppercase text-muted fw-bold small">Cabang Operasional</span>
                        <h4 class="fw-bolder text-info mb-0 mt-1">{{ count($cabangBranches) }} <small class="text-muted fs-6 fw-normal">Cabang</small></h4>
                    </div>
                    <div class="branch-icon-box cabang">
                        <i class="mdi mdi-storefront-outline"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-2 border-top">
                    <span>Total Karyawan: <strong>{{ $totalCabangUsers }}</strong></span>
                    <span class="badge {{ $evaluatedCabangUsers > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' }}">
                        <i class="mdi mdi-account-check-outline me-1"></i>{{ $evaluatedCabangUsers }} / {{ $totalCabangUsers }} Dinilai
                    </span>
                </div>
            </div>
        </div>

        {{-- Card Total --}}
        <div class="col-lg-4 col-md-12">
            <div class="stat-card-widget border-start border-dark border-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-uppercase text-muted fw-bold small">Total Keseluruhan</span>
                        <h4 class="fw-bolder text-dark mb-0 mt-1">{{ count($branches) }} <small class="text-muted fs-6 fw-normal">Unit/Cabang</small></h4>
                    </div>
                    <div class="branch-icon-box">
                        <i class="mdi mdi-domain"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-2 border-top">
                    <span>Total Karyawan: <strong>{{ $totalPusatUsers + $totalCabangUsers }}</strong></span>
                    @php
                        $grandTotalUsers = $totalPusatUsers + $totalCabangUsers;
                        $grandTotalEvaluated = $evaluatedPusatUsers + $evaluatedCabangUsers;
                    @endphp
                    <span class="badge bg-primary text-white">
                        <i class="mdi mdi-check-all me-1"></i>{{ $grandTotalEvaluated }} / {{ $grandTotalUsers }} Dinilai
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Pemisah & Pencarian Cabang --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-lg-8 col-md-7">
                    <ul class="nav nav-pills nav-pills-custom gap-2" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center" id="pills-pusat-tab" data-bs-toggle="pill" data-bs-target="#pills-pusat" type="button" role="tab">
                                <i class="mdi mdi-city me-2"></i> Kantor & Unit Pusat
                                <span class="badge bg-white text-dark ms-2">{{ count($pusatBranches) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center" id="pills-cabang-tab" data-bs-toggle="pill" data-bs-target="#pills-cabang" type="button" role="tab">
                                <i class="mdi mdi-storefront-outline me-2"></i> Cabang Operasional
                                <span class="badge bg-white text-dark ms-2">{{ count($cabangBranches) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab">
                                <i class="mdi mdi-view-grid-outline me-2"></i> Semua Cabang & Unit
                                <span class="badge bg-white text-dark ms-2">{{ count($branches) }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="text" id="branchSearchInput" class="form-control border-start-0 bg-light" 
                               placeholder="Cari nama cabang atau unit...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Tab Panes --}}
    <div class="tab-content" id="pills-tabContent">
        {{-- TAB 1: PUSAT --}}
        <div class="tab-pane fade show active" id="pills-pusat" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="mdi mdi-city text-primary me-1"></i> Daftar Unit Pusat ({{ count($pusatBranches) }})
                </h6>
                <span class="text-muted small">
                    Progres: <strong>{{ $evaluatedPusatUsers }}</strong> dari <strong>{{ $totalPusatUsers }}</strong> karyawan sudah dinilai
                </span>
            </div>

            <div class="row g-4 branch-list-container">
                @forelse ($pusatBranches as $branch)
                    @include('employee_evaluations._branch_card', ['branch' => $branch, 'type' => 'pusat'])
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4">
                            <div class="text-muted">
                                <i class="mdi mdi-office-building-off" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada unit pusat yang ditemukan.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 2: CABANG --}}
        <div class="tab-pane fade" id="pills-cabang" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="mdi mdi-storefront-outline text-info me-1"></i> Daftar Cabang Operasional ({{ count($cabangBranches) }})
                </h6>
                <span class="text-muted small">
                    Progres: <strong>{{ $evaluatedCabangUsers }}</strong> dari <strong>{{ $totalCabangUsers }}</strong> karyawan sudah dinilai
                </span>
            </div>

            <div class="row g-4 branch-list-container">
                @forelse ($cabangBranches as $branch)
                    @include('employee_evaluations._branch_card', ['branch' => $branch, 'type' => 'cabang'])
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4">
                            <div class="text-muted">
                                <i class="mdi mdi-store-off" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada cabang operasional yang ditemukan.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 3: SEMUA --}}
        <div class="tab-pane fade" id="pills-all" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="mdi mdi-view-grid-outline text-secondary me-1"></i> Semua Cabang & Unit ({{ count($branches) }})
                </h6>
                <span class="text-muted small">
                    Total: <strong>{{ $evaluatedPusatUsers + $evaluatedCabangUsers }}</strong> dari <strong>{{ $totalPusatUsers + $totalCabangUsers }}</strong> karyawan sudah dinilai
                </span>
            </div>

            <div class="row g-4 branch-list-container">
                @forelse ($branches as $branch)
                    @include('employee_evaluations._branch_card', ['branch' => $branch, 'type' => $branch->is_pusat ? 'pusat' : 'cabang'])
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4">
                            <div class="text-muted">
                                <i class="mdi mdi-office-building-off" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada data cabang yang ditemukan.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('branchSearchInput');
            if (!searchInput) return;

            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const activeTabPane = document.querySelector('.tab-pane.active');
                if (!activeTabPane) return;

                const cards = activeTabPane.querySelectorAll('.branch-card-col');
                let visibleCount = 0;

                cards.forEach(card => {
                    const branchName = card.getAttribute('data-branch-name') || '';
                    if (branchName.toLowerCase().includes(query)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Re-apply search when tab changes
            const tabButtons = document.querySelectorAll('button[data-bs-toggle="pill"]');
            tabButtons.forEach(btn => {
                btn.addEventListener('shown.bs.tab', function () {
                    if (searchInput.value.trim() !== '') {
                        searchInput.dispatchEvent(new Event('input'));
                    } else {
                        const activeTabPane = document.querySelector('.tab-pane.active');
                        if (activeTabPane) {
                            activeTabPane.querySelectorAll('.branch-card-col').forEach(card => card.style.display = '');
                        }
                    }
                });
            });
        });
    </script>
@endpush
