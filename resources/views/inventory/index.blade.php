@extends('layout.master')

@section('title', 'Daftar Inventaris')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Inventaris & Aset</h4>
                
                {{-- ... Search form & Add Button Code (Sama seperti sebelumnya) ... --}}
                
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
                                    {{-- ... Code Foto (Sama seperti sebelumnya) ... --}}
                                    @if($item->item_photo_path)
                                         <img src="{{ asset('storage/'.$item->item_photo_path) }}" style="width: 50px; height: 50px; border-radius: 4px;">
                                    @else
                                        <div class="bg-secondary" style="width: 50px; height: 50px; border-radius: 4px;"></div>
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
                                <td>{{ $item->condition }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- View Button --}}
                                        <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-inverse-info btn-icon btn-sm"><i class="mdi mdi-eye"></i></a>

                                        @if(auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
                                            {{-- Edit Button --}}
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-inverse-warning btn-icon btn-sm"><i class="mdi mdi-pencil"></i></a>
                                            
                                            {{-- TOMBOL KEMBALIKAN BARANG (Hanya muncul jika barang ada pemiliknya) --}}
                                            @if($item->user_id)
                                                <button type="button" 
                                                        class="btn btn-inverse-primary btn-icon btn-sm" 
                                                        title="Proses Pengembalian"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#returnModal"
                                                        data-id="{{ $item->id }}"
                                                        data-name="{{ $item->item_name }}"
                                                        data-user="{{ $item->user->name }}">
                                                    <i class="mdi mdi-keyboard-return"></i>
                                                </button>
                                            @endif

                                            {{-- Delete Button --}}
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">Data kosong.</td></tr>
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
                        <i class="mdi mdi-information-outline"></i> Barang ini akan dilepas dari penanggung jawabnya dan status akan menjadi "Available".
                    </div>

                    <div class="form-group mb-3">
                        <label>Nama Barang</label>
                        <input type="text" id="modalItemName" class="form-control" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label>Dikembalikan Oleh (User Saat Ini)</label>
                        <input type="text" id="modalUserName" class="form-control" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label>Bukti Foto Pengembalian <span class="text-danger">*</span></label>
                        <input type="file" name="return_photo" class="form-control" required accept="image/*">
                        <small class="text-muted">Foto akan otomatis dikompres (Max 100KB)</small>
                    </div>

                    <div class="form-group mb-3">
                        <label>Catatan / Kondisi Akhir</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Contoh: Barang dikembalikan lengkap dengan charger, kondisi baik."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses & Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Script untuk mengisi data ke Modal Return
    var returnModal = document.getElementById('returnModal');
    returnModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var user = button.getAttribute('data-user');

        // Update Form Action URL
        var form = document.getElementById('returnForm');
        form.action = '/inventory/' + id + '/return';

        // Update Input Fields
        document.getElementById('modalItemName').value = name;
        document.getElementById('modalUserName').value = user;
    });
</script>
@endpush

@endsection