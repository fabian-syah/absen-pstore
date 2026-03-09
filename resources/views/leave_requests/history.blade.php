@extends('layout.master')

@section('title')
    Riwayat Izin
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">{{ $page_title ?? 'Riwayat Pengajuan (Selesai)' }}</h4>

                        {{-- TOMBOL KEMBALI KE DAFTAR PENDING --}}
                        <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar Aktif
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User & Cabang</th>
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Bukti</th>
                                    <th>Status & Approver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- KOLOM USER & CABANG --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{ $req->user->division->name ?? '-' }}
                                                        &bull;
                                                        <span
                                                            class="text-primary">{{ $req->user->branch->name ?? 'Pusat' }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark border">{{ ucfirst($req->type) }}</span>
                                        </td>

                                        <td>
                                            @if ($req->type == 'telat')
                                                {{ $req->start_date->format('d/m/Y') }}
                                                <br>
                                                <span class="text-muted" style="font-size: 11px;">
                                                    Jam: {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}
                                                </span>
                                            @else
                                                {{ $req->start_date->format('d M') }}
                                                @if($req->end_date)
                                                    - {{ \Carbon\Carbon::parse($req->end_date)->format('d M') }}
                                                @endif
                                            @endif
                                        </td>

                                        <td style="max-width: 200px; white-space: normal; line-height: 1.4;">
                                            <p class="mb-0 text-muted">{{ Str::limit($req->reason, 40) }}</p>
                                            @if($req->rejection_reason)
                                                <div class="mt-2 p-2 bg-light rounded border border-danger text-danger"
                                                    style="font-size: 11px;">
                                                    <strong>Catatan:</strong> {{ $req->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- KOLOM BUKTI (DIPERBARUI) --}}
                                        <td>
                                            @if($req->file_proof)
                                                {{-- Ubah link menjadi trigger modal --}}
                                                <a href="javascript:void(0)" class="text-primary"
                                                    style="text-decoration: none; cursor: pointer;" data-bs-toggle="modal"
                                                    data-bs-target="#imageModal"
                                                    data-src="{{ asset('storage/' . $req->file_proof) }}" title="Lihat Bukti">
                                                    <i class="mdi mdi-eye"></i> Lihat
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- KOLOM STATUS & APPROVER --}}
                                        <td>
                                            @if ($req->status == 'approved')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-success mb-1" style="width: fit-content;">
                                                        <i class="mdi mdi-check-circle"></i> Disetujui
                                                    </span>
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: {{ $req->approver->name ?? 'Admin' }}
                                                    </small>
                                                </div>

                                            @elseif($req->status == 'rejected')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-danger mb-1" style="width: fit-content;">
                                                        <i class="mdi mdi-close-circle"></i> Ditolak
                                                    </span>
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: {{ $req->approver->name ?? 'Admin' }}
                                                    </small>
                                                </div>

                                            @elseif($req->status == 'cancelled')
                                                <span class="badge badge-opacity-secondary">Dibatalkan</span>
                                            @else
                                                <span class="badge badge-opacity-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Belum ada riwayat data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4 d-flex justify-content-end">
                            {{ $requests->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL UNTUK PREVIEW GAMBAR --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Bukti Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    {{-- Gambar akan di-load di sini --}}
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded shadow-sm"
                        style="max-height: 400px;">
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT UNTUK MENANGANI GAMBAR DI MODAL --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var imageModal = document.getElementById('imageModal');
            imageModal.addEventListener('show.bs.modal', function (event) {
                // Tombol yang memicu modal
                var button = event.relatedTarget;
                // Ambil info dari atribut data-src
                var imageUrl = button.getAttribute('data-src');
                // Update src gambar di dalam modal
                var modalImage = document.getElementById('modalImagePreview');
                modalImage.src = imageUrl;
            });

            // Opsional: Reset gambar saat modal ditutup (agar tidak ada flash gambar lama)
            imageModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('modalImagePreview').src = '';
            });
        });
    </script>
@endsection