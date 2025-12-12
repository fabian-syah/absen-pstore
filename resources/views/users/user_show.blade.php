@extends('layout.master')

@section('title', 'Detail User')

@section('content')

    {{-- TAMPILKAN ERROR JIKA GAGAL VERIFIKASI (KARENA FOTO/KTP KOSONG) --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI (FOTO & MENU) --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    {{-- Foto Profil --}}
                    <div class="mb-3 position-relative d-inline-block">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                            @if ($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-lg rounded-circle"
                                    style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }}">
                            @else
                                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                    style="background-color: #007bff; width: 150px; height: 150px; font-size: 40px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                            @if ($user->is_verified)
                                <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                    style="bottom: 5px; right: 5px; width: 45px; height: 45px; border: 3px solid white;">
                                    <i class="mdi mdi-check-decagram text-primary" style="font-size: 30px;"></i>
                                </div>
                            @endif
                        </a>
                    </div>

                    <h4 class="fw-bold mt-2">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                    {{-- STATUS AKTIF / NON-AKTIF --}}
                    <div class="mb-3">
                        @if ($user->is_active)
                            <span class="badge rounded-pill bg-success px-3 py-2">
                                <i class="mdi mdi-account-check me-1"></i> AKTIF
                            </span>
                        @else
                            <span class="badge rounded-pill bg-danger px-3 py-2">
                                <i class="mdi mdi-account-off me-1"></i> NON-AKTIF
                            </span>
                        @endif
                    </div>

                    {{-- Menu Navigasi --}}
                    <div class="text-start mb-4 mt-4">
                        <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">MENU & RIWAYAT</h6>
                        <div class="list-group list-group-flush">
                            {{-- 1. HISTORY ABSEN --}}
                            <a href="{{ route('attendance.history', ['employeeId' => $user->id]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                <span><i class="mdi mdi-calendar-clock text-primary me-2"></i> History Absen Full</span>
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </a>

                            {{-- 2. HISTORY TAHUNAN --}}
                            <a href="{{ route('attendance.summary.user', ['user_id' => $user->id]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                <span><i class="mdi mdi-chart-bar text-warning me-2"></i> History Tahunan</span>
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </a>

                            {{-- [TAMBAHAN BARU] 3. RIWAYAT PELANGGARAN (Anchor Link ke bawah) --}}
                             <a href="#violationSection"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                <span><i class="mdi mdi-alert-circle text-danger me-2"></i> Riwayat Pelanggaran</span>
                                <span class="badge bg-danger rounded-pill">{{ $violations->count() }}</span>
                            </a>

                            {{-- 4. KELOLA RIWAYAT KARIR (MUTASI/DIVISI) --}}
                            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
                                <a href="{{ route('employment-history.index', ['user_id' => $user->id, 'mode' => 'edit']) }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-briefcase-edit text-info me-2"></i> Riwayat
                                        Divisi/Cabang</span>
                                    <i class="mdi mdi-chevron-right text-muted"></i>
                                </a>
                            @endif
                            {{-- 5. HISTORY INVENTARIS --}}
                            <a href="{{ route('inventory.index', ['user_id' => $user->id]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                <span><i class="mdi mdi-package-variant text-success me-2"></i> History Inventaris</span>
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </a>
                        </div>
                    </div>

                    {{-- AREA VERIFIKASI & KTP --}}
                    <div class="d-grid gap-2">
                        <form action="{{ route('users.verify', $user->id) }}" method="POST">
                            @csrf @method('PATCH')

                            @if ($user->is_verified)
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                    onclick="return confirm('Cabut verifikasi?')">
                                    <i class="mdi mdi-close-circle"></i> Cabut Verifikasi
                                </button>
                            @else
                                {{-- Tombol Verifikasi --}}
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="mdi mdi-check-decagram"></i> Verifikasi Akun
                                </button>
                            @endif
                        </form>

                        {{-- TOMBOL LIHAT KTP (POP UP) --}}
                        @if ($user->ktp_photo_path)
                            <button type="button" class="btn btn-info btn-sm text-white w-100 mt-1" data-bs-toggle="modal"
                                data-bs-target="#ktpPhotoModal">
                                <i class="mdi mdi-card-account-details-outline"></i> Lihat Foto KTP
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm w-100 mt-1" disabled>
                                <i class="mdi mdi-close-circle-outline"></i> KTP Belum Diupload
                            </button>
                        @endif
                    </div>

                    {{-- Indikator Status Kelengkapan --}}
                    @if (!$user->is_verified)
                        <div class="mt-2 text-start small">
                            <span class="d-block {{ $user->profile_photo_path ? 'text-success' : 'text-danger' }}">
                                <i class="mdi {{ $user->profile_photo_path ? 'mdi-check' : 'mdi-close' }}"></i> Foto Profil
                            </span>
                            <span class="d-block {{ $user->ktp_photo_path ? 'text-success' : 'text-danger' }}">
                                <i class="mdi {{ $user->ktp_photo_path ? 'mdi-check' : 'mdi-close' }}"></i> Foto KTP
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (DETAIL & 5 AKTIVITAS & PELANGGARAN) --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Detail Informasi</h4>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Email</label>
                            <p class="h6">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Cabang</label>
                            <p class="h6">{{ $user->branch->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Divisi</label>
                            <p class="h6">{{ $user->division->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">WhatsApp</label>
                            <p class="h6">{{ $user->whatsapp ?? '-' }}</p>
                        </div>

                        {{-- INDIKATOR TIPE ABSENSI --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Tipe Absensi</label>
                            @if ($user->only_security_scan)
                                <p class="h6 text-danger fw-bold">
                                    <i class="mdi mdi-qrcode-scan"></i> Wajib Scan Security (Locked)
                                </p>
                            @else
                                <p class="h6 text-success">
                                    <i class="mdi mdi-cellphone"></i> Bisa Mandiri & Scan
                                </p>
                            @endif
                        </div>

                        {{-- TANGGAL LAHIR --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Tanggal Lahir</label>
                            <p class="h6">
                                <i class="mdi mdi-cake-variant text-warning me-1"></i>
                                {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y') : '-' }}
                            </p>
                        </div>

                        {{-- SOSMED --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Media Sosial</label>
                            <div class="d-flex align-items-center gap-3 mt-1">
                                @if ($user->facebook)
                                    <a href="{{ $user->facebook }}" target="_blank" class="text-decoration-none"
                                        title="Facebook">
                                        <i class="mdi mdi-facebook text-primary" style="font-size: 28px;"></i>
                                    </a>
                                @endif
                                @if ($user->instagram)
                                    <a href="{{ $user->instagram }}" target="_blank" class="text-decoration-none"
                                        title="Instagram">
                                        <i class="mdi mdi-instagram text-danger" style="font-size: 28px;"></i>
                                    </a>
                                @endif
                                @if ($user->tiktok)
                                    <a href="{{ $user->tiktok }}" target="_blank" class="text-decoration-none"
                                        title="TikTok">
                                        <i class="mdi mdi-music-note text-dark" style="font-size: 28px;"></i>
                                    </a>
                                @endif
                                @if (!$user->facebook && !$user->instagram && !$user->tiktok)
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                        </div>

                        {{-- JAM KERJA PERSONAL --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Jam Kerja Personal</label>
                            @if ($user->check_in_start || $user->check_out_start)
                                <div class="d-flex align-items-center mt-1">
                                    <span class="badge bg-primary text-white border border-primary me-2">
                                        <i class="mdi mdi-login-variant me-1"></i>
                                        {{ $user->check_in_start ? \Carbon\Carbon::parse($user->check_in_start)->format('H:i') : '?' }}
                                    </span>
                                    <span class="text-muted mx-1 fw-bold">-</span>
                                    <span class="badge bg-danger text-white border border-danger ms-2">
                                        <i class="mdi mdi-logout-variant me-1"></i>
                                        {{ $user->check_out_start ? \Carbon\Carbon::parse($user->check_out_start)->format('H:i') : '?' }}
                                    </span>
                                </div>
                            @else
                                <p class="h6 text-muted mt-1">
                                    <i class="mdi mdi-calendar-clock"></i> Mengikuti Shift / Fleksibel
                                </p>
                            @endif
                        </div>

                    </div>

                    {{-- TABEL 5 AKTIVITAS TERAKHIR --}}
                    <div class="mt-4">
                        <h5 class="card-title mb-3 border-bottom pb-2">5 Kehadiran Terakhir</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Masuk</th>
                                        <th>Pulang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendance as $log)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">
                                                    {{ \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') }}</div>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($log->check_in_time)->format('l') }}</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    {{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                                </span>
                                                @if ($log->is_late_checkin)
                                                    <i class="mdi mdi-alert-circle text-warning ms-1"
                                                        title="Terlambat"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($log->check_out_time)
                                                    <span class="fw-bold text-dark">
                                                        {{ \Carbon\Carbon::parse($log->check_out_time)->format('H:i') }}
                                                    </span>
                                                    @if ($log->is_early_checkout)
                                                        <span class="badge bg-warning text-dark ms-1"
                                                            style="font-size: 10px;">Cepat</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Belum</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($log->presence_status == 'Izin' || $log->presence_status == 'Sakit' || $log->presence_status == 'Cuti')
                                                    <span class="badge bg-info">{{ $log->presence_status }}</span>
                                                @elseif($log->is_late_checkin)
                                                    <span class="badge bg-warning text-dark">Telat Hadir</span>
                                                @else
                                                    <span class="badge bg-success">Hadir / Tepat Waktu</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="mdi mdi-calendar-blank display-4 mb-2 d-block"></i>
                                                Belum ada data kehadiran valid.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- [TAMBAHAN BARU] TABEL RIWAYAT PELANGGARAN --}}
                    <div class="mt-5" id="violationSection">
                         <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="card-title mb-0 text-danger">
                                <i class="mdi mdi-alert-circle me-2"></i>Riwayat Pelanggaran
                            </h5>
                            @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                <a href="{{ route('violations.create') }}" class="btn btn-sm btn-outline-danger">
                                    <i class="mdi mdi-plus"></i> Tambah
                                </a>
                            @endif
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Judul & Tanggal</th>
                                        <th>Masa Berlaku</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($violations as $violation)
                                        <tr>
                                            <td>
                                                @if($violation->category == 'berat')
                                                    <span class="badge bg-danger">BERAT</span>
                                                @elseif($violation->category == 'sedang')
                                                    <span class="badge bg-warning text-dark">SEDANG</span>
                                                @else
                                                    <span class="badge bg-info">RINGAN</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $violation->title }}</div>
                                                <small class="text-muted">
                                                    Dibuat: {{ $violation->created_at->format('d M Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($violation->expires_at)
                                                    <span class="d-block small text-muted">Hingga:</span>
                                                    <span class="fw-bold {{ $violation->expires_at->isPast() ? 'text-success' : 'text-danger' }}">
                                                        {{ $violation->expires_at->format('d M Y') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($violation->expires_at && $violation->expires_at->isPast())
                                                    <span class="badge bg-soft-success text-success border border-success">
                                                        <i class="mdi mdi-check"></i> Selesai
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger border border-danger">
                                                        <i class="mdi mdi-alert"></i> Aktif
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Tombol Lihat Detail (Modal Gambar) --}}
                                                @if($violation->photo_path)
                                                    <button type="button" class="btn btn-sm btn-info text-white" 
                                                        onclick="showImageModal('{{ asset('storage/' . $violation->photo_path) }}', '{{ $violation->title }}')">
                                                        <i class="mdi mdi-image"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="mdi mdi-shield-check display-4 mb-2 d-block text-success"></i>
                                                <span class="text-success fw-bold">Bersih!</span> Tidak ada riwayat pelanggaran.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-light">Kembali</a>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning text-white">Edit Data</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL FOTO PROFIL --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 text-center">
                @if ($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded shadow-lg"
                        style="max-height: 80vh;">
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL FOTO KTP --}}
    <div class="modal fade" id="ktpPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-white shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Foto KTP - {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    @if ($user->ktp_photo_path)
                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid"
                            style="width: 100%; object-fit: contain;">
                    @else
                        <div class="p-5 text-muted">
                            <i class="mdi mdi-image-off display-1"></i>
                            <p class="mt-2">Tidak ada foto KTP.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL BUKTI PELANGGARAN --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="violationModalTitle">Bukti Pelanggaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="$('#imagePreviewModal').modal('hide')"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImageSrc" src="" class="img-fluid rounded" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function showImageModal(src, title) {
        document.getElementById('previewImageSrc').src = src;
        document.getElementById('violationModalTitle').innerText = 'Bukti: ' + title;
        var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        myModal.show();
    }
</script>
@endpush