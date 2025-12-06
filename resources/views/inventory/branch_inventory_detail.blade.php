@extends('layout.master')

@section('title')
    Inventaris Cabang - {{ $branch->name }}
@endsection

@section('heading')
    <a href="{{ route('inventory.branches') }}" class="text-decoration-none text-muted me-2">
        <i class="mdi mdi-arrow-left"></i> Kembali ke List Cabang
    </a>
@endsection

@section('content')
<div class="row">
    {{-- HEADER INFO CABANG --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">{{ $branch->name }}</h3>
                        <p class="mb-0 opacity-75"><i class="mdi mdi-map-marker me-1"></i> {{ $branch->address ?? 'Alamat belum diset' }}</p>
                    </div>
                    <div class="text-end">
                        <h1 class="fw-bold mb-0">{{ $inventories->count() }}</h1>
                        <small class="opacity-75">Total Unit Aset</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR BARANG --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h4 class="card-title mb-0">Daftar Inventaris di {{ $branch->name }}</h4>
                    
                    <div class="d-flex gap-2">
                        {{-- TOMBOL TAMBAH INVENTARIS KHUSUS CABANG INI --}}
                        @if(in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
                            <a href="{{ route('inventory.create', ['branch_id' => $branch->id]) }}" 
                               class="btn btn-primary btn-sm shadow-sm">
                                <i class="mdi mdi-plus-circle me-1"></i> Tambah Aset Cabang
                            </a>
                        @endif

                        <span class="badge bg-light text-secondary border d-flex align-items-center">Total: {{ $inventories->count() }}</span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 120px;">Dokumentasi</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Kondisi</th>
                                <th>Pemegang (User)</th>
                                <th>Tanggal Terima</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventories as $item)
                                <tr>
                                    {{-- KOLOM DOKUMENTASI (2 FOTO) --}}
                                    <td>
                                        <div class="d-flex gap-2">
                                            {{-- 1. Foto Barang --}}
                                            <div class="text-center">
                                                @if($item->item_photo_path)
                                                    <img src="{{ asset('storage/'.$item->item_photo_path) }}" 
                                                         class="cursor-pointer"
                                                         title="Klik untuk memperbesar (Fisik Barang)"
                                                         style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd; cursor: pointer;"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#imagePreviewModal"
                                                         data-bs-img-src="{{ asset('storage/'.$item->item_photo_path) }}"
                                                         data-bs-img-title="Foto Fisik Barang: {{ $item->item_name }}">
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
                                                         class="cursor-pointer"
                                                         title="Klik untuk memperbesar (Bukti Serah Terima)"
                                                         style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover; border: 2px solid #57B657; cursor: pointer;"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#imagePreviewModal"
                                                         data-bs-img-src="{{ asset('storage/'.$item->user_item_photo_path) }}"
                                                         data-bs-img-title="Bukti Serah Terima: {{ $item->user->name ?? 'User' }}">
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

                                    {{-- NAMA --}}
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                        <small class="text-muted">SN: {{ $item->serial_number ?? '-' }}</small>
                                    </td>

                                    {{-- KATEGORI --}}
                                    <td>{{ ucfirst($item->category) }}</td>

                                    {{-- KONDISI --}}
                                    <td>
                                        @php
                                            $badgeClass = match($item->condition) {
                                                'Baru' => 'badge-success',
                                                'Baik' => 'badge-primary',
                                                'Rusak Ringan' => 'badge-warning',
                                                'Rusak Berat' => 'badge-danger',
                                                'Perbaikan' => 'badge-info',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <label class="badge {{ $badgeClass }}">{{ $item->condition }}</label>
                                    </td>

                                    {{-- PEMEGANG --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-account-circle-outline fs-5 text-muted me-2"></i>
                                            <div>
                                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $item->user->name ?? 'Tanpa Pemilik' }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $item->user->division->name ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-muted">
                                        {{ \Carbon\Carbon::parse($item->received_date)->format('d M Y') }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td>
                                        <a href="{{ route('inventory.show', $item->id) }}" 
                                           class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                            <i class="mdi mdi-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed text-secondary opacity-25" style="font-size: 4rem;"></i>
                                            <p class="mt-3 fw-bold">Belum ada inventaris tercatat.</p>
                                            <small>Cabang ini belum memiliki aset yang terdaftar.</small>
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

{{-- MODAL PREVIEW IMAGE (CLEAN POP-UP) --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="previewTitle">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Script untuk Modal Image Preview (POP-UP)
    var imageModal = document.getElementById('imagePreviewModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        // Tombol / Gambar yang diklik
        var button = event.relatedTarget;
        
        // Ambil data dari atribut
        var imgSrc = button.getAttribute('data-bs-img-src');
        var imgTitle = button.getAttribute('data-bs-img-title');
        
        // Update isi modal
        var modalImg = imageModal.querySelector('#previewImage');
        var modalTitle = imageModal.querySelector('#previewTitle');
        
        modalImg.src = imgSrc;
        modalTitle.textContent = imgTitle || 'Detail Foto';
    });

    // Bersihkan src saat modal ditutup
    imageModal.addEventListener('hidden.bs.modal', function () {
        var modalImg = imageModal.querySelector('#previewImage');
        modalImg.src = '';
    });
</script>
@endpush

@endsection