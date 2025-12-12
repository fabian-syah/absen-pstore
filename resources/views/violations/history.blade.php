@extends('layout.master')

@section('title', 'Riwayat History Pelanggaran')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card bg-light">
            <div class="card-body">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1 text-secondary">History Pelanggaran (Arsip)</h4>
                        <p class="text-muted small mb-0">
                            Daftar pelanggaran yang statusnya <span class="text-success fw-bold">sudah selesai</span> atau kadaluarsa.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('violations.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Data Aktif
                        </a>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Status</th>
                                <th>Pelanggar</th>
                                <th>Masalah</th>
                                <th>Bukti</th>
                                <th>Penyelesaian (Log)</th> {{-- Kolom Baru --}}
                                <th>Waktu Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $v)
                                <tr class="text-muted">
                                    {{-- Status --}}
                                    <td>
                                        <span class="badge badge-outline-secondary">
                                            {{ strtoupper($v->category) }}
                                        </span>
                                    </td>

                                    {{-- Nama --}}
                                    <td class="font-weight-bold">{{ $v->user->name ?? '-' }}</td>

                                    {{-- Masalah --}}
                                    <td>
                                        <span>{{ $v->title }}</span>
                                        <br>
                                        <small>{{ Str::limit($v->description, 35) }}</small>
                                    </td>

                                    {{-- Bukti --}}
                                    <td>
                                        @if($v->photo_path)
                                            <button class="btn btn-sm btn-light border" onclick="showImageModal('{{ asset('storage/' . $v->photo_path) }}', '{{ $v->title }}')">
                                                <i class="mdi mdi-image"></i>
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    
                                    {{-- LOG PENYELESAIAN (Kolom Baru) --}}
                                    <td>
                                        @if($v->resolution_notes)
                                            <div class="bg-white p-2 rounded border small">
                                                <strong><i class="mdi mdi-comment-check text-success"></i> Catatan:</strong> <br>
                                                "{{ $v->resolution_notes }}"
                                                <br>
                                                <span class="text-muted" style="font-size: 0.8em; font-style: italic;">
                                                    Oleh: {{ $v->resolver->name ?? 'Admin/Audit' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted small font-italic">Habis Masa Berlaku (Otomatis)</span>
                                        @endif
                                    </td>

                                    {{-- Tanggal Selesai --}}
                                    <td>
                                        <span class="text-success fw-bold">
                                            <i class="mdi mdi-calendar-check"></i> {{ $v->expires_at->format('d M Y') }}
                                        </span>
                                        @if($v->resolved_at)
                                             <br><small class="text-muted">Jam: {{ $v->resolved_at->format('H:i') }}</small>
                                        @endif
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

{{-- Modal Preview Image (Reused) --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Bukti History</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="$('#imagePreviewModal').modal('hide')">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImageSrc" src="" class="img-fluid rounded" style="max-height: 80vh; filter: grayscale(100%);">
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