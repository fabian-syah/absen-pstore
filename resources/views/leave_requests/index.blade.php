@extends('layout.master')

@section('title')
    Verifikasi Izin & Keterlambatan
@endsection

@section('content')
    @php
        $isSuperAdmin = strtolower(trim(auth()->user()->login_id ?? '')) === 'superadmin';
    @endphp

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                        <h4 class="card-title mb-3 mb-md-0">Verifikasi Izin & Keterlambatan</h4>
                        <div class="d-flex flex-wrap gap-2">
                            {{-- FITUR KHUSUS SUPERADMIN: AKSI MASSAL (BULK ACC & BULK REJECT) --}}
                            @if ($isSuperAdmin)
                                <button type="button" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBulkActionSuperadmin">
                                    <i class="mdi mdi-flash me-1"></i> Aksi Massal Superadmin
                                </button>
                            @endif

                            {{-- Tombol History --}}
                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                <a href="{{ route('audit.late.history') }}" class="btn btn-inverse-info btn-sm">
                                    <i class="mdi mdi-history"></i> Riwayat Selesai
                                </a>
                                <a href="{{ route('audit.late.rejected.history') }}" class="btn btn-inverse-danger btn-sm">
                                    <i class="mdi mdi-close-circle-multiple-outline"></i> Riwayat Ditolak
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

                    {{-- Banner Khusus Superadmin --}}
                    @if ($isSuperAdmin)
                        <div class="alert alert-warning border border-warning d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-shield-crown text-warning me-3" style="font-size: 32px;"></i>
                                <div>
                                    <strong class="text-dark">Fitur Khusus Superadmin Aktif:</strong>
                                    <span class="text-muted d-block" style="font-size: 13px;">
                                        Anda dapat menyetujui (ACC) atau menolak izin secara massal berdasarkan rentang tanggal & jenis izin (misal: 22-27 September WFH & Dinas Luar), atau memilih baris langsung pada tabel.
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3" data-bs-toggle="modal" data-bs-target="#modalBulkActionSuperadmin">
                                <i class="mdi mdi-flash me-1"></i> Buka Menu Aksi Massal
                            </button>
                        </div>
                    @endif

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

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            {{ session('warning') }}
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
                                    @if ($isSuperAdmin)
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="checkAllRows" class="form-check-input" title="Pilih Semua di Halaman Ini" style="cursor: pointer; width: 18px; height: 18px;">
                                        </th>
                                    @endif
                                    <th>User</th>
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Riwayat</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- 0. CHECKBOX SUPERADMIN --}}
                                        @if ($isSuperAdmin)
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $req->id }}" style="cursor: pointer; width: 18px; height: 18px;">
                                            </td>
                                        @endif

                                        {{-- 1. USER INFO --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name ?? 'U', 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name ?? 'User #' . $req->user_id }}</span>
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
                                            @elseif($req->type == 'libur')
                                                <span class="badge bg-secondary text-white">Libur</span>
                                            @elseif($req->type == 'wfh')
                                                <span class="badge bg-primary text-white">WFH / Dinas</span>
                                            @elseif($req->type == 'dinas')
                                                <span class="badge bg-primary text-white">Dinas Luar</span>
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
                                        <td class="text-wrap" style="max-width: 200px;">{{ $req->reason }}</td>

                                        {{-- 5. RIWAYAT --}}
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
                                                        <span class="badge badge-outline-primary p-1" style="font-size: 10px;">Dinas</span>
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
                                        <td>
                                            @if ($req->file_proof)
                                                <a href="javascript:void(0)"
                                                    onclick="window.showImageModal('{{ asset('storage/' . $req->file_proof) }}')"
                                                    class="btn btn-inverse-secondary btn-icon btn-sm" title="Lihat Bukti">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- 7. STATUS --}}
                                        <td>
                                            <span class="badge badge-opacity-warning">Menunggu</span>
                                        </td>

                                        {{-- 8. AKSI --}}
                                        <td>
                                            {{-- USER: BATALKAN --}}
                                            @if (auth()->id() == $req->user_id)
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="confirmSubmit(event, this, 'Yakin ingin membatalkan pengajuan?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger" title="Batalkan">
                                                        <i class="mdi mdi-close-circle"></i> Batal
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- ADMIN/AUDIT: APPROVE & REJECT --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                <form action="{{ route('late.approve', $req->id) }}" method="POST" class="d-inline"
                                                    onsubmit="confirmSubmit(event, this, 'Setujui pengajuan ini?')">
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
                                        <td colspan="{{ $isSuperAdmin ? '9' : '8' }}" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center"
                                                    style="width: 80px; height: 80px;">
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

    {{-- FLOATING ACTION BAR FOR SELECTED ROWS (SUPERADMIN) --}}
    @if ($isSuperAdmin)
        <div id="selectedActionBar" class="card shadow-lg border border-primary position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" style="z-index: 1050; min-width: 340px; max-width: 90%;">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-3 bg-white rounded">
                <div>
                    <i class="mdi mdi-checkbox-marked-circle-outline text-primary me-1"></i>
                    <strong id="selectedCountText">0</strong> pengajuan dipilih di halaman ini
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="window.approveSelectedRows()">
                        <i class="mdi mdi-check me-1"></i> Setujui Terpilih
                    </button>
                    <button type="button" class="btn btn-danger btn-sm fw-bold px-3" onclick="window.rejectSelectedRows()">
                        <i class="mdi mdi-close me-1"></i> Tolak Terpilih
                    </button>
                    <button type="button" class="btn btn-light btn-sm text-secondary" onclick="window.clearSelectedRows()">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL AKSI MASSAL SUPERADMIN (FILTER RENTANG TANGGAL & MULTI-TYPE) --}}
    @if ($isSuperAdmin)
        <div class="modal fade" id="modalBulkActionSuperadmin" tabindex="-1" aria-labelledby="modalBulkActionLabel" aria-hidden="true" style="z-index: 9998;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning bg-opacity-25 py-3">
                        <h5 class="modal-title fw-bold text-dark" id="modalBulkActionLabel">
                            <i class="mdi mdi-shield-crown text-warning me-2"></i>Aksi Massal Pengajuan Izin (Khusus Superadmin)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="formBulkAction">
                            {{-- 1. PILIH RENTANG TANGGAL --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-dark mb-0">
                                        <i class="mdi mdi-calendar-range text-primary me-1"></i>1. Rentang Tanggal Izin <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('today')">Hari Ini</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('yesterday')">Kemarin</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('last7')">7 Hari</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('thisMonth')">Bulan Ini</button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="small text-muted mb-1">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="bulk_start_date" name="start_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted mb-1">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="bulk_end_date" name="end_date" required>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="small text-muted d-block mb-1">Metode Pencocokan Tanggal:</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="date_mode" id="date_mode_within" value="within" checked>
                                            <label class="form-check-label small" for="date_mode_within" title="Pengajuan yang tanggal mulai dan selesai berada di antara tanggal yang dipilih">
                                                Di dalam rentang (Rekomendasi)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="date_mode" id="date_mode_overlap" value="overlap">
                                            <label class="form-check-label small" for="date_mode_overlap" title="Pengajuan yang bersinggungan dengan rentang tanggal yang dipilih">
                                                Bersinggungan (Overlap)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="date_mode" id="date_mode_start_only" value="start_only">
                                            <label class="form-check-label small" for="date_mode_start_only">
                                                Tanggal Mulai Saja
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PILIH JENIS IZIN (MULTI-SELECT) --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-dark mb-0">
                                        <i class="mdi mdi-checkbox-multiple-marked text-primary me-1"></i>2. Jenis Izin (Bisa pilih lebih dari 1) <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" id="btnSelectAllTypes">Pilih Semua</button>
                                        <button type="button" class="btn btn-outline-info btn-sm py-1 px-2" id="btnSelectWfhDinas">Hanya WFH & Dinas</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" id="btnResetTypes">Reset</button>
                                    </div>
                                </div>
                                <div class="p-3 bg-light rounded border">
                                    <div class="row g-2">
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="wfh" id="type_wfh" checked>
                                                <label class="form-check-label fw-semibold" for="type_wfh">
                                                    <span class="badge bg-primary text-white me-1">WFH</span> Work From Home
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="dinas" id="type_dinas" checked>
                                                <label class="form-check-label fw-semibold" for="type_dinas">
                                                    <span class="badge bg-primary text-white me-1">Dinas</span> Dinas Luar
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="izin" id="type_izin">
                                                <label class="form-check-label" for="type_izin">
                                                    <span class="badge bg-info text-white me-1">Izin</span> Izin Pribadi
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="sakit" id="type_sakit">
                                                <label class="form-check-label" for="type_sakit">
                                                    <span class="badge bg-danger text-white me-1">Sakit</span> Sakit
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="libur" id="type_libur">
                                                <label class="form-check-label" for="type_libur">
                                                    <span class="badge bg-secondary text-white me-1">Libur</span> Libur / Off Day
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="cuti" id="type_cuti">
                                                <label class="form-check-label" for="type_cuti">
                                                    <span class="badge bg-success text-white me-1">Cuti</span> Cuti
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="telat" id="type_telat">
                                                <label class="form-check-label" for="type_telat">
                                                    <span class="badge bg-warning text-dark me-1">Telat</span> Izin Terlambat
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. FILTER CABANG (OPSIONAL) --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">
                                    <i class="mdi mdi-store text-primary me-1"></i>3. Filter Cabang (Opsional)
                                </label>
                                <select class="form-select" id="bulk_branch_id" name="branch_id">
                                    <option value="all">Semua Cabang (Global)</option>
                                    @isset($branches)
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>

                            {{-- TOMBOL CEK PREVIEW & HASIL PREVIEW --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-dark mb-0">
                                        <i class="mdi mdi-clipboard-text-search text-primary me-1"></i>Pratinjau Data Pending
                                    </label>
                                    <button type="button" class="btn btn-outline-dark btn-sm py-1 px-3" id="btnCheckPreview">
                                        <i class="mdi mdi-refresh me-1"></i> Cek / Hitung Data
                                    </button>
                                </div>

                                {{-- Area Loading & Status --}}
                                <div id="previewStatusBox" class="alert alert-secondary py-3 text-center mb-2">
                                    <span class="text-muted"><i class="mdi mdi-information-outline"></i> Tentukan rentang tanggal dan jenis izin, lalu klik <strong>"Cek / Hitung Data"</strong>.</span>
                                </div>

                                {{-- Area Table Preview --}}
                                <div id="previewDetailsContainer" class="d-none">
                                    <div class="d-flex flex-wrap gap-1 mb-2" id="previewBreakdownBadges"></div>
                                    <div class="border rounded" style="max-height: 220px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>#</th>
                                                    <th>User</th>
                                                    <th>Cabang</th>
                                                    <th>Tipe</th>
                                                    <th>Tanggal</th>
                                                    <th>Alasan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="previewTableBody"></tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted mt-1 d-block text-end" id="previewMoreNotice"></small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer bg-light d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <div class="d-flex gap-2">
                            {{-- TOMBOL REJECT ALL --}}
                            <button type="button" class="btn btn-danger fw-bold px-3" id="btnBulkRejectAction" disabled>
                                <i class="mdi mdi-close-octagon me-1"></i> Tolak Semua (<span class="bulk-match-count">0</span>)
                            </button>
                            {{-- TOMBOL APPROVE ALL --}}
                            <button type="button" class="btn btn-success fw-bold px-3" id="btnBulkApproveAction" disabled>
                                <i class="mdi mdi-check-all me-1"></i> Setujui Semua (<span class="bulk-match-count">0</span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded shadow-sm"
                        style="max-height: 70vh; width: auto;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if(session('swal_error'))
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: '{{ session('swal_error') }}',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Mengerti'
            });
        @endif

        // FUNGSI UNTUK KONFIRMASI SUBMIT DENGAN SWEETALERT
        window.confirmSubmit = function(event, form, message) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        };

        // FUNGSI UNTUK MODAL REJECT (SOLUSI IPHONE)
        window.openRejectModal = function (reqId, actionUrl) {
            var modalElement = document.getElementById('rejectModalDynamic');
            var formElement = document.getElementById('formRejectDynamic');

            formElement.action = actionUrl;

            try {
                $(modalElement).modal('show');
            } catch (e) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        };

        // FUNGSI UNTUK PREVIEW GAMBAR
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

        // Reset data saat modal ditutup agar tidak conflict
        $(document).ready(function () {
            $('#imageModal').on('hidden.bs.modal', function () {
                $('#modalImagePreview').attr('src', '');
            });

            $('#rejectModalDynamic').on('hidden.bs.modal', function () {
                $('#formRejectDynamic').attr('action', '');
                $(this).find('textarea').val('');
            });
        });

        @if ($isSuperAdmin)
        // ==========================================================
        //  LOGIKA KHUSUS SUPERADMIN: AKSI MASSAL & SELEKSI TABEL
        // ==========================================================
        let matchedPendingCount = 0;

        // Quick date setter
        window.setQuickDate = function(period) {
            const today = new Date();
            let start = new Date();
            let end = new Date();

            const formatDate = (d) => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (period === 'today') {
                start = today;
                end = today;
            } else if (period === 'yesterday') {
                start.setDate(today.getDate() - 1);
                end.setDate(today.getDate() - 1);
            } else if (period === 'last7') {
                start.setDate(today.getDate() - 6);
                end = today;
            } else if (period === 'thisMonth') {
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            }

            $('#bulk_start_date').val(formatDate(start));
            $('#bulk_end_date').val(formatDate(end));
            triggerPreviewCheck();
        };

        // Button type selectors
        $('#btnSelectAllTypes').on('click', function () {
            $('.type-checkbox').prop('checked', true);
            triggerPreviewCheck();
        });

        $('#btnSelectWfhDinas').on('click', function () {
            $('.type-checkbox').prop('checked', false);
            $('#type_wfh, #type_dinas').prop('checked', true);
            triggerPreviewCheck();
        });

        $('#btnResetTypes').on('click', function () {
            $('.type-checkbox').prop('checked', false);
            triggerPreviewCheck();
        });

        // Trigger preview saat input berubah
        $('#bulk_start_date, #bulk_end_date, #bulk_branch_id, input[name="date_mode"]').on('change', function () {
            triggerPreviewCheck();
        });
        $('.type-checkbox').on('change', function () {
            triggerPreviewCheck();
        });

        $('#btnCheckPreview').on('click', function () {
            runBulkPreview(true);
        });

        let previewDebounceTimer = null;
        function triggerPreviewCheck() {
            clearTimeout(previewDebounceTimer);
            previewDebounceTimer = setTimeout(function () {
                runBulkPreview(false);
            }, 500);
        }

        function getSelectedTypes() {
            let types = [];
            $('.type-checkbox:checked').each(function () {
                types.push($(this).val());
            });
            return types;
        }

        function runBulkPreview(isManualClick) {
            const startDate = $('#bulk_start_date').val();
            const endDate = $('#bulk_end_date').val();
            const types = getSelectedTypes();
            const branchId = $('#bulk_branch_id').val();
            const dateMode = $('input[name="date_mode"]:checked').val() || 'within';

            if (!startDate || !endDate) {
                if (isManualClick) {
                    Swal.fire('Perhatian', 'Silakan pilih rentang tanggal mulai dan tanggal selesai terlebih dahulu.', 'warning');
                }
                return;
            }

            if (types.length === 0) {
                if (isManualClick) {
                    Swal.fire('Perhatian', 'Silakan centang minimal 1 jenis izin yang ingin diproses.', 'warning');
                }
                return;
            }

            $('#previewStatusBox').html('<div class="spinner-border spinner-border-sm text-primary me-2"></div> Menghitung pengajuan pending yang cocok...');
            $('#btnBulkApproveAction, #btnBulkRejectAction').prop('disabled', true);

            $.ajax({
                url: "{{ route('late.bulk-preview') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: startDate,
                    end_date: endDate,
                    types: types,
                    date_mode: dateMode,
                    branch_id: branchId
                },
                success: function (resp) {
                    matchedPendingCount = resp.total;
                    $('.bulk-match-count').text(resp.total);

                    if (resp.total > 0) {
                        $('#previewStatusBox').html(`
                            <div class="alert alert-success py-2 mb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <i class="mdi mdi-check-circle text-success me-1"></i>
                                    <strong>Ditemukan ${resp.total} Pengajuan Pending</strong> yang sesuai filter.
                                </div>
                                <div class="text-muted small">Siap disetujui / ditolak massal</div>
                            </div>
                        `);

                        // Render badges breakdown
                        let breakdownHtml = '';
                        if (resp.breakdown && resp.breakdown.length > 0) {
                            resp.breakdown.forEach(function (b) {
                                breakdownHtml += `<span class="badge bg-primary text-white py-1 px-2">${b.label}: <strong>${b.count}</strong></span> `;
                            });
                        }
                        $('#previewBreakdownBadges').html(breakdownHtml);

                        // Render table rows
                        let rowsHtml = '';
                        if (resp.sample_items && resp.sample_items.length > 0) {
                            resp.sample_items.forEach(function (item, idx) {
                                rowsHtml += `
                                    <tr>
                                        <td>${idx + 1}</td>
                                        <td class="fw-bold">${item.user_name}</td>
                                        <td><span class="badge bg-light text-dark border">${item.branch_name}</span></td>
                                        <td><span class="badge bg-info text-white">${item.type_label}</span></td>
                                        <td>${item.dates}</td>
                                        <td class="text-truncate" style="max-width: 180px;" title="${item.reason}">${item.reason}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#previewTableBody').html(rowsHtml);

                        if (resp.total > resp.sample_items.length) {
                            $('#previewMoreNotice').text(`Menampilkan ${resp.sample_items.length} dari total ${resp.total} pengajuan pending.`);
                        } else {
                            $('#previewMoreNotice').text(`Menampilkan semua ${resp.total} pengajuan pending.`);
                        }

                        $('#previewDetailsContainer').removeClass('d-none');
                        $('#btnBulkApproveAction, #btnBulkRejectAction').prop('disabled', false);
                    } else {
                        $('#previewStatusBox').html(`
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="mdi mdi-alert-circle-outline me-1"></i>
                                <strong>Tidak ditemukan</strong> pengajuan izin pending dengan kriteria rentang tanggal dan jenis izin tersebut.
                            </div>
                        `);
                        $('#previewDetailsContainer').addClass('d-none');
                        $('#btnBulkApproveAction, #btnBulkRejectAction').prop('disabled', true);
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memeriksa preview data.';
                    $('#previewStatusBox').html(`<div class="alert alert-danger py-2 mb-0">${msg}</div>`);
                    $('#btnBulkApproveAction, #btnBulkRejectAction').prop('disabled', true);
                }
            });
        }

        // Action: Setujui Semua (Batch Approve dari Modal)
        $('#btnBulkApproveAction').on('click', function () {
            if (matchedPendingCount <= 0) return;

            const startDate = $('#bulk_start_date').val();
            const endDate = $('#bulk_end_date').val();
            const types = getSelectedTypes();
            const branchId = $('#bulk_branch_id').val();
            const dateMode = $('input[name="date_mode"]:checked').val() || 'within';

            Swal.fire({
                title: 'Konfirmasi Persetujuan Massal',
                html: `Apakah Anda yakin ingin <strong>MENYETUJUI SEMUA (${matchedPendingCount})</strong> pengajuan izin pending terpilih?<br><br>
                       <div class="text-start bg-light p-3 rounded small border">
                       • Rentang Tanggal: <strong>${startDate} s/d ${endDate}</strong><br>
                       • Jenis Izin: <strong>${types.join(', ').toUpperCase()}</strong><br>
                       • Total Data: <strong>${matchedPendingCount} Pengajuan</strong>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="mdi mdi-check-all"></i> Ya, Setujui Semua',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Persetujuan Massal...',
                        html: `Sedang memproses <strong>${matchedPendingCount}</strong> pengajuan izin dan mencatat kehadiran. Mohon tunggu sebentar.`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('late.bulk-approve') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            start_date: startDate,
                            end_date: endDate,
                            types: types,
                            date_mode: dateMode,
                            branch_id: branchId
                        },
                        success: function (resp) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disetujui!',
                                text: resp.message,
                                confirmButtonText: 'Selesai'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat memproses approval massal.';
                            Swal.fire('Gagal', err, 'error');
                        }
                    });
                }
            });
        });

        // Action: Tolak Semua (Batch Reject dari Modal)
        $('#btnBulkRejectAction').on('click', function () {
            if (matchedPendingCount <= 0) return;

            const startDate = $('#bulk_start_date').val();
            const endDate = $('#bulk_end_date').val();
            const types = getSelectedTypes();
            const branchId = $('#bulk_branch_id').val();
            const dateMode = $('input[name="date_mode"]:checked').val() || 'within';

            Swal.fire({
                title: 'Konfirmasi Penolakan Massal',
                html: `Anda akan <strong>MENOLAK SEMUA (${matchedPendingCount})</strong> pengajuan izin pending terpilih.<br><br>
                       <label class="form-label fw-bold text-dark d-block text-start">Alasan Penolakan <span class="text-danger">*</span>:</label>
                       <textarea id="swalBulkRejectReason" class="form-control text-dark" rows="3" placeholder="Tulis alasan penolakan untuk seluruh pengajuan ini..."></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="mdi mdi-close-octagon"></i> Ya, Tolak Semua',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                preConfirm: () => {
                    const reason = document.getElementById('swalBulkRejectReason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const rejectionReason = result.value;

                    Swal.fire({
                        title: 'Memproses Penolakan Massal...',
                        html: `Sedang memproses penolakan <strong>${matchedPendingCount}</strong> pengajuan izin. Mohon tunggu.`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('late.bulk-reject') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            start_date: startDate,
                            end_date: endDate,
                            types: types,
                            date_mode: dateMode,
                            branch_id: branchId,
                            rejection_reason: rejectionReason
                        },
                        success: function (resp) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Ditolak!',
                                text: resp.message,
                                confirmButtonText: 'Selesai'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat memproses penolakan massal.';
                            Swal.fire('Gagal', err, 'error');
                        }
                    });
                }
            });
        });

        // ==========================================================
        //  PILIHAN CHECKBOX BARIS TABEL (AKTIF PER HALAMAN)
        // ==========================================================
        $('#checkAllRows').on('change', function () {
            $('.row-checkbox').prop('checked', $(this).is(':checked'));
            updateSelectedActionBar();
        });

        $(document).on('change', '.row-checkbox', function () {
            updateSelectedActionBar();
            const totalOnPage = $('.row-checkbox').length;
            const checkedOnPage = $('.row-checkbox:checked').length;
            $('#checkAllRows').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
        });

        function updateSelectedActionBar() {
            const checkedCount = $('.row-checkbox:checked').length;
            $('#selectedCountText').text(checkedCount);
            if (checkedCount > 0) {
                $('#selectedActionBar').removeClass('d-none');
            } else {
                $('#selectedActionBar').addClass('d-none');
            }
        }

        window.clearSelectedRows = function () {
            $('.row-checkbox, #checkAllRows').prop('checked', false);
            updateSelectedActionBar();
        };

        window.getSelectedRowIds = function () {
            let ids = [];
            $('.row-checkbox:checked').each(function () {
                ids.push($(this).val());
            });
            return ids;
        };

        window.approveSelectedRows = function () {
            const ids = window.getSelectedRowIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Konfirmasi Persetujuan',
                text: `Setujui ${ids.length} pengajuan izin yang dipilih di halaman ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('late.bulk-approve') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            selected_ids: ids
                        },
                        success: function (resp) {
                            Swal.fire('Berhasil!', resp.message, 'success').then(() => {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyetujui data terpilih.';
                            Swal.fire('Gagal', err, 'error');
                        }
                    });
                }
            });
        };

        window.rejectSelectedRows = function () {
            const ids = window.getSelectedRowIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Tolak Pengajuan Terpilih',
                html: `Anda akan menolak <strong>${ids.length}</strong> pengajuan izin.<br><br>
                       <label class="form-label fw-bold text-dark d-block text-start">Alasan Penolakan <span class="text-danger">*</span>:</label>
                       <textarea id="swalSelectedRejectReason" class="form-control" rows="3" placeholder="Tulis alasan penolakan..."></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                preConfirm: () => {
                    const reason = document.getElementById('swalSelectedRejectReason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                        return false;
                    }
                    return reason;
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    const rejectionReason = res.value;

                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('late.bulk-reject') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            selected_ids: ids,
                            rejection_reason: rejectionReason
                        },
                        success: function (resp) {
                            Swal.fire('Berhasil!', resp.message, 'success').then(() => {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menolak data terpilih.';
                            Swal.fire('Gagal', err, 'error');
                        }
                    });
                }
            });
        };
        @endif
    </script>
@endsection