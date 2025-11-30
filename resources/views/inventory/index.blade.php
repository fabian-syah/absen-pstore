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
                                        <img src="{{ asset('storage/'.$item->item_photo_path) }}" alt="img" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
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
                                        $conditionKey = ucfirst(str_replace('_', ' ', $item->condition)); // Normalisasi string
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
@endsection