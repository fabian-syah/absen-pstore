@extends('layout.master')

@section('title')
    Riwayat Izin Saya
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Riwayat Izin Saya (Selesai)</h4>
                        
                        {{-- Tombol kembali ke Dashboard atau Halaman Utama --}}
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-home"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    {{-- Kolom User dihapus karena ini riwayat pribadi --}}
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
                                        {{-- KOLOM TIPE --}}
                                        <td>
                                            @php
                                                $badgeClass = match($req->type) {
                                                    'sakit' => 'bg-danger text-white',
                                                    'izin' => 'bg-info text-white',
                                                    'telat' => 'bg-warning text-dark',
                                                    'cuti' => 'bg-primary text-white',
                                                    'wfh' => 'bg-success text-white',
                                                    default => 'bg-secondary text-white'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} border">
                                                {{ ucfirst($req->type) }}
                                            </span>
                                        </td>

                                        {{-- KOLOM TANGGAL --}}
                                        <td>
                                            @if ($req->type == 'telat')
                                                {{ $req->start_date->format('d/m/Y') }} 
                                                <br>
                                                <span class="text-muted" style="font-size: 11px;">
                                                    Jam: {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}
                                                </span>
                                            @else
                                                {{ $req->start_date->format('d M Y') }}
                                                @if($req->end_date)
                                                    <br><small class="text-muted">s/d {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</small>
                                                @endif
                                            @endif
                                        </td>

                                        {{-- KOLOM ALASAN --}}
                                        <td class="text-muted" style="max-width: 250px; white-space: normal; line-height: 1.2;">
                                            {{ $req->reason }}
                                            @if($req->rejection_reason)
                                                <div class="mt-1 text-danger small">
                                                    <strong>Catatan:</strong> {{ $req->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        
                                        {{-- KOLOM BUKTI --}}
                                        <td>
                                            @if($req->file_proof)
                                                <a href="javascript:void(0)" 
                                                   class="text-primary" 
                                                   style="text-decoration: none; cursor: pointer;"
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#imageModal"
                                                   data-src="{{ asset('storage/' . $req->file_proof) }}"
                                                   title="Lihat Bukti">
                                                    <i class="mdi mdi-image"></i> Lihat
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
                                                        Oleh: {{ $req->approver->name ?? 'System' }}
                                                    </small>
                                                </div>
                                            @elseif($req->status == 'rejected')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-danger mb-1" style="width: fit-content;">
                                                        <i class="mdi mdi-close-circle"></i> Ditolak
                                                    </span>
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: {{ $req->approver->name ?? 'System' }}
                                                    </small>
                                                </div>
                                            @elseif($req->status == 'cancelled')
                                                <span class="badge badge-opacity-secondary">Dibatalkan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4">Belum ada riwayat izin.</td></tr>
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

    {{-- MODAL UNTUK PREVIEW GAMBAR (SAMA SEPERTI SEBELUMNYA) --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Bukti Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
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