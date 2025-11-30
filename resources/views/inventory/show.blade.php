@extends('layout.master')

@section('title', 'Detail Inventaris')

@section('content')
<div class="row">
    {{-- Kolom Kiri: Foto --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title">Foto Aset</h4>
                @if($inventory->item_photo_path)
                    <img src="{{ asset('storage/' . $inventory->item_photo_path) }}" class="img-fluid rounded mb-3 shadow-sm" alt="Foto Barang" style="max-height: 400px; object-fit: contain;">
                    <a href="{{ asset('storage/' . $inventory->item_photo_path) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        <i class="mdi mdi-magnify-plus"></i> Lihat Ukuran Penuh
                    </a>
                @else
                    <div class="py-5 bg-light rounded text-muted border border-dashed">
                        <i class="mdi mdi-image-off mdi-48px"></i>
                        <p class="mt-2">Tidak ada foto</p>
                    </div>
                @endif
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
                            @if($inventory->user)
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
                    @if($inventory->document_path)
                        <div class="d-flex align-items-center p-3 border rounded bg-white">
                            <i class="mdi mdi-file-document text-danger mdi-36px me-3"></i>
                            <div>
                                <h6 class="mb-1">File Dokumen Tersedia</h6>
                                <a href="{{ asset('storage/' . $inventory->document_path) }}" target="_blank" class="btn btn-sm btn-info text-white">
                                    <i class="mdi mdi-download"></i> Download / Lihat
                                </a>
                            </div>
                        </div>
                    @else
                        <span class="text-small text-muted fst-italic">Tidak ada dokumen (Faktur/Garansi) terlampir.</span>
                    @endif
                </div>

                {{-- TOMBOL AKSI (Back, Edit, Delete) --}}
                <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-light">Kembali</a>
                    
                    {{-- Tombol Edit & Delete hanya untuk Admin/Audit --}}
                    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
                        
                        {{-- Tombol Edit --}}
                        <a href="{{ route('inventory.edit', $inventory->id) }}" class="btn btn-warning text-white">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>

                        {{-- Tombol Delete --}}
                        <form action="{{ route('inventory.destroy', $inventory->id) }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Data ini akan dihapus permanen.')">
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
@endsection 