@extends('layout.master')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Manajemen Kasbon</h2>
            <p class="text-muted mb-0">Overview data peminjaman dan status pembayaran karyawan.</p>
        </div>
        <a href="{{ route('kasbon.create') }}" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm fw-bold">
            <i class="mdi mdi-plus-circle-outline me-2"></i> Buat Pengajuan
        </a>
    </div>

    @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white h-100 rounded-4 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-white bg-opacity-25 text-white">Need Action</span>
                        <i class="mdi mdi-clock-alert-outline fs-2 text-white opacity-50"></i>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ $stats['pending'] }}</h2>
                    <small class="text-white opacity-75">Menunggu Approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100 rounded-4 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-white bg-opacity-25 text-white">Active Loans</span>
                        <i class="mdi mdi-wallet-outline fs-2 text-white opacity-50"></i>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ $stats['active'] }}</h2>
                    <small class="text-white opacity-75">Karyawan Sedang Mencicil</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100 rounded-4 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-white bg-opacity-25 text-white">Completed</span>
                        <i class="mdi mdi-check-decagram-outline fs-2 text-white opacity-50"></i>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ $stats['paid'] }}</h2>
                    <small class="text-white opacity-75">Pinjaman Lunas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white border-start border-4 border-info h-100 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted fw-bold text-uppercase">Total Piutang Aktif</small>
                        <i class="mdi mdi-chart-line fs-3 text-info"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-dark">Rp {{ number_format($stats['total_active_amount'], 0, ',', '.') }}</h3>
                    <small class="text-muted">Uang perusahaan di luar</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('kasbon.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari data..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aktif</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark fw-bold flex-grow-1">
                            <i class="mdi mdi-filter-variant me-1"></i> Filter
                        </button>
                        <a href="{{ route('kasbon.export', request()->query()) }}" class="btn btn-outline-success fw-bold flex-grow-1">
                            <i class="mdi mdi-microsoft-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr class="text-uppercase small text-muted letter-spacing-1">
                            <th class="py-4 ps-4">Karyawan</th>
                            <th class="py-4">Tanggal & Keterangan</th>
                            <th class="py-4 text-end">Total Pinjam</th>
                            <th class="py-4 text-end">Sisa Hutang</th>
                            <th class="py-4 text-center">Status</th>
                            <th class="py-4 text-center pe-4" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasbons as $k)
                            @php
                                // JSON Decode Divisi & Cabang
                                $divisionName = $k->division;
                                $decodedDiv = json_decode($divisionName);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decodedDiv->name)) {
                                    $divisionName = $decodedDiv->name;
                                }

                                $branchName = $k->branch;
                                $decodedBranch = json_decode($branchName);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decodedBranch->name)) {
                                    $branchName = $decodedBranch->name;
                                }
                                
                                // Progress Calculation
                                $percent = $k->amount > 0 ? ($k->total_paid / $k->amount) * 100 : 0;
                            @endphp

                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm" 
                                             style="width: 42px; height: 42px; font-size: 14px;">
                                            {{ substr($k->user_name, 0, 2) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $k->user_name }}</h6>
                                            <div class="small text-muted d-flex align-items-center mt-1">
                                                <i class="mdi mdi-domain me-1"></i> {{ $divisionName }}
                                                <span class="mx-2 text-secondary">|</span>
                                                <span class="text-truncate" style="max-width: 120px;">{{ $branchName }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark fs-6">{{ $k->created_at->format('d M Y') }}</span>
                                        <span class="text-muted small text-truncate mt-1" style="max-width: 250px;">
                                            {{ Str::limit($k->description, 50) }}
                                        </span>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <h6 class="mb-0 fw-bold text-dark">Rp {{ number_format($k->amount, 0, ',', '.') }}</h6>
                                </td>

                                <td class="text-end">
                                    <h6 class="mb-0 fw-bold {{ $k->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($k->remaining_amount, 0, ',', '.') }}
                                    </h6>
                                    @if($k->amount > 0)
                                    <div class="d-flex align-items-center justify-content-end mt-1">
                                        <div class="progress" style="height: 4px; width: 80px;">
                                            <div class="progress-bar {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ min($percent, 100) }}%"></div>
                                        </div>
                                        <span class="ms-2 small text-muted" style="font-size: 10px;">{{ number_format($percent, 0) }}%</span>
                                    </div>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($k->status == 'pending')
                                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold border border-warning">
                                            <i class="mdi mdi-timer-sand me-1"></i> MENUNGGU
                                        </span>
                                    @elseif($k->status == 'approved')
                                        <span class="badge rounded-pill bg-white text-primary px-3 py-2 fw-bold border border-primary shadow-sm">
                                            <i class="mdi mdi-run me-1"></i> AKTIF
                                        </span>
                                    @elseif($k->status == 'paid')
                                        <span class="badge rounded-pill bg-success text-white px-3 py-2 fw-bold shadow-sm">
                                            <i class="mdi mdi-check-all me-1"></i> LUNAS
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-danger text-white px-3 py-2 fw-bold shadow-sm">
                                            <i class="mdi mdi-close me-1"></i> DITOLAK
                                        </span>
                                    @endif
                                </td>

                                <td class="pe-4 text-center">
                                    <a href="{{ route('kasbon.show', $k->id) }}" class="btn btn-sm btn-light border fw-bold text-dark rounded-pill px-3 hover-shadow">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2"></i>
                                        <h6 class="fw-bold">Tidak ada data ditemukan</h6>
                                        <p class="small">Coba ubah filter pencarian Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($kasbons->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-end">
                    {{ $kasbons->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Styling Tambahan */
    .hover-shadow:hover {
        transform: translateY(-1px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        transition: all .2s;
    }
    .letter-spacing-1 { letter-spacing: 1px; }
    .badge { font-size: 0.75rem; letter-spacing: 0.5px; }
    
    .pagination { margin-bottom: 0; }
    .page-item.active .page-link {
        background-color: #4b49ac;
        border-color: #4b49ac;
    }
    .page-link { color: #4b49ac; }
</style>
@endsection