@extends('layout.master')

@section('title')
    {{ $page_title ?? 'Riwayat Izin' }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body">
                    {{-- HEADER & TOMBOL NAVIGASI --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <div>
                            <h4 class="card-title mb-1 text-dark fw-bold">{{ $page_title ?? 'Riwayat Pengajuan (Selesai)' }}</h4>
                            <p class="text-muted small mb-0">Catatan riwayat verifikasi izin &amp; keterlambatan karyawan</p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            {{-- TOMBOL KEMBALI KE DAFTAR PENDING --}}
                            <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1">
                                <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar Aktif
                            </a>

                            {{-- TAB RIWAYAT SELESAI --}}
                            <a href="{{ route('audit.late.history') }}" class="btn btn-sm rounded-3 {{ request()->routeIs('audit.late.history') ? 'btn-info text-white fw-bold shadow-sm' : 'btn-inverse-info' }}">
                                <i class="mdi mdi-check-all"></i> Riwayat Selesai
                            </a>

                            {{-- TAB RIWAYAT DITOLAK --}}
                            <a href="{{ route('audit.late.rejected.history') }}" class="btn btn-sm rounded-3 {{ request()->routeIs('audit.late.rejected.history') ? 'btn-danger text-white fw-bold shadow-sm' : 'btn-inverse-danger' }}">
                                <i class="mdi mdi-close-circle-multiple-outline"></i> Riwayat Ditolak
                            </a>
                        </div>
                    </div>

                    {{-- FILTER BAR --}}
                    <div class="p-3 mb-4 rounded-3 border" style="background: #f8fafc;">
                        <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
                            {{-- Cari Nama / Alasan --}}
                            <div class="col-12 col-md-3">
                                <label class="small fw-bold text-dark mb-1">Cari Karyawan / Alasan</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="mdi mdi-magnify"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Ketik nama atau alasan..." value="{{ request('search') }}">
                                </div>
                            </div>

                            {{-- Filter Tipe --}}
                            <div class="col-6 col-md-2">
                                <label class="small fw-bold text-dark mb-1">Tipe Izin</label>
                                <select name="type" class="form-select form-select-sm">
                                    <option value="all">Semua Tipe</option>
                                    <option value="wfh" {{ request('type') == 'wfh' ? 'selected' : '' }}>WFH</option>
                                    <option value="dinas" {{ request('type') == 'dinas' ? 'selected' : '' }}>Dinas Luar</option>
                                    <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin Pribadi</option>
                                    <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="cuti" {{ request('type') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                    <option value="libur" {{ request('type') == 'libur' ? 'selected' : '' }}>Libur / Off</option>
                                    <option value="telat" {{ request('type') == 'telat' ? 'selected' : '' }}>Telat</option>
                                </select>
                            </div>

                            {{-- Filter Cabang --}}
                            @isset($branches)
                                <div class="col-6 col-md-2">
                                    <label class="small fw-bold text-dark mb-1">Cabang</label>
                                    <select name="branch_id" class="form-select form-select-sm">
                                        <option value="all">Semua Cabang</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endisset

                            {{-- Tanggal Mulai --}}
                            <div class="col-6 col-md-2">
                                <label class="small fw-bold text-dark mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>

                            {{-- Tanggal Selesai --}}
                            <div class="col-6 col-md-2">
                                <label class="small fw-bold text-dark mb-1">Tanggal Selesai</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            {{-- Tombol Filter & Reset --}}
                            <div class="col-12 col-md-1 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill" title="Terapkan Filter">
                                    <i class="mdi mdi-filter"></i>
                                </button>
                                <a href="{{ url()->current() }}" class="btn btn-light btn-sm border" title="Reset Filter">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    {{-- TABEL DATA RIWAYAT --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User &amp; Cabang</th>
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Riwayat</th>
                                    <th class="text-center">Bukti</th>
                                    <th>Status &amp; Approver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- 1. USER & CABANG --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2 shadow-xs"
                                                    style="width: 36px; height: 36px; font-weight: bold; font-size: 13px;">
                                                    {{ substr($req->user->name ?? 'U', 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark" style="font-size: 13px;">{{ $req->user->name ?? 'User #' . $req->user_id }}</span>
                                                    <small class="text-muted" style="font-size: 11px;">
                                                        {{ $req->user->division->name ?? '-' }} &bull;
                                                        <span class="text-primary fw-semibold">{{ $req->user->branch->name ?? 'Pusat' }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. TIPE IZIN --}}
                                        <td>
                                            @if ($req->type == 'sakit')
                                                <span class="badge bg-danger text-white rounded-pill px-2 py-1">Sakit</span>
                                            @elseif($req->type == 'izin')
                                                <span class="badge bg-info text-white rounded-pill px-2 py-1">Izin</span>
                                            @elseif($req->type == 'libur')
                                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1">Libur</span>
                                            @elseif($req->type == 'wfh')
                                                <span class="badge bg-primary text-white rounded-pill px-2 py-1">WFH</span>
                                            @elseif($req->type == 'dinas')
                                                <span class="badge bg-info text-white rounded-pill px-2 py-1">Dinas Luar</span>
                                            @elseif($req->type == 'cuti')
                                                <span class="badge bg-success text-white rounded-pill px-2 py-1">Cuti</span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Telat</span>
                                            @endif
                                        </td>

                                        {{-- 3. WAKTU / TANGGAL --}}
                                        <td>
                                            @if ($req->type == 'telat')
                                                <div class="text-dark" style="font-size: 13px;">
                                                    <i class="mdi mdi-calendar"></i> {{ $req->start_date ? $req->start_date->format('d/m/Y') : '-' }}<br>
                                                    <strong class="text-danger"><i class="mdi mdi-clock"></i>
                                                        {{ $req->start_time ? \Carbon\Carbon::parse($req->start_time)->format('H:i') : '-' }}</strong>
                                                </div>
                                            @else
                                                <div class="text-dark" style="font-size: 13px;">
                                                    <i class="mdi mdi-calendar-range"></i>
                                                    {{ $req->start_date ? $req->start_date->format('d M') : '-' }}
                                                    @if ($req->end_date && $req->start_date && $req->end_date->format('Y-m-d') != $req->start_date->format('Y-m-d'))
                                                        - {{ $req->end_date->format('d M') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 4. ALASAN --}}
                                        <td style="max-width: 220px; white-space: normal; font-size: 13px;">
                                            <p class="mb-0 text-dark">{{ $req->reason }}</p>
                                            @if($req->rejection_reason)
                                                <div class="mt-1 p-2 bg-light rounded border border-danger text-danger" style="font-size: 11px;">
                                                    <strong>Catatan Tolak:</strong> {{ $req->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 5. RIWAYAT (Izin Lainnya dari Karyawan) --}}
                                        <td>
                                            @php
                                                $history = $req->user && $req->user->leaveRequests
                                                    ? $req->user->leaveRequests
                                                        ->where('id', '!=', $req->id)
                                                        ->where('status', 'approved')
                                                        ->sortByDesc('start_date')
                                                        ->take(1)
                                                    : collect();
                                            @endphp
                                            @forelse($history as $h)
                                                <div class="mb-1" style="font-size: 11px; white-space: nowrap;">
                                                    @if ($h->type == 'sakit')
                                                        <span class="badge badge-outline-danger p-1" style="font-size: 10px;">Sakit</span>
                                                    @elseif($h->type == 'izin')
                                                        <span class="badge badge-outline-info p-1" style="font-size: 10px;">Izin</span>
                                                    @elseif($h->type == 'libur')
                                                        <span class="badge badge-outline-secondary p-1" style="font-size: 10px;">Libur</span>
                                                    @elseif($h->type == 'wfh')
                                                        <span class="badge badge-outline-primary p-1" style="font-size: 10px;">WFH</span>
                                                    @elseif($h->type == 'dinas')
                                                        <span class="badge badge-outline-info p-1" style="font-size: 10px;">Dinas</span>
                                                    @elseif($h->type == 'cuti')
                                                        <span class="badge badge-outline-success p-1" style="font-size: 10px;">Cuti</span>
                                                    @else
                                                        <span class="badge badge-outline-warning p-1" style="font-size: 10px;">Telat</span>
                                                    @endif
                                                    <span class="text-dark ms-1">{{ $h->start_date ? $h->start_date->translatedFormat('D, d M') : '-' }}</span>
                                                </div>
                                            @empty
                                                <small class="text-muted" style="font-style: italic;">Bersih</small>
                                            @endforelse
                                        </td>

                                        {{-- 6. BUKTI --}}
                                        <td class="text-center">
                                            @if($req->file_proof)
                                                <a href="javascript:void(0)"
                                                    onclick="window.showImageModal('{{ asset('storage/' . $req->file_proof) }}')"
                                                    class="btn btn-inverse-secondary btn-icon btn-sm rounded-circle" title="Lihat Bukti">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- 7. STATUS & APPROVER --}}
                                        <td>
                                            @if ($req->status == 'approved')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-success mb-1 rounded-pill" style="width: fit-content;">
                                                        <i class="mdi mdi-check-circle me-1"></i> Disetujui
                                                    </span>
                                                    <small class="text-muted" style="font-size: 11px;">
                                                        Oleh: <strong class="text-dark">{{ $req->approver->name ?? 'Admin' }}</strong>
                                                    </small>
                                                </div>
                                            @elseif($req->status == 'rejected')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-danger mb-1 rounded-pill" style="width: fit-content;">
                                                        <i class="mdi mdi-close-circle me-1"></i> Ditolak
                                                    </span>
                                                    <small class="text-muted" style="font-size: 11px;">
                                                        Oleh: <strong class="text-dark">{{ $req->approver->name ?? 'Admin' }}</strong>
                                                    </small>
                                                </div>
                                            @elseif($req->status == 'cancelled')
                                                <span class="badge badge-opacity-secondary rounded-pill">Dibatalkan</span>
                                            @else
                                                <span class="badge badge-opacity-warning rounded-pill">Menunggu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center"
                                                    style="width: 70px; height: 70px;">
                                                    <i class="mdi mdi-file-document-box-remove-outline text-muted" style="font-size: 36px;"></i>
                                                </div>
                                                <h5 class="fw-bold text-dark">Belum Ada Data Riwayat</h5>
                                                <p class="text-muted small mb-0">Tidak ditemukan pengajuan dengan kriteria pencarian tersebut.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW GAMBAR --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="imageModalLabel" style="font-size: 16px;">Bukti Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4" style="background: #f8fafc;">
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded-3 shadow-sm" style="max-height: 70vh; width: auto;">
                </div>
                <div class="modal-footer bg-light px-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT UNTUK PREVIEW GAMBAR --}}
    <script>
        window.showImageModal = function (imageUrl) {
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

        document.addEventListener('DOMContentLoaded', function () {
            var imageModal = document.getElementById('imageModal');
            if (imageModal) {
                imageModal.addEventListener('hidden.bs.modal', function () {
                    var img = document.getElementById('modalImagePreview');
                    if (img) img.src = '';
                });
            }
        });
    </script>
@endsection