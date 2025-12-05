@extends('layout.master')

@section('title', 'Daftar Inventaris')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Inventaris & Aset</h4>
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    
                    {{-- TOMBOL TAMBAH: SEMUA ROLE BISA --}}
                    @if(in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'security', 'user_biasa']))
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Barang
                        </a>
                    @endif
                    
                    {{-- SEARCH FORM --}}
                    <form action="{{ route('inventory.index') }}" method="GET" class="d-flex">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Cari aset / user..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
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
                                         <img src="{{ asset('storage/'.$item->item_photo_path) }}" style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; border-radius: 4px;">
                                            <i class="mdi mdi-image-off"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->item_name }}</div>
                                    <small class="text-muted">{{ $item->serial_number }}</small>
                                </td>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>
                                    @if($item->user)
                                        {{ $item->user->name }}
                                        <br><small class="text-muted">{{ $item->user->branch->name ?? '-' }}</small>
                                    @else
                                        <span class="badge badge-success">Available</span>
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
                                        {{-- View Button (SEMUA ROLE) --}}
                                        <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-inverse-info btn-icon btn-sm" title="Lihat"><i class="mdi mdi-eye"></i></a>

                                        {{-- Edit & Delete (ADMIN & AUDIT ONLY) --}}
                                        @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-inverse-warning btn-icon btn-sm" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                            
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus barang ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm" title="Hapus"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif

                                        {{-- Return Button (SEMUA ROLE - Jika barang milik dia ATAU Admin/Audit) --}}
                                        @php
                                            $canReturn = false;
                                            // Admin/Audit bisa mengembalikan barang siapa saja
                                            if(in_array(auth()->user()->role, ['admin', 'audit'])) {
                                                $canReturn = true;
                                            }
                                            // User biasa hanya bisa mengembalikan barang miliknya
                                            elseif($item->user_id == auth()->id()) {
                                                $canReturn = true;
                                            }
                                        @endphp

                                        @if($item->user_id && $canReturn)
                                            <button type="button" 
                                                    class="btn btn-inverse-primary btn-icon btn-sm" 
                                                    title="Kembalikan Barang"
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
                            <tr><td colspan="6" class="text-center py-4">Data inventaris kosong.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $inventories->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PENGEMBALIAN INVENTARIS --}}
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
                        <i class="mdi mdi-information-outline"></i> Status barang akan berubah menjadi <strong>Available</strong> (Tanpa Pemilik).
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
                    <button type="submit" class="btn btn-primary">Proses Pengembalian</button>
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

        // Set Action URL dinamis
        var form = document.getElementById('returnForm');
        form.action = '/inventory/' + id + '/return';

        document.getElementById('modalItemName').value = name;
        document.getElementById('modalUserName').value = user;
    });
</script>
@endpush

@endsection