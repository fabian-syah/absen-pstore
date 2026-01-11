@extends('layout.master')

@section('title')
    Detail Cabang: {{ $branch->name }}
@endsection

@section('heading')
    Detail Cabang
@endsection

@push('styles')
    <style>
        .audit-pill { background: rgba(13, 110, 253, 0.1); border: 1px solid rgba(13, 110, 253, 0.2); border-radius: 50px; padding: 4px 12px; display: inline-flex; align-items: center; transition: all 0.3s ease; margin-bottom: 6px; margin-right: 4px; }
        .audit-pill:hover { background: rgba(13, 110, 253, 0.2); transform: translateY(-2px); }
        .audit-pill img, .audit-pill .audit-initial { width: 20px; height: 20px; border-radius: 50%; object-fit: cover; margin-right: 8px; }
        .audit-initial { background: #0d6efd; color: white; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .audit-name { font-size: 0.8rem; font-weight: 600; color: #0d6efd; }
        .badge-role-custom { padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 3px 6px rgba(0,0,0,0.08); border: 1px solid rgba(255,255,255,0.4); transition: transform 0.2s; }
        .badge-role-custom:hover { transform: translateY(-1px); }
        .role-leader { background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); color: #7a5800; border: 1px solid #ffeeb0; }
        .role-employee { background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%); color: #4a5073; border: 1px solid #dae1f5; }
        .role-default { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        .tr-leader-highlight { background-color: #fffef2 !important; }
        .tr-leader-highlight:hover { background-color: #fff9db !important; }
    </style>
@endpush

@section('content')
    <div class="row">
        {{-- KOLOM KIRI: INFO CABANG & TARGET CABANG --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Informasi Cabang</h4>

                    <div class="template-demo mb-4">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-bold text-muted">Nama Cabang</span>
                            <span class="text-dark">{{ $branch->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-bold text-muted">Alamat</span>
                            <span class="text-dark text-end" style="max-width: 200px;">{{ $branch->address ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-bold text-muted">Total Karyawan</span>
                            <span class="badge bg-primary fs-6">{{ $totalEmployees }}</span>
                        </div>
                        {{-- Menampilkan Timezone Cabang agar informatif --}}
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-bold text-muted">Zona Waktu</span>
                            <span class="text-dark">
                                {{ $branch->timezone }} 
                                ({{ $branch->timezone == 'Asia/Jakarta' ? 'WIB' : ($branch->timezone == 'Asia/Makassar' ? 'WITA' : 'WIT') }})
                            </span>
                        </div>

                        <div class="py-3">
                            <span class="fw-bold text-muted d-block mb-2">Audit Penanggung Jawab</span>
                            <div class="d-flex flex-wrap">
                                @forelse($assignedAudits as $audit)
                                    <div class="audit-pill" title="{{ $audit->name }}">
                                        @if($audit->profile_photo_path)
                                            <img src="{{ asset('storage/' . $audit->profile_photo_path) }}" alt="audit">
                                        @else
                                            <div class="audit-initial">{{ substr($audit->name, 0, 1) }}</div>
                                        @endif
                                        <span class="audit-name">{{ Str::limit($audit->name, 15) }}</span>
                                    </div>
                                @empty
                                    <span class="text-muted small fst-italic">Belum ada audit ditugaskan.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- [NEW SECTION] TARGET & PRESTASI CABANG --}}
                    <div class="border-top pt-4">
                        <h5 class="card-title mb-3 text-primary"><i class="mdi mdi-target-variant me-1"></i> Target & Prestasi</h5>
                        
                        <ul class="nav nav-tabs" id="branchTargetTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active small p-2" id="targets-tab" data-bs-toggle="tab" data-bs-target="#targets-content" type="button">Target Aktif</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link small p-2" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements-content" type="button">Pencapaian</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content border border-top-0 p-2 rounded-bottom" style="max-height: 300px; overflow-y: auto;">
                            {{-- TAB TARGET --}}
                            <div class="tab-pane fade show active" id="targets-content">
                                @forelse($branchTargets as $bt)
                                    <div class="border-bottom py-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-dark small">{{ Str::limit($bt->title, 25) }}</span>
                                            <span class="badge bg-light text-dark border" style="font-size: 9px;">{{ $bt->deadline->format('d M') }}</span>
                                        </div>
                                        <p class="mb-0 text-muted small" style="font-size: 11px;">{{ Str::limit($bt->description, 40) }}</p>
                                    </div>
                                @empty
                                    <p class="text-center text-muted small py-2">Tidak ada target aktif.</p>
                                @endforelse
                            </div>

                            {{-- TAB PENCAPAIAN --}}
                            <div class="tab-pane fade" id="achievements-content">
                                @forelse($branchAchievements as $ba)
                                    <div class="border-bottom py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark small">{{ Str::limit($ba->title, 25) }}</span>
                                            @if(str_contains($ba->type, 'achievement'))
                                                <i class="mdi mdi-trophy text-warning" title="Prestasi"></i>
                                            @else
                                                <i class="mdi mdi-check-circle text-success" title="Selesai"></i>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 10px;">{{ $ba->completed_at ? \Carbon\Carbon::parse($ba->completed_at)->format('d M Y') : '-' }}</small>
                                    </div>
                                @empty
                                    <p class="text-center text-muted small py-2">Belum ada pencapaian.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-grid gap-2">
                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-warning text-white"><i class="mdi mdi-pencil me-1"></i> Edit Cabang</a>
                        <a href="{{ route('branches.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left me-1"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: DAFTAR KARYAWAN --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Daftar Karyawan</h4>
                    <p class="text-muted mb-4">Karyawan yang terdaftar di cabang ini.</p>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Nama / ID</th>
                                    <th>Status Login</th> {{-- KOLOM BARU --}}
                                    <th>Jabatan</th>
                                    <th>Divisi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $user)
                                    <tr class="{{ $user->role == 'leader' ? 'tr-leader-highlight' : '' }}">
                                        <td>
                                            <div class="position-relative d-inline-block">
                                                @if ($user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="image" class="img-sm rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: {{ $user->is_verified ? '2px solid #0d6efd' : '2px solid #e9ecef' }}; padding: 1px;" />
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 14px; border: {{ $user->is_verified ? '2px solid #0d6efd' : '2px solid #e9ecef' }};">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                @if($user->is_verified)
                                                    <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" style="bottom: -2px; right: -2px; width: 16px; height: 16px; border: 1px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                        <i class="mdi mdi-check-decagram text-primary" style="font-size: 12px;"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1"><h6 class="mb-0 fw-semibold">{{ $user->name }}</h6></div>
                                            <small class="text-muted d-block mt-1">{{ $user->login_id }}</small>
                                        </td>
                                        
                                        {{-- LOGIKA LAST SEEN SESUAI TIMEZONE CABANG --}}
                                        <td>
                                            @php
                                                // Ambil timezone dari object branch yang dikirim controller
                                                $branchTz = $branch->timezone ?? 'Asia/Jakarta';
                                                
                                                // Cek cache online (gunakan Facades Cache secara eksplisit agar aman di blade)
                                                $isOnline = \Illuminate\Support\Facades\Cache::has('user-is-online-' . $user->id);
                                                
                                                // Konversi last_login_at ke timezone cabang
                                                $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->setTimezone($branchTz) : null;
                                            @endphp

                                            @if($isOnline)
                                                <span class="badge badge-success d-flex align-items-center" style="width: fit-content; padding: 5px 10px;">
                                                    <i class="mdi mdi-circle me-1" style="font-size: 8px;"></i> Online
                                                </span>
                                            @elseif($lastLogin)
                                                <div class="lh-1">
                                                    <div class="fw-bold mb-1" style="font-size: 0.8rem;">{{ $lastLogin->translatedFormat('d M Y') }}</div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="mdi mdi-clock-outline me-1"></i>{{ $lastLogin->format('H:i') }} 
                                                        {{ $branchTz == 'Asia/Jakarta' ? 'WIB' : ($branchTz == 'Asia/Makassar' ? 'WITA' : 'WIT') }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted small fst-italic">Belum Login</span>
                                            @endif
                                        </td>

                                        <td>
                                            @php
                                                if($user->role == 'leader') { $displayRole = 'Leader'; $badgeClass = 'role-leader'; $icon = 'mdi-crown'; } 
                                                elseif($user->role == 'user_biasa') { $displayRole = 'Karyawan'; $badgeClass = 'role-employee'; $icon = 'mdi-account-tie'; } 
                                                else { $displayRole = $user->role; $badgeClass = 'role-default'; $icon = 'mdi-account-circle'; }
                                            @endphp
                                            <span class="badge-role-custom {{ $badgeClass }}"><i class="mdi {{ $icon }} fs-6"></i> {{ $displayRole }}</span>
                                        </td>
                                        <td>
                                            @if ($user->division) <span class="badge badge-outline-primary rounded-pill">{{ $user->division->name }}</span> @else <span class="text-muted small fst-italic">-</span> @endif
                                        </td>
                                        <td>
                                            @if ($user->is_active) <span class="badge badge-success rounded-pill"><i class="mdi mdi-check me-1"></i> Aktif</span> @else <span class="badge badge-danger rounded-pill"><i class="mdi mdi-close me-1"></i> Non-Aktif</span> @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-inverse-info btn-icon" title="Lihat Profil"><i class="mdi mdi-account-details"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle p-3 mb-2"><i class="mdi mdi-account-off fs-2 text-secondary"></i></div>
                                                <p class="mb-0">Belum ada karyawan di cabang ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection