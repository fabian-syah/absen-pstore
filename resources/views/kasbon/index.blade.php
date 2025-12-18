@extends('layout.master')

@section('content')
<div class="container-fluid">
    <!-- HEADER & ACTION -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Manajemen Kasbon</h1>
            <p class="text-muted mb-0">Overview data peminjaman dan status pembayaran karyawan.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-lg shadow-sm px-4 fw-semibold rounded-pill d-flex align-items-center">
                <i class="mdi mdi-filter-outline me-2 fs-5"></i> Filter
            </button>
            <button class="btn btn-outline-primary btn-lg shadow-sm px-4 fw-semibold rounded-pill d-flex align-items-center">
                <i class="mdi mdi-export me-2 fs-5"></i> Ekspor
            </button>
            <a href="{{ route('kasbon.create') }}" class="btn btn-primary btn-lg shadow-sm px-4 fw-bold rounded-pill d-flex align-items-center">
                <i class="mdi mdi-plus me-2 fs-5"></i> Buat Pengajuan
            </a>
        </div>
    </div>

    <!-- STATISTICS CARDS -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Menunggu Approval</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $kasbons->where('status', 'pending')->count() }}</h4>
                            <small class="text-muted">Pengajuan</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="mdi mdi-clock-outline fs-3 text-warning"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($kasbons->where('status', 'pending')->count() / max($kasbons->count(), 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Aktif Berjalan</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $kasbons->where('status', 'approved')->where('remaining_amount', '>', 0)->count() }}</h4>
                            <small class="text-muted">Karyawan</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="mdi mdi-cash-multiple fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($kasbons->where('status', 'approved')->where('remaining_amount', '>', 0)->count() / max($kasbons->count(), 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Total Lunas</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $kasbons->where('status', 'paid')->count() }}</h4>
                            <small class="text-muted">Selesai</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="mdi mdi-check-decagram fs-3 text-success"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($kasbons->where('status', 'paid')->count() / max($kasbons->count(), 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Total Nilai Aktif</p>
                            <h4 class="mb-0 fw-bold text-dark">Rp {{ number_format($kasbons->where('status', 'approved')->where('remaining_amount', '>', 0)->sum('remaining_amount'), 0, ',', '.') }}</h4>
                            <small class="text-muted">Sisa Kewajiban</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="mdi mdi-chart-line fs-3 text-info"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari berdasarkan nama karyawan, divisi, atau keterangan...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Aktif</option>
                        <option value="paid">Lunas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Semua Divisi</option>
                        <!-- Dynamic options would go here -->
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TABLE -->
    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small text-muted letter-spacing-1">
                            <th class="py-3 ps-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="">
                                </div>
                            </th>
                            <th class="py-3">Karyawan</th>
                            <th class="py-3">Detail Pengajuan</th>
                            <th class="py-3 text-end">Nominal</th>
                            <th class="py-3 text-end">Sisa</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasbons as $k)
                            @php
                                // Decode JSON data for division and branch
                                $divisionName = $k->division;
                                $branchName = $k->branch;
                                
                                $decodedDiv = json_decode($divisionName);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decodedDiv->name)) {
                                    $divisionName = $decodedDiv->name;
                                }
                                
                                $decodedBranch = json_decode($branchName);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decodedBranch->name)) {
                                    $branchName = $decodedBranch->name;
                                }
                                
                                // Calculate percentage for progress bar[citation:3]
                                $percent = $k->amount > 0 ? ($k->total_paid / $k->amount) * 100 : 0;
                            @endphp

                            <tr class="hover-highlight">
                                <!-- Checkbox -->
                                <td class="ps-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="">
                                    </div>
                                </td>
                                
                                <!-- Employee Info -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initials rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            {{ substr($k->user_name, 0, 2) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $k->user_name }}</h6>
                                            <div class="small text-muted d-flex align-items-center flex-wrap mt-1">
                                                <span class="d-flex align-items-center me-2">
                                                    <i class="mdi mdi-briefcase-outline me-1 fs-6"></i> {{ $divisionName }}
                                                </span>
                                                <span class="d-flex align-items-center">
                                                    <i class="mdi mdi-map-marker-outline me-1 fs-6"></i> {{ $branchName }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Submission Details -->
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="mdi mdi-calendar-blank text-muted me-1 fs-6"></i>
                                            <span class="text-dark fw-semibold">{{ $k->created_at->format('d M Y') }}</span>
                                            <span class="badge bg-light text-dark ms-2">ID: {{ $k->id }}</span>
                                        </div>
                                        <small class="text-muted text-truncate" style="max-width: 250px;">
                                            {{ Str::limit($k->description, 40) }}
                                        </small>
                                    </div>
                                </td>
                                
                                <!-- Amount -->
                                <td class="text-end">
                                    <div class="d-flex flex-column align-items-end">
                                        <h6 class="mb-0 fw-bold text-dark">Rp {{ number_format($k->amount, 0, ',', '.') }}</h6>
                                        <small class="text-muted">Total Pinjaman</small>
                                    </div>
                                </td>
                                
                                <!-- Remaining Amount -->
                                <td class="text-end">
                                    <div class="d-flex flex-column align-items-end">
                                        <h6 class="mb-0 fw-bold {{ $k->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            Rp {{ number_format($k->remaining_amount, 0, ',', '.') }}
                                        </h6>
                                        <small class="text-muted">Sisa Kewajiban</small>
                                        
                                        @if($k->amount > 0)
                                            <div class="mt-2">
                                                <div class="progress" style="height: 6px; width: 100px;">
                                                    <div class="progress-bar {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ min($percent, 100) }}%"
                                                         aria-valuenow="{{ $percent }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ number_format($percent, 1) }}% Terbayar</small>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Status -->
                                <td class="text-center">
                                    @if($k->status == 'pending')
                                        <span class="badge bg-warning bg-opacity-15 text-warning px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <i class="mdi mdi-clock-outline me-1 fs-6"></i> Pending
                                        </span>
                                    @elseif($k->status == 'approved')
                                        <span class="badge bg-primary bg-opacity-15 text-primary px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <i class="mdi mdi-run me-1 fs-6"></i> Aktif
                                        </span>
                                    @elseif($k->status == 'paid')
                                        <span class="badge bg-success bg-opacity-15 text-success px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <i class="mdi mdi-check-circle-outline me-1 fs-6"></i> Lunas
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-15 text-danger px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <i class="mdi mdi-close-circle-outline me-1 fs-6"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Actions -->
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('kasbon.show', $k->id) }}" 
                                           class="btn btn-light btn-sm rounded-start-pill px-3 fw-semibold text-primary d-flex align-items-center">
                                            <i class="mdi mdi-eye-outline me-1"></i> Detail
                                        </a>
                                        <button type="button" class="btn btn-light btn-sm rounded-end-pill px-3 dropdown-toggle dropdown-toggle-split" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('kasbon.show', $k->id) }}"><i class="mdi mdi-eye-outline me-2"></i> Lihat Detail</a></li>
                                            <li><a class="dropdown-item" href="{{ route('kasbon.edit', $k->id) }}"><i class="mdi mdi-pencil-outline me-2"></i> Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="return confirm('Hapus pengajuan ini?')"><i class="mdi mdi-delete-outline me-2"></i> Hapus</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="bg-light rounded-circle p-4 mb-3">
                                            <i class="mdi mdi-file-document-outline fs-1 text-muted"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">Belum ada data kasbon</h5>
                                        <p class="text-muted mb-4">Silakan buat pengajuan baru untuk memulai.</p>
                                        <a href="{{ route('kasbon.create') }}" class="btn btn-primary rounded-pill px-4">
                                            <i class="mdi mdi-plus me-2"></i> Buat Pengajuan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination & Summary -->
            @if($kasbons->count() > 0)
                <div class="card-footer bg-transparent border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $kasbons->firstItem() ?? 0 }}-{{ $kasbons->lastItem() ?? 0 }} dari {{ $kasbons->total() }} entri
                        </div>
                        <div>
                            {{ $kasbons->links() }}
                        </div>
                        <div class="text-muted small">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Total Aktif: Rp {{ number_format($kasbons->where('status', 'approved')->where('remaining_amount', '>', 0)->sum('remaining_amount'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Custom CSS for Enhancements -->
<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
    }
    .hover-highlight:hover {
        background-color: rgba(13, 110, 253, 0.02) !important;
    }
    .avatar-initials {
        font-size: 0.9rem;
    }
    .letter-spacing-1 {
        letter-spacing: 0.5px;
    }
    .progress {
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
</style>
@endsection