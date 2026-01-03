@extends('layout.master')

@section('title')
    Verifikasi Izin & Keterlambatan
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Verifikasi Izin & Keterlambatan</h4>
                        <div>
                            {{-- Tombol History --}}
                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                <a href="{{ route('audit.late.history') }}" class="btn btn-inverse-info btn-sm me-2">
                                    <i class="mdi mdi-history"></i> Lihat Riwayat
                                </a>
                            @endif

                            {{-- Tombol Ajukan Baru (untuk user biasa) --}}
                            @if (in_array(auth()->user()->role, ['user_biasa', 'leader']))
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm">
                                    <i class="mdi mdi-plus"></i> Ajukan Baru
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Informasi Halaman --}}
                    <div class="alert alert-info mb-3">
                        <strong>Informasi:</strong><br>
                        • Total data pending: <strong>{{ $requests->total() }}</strong><br>
                        • Klik tombol mata (<i class="mdi mdi-eye"></i>) untuk melihat bukti foto.<br>
                        • Data diurutkan dari yang <strong>paling lama</strong> diajukan (Prioritas Lama).
                    </div>

                    {{-- Notifikasi Sukses/Error --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- 1. USER INFO --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{ $req->user->division->name ?? '-' }} | 
                                                        <span class="text-primary fw-bold">{{ $req->user->branch->name ?? 'Pusat' }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. TIPE IZIN --}}
                                        <td>
                                            @if ($req->type == 'sakit')
                                                <span class="badge bg-danger text-white">Sakit</span>
                                            @elseif($req->type == 'izin')
                                                <span class="badge bg-info text-white">Izin</span>
                                            @elseif($req->type == 'wfh')
                                                <span class="badge bg-primary text-white">WFH / Dinas</span>
                                            @elseif($req->type == 'cuti')
                                                <span class="badge bg-success text-white">Cuti</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Telat</span>
                                            @endif
                                        </td>

                                        {{-- 3. WAKTU --}}
                                        <td>
                                            @if ($req->type == 'telat')
                                                <div class="text-dark" style="font-size: 13px;">
                                                    <i class="mdi mdi-calendar"></i> {{ $req->start_date->format('d/m/Y') }}<br>
                                                    <strong class="text-danger"><i class="mdi mdi-clock"></i>
                                                        {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}</strong>
                                                </div>
                                            @else
                                                <div class="text-dark" style="font-size: 13px;">
                                                    <i class="mdi mdi-calendar-range"></i>
                                                    {{ $req->start_date->format('d M') }}
                                                    @if ($req->end_date && $req->end_date != $req->start_date)
                                                        - {{ $req->end_date->format('d M') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 4. ALASAN --}}
                                        <td class="text-wrap" style="max-width: 200px;">{{ $req->reason }}</td>

                                        {{-- 5. BUKTI --}}
                                        <td>
                                            @if ($req->file_proof)
                                                <a href="javascript:void(0)" 
                                                   onclick="window.showImageModal('{{ asset('storage/' . $req->file_proof) }}')"
                                                   class="btn btn-inverse-secondary btn-icon btn-sm"
                                                   title="Lihat Bukti">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- 6. STATUS --}}
                                        <td>
                                            <span class="badge badge-opacity-warning">Menunggu</span>
                                        </td>

                                        {{-- 7. AKSI --}}
                                        <td>
                                            {{-- USER: BATALKAN --}}
                                            @if (auth()->id() == $req->user_id)
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin membatalkan pengajuan?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger" title="Batalkan">
                                                        <i class="mdi mdi-close-circle"></i> Batal
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- ADMIN/AUDIT: APPROVE & REJECT --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                <form action="{{ route('late.approve', $req->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Setujui pengajuan ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm p-2" title="Setujui">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                </form>
                                                
                                                {{-- Tombol Reject dengan data dinamis untuk iPhone --}}
                                                <button type="button" class="btn btn-danger btn-sm p-2" 
                                                        onclick="window.openRejectModal('{{ $req->id }}', '{{ route('late.reject', $req->id) }}')">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                    <i class="mdi mdi-check-all text-success" style="font-size: 40px;"></i>
                                                </div>
                                                <h4 class="fw-bold text-dark">Semua Beres!</h4>
                                                <p class="text-muted">Tidak ada pengajuan izin pending.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REJECT DINAMIS (Paling Stabil untuk iPhone/iOS) --}}
    <div class="modal fade" id="rejectModalDynamic" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRejectDynamic" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control text-dark" rows="3" required placeholder="Tulis alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW GAMBAR --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded shadow-sm" style="max-height: 70vh; width: auto;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // FUNGSI UNTUK MODAL REJECT (SOLUSI IPHONE)
        window.openRejectModal = function(reqId, actionUrl) {
            var modalElement = document.getElementById('rejectModalDynamic');
            var formElement = document.getElementById('formRejectDynamic');
            
            // Set action URL secara dinamis
            formElement.action = actionUrl;
            
            // Buka Modal
            try {
                $(modalElement).modal('show');
            } catch (e) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        };

        // FUNGSI UNTUK PREVIEW GAMBAR
        window.showImageModal = function(imageUrl) {
            var imgElement = document.getElementById('modalImagePreview');
            if (imgElement) {
                imgElement.src = imageUrl;
            }

            try {
                $('#imageModal').modal('show');
            } catch (e) {
                var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
                myModal.show();
            }
        };

        // Reset data saat modal ditutup agar tidak conflict
        $(document).ready(function() {
            $('#imageModal').on('hidden.bs.modal', function () {
                $('#modalImagePreview').attr('src', '');
            });
            
            $('#rejectModalDynamic').on('hidden.bs.modal', function () {
                $('#formRejectDynamic').attr('action', '');
                $(this).find('textarea').val('');
            });
        });
    </script>
@endsection