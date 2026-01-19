@extends('layout.master')

@section('content')
{{-- Custom Style --}}
<style>
    :root {
        --primary: #4B49AC;
        --primary-light: rgba(75, 73, 172, 0.1);
        --success: #10b981;
        --warning: #f59e0b;
        --info: #0ea5e9;
        --secondary: #6b7280;
        --light-bg: #f9fafb;
        --border-color: #e5e7eb;
    }

    body { background-color: #f3f4f6; }

    .card-modern { 
        border: none; 
        border-radius: 16px; 
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.05); 
        background: #fff; 
        transition: all 0.3s ease;
    }

    .card-modern:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12); }

    .text-dark-contrast { color: #111827 !important; font-weight: 600; }

    .form-control-clean { 
        border: 1.5px solid var(--border-color); 
        border-radius: 10px; 
        padding: 12px 16px; 
        font-size: 0.9rem; 
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .form-control-clean:focus { 
        border-color: var(--primary); 
        box-shadow: 0 0 0 3px var(--primary-light); 
        background-color: #fff;
    }

    .table-custom thead th { 
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #374151; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.7rem; 
        letter-spacing: 0.8px; 
        border-bottom: 2px solid var(--border-color); 
        padding: 16px 15px;
    }

    .table-custom tbody td { 
        padding: 16px 15px; 
        vertical-align: middle; 
        border-bottom: 1px solid var(--border-color); 
        color: #374151;
    }

    .table-custom tbody tr { 
        transition: all 0.3s ease;
    }

    .table-custom tbody tr:hover { 
        background-color: #f9fafb; 
        transform: translateY(-2px);
    }

    .badge-soft-success { 
        background-color: #d1fae5; 
        color: #047857; 
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .badge-soft-warning { 
        background-color: #fef3c7; 
        color: #92400e; 
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .badge-soft-info { 
        background-color: #cffafe; 
        color: #0c4a6e; 
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .badge-soft-secondary { 
        background-color: #f3f4f6; 
        color: #4b5563; 
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .avatar-wrapper { 
        position: relative; 
        width: 48px; 
        height: 48px; 
        flex-shrink: 0;
    }

    .avatar-img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        border-radius: 12px; 
        border: 2px solid var(--border-color);
    }

    .nav-tabs-custom { 
        border-bottom: 2px solid var(--border-color); 
        background-color: var(--light-bg);
        border-radius: 16px 16px 0 0;
    }

    .nav-tabs-custom .nav-link { 
        border: none; 
        color: #6b7280; 
        font-weight: 600; 
        padding: 14px 24px; 
        background: transparent; 
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .nav-tabs-custom .nav-link:hover { 
        color: var(--primary);
        background-color: rgba(75, 73, 172, 0.05);
    }

    .nav-tabs-custom .nav-link.active { 
        color: var(--primary); 
        background: transparent; 
        border-bottom: 3px solid var(--primary);
    }

    .header-section {
        background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
        border-radius: 16px;
        padding: 32px;
        color: white;
        margin-bottom: 32px;
        box-shadow: 0 4px 20px rgba(75, 73, 172, 0.2);
    }

    .header-section h3 { 
        font-size: 2rem; 
        margin-bottom: 8px;
        font-weight: 700;
    }

    .header-section p { 
        opacity: 0.95;
        font-size: 1rem;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.4);
        color: white !important;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
        transform: translateY(-2px);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #3a3896;
        border-color: #3a3896;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(75, 73, 172, 0.3);
    }

    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .btn-dark {
        background-color: #1f2937;
        border-color: #1f2937;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-dark:hover {
        background-color: #111827;
        border-color: #111827;
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .filter-card {
        background-color: var(--light-bg);
    }

    .position-relative .mdi {
        pointer-events: none;
    }

    .user-info h6 { 
        font-size: 0.95rem; 
        color: #111827;
    }

    .user-info small { 
        color: #6b7280;
    }

    .location-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .location-info span {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
    }

    .salary-info h6 {
        color: #111827;
        font-size: 1rem;
    }

    .salary-info small {
        color: #6b7280;
        font-size: 0.8rem;
    }

    .pagination-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-top: 2px solid var(--border-color);
        background-color: var(--light-bg);
        border-radius: 0 0 16px 16px;
    }

    .pagination-section span {
        color: #374151;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .header-section {
            padding: 24px;
        }

        .header-section h3 {
            font-size: 1.5rem;
        }

        .table-custom thead th,
        .table-custom tbody td {
            padding: 12px 10px;
            font-size: 0.8rem;
        }

        .btn-header {
            padding: 10px 16px;
            font-size: 0.9rem;
        }

        .nav-tabs-custom .nav-link {
            padding: 10px 12px;
            font-size: 0.85rem;
        }

        .avatar-wrapper {
            width: 40px;
            height: 40px;
        }
    }

    .filter-form .row {
        gap: 12px;
    }

    .search-wrapper {
        position: relative;
    }

    .search-wrapper .mdi {
        font-size: 1.1rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            {{-- HEADER --}}
            <div class="header-section d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3 class="mb-2">📊 Master Data Gaji</h3>
                    <p class="mb-0">Kelola komponen gaji pokok, tunjangan, dan kategori karyawan dengan mudah dan efisien.</p>
                </div>
                <div class="d-flex gap-2">
                    {{-- TOMBOL EXPORT EXCEL --}}
                    {{-- Menggunakan request()->query() untuk menyertakan filter saat ini (search, branch, dll) --}}
                    <a href="{{ route('employee-salaries.export', request()->query()) }}" class="btn btn-header d-flex align-items-center gap-2 bg-success border-success text-white">
                        <i class="mdi mdi-file-excel"></i>
                        <span>Export Excel</span>
                    </a>

                    <button class="btn btn-header d-flex align-items-center gap-2">
                        <i class="mdi mdi-account-group"></i>
                        <span>Total: <strong>{{ $users->total() }}</strong> Karyawan</span>
                    </button>
                </div>
            </div>

            {{-- ALERT --}}
            @if(session('error'))
                <div class="alert alert-danger shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="mdi mdi-alert-circle fs-5"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="mdi mdi-check-circle fs-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- FILTER --}}
            <div class="card card-modern mb-4">
                <div class="card-body filter-card">
                    <form action="{{ route('employee-salaries.index') }}" method="GET" class="filter-form">
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="fw-bold text-dark small mb-2">🔍 Pencarian</label>
                                <div class="search-wrapper">
                                    <i class="mdi mdi-magnify position-absolute text-secondary" style="top: 50%; left: 15px; transform: translateY(-50%); font-size: 1.1rem;"></i>
                                    <input type="text" name="search" class="form-control form-control-clean ps-5" 
                                           placeholder="Cari Nama / ID Karyawan..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold text-dark small mb-2">🏢 Filter Cabang</label>
                                <select name="branch_id" class="form-select form-control-clean">
                                    <option value="">Semua Cabang</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold text-dark small mb-2">👥 Filter Divisi</label>
                                <select name="division_id" class="form-select form-control-clean">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-dark w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2">
                                    <i class="mdi mdi-check"></i> Terapkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABEL CONTENT --}}
            <div class="card card-modern">
                <div class="card-body p-0">
                    <div class="px-4 pt-3">
                        <ul class="nav nav-tabs nav-tabs-custom">
                            <li class="nav-item">
                                <a class="nav-link {{ !request('category') ? 'active' : '' }}" href="{{ route('employee-salaries.index') }}">
                                    <i class="mdi mdi-format-list-bulleted me-2"></i>Semua
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'employee' ? 'active' : '' }}" 
                                   href="{{ route('employee-salaries.index', [...request()->except(['category', 'page']), 'category' => 'employee']) }}">
                                    <i class="mdi mdi-briefcase me-2"></i>Karyawan Tetap
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'promotor' ? 'active' : '' }}" 
                                   href="{{ route('employee-salaries.index', [...request()->except(['category', 'page']), 'category' => 'promotor']) }}">
                                    <i class="mdi mdi-account-star me-2"></i>Promotor
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'freelance' ? 'active' : '' }}" 
                                   href="{{ route('employee-salaries.index', [...request()->except(['category', 'page']), 'category' => 'freelance']) }}">
                                    <i class="mdi mdi-laptop me-2"></i>Freelance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'unset' ? 'active' : '' }}" 
                                   href="{{ route('employee-salaries.index', [...request()->except(['category', 'page']), 'category' => 'unset']) }}">
                                    <i class="mdi mdi-alert-circle me-2"></i>Belum Diatur
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">PROFIL KARYAWAN</th>
                                    <th>LOKASI KERJA</th>
                                    <th>KATEGORI</th>
                                    <th>GAJI UTAMA</th>
                                    <th>TOTAL MASTER GAJI</th>
                                    <th class="text-center pe-4">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-wrapper">
                                                @if($user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="avatar-img" alt="user">
                                                @else
                                                    <div class="avatar-img bg-gradient text-white d-flex align-items-center justify-content-center fw-bold" style="background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%); font-size: 1.2rem;">{{ substr($user->name, 0, 1) }}</div>
                                                @endif
                                            </div>
                                            <div class="user-info">
                                                <h6 class="mb-1">{{ $user->name }}</h6>
                                                <small class="text-primary fw-bold">ID: {{ $user->login_id ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="location-info">
                                            <span><i class="mdi mdi-sitemap text-secondary me-2"></i><strong>{{ $user->division->name ?? 'Non-Div' }}</strong></span>
                                            <span><i class="mdi mdi-map-marker text-secondary me-2"></i>{{ $user->branch->name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        @if($user->employeeSalary)
                                            @php $cat = $user->employeeSalary->category; @endphp
                                            @if($cat == 'employee')
                                                <span class="badge badge-soft-success">KARYAWAN TETAP</span>
                                            @elseif($cat == 'promotor')
                                                <span class="badge badge-soft-info">PROMOTOR</span>
                                            @elseif($cat == 'freelance')
                                                <span class="badge badge-soft-warning">FREELANCE</span>
                                            @endif
                                        @else
                                            <span class="badge badge-soft-secondary">BELUM DIATUR</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($user->employeeSalary)
                                            @if($user->employeeSalary->category == 'freelance')
                                                <div class="salary-info">
                                                    <h6>Rp {{ number_format($user->employeeSalary->daily_salary, 0, ',', '.') }}</h6>
                                                    <small>/ kehadiran</small>
                                                </div>
                                            @elseif($user->employeeSalary->category == 'promotor')
                                                <div class="salary-info">
                                                    <h6>Rp {{ number_format($user->employeeSalary->promotor_bonus, 0, ',', '.') }}</h6>
                                                    <small class="text-success fw-bold">/ bulan (Insentif)</small>
                                                </div>
                                            @else
                                                <div class="salary-info">
                                                    <h6>Rp {{ number_format($user->employeeSalary->basic_salary, 0, ',', '.') }}</h6>
                                                    <small>/ bulan</small>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($user->employeeSalary && $user->employeeSalary->category == 'employee')
                                            @php
                                                $totalMaster = $user->employeeSalary->basic_salary + 
                                                               $user->employeeSalary->position_allowance + 
                                                               $user->employeeSalary->owner_privilege;
                                            @endphp
                                            <div class="salary-info">
                                                <h6 class="text-primary">Rp {{ number_format($totalMaster, 0, ',', '.') }}</h6>
                                                <small>Pokok + Tunj + Priv</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center pe-4">
                                        {{-- MODIFIKASI: Menggunakan 'id' sebagai parameter, bukan 'employee_salary' --}}
                                        <a href="{{ route('employee-salaries.edit', [
                                            'id' => $user->id, 
                                            'page' => request('page'),
                                            'search' => request('search'),
                                            'branch_id' => request('branch_id'),
                                            'category' => request('category')
                                        ]) }}" 
                                           class="btn btn-outline-primary btn-sm fw-bold px-3 py-2 d-inline-flex align-items-center gap-2" 
                                           style="border-radius: 8px; border-width: 2px;">
                                            <i class="mdi mdi-pencil"></i>Atur Gaji
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="mdi mdi-folder-open text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-muted fw-bold">Tidak ada data ditemukan</h5>
                                        <p class="text-secondary">Coba ubah filter atau lakukan pencarian kembali</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-section">
                        <span>Halaman <strong>{{ $users->currentPage() }}</strong> dari <strong>{{ $users->lastPage() }}</strong></span>
                        <div>{{ $users->links('pagination::bootstrap-4') }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection