@extends('layout.master')

@section('title', 'Daftar Pelanggaran Aktif')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Pelanggaran Aktif</h4>
                        <p class="text-muted small mb-0">
                            Data pelanggaran yang masa berlakunya <span class="text-danger fw-bold">belum habis</span>.
                        </p>
                    </div>
                    
                    <div class="d-flex gap-2">
                        {{-- Tombol Menuju History --}}
                        <a href="{{ route('violations.history') }}" class="btn btn-secondary btn-sm text-white mr-2">
                            <i class="mdi mdi-history"></i> Lihat History (Selesai)
                        </a>

                        {{-- Tombol Tambah (Hanya Admin/Audit) --}}
                        @if(in_array(auth()->user()->role, ['admin', 'audit']))
                            <a href="{{ route('violations.create') }}" class="btn btn-primary btn-sm text-white">
                                <i class="mdi mdi-plus"></i> Tambah Pelanggaran
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Table Section --}}
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Nama Pelanggar</th>
                                <th>Judul Masalah</th>
                                <th>Bukti</th>
                                <th>Pelapor</th>
                                <th>Tgl Input</th>
                                <th>Masa Berlaku</th>
                                @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $v)
                                <tr>
                                    {{-- Kategori Badge --}}
                                    <td>
                                        @if($v->category == 'berat')
                                            <label class="badge badge-danger">BERAT</label>
                                        @elseif($v->category == 'sedang')
                                            <label class="badge badge-warning">SEDANG</label>
                                        @else
                                            <label class="badge badge-info">RINGAN</label>
                                        @endif
                                    </td>

                                    {{-- Nama --}}
                                    <td class="font-weight-bold">{{ $v->user->name ?? 'User Terhapus' }}</td>

                                    {{-- Masalah --}}
                                    <td>
                                        <span class="d-block text-dark">{{ $v->title }}</span>
                                        <small class="text-muted">{{ Str::limit($v->description, 40) }}</small>
                                    </td>

                                    {{-- Bukti Foto --}}
                                    <td>
                                        @if($v->photo_path)
                                            <img src="{{ asset('storage/' . $v->photo_path) }}" 
                                                 alt="Bukti" 
                                                 class="img-thumbnail" 
                                                 style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;"
                                                 onclick="showImageModal('{{ asset('storage/' . $v->photo_path) }}', '{{ $v->title }}')">
                                        @else
                                            <span class="text-muted text-small">-</span>
                                        @endif
                                    </td>

                                    {{-- Pelapor --}}
                                    <td>{{ $v->reporter->name ?? 'System' }}</td>

                                    {{-- Tgl Input --}}
                                    <td>{{ $v->created_at->format('d M Y') }}</td>

                                    {{-- Masa Berlaku --}}
                                    <td>
                                        <span class="text-danger fw-bold">{{ $v->expires_at->format('d M Y') }}</span>
                                        <br>
                                        <small class="text-muted">({{ now()->diffInDays($v->expires_at) }} hari lagi)</small>
                                    </td>

                                    {{-- AKSI (Edit, Resolve, Delete) --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                        <td>
                                            <div class="d-flex">
                                                {{-- 1. Edit --}}
                                                <a href="{{ route('violations.edit', $v->id) }}" class="btn btn-warning btn-sm icon-btn p-2 mr-1" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>

                                                {{-- 2. Selesaikan (RESOLVE) --}}
                                                <button type="button" class="btn btn-success btn-sm icon-btn p-2 mr-1" 
                                                        onclick="showResolveModal('{{ $v->id }}', '{{ $v->user->name }}', '{{ $v->title }}')"
                                                        title="Selesaikan / Cabut">
                                                    <i class="mdi mdi-check-bold"></i>
                                                </button>

                                                {{-- 3. Hapus --}}
                                                @if(auth()->user()->role == 'admin')
                                                    <form action="{{ route('violations.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm icon-btn p-2" title="Hapus">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="mdi mdi-shield-check text-success" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">Tidak ada pelanggaran aktif. Kerja bagus!</p>
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

{{-- MODAL RESOLVE (PENYELESAIAN) --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="resolveForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Selesaikan Pelanggaran</h5>
                    <button type="button" class="close" data-dismiss="modal" onclick="$('#resolveModal').modal('hide')">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menyelesaikan pelanggaran berikut:</p>
                    <ul class="list-unstyled bg-light p-2 rounded">
                        <li><strong>Nama:</strong> <span id="resName"></span></li>
                        <li><strong>Masalah:</strong> <span id="resTitle"></span></li>
                    </ul>
                    <div class="form-group mt-3">
                        <label class="fw-bold">Catatan Penyelesaian <span class="text-danger">*</span></label>
                        <textarea name="resolution_notes" class="form-control" rows="3" required placeholder="Contoh: Sudah ditebus lembur, Dimaafkan HRD, Salah input, dll..."></textarea>
                    </div>
                    <small class="text-muted text-danger d-block mt-2">
                        * Data akan langsung dipindahkan ke menu <b>History</b>.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="$('#resolveModal').modal('hide')">Batal</button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="mdi mdi-check"></i> Selesaikan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW IMAGE --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Bukti</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="$('#imagePreviewModal').modal('hide')">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImageSrc" src="" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Script untuk Modal Resolve
    function showResolveModal(id, name, title) {
        let url = "{{ route('violations.resolve', ':id') }}";
        url = url.replace(':id', id);
        
        $('#resolveForm').attr('action', url);
        $('#resName').text(name);
        $('#resTitle').text(title);
        $('#resolveModal').modal('show');
    }

    // Script untuk Modal Image
    function showImageModal(src, title) {
        document.getElementById('previewImageSrc').src = src;
        $('.modal-title').text('Bukti: ' + title);
        $('#imagePreviewModal').modal('show');
    }
</script>
@endpush