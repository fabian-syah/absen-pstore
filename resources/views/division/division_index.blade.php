@extends('layout.master')

@section('title', 'Data Divisi')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                
                {{-- HEADER & TOOLS SECTION --}}
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h4 class="card-title mb-1 text-dark font-weight-bold">
                            <i class="mdi mdi-layers-outline text-primary me-2"></i>Daftar Divisi
                        </h4>
                        <p class="text-muted small mb-0">
                            Kelola data divisi dan pantau total personel.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        {{-- Search Form --}}
                        <form action="{{ route('divisions.index') }}" method="GET" class="d-flex align-items-center">
                            <div class="input-group input-group-sm bg-white border rounded-pill px-2" style="width: 250px;">
                                <span class="input-group-text border-0 bg-transparent p-1">
                                    <i class="mdi mdi-magnify text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-0 bg-transparent shadow-none" 
                                       placeholder="Cari divisi..." value="{{ request('search') }}">
                                @if(request('search'))
                                    <a href="{{ route('divisions.index') }}" class="text-danger p-1" title="Hapus Filter">
                                        <i class="mdi mdi-close"></i>
                                    </a>
                                @endif
                            </div>
                        </form>

                        {{-- Tombol Tambah --}}
                        <a href="{{ route('divisions.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2 d-flex align-items-center">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Baru
                        </a>
                    </div>
                </div>

                {{-- NOTIFIKASI --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show p-2 px-3 mb-3 rounded" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show p-2 px-3 mb-3 rounded" role="alert">
                        <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- TABLE SECTION --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center py-3 text-muted" width="5%">NO</th>
                                <th class="py-3 text-muted" width="30%">NAMA DIVISI</th>
                                <th class="py-3 text-muted text-center" width="20%">TOTAL PERSONEL</th>
                                <th class="py-3 text-muted" width="20%">DIBUAT PADA</th>
                                <th class="py-3 text-muted text-center" width="15%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($divisions as $index => $division)
                                <tr>
                                    {{-- Nomor --}}
                                    <td class="text-center fw-bold text-secondary">
                                        {{ $divisions->firstItem() + $index }}
                                    </td>
                                    
                                    {{-- Nama Divisi --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 35px; height: 35px;">
                                                <i class="mdi mdi-domain fs-5"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block">{{ $division->name }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Jumlah Personel (FIX "NYARU") --}}
                                    <td class="text-center">
                                        @if($division->users_count > 0)
                                            {{-- Style: Background Hijau Muda, Teks Hijau Tua (Kontras Tinggi) --}}
                                            <span class="badge rounded-pill bg-success bg-opacity-25 text-success px-3 py-2 border border-success border-opacity-25">
                                                <i class="mdi mdi-account-group me-1"></i> 
                                                <span class="fw-bold" style="font-size: 1rem;">{{ $division->users_count }}</span>
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                                0 Personel
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Tanggal --}}
                                    <td class="text-muted small">
                                        <i class="mdi mdi-calendar-blank me-1"></i>
                                        {{ $division->created_at->translatedFormat('d M Y') }}
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('divisions.show', $division->id) }}" 
                                               class="btn btn-sm btn-info text-white" 
                                               data-bs-toggle="tooltip" title="Lihat Detail">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('divisions.edit', $division->id) }}" 
                                               class="btn btn-sm btn-warning text-dark" 
                                               data-bs-toggle="tooltip" title="Edit Data">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('divisions.destroy', $division->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Hapus divisi {{ $division->name }}? User terkait akan kehilangan data divisi.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                            <i class="mdi mdi-folder-open-outline" style="font-size: 4rem;"></i>
                                            <p class="mt-2 fw-bold">Tidak ada data divisi ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-4">
                    {{ $divisions->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Tambahan CSS kecil untuk mempercantik jika Bootstrap standar belum cukup */
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .bg-opacity-25 { --bs-bg-opacity: 0.25; }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection