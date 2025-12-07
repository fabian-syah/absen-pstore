@extends('layout.master')

@section('title')
    Data Cabang
@endsection

@section('heading')
    Manajemen Cabang
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    {{-- Header Section --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1 fw-bold text-primary">
                                <i class="mdi mdi-office-building-outline me-2"></i>Daftar Cabang PStore
                            </h4>
                            <p class="text-muted small mb-0">Kelola semua cabang toko Anda</p>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            {{-- Form Pencarian --}}
                            <form action="{{ route('branches.index') }}" method="GET" class="d-flex">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Cari nama/alamat..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('branches.index') }}" class="btn btn-outline-danger" title="Reset">
                                            <i class="mdi mdi-close"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>

                            {{-- Tombol Tambah: Hanya muncul untuk Super Admin --}}
                            @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                                    <i class="mdi mdi-plus-circle me-1"></i> Tambah Cabang
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Notifikasi --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-check-circle fs-4 me-2"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-alert-circle fs-4 me-2"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Tabel Desktop --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="border-bottom">
                                    <th width="5%" class="text-muted fw-semibold">#</th>
                                    <th class="text-muted fw-semibold">Nama Cabang</th>
                                    <th class="text-muted fw-semibold">Alamat</th>
                                    <th width="15%" class="text-center text-muted fw-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $key => $branch)
                                    <tr class="border-bottom">
                                        <td class="text-muted">{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-primary bg-gradient fs-6 px-3 py-2">
                                                        {{ strtoupper(substr($branch->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $branch->name }}</div>
                                                    <small class="text-muted">ID: {{ $branch->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <i class="mdi mdi-map-marker text-danger me-1"></i>
                                                {{ Str::limit($branch->address ?? 'Alamat belum diisi', 60) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                {{-- Tombol LIHAT --}}
                                                <a href="{{ route('branches.show', $branch->id) }}"
                                                    class="btn btn-sm btn-info text-white" 
                                                    title="Lihat Detail" 
                                                    data-bs-toggle="tooltip">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>

                                                {{-- Tombol EDIT & DELETE: Tidak muncul untuk Audit --}}
                                                @if(auth()->user()->role != 'audit')
                                                    <a href="{{ route('branches.edit', $branch->id) }}"
                                                        class="btn btn-sm btn-warning text-white" 
                                                        title="Edit Data"
                                                        data-bs-toggle="tooltip">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>

                                                    {{-- Tombol HAPUS: Hanya Super Admin --}}
                                                    @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                                        <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Yakin ingin menghapus cabang ini? Data terkait mungkin akan terpengaruh.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Hapus"
                                                                data-bs-toggle="tooltip">
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
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                @if(request('search'))
                                                    <i class="mdi mdi-magnify-remove-outline" style="font-size: 4rem; opacity: 0.3;"></i>
                                                    <p class="mt-3 mb-0">Cabang "{{ request('search') }}" tidak ditemukan.</p>
                                                    <a href="{{ route('branches.index') }}" class="btn btn-link text-decoration-none">Reset Pencarian</a>
                                                @else
                                                    <i class="mdi mdi-office-building-marker-outline" style="font-size: 4rem; opacity: 0.3;"></i>
                                                    <p class="mt-3 mb-0">Belum ada data cabang</p>
                                                    <small>Klik tombol "Tambah Cabang" untuk menambahkan cabang baru</small>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Card Mobile View --}}
                    <div class="d-md-none">
                        @forelse ($branches as $key => $branch)
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="me-3">
                                            <span class="badge bg-primary bg-gradient fs-6 px-3 py-2">
                                                {{ strtoupper(substr($branch->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark">{{ $branch->name }}</h6>
                                            <div class="text-muted small mb-1">
                                                <i class="mdi mdi-identifier"></i> ID: {{ $branch->id }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="mdi mdi-map-marker text-danger"></i>
                                                {{ Str::limit($branch->address ?? 'Alamat belum diisi', 50) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                        {{-- Tombol LIHAT --}}
                                        <a href="{{ route('branches.show', $branch->id) }}"
                                            class="btn btn-sm btn-info text-white flex-fill">
                                            <i class="mdi mdi-eye me-1"></i> Lihat
                                        </a>

                                        {{-- Tombol EDIT & DELETE: Tidak muncul untuk Audit --}}
                                        @if(auth()->user()->role != 'audit')
                                            <a href="{{ route('branches.edit', $branch->id) }}"
                                                class="btn btn-sm btn-warning text-white flex-fill">
                                                <i class="mdi mdi-pencil me-1"></i> Edit
                                            </a>

                                            {{-- Tombol HAPUS: Hanya Super Admin --}}
                                            @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                                <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                                    class="flex-fill"
                                                    onsubmit="return confirm('Yakin ingin menghapus cabang ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger w-100">
                                                        <i class="mdi mdi-delete me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                @if(request('search'))
                                    <i class="mdi mdi-magnify-remove-outline" style="font-size: 4rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">Pencarian tidak ditemukan.</p>
                                    <a href="{{ route('branches.index') }}" class="btn btn-link text-decoration-none">Reset</a>
                                @else
                                    <i class="mdi mdi-office-building-marker-outline text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">Belum ada data cabang</p>
                                    <small class="text-muted">Klik tombol "Tambah Cabang" untuk menambahkan</small>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    {{-- Optional: Tambahkan CSS Custom --}}
    <style>
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
            transform: translateY(-1px);
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .btn {
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }

        .badge {
            font-weight: 600;
            letter-spacing: 1px;
        }
    </style>
@endsection