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
                                    {{-- NAMA BARANG & FOTO --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->item_photo)
                                                <img src="{{ asset('storage/'.$item->item_photo) }}" class="rounded-3 me-3" width="45" height="45" style="object-fit: cover; border: 1px solid #eee;">
                                            @else
                                                <div class="rounded-3 bg-light text-secondary d-flex align-items-center justify-content-center me-3 border" style="width: 45px; height: 45px;">
                                                    <i class="mdi mdi-image-off"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    SN: {{ $item->serial_number ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KATEGORI --}}
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            {{ $item->category }}
                                        </span>
                                    </td>

                                    {{-- KONDISI (Logika Badge Warna) --}}
                                    <td>
                                        @php
                                            $badgeClass = match($item->condition) {
                                                'Baru' => 'bg-success',
                                                'Baik' => 'bg-primary',
                                                'Rusak Ringan' => 'bg-warning text-dark',
                                                'Rusak Berat' => 'bg-danger',
                                                'Perbaikan' => 'bg-secondary',
                                                default => 'bg-light text-dark border'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $item->condition }}
                                        </span>
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
                                    <td colspan="6" class="text-center py-5 text-muted">
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