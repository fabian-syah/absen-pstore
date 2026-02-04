@extends('layout.master')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Monitoring Cuti</h2>
                <p class="text-muted mb-0">Overview data cuti dan sisa saldo seluruh karyawan.</p>
            </div>

            {{-- Tombol Aksi (Opsional) --}}
            {{-- <a href="{{ route('leave-requests.create-cuti') }}"
                class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm fw-bold">
                <i class="mdi mdi-plus-circle-outline me-2"></i> Buat Pengajuan
            </a> --}}
        </div>

        {{-- STATISTICS CARDS --}}
        <div class="row g-3 mb-4">
            {{-- Card 1: Total Karyawan --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white h-100 rounded-4 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-white bg-opacity-25 text-white">Total Staff</span>
                            <i class="mdi mdi-account-group-outline fs-2 text-white opacity-50"></i>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ $stats['total_users'] }}</h2>
                        <small class="text-white opacity-75">Total Karyawan Aktif</small>
                    </div>
                </div>
            </div>

            {{-- Card 2: Menunggu Approval --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-white h-100 rounded-4 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-white bg-opacity-25 text-white">Need Action</span>
                            <i class="mdi mdi-clock-alert-outline fs-2 text-white opacity-50"></i>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ $stats['pending_requests'] }}</h2>
                        <small class="text-white opacity-75">Menunggu Approval</small>
                    </div>
                </div>
            </div>

            {{-- Card 3: Cuti Terpakai --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-danger text-white h-100 rounded-4 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-white bg-opacity-25 text-white">Used</span>
                            <i class="mdi mdi-calendar-remove-outline fs-2 text-white opacity-50"></i>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ $stats['total_taken'] }}</h2>
                        <small class="text-white opacity-75">Total Hari Terpakai</small>
                    </div>
                </div>
            </div>

            {{-- Card 4: Sisa Saldo --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white h-100 rounded-4 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-white bg-opacity-25 text-white">Balance</span>
                            <i class="mdi mdi-wallet-giftcard fs-2 text-white opacity-50"></i>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ $stats['total_balance'] }}</h2>
                        <small class="text-white opacity-75">Total Sisa Saldo</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER & SEARCH --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('leave-requests.admin-summary') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0"
                                    placeholder="Cari nama karyawan..." value="{{ request('search') }}">
                            </div>
                        </div>
                        {{-- Bisa ditambah filter cabang/divisi disini --}}
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark fw-bold w-100">
                                <i class="mdi mdi-filter-variant me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE USERS --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr class="text-uppercase small text-muted letter-spacing-1">
                                <th class="py-4 ps-4">Karyawan</th>
                                <th class="py-4">Cabang / Divisi</th>
                                <th class="py-4 text-center">Jatah Cuti</th>
                                <th class="py-4 text-center">Terpakai</th>
                                <th class="py-4 text-center">Sisa Saldo</th>
                                <th class="py-4 text-center pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $usr)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            {{-- Avatar --}}
                                            <div class="avatar-sm me-3">
                                                @if($usr->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $usr->profile_photo_path) }}" alt="..."
                                                        class="avatar-img rounded-circle shadow-sm"
                                                        style="width: 42px; height: 42px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center shadow-sm"
                                                        style="width: 42px; height: 42px; font-weight: bold; font-size: 14px;">
                                                        {{ substr($usr->name, 0, 2) }}
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
                                        <div class="d-flex flex-column small">
                                            <span class="text-dark fw-bold"><i
                                                    class="mdi mdi-office-building me-1 text-primary"></i>
                                                {{ $usr->branch->name ?? '-' }}</span>
                                            <span class="text-muted"><i class="mdi mdi-domain me-1"></i>
                                                {{ $usr->division->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <h6 class="mb-0 fw-bold">{{ $usr->yearly_leave_limit ?? 12 }}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6 class="mb-0 fw-bold text-danger">{{ $usr->leave_taken ?? 0 }}</h6>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $balance = $usr->leave_balance ?? 12;
                                            $balColor = $balance > 5 ? 'success' : ($balance > 0 ? 'warning' : 'danger');
                                        @endphp
                                        <h6 class="mb-0 fw-bold text-{{ $balColor }}">{{ $balance }}</h6>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($balance > 0)
                                            <span class="badge rounded-pill bg-light text-success border border-success px-3 py-2">
                                                Available
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-light text-danger border border-danger px-3 py-2">
                                                Habis
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                            <i class="mdi mdi-account-off-outline fs-1 mb-2"></i>
                                            <h6 class="fw-bold">Tidak ada data karyawan</h6>
                                            <p class="small">Coba ubah kata kunci pencarian.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-end">
                            {{ $users->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
    </style>
@endsection