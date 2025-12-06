@extends('layout.master')

@section('title', 'Detail Inventaris')

@section('content')
<div class="row">
    {{-- Kolom Kiri: Foto --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title text-start mb-3 border-bottom pb-2">Dokumentasi Aset</h4>
                
                {{-- 1. FOTO FISIK BARANG --}}
                <div class="mb-4">
                    <h6 class="text-muted mb-2 text-start small fw-bold">1. FOTO FISIK BARANG</h6>
                    @if ($inventory->item_photo_path)
                        <img src="{{ asset('storage/' . $inventory->item_photo_path) }}"
                            class="img-fluid rounded mb-2 shadow-sm border" alt="Foto Barang"
                            style="width: 100%; max-height: 300px; object-fit: contain; cursor: pointer;"
                            data-bs-toggle="modal" 
                            data-bs-target="#imagePreviewModal"
                            data-bs-img-src="{{ asset('storage/' . $inventory->item_photo_path) }}"
                            data-bs-img-title="Foto Fisik Barang: {{ $inventory->item_name }}">
                            
                        <button type="button" class="btn btn-sm btn-outline-primary w-100"
                            data-bs-toggle="modal" 
                            data-bs-target="#imagePreviewModal"
                            data-bs-img-src="{{ asset('storage/' . $inventory->item_photo_path) }}"
                            data-bs-img-title="Foto Fisik Barang: {{ $inventory->item_name }}">
                            <i class="mdi mdi-magnify-plus"></i> Perbesar
                        </button>
                    @else
                        <div class="py-4 bg-light rounded text-muted border border-dashed">
                            <i class="mdi mdi-image-off mdi-36px"></i>
                            <p class="mt-1 small">Tidak ada foto barang</p>
                        </div>
                    @endif
                </div>

                {{-- 2. FOTO SERAH TERIMA --}}
                <div class="mb-2">
                    <h6 class="text-success mb-2 text-start small fw-bold">2. BUKTI SERAH TERIMA (USER)</h6>
                    @if ($inventory->user_item_photo_path)
                        <img src="{{ asset('storage/' . $inventory->user_item_photo_path) }}"
                            class="img-fluid rounded mb-2 shadow-sm border border-success" alt="Foto Serah Terima"
                            style="width: 100%; max-height: 300px; object-fit: contain; cursor: pointer;"
                            data-bs-toggle="modal" 
                            data-bs-target="#imagePreviewModal"
                            data-bs-img-src="{{ asset('storage/' . $inventory->user_item_photo_path) }}"
                            data-bs-img-title="Bukti Serah Terima: {{ $inventory->user->name }}">

                        <button type="button" class="btn btn-sm btn-outline-success w-100"
                            data-bs-toggle="modal" 
                            data-bs-target="#imagePreviewModal"
                            data-bs-img-src="{{ asset('storage/' . $inventory->user_item_photo_path) }}"
                            data-bs-img-title="Bukti Serah Terima: {{ $inventory->user->name }}">
                            <i class="mdi mdi-magnify-plus"></i> Perbesar
                        </button>
                    @else
                        <div class="py-4 bg-light rounded text-muted border border-dashed">
                            <i class="mdi mdi-account-off mdi-36px"></i>
                            <p class="mt-1 small">Belum ada foto serah terima</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Detail & Tombol Aksi --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">{{ $inventory->item_name }}</h4>
                        <span class="text-muted">SN: {{ $inventory->serial_number ?? 'N/A' }}</span>
                    </div>
                    <span class="badge badge-primary fs-6">{{ ucfirst($inventory->category) }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded h-100">
                            <small class="text-muted d-block">Kondisi</small>
                            <span class="fw-bold fs-5">{{ ucfirst(str_replace('_', ' ', $inventory->condition)) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded h-100">
                            <small class="text-muted d-block">Tanggal Diterima</small>
                            <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($inventory->received_date)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="bg-light p-3 rounded mt-3">
                            <small class="text-muted d-block">Penanggung Jawab Saat Ini</small>
                            <h5 class="fw-bold mb-0 mt-1">{{ $inventory->user->name ?? 'Tanpa Pemilik' }}</h5>
                            @if ($inventory->user)
                                <div class="text-primary">{{ $inventory->user->email }}</div>
                                <div class="text-muted small">{{ $inventory->user->branch->name ?? 'Pusat' }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-4">
                    <h5 class="card-title">Keterangan Tambahan</h5>
                    <div class="p-3 border rounded bg-white">
                        {{ $inventory->description ?? 'Tidak ada keterangan tambahan.' }}
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="card-title">Dokumen Pendukung</h5>
                    @if ($inventory->document_path)
                        <div class="d-flex align-items-center p-3 border rounded bg-white">
                            <i class="mdi mdi-file-document text-danger mdi-36px me-3"></i>
                            <div>
                                <h6 class="mb-1">File Dokumen Tersedia</h6>
                                <a href="{{ asset('storage/' . $inventory->document_path) }}" target="_blank"
                                    class="btn btn-sm btn-info text-white">
                                    <i class="mdi mdi-download"></i> Download / Lihat
                                </a>
                            </div>
                        </div>
                    @else
                        <span class="text-small text-muted fst-italic">Tidak ada dokumen (Faktur/Garansi) terlampir.</span>
                    @endif
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-light">Kembali</a>

                    @if (auth()->user()->role == 'admin')
                        {{-- Tombol Edit --}}
                        <a href="{{ route('inventory.edit', $inventory->id) }}" class="btn btn-warning text-white">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>

                        {{-- Tombol Delete --}}
                        <form action="{{ route('inventory.destroy', $inventory->id) }}" method="POST"
                            onsubmit="return confirm('APAKAH ANDA YAKIN? Data ini akan dihapus permanen.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger text-white">
                                <i class="mdi mdi-delete"></i> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW IMAGE (SHARED) --}}
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