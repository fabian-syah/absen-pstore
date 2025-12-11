@extends('layout.master')

@section('title')
    Detail Cabang: {{ $branch->name }}
@endsection

@section('heading')
    Detail Cabang
@endsection

@push('styles')
    <style>
        /* Style untuk Pill Audit (Mirip Team Blade) */
        .audit-pill {
            background: rgba(13, 110, 253, 0.1); /* Warna Soft Primary */
            border: 1px solid rgba(13, 110, 253, 0.2);
            border-radius: 50px;
            padding: 4px 12px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            margin-bottom: 6px;
            margin-right: 4px;
        }
        .audit-pill:hover {
            background: rgba(13, 110, 253, 0.2);
            transform: translateY(-2px);
        }
        .audit-pill img, .audit-pill .audit-initial {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }
        .audit-initial {
            background: #0d6efd;
            color: white;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .audit-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #0d6efd;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        {{-- INFO CABANG --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Informasi Cabang</h4>

                    <div class="template-demo">
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

                        {{-- [BARU] SECTION AUDIT PENANGGUNG JAWAB --}}
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
                        {{-- [AKHIR] SECTION AUDIT --}}

                    </div>

                    <div class="mt-4 d-grid gap-2">
                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-warning text-white">
                            <i class="mdi mdi-pencil me-1"></i> Edit Cabang
                        </a>
                        <a href="{{ route('branches.index') }}" class="btn btn-light">
                            <i class="mdi mdi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR KARYAWAN --}}
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
                                    <th>Divisi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <div class="position-relative d-inline-block">
                                                @if ($user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                                        alt="image" 
                                                        class="img-sm rounded-circle"
                                                        style="width: 40px; height: 40px; object-fit: cover; border: {{ $user->is_verified ? '2px solid #0d6efd' : '2px solid #e9ecef' }}; padding: 1px;" />
                                                @else
                                                    {{-- Fallback ke Initial jika tidak ada foto --}}
                                                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 14px; border: {{ $user->is_verified ? '2px solid #0d6efd' : '2px solid #e9ecef' }};">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                @endif

                                                {{-- Verified Badge --}}
                                                @if($user->is_verified)
                                                    <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                                          style="bottom: -2px; right: -2px; width: 16px; height: 16px; border: 1px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                        <i class="mdi mdi-check-decagram text-primary" style="font-size: 12px;"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                                                @if($user->is_verified)
                                                    <i class="mdi mdi-check-decagram text-primary" title="Verified Account" style="font-size: 14px;"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted d-block mt-1">{{ $user->login_id }}</small>
                                        </td>
                                        <td>
                                            @if ($user->division)
                                                <span class="badge badge-outline-primary rounded-pill">{{ $user->division->name }}</span>
                                            @else
                                                <span class="text-muted small fst-italic">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->is_active)
                                                <span class="badge badge-success rounded-pill"><i class="mdi mdi-check me-1"></i> Aktif</span>
                                            @else
                                                <span class="badge badge-danger rounded-pill"><i class="mdi mdi-close me-1"></i> Non-Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="btn btn-sm btn-inverse-info btn-icon" title="Lihat Profil">
                                                <i class="mdi mdi-account-details"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle p-3 mb-2">
                                                    <i class="mdi mdi-account-off fs-2 text-secondary"></i>
                                                </div>
                                                <p class="mb-0">Belum ada karyawan di cabang ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection