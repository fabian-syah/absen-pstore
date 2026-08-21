@extends('layout.master')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Izin & Cuti (Aktif & Mendatang)</h2>
                <p class="text-muted mb-0">Daftar karyawan dengan izin/cuti berjalan atau akan datang.</p>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('leave-requests.admin-summary') }}" class="btn btn-outline-dark btn-lg px-4 rounded-pill shadow-sm fw-bold small">
                    <i class="mdi mdi-chart-line me-2"></i> Dashboard Cuti
                </a>
            </div>
        </div>

        {{-- FILTER & SEARCH --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('leave-requests.active') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-muted fw-bold">Pencarian Karyawan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0"
                                    placeholder="Cari nama karyawan..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark fw-bold w-100">
                                <i class="mdi mdi-filter-variant me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE ACTIVE LEAVES --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr class="text-uppercase small text-muted letter-spacing-1">
                                <th class="py-4 ps-4">Karyawan</th>
                                <th class="py-4">Cabang</th>
                                <th class="py-4 text-center">Periode Cuti</th>
                                <th class="py-4 text-center">Status Progress</th>
                                <th class="py-4">Disetujui Oleh</th>
                                <th class="py-4 text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                                @php
                                    $start = \Carbon\Carbon::parse($leave->start_date);
                                    $end = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date) : $start;
                                    $total = $start->diffInDays($end) + 1;
                                    
                                    // Hitung hari yang sudah terlewati (termasuk hari ini)
                                    $today = now()->startOfDay();
                                    $elapsed = 0;
                                    if ($today->gte($start)) {
                                        $elapsed = $start->diffInDays($today->min($end)) + 1;
                                    }
                                    
                                    $percentage = ($elapsed / $total) * 100;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                @if($leave->user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $leave->user->profile_photo_path) }}" alt="..."
                                                        class="avatar-img rounded-circle shadow-sm"
                                                        style="width: 42px; height: 42px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center shadow-sm"
                                                        style="width: 42px; height: 42px; font-weight: bold; font-size: 14px;">
                                                        {{ substr($leave->user->name, 0, 2) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $leave->user->name }}</h6>
                                                <small class="text-muted">{{ $leave->user->division->name ?? 'No Division' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info border border-info rounded-pill px-3 py-2">
                                            <i class="mdi mdi-office-building me-1"></i> {{ $leave->user->branch->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-dark small">
                                            {{ $start->format('d/m/Y') }} 
                                            @if($leave->end_date)
                                                <i class="mdi mdi-arrow-right mx-1 text-muted"></i> 
                                                {{ $end->format('d/m/Y') }}
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $leave->reason }}
                                        </div>
                                    </td>
                                    <td class="text-center" style="min-width: 180px;">
                                        <div class="d-flex justify-content-between align-items-center mb-1 small fw-bold">
                                            <span class="text-primary">Hari ke-{{ $elapsed }}</span>
                                            <span class="text-muted">{{ $elapsed }}/{{ $total }} Hari</span>
                                        </div>
                                        <div class="progress rounded-pill shadow-none" style="height: 6px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                                 role="progressbar" 
                                                 style="width: {{ $percentage }}%" 
                                                 aria-valuenow="{{ $percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="mt-1 x-small text-muted text-start" style="font-size: 10px;">
                                            *Sisa {{ $total - $elapsed }} hari lagi
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-check-decagram text-success me-2"></i>
                                            <span class="small fw-bold">{{ $leave->approver->name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- TOMBOL AKHIRI AWAL (Jika sudah jalan minimal 1 hari) --}}
                                            @if($elapsed > 0 && $elapsed < $total)
                                                <button type="button" class="btn btn-soft-warning btn-icon rounded-circle shadow-sm" 
                                                        onclick="confirmFinishEarly('{{ $leave->id }}', '{{ $leave->user->name }}', '{{ $elapsed }}')"
                                                        title="Akhiri Izin (Masuk Hari Ini)">
                                                    <i class="mdi mdi-calendar-check"></i>
                                                </button>
                                                <form id="finish-early-form-{{ $leave->id }}" action="{{ route('leave-requests.finish-early-admin', $leave->id) }}" method="POST" class="d-none">
                                                    @csrf
                                                    @method('PATCH')
                                                </form>
                                            @endif

                                            <button type="button" class="btn btn-soft-danger btn-icon rounded-circle shadow-sm" 
                                                    onclick="confirmDelete('{{ $leave->id }}', '{{ $leave->user->name }}')"
                                                    title="Hapus Permanen & Restore Penuh">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                            <form id="delete-form-{{ $leave->id }}" action="{{ route('leave-requests.destroy-approved', $leave->id) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                            <div class="bg-light rounded-circle p-4 mb-3">
                                                <i class="mdi mdi-calendar-blank fs-1 text-muted"></i>
                                            </div>
                                            <h6 class="fw-bold">Tidak Ada Data Izin/Cuti</h6>
                                            <p class="small">Tidak ada karyawan yang tercatat sedang izin/cuti hari ini atau mendatang.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leaves->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-end">
                            {{ $leaves->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Batalkan Izin/Cuti?',
                text: `Data izin/cuti ${name} akan DIBATALKAN. Jika ini cuti, saldo akan dikembalikan. Absensi yang tercatat juga akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function confirmFinishEarly(id, name, elapsed) {
            Swal.fire({
                title: 'Akhiri Izin Lebih Awal?',
                text: `${name} masuk kerja hari ini? Masa izin akan diubah menjadi ${parseInt(elapsed) - 1} hari (selesai kemarin). Sisa saldo (jika cuti) akan dikembalikan.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Masuk Hari Ini',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('finish-early-form-' + id).submit();
                }
            });
        }
    </script>

    <style>
        .letter-spacing-1 {
            letter-spacing: 1px;
        }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1) !important; }
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1) !important; }
        .btn-soft-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
            transition: all 0.3s;
        }
        .btn-soft-danger:hover {
            background-color: #dc3545;
            color: white;
            transform: scale(1.1);
        }
        .btn-soft-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
            transition: all 0.3s;
        }
        .btn-soft-warning:hover {
            background-color: #ffc107;
            color: white;
            transform: scale(1.1);
        }
        .btn-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .x-small { font-size: 10px; }
    </style>
@endsection
