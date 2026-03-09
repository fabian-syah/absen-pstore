@extends('layout.master')

@section('title')
    Riwayat Izin Saya
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Riwayat Izin Saya</h4>
                            <p class="text-muted small">Daftar pengajuan izin, sakit, cuti, dan keterlambatan.</p>
                        </div>

                        <i class="mdi mdi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    {{-- STATISTIK CUTI --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-3 mb-md-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-white mb-1">Jatah Cuti Tahunan</h6>
                                    <h2 class="mb-0">{{ auth()->user()->yearly_leave_limit ?? 12 }} <small
                                            class="fs-6">Hari</small></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark mb-3 mb-md-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-dark mb-1">Cuti Terpakai</h6>
                                    <h2 class="mb-0">{{ auth()->user()->leave_taken ?? 0 }} <small class="fs-6">Hari</small>
                                    </h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-white mb-1">Sisa Cuti</h6>
                                    <h2 class="mb-0">{{ auth()->user()->leave_balance ?? 12 }} <small
                                            class="fs-6">Hari</small></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-3 rounded-start">Tipe Izin</th>
                                    <th class="py-3">Waktu / Tanggal</th>
                                    <th class="py-3">Alasan</th>
                                    <th class="py-3">Bukti</th>
                                    <th class="py-3 rounded-end">Status & Approver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- KOLOM TIPE --}}
                                        <td class="ps-3">
                                            @php
                                                $badgeClass = match ($req->type) {
                                                    'sakit' => 'bg-danger text-white',
                                                    'izin' => 'bg-info text-white',
                                                    'telat' => 'bg-warning text-dark',
                                                    'cuti' => 'bg-primary text-white',
                                                    'wfh' => 'bg-success text-white',
                                                    'libur' => 'bg-secondary text-white',
                                                    default => 'bg-dark text-white'
                                                };

                                                // Label khusus untuk telat dan libur
                                                $typeLabel = match ($req->type) {
                                                    'telat' => 'Izin Telat',
                                                    'libur' => 'Libur (Off Day)',
                                                    default => ucfirst($req->type)
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} border px-3 py-2 rounded-pill">
                                                {{ $typeLabel }}
                                            </span>
                                        </td>

                                        {{-- KOLOM WAKTU / TANGGAL (PERBAIKAN UTAMA DISINI) --}}
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark">
                                                    {{ \Carbon\Carbon::parse($req->start_date)->translatedFormat('d M Y') }}
                                                </span>

                                                {{-- Jika Telat, Tampilkan Jam --}}
                                                @if ($req->type == 'telat')
                                                    <small class="text-muted mt-1">
                                                        <i class="mdi mdi-clock-outline me-1"></i>
                                                        {{-- Cek apakah start_time ada isinya --}}
                                                        @if(!empty($req->start_time))
                                                            {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }} WIB
                                                        @else
                                                            -
                                                        @endif
                                                    </small>

                                                    {{-- Jika Cuti/Sakit/Izin > 1 Hari, Tampilkan Sampai Tanggal --}}
                                                @elseif($req->end_date && $req->end_date != $req->start_date)
                                                    <small class="text-muted mt-1">
                                                        s/d {{ \Carbon\Carbon::parse($req->end_date)->translatedFormat('d M Y') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- KOLOM ALASAN --}}
                                        <td>
                                            <p class="mb-0 text-muted text-wrap" style="max-width: 250px; line-height: 1.4;">
                                                {{ $req->reason }}
                                            </p>
                                            @if($req->rejection_reason)
                                                <div class="mt-2 p-2 bg-light rounded border border-danger text-danger small">
                                                    <strong>Catatan:</strong> {{ $req->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- KOLOM BUKTI --}}
                                        <td>
                                            @if($req->file_proof)
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    data-bs-toggle="modal" data-bs-target="#imageModal"
                                                    data-src="{{ asset('storage/' . $req->file_proof) }}">
                                                    <i class="mdi mdi-image-area me-1"></i> Lihat
                                                </button>
                                            @else
                                                <span class="text-muted small fst-italic">- Tidak ada -</span>
                                            @endif
                                        </td>

                                        {{-- KOLOM STATUS --}}
                                        <td>
                                            @if ($req->status == 'approved')
                                                <div class="d-flex align-items-center text-success fw-bold">
                                                    <i class="mdi mdi-check-circle fs-5 me-2"></i> Disetujui
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Oleh: {{ $req->approver->name ?? 'System' }}
                                                </small>
                                            @elseif($req->status == 'rejected')
                                                <div class="d-flex align-items-center text-danger fw-bold">
                                                    <i class="mdi mdi-close-circle fs-5 me-2"></i> Ditolak
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Oleh: {{ $req->approver->name ?? 'System' }}
                                                </small>
                                            @elseif($req->status == 'cancelled')
                                                <span class="badge bg-secondary text-white">Dibatalkan</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="mdi mdi-file-document-outline text-muted"
                                                    style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">Belum ada riwayat pengajuan izin.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4 px-3 d-flex justify-content-end">
                            {{ $requests->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW GAMBAR --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Bukti Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light p-4">
                    <img id="modalImagePreview" src="" class="img-fluid rounded shadow-sm"
                        style="max-height: 500px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var imageModal = document.getElementById('imageModal');
            imageModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var imageUrl = button.getAttribute('data-src');
                var modalImage = document.getElementById('modalImagePreview');
                modalImage.src = imageUrl;
            });
            imageModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('modalImagePreview').src = '';
            });
        });
    </script>
@endsection