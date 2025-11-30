@extends('layout.master')

@section('title')
    Detail Cabang: {{ $branch->name }}
@endsection

@section('heading')
    Detail Cabang
@endsection

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
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold text-muted">Total Karyawan</span>
                            <span class="badge bg-primary fs-6">{{ $totalEmployees }}</span>
                        </div>
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
                        <table class="table table-hover">
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
                                            @if ($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                                    alt="image" class="img-sm rounded-circle" />
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                                                    alt="image" class="img-sm rounded-circle" />
                                            @endif
                                        </td>
                                        <td>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->login_id }}</small>
                                        </td>
                                        <td>
                                            @if ($user->division)
                                                <span
                                                    class="badge badge-outline-primary">{{ $user->division->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-danger">Non-Aktif</span>
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
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-account-off d-block fs-3 mb-2"></i>
                                            Belum ada karyawan di cabang ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <<div class="mt-4 d-flex justify-content-end">
                        {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
