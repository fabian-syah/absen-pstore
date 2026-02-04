@extends('layout.master')

@section('title')
    Persetujuan Cuti
@endsection

@section('heading')
    <h4 class="mb-0 fw-bold">Persetujuan Cuti</h4>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 rounded-4 shadow-sm border-0">
                    <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($requests->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                    <div class="card-body">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="mdi mdi-check-all text-success" style="font-size: 40px;"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Semua Beres!</h4>
                        <p class="text-muted">Tidak ada pengajuan cuti yang perlu persetujuan saat ini.</p>
                    </div>
                </div>
            @else

                <h5 class="mb-3 text-muted fw-bold">Menunggu Persetujuan ({{ $requests->total() }})</h5>

                @foreach($requests as $req)
                    <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                {{-- LEFT SIDE: USER PROFIL & STATS --}}
                                <div
                                    class="col-md-3 bg-light border-end d-flex flex-column align-items-center justify-content-center p-4 text-center">
                                    {{-- Photo --}}
                                    <div class="avatar-lg mb-3">
                                        @if($req->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $req->user->profile_photo_path) }}"
                                                class="rounded-circle shadow-sm"
                                                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff;">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 80px; height: 80px; font-size: 32px; fw-bold; border: 3px solid #fff;">
                                                {{ substr($req->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">{{ $req->user->name }}</h6>
                                    <span
                                        class="badge bg-white text-dark border mb-3">{{ $req->user->division->name ?? '-' }}</span>

                                    {{-- Leave Stats Grid --}}
                                    <div class="row w-100 g-2">
                                        <div class="col-4 px-1">
                                            <div class="bg-white rounded p-2 border">
                                                <small class="d-block text-muted" style="font-size: 10px;">JATAH</small>
                                                <strong class="text-primary">{{ $req->user->yearly_leave_limit ?? 12 }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="bg-white rounded p-2 border">
                                                <small class="d-block text-muted" style="font-size: 10px;">TERPAKAI</small>
                                                <strong class="text-warning">{{ $req->user->leave_taken ?? 0 }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="bg-white rounded p-2 border">
                                                <small class="d-block text-muted" style="font-size: 10px;">SISA</small>
                                                <strong class="text-success">{{ $req->user->leave_balance ?? 12 }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT SIDE: REQUEST DETAILS --}}
                                <div class="col-md-9 p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Tanggal Pengajuan</h6>
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-calendar-range text-primary fs-4 me-2"></i>
                                                <h5 class="mb-0 fw-bold text-dark">
                                                    {{ $req->start_date->format('d M Y') }}
                                                    @if($req->end_date && $req->end_date != $req->start_date)
                                                        - {{ $req->end_date->format('d M Y') }}
                                                    @endif
                                                </h5>
                                                <span class="badge bg-soft-primary text-primary ms-3">
                                                    {{ $req->start_date->diffInDays($req->end_date ?? $req->start_date) + 1 }} Hari
                                                </span>
                                            </div>
                                        </div>

                                        {{-- PROOF BUTTON --}}
                                        @if($req->file_proof)
                                            <button onclick="window.showImageModal('{{ asset('storage/' . $req->file_proof) }}')"
                                                class="btn btn-light btn-sm rounded-pill text-muted border">
                                                <i class="mdi mdi-paperclip me-1"></i> Lihat Bukti
                                            </button>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <label class="small text-muted fw-bold">ALASAN CUTI:</label>
                                        <p
                                            class="mb-0 text-dark bg-light p-3 rounded-3 fst-italic border-start border-4 border-primary">
                                            "{{ $req->reason }}"
                                        </p>
                                    </div>

                                    <hr class="border-light">

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><i class="mdi mdi-clock-outline me-1"></i> Diajukan pada
                                            {{ $req->created_at->format('d M Y H:i') }}</small>

                                        <div>
                                            {{-- REJECT BUTTON --}}
                                            <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold me-2"
                                                onclick="window.openRejectModal('{{ $req->id }}', '{{ route('late.reject', $req->id) }}')">
                                                <i class="mdi mdi-close me-1"></i> Tolak
                                            </button>

                                            {{-- APPROVE BUTTON --}}
                                            <form action="{{ route('late.approve', $req->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin setujui cuti ini? Saldo user sudah terpotong otomatis.')">
                                                @csrf
                                                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                                    <i class="mdi mdi-check me-1"></i> SETUJUI
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4 d-flex justify-content-end">
                    {{ $requests->links('pagination::bootstrap-5') }}
                </div>

            @endif
        </div>
    </div>

    {{-- MODAL REJECT DINAMIS --}}
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
                            <label class="form-label font-weight-bold">Alasan Penolakan <span
                                    class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control text-dark" rows="3" required
                                placeholder="Tulis alasan penolakan..."></textarea>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded shadow-sm"
                        style="max-height: 70vh; width: auto;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // FUNGSI UNTUK MODAL REJECT
        window.openRejectModal = function (reqId, actionUrl) {
            var modalElement = document.getElementById('rejectModalDynamic');
            var formElement = document.getElementById('formRejectDynamic');
            formElement.action = actionUrl;

            // Initializing modal
            try {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            } catch (e) {
                $(modalElement).modal('show');
            }
        };

        // FUNGSI UNTUK PREVIEW GAMBAR
        window.showImageModal = function (imageUrl) {
            var imgElement = document.getElementById('modalImagePreview');
            imgElement.src = imageUrl;

            try {
                var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
                myModal.show();
            } catch (e) {
                $('#imageModal').modal('show');
            }
        };
    </script>
@endpush