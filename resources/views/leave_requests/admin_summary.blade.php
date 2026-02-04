@extends('layout.master')

@section('title')
    Monitoring Cuti Karyawan
@endsection

@section('heading')
    <div class="d-flex align-items-center justify-content-between">
        <h4 class="mb-0 fw-bold">Monitoring Cuti Karyawan</h4>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body">
                    {{-- SEARCH BAR --}}
                    <form action="{{ route('leave-requests.admin-summary') }}" method="GET" class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-start-0"
                                placeholder="Cari nama karyawan..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary px-4">Cari</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Karyawan</th>
                                    <th>Cabang / Divisi</th>
                                    <th class="text-center">Jatah Cuti</th>
                                    <th class="text-center">Terpakai</th>
                                    <th class="text-center">Sisa</th>
                                    {{-- <th class="text-end pe-4">Aksi</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $usr)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                {{-- Avatar Placeholder --}}
                                                <div class="avatar-sm me-3">
                                                    @if($usr->profile_photo_path)
                                                        <img src="{{ asset('storage/' . $usr->profile_photo_path) }}" alt="..."
                                                            class="avatar-img rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center"
                                                            style="width: 40px; height: 40px; font-weight: bold;">
                                                            {{ substr($usr->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $usr->name }}</h6>
                                                    <small class="text-muted">{{ $usr->role }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <i class="mdi mdi-office-building text-muted me-1"></i>
                                                {{ $usr->branch->name ?? '-' }}
                                                <br>
                                                <i class="mdi mdi-domain text-muted me-1"></i> {{ $usr->division->name ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-primary rounded-pill px-3">{{ $usr->yearly_leave_limit ?? 10 }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-warning text-dark rounded-pill px-3">{{ $usr->leave_taken ?? 0 }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $balance = $usr->leave_balance ?? 10;
                                                $color = $balance > 5 ? 'success' : ($balance > 0 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $color }} rounded-pill px-3 fs-6">{{ $balance }}</span>
                                        </td>
                                        {{-- <td class="text-end pe-4">
                                            <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                <i class="mdi mdi-history me-1"></i> Detail
                                            </a>
                                        </td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">User tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-4 pb-4 d-flex justify-content-end">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection