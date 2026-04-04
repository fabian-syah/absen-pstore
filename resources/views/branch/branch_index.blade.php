@extends('layout.master')

@section('title')
    Data User Non Karyawan
@endsection

@section('heading')
    Manajemen Non Karyawan
@endsection

@section('content')
    {{-- Custom CSS untuk UI Profesional --}}
    <style>
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
        
        .avatar-initial {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border-radius: 12px;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }

        .branch-closed {
            background-color: #fcfcfc;
            opacity: 0.8;
        }
        
        .branch-closed .branch-name {
            text-decoration: line-through;
            color: #6c757d;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    
                    {{-- Header & Toolbar --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                        
                        {{-- Judul --}}
                        <div>
                            <h4 class="card-title mb-1 fw-bold text-dark">
                                <i class="mdi mdi-office-building text-primary me-2"></i>Daftar Non Karyawan PStore
                            </h4>
                            <p class="text-muted small mb-0">Monitor status operasional dan jumlah karyawan per lokasi.</p>
                        </div>
                        
                        {{-- Aksi (Search + Add) --}}
                        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                            <form action="{{ route('branches.index') }}" method="GET" class="d-flex flex-grow-1">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                        <i class="mdi mdi-magnify"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                           placeholder="Cari cabang..." value="{{ request('search') }}">
                                    @if(request('search'))
                                        <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary" title="Reset">
                                            <i class="mdi mdi-close"></i>
                                        </a>
                                    @endif
                                    <button class="btn btn-primary" type="submit">Cari</button>
                                </div>
                            </form>

                            {{-- Tombol Tambah (Hanya Super Admin) --}}
                            @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                <a href="{{ route('branches.create') }}" class="btn btn-primary px-3 d-flex align-items-center gap-1 shadow-sm">
                                    <i class="mdi mdi-plus-circle-outline fs-5"></i> 
                                    <span class="d-none d-sm-inline">Tambah</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Alert Messages --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 bg-soft-success shadow-sm mb-4" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 bg-soft-danger shadow-sm mb-4" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- TABEL DESKTOP --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%" class="text-muted fw-semibold ps-4">#</th>
                                    <th width="35%" class="text-muted fw-semibold">Informasi Non Karyawan</th>
                                    <th width="15%" class="text-muted fw-semibold">Statistik</th>
                                    <th width="30%" class="text-muted fw-semibold">Lokasi</th>
                                    <th width="15%" class="text-center text-muted fw-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $key => $branch)
                                    <tr class="border-bottom {{ $branch->is_active ? '' : 'branch-closed' }}">
                                        <td class="ps-4 text-muted">{{ $key + 1 }}</td>
                                        
                                        {{-- Kolom Informasi Non Karyawan --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- Avatar --}}
                                                <div class="me-3 position-relative">
                                                    <div class="avatar-initial {{ $branch->is_active ? 'bg-primary text-white' : 'bg-secondary text-white' }} bg-gradient shadow-sm">
                                                        {{ strtoupper(substr($branch->name, 0, 2)) }}
                                                    </div>
                                                    {{-- Status Dot --}}
                                                    <span class="position-absolute top-0 start-100 translate-middle p-1 border border-light rounded-circle {{ $branch->is_active ? 'bg-success' : 'bg-danger' }}"></span>
                                                </div>

                                                <div>
                                                    <div class="fw-bold text-dark branch-name mb-1">{{ $branch->name }}</div>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <span class="badge bg-light text-muted border fw-normal">ID: {{ $branch->id }}</span>
                                                        
                                                        @if($branch->is_active)
                                                            <span class="badge bg-soft-success border border-success border-opacity-25 rounded-pill px-2">
                                                                <i class="mdi mdi-store-check me-1"></i>Buka
                                                            </span>
                                                        @else
                                                            <span class="badge bg-soft-danger border border-danger border-opacity-25 rounded-pill px-2">
                                                                <i class="mdi mdi-store-off me-1"></i>Tutup
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Kolom Statistik --}}
                                        <td>
                                            <div class="d-flex align-items-center text-dark">
                                                <div class="icon-box bg-soft-info rounded-circle p-2 me-2">
                                                    <i class="mdi mdi-account-group text-info"></i>
                                                </div>
                                                <div>
                                                    {{-- FIX: Menggunakan count() langsung agar tidak 0 --}}
                                                    <h6 class="mb-0 fw-bold">{{ $branch->users()->count() }}</h6>
                                                    <small class="text-muted">Karyawan</small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Kolom Lokasi --}}
                                        <td>
                                            <div class="d-flex">
                                                <i class="mdi mdi-map-marker text-danger mt-1 me-2"></i>
                                                <span class="text-muted small lh-sm">
                                                    {{ Str::limit($branch->address ?? 'Alamat belum diisi', 70) }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Kolom Aksi --}}
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('branches.show', $branch->id) }}" 
                                                   class="btn btn-sm btn-info text-white shadow-sm" title="Lihat Detail" data-bs-toggle="tooltip">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>

                                                @if(auth()->user()->role != 'audit')
                                                    <a href="{{ route('branches.edit', $branch->id) }}" 
                                                       class="btn btn-sm btn-warning text-white shadow-sm" title="Edit" data-bs-toggle="tooltip">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>

                                                    @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                                        <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Hapus" data-bs-toggle="tooltip">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="mdi mdi-office-building-marker-outline" style="font-size: 4rem; opacity: 0.2;"></i>
                                                <p class="mt-3 mb-0 fw-bold">Data Non Karyawan Tidak Ditemukan</p>
                                                <small>Coba kata kunci lain atau tambahkan data baru.</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE VIEW (Cards) --}}
                    <div class="d-md-none">
                        @forelse ($branches as $branch)
                            <div class="card mb-3 border {{ $branch->is_active ? 'border-light' : 'border-secondary bg-light' }} shadow-sm rounded-3">
                                <div class="card-body">
                                    {{-- Header Card --}}
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial {{ $branch->is_active ? 'bg-primary' : 'bg-secondary' }} text-white me-3" style="width:40px; height:40px; font-size: 0.9rem;">
                                                {{ strtoupper(substr($branch->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold {{ $branch->is_active ? 'text-dark' : 'text-muted text-decoration-line-through' }}">
                                                    {{ $branch->name }}
                                                </h6>
                                                <small class="text-muted">ID: {{ $branch->id }}</small>
                                            </div>
                                        </div>
                                        
                                        {{-- Status Badge Mobile --}}
                                        @if($branch->is_active)
                                            <span class="badge bg-soft-success rounded-pill">Buka</span>
                                        @else
                                            <span class="badge bg-soft-danger rounded-pill">Tutup</span>
                                        @endif
                                    </div>

                                    {{-- Info Stats --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="p-2 border rounded bg-white text-center">
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">TOTAL KARYAWAN</small>
                                                {{-- FIX: Menggunakan count() langsung agar tidak 0 --}}
                                                <span class="fw-bold text-dark fs-5">{{ $branch->users()->count() }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded bg-white d-flex align-items-center justify-content-center h-100">
                                                <i class="mdi mdi-map-marker text-danger me-1"></i>
                                                <small class="text-muted text-truncate" style="max-width: 100px;">
                                                    {{ $branch->address ? substr($branch->address, 0, 15).'...' : '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('branches.show', $branch->id) }}" class="btn btn-sm btn-outline-info flex-fill">
                                            <i class="mdi mdi-eye me-1"></i> Detail
                                        </a>
                                        @if(auth()->user()->role != 'audit')
                                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-outline-warning flex-fill">
                                                <i class="mdi mdi-pencil me-1"></i> Edit
                                            </a>
                                            @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                                <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="flex-fill"
                                                      onsubmit="return confirm('Hapus data?');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-alert-circle-outline fs-1"></i>
                                <p>Tidak ada data ditemukan</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection