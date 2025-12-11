@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Riwayat Pelanggaran</h4>
                        <p class="text-muted small mb-0">
                            Masa Berlaku: 
                            <span class="text-danger fw-bold">Berat (1 Thn)</span>, 
                            <span class="text-warning fw-bold">Sedang (6 Bln)</span>, 
                            <span class="text-info fw-bold">Ringan (1 Bln)</span>.
                        </p>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        @if(in_array(auth()->user()->role, ['admin', 'audit']))
                            <a href="{{ route('violations.create') }}" class="btn btn-primary btn-sm text-white">
                                <i class="mdi mdi-plus"></i> Tambah
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
                                <th>Judul</th>
                                <th>Deskripsi & Ket</th>
                                <th>Bukti Foto</th>
                                <th>Pelapor</th>
                                <th>Tanggal Input</th>
                                <th>Masa Berlaku</th> {{-- KOLOM BARU --}}
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
                                    <td>{{ $v->title }}</td>
                                    <td>
                                        <p class="mb-1" style="font-size: 0.9rem; line-height: 1.2;">{{ Str::limit($v->description, 50) }}</p>
                                        <small class="text-muted">Ket: {{ $v->notes ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($v->photo_path)
                                            <img src="{{ asset('storage/' . $v->photo_path) }}" 
                                                 alt="Bukti" 
                                                 class="img-thumbnail" 
                                                 style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                                 onclick="showImageModal('{{ asset('storage/' . $v->photo_path) }}', '{{ $v->title }}')">
                                        @else
                                            <span class="text-muted text-small">Tidak ada foto</span>
                                        @endif
                                    </td>
                                    <td>{{ $v->reporter->name ?? 'Sistem' }}</td>
                                    <td>{{ $v->created_at->format('d M Y') }}</td>
                                    
                                    {{-- KOLOM MASA BERLAKU --}}
                                    <td>
                                        @if($v->expires_at)
                                            @if($v->expires_at->isPast())
                                                <span class="badge badge-success">Sudah Berakhir</span>
                                                <br>
                                                <small class="text-muted">{{ $v->expires_at->format('d M Y') }}</small>
                                            @else
                                                <span class="fw-bold text-danger">{{ $v->expires_at->format('d M Y') }}</span>
                                                <br>
                                                <small class="text-muted">({{ now()->diffInDays($v->expires_at) }} hari lagi)</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    @if(auth()->user()->role == 'admin')
                                        <td>
                                            <a href="{{ route('violations.edit', $v->id) }}" class="btn btn-warning btn-sm icon-btn p-2">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('violations.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                                    <td colspan="9" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-check-circle-outline text-success" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">Tidak ada data pelanggaran.</p>
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

{{-- Modal Preview Gambar --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="imagePreviewLabel">Bukti Pelanggaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#imagePreviewModal').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
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
    function showImageModal(src, title) {
        document.getElementById('previewImageSrc').src = src;
        document.getElementById('imagePreviewLabel').innerText = 'Bukti: ' + title;
        $('#imagePreviewModal').modal('show');
    }
</script>
@endpush