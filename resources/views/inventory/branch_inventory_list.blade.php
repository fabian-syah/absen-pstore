@extends('layout.master')

@section('title', 'Inventaris Cabang')
@section('heading', 'Monitoring Aset Wilayah')

@push('styles')
<style>
    /* Styling Judul Halaman */
    .branch-section-title {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
        color: #1e293b;
        font-weight: 700;
    }

    .branch-section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 24px;
        background: linear-gradient(to bottom, #11cdef, #1171ef); /* Biru Info */
        border-radius: 4px;
    }

    /* Styling Kartu (Kotak Persegi) */
    .branch-card-item {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .branch-card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(17, 205, 239, 0.15);
        border-color: #b3e5fc;
    }

    /* Icon Box */
    .branch-icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #e1f5fe 0%, #e0f7fa 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0288d1;
        font-size: 24px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .branch-card-item:hover .branch-icon-box {
        background: linear-gradient(135deg, #29b6f6 0%, #0288d1 100%);
        color: white;
    }

    /* Styling List Inventaris */
    .inventory-list-group {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1; /* Supaya tombol detail terdorong ke bawah jika konten sedikit */
    }

    .inventory-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .inventory-list-item:last-child {
        border-bottom: none;
    }

    .cat-badge {
        font-weight: 600;
        color: #334155;
    }
    
    .count-badge {
        background-color: #f1f5f9;
        color: #334155;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="branch-section-title">Ringkasan Aset Cabang ({{ count($branches) }})</h4>
        </div>
    </div>

    <div class="row">
        @forelse ($branches as $branch)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="branch-card-item p-4">
                    {{-- Header: Icon & Total Aset --}}
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="branch-icon-box">
                            <i class="mdi mdi-package-variant-closed"></i>
                        </div>
                        <span class="badge bg-light text-secondary border">
                            Total: {{ $branch->total_assets }} Unit
                        </span>
                    </div>

                    {{-- Nama Cabang & Alamat --}}
                    <h5 class="fw-bold mb-1">{{ $branch->name }}</h5>
                    <p class="text-muted small mb-3">
                        <i class="mdi mdi-map-marker-outline me-1"></i>
                        {{ Str::limit($branch->address ?? 'Alamat belum diatur', 40) }}
                    </p>

                    <hr class="my-2">

                    {{-- List Kategori (Looping Data) --}}
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Rincian Aset:</h6>
                        
                        <ul class="inventory-list-group">
                            @php $countDisplay = 0; @endphp
                            
                            @forelse($branch->inventory_summary as $category => $count)
                                {{-- Tampilkan maksimal 4 kategori agar kartu tidak kepanjangan --}}
                                @if($countDisplay < 4) 
                                    <li class="inventory-list-item">
                                        <span class="cat-badge">{{ $category }}</span>
                                        <span class="count-badge">{{ $count }}</span>
                                    </li>
                                @endif
                                @php $countDisplay++; @endphp
                            @empty
                                <li class="text-center py-3 text-muted small">
                                    <i>Belum ada data aset.</i>
                                </li>
                            @endforelse

                            {{-- Indikator jika ada lebih banyak kategori --}}
                            @if($branch->inventory_summary->count() > 4)
                                <li class="inventory-list-item justify-content-center text-primary" style="font-size: 0.75rem;">
                                    + {{ $branch->inventory_summary->count() - 4 }} Kategori Lainnya
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Footer: Tombol Detail --}}
                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                         <small class="text-muted">ID: {{ $branch->id }}</small>
                         {{-- Link ini mengarah ke detail tim/cabang yang sudah ada --}}
                        <a href="{{ route('team.branch.detail', $branch->id) }}" 
                           class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm">
                            Lihat Detail <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center border-0 shadow-sm">
                    <div class="text-muted">
                        <i class="mdi mdi-package-variant" style="font-size: 3rem;"></i>
                        <p class="mt-2">Tidak ada data cabang yang ditemukan untuk Anda.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection