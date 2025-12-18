@extends('layout.master')

@section('title', 'Detail User')

@section('content')

    {{-- FLASH MESSAGES --}}
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
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    {{-- Foto Profil --}}
                    <div class="mb-3 position-relative d-inline-block">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                            @if ($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-lg rounded-circle shadow-sm"
                                    style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '5px solid #0d6efd' : '3px solid #e3e3e3' }}">
                            @else
                                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                    style="background-color: #007bff; width: 150px; height: 150px; font-size: 40px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                            @if ($user->is_verified)
                                <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="bottom: 5px; right: 5px; width: 45px; height: 45px; border: 3px solid white;">
                                    <i class="mdi mdi-check-decagram text-primary" style="font-size: 30px;"></i>
                                </div>
                            @endif
                        </a>
                    </div>

                    <h4 class="fw-bold mt-2 text-dark">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>

                    <div class="mb-3">
                        @if ($user->is_active)
                            <span class="badge rounded-pill bg-success px-3 py-2"><i class="mdi mdi-account-check me-1"></i> AKTIF</span>
                        @else
                            <span class="badge rounded-pill bg-danger px-3 py-2"><i class="mdi mdi-account-off me-1"></i> NON-AKTIF</span>
                        @endif
                    </div>

                    {{-- MENU NAVIGASI --}}
                    <div class="text-start mb-4 mt-4">
                        <h6 class="text-muted text-small fw-bold mb-2 border-bottom pb-2">MENU & RIWAYAT</h6>
                        <div class="list-group list-group-flush">
                            
                            {{-- LOGIKA MENU KHUSUS ADMIN GAJI --}}
                            @if(auth()->user()->role == 'admin_gaji')
                                
                                <a href="{{ route('attendance.history', ['employeeId' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-calendar-month text-info me-2"></i> Riwayat Absen Bulanan</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>

                                <a href="{{ route('attendance.summary', ['user_id' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-chart-bar text-warning me-2"></i> Riwayat Absen Tahunan</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>

                                <a href="{{ route('salary-summary.index', ['user_id' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-file-chart text-success me-2"></i> Ringkasan Gaji Tahunan</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>
                                
                                <a href="{{ route('branch-salary.show', $user->branch_id ?? 0) }}?search={{ $user->name }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-cash-register text-primary me-2"></i> Riwayat Slip Gaji</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>

                                <a href="{{ route('employee-salaries.edit', $user->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-cog text-secondary me-2"></i> Setting Master Gaji</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>

                                {{-- SISA KASBON (LINK AKTIF UNTUK ADMIN GAJI) --}}
                                <a href="{{ route('kasbon.index') }}?search={{ $user->name }}&status=approved" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-wallet-outline text-danger me-2"></i> 
                                        <span>Sisa Kasbon Karyawan</span>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">Rp {{ number_format($totalKasbon ?? 0, 0, ',', '.') }}</span>
                                </a>

                            @else
                                {{-- MENU STANDAR (ADMIN / LEADER / AUDIT) --}}
                                <a href="{{ route('attendance.history', ['employeeId' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-calendar-clock text-primary me-2"></i> History Absen Full</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>
                                
                                <a href="{{ route('attendance.summary', ['user_id' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-chart-bar text-warning me-2"></i> History Tahunan</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>

                                <a href="#violationSection" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-alert-circle text-danger me-2"></i> Pelanggaran</span>
                                    @if($activeViolations->count() > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $activeViolations->count() }} Aktif</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill" style="opacity: 0.5">0</span>
                                    @endif
                                </a>

                                @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
                                    <a href="{{ route('employment-history.index', ['user_id' => $user->id, 'mode' => 'edit']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                        <span><i class="mdi mdi-briefcase-edit text-info me-2"></i> Riwayat Divisi/Cabang</span><i class="mdi mdi-chevron-right text-muted"></i>
                                    </a>
                                @endif

                                <a href="{{ route('inventory.index', ['user_id' => $user->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-package-variant text-success me-2"></i> History Inventaris</span><i class="mdi mdi-chevron-right text-muted"></i>
                                </a>

                                {{-- SISA KASBON (LOGIKA HAK AKSES LINK) --}}
                                @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                                    {{-- BISA KLIK LINK (Admin & Admin Gaji) --}}
                                    <a href="{{ route('kasbon.index') }}?search={{ $user->name }}&status=approved" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-wallet-outline text-danger me-2"></i> 
                                            <span>Sisa Kasbon Karyawan</span>
                                        </div>
                                        <span class="badge bg-danger rounded-pill">Rp {{ number_format($totalKasbon ?? 0, 0, ',', '.') }}</span>
                                    </a>
                                @else
                                    {{-- READ ONLY (Audit & Leader) --}}
                                    <div class="list-group-item d-flex justify-content-between align-items-center p-2 bg-light text-muted" style="cursor: default;">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-wallet-outline me-2"></i> 
                                            <span>Sisa Kasbon Karyawan</span>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill">Rp {{ number_format($totalKasbon ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                @endif

                            @endif

                        </div>
                    </div>

                    {{-- VERIFIKASI & KTP --}}
                    @if(auth()->user()->role != 'admin_gaji')
                        <div class="d-grid gap-2">
                            <form action="{{ route('users.verify', $user->id) }}" method="POST">
                                @csrf @method('PATCH')
                                @if ($user->is_verified)
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Cabut verifikasi?')"><i class="mdi mdi-close-circle"></i> Cabut Verifikasi</button>
                                @else
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="mdi mdi-check-decagram"></i> Verifikasi Akun</button>
                                @endif
                            </form>
                            @if ($user->ktp_photo_path)
                                <button type="button" class="btn btn-info btn-sm text-white w-100 mt-1" data-bs-toggle="modal" data-bs-target="#ktpPhotoModal"><i class="mdi mdi-card-account-details-outline"></i> Lihat Foto KTP</button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm w-100 mt-1" disabled><i class="mdi mdi-close-circle-outline"></i> KTP Belum Diupload</button>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (DETAIL & AKTIVITAS) --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="mdi mdi-account-details me-2"></i>Detail Informasi</h4>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Email</label>
                            <p class="h6 text-dark">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Cabang</label>
                            <p class="h6 text-dark">{{ $user->branch->name ?? '-' }} 
                                @if($user->branch && $user->branch->timezone) 
                                    <span class="badge bg-light text-muted border">{{ $user->branch->timezone }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">Divisi</label>
                            <p class="h6 text-dark">{{ $user->division->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted small">WhatsApp</label>
                            <p class="h6 text-dark">{{ $user->whatsapp ?? '-' }}</p>
                        </div>
                        
                        @if(auth()->user()->role == 'admin_gaji')
                             <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">Rekening</label>
                                <p class="h6 text-dark">
                                    {{ $user->employeeSalary->bank_name ?? '-' }} - {{ $user->employeeSalary->bank_account_number ?? '-' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">Kategori Gaji</label>
                                <p class="h6 text-dark">
                                    @if($user->employeeSalary)
                                        <span class="badge bg-info">{{ ucfirst($user->employeeSalary->category) }}</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Set</span>
                                    @endif
                                </p>
                            </div>
                        @else
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">Tipe Absensi</label>
                                @if ($user->only_security_scan)
                                    <p class="h6 text-danger fw-bold"><i class="mdi mdi-qrcode-scan"></i> Wajib Scan Security (Locked)</p>
                                @else
                                    <p class="h6 text-success"><i class="mdi mdi-cellphone"></i> Bisa Mandiri & Scan</p>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">Tanggal Lahir</label>
                                <p class="h6"><i class="mdi mdi-cake-variant text-warning me-1"></i> {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y') : '-' }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- TARGET & PENCAPAIAN (HANYA BUKAN ADMIN GAJI) --}}
                    @if(auth()->user()->role != 'admin_gaji')
                        <div class="mt-4 mb-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title mb-3 border-bottom pb-2 text-primary">
                                        <i class="mdi mdi-target me-1"></i> Target Aktif
                                    </h5>
                                    <div class="list-group list-group-flush">
                                        @forelse($activeTargets as $target)
                                            <div class="list-group-item px-0 py-2 border-bottom">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <h6 class="mb-1 text-dark fw-bold" style="font-size: 0.9rem;">
                                                        @if($target->star_level == 3) <span class="text-warning">★</span> @endif
                                                        {{ Str::limit($target->title, 25) }}
                                                    </h6>
                                                    <small class="text-muted">{{ $target->deadline->format('d M') }}</small>
                                                </div>
                                                <p class="mb-1 text-muted small" style="line-height: 1.2;">{{ Str::limit($target->description, 50) }}</p>
                                                <span class="badge bg-white text-primary border border-primary rounded-pill" style="font-size: 10px;">Ongoing</span>
                                            </div>
                                        @empty
                                            <div class="text-center py-3 text-muted small border rounded bg-light">
                                                <i class="mdi mdi-checkbox-multiple-blank-outline d-block mb-1"></i>
                                                Tidak ada target aktif.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="col-md-6 mt-4 mt-md-0">
                                    <h5 class="card-title mb-3 border-bottom pb-2 text-success">
                                        <i class="mdi mdi-trophy-variant me-1"></i> Pencapaian Terakhir
                                    </h5>
                                    <div class="list-group list-group-flush">
                                        @forelse($achievements as $ach)
                                            <div class="list-group-item px-0 py-2 border-bottom bg-transparent">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1 text-dark" style="font-size: 0.9rem;">{{ Str::limit($ach->title, 30) }}</h6>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    @if(str_contains($ach->type, 'achievement'))
                                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">PRESTASI</span>
                                                    @else
                                                        <span class="badge bg-success" style="font-size: 10px;">SELESAI</span>
                                                    @endif
                                                    <small class="text-muted" style="font-size: 11px;">
                                                        {{ $ach->completed_at ? \Carbon\Carbon::parse($ach->completed_at)->format('d M Y') : '-' }}
                                                    </small>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-3 text-muted small border rounded bg-light">
                                                <i class="mdi mdi-trophy-broken d-block mb-1"></i>
                                                Belum ada pencapaian.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL 5 KEHADIRAN TERAKHIR --}}
                        <div class="mt-4">
                            <h5 class="card-title mb-3 border-bottom pb-2">5 Kehadiran Terakhir</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Masuk (Lokal)</th>
                                            <th>Pulang (Lokal)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentAttendance as $log)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') }}</div>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->check_in_time)->format('l') }}</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark">
                                                        {{ $log->check_in_local instanceof \Carbon\Carbon ? $log->check_in_local->format('H:i') : \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                                    </span>
                                                    @if ($log->is_late_checkin) <i class="mdi mdi-alert-circle text-warning ms-1" title="Terlambat"></i> @endif
                                                </td>
                                                <td>
                                                    @if ($log->check_out_time)
                                                        <span class="fw-bold text-dark">
                                                             {{ $log->check_out_local instanceof \Carbon\Carbon ? $log->check_out_local->format('H:i') : \Carbon\Carbon::parse($log->check_out_time)->format('H:i') }}
                                                        </span>
                                                        @if ($log->is_early_checkout) <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Cepat</span> @endif
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
                                                        <span class="badge bg-success">Hadir</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Belum ada data kehadiran valid.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- PELANGGARAN SECTION --}}
                        <div class="mt-5" id="violationSection">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 text-danger"><i class="mdi mdi-gavel me-2"></i>Catatan Pelanggaran</h5>
                                @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                    <a href="{{ route('violations.create') }}" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-plus"></i> Input Pelanggaran</a>
                                @endif
                            </div>
                            <ul class="nav nav-tabs" id="violationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold text-danger" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-violations" type="button" role="tab">
                                        <i class="mdi mdi-alert-circle-outline"></i> Masih Berlaku @if($activeViolations->count() > 0) <span class="badge bg-danger ms-1">{{ $activeViolations->count() }}</span> @endif
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold text-secondary" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-violations" type="button" role="tab">
                                        <i class="mdi mdi-history"></i> Riwayat / Selesai <span class="badge bg-light text-dark ms-1 border">{{ $historyViolations->count() }}</span>
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content border border-top-0 p-3 rounded-bottom" id="violationTabsContent">
                                <div class="tab-pane fade show active" id="active-violations" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light"><tr><th>Level</th><th>Masalah</th><th>Berakhir Pada</th><th>Bukti</th></tr></thead>
                                            <tbody>
                                                @forelse($activeViolations as $v)
                                                    <tr>
                                                        <td>
                                                            @if($v->category == 'berat') <span class="badge bg-danger">BERAT</span>
                                                            @elseif($v->category == 'sedang') <span class="badge bg-warning text-dark">SEDANG</span>
                                                            @else <span class="badge bg-info">RINGAN</span> @endif
                                                        </td>
                                                        <td><div class="fw-bold text-dark">{{ $v->title }}</div><small class="text-muted">Tgl Input: {{ $v->created_at->format('d M Y') }}</small></td>
                                                        <td><span class="fw-bold text-danger">{{ $v->expires_at->format('d M Y') }}</span><br><small class="text-muted">({{ now()->diffInDays($v->expires_at) }} hari lagi)</small></td>
                                                        <td>@if($v->photo_path) <button class="btn btn-sm btn-light border" onclick="showImageModal('{{ asset('storage/' . $v->photo_path) }}', '{{ $v->title }}')"><i class="mdi mdi-image text-primary"></i></button> @else - @endif</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center py-4"><p class="mb-0 mt-2 text-success fw-bold">Tidak ada pelanggaran aktif.</p></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="history-violations" role="tabpanel">
                                    {{-- Content History Violation Sama --}}
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- JIKA ADMIN GAJI: TAMPILKAN GRAFIK GAJI SEDERHANA ATAU KOSONGKAN --}}
                        <div class="mt-4 text-center py-5 border rounded bg-light">
                            <i class="mdi mdi-lock-outline display-4 text-muted"></i>
                            <h5 class="text-muted mt-3">Informasi Lain Disembunyikan</h5>
                            <p class="small text-muted">Anda login sebagai Admin Gaji. Detail absensi dan pelanggaran hanya dapat diakses oleh Admin & Audit.</p>
                        </div>
                    @endif

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-light">Kembali</a>
                        @if(auth()->user()->role == 'admin')
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning text-white">Edit Data</a>
                        @endif
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

    {{-- MODAL BUKTI PELANGGARAN (Hanya Render Jika Bukan Admin Gaji) --}}
    @if(auth()->user()->role != 'admin_gaji')
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
    @endif

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