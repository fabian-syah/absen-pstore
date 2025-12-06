@extends('layout.master')

@section('title', $pageTitle ?? 'Daftar Inventaris')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $pageTitle ?? 'Daftar Inventaris' }}</h4>
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    
                    {{-- TOMBOL TAMBAH --}}
                    @if(in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'security', 'user_biasa']))
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Barang
                        </a>
                    @endif

                    {{-- TOMBOL SWITCH VIEW --}}
                    <div class="btn-group" role="group">
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm {{ request()->routeIs('inventory.index') ? 'btn-info' : 'btn-outline-info' }}">
                            <i class="mdi mdi-account-box"></i> Sedang Dipakai
                        </a>
                        <a href="{{ route('inventory.available') }}" class="btn btn-sm {{ request()->routeIs('inventory.available') ? 'btn-success' : 'btn-outline-success' }}">
                            <i class="mdi mdi-warehouse"></i> Dikembalikan (Available)
                        </a>
                    </div>
                    
                    {{-- SEARCH FORM --}}
                    <form action="{{ url()->current() }}" method="GET" class="d-flex">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Cari aset / user..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </div>
                    </form>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 120px;">Dokumentasi</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Penanggung Jawab</th>
                                <th>Kondisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventories as $item)
                            <tr>
                                {{-- KOLOM FOTO (UPDATE: MENAMPILKAN 2 FOTO) --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- 1. Foto Barang --}}
                                        <div class="text-center">
                                            @if($item->item_photo_path)
                                                <img src="{{ asset('storage/'.$item->item_photo_path) }}" 
                                                     title="Fisik Barang"
                                                     style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;">
                                            @else
                                                <div class="bg-secondary d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 45px; height: 45px; border-radius: 4px;" title="No Image">
                                                    <i class="mdi mdi-image-off"></i>
                                                </div>
                                            @endif
                                            <div style="font-size: 9px;" class="text-muted mt-1">Brg</div>
                                        </div>

                                        {{-- 2. Foto User (Serah Terima) --}}
                                        <div class="text-center">
                                            @if($item->user_item_photo_path)
                                                <img src="{{ asset('storage/'.$item->user_item_photo_path) }}" 
                                                     title="Bukti Serah Terima"
                                                     style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover; border: 2px solid #57B657;">
                                            @else
                                                 <div class="bg-light d-flex align-items-center justify-content-center text-muted border" 
                                                     style="width: 45px; height: 45px; border-radius: 4px;" title="Belum ada foto user">
                                                    <i class="mdi mdi-account-off"></i>
                                                </div>
                                            @endif
                                            <div style="font-size: 9px;" class="text-success mt-1">User</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-bold">{{ $item->item_name }}</div>
                                    <small class="text-muted">{{ $item->serial_number }}</small>
                                </td>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>
                                    @if($item->user)
                                        <span class="fw-bold">{{ $item->user->name }}</span>
                                        <br><small class="text-muted">{{ $item->user->branch->name ?? '-' }}</small>
                                    @else
                                        <span class="badge badge-success">Gudang / Available</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badges = [
                                            'Baik' => 'badge-success',
                                            'Baru' => 'badge-primary',
                                            'Rusak Ringan' => 'badge-warning',
                                            'Rusak Berat' => 'badge-danger',
                                            'Perbaikan' => 'badge-info',
                                        ];
                                        $bg = $badges[$item->condition] ?? 'badge-secondary';
                                    @endphp
                                    <label class="badge {{ $bg }}">{{ $item->condition }}</label>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- 1. SHOW --}}
                                        <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-inverse-info btn-icon btn-sm" title="Lihat Detail"><i class="mdi mdi-eye"></i></a>

                                        {{-- 2. EDIT & DELETE --}}
                                        @if(auth()->user()->role == 'admin')
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-inverse-warning btn-icon btn-sm" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                            
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus barang ini secara permanen?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm" title="Hapus"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif

                                        {{-- 3. RETURN --}}
                                        @php
                                            $canReturn = false;
                                            if($item->user_id) {
                                                if(in_array(auth()->user()->role, ['admin', 'audit'])) {
                                                    $canReturn = true;
                                                } elseif($item->user_id == auth()->id()) {
                                                    $canReturn = true;
                                                }
                                            }
                                        @endphp

                                        @if($canReturn)
                                            <button type="button" 
                                                    class="btn btn-inverse-primary btn-icon btn-sm" 
                                                    title="Ajukan Pengembalian"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#returnModal"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->item_name }}"
                                                    data-user="{{ $item->user->name }}">
                                                <i class="mdi mdi-keyboard-return"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">Data inventaris tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $inventories->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL RETURN (Tetap sama seperti sebelumnya) --}}
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pengembalian Inventaris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information-outline"></i> 
                        <strong>Proses Pengembalian:</strong><br>
                        1. Upload foto bukti pengembalian.<br>
                        2. Status akan menjadi <strong>Menunggu Verifikasi</strong>.
                    </div>

                    <div class="form-group mb-3">
                        <label>Nama Barang</label>
                        <input type="text" id="modalItemName" class="form-control" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label>Pemilik Saat Ini</label>
                        <input type="text" id="modalUserName" class="form-control" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label>Bukti Foto <span class="text-danger">*</span></label>
                        <input type="file" name="return_photo" class="form-control" required accept="image/*">
                        <small class="text-muted">Max 5MB (Otomatis Kompres)</small>
                    </div>

                    <div class="form-group mb-3">
                        <label>Catatan</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Kondisi barang saat dikembalikan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ajukan Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var returnModal = document.getElementById('returnModal');
    returnModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var user = button.getAttribute('data-user');

        var form = document.getElementById('returnForm');
        form.action = '/inventory/' + id + '/return';

        document.getElementById('modalItemName').value = name;
        document.getElementById('modalUserName').value = user;
    });
</script>
@endpush

@endsection