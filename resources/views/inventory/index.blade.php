@extends('layout.master')

@section('title', $pageTitle ?? 'Daftar Inventaris')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $pageTitle ?? 'Daftar Inventaris' }}</h4>
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    @if(in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'security', 'user_biasa']))
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Barang
                        </a>
                    @endif
                    
                    {{-- TOMBOL NAVIGASI FILTER --}}
                    <div class="btn-group" role="group">
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm {{ request()->routeIs('inventory.index') ? 'btn-info' : 'btn-outline-info' }}">
                            <i class="mdi mdi-account-box"></i> Sedang Dipakai
                        </a>
                        <a href="{{ route('inventory.available') }}" class="btn btn-sm {{ request()->routeIs('inventory.available') ? 'btn-success' : 'btn-outline-success' }}">
                            <i class="mdi mdi-warehouse"></i> Dikembalikan (Available)
                        </a>

                        {{-- [BARU] TOMBOL KHUSUS ADMIN: LIHAT SEMUA --}}
                        @if(auth()->user()->role == 'admin')
                            <a href="{{ route('inventory.admin.all') }}" class="btn btn-sm {{ request()->routeIs('inventory.admin.all') ? 'btn-danger' : 'btn-outline-danger' }}">
                                <i class="mdi mdi-database"></i> Master Data (Semua)
                            </a>
                        @endif
                    </div>

                    <form action="{{ url()->current() }}" method="GET" class="d-flex">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Cari aset / user..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                        </div>
                    </form>
                </div>

                {{-- Alert --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                {{-- TABLE CONTENT --}}
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
                                <td>
                                    <div class="d-flex gap-2">
                                        <div class="text-center">
                                            @if($item->item_photo_path)
                                                <img src="{{ asset('storage/'.$item->item_photo_path) }}" class="cursor-pointer" style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;" data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-bs-img-src="{{ asset('storage/'.$item->item_photo_path) }}" data-bs-img-title="Fisik: {{ $item->item_name }}">
                                            @else
                                                <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 45px; height: 45px; border-radius: 4px;"><i class="mdi mdi-image-off"></i></div>
                                            @endif
                                            <div style="font-size: 9px;" class="text-muted mt-1">Brg</div>
                                        </div>
                                        <div class="text-center">
                                            @if($item->user_item_photo_path)
                                                <img src="{{ asset('storage/'.$item->user_item_photo_path) }}" class="cursor-pointer" style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover; border: 2px solid #57B657;" data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-bs-img-src="{{ asset('storage/'.$item->user_item_photo_path) }}" data-bs-img-title="User: {{ $item->user->name ?? 'User' }}">
                                            @else
                                                 <div class="bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px; border-radius: 4px;"><i class="mdi mdi-account-off"></i></div>
                                            @endif
                                            <div style="font-size: 9px;" class="text-success mt-1">User</div>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="fw-bold">{{ $item->item_name }}</div><small class="text-muted">{{ $item->serial_number }}</small></td>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>
                                    @if($item->user) <span class="fw-bold">{{ $item->user->name }}</span><br><small class="text-muted">{{ $item->user->branch->name ?? '-' }}</small>
                                    @else <span class="badge badge-success">Gudang / Available</span> @endif
                                </td>
                                <td><label class="badge badge-secondary">{{ $item->condition }}</label></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-inverse-info btn-icon btn-sm"><i class="mdi mdi-eye"></i></a>
                                        @if(auth()->user()->role == 'admin')
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-inverse-warning btn-icon btn-sm"><i class="mdi mdi-pencil"></i></a>
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-inverse-danger btn-icon btn-sm"><i class="mdi mdi-delete"></i></button></form>
                                        @endif
                                        
                                        {{-- LOGIC TOMBOL RETURN --}}
                                        @php
                                            $canReturn = false;
                                            if($item->user_id) {
                                                if(in_array(auth()->user()->role, ['admin', 'audit'])) { $canReturn = true; }
                                                elseif($item->user_id == auth()->id()) { $canReturn = true; }
                                            }
                                        @endphp
                                        @if($canReturn)
                                            <button type="button" class="btn btn-inverse-primary btn-icon btn-sm" title="Kembalikan" data-bs-toggle="modal" data-bs-target="#returnModal" data-id="{{ $item->id }}" data-name="{{ $item->item_name }}" data-user="{{ $item->user->name }}">
                                                <i class="mdi mdi-keyboard-return"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">Data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $inventories->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW IMAGE --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0"><h5 class="modal-title" id="previewTitle">Foto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center"><img id="previewImage" src="" alt="Preview" class="img-fluid rounded" style="max-height: 80vh;"></div>
        </div>
    </div>
</div>

{{-- MODAL RETURN (YANG DIUPDATE) --}}
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
                        <strong>Proses:</strong> Upload bukti & nama penerima. Status akan menjadi <strong>Pending</strong>.
                    </div>

                    <div class="form-group mb-3">
                        <label>Nama Barang</label>
                        <input type="text" id="modalItemName" class="form-control bg-light" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label>Pemilik Saat Ini</label>
                        <input type="text" id="modalUserName" class="form-control bg-light" readonly>
                    </div>

                    {{-- INPUT BARU: NAMA PENERIMA --}}
                    <div class="form-group mb-3">
                        <label>Nama Penerima (Fisik) <span class="text-danger">*</span></label>
                        <input type="text" name="receiver_name" class="form-control" required placeholder="Contoh: Pak Budi (Security) / Bu Siti (HRD)">
                        <small class="text-muted">Siapa yang menerima barang ini secara fisik?</small>
                    </div>

                    <div class="form-group mb-3">
                        <label>Bukti Foto <span class="text-danger">*</span></label>
                        <input type="file" name="return_photo" class="form-control" required accept="image/*">
                    </div>

                    <div class="form-group mb-3">
                        <label>Catatan</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Kondisi barang..."></textarea>
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
    // Modal Return
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

    // Modal Image
    var imageModal = document.getElementById('imagePreviewModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var imgSrc = button.getAttribute('data-bs-img-src');
        var imgTitle = button.getAttribute('data-bs-img-title');
        var modalImg = imageModal.querySelector('#previewImage');
        var modalTitle = imageModal.querySelector('#previewTitle');
        modalImg.src = imgSrc;
        modalTitle.textContent = imgTitle || 'Foto';
    });
    imageModal.addEventListener('hidden.bs.modal', function () {
        var modalImg = imageModal.querySelector('#previewImage');
        modalImg.src = '';
    });
</script>
@endpush

@endsection