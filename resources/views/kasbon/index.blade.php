@extends('layout.master')

@section('content')
<div class="container-fluid">
    
    {{-- 1. TITLE & ACTION BUTTON --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">Manajemen Kasbon</h3>
            <p class="text-muted mb-0">Overview data peminjaman dan status pembayaran karyawan.</p>
        </div>
        <a href="{{ route('kasbon.create') }}" class="btn btn-primary btn-lg shadow-sm px-4 fw-bold rounded-pill">
            <i class="mdi mdi-plus me-2"></i> Buat Pengajuan Baru
        </a>
    </div>

    {{-- 2. STATS WIDGETS (Agar terlihat Profesional) --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-lg bg-white h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning me-3">
                            <i class="mdi mdi-timer-sand fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Menunggu Approval</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $kasbons->where('status', 'pending')->count() }} Pengajuan</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-lg bg-white h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary me-3">
                            <i class="mdi mdi-cash-multiple fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Sedang Berjalan (Aktif)</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $kasbons->where('status', 'approved')->where('remaining_amount', '>', 0)->count() }} Karyawan</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-lg bg-white h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success me-3">
                            <i class="mdi mdi-check-decagram fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Total Lunas</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $kasbons->where('status', 'paid')->count() }} Selesai</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. MAIN TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small text-muted letter-spacing-1">
                            <th class="py-4 ps-4">Karyawan</th>
                            <th class="py-4">Detail Pengajuan</th>
                            <th class="py-4 text-end">Nominal Pinjam</th>
                            <th class="py-4 text-end">Sisa Kewajiban</th>
                            <th class="py-4 text-center">Status</th>
                            <th class="py-4 text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasbons as $k)
                            {{-- LOGIC PERBAIKAN TAMPILAN JSON --}}
                            @php
                                // Cek apakah Divisi tersimpan sebagai JSON String
                                $divisionName = $k->division;
                                $decodedDiv = json_decode($divisionName);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decodedDiv->name)) {
                                    $divisionName = $decodedDiv->name;
                                }

                                // Cek apakah Cabang tersimpan sebagai JSON String
                                $branchName = $k->branch;
                                $decodedBranch = json_decode($branchName);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decodedBranch->name)) {
                                    $branchName = $decodedBranch->name;
                                }
                            @endphp

                            <tr>
                                {{-- KOLOM 1: PROFILE --}}
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                            {{ substr($k->user_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $k->user_name }}</h6>
                                            <div class="small text-muted d-flex align-items-center mt-1">
                                                <i class="mdi mdi-briefcase-outline me-1"></i> {{ $divisionName }}
                                                <span class="mx-2">•</span>
                                                <i class="mdi mdi-map-marker-outline me-1"></i> {{ $branchName }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- KOLOM 2: INFO TANGGAL & KET --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-bold" style="font-size: 0.9rem;">
                                            <i class="mdi mdi-calendar-blank me-1 text-muted"></i> {{ $k->created_at->format('d M Y') }}
                                        </span>
                                        <small class="text-muted text-truncate mt-1" style="max-width: 200px;" title="{{ $k->description }}">
                                            {{ Str::limit($k->description, 30) }}
                                        </small>
                                    </div>
                                </td>

                                {{-- KOLOM 3: NOMINAL PINJAM --}}
                                <td class="text-end">
                                    <h6 class="mb-0 fw-bold text-dark">Rp {{ number_format($k->amount, 0, ',', '.') }}</h6>
                                    <small class="text-muted">Total</small>
                                </td>

                                {{-- KOLOM 4: SISA HUTANG --}}
                                <td class="text-end">
                                    <h6 class="mb-0 fw-bold text-danger">Rp {{ number_format($k->remaining_amount, 0, ',', '.') }}</h6>
                                    @if($k->amount > 0)
                                        @php $percent = ($k->total_paid / $k->amount) * 100; @endphp
                                        <div class="progress mt-1 ms-auto" style="height: 4px; width: 80px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                    @endif
                                </td>

                                {{-- KOLOM 5: STATUS --}}
                                <td class="text-center">
                                    @if($k->status == 'pending')
                                        <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 rounded-pill border border-warning fw-bold">
                                            <i class="mdi mdi-clock-outline me-1"></i> Pending
                                        </span>
                                    @elseif($k->status == 'approved')
                                        <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-2 rounded-pill border border-primary fw-bold">
                                            <i class="mdi mdi-run me-1"></i> Aktif
                                        </span>
                                    @elseif($k->status == 'paid')
                                        <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill border border-success fw-bold">
                                            <i class="mdi mdi-check-circle-outline me-1"></i> Lunas
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-2 rounded-pill border border-danger fw-bold">
                                            <i class="mdi mdi-close-circle-outline me-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>

                                {{-- KOLOM 6: AKSI --}}
                                <td class="text-center pe-4">
                                    <a href="{{ route('kasbon.show', $k->id) }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary hover-shadow transition">
                                        Detail <i class="mdi mdi-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="bg-light rounded-circle p-4 mb-3">
                                            <i class="mdi mdi-file-document-outline fs-1 text-muted"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark">Belum ada data kasbon</h5>
                                        <p class="text-muted mb-3">Silakan buat pengajuan baru untuk memulai.</p>
                                        <a href="{{ route('kasbon.create') }}" class="btn btn-primary rounded-pill px-4">
                                            Buat Pengajuan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection