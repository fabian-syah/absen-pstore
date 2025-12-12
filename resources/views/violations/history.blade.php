@extends('layout.master')

@section('title', 'Riwayat History Pelanggaran')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card bg-light">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1 text-secondary">History Pelanggaran (Arsip)</h4>
                        <p class="text-muted small mb-0">
                            Daftar pelanggaran yang masa hukumannya <span class="text-success fw-bold">sudah selesai</span>.
                        </p>
                    </div>
                    
                    <div>
                        <a href="{{ route('violations.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Data Aktif
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Status</th>
                                <th>Nama Pelanggar</th>
                                <th>Masalah</th>
                                <th>Bukti</th>
                                <th>Tgl Input</th>
                                <th>Selesai Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $v)
                                <tr class="text-muted">
                                    <td>
                                        <span class="badge badge-outline-secondary">
                                            {{ strtoupper($v->category) }} - SELESAI
                                        </span>
                                    </td>
                                    <td class="font-weight-bold">{{ $v->user->name ?? 'User Terhapus' }}</td>
                                    <td>
                                        <span>{{ $v->title }}</span>
                                        <br>
                                        <small>{{ Str::limit($v->description, 40) }}</small>
                                    </td>
                                    <td>
                                        @if($v->photo_path)
                                            <img src="{{ asset('storage/' . $v->photo_path) }}" 
                                                 alt="Bukti" 
                                                 class="img-thumbnail grayscale" 
                                                 style="width: 40px; height: 40px; object-fit: cover; cursor: pointer; filter: grayscale(100%);"
                                                 onclick="showImageModal('{{ asset('storage/' . $v->photo_path) }}', '{{ $v->title }}')">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $v->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="text-success fw-bold">
                                            <i class="mdi mdi-check-circle"></i> {{ $v->expires_at->format('d M Y') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="mdi mdi-history text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                        <p class="text-muted mt-2">Belum ada history pelanggaran yang selesai.</p>
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

{{-- Modal Preview Code (Bisa dipisah jadi partials/image_modal.blade.php jika mau rapi) --}}
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
    function showImageModal(src, title) {
        document.getElementById('previewImageSrc').src = src;
        $('.modal-title').text('Bukti History: ' + title);
        $('#imagePreviewModal').modal('show');
    }
</script>
@endpush