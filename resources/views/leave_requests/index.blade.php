@extends('layout.master')

@section('title')
    Verifikasi Izin & Keterlambatan
@endsection

@section('content')
    @php
        $isSuperAdmin = strtolower(trim(auth()->user()->login_id ?? '')) === 'superadmin';
    @endphp

    <style>
        /* Base typography enforcement for modal */
        #modalBulkActionSuperadmin,
        #modalBulkActionSuperadmin * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            letter-spacing: normal !important;
            word-spacing: normal !important;
        }

        .type-select-card {
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            transition: all 0.15s ease-in-out;
            user-select: none;
            color: #0f172a;
            padding: 9px 12px;
        }
        .type-select-card:hover {
            border-color: #0d6efd;
            background: #f8fafc;
        }
        .type-select-card.active {
            border-color: #0d6efd !important;
            background: #eff6ff !important;
        }
        .type-select-card .fw-semibold {
            color: #0f172a !important;
        }

        .date-mode-card {
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            transition: all 0.15s ease-in-out;
            user-select: none;
            padding: 10px 14px;
        }
        .date-mode-card:hover {
            border-color: #0d6efd;
            background: #f8fafc;
        }
        .date-mode-card.active {
            border-color: #0d6efd !important;
            background: #eff6ff !important;
        }
        .date-mode-card .mode-title {
            color: #0f172a !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
            margin-bottom: 2px !important;
        }
        .date-mode-card .mode-desc {
            color: #475569 !important;
            font-weight: 400 !important;
            font-size: 11px !important;
            line-height: 1.3 !important;
        }

        .quick-date-btn {
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            background: #ffffff;
            transition: all 0.15s;
        }
        .quick-date-btn:hover {
            background: #0d6efd;
            color: #ffffff;
            border-color: #0d6efd;
        }

        /* HIGH-CONTRAST FORM CONTROLS (EXCLUDING CHECKBOX & RADIO) */
        #modalBulkActionSuperadmin select.form-select,
        #modalBulkActionSuperadmin .form-select,
        #modalBulkActionSuperadmin .form-control,
        #modalBulkActionSuperadmin select,
        #modalBulkActionSuperadmin textarea,
        #modalBulkActionSuperadmin input[type="date"],
        #modalBulkActionSuperadmin input[type="text"],
        #modalBulkActionSuperadmin input[type="number"] {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a !important;
            font-weight: 600 !important;
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            opacity: 1 !important;
        }
        #modalBulkActionSuperadmin select option {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a !important;
            font-weight: 600 !important;
            background-color: #ffffff !important;
        }
        #modalBulkActionSuperadmin .input-group-text {
            color: #1e293b !important;
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            font-weight: 600;
        }

        /* CHECKBOX & RADIO STYLING DENGAN CENTANG JELAS */
        #modalBulkActionSuperadmin input[type="checkbox"].form-check-input,
        #modalBulkActionSuperadmin input[type="radio"].form-check-input,
        .form-check-input.row-checkbox,
        #checkAllRows {
            width: 19px !important;
            height: 19px !important;
            min-width: 19px !important;
            min-height: 19px !important;
            border: 1.8px solid #64748b !important;
            background-color: #ffffff !important;
            cursor: pointer !important;
            margin: 0 !important;
            padding: 0 !important;
            display: inline-block !important;
            vertical-align: middle !important;
            -webkit-appearance: none !important;
            appearance: none !important;
            transition: all 0.15s ease-in-out !important;
            box-shadow: none !important;
        }

        #modalBulkActionSuperadmin input[type="checkbox"].form-check-input,
        .form-check-input.row-checkbox,
        #checkAllRows {
            border-radius: 4.5px !important;
        }

        #modalBulkActionSuperadmin input[type="radio"].form-check-input {
            border-radius: 50% !important;
        }

        #modalBulkActionSuperadmin input[type="checkbox"].form-check-input:hover,
        #modalBulkActionSuperadmin input[type="radio"].form-check-input:hover,
        .form-check-input.row-checkbox:hover,
        #checkAllRows:hover {
            border-color: #0d6efd !important;
        }

        #modalBulkActionSuperadmin input[type="checkbox"].form-check-input:checked,
        .form-check-input.row-checkbox:checked,
        #checkAllRows:checked {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m4 10 4.5 4.5 8-9'/%3e%3c/svg%3e") !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-size: 13px !important;
        }

        #modalBulkActionSuperadmin input[type="radio"].form-check-input:checked {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='2.5' fill='%23ffffff'/%3e%3c/svg%3e") !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-size: 8px !important;
        }

        /* TABLE PREVIEW DI MODAL */
        #modalBulkActionSuperadmin .table thead th {
            color: #0f172a !important;
            font-weight: 700 !important;
            font-size: 11.5px !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background-color: #e2e8f0 !important;
            border-bottom: 2px solid #94a3b8 !important;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            white-space: nowrap;
            font-style: normal !important;
        }
        #modalBulkActionSuperadmin .table tbody td {
            color: #0f172a !important;
            vertical-align: middle;
            font-style: normal !important;
        }
        #modalBulkActionSuperadmin .text-muted {
            color: #475569 !important;
        }

        /* SWEETALERT2 SELALU DI PALING DEPAN DI ATAS SEMUA MODAL */
        div.swal2-container,
        .swal2-container {
            z-index: 999999 !important;
        }
        .swal2-popup {
            z-index: 1000000 !important;
        }
    </style>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <div>
                            <h4 class="card-title mb-1 text-dark fw-bold">Verifikasi Izin & Keterlambatan</h4>
                            <p class="text-muted small mb-0">Daftar permohonan izin karyawan yang menunggu persetujuan</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            {{-- FITUR KHUSUS SUPERADMIN: AKSI MASSAL --}}
                            @if ($isSuperAdmin)
                                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalBulkActionSuperadmin">
                                    <i class="mdi mdi-layers-triple-outline"></i> Aksi Massal
                                </button>
                            @endif

                            {{-- Tombol History --}}
                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                <a href="{{ route('audit.late.history') }}" class="btn btn-inverse-info btn-sm rounded-3">
                                    <i class="mdi mdi-history"></i> Riwayat Selesai
                                </a>
                                <a href="{{ route('audit.late.rejected.history') }}" class="btn btn-inverse-danger btn-sm rounded-3">
                                    <i class="mdi mdi-close-circle-multiple-outline"></i> Riwayat Ditolak
                                </a>
                            @endif

                            {{-- Tombol Ajukan Baru (untuk user biasa) --}}
                            @if (in_array(auth()->user()->role, ['user_biasa', 'leader']))
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm rounded-3">
                                    <i class="mdi mdi-plus"></i> Ajukan Baru
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Banner Khusus Superadmin --}}
                    @if ($isSuperAdmin)
                        <div class="card border-0 mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-left: 4px solid #0d6efd !important; border-radius: 12px;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                        <i class="mdi mdi-shield-account-outline" style="font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">Aksi Massal Superadmin Aktif</div>
                                        <div class="text-muted small">Persetujuan atau penolakan izin sekaligus berdasarkan rentang tanggal &amp; tipe izin (misal: 22-27 September WFH &amp; Dinas Luar).</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm px-3 py-2 rounded-3 fw-semibold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalBulkActionSuperadmin">
                                    <i class="mdi mdi-layers-triple-outline"></i>
                                    <span>Buka Aksi Massal</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Informasi Halaman --}}
                    <div class="alert alert-info mb-3 rounded-3 border-0" style="background: rgba(13, 110, 253, 0.08); color: #084298;">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-outline me-2" style="font-size: 18px;"></i>
                            <div style="font-size: 13px;">
                                Total data pending: <strong>{{ $requests->total() }}</strong> |
                                Klik tombol mata (<i class="mdi mdi-eye"></i>) untuk melihat bukti |
                                Diurutkan dari yang <strong>paling lama</strong> diajukan.
                            </div>
                        </div>
                    </div>

                    {{-- Notifikasi Sukses/Error --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show rounded-3">
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    @if ($isSuperAdmin)
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="checkAllRows" class="form-check-input" title="Pilih Semua di Halaman Ini" style="cursor: pointer; width: 17px; height: 17px;">
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
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $req->id }}" style="cursor: pointer; width: 17px; height: 17px;">
                                            </td>
                                        @endif

                                        {{-- 1. USER INFO --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold; font-size: 13px;">
                                                    {{ substr($req->user->name ?? 'U', 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark" style="font-size: 13px;">{{ $req->user->name ?? 'User #' . $req->user_id }}</span>
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
                                        <td class="text-wrap" style="max-width: 200px; font-size: 13px;">{{ $req->reason }}</td>

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
                                        <td>
                                            @if ($req->file_proof)
                                                <a href="javascript:void(0)"
                                                    onclick="window.showImageModal('{{ asset('storage/' . $req->file_proof) }}')"
                                                    class="btn btn-inverse-secondary btn-icon btn-sm rounded-circle" title="Lihat Bukti">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- 7. STATUS --}}
                                        <td>
                                            <span class="badge badge-opacity-warning rounded-pill">Menunggu</span>
                                        </td>

                                        {{-- 8. AKSI --}}
                                        <td>
                                            {{-- USER: BATALKAN --}}
                                            @if (auth()->id() == $req->user_id)
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="confirmSubmit(event, this, 'Yakin ingin membatalkan pengajuan?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger rounded-3" title="Batalkan">
                                                        <i class="mdi mdi-close-circle"></i> Batal
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- ADMIN/AUDIT: APPROVE & REJECT --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                <form action="{{ route('late.approve', $req->id) }}" method="POST" class="d-inline"
                                                    onsubmit="confirmSubmit(event, this, 'Setujui pengajuan ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm p-2 rounded-3" title="Setujui">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                </form>

                                                {{-- Tombol Reject dengan data dinamis untuk iPhone --}}
                                                <button type="button" class="btn btn-danger btn-sm p-2 rounded-3"
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
        <div id="selectedActionBar" class="card shadow-lg border-0 position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" style="z-index: 1050; min-width: 320px; max-width: 90%; border-radius: 14px; background: #1e293b; color: #fff;">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center text-white">
                    <i class="mdi mdi-checkbox-marked-circle-outline text-success me-2" style="font-size: 20px;"></i>
                    <span style="font-size: 13px;"><strong id="selectedCountText" class="text-warning">0</strong> izin terpilih</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm fw-semibold px-3 rounded-pill" onclick="window.approveSelectedRows()">
                        <i class="mdi mdi-check me-1"></i> Setujui
                    </button>
                    <button type="button" class="btn btn-danger btn-sm fw-semibold px-3 rounded-pill" onclick="window.rejectSelectedRows()">
                        <i class="mdi mdi-close me-1"></i> Tolak
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm px-3 rounded-pill" onclick="window.clearSelectedRows()">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL AKSI MASSAL SUPERADMIN (FILTER RENTANG TANGGAL & MULTI-TYPE) --}}
    @if ($isSuperAdmin)
        <div class="modal fade" id="modalBulkActionSuperadmin" tabindex="-1" aria-labelledby="modalBulkActionLabel" aria-hidden="true" style="z-index: 2000;">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    {{-- MODAL HEADER --}}
                    <div class="modal-header bg-white border-bottom px-4 py-3 align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: rgba(13, 110, 253, 0.08); color: #0d6efd;">
                                <i class="mdi mdi-layers-triple-outline" style="font-size: 22px;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalBulkActionLabel" style="font-size: 16px;">
                                    Aksi Massal Pengajuan Izin
                                </h5>
                                <p class="text-muted small mb-0">Setujui (ACC) atau tolak pengajuan pending secara serentak</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- MODAL BODY --}}
                    <div class="modal-body p-4" style="background: #ffffff;">
                        <form id="formBulkAction">
                            {{-- 1. PILIH RENTANG TANGGAL --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <label class="form-label fw-bold text-dark mb-0 d-flex align-items-center">
                                        <span class="badge rounded-circle bg-primary text-white me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">1</span>
                                        <span>Rentang Tanggal</span>
                                        <span class="text-danger ms-1">*</span>
                                    </label>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" onclick="setQuickDate('today')">Hari Ini</button>
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" onclick="setQuickDate('yesterday')">Kemarin</button>
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" onclick="setQuickDate('last7')">7 Hari</button>
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" onclick="setQuickDate('thisMonth')">Bulan Ini</button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group mb-0">
                                            <label class="small text-dark mb-1 fw-bold">Tanggal Mulai</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0 text-dark"><i class="mdi mdi-calendar"></i></span>
                                                <input type="date" class="form-control border-start-0" id="bulk_start_date" name="start_date" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group mb-0">
                                            <label class="small text-dark mb-1 fw-bold">Tanggal Selesai</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0 text-dark"><i class="mdi mdi-calendar"></i></span>
                                                <input type="date" class="form-control border-start-0" id="bulk_end_date" name="end_date" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Metode Pencocokan --}}
                                <div class="mt-3">
                                    <label class="small text-dark d-block mb-2 fw-bold">Metode Pencocokan Tanggal:</label>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-4">
                                            <label class="date-mode-card d-flex align-items-center p-3 rounded-3 border w-100 mb-0 active" for="date_mode_within">
                                                <input class="form-check-input me-3 mt-0" type="radio" name="date_mode" id="date_mode_within" value="within" checked>
                                                <div>
                                                    <span class="d-block mode-title">Di Dalam Rentang</span>
                                                    <small class="mode-desc d-block">Mulai &amp; selesai di rentang ini</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="date-mode-card d-flex align-items-center p-3 rounded-3 border w-100 mb-0" for="date_mode_overlap">
                                                <input class="form-check-input me-3 mt-0" type="radio" name="date_mode" id="date_mode_overlap" value="overlap">
                                                <div>
                                                    <span class="d-block mode-title">Bersinggungan</span>
                                                    <small class="mode-desc d-block">Overlap tanggal yang dipilih</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="date-mode-card d-flex align-items-center p-3 rounded-3 border w-100 mb-0" for="date_mode_start_only">
                                                <input class="form-check-input me-3 mt-0" type="radio" name="date_mode" id="date_mode_start_only" value="start_only">
                                                <div>
                                                    <span class="d-block mode-title">Tanggal Mulai Saja</span>
                                                    <small class="mode-desc d-block">Berdasarkan tanggal awal</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PILIH JENIS IZIN (MULTI-SELECT) --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <label class="form-label fw-bold text-dark mb-0 d-flex align-items-center">
                                        <span class="badge rounded-circle bg-primary text-white me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">2</span>
                                        <span>Jenis Izin</span>
                                        <span class="text-danger ms-1">*</span>
                                    </label>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" id="btnSelectAllTypes">Pilih Semua</button>
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" id="btnSelectWfhDinas">Hanya WFH &amp; Dinas</button>
                                        <button type="button" class="quick-date-btn btn btn-sm py-1 px-3" id="btnResetTypes">Reset</button>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0 active" for="type_wfh">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="wfh" id="type_wfh" checked>
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">WFH</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(13, 110, 253, 0.12); color: #0d6efd; font-size: 10px;">Remote</span>
                                        </label>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0 active" for="type_dinas">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="dinas" id="type_dinas" checked>
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">Dinas Luar</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(13, 202, 240, 0.15); color: #0aa2c0; font-size: 10px;">Dinas</span>
                                        </label>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0" for="type_izin">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="izin" id="type_izin">
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">Izin Pribadi</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(108, 117, 125, 0.12); color: #495057; font-size: 10px;">Izin</span>
                                        </label>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0" for="type_sakit">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="sakit" id="type_sakit">
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">Sakit</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(220, 53, 69, 0.12); color: #dc3545; font-size: 10px;">Sakit</span>
                                        </label>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0" for="type_libur">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="libur" id="type_libur">
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">Libur / Off</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(108, 117, 125, 0.12); color: #6c757d; font-size: 10px;">Off</span>
                                        </label>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0" for="type_cuti">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="cuti" id="type_cuti">
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">Cuti</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(25, 135, 84, 0.12); color: #198754; font-size: 10px;">Cuti</span>
                                        </label>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3">
                                        <label class="type-select-card d-flex align-items-center justify-content-between p-2 rounded-3 border w-100 mb-0" for="type_telat">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input type-checkbox me-2 mt-0" type="checkbox" name="types[]" value="telat" id="type_telat">
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">Telat</span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(255, 193, 7, 0.2); color: #997404; font-size: 10px;">Telat</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. FILTER CABANG (OPSIONAL) --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-1 d-flex align-items-center">
                                    <span class="badge rounded-circle bg-secondary text-white me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">3</span>
                                    <span>Filter Cabang (Opsional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-dark"><i class="mdi mdi-store-outline"></i></span>
                                    <select class="form-select border-start-0" id="bulk_branch_id" name="branch_id" style="color: #0f172a !important; -webkit-text-fill-color: #0f172a !important; font-weight: 600;">
                                        <option value="all" style="color: #0f172a; font-weight: 500;">Semua Cabang (Global)</option>
                                        @isset($branches)
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}" style="color: #0f172a; font-weight: 500;">{{ $b->name }}</option>
                                            @endforeach
                                        @endisset
                                    </select>
                                </div>
                            </div>

                            {{-- 4. PREVIEW & STATUS DATA --}}
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <label class="form-label fw-bold text-dark mb-0 d-flex align-items-center">
                                        <span class="badge rounded-circle bg-info text-white me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 11px;">4</span>
                                        <span>Hasil Pencocokan Data</span>
                                    </label>
                                    <button type="button" class="btn btn-primary btn-sm py-1 px-3 rounded-pill fw-semibold shadow-sm" id="btnCheckPreview">
                                        <i class="mdi mdi-refresh me-1"></i> Cek Data
                                    </button>
                                </div>

                                {{-- Preview Box --}}
                                <div id="previewStatusBox" class="p-3 rounded-3 text-center border" style="background: #f8fafc;">
                                    <span class="text-dark small fw-medium"><i class="mdi mdi-information-outline text-primary me-1"></i> Masukkan rentang tanggal dan pilih jenis izin untuk melihat data.</span>
                                </div>

                                <div id="previewDetailsContainer" class="d-none mt-3">
                                    <div class="d-flex flex-wrap gap-1 mb-2" id="previewBreakdownBadges"></div>
                                    <div class="border rounded-3 overflow-hidden shadow-sm" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                            <thead class="sticky-top">
                                                <tr>
                                                    <th class="ps-3 text-dark fw-bold" style="width: 45px;">#</th>
                                                    <th class="text-dark fw-bold" style="width: 170px;">Nama Karyawan</th>
                                                    <th class="text-dark fw-bold" style="width: 120px;">Cabang</th>
                                                    <th class="text-dark fw-bold" style="width: 100px;">Tipe</th>
                                                    <th class="text-dark fw-bold" style="width: 130px;">Tanggal</th>
                                                    <th class="text-dark fw-bold">Alasan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="previewTableBody"></tbody>
                                        </table>
                                    </div>
                                    <small class="fw-semibold mt-1 d-block text-end" style="color: #334155; font-size: 12px;" id="previewMoreNotice"></small>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- MODAL FOOTER --}}
                    <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between flex-wrap gap-2 border-top">
                        <button type="button" class="btn btn-light border text-dark fw-semibold px-4 py-2 rounded-3" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger px-4 py-2 rounded-3 shadow-sm fw-semibold" id="btnBulkRejectAction" disabled>
                                <i class="mdi mdi-close-circle-outline me-1"></i> Tolak Semua (<span class="bulk-match-count">0</span>)
                            </button>
                            <button type="button" class="btn btn-success px-4 py-2 rounded-3 shadow-sm fw-semibold" id="btnBulkApproveAction" disabled>
                                <i class="mdi mdi-check-circle-outline me-1"></i> Setujui Semua (<span class="bulk-match-count">0</span>)
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
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 16px;">Tolak Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRejectDynamic" action="" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control text-dark rounded-3" rows="3" required
                                placeholder="Tulis alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-light border text-muted rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-3 fw-semibold">Tolak Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW GAMBAR --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 16px;">Bukti Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4" style="background: #f8fafc;">
                    <img id="modalImagePreview" src="" alt="Bukti" class="img-fluid rounded-3 shadow-sm"
                        style="max-height: 70vh; width: auto;">
                </div>
                <div class="modal-footer bg-light px-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
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

        // CEGAH BOOTSTRAP MODAL FOCUS TRAP MENGGANGGU SWEETALERT2
        $(document).on('focusin', function(e) {
            if ($(e.target).closest('.swal2-container').length) {
                e.stopImmediatePropagation();
            }
        });

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

        // Sync card active states
        $(document).on('change', '.type-checkbox', function () {
            if ($(this).is(':checked')) {
                $(this).closest('.type-select-card').addClass('active');
            } else {
                $(this).closest('.type-select-card').removeClass('active');
            }
            triggerPreviewCheck();
        });

        $(document).on('change', 'input[name="date_mode"]', function () {
            $('.date-mode-card').removeClass('active');
            $(this).closest('.date-mode-card').addClass('active');
            triggerPreviewCheck();
        });

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
            $('.type-checkbox').prop('checked', true).trigger('change');
            $('.type-select-card').addClass('active');
            triggerPreviewCheck();
        });

        $('#btnSelectWfhDinas').on('click', function () {
            $('.type-checkbox').prop('checked', false);
            $('.type-select-card').removeClass('active');
            $('#type_wfh, #type_dinas').prop('checked', true).closest('.type-select-card').addClass('active');
            $('.type-checkbox').trigger('change');
            triggerPreviewCheck();
        });

        $('#btnResetTypes').on('click', function () {
            $('.type-checkbox').prop('checked', false).trigger('change');
            $('.type-select-card').removeClass('active');
            triggerPreviewCheck();
        });

        // Trigger preview saat input berubah
        $('#bulk_start_date, #bulk_end_date, #bulk_branch_id').on('change', function () {
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
            }, 400);
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
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pemberitahuan',
                        text: 'Silakan pilih rentang tanggal mulai dan tanggal selesai terlebih dahulu.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
                return;
            }

            if (types.length === 0) {
                if (isManualClick) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pemberitahuan',
                        text: 'Silakan centang minimal 1 jenis izin yang ingin diproses.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
                return;
            }

            $('#previewStatusBox').html(`
                <div class="d-flex align-items-center justify-content-center py-2 text-primary">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    <span class="small fw-semibold">Menghitung pengajuan pending yang cocok...</span>
                </div>
            `);
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
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-start p-2">
                                <div>
                                    <div class="fw-bold text-success" style="font-size: 15px;">
                                        <i class="mdi mdi-check-circle me-1"></i> Ditemukan ${resp.total} Pengajuan Pending
                                    </div>
                                    <div class="text-muted small mt-0.5">Siap diproses untuk rentang tanggal yang dipilih</div>
                                </div>
                                <span class="badge rounded-pill px-3 py-2 fw-bold" style="background: #198754; color: #ffffff !important; font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                    ${resp.total} Data Cocok
                                </span>
                            </div>
                        `);

                        // Render badges breakdown
                        let breakdownHtml = '';
                        if (resp.breakdown && resp.breakdown.length > 0) {
                            resp.breakdown.forEach(function (b) {
                                breakdownHtml += `<span class="badge rounded-pill border py-1.5 px-3 me-2 mb-2 shadow-xs" style="background: #ffffff; color: #0f172a; border-color: #cbd5e1 !important; font-size: 12px; font-weight: 600;">${b.label}: <strong class="text-primary">${b.count}</strong></span>`;
                            });
                        }
                        $('#previewBreakdownBadges').html(breakdownHtml);

                        // Render table rows
                        let rowsHtml = '';
                        if (resp.sample_items && resp.sample_items.length > 0) {
                            resp.sample_items.forEach(function (item, idx) {
                                rowsHtml += `
                                    <tr>
                                        <td class="ps-3 text-dark fw-bold align-middle">${idx + 1}</td>
                                        <td class="fw-bold text-dark align-middle">${item.user_name}</td>
                                        <td class="align-middle"><span class="badge" style="background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; font-weight: 600; font-size: 11px;">${item.branch_name}</span></td>
                                        <td class="align-middle"><span class="badge bg-primary text-white rounded-pill px-2 py-1 fw-semibold">${item.type_label}</span></td>
                                        <td class="text-dark fw-semibold align-middle" style="font-size: 12px; white-space: nowrap;">${item.dates}</td>
                                        <td class="text-dark fw-normal align-middle" style="max-width: 250px; word-break: break-word; font-style: normal;" title="${item.reason}">${item.reason}</td>
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
                            <div class="py-2 text-dark small fw-medium">
                                <i class="mdi mdi-alert-circle-outline text-warning me-1"></i>
                                <span>Tidak ditemukan pengajuan pending dengan kriteria rentang tanggal dan jenis izin tersebut.</span>
                            </div>
                        `);
                        $('#previewDetailsContainer').addClass('d-none');
                        $('#btnBulkApproveAction, #btnBulkRejectAction').prop('disabled', true);
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memeriksa preview data.';
                    $('#previewStatusBox').html(`<div class="text-danger small py-2">${msg}</div>`);
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
                       <div class="text-start p-3 rounded-3 small border" style="background: #f8fafc; color: #0f172a;">
                       • Rentang Tanggal: <strong style="color: #0f172a;">${startDate} s/d ${endDate}</strong><br>
                       • Jenis Izin: <strong style="color: #0f172a;">${types.join(', ').toUpperCase()}</strong><br>
                       • Total Pengajuan: <strong class="text-primary">${matchedPendingCount} Data</strong>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui Semua',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Persetujuan...',
                        html: `Sedang memproses <strong>${matchedPendingCount}</strong> pengajuan izin dan mencatat kehadiran.<br>Mohon tunggu sebentar.`,
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
                                title: 'Berhasil Disetujui',
                                text: resp.message,
                                confirmButtonColor: '#198754',
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
                       <label class="form-label fw-bold text-dark d-block text-start small">Alasan Penolakan <span class="text-danger">*</span>:</label>
                       <textarea id="swalBulkRejectReason" class="form-control text-dark rounded-3" rows="3" placeholder="Tulis alasan penolakan..." style="color: #0f172a !important; -webkit-text-fill-color: #0f172a !important; font-weight: 500; background: #ffffff !important; border: 1.5px solid #cbd5e1 !important;"></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Tolak Semua',
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
                        title: 'Memproses Penolakan...',
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
                                title: 'Berhasil Ditolak',
                                text: resp.message,
                                confirmButtonColor: '#dc3545',
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
                confirmButtonColor: '#198754',
                cancelButtonColor: '#64748b',
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disetujui',
                                text: resp.message,
                                confirmButtonColor: '#198754'
                            }).then(() => {
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
                       <label class="form-label fw-bold text-dark d-block text-start small">Alasan Penolakan <span class="text-danger">*</span>:</label>
                       <textarea id="swalSelectedRejectReason" class="form-control text-dark rounded-3" rows="3" placeholder="Tulis alasan penolakan..." style="color: #0f172a !important; -webkit-text-fill-color: #0f172a !important; font-weight: 500; background: #ffffff !important; border: 1.5px solid #cbd5e1 !important;"></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#64748b',
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Ditolak',
                                text: resp.message,
                                confirmButtonColor: '#dc3545'
                            }).then(() => {
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