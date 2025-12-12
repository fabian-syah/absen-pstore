@extends('layout.master')

@section('title', 'Daftar Pelanggaran Aktif')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Pelanggaran Aktif</h4>
                        <p class="text-muted small mb-0">
                            Data pelanggaran yang masa berlakunya <span class="text-danger fw-bold">belum habis</span>.
                        </p>
                    </div>
                    
                    <div class="d-flex gap-2">
                        {{-- TOMBOL MENUJU HISTORY --}}
                        <a href="{{ route('violations.history') }}" class="btn btn-secondary btn-sm text-white mr-2">
                            <i class="mdi mdi-history"></i> Lihat History (Selesai)
                        </a>

                        @if(in_array(auth()->user()->role, ['admin', 'audit']))
                            <a href="{{ route('violations.create') }}" class="btn btn-primary btn-sm text-white">
                                <i class="mdi mdi-plus"></i> Tambah Pelanggaran
                            </a>
                        @endif
                    </div>
                </div>

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
                                @if(auth()->user()->role == 'admin')
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $v)
                                <tr>
                                    <td>
                                        @if($v->category == 'berat')
                                            <label class="badge badge-danger">BERAT</label>
                                        @elseif($v->category == 'sedang')
                                            <label class="badge badge-warning">SEDANG</label>
                                        @else
                                            <label class="badge badge-info">RINGAN</label>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">{{ $v->user->name ?? 'User Terhapus' }}</td>
                                    <td>
                                        <span class="d-block text-dark">{{ $v->title }}</span>
                                        <small class="text-muted">{{ Str::limit($v->description, 40) }}</small>
                                    </td>
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
                                    <td>{{ $v->reporter->name ?? 'System' }}</td>
                                    <td>{{ $v->created_at->format('d M Y') }}</td>
                                    <td>
                                        <span class="text-danger fw-bold">{{ $v->expires_at->format('d M Y') }}</span>
                                        <br>
                                        <small class="text-muted">({{ now()->diffInDays($v->expires_at) }} hari lagi)</small>
                                    </td>

                                    @if(auth()->user()->role == 'admin')
                                        <td>
                                            <a href="{{ route('violations.edit', $v->id) }}" class="btn btn-warning btn-sm icon-btn p-2">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('violations.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm icon-btn p-2">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
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

{{-- Include Modal Image --}}
@include('violations.partials.image_modal') 

@endsection

@push('scripts')
<script>
    function showImageModal(src, title) {
        // Pastikan modal ada di partials atau footer
        $('#previewImageSrc').attr('src', src);
        $('.modal-title').text('Bukti: ' + title);
        $('#imagePreviewModal').modal('show');
    }
</script>
@endpush