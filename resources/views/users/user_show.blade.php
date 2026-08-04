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

                        {{-- [GAMIFICATION] USER RANK & XP - HIDDEN PER USER REQUEST
                        @php 
                            $rank = $user->calculateRank(); 
                            $isEternal = $rank['level'] == 20;
                            $isDarkText = in_array($rank['level'], [5, 7, 8, 12, 14, 16, 19]);
                        @endphp
                        <div class="mt-4 p-3 rounded-3 border shadow-sm bg-light text-start {{ $rank['effect_class'] }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-muted small fw-bold mb-0">CURRENT RANK</h6>
                                <div class="rank-icon-mini shadow-sm d-flex align-items-center justify-content-center {{ $rank['effect_class'] }}" 
                                     style="width: 35px; height: 35px; background: {{ $rank['color'] }}; border-radius: 8px; color: {{ $isDarkText ? '#000' : '#fff' }}; overflow: hidden;">
                                    @if($rank['rank_image'])
                                        <img src="{{ asset($rank['rank_image']) }}" alt="{{ $rank['name'] }}" style="width: 100%; height: 100%; object-fit: contain; transform: scale(1.2);">
                                    @else
                                        <i class="mdi {{ $rank['icon'] }} fs-5"></i>
                                    @endif
                                </div>
                            </div>
                            <h4 class="fw-bolder mb-1 rank-name-premium" 
                                style="color: {{ $rank['color'] }}; text-shadow: 0 1px 2px rgba(0,0,0,0.1); font-size: 1.8rem; display: none !important;">
                                {{ $rank['name'] }}
                                <span class="badge bg-dark text-white rounded-pill" style="font-size: 10px; vertical-align: middle; padding: 3px 8px;">Tier {{ $rank['level'] }}</span>
                            </h4>
                            <div class="progress mb-2" style="height: 10px; border-radius: 5px; background-color: #e9ecef;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                     style="width: {{ $user->getRankProgress() }}%; background-color: {{ $rank['color'] }};" 
                                     aria-valuenow="{{ $user->getRankProgress() }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted fw-bold">{{ number_format($user->xp) }} XP</small>
                                <small class="text-muted small">Level {{ $rank['level'] }} Progress</small>
                            </div>
                        </div>
                        --}}

                        {{-- Tombol Toggle Status (Admin, Audit, Admin Gaji Only) --}}
                        @if(in_array(auth()->user()->role, ['admin', 'audit', 'admin_gaji']) && $user->id != auth()->id())
                            <div class="mt-3 d-grid">
                                <form action="{{ route('users.toggle-status', $user->id) }}" method="POST">
                                    @csrf
                                    @if($user->is_active)
                                        <button type="submit" class="btn btn-inverse-danger btn-sm w-100" onclick="return confirm('Yakin ingin menonaktifkan akun ini? User akan dipindahkan ke EX Karyawan.')">
                                            <i class="mdi mdi-power-off me-1"></i> Nonaktifkan Akun
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-inverse-success btn-sm w-100">
                                            <i class="mdi mdi-power me-1"></i> Aktifkan Kembali
                                        </button>
                                    @endif
                                </form>
                            </div>
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

                                <a href="#violationSection" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span><i class="mdi mdi-alert-circle text-danger me-2"></i> Pelanggaran</span>
                                    @if($activeViolations->count() > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $activeViolations->count() }} Aktif</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill" style="opacity: 0.5">0</span>
                                    @endif
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
                                
                                {{-- UNLOCK KTP (ADMIN / AUDIT ONLY) --}}
                                @if (in_array(auth()->user()->role, ['admin', 'audit']) && !is_null($user->ktp_countdown_start_at) && is_null($user->ktp_unlock_at))
                                    @php
                                        $daysPassed = now()->diffInDays(\Carbon\Carbon::parse($user->ktp_countdown_start_at));
                                    @endphp
                                    @if($daysPassed > 7)
                                        <form action="{{ route('users.unlock-ktp', $user->id) }}" method="POST" class="mt-1">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold" onclick="return confirm('Buka blokir absensi untuk user ini?')">
                                                <i class="mdi mdi-lock-open-variant text-dark"></i> Buka Blokir KTP
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @endif
                        </div>
                    @endif

                    {{-- Admin Photo Upload Section (Visible to Admin/Audit roles) --}}
                    @if (in_array(auth()->user()->role, ['admin', 'audit']))
                        <div class="mt-4 pt-3 border-top text-start">
                            <h6 class="text-muted text-small fw-bold mb-3">KELOLA FOTO (ADMIN)</h6>
                            <form action="{{ route('users.admin-photo', $user->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="small fw-bold text-muted d-block mb-1">Ganti Foto Profil</label>
                                    <div class="input-group">
                                        <input type="file" name="profile_photo" class="form-control form-control-sm" accept="image/*" required>
                                        <button class="btn btn-outline-info btn-sm" type="submit">Upload</button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                        * Maksimal 10MB. Mengunggah akan langsung menimpa foto lama.
                                    </small>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- HISTORY RAPOR KARYAWAN --}}
                    @if(isset($evaluations) && $evaluations->count() > 0)
                        <div class="mt-4 pt-3 border-top text-start">
                            <h6 class="text-muted text-small fw-bold mb-3"><i class="mdi mdi-file-document-outline me-1"></i> RAPOR KARYAWAN (3 BLN)</h6>
                            <div class="list-group list-group-flush bg-white rounded p-2 border shadow-sm">
                                @foreach($evaluations as $evaluation)
                                    @php
                                        if ($evaluation->evaluation_date) {
                                            $displayDate = \Carbon\Carbon::parse($evaluation->evaluation_date)->translatedFormat('d M Y');
                                        } else {
                                            $monthName = \Carbon\Carbon::create()->month($evaluation->month)->translatedFormat('M');
                                            $displayDate = $monthName . ' ' . $evaluation->year;
                                        }
                                    @endphp
                                    <div class="list-group-item bg-transparent px-2 py-2 d-flex justify-content-between align-items-center border-bottom-0">
                                        <div>
                                            <span class="d-block fw-bold text-dark" style="font-size: 12px;">{{ $displayDate }}</span>
                                            <span class="text-muted" style="font-size: 10px;">Skor: {{ number_format($evaluation->average_score, 1) }}</span>
                                        </div>
                                        <div>
                                            <span class="badge {{ $evaluation->grade == 'A+' || $evaluation->grade == 'A' || $evaluation->grade == 'B+' || $evaluation->grade == 'B' ? 'bg-success' : ($evaluation->grade == 'C' ? 'bg-warning' : 'bg-danger') }} rounded-pill" style="font-size: 11px;">{{ $evaluation->grade }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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

                        {{-- Akses Wilayah (Khusus Audit & Leader) --}}
                        @if(in_array($user->role, ['audit', 'leader']))
                            <div class="col-12 mb-3">
                                <label class="fw-bold text-primary small">
                                    <i class="mdi mdi-map-marker-multiple me-1"></i>
                                    {{ $user->role == 'audit' ? 'Wilayah Kerja Audit' : 'Wilayah Kendali Leader' }}
                                </label>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    @forelse($user->branches as $b)
                                        <span class="badge bg-white text-primary border border-primary rounded-pill px-3 py-2 shadow-sm" style="font-weight: 600; font-size: 0.75rem;">
                                            <i class="mdi mdi-storefront-outline me-1"></i> {{ $b->name }}
                                        </span>
                                    @empty
                                        <p class="text-muted small"><i>Belum ada cabang khusus yang ditugaskan.</i></p>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                        {{-- Tanggal Join PStore (Visible: admin, audit, admin_gaji) --}}
                        @if(in_array(auth()->user()->role, ['admin', 'audit', 'admin_gaji']))
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">Tanggal Join PStore</label>
                                <p class="h6 text-dark">
                                    <i class="mdi mdi-calendar-star text-info me-1"></i>
                                    {{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->translatedFormat('d F Y') : ($user->created_at ? $user->created_at->translatedFormat('d F Y') : '-') }}
                                    @if($user->hire_date || $user->created_at)
                                        @php
                                            $diffDate = $user->hire_date ? \Carbon\Carbon::parse($user->hire_date) : $user->created_at;
                                        @endphp
                                        <span class="badge bg-light text-muted border ms-1" style="font-size: 10px;">{{ $diffDate->diffForHumans() }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">Akun Dibuat Oleh</label>
                                <p class="h6 text-dark">
                                    <i class="mdi mdi-account-plus text-primary me-1"></i>
                                    @if($user->creator)
                                        {{ $user->creator->name }} 
                                        <span class="badge bg-light text-muted border ms-1" style="font-size: 10px;">{{ strtoupper($user->creator->role) }}</span>
                                    @else
                                        <span class="text-muted">System / Migrasi</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                        
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
                                <p class="h6"><i class="mdi mdi-cake-variant text-warning me-1"></i> {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y') . ' (' . \Carbon\Carbon::parse($user->birth_date)->age . ' Tahun)' : '-' }}</p>
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
                                                    @if (str_contains(strtolower($log->presence_status ?? ''), 'telat') || $log->is_late_checkin)
                                                        <span class="badge bg-warning text-dark">Telat Hadir</span>
                                                    @elseif($log->presence_status == 'Izin' || $log->presence_status == 'Sakit' || $log->presence_status == 'Cuti')
                                                        <span class="badge bg-info">{{ $log->presence_status }}</span>
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
                                @if(in_array(auth()->user()->role, ['admin', 'audit', 'admin_gaji']))
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
                        {{-- JIKA ADMIN GAJI: TAMPILKAN PELANGGARAN + INFO TERBATAS --}}
                        <div class="mt-4 text-center py-5 border rounded bg-light">
                            <i class="mdi mdi-lock-outline display-4 text-muted"></i>
                            <h5 class="text-muted mt-3">Informasi Absensi Disembunyikan</h5>
                            <p class="small text-muted">Anda login sebagai Admin Gaji. Detail absensi hanya dapat diakses oleh Admin & Audit.</p>
                        </div>

                        {{-- PELANGGARAN SECTION (UNTUK ADMIN GAJI) --}}
                        <div class="mt-5" id="violationSection">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 text-danger"><i class="mdi mdi-gavel me-2"></i>Catatan Pelanggaran</h5>
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