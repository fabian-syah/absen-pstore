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
                                    <th class="border-0">User</th>
                                    <th class="border-0 text-center">Tanggal</th>
                                    <th class="border-0 text-center">Status (Audit)</th>
                                    <th class="border-0">Catatan Audit</th>
                                    <th class="border-0 text-center">Foto Audit</th>
                                    <th class="border-0 text-center">Diverifikasi Oleh</th>
                                    <th class="border-0 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="ms-3">
                                                    <p class="font-weight-bold mb-0 text-dark">{{ $attendance->user->name }}</p>
                                                    <small class="text-muted">{{ $attendance->user->branch->name ?? 'N/A' }} | {{ $attendance->user->division->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ $attendance->check_in_time->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $attendance->presence_status_badge }} rounded-pill px-3">
                                                {{ $attendance->presence_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small" title="{{ $attendance->audit_note }}">
                                                {{ Str::limit($attendance->audit_note, 50) ?: '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($attendance->audit_photo_path)
                                                <a href="{{ Storage::url($attendance->audit_photo_path) }}" target="_blank">
                                                    <img src="{{ Storage::url($attendance->audit_photo_path) }}" 
                                                         alt="Audit Photo" 
                                                         class="rounded" 
                                                         style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #eee;">
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-outline-primary rounded-pill">
                                                <i class="mdi mdi-account-star mr-1"></i> {{ $attendance->verifier->name ?? 'System' }}
                                            </span>
                                            <br>
                                            <small class="text-muted" style="font-size: 10px;">{{ $attendance->updated_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-inverse-danger btn-icon-text"
                                                    onclick="confirmRevert({{ $attendance->id }}, '{{ $attendance->user->name }}')">
                                                <i class="mdi mdi-delete-forever btn-icon-prepend"></i> Revert Alpha
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
                                        <td colspan="7" class="text-center py-5">
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

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
