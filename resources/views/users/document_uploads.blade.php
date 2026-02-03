@extends('layout.master')

@section('title')
    Monitor Upload Dokumen
@endsection

@section('heading')
    Monitor Upload Dokumen
@endsection

@section('content')
    <div class="row">
        {{-- STATISTIK CARDS --}}
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="mdi mdi-account-group mdi-48px me-3 opacity-75"></i>
                            <div>
                                <h5 class="mb-0">{{ $stats['total'] }}</h5>
                                <small>Total Karyawan</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="mdi mdi-check-circle mdi-48px me-3 opacity-75"></i>
                            <div>
                                <h5 class="mb-0">{{ $stats['complete'] }}</h5>
                                <small>Dokumen Lengkap</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="mdi mdi-alert-circle mdi-48px me-3 opacity-75"></i>
                            <div>
                                <h5 class="mb-0">{{ $stats['incomplete'] }}</h5>
                                <small>Belum Lengkap</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN TABLE --}}
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Status Dokumen Karyawan</h4>

                    {{-- FILTER & SEARCH --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        {{-- Filter Buttons --}}
                        <div class="btn-group" role="group">
                            <a href="{{ route('users.document-uploads', ['filter' => 'all']) }}"
                                class="btn btn-sm {{ $filter == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Semua
                            </a>
                            <a href="{{ route('users.document-uploads', ['filter' => 'complete']) }}"
                                class="btn btn-sm {{ $filter == 'complete' ? 'btn-success' : 'btn-outline-success' }}">
                                <i class="mdi mdi-check"></i> Lengkap
                            </a>
                            <a href="{{ route('users.document-uploads', ['filter' => 'incomplete']) }}"
                                class="btn btn-sm {{ $filter == 'incomplete' ? 'btn-danger' : 'btn-outline-danger' }}">
                                <i class="mdi mdi-alert"></i> Belum Lengkap
                            </a>
                        </div>

                        {{-- Search Form --}}
                        <form action="{{ route('users.document-uploads') }}" method="GET" class="d-flex">
                            <input type="hidden" name="filter" value="{{ $filter }}">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control" placeholder="Cari Nama / ID..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                                @if (request('search'))
                                    <a href="{{ route('users.document-uploads', ['filter' => $filter]) }}"
                                        class="btn btn-secondary" title="Reset">
                                        <i class="mdi mdi-refresh"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Profil Pengguna</th>
                                    <th>Login ID</th>
                                    <th>Cabang</th>
                                    <th>Divisi</th>
                                    <th class="text-center">Foto Profil</th>
                                    <th class="text-center">Foto KTP</th>
                                    <th>Terakhir Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $key => $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none"
                                                style="color: inherit;">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        @if ($user->profile_photo_path)
                                                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                                                alt="profile" class="rounded-circle"
                                                                style="width: 40px; height: 40px; object-fit: cover;">
                                                        @else
                                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                                                                alt="profile" class="rounded-circle"
                                                                style="width: 40px; height: 40px;">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $user->name }}</div>
                                                        <small
                                                            class="text-muted">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td><code>{{ $user->login_id ?? '-' }}</code></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <i class="mdi mdi-store me-1"></i>{{ $user->branch->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($user->divisions->isNotEmpty())
                                                @foreach ($user->divisions as $division)
                                                    <span class="badge bg-info text-dark mb-1">{{ $division->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($user->profile_photo_path)
                                                <span class="badge bg-success"><i class="mdi mdi-check"></i> Ada</span>
                                            @else
                                                <span class="badge bg-danger"><i class="mdi mdi-close"></i> Belum</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($user->ktp_photo_path)
                                                <span class="badge bg-success"><i class="mdi mdi-check"></i> Ada</span>
                                            @else
                                                <span class="badge bg-danger"><i class="mdi mdi-close"></i> Belum</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->updated_at ? $user->updated_at->diffForHumans() : '-' }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">Tidak ada data ditemukan.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection