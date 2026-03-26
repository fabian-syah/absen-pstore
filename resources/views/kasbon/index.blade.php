@extends('layout.master')

@section('content')
    @php
        // Cek Role Admin / Admin Gaji
        $isAdmin = in_array(auth()->user()->role, ['admin', 'admin_gaji']);
        $currentView = request('view_type', 'active'); // Ambil parameter active/history
    @endphp

    <div class="container-fluid px-3 px-md-4">

        {{-- HEADER (Responsive) --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold text-dark mb-1 fs-4 fs-md-2">Manajemen Kasbon</h2>
                <p class="text-muted mb-0 small">
                    @if($isAdmin)
                        Overview data peminjaman seluruh karyawan.
                    @else
                        Riwayat peminjaman dan sisa kewajiban Anda.
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 w-100 w-md-auto">
                <a href="{{ route('kasbon.calendar') }}" class="btn btn-outline-primary px-3 px-md-4 rounded-pill shadow-sm fw-bold flex-grow-1 flex-md-grow-0">
                    <i class="mdi mdi-calendar-month me-1"></i> <span class="d-none d-sm-inline">Kalender</span>
                </a>
                <a href="{{ route('kasbon.create') }}" class="btn btn-primary px-3 px-md-4 rounded-pill shadow-sm fw-bold flex-grow-1 flex-md-grow-0">
                    <i class="mdi mdi-plus-circle-outline me-1"></i> <span class="d-none d-sm-inline">Buat</span> Pengajuan
                </a>
            </div>
        </div>

        {{-- STATISTIK CARDS (Responsive 2-col on mobile) --}}
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-3">
                @if($isAdmin)
                    <div class="card border-0 shadow-sm bg-warning text-white h-100 rounded-4 overflow-hidden">
                        <div class="card-body p-3 p-md-4 position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-white bg-opacity-25 text-white" style="font-size: 0.65rem;">Need Action</span>
                                <i class="mdi mdi-clock-alert-outline fs-4 fs-md-2 text-white opacity-50"></i>
                            </div>
                            <h3 class="mb-0 fw-bold fs-4">{{ $stats['pending'] }}</h3>
                            <small class="text-white opacity-75" style="font-size: 0.7rem;">Menunggu Approval</small>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm text-white h-100 rounded-4 overflow-hidden" style="background-color: #6f42c1;">
                        <div class="card-body p-3 p-md-4 position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-white bg-opacity-25 text-white" style="font-size: 0.65rem;">History</span>
                                <i class="mdi mdi-file-document-multiple-outline fs-4 fs-md-2 text-white opacity-50"></i>
                            </div>
                            <h3 class="mb-0 fw-bold fs-4">{{ $kasbons->total() }}</h3>
                            <small class="text-white opacity-75" style="font-size: 0.7rem;">Total Transaksi</small>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white h-100 rounded-4 overflow-hidden">
                    <div class="card-body p-3 p-md-4 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-white bg-opacity-25 text-white" style="font-size: 0.65rem;">Active</span>
                            <i class="mdi mdi-wallet-outline fs-4 fs-md-2 text-white opacity-50"></i>
                        </div>
                        <h3 class="mb-0 fw-bold fs-4">{{ $stats['active'] }}</h3>
                        <small class="text-white opacity-75" style="font-size: 0.7rem;">
                            {{ $isAdmin ? 'Karyawan Mencicil' : 'Pinjaman Aktif' }}
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white h-100 rounded-4 overflow-hidden">
                    <div class="card-body p-3 p-md-4 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-white bg-opacity-25 text-white" style="font-size: 0.65rem;">Completed</span>
                            <i class="mdi mdi-check-decagram-outline fs-4 fs-md-2 text-white opacity-50"></i>
                        </div>
                        <h3 class="mb-0 fw-bold fs-4">{{ $stats['paid'] }}</h3>
                        <small class="text-white opacity-75" style="font-size: 0.7rem;">
                            {{ $isAdmin ? 'Total Lunas' : 'Riwayat Lunas' }}
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm bg-white border-start border-4 border-info h-100 rounded-4">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">
                                {{ $isAdmin ? 'Total Piutang' : 'Sisa Kewajiban' }}
                            </small>
                            <i class="mdi mdi-chart-line fs-4 text-info"></i>
                        </div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size: clamp(0.85rem, 2.5vw, 1.25rem);">Rp
                            {{ number_format($stats['total_active_amount'], 0, ',', '.') }}
                        </h4>
                        <small class="text-muted d-none d-md-block" style="font-size: 0.7rem;">
                            {{ $isAdmin ? 'Uang perusahaan di luar' : 'Total belum dibayar' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADMIN FILTER (Responsive) --}}
        @if($isAdmin)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-md-4">
                    <form action="{{ route('kasbon.index') }}" method="GET">
                        <input type="hidden" name="view_type" value="{{ $currentView }}">

                        <div class="row g-2 g-md-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label small text-muted fw-bold mb-1">Pencarian</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                                        placeholder="Nama / Keterangan..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted fw-bold mb-1">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aktif</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted fw-bold mb-1">Dari</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted fw-bold mb-1">Sampai</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-6 col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-dark fw-bold flex-grow-1">
                                    <i class="mdi mdi-filter-variant me-1"></i> Filter
                                </button>
                                <a href="{{ route('kasbon.export', request()->query()) }}"
                                    class="btn btn-outline-success fw-bold flex-grow-1">
                                    <i class="mdi mdi-microsoft-excel me-1"></i> <span class="d-none d-lg-inline">Excel</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- NAV PILLS (Responsive) --}}
        <div class="mb-3">
            <ul class="nav nav-pills nav-fill bg-white p-2 rounded-4 shadow-sm" style="max-width: 100%;">
                <li class="nav-item">
                    <a class="nav-link fw-bold {{ $currentView == 'active' ? 'active shadow-sm' : 'text-muted' }}"
                        href="{{ route('kasbon.index', ['view_type' => 'active']) }}">
                        <i class="mdi mdi-format-list-bulleted me-1"></i> Aktif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold {{ $currentView == 'history' ? 'active shadow-sm bg-secondary' : 'text-muted' }}"
                        href="{{ route('kasbon.index', ['view_type' => 'history']) }}">
                        <i class="mdi mdi-history me-1"></i> Riwayat
                    </a>
                </li>
            </ul>
        </div>

        {{-- BUTTON BULK APPROVE (Admin Only) --}}
        @if($isAdmin)
            <form id="bulkForm" action="{{ route('kasbon.bulk-approve') }}" method="POST">
                @csrf
                <div id="bulkActionContainer" class="mb-3 d-none">
                    <button type="submit" class="btn btn-warning fw-bold text-dark rounded-pill px-4 shadow-sm"
                        onclick="return confirm('Yakin ingin menyetujui data terpilih?')">
                        <i class="mdi mdi-checkbox-multiple-marked-outline me-2"></i> Setujui Yang Dipilih
                    </button>
                </div>
        @endif

            {{-- ==================== DESKTOP TABLE (Hidden on Mobile) ==================== --}}
            <div class="card border-0 shadow-sm rounded-4 d-none d-lg-block">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light border-bottom">
                                <tr class="text-uppercase small text-muted letter-spacing-1">
                                    @if($isAdmin)
                                        <th class="py-4 ps-4" style="width: 50px;">
                                            <input type="checkbox" id="selectAll" class="form-check-input" style="cursor: pointer;">
                                        </th>
                                    @endif
                                    <th class="py-4 {{ $isAdmin ? '' : 'ps-4' }}">Karyawan</th>
                                    <th class="py-4">Tanggal</th>
                                    <th class="py-4">Keterangan</th>
                                    <th class="py-4 text-end">Total Pinjam</th>
                                    <th class="py-4 text-center">Potongan/Bln</th>
                                    <th class="py-4 text-end">Sisa Hutang</th>
                                    <th class="py-4 text-center">Status</th>
                                    <th class="py-4 text-center pe-4" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasbons as $k)
                                    @php
                                        $divisionName = $k->division;
                                        $decodedDiv = json_decode($divisionName);
                                        if (json_last_error() === JSON_ERROR_NONE && isset($decodedDiv->name))
                                            $divisionName = $decodedDiv->name;

                                        $branchName = $k->branch;
                                        $decodedBranch = json_decode($branchName);
                                        if (json_last_error() === JSON_ERROR_NONE && isset($decodedBranch->name))
                                            $branchName = $decodedBranch->name;

                                        $percent = $k->amount > 0 ? ($k->total_paid / $k->amount) * 100 : 0;
                                    @endphp

                                    <tr>
                                        @if($isAdmin)
                                            <td class="ps-4">
                                                @if($k->status == 'pending')
                                                    <input type="checkbox" name="ids[]" value="{{ $k->id }}"
                                                        class="form-check-input bulk-check" style="cursor: pointer;">
                                                @else
                                                    <i class="mdi mdi-minus text-muted"></i>
                                                @endif
                                            </td>
                                        @endif

                                        <td class="{{ $isAdmin ? '' : 'ps-4' }}">
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
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-calendar-blank text-muted me-2 fs-5"></i>
                                                <span class="fw-bold text-dark">{{ $k->created_at->format('d M Y') }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="text-muted small d-block text-truncate" style="max-width: 200px;"
                                                title="{{ $k->description }}">
                                                {{ Str::limit($k->description, 35) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <h6 class="mb-0 fw-bold text-dark">Rp {{ number_format($k->amount, 0, ',', '.') }}</h6>
                                        </td>

                                        <td class="text-center">
                                            @if($k->monthly_deduction > 0)
                                                <div>
                                                    <span class="badge rounded-pill bg-light text-success border border-success fw-bold px-3 py-2">
                                                        <i class="mdi mdi-calendar-clock me-1"></i>
                                                        Rp {{ number_format($k->monthly_deduction, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                                @if($k->installment_months)
                                                    <small class="text-muted" style="font-size: 10px;">{{ $k->installment_months }} bulan</small>
                                                @endif
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            <h6 class="mb-0 fw-bold {{ $k->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                Rp {{ number_format($k->remaining_amount, 0, ',', '.') }}
                                            </h6>
                                            @if($k->amount > 0 && $k->remaining_amount > 0)
                                                <div class="d-flex align-items-center justify-content-end mt-1">
                                                    <div class="progress" style="height: 4px; width: 80px;">
                                                        <div class="progress-bar {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}"
                                                            role="progressbar" style="width: {{ min($percent, 100) }}%"></div>
                                                    </div>
                                                    <span class="ms-2 small text-muted" style="font-size: 10px;">{{ number_format($percent, 0) }}%</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @include('kasbon.partials._status_badge', ['status' => $k->status])
                                        </td>

                                        <td class="pe-4 text-center">
                                            <a href="{{ route('kasbon.show', $k->id) }}"
                                                class="btn btn-sm btn-light border fw-bold text-dark rounded-pill px-3 hover-shadow">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                                <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2"></i>
                                                <h6 class="fw-bold">Tidak ada data ditemukan</h6>
                                                <p class="small">Belum ada data pada tab ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ==================== MOBILE CARD LAYOUT (Visible on Mobile Only) ==================== --}}
            <div class="d-lg-none">
                @forelse($kasbons as $k)
                    @php
                        $divisionName = $k->division;
                        $decodedDiv = json_decode($divisionName);
                        if (json_last_error() === JSON_ERROR_NONE && isset($decodedDiv->name))
                            $divisionName = $decodedDiv->name;

                        $branchName = $k->branch;
                        $decodedBranch = json_decode($branchName);
                        if (json_last_error() === JSON_ERROR_NONE && isset($decodedBranch->name))
                            $branchName = $decodedBranch->name;

                        $percent = $k->amount > 0 ? ($k->total_paid / $k->amount) * 100 : 0;
                    @endphp

                    <div class="card border-0 shadow-sm rounded-4 mb-3 kasbon-mobile-card">
                        <div class="card-body p-3">
                            {{-- Top Row: User + Status --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center flex-grow-1 me-2">
                                    @if($isAdmin && $k->status == 'pending')
                                        <input type="checkbox" name="ids[]" value="{{ $k->id }}"
                                            class="form-check-input bulk-check me-2 flex-shrink-0" style="cursor: pointer;">
                                    @endif
                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold me-2 shadow-sm flex-shrink-0"
                                        style="width: 36px; height: 36px; font-size: 12px;">
                                        {{ substr($k->user_name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.9rem;">{{ $k->user_name }}</h6>
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">
                                            {{ $divisionName }} • {{ $branchName }}
                                        </small>
                                    </div>
                                </div>
                                @include('kasbon.partials._status_badge', ['status' => $k->status])
                            </div>

                            {{-- Keterangan --}}
                            @if($k->description)
                                <p class="text-muted small mb-3 bg-light rounded-3 p-2" style="font-size: 0.75rem;">
                                    {{ Str::limit($k->description, 60) }}
                                </p>
                            @endif

                            {{-- Financial Info --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block fw-bold" style="font-size: 0.65rem;">TOTAL PINJAM</small>
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">Rp {{ number_format($k->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block fw-bold" style="font-size: 0.65rem;">SISA HUTANG</small>
                                        <span class="fw-bold {{ $k->remaining_amount > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 0.85rem;">
                                            Rp {{ number_format($k->remaining_amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            @if($k->amount > 0 && $k->remaining_amount > 0)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted fw-bold" style="font-size: 0.65rem;">PROGRESS</span>
                                        <span class="fw-bold text-primary" style="font-size: 0.65rem;">{{ number_format($percent, 0) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}"
                                            style="width: {{ min($percent, 100) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            {{-- Bottom Row: Potongan + Tanggal + Detail --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    @if($k->monthly_deduction > 0)
                                        <span class="badge bg-light text-success border border-success fw-bold px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="mdi mdi-calendar-clock"></i> Rp {{ number_format($k->monthly_deduction, 0, ',', '.') }}/bln
                                        </span>
                                    @endif
                                    <span class="text-muted" style="font-size: 0.7rem;">
                                        <i class="mdi mdi-calendar-blank"></i> {{ $k->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <a href="{{ route('kasbon.show', $k->id) }}"
                                    class="btn btn-sm btn-primary fw-bold rounded-pill px-3" style="font-size: 0.75rem;">
                                    Detail <i class="mdi mdi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body text-center py-5">
                            <div class="opacity-50">
                                <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2 d-block"></i>
                                <h6 class="fw-bold">Tidak ada data ditemukan</h6>
                                <p class="small mb-0">Belum ada data pada tab ini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- PAGINATION (Both Desktop & Mobile) --}}
            @if($kasbons->hasPages())
                <div class="d-flex justify-content-center justify-content-md-end mt-3">
                    {{ $kasbons->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @if($isAdmin)
            </form>
        @endif
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-1px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            transition: all .2s;
        }

        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .badge {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-item.active .page-link {
            background-color: #4b49ac;
            border-color: #4b49ac;
        }

        .page-link {
            color: #4b49ac;
        }

        /* Nav Pills Style */
        .nav-pills .nav-link {
            border-radius: 50rem;
            transition: all 0.2s;
            font-size: 0.85rem;
            padding: 8px 16px;
        }

        .nav-pills .nav-link.active {
            background-color: #4b49ac;
            color: white;
        }

        .nav-pills .nav-link.bg-secondary {
            background-color: #6c757d !important;
            color: white;
        }

        /* Mobile Card Animations */
        .kasbon-mobile-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .kasbon-mobile-card:active {
            transform: scale(0.98);
        }

        /* Min width helper */
        .min-w-0 { min-width: 0; }

        /* Mobile specific */
        @media (max-width: 767.98px) {
            .nav-pills .nav-link {
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            .pagination .page-link {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
    </style>

    @if($isAdmin)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selectAll = document.getElementById('selectAll');
                const bulkChecks = document.querySelectorAll('.bulk-check');
                const bulkActionContainer = document.getElementById('bulkActionContainer');

                function toggleBulkButton() {
                    const anyChecked = Array.from(bulkChecks).some(cb => cb.checked);
                    if (anyChecked) {
                        bulkActionContainer.classList.remove('d-none');
                    } else {
                        bulkActionContainer.classList.add('d-none');
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        bulkChecks.forEach(cb => {
                            cb.checked = this.checked;
                        });
                        toggleBulkButton();
                    });
                }

                bulkChecks.forEach(cb => {
                    cb.addEventListener('change', toggleBulkButton);
                });
            });
        </script>
    @endif
@endsection