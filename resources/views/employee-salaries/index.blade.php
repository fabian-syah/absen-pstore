@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        
        {{-- CARD FILTER --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-body py-3">
                <form action="{{ route('employee-salaries.index') }}" method="GET">
                    {{-- Pertahankan kategori saat filter lain digunakan --}}
                    <input type="hidden" name="category" value="{{ request('category') }}">

                    <div class="row gx-2 align-items-center">
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted">Cari Nama</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Nama Karyawan..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted">Filter Cabang</label>
                            <select name="branch_id" class="form-select form-control">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted">Filter Divisi</label>
                            <select name="division_id" class="form-select form-control">
                                <option value="">Semua Divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary text-white w-100 me-2 mt-4">
                                Filter
                            </button>
                            <a href="{{ route('employee-salaries.index') }}" class="btn btn-light w-50 mt-4 border">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- CARD TABEL --}}
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Master Data Gaji</h4>
                        <p class="text-muted mb-0">Atur template gaji pokok & tunjangan karyawan.</p>
                    </div>
                </div>

                {{-- TABS KATEGORI (PEMISAH) --}}
                <ul class="nav nav-tabs nav-pills mb-4 border-bottom-0">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('category') ? 'active bg-primary text-white shadow' : 'bg-light text-muted' }} me-2 rounded-pill px-4" 
                           href="{{ route('employee-salaries.index') }}">
                           Semua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'employee' ? 'active bg-success text-white shadow' : 'bg-light text-muted' }} me-2 rounded-pill px-4" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except('category'), ['category' => 'employee'])) }}">
                           <i class="mdi mdi-account-tie"></i> Karyawan Tetap
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'promotor' ? 'active bg-info text-white shadow' : 'bg-light text-muted' }} me-2 rounded-pill px-4" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except('category'), ['category' => 'promotor'])) }}">
                           <i class="mdi mdi-bullhorn"></i> Promotor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'freelance' ? 'active bg-warning text-white shadow' : 'bg-light text-muted' }} me-2 rounded-pill px-4" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except('category'), ['category' => 'freelance'])) }}">
                           <i class="mdi mdi-account-clock"></i> Freelance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == 'unset' ? 'active bg-secondary text-white shadow' : 'bg-light text-muted' }} rounded-pill px-4" 
                           href="{{ route('employee-salaries.index', array_merge(request()->except('category'), ['category' => 'unset'])) }}">
                           Belum Diatur
                        </a>
                    </li>
                </ul>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Karyawan</th>
                                <th>Divisi & Cabang</th>
                                <th>Kategori</th>
                                <th>Gaji Utama</th>
                                <th>Tunjangan / Bonus</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-sm rounded-circle me-3" alt="image">
                                        @else
                                            <div class="img-sm rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3 text-white fw-bold">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->login_id ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="badge badge-opacity-primary mb-1">{{ $user->division->name ?? 'Non-Div' }}</div>
                                    <div class="d-block text-muted small"><i class="mdi mdi-map-marker"></i> {{ $user->branch->name ?? 'Non-Cabang' }}</div>
                                </td>
                                <td>
                                    @if($user->employeeSalary)
                                        @if($user->employeeSalary->category == 'employee')
                                            <span class="badge badge-success rounded-pill">Karyawan Tetap</span>
                                        @elseif($user->employeeSalary->category == 'promotor')
                                            <span class="badge badge-info rounded-pill">Promotor</span>
                                        @elseif($user->employeeSalary->category == 'freelance')
                                            <span class="badge badge-warning rounded-pill">Freelance</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary rounded-pill">Belum Set</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">
                                    @if($user->employeeSalary)
                                        @if($user->employeeSalary->category == 'freelance')
                                            Rp {{ number_format($user->employeeSalary->daily_salary, 0, ',', '.') }} <span class="text-muted small fw-normal">/hari</span>
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
                                            <div class="small">Jabatan: Rp {{ number_format($user->employeeSalary->position_allowance, 0, ',', '.') }}</div>
                                            <div class="small">Privilege: Rp {{ number_format($user->employeeSalary->owner_privilege, 0, ',', '.') }}</div>
                                        @elseif($user->employeeSalary->category == 'promotor')
                                            <div class="small text-success">Bonus: Rp {{ number_format($user->employeeSalary->promotor_bonus, 0, ',', '.') }}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('employee-salaries.edit', $user->id) }}" class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm">
                                        <i class="mdi mdi-pencil"></i> Atur
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-account-off text-muted mb-2" style="font-size: 3rem;"></i>
                                        <h5 class="text-muted">Tidak ada data karyawan ditemukan</h5>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Tabs agar lebih modern */
    .nav-pills .nav-link {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
    }
    .nav-pills .nav-link.active {
        border: 1px solid transparent;
    }
</style>
@endsection