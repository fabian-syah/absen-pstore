@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Inventaris & Aset</h4>
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    {{-- TOMBOL TAMBAH: HANYA ADMIN/AUDIT --}}
                    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'audit' || auth()->user()->role == 'leader'|| auth()->user()->role == 'decurity' || auth()->user()->role == 'user_biasa')
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Barang
                        </a>
                    @else
                        <div></div> {{-- Spacer --}}
                    @endif
                    
                    {{-- SEARCH FORM: SEMUA ROLE --}}
                    <form action="{{ route('inventory.index') }}" method="GET" class="d-flex">
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
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
                                    @if($item->item_photo_path)
                                        {{-- MODIFIKASI: Tambahkan data-bs-toggle dan class pointer --}}
                                        <img src="{{ asset('storage/'.$item->item_photo_path) }}" 
                                             alt="{{ $item->item_name }}" 
                                             class="img-clickable"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: pointer; transition: 0.3s;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imagePreviewModal"
                                             data-img-src="{{ asset('storage/'.$item->item_photo_path) }}"
                                             data-img-title="{{ $item->item_name }}">
                                    @else
                                        <div class="bg-secondary text-white d-flex justify-content-center align-items-center" 
                                             style="width: 50px; height: 50px; border-radius: 4px;">
                                            <i class="mdi mdi-image-off"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->item_name }}</div>
                                    <small class="text-muted">SN: {{ $item->serial_number ?? '-' }}</small>
                                </td>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>
                                    @if($item->user)
                                        {{ $item->user->name }}
                                        <br>
                                        <small class="text-muted">{{ $item->user->branch->name ?? 'Pusat' }}</small>
                                    @else
                                        <span class="text-danger">Belum ditentukan</span>
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
                                        $conditionKey = ucfirst(str_replace('_', ' ', $item->condition)); 
                                        $badgeClass = $badges[$conditionKey] ?? 'badge-secondary';
                                    @endphp
                                    <label class="badge {{ $badgeClass }}">{{ $conditionKey }}</label>
                                </td>
                                <td>
                                    {{-- LIHAT: SEMUA ROLE --}}
                                    <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-inverse-info btn-icon btn-sm" title="Lihat Detail">
                                        <i class="mdi mdi-eye"></i>
                                    </a>

                                    {{-- EDIT & DELETE: HANYA ADMIN/AUDIT --}}
                                    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
                                        <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-inverse-warning btn-icon btn-sm" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        
                                        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini? Data tidak dapat dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm" title="Hapus">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Data inventaris tidak ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-end">
                    {{ $inventories->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW IMAGE --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- modal-lg agar gambar besar --}}
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="imagePreviewModalLabel">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                {{-- Gambar akan diisi lewat JS --}}
                <img src="" id="previewImage" class="img-fluid rounded" alt="Preview" style="max-height: 80vh; width: auto;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Script untuk menangani modal gambar dinamis
    var imageModal = document.getElementById('imagePreviewModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        // Tombol (gambar) yang memicu modal
        var button = event.relatedTarget;
        
        // Ambil info dari data attributes
        var imgSrc = button.getAttribute('data-img-src');
        var imgTitle = button.getAttribute('data-img-title');
        
        // Update isi modal
        var modalTitle = imageModal.querySelector('.modal-title');
        var modalImg = imageModal.querySelector('#previewImage');
        
        modalTitle.textContent = imgTitle;
        modalImg.src = imgSrc;
    });
</script>

{{-- Style Tambahan untuk hover effect pada gambar kecil --}}
<style>
    .img-clickable:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
</style>
@endpush