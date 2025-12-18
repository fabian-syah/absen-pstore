@extends('layout.master')

@section('content')
{{-- Custom Style --}}
<style>
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: #fff; }
    .text-dark-contrast { color: #111 !important; }
    .form-control-clean { border: 1px solid #e0e0e0; border-radius: 10px; padding: 10px 15px; font-size: 0.9rem; transition: all 0.3s; }
    .form-control-clean:focus { border-color: #4B49AC; box-shadow: 0 0 0 3px rgba(75, 73, 172, 0.1); }
    .table-custom thead th { background-color: #f8f9fa; color: #555; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; padding: 15px; }
    .table-custom tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
    .table-custom tbody tr:hover { background-color: #fcfcfc; }
    .badge-soft-success { background-color: #e6fffa; color: #047857; }
    .badge-soft-warning { background-color: #fffbeb; color: #b45309; }
    .badge-soft-info { background-color: #ebf8ff; color: #0369a1; }
    .badge-soft-secondary { background-color: #f3f4f6; color: #374151; }
    .avatar-wrapper { position: relative; width: 45px; height: 45px; }
    .avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }
    .nav-tabs-custom { border-bottom: 2px solid #eee; }
    .nav-tabs-custom .nav-link { border: none; color: #666; font-weight: 600; padding: 10px 20px; background: transparent; border-bottom: 3px solid transparent; }
    .nav-tabs-custom .nav-link.active { color: #4B49AC; background: transparent; border-bottom: 3px solid #4B49AC; }
</style>

<div class="row">
    <div class="col-12">
        
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark-contrast mb-1">Master Data Gaji</h3>
                <p class="text-muted mb-0">Kelola komponen gaji pokok, tunjangan, dan kategori karyawan.</p>
            </div>
            <div>
                <button class="btn btn-primary btn-icon-text text-white fw-bold px-4 py-2" style="border-radius: 10px;">
                    <i class="mdi mdi-account-group me-2"></i> Total: {{ $users->total() }} Karyawan
                </button>
            </div>
        </div>

        {{-- ALERT JIKA ADA ERROR --}}
        @if(session('error'))
            <div class="alert alert-danger shadow-sm border-0 mb-4">
                <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
            </div>
        @endif

        {{-- FILTER --}}
        <div class="card card-modern mb-4">
            <div class="card-body">
                <form action="{{ route('employee-salaries.index') }}" method="GET">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="fw-bold text-dark small mb-1">Pencarian</label>
                            <div class="position-relative">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="top: 12px; left: 15px; font-size: 1.2rem;"></i>
                                <input type="text" name="search" class="form-control form-control-clean ps-5" 
                                       placeholder="Cari Nama / ID Karyawan..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold text-dark small mb-1">Filter Cabang</label>
                            <select name="branch_id" class="form-select form-control-clean">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold text-dark small mb-1">Filter Divisi</label>
                            <select name="division_id" class="form-select form-control-clean">
                                <option value="">Semua Divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 fw-bold py-2" style="border-radius: 10px;">Terapkan</button>
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
                        <li class="nav-item"><a class="nav-link {{ !request('category') ? 'active' : '' }}" href="{{ route('employee-salaries.index') }}">Semua</a></li>
                        <li class="nav-item"><a class="nav-link {{ request('category') == 'employee' ? 'active' : '' }}" href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'employee'])) }}">Karyawan Tetap</a></li>
                        <li class="nav-item"><a class="nav-link {{ request('category') == 'promotor' ? 'active' : '' }}" href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'promotor'])) }}">Promotor</a></li>
                        <li class="nav-item"><a class="nav-link {{ request('category') == 'freelance' ? 'active' : '' }}" href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'freelance'])) }}">Freelance</a></li>
                        <li class="nav-item"><a class="nav-link {{ request('category') == 'unset' ? 'active' : '' }}" href="{{ route('employee-salaries.index', array_merge(request()->except(['category', 'page']), ['category' => 'unset'])) }}">Belum Diatur</a></li>
                    </ul>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">PROFIL KARYAWAN</th>
                                <th>LOKASI KERJA</th>
                                <th>KATEGORI GAJI</th>
                                <th>GAJI UTAMA / INSENTIF</th>
                                <th>DETAIL TUNJANGAN & CATATAN</th>
                                <th class="text-center pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-wrapper me-3">
                                            @if($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="avatar-img shadow-sm" alt="user">
                                            @else
                                                <div class="avatar-img bg-secondary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 1.2rem;">{{ substr($user->name, 0, 1) }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark-contrast" style="font-size: 0.95rem;">{{ $user->name }}</h6>
                                            <small class="text-primary fw-bold" style="font-size: 0.75rem;">ID: {{ $user->login_id ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;"><i class="mdi mdi-sitemap me-1 text-muted"></i> {{ $user->division->name ?? 'Non-Div' }}</span>
                                        <span class="text-secondary small mt-1"><i class="mdi mdi-map-marker me-1"></i> {{ $user->branch->name ?? '-' }}</span>
                                    </div>
                                </td>

                                <td>
                                    @if($user->employeeSalary)
                                        @php $cat = $user->employeeSalary->category; @endphp
                                        @if($cat == 'employee') <span class="badge badge-soft-success">KARYAWAN TETAP</span>
                                        @elseif($cat == 'promotor') <span class="badge badge-soft-info">PROMOTOR</span>
                                        @elseif($cat == 'freelance') <span class="badge badge-soft-warning">FREELANCE</span>
                                        @endif
                                    @else
                                        <span class="badge badge-soft-secondary">BELUM DIATUR</span>
                                    @endif
                                </td>

                                <td>
                                    @if($user->employeeSalary)
                                        @if($user->employeeSalary->category == 'freelance')
                                            <h6 class="mb-0 fw-800 text-dark-contrast">Rp {{ number_format($user->employeeSalary->daily_salary, 0, ',', '.') }}</h6>
                                            <small class="text-muted">/ kehadiran</small>
                                        @elseif($user->employeeSalary->category == 'promotor')
                                            <h6 class="mb-0 fw-800 text-dark-contrast">Rp {{ number_format($user->employeeSalary->promotor_bonus, 0, ',', '.') }}</h6>
                                            <small class="text-success fw-bold">/ bulan (Insentif)</small>
                                        @else
                                            <h6 class="mb-0 fw-800 text-dark-contrast">Rp {{ number_format($user->employeeSalary->basic_salary, 0, ',', '.') }}</h6>
                                            <small class="text-muted">/ bulan</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- DETAIL TUNJANGAN & CATATAN --}}
                                <td style="min-width: 200px;">
                                    @if($user->employeeSalary)
                                        <div class="d-flex flex-column gap-1">
                                            @if($user->employeeSalary->category == 'employee')
                                                <small class="text-dark fw-bold">Jabatan: <span class="text-secondary fw-normal">Rp {{ number_format($user->employeeSalary->position_allowance, 0, ',', '.') }}</span></small>
                                                <small class="text-dark fw-bold">Privilege: <span class="text-secondary fw-normal">Rp {{ number_format($user->employeeSalary->owner_privilege, 0, ',', '.') }}</span></small>
                                                @if($user->employeeSalary->use_privilege_mode)
                                                    <span class="badge bg-success mt-1" style="width: fit-content; font-size: 0.6rem;"><i class="mdi mdi-shield-check"></i> Bebas Potongan</span>
                                                @endif
                                            @endif
                                            
                                            {{-- TAMPILKAN CATATAN JIKA ADA --}}
                                            @if($user->employeeSalary->notes)
                                                <div class="mt-2 p-2 bg-light rounded border border-light">
                                                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;"><i class="mdi mdi-note-text-outline"></i> Catatan:</small>
                                                    <small class="text-dark fst-italic" style="font-size: 0.75rem; display: block; line-height: 1.2;">
                                                        "{{ Str::limit($user->employeeSalary->notes, 50) }}"
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>

                                <td class="text-center pe-4">
                                    <a href="{{ route('employee-salaries.edit', $user->id) }}" class="btn btn-outline-primary btn-sm fw-bold px-3 py-1" style="border-radius: 8px; border-width: 2px;">Atur Gaji</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <h5 class="text-muted fw-bold">Tidak ada data ditemukan</h5>
                                    <p class="text-muted small">
                                        @if(request('search'))
                                            User "<strong>{{ request('search') }}</strong>" tidak ditemukan atau merupakan Admin.
                                        @else
                                            Belum ada data karyawan.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center p-4 border-top bg-light" style="border-radius: 0 0 16px 16px;">
                    <span class="text-dark fw-bold small">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>
                    <div>{{ $users->links('pagination::bootstrap-4') }}</div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection