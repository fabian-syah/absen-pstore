@extends('layout.master')

@section('title', 'Data Divisi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- HEADER SECTION --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h4 class="card-title mb-2">
                                <span class="d-flex align-items-center">
                                    <span
                                        class="icon-wrapper bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-layers-outline text-white" style="font-size: 1.5rem;"></i>
                                    </span>
                                    <span>
                                        <span class="d-block text-dark fw-bold" style="font-size: 1.5rem;">Daftar
                                            Divisi</span>
                                        <span class="text-muted small">Kelola data divisi dan pantau total personel</span>
                                    </span>
                                </span>
                            </h4>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            {{-- Search Form --}}
                            <div class="search-wrapper">
                                <form action="{{ route('divisions.index') }}" method="GET" class="d-flex">
                                    <div class="input-group input-group-search">
                                        <span class="input-group-text border-0 bg-transparent px-3">
                                            <i class="mdi mdi-magnify text-muted"></i>
                                        </span>
                                        <input type="text" name="search" class="form-control border-0 shadow-none"
                                            placeholder="Cari divisi..." value="{{ request('search') }}">
                                        @if (request('search'))
                                            <a href="{{ route('divisions.index') }}"
                                                class="input-group-text border-0 bg-transparent px-3 text-danger"
                                                title="Hapus Filter">
                                                <i class="mdi mdi-close"></i>
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            {{-- Tombol Tambah --}}
                            <a href="{{ route('divisions.create') }}"
                                class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2">
                                <i class="mdi mdi-plus-circle-outline me-1"></i>
                                <span>Tambah Divisi</span>
                            </a>
                        </div>
                    </div>

                    {{-- STATS CARDS --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card bg-gradient-primary text-white rounded-3 p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Total Divisi</h6>
                                        <h3 class="mb-0 fw-bold">{{ $divisions->total() }}</h3>
                                    </div>
                                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 60px; height: 60px; background: rgba(255,255,255,0.2);">
                                        <i class="mdi mdi-layers" style="font-size: 1.75rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="stat-card bg-gradient-success text-white rounded-3 p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Divisi dengan Personel</h6>
                                        <h3 class="mb-0 fw-bold">
                                            {{ $divisions->filter(function ($div) {return $div->users_count > 0;})->count() }}
                                        </h3>
                                    </div>
                                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 60px; height: 60px; background: rgba(255,255,255,0.2);">
                                        <i class="mdi mdi-account-group" style="font-size: 1.75rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="stat-card bg-gradient-info text-white rounded-3 p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Divisi Kosong</h6>
                                        <h3 class="mb-0 fw-bold">
                                            {{ $divisions->filter(function ($div) {return $div->users_count === 0;})->count() }}
                                        </h3>
                                    </div>
                                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 60px; height: 60px; background: rgba(255,255,255,0.2);">
                                        <i class="mdi mdi-folder-outline" style="font-size: 1.75rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- NOTIFIKASI --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4"
                            role="alert">
                            <i class="mdi mdi-check-circle me-3" style="font-size: 1.25rem;"></i>
                            <div class="flex-grow-1">{{ session('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4"
                            role="alert">
                            <i class="mdi mdi-alert-circle me-3" style="font-size: 1.25rem;"></i>
                            <div class="flex-grow-1">{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- TABLE SECTION --}}
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted fw-semibold" style="width: 60px;">NO</th>
                                    <th class="py-3 text-muted fw-semibold">NAMA DIVISI</th>
                                    <th class="py-3 text-muted fw-semibold text-center" style="width: 140px;">TOTAL PERSONEL
                                    </th>
                                    <th class="py-3 text-muted fw-semibold text-center" style="width: 120px;">STATUS</th>
                                    <th class="py-3 text-muted fw-semibold">DIBUAT PADA</th>
                                    <th class="pe-4 py-3 text-muted fw-semibold text-center" style="width: 150px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($divisions as $index => $division)
                                    <tr class="border-bottom">
                                        {{-- Nomor --}}
                                        <td class="ps-4 fw-bold text-secondary text-center">
                                            {{ $divisions->firstItem() + $index }}
                                        </td>

                                        {{-- Nama Divisi --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="divisi-icon-wrapper bg-primary bg-opacity-10 text-primary rounded-2 d-flex align-items-center justify-content-center me-3"
                                                    style="width: 42px; height: 42px;">
                                                    <i class="mdi mdi-domain fs-5"></i>
                                                </div>
                                                <div>
                                                    <span
                                                        class="d-block fw-semibold text-dark mb-1">{{ $division->name }}</span>
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-identifier me-1"></i>
                                                        DIV-{{ str_pad($division->id, 3, '0', STR_PAD_LEFT) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Jumlah Personel --}}
                                        <td class="text-center">
                                            <div class="personel-count-wrapper">
                                                @if ($division->users_count > 0)
                                                    <div
                                                        class="personel-badge bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 px-3 py-2 d-inline-flex align-items-center">
                                                        <i class="mdi mdi-account-group me-2 text-success"></i>
                                                        <span class="fw-bold text-dark"
                                                            style="font-size: 1.1rem;">{{ $division->users_count }}</span>
                                                        <span class="ms-2 text-muted small">orang</span>
                                                    </div>
                                                @else
                                                    <div
                                                        class="personel-badge bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3 px-3 py-2 d-inline-flex align-items-center">
                                                        <i class="mdi mdi-account-off me-2 text-secondary"></i>
                                                        <span class="text-muted">0 personel</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Status --}}
                                        <td class="text-center">
                                            @if ($division->users_count > 0)
                                                {{-- UPDATE: Menggunakan Solid Green + Text White agar kontras --}}
                                                <span
                                                    class="status-badge badge bg-success text-white px-3 py-2 d-inline-flex align-items-center shadow-sm border-0">
                                                    <i class="mdi mdi-check-circle me-2 text-white"></i>
                                                    <span class="fw-bold" style="letter-spacing: 0.5px;">AKTIF</span>
                                                </span>
                                            @else
                                                {{-- Bagian Kosong dibuat sedikit lebih tegas tapi tetap beda --}}
                                                <span
                                                    class="status-badge badge bg-light text-secondary border border-secondary border-opacity-25 px-3 py-2 d-inline-flex align-items-center">
                                                    <i class="mdi mdi-clock-outline me-2"></i>
                                                    <span class="fw-medium">Kosong</span>
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Tanggal --}}
                                        <td>
                                            <div class="date-wrapper">
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-calendar-blank text-primary me-2"></i>
                                                    <div>
                                                        <span
                                                            class="d-block fw-medium text-dark">{{ $division->created_at->translatedFormat('d M Y') }}</span>
                                                        <small class="text-muted">
                                                            <i class="mdi mdi-clock-outline me-1"></i>
                                                            {{ $division->created_at->format('H:i') }} WIB
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="pe-4 text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('divisions.show', $division->id) }}"
                                                    class="btn btn-outline-info border px-3 d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" title="Lihat Detail" style="height: 36px;">
                                                    <i class="mdi mdi-eye me-1"></i>
                                                    <span class="d-none d-md-inline">Detail</span>
                                                </a>
                                                <a href="{{ route('divisions.edit', $division->id) }}"
                                                    class="btn btn-outline-warning border px-3 d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" title="Edit Data" style="height: 36px;">
                                                    <i class="mdi mdi-pencil me-1"></i>
                                                    <span class="d-none d-md-inline">Edit</span>
                                                </a>
                                                <form action="{{ route('divisions.destroy', $division->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Hapus divisi {{ $division->name }}? User terkait akan kehilangan data divisi.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-outline-danger border px-3 d-flex align-items-center justify-content-center"
                                                        title="Hapus" style="height: 36px;">
                                                        <i class="mdi mdi-delete me-1"></i>
                                                        <span class="d-none d-md-inline">Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="empty-state">
                                                <div class="empty-state-icon mb-3">
                                                    <i class="mdi mdi-folder-open-outline text-muted"
                                                        style="font-size: 4rem;"></i>
                                                </div>
                                                <h5 class="text-muted fw-normal mb-2">Tidak ada data divisi ditemukan</h5>
                                                <p class="text-muted small mb-4">
                                                    @if (request('search'))
                                                        Coba gunakan kata kunci lain atau <a
                                                            href="{{ route('divisions.index') }}"
                                                            class="text-primary">reset pencarian</a>
                                                    @else
                                                        Mulai dengan <a href="{{ route('divisions.create') }}"
                                                            class="text-primary">menambahkan divisi baru</a>
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($divisions->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Menampilkan {{ $divisions->firstItem() ?? 0 }} - {{ $divisions->lastItem() ?? 0 }} dari
                                {{ $divisions->total() }} divisi
                            </div>
                            <div class="pagination-wrapper">
                                {{ $divisions->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .card-body {
            padding: 2rem;
        }

        .icon-wrapper {
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);
        }

        .input-group-search {
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
        }

        .input-group-search:focus-within {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
        }

        .input-group-search .form-control {
            padding: 0.65rem 0.75rem;
            min-width: 250px;
        }

        .input-group-search .form-control:focus {
            box-shadow: none;
        }

        .stat-card {
            transition: transform 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .divisi-icon-wrapper {
            transition: all 0.3s ease;
        }

        tr:hover .divisi-icon-wrapper {
            background-color: rgba(var(--bs-primary-rgb), 0.2) !important;
            transform: scale(1.05);
        }

        /* Personel Badge Styles */
        .personel-badge {
            transition: all 0.3s ease;
            min-width: 100px;
            justify-content: center;
        }

        tr:hover .personel-badge {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .personel-badge.bg-success {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        /* Status Badge Styles */
        .status-badge {
            transition: all 0.3s ease;
            min-width: 90px;
            justify-content: center;
        }

        tr:hover .status-badge {
            transform: translateY(-2px);
        }

        /* Date Wrapper */
        .date-wrapper {
            transition: all 0.3s ease;
        }

        tr:hover .date-wrapper {
            transform: translateX(5px);
        }

        /* Button Styles */
        .btn-outline-info,
        .btn-outline-warning,
        .btn-outline-danger {
            transition: all 0.3s ease;
            margin: 0 1px;
        }

        .btn-outline-info:hover {
            background-color: var(--bs-info);
            color: white !important;
            border-color: var(--bs-info) !important;
        }

        .btn-outline-warning:hover {
            background-color: var(--bs-warning);
            color: white !important;
            border-color: var(--bs-warning) !important;
        }

        .btn-outline-danger:hover {
            background-color: var(--bs-danger);
            color: white !important;
            border-color: var(--bs-danger) !important;
        }

        .btn-group .btn {
            border-radius: 6px !important;
        }

        /* Empty State */
        .empty-state {
            opacity: 0.7;
        }

        /* Table Styles */
        .table thead th {
            border-bottom: 2px solid #e9ecef;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.03);
        }

        /* Gradient Backgrounds */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #1d976c 0%, #93f9b9 100%);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }

            .input-group-search .form-control {
                min-width: 180px;
            }

            .table-responsive {
                border: 0;
            }

            .btn-group {
                flex-wrap: nowrap;
            }

            .btn-group .btn span {
                display: none;
            }

            .btn-group .btn i {
                margin-right: 0 !important;
            }

            .personel-badge,
            .status-badge {
                min-width: auto;
                padding: 0.25rem 0.5rem !important;
                font-size: 0.875rem;
            }

            .personel-badge span.fw-bold {
                font-size: 1rem !important;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                margin-bottom: 1rem;
            }

            .personel-badge .ms-2,
            .personel-badge .text-muted.small {
                display: none;
            }
        }
    </style>

    <script>
        // Inisialisasi tooltip
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Highlight personel count yang lebih dari 0
            document.querySelectorAll('.personel-badge.bg-success').forEach(badge => {
                const count = parseInt(badge.querySelector('.fw-bold').textContent);
                if (count > 5) {
                    badge.classList.add('bg-success', 'bg-opacity-25');
                    badge.classList.remove('bg-success', 'bg-opacity-10');
                }
            });

            // Animasi saat halaman dimuat
            setTimeout(() => {
                document.querySelectorAll('.stat-card').forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        card.style.transition = 'all 0.5s ease';
                        card.offsetHeight; // Trigger reflow
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 100);
        });
    </script>
@endsection
