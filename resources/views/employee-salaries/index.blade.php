@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-12">
        
        {{-- HEADER & STATS --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Master Data Gaji</h3>
                <p class="text-muted mb-0">Kelola komponen gaji pokok, tunjangan, dan kategori karyawan.</p>
            </div>
            <div>
                <span class="badge bg-primary fs-6 p-2 rounded shadow-sm">
                    Total: {{ $users->total() }} Karyawan
                </span>
            </div>
        </div>

        {{-- CARD FILTER --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3 bg-light rounded">
                <form action="{{ route('employee-salaries.index') }}" method="GET">
                    {{-- Hidden Input untuk menjaga kategori tab yang aktif --}}
                    <input type="hidden" name="category" value="{{ request('category') }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="small fw-bold text-secondary mb-1">Cari Karyawan</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify text-primary"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Nama atau ID..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-secondary mb-1">Cabang</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-secondary mb-1">Divisi</label>
                            <select name="division_id" class="form-select form-select-sm">
                                <option value="">Semua Divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm">
                                <i class="mdi mdi-filter-variant"></i> Terapkan
                            </button>
                            @if(request()->hasAny(['search', 'branch_id', 'division_id', 'category']))
                                <a href="{{ route('employee-salaries.index') }}" class="btn btn-secondary btn-sm w-50" title="Reset Filter">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- CARD TABEL --}}
        <div class="card shadow border-0">
            <div class="card-body">

                {{-- TABS NAVIGASI KATEGORI --}}
                <ul class="nav nav-tabs nav-pills mb-4 border-bottom-0 gap-2" id="categoryTabs">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('category') ? 'active bg-primary text-white shadow-sm' : 'bg-light text-muted' }} rounded-pill px-4 fw-bold small" 
                           href="{{ route('employee-salaries.index') }}">
                           Semua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'employee' ? 'active bg-success text-white shadow-sm' : 'bg-light text-muted' }} rounded-pill px-4 fw-bold small" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'employee'])) }}">
                           <i class="mdi mdi-account-tie me-1"></i> Tetap
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'promotor' ? 'active bg-info text-white shadow-sm' : 'bg-light text-muted' }} rounded-pill px-4 fw-bold small" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'promotor'])) }}">
                           <i class="mdi mdi-bullhorn me-1"></i> Promotor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'freelance' ? 'active bg-warning text-white shadow-sm' : 'bg-light text-muted' }} rounded-pill px-4 fw-bold small" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'freelance'])) }}">
                           <i class="mdi mdi-account-clock me-1"></i> Freelance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'unset' ? 'active bg-secondary text-white shadow-sm' : 'bg-light text-muted' }} rounded-pill px-4 fw-bold small" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'unset'])) }}">
                           Belum Set
                        </a>
                    </li>
                </ul>

                {{-- TABEL DATA --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-borderless">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="py-3 ps-3 rounded-start">Karyawan</th>
                                <th class="py-3">Posisi & Cabang</th>
                                <th class="py-3">Kategori</th>
                                <th class="py-3">Gaji Utama</th>
                                <th class="py-3">Tunjangan / Bonus</th>
                                <th class="py-3 text-center rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr class="border-bottom">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        @if($user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="rounded-circle me-3 shadow-sm" width="40" height="40" alt="user">
                                        @else
                                            <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px; font-weight: bold;">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">ID: {{ $user->login_id ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block fw-bold text-dark" style="font-size: 0.85rem;">{{ $user->division->name ?? 'Non-Div' }}</span>
                                    <span class="d-block text-muted small"><i class="mdi mdi-map-marker-outline"></i> {{ $user->branch->name ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($user->employeeSalary)
                                        @php $cat = $user->employeeSalary->category; @endphp
                                        <span class="badge rounded-pill px-3 py-2 
                                            {{ $cat == 'employee' ? 'bg-soft-success text-success' : 
                                              ($cat == 'promotor' ? 'bg-soft-info text-info' : 'bg-soft-warning text-warning') }}">
                                            {{ ucfirst($cat) }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary rounded-pill px-3 py-2 border">Belum Set</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">
                                    @if($user->employeeSalary)
                                        @if($user->employeeSalary->category == 'freelance')
                                            Rp {{ number_format($user->employeeSalary->daily_salary, 0, ',', '.') }} <span class="text-muted fw-normal small">/hari</span>
                                        @else
                                            Rp {{ number_format($user->employeeSalary->basic_salary, 0, ',', '.') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($user->employeeSalary)
                                        @if($user->employeeSalary->category == 'employee')
                                            <div class="small text-muted">Jbt: <span class="text-dark fw-bold">Rp {{ number_format($user->employeeSalary->position_allowance, 0, ',', '.') }}</span></div>
                                            <div class="small text-muted">Priv: <span class="text-dark fw-bold">Rp {{ number_format($user->employeeSalary->owner_privilege, 0, ',', '.') }}</span></div>
                                        @elseif($user->employeeSalary->category == 'promotor')
                                            <div class="small text-success fw-bold"><i class="mdi mdi-star"></i> Bonus: Rp {{ number_format($user->employeeSalary->promotor_bonus, 0, ',', '.') }}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('employee-salaries.edit', $user->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="mdi mdi-pencil"></i> Atur
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="bg-light rounded-circle p-3 mb-2">
                                            <i class="mdi mdi-account-search text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="text-muted">Tidak ada data karyawan ditemukan</h6>
                                        <small class="text-muted">Coba ubah filter pencarian Anda.</small>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <small class="text-muted">
                        Menampilkan {{ $users->firstItem() ?? 0 }} sampai {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
                    </small>
                    <div>
                        {{ $users->links('pagination::bootstrap-4') }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- CSS Tambahan agar lebih cantik --}}
<style>
    .bg-soft-primary { background-color: rgba(75, 73, 172, 0.1) !important; color: #4B49AC !important; }
    .bg-soft-success { background-color: rgba(26, 188, 156, 0.1) !important; color: #1abc9c !important; }
    .bg-soft-info { background-color: rgba(52, 152, 219, 0.1) !important; color: #3498db !important; }
    .bg-soft-warning { background-color: rgba(241, 196, 15, 0.1) !important; color: #f1c40f !important; }
    
    .table-hover tbody tr:hover {
        background-color: #fcfcfc;
    }
    .pagination .page-item.active .page-link {
        background-color: #4B49AC;
        border-color: #4B49AC;
    }
    .nav-pills .nav-link {
        transition: all 0.2s;
    }
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #e9ecef;
        transform: translateY(-1px);
    }
</style>
@endsection