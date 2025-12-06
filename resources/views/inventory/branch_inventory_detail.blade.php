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
    {{-- HEADER INFO CABANG (Style Gradient Ungu/Biru sesuai gambar) --}}
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Daftar Inventaris di {{ $branch->name }}</h4>
                    
                    {{-- Filter Ringan (Opsional visual saja) --}}
                    <div>
                        <span class="badge bg-light text-secondary border">Total: {{ $inventories->count() }}</span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Foto</th>
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
                                    {{-- FOTO (Disamakan dengan Index) --}}
                                    <td>
                                        @if($item->item_photo_path)
                                             <img src="{{ asset('storage/'.$item->item_photo_path) }}" style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; border-radius: 4px;">
                                                <i class="mdi mdi-image-off"></i>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- NAMA BARANG --}}
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                        <small class="text-muted">
                                            SN: {{ $item->serial_number ?? '-' }}
                                        </small>
                                    </td>

                                    {{-- KATEGORI (Style teks biasa agar terbaca jelas) --}}
                                    <td>
                                        {{ ucfirst($item->category) }}
                                    </td>

                                    {{-- KONDISI (Logika Badge Warna) --}}
                                    <td>
                                        @php
                                            $badgeClass = match($item->condition) {
                                                'Baru' => 'badge-success',     // Hijau
                                                'Baik' => 'badge-primary',     // Biru
                                                'Rusak Ringan' => 'badge-warning', // Kuning/Orange
                                                'Rusak Berat' => 'badge-danger',   // Merah
                                                'Perbaikan' => 'badge-info',       // Cyan/Biru Muda
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <label class="badge {{ $badgeClass }}">
                                            {{ $item->condition }}
                                        </label>
                                    </td>

                                    {{-- PEMEGANG (USER) --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-account-circle-outline fs-5 text-muted me-2"></i>
                                            <div>
                                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $item->user->name ?? 'Tanpa Pemilik' }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $item->user->division->name ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- TANGGAL TERIMA --}}
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
                                            <small>Cabang ini belum memiliki aset yang terdaftar pada sistem.</small>
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
@endsection