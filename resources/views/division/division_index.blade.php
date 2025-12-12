@extends('layout.master')

@section('title', 'Data Divisi')

@section('heading', 'Manajemen Divisi')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                
                {{-- HEADER SECTION: Judul & Action --}}
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <div>
                        <h4 class="card-title mb-1 text-primary">Daftar Divisi</h4>
                        <p class="text-muted small mb-0">Kelola data divisi dan pantau jumlah personel.</p>
                    </div>
                    
                    {{-- Tombol Tambah --}}
                    <a href="{{ route('divisions.create') }}" class="btn btn-primary btn-icon-text">
                        <i class="mdi mdi-plus-circle btn-icon-prepend"></i> Tambah Divisi
                    </a>
                </div>

                {{-- SEARCH & FILTER SECTION --}}
                <div class="row mb-3">
                    <div class="col-md-4 ms-auto">
                        <form action="{{ route('divisions.index') }}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-magnify text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Cari nama divisi..." value="{{ request('search') }}">
                                @if(request('search'))
                                    <a href="{{ route('divisions.index') }}" class="btn btn-light border" title="Reset">
                                        <i class="mdi mdi-close text-danger"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ALERTS --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- TABLE SECTION --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="35%">Nama Divisi</th>
                                <th width="20%" class="text-center">Total Personel</th>
                                <th width="20%">Dibuat Pada</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($divisions as $index => $division)
                                <tr>
                                    <td class="text-center">{{ $divisions->firstItem() + $index }}</td>
                                    
                                    {{-- Nama Divisi --}}
                                    <td class="fw-bold text-dark">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                                <i class="mdi mdi-domain"></i>
                                            </div>
                                            {{ $division->name }}
                                        </div>
                                    </td>

                                    {{-- Jumlah Karyawan (Badge) --}}
                                    <td class="text-center">
                                        @if($division->users_count > 0)
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">
                                                <i class="mdi mdi-account-group me-1"></i> {{ $division->users_count }} Orang
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                                Kosong
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Tanggal --}}
                                    <td class="text-muted small">
                                        <i class="mdi mdi-calendar-clock me-1"></i>
                                        {{ $division->created_at->translatedFormat('d M Y') }}
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            {{-- Show --}}
                                            <a href="{{ route('divisions.show', $division->id) }}" class="btn btn-outline-info btn-sm" title="Lihat Anggota">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            {{-- Edit --}}
                                            <a href="{{ route('divisions.edit', $division->id) }}" class="btn btn-outline-warning btn-sm" title="Edit Data">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            {{-- Delete --}}
                                            <form action="{{ route('divisions.destroy', $division->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus divisi {{ $division->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-folder-remove-outline text-muted mb-3" style="font-size: 3rem;"></i>
                                            <h5 class="text-muted">Data Divisi Tidak Ditemukan</h5>
                                            @if(request('search'))
                                                <p class="text-muted small">Pencarian untuk "{{ request('search') }}" tidak menghasilkan data apapun.</p>
                                                <a href="{{ route('divisions.index') }}" class="btn btn-sm btn-light mt-2">Reset Filter</a>
                                            @else
                                                <p class="text-muted small">Belum ada divisi yang ditambahkan.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4 d-flex justify-content-end">
                    {{ $divisions->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection