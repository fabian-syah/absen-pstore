@extends('layout.master')

@section('title', 'Monitoring Edit Audit')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1 font-weight-bold" style="color: #0d6efd;">
                                <i class="mdi mdi-shield-check-outline mr-2"></i>Monitoring Edit Audit
                            </h4>
                            <p class="text-muted small">Daftar absensi yang telah diverifikasi atau diubah oleh tim Audit.</p>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                            <i class="mdi mdi-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover border-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">User & Cabang</th>
                                    <th class="border-0 text-center">Tanggal Absen</th>
                                    <th class="border-0 text-center">Foto User</th>
                                    <th class="border-0 text-center">Status (Audit)</th>
                                    <th class="border-0 text-center">Foto Bukti Audit</th>
                                    <th class="border-0">Catatan Audit</th>
                                    <th class="border-0 text-center">Diverifikasi Oleh</th>
                                    <th class="border-0 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="ms-1">
                                                    <p class="font-weight-bold mb-0 text-dark">
                                                        <a href="{{ route('attendance.history', ['employeeId' => $attendance->user_id]) }}" 
                                                           class="text-decoration-none text-dark hover-primary" 
                                                           title="Lihat Riwayat Lengkap">
                                                            {{ $attendance->user->name }}
                                                            <i class="mdi mdi-open-in-new small text-muted" style="font-size: 10px;"></i>
                                                        </a>
                                                    </p>
                                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;">
                                                        {{ $attendance->user->branch->name ?? 'N/A' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="font-weight-bold">{{ $attendance->check_in_time->format('d/m/Y') }}</div>
                                            <small class="text-muted d-none d-md-block">{{ $attendance->check_in_time->format('l') }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($attendance->photo_path)
                                                <img src="{{ Storage::url($attendance->photo_path) }}" 
                                                     alt="User Photo" 
                                                     class="rounded shadow-sm img-preview-trigger" 
                                                     style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #0d6efd; cursor: pointer;"
                                                     data-src="{{ Storage::url($attendance->photo_path) }}"
                                                     data-title="Foto Absen: {{ $attendance->user->name }}">
                                            @else
                                                <span class="text-muted" style="font-size: 10px;">No Photo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $attendance->presence_status_badge }} rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                {{ $attendance->presence_status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($attendance->audit_photo_path)
                                                <img src="{{ Storage::url($attendance->audit_photo_path) }}" 
                                                     alt="Audit Photo" 
                                                     class="rounded shadow-sm img-preview-trigger" 
                                                     style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #ffc107; cursor: pointer;"
                                                     data-src="{{ Storage::url($attendance->audit_photo_path) }}"
                                                     data-title="Bukti Audit: {{ $attendance->user->name }}">
                                            @else
                                                <span class="text-muted small">Tanpa Bukti</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="p-2 bg-light rounded small" style="max-width: 150px; border-left: 3px solid #0d6efd; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $attendance->audit_note ?: 'Tidak ada catatan' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="font-weight-bold text-primary small">{{ $attendance->verifier->name ?? 'System' }}</div>
                                            <small class="text-muted" style="font-size: 9px;">
                                                {{ $attendance->updated_at->format('d/m H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger btn-rounded px-2"
                                                    onclick="confirmRevert({{ $attendance->id }}, '{{ $attendance->user->name }}')">
                                                <i class="mdi mdi-refresh"></i>
                                            </button>
                                            <form id="revert-form-{{ $attendance->id }}" 
                                                  action="{{ route('admin.audit-monitor.revert', $attendance->id) }}" 
                                                  method="POST" 
                                                  class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="mdi mdi-shield-off-outline text-muted" style="font-size: 3rem;"></i>
                                                <p class="mt-2 text-muted">Belum ada data editan audit yang ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-center overflow-auto">
                        {!! $attendances->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Preview Foto --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-primary text-white border-0 py-2">
                <h6 class="modal-title" id="modalTitle">Preview Foto</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark text-center">
                <img src="" id="modalImage" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('imagePreviewModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalImg = document.getElementById('modalImage');
        const modalTitle = document.getElementById('modalTitle');

        document.querySelectorAll('.img-preview-trigger').forEach(img => {
            img.addEventListener('click', function() {
                modalImg.src = this.getAttribute('data-src');
                modalTitle.innerText = this.getAttribute('data-title');
                modal.show();
            });
        });
    });

    function confirmRevert(id, userName) {
        Swal.fire({
            title: 'Hapus Editan Audit?',
            text: "Data absensi " + userName + " akan dihapus permanen dan status user kembali menjadi Alpha.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus & Alpha!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('revert-form-' + id).submit();
            }
        })
    }
</script>
@endpush
@endsection
