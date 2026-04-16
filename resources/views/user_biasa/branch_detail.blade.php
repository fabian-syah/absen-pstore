@extends('layout.master')

@section('title')
    Detail Cabang - {{ $branch->name }}
@endsection

@section('heading')
    <a href="{{ route('team.my-branches') }}" class="text-decoration-none text-muted me-2">
        <i class="mdi mdi-arrow-left"></i> Kembali ke Cabang Saya
    </a>
@endsection

@section('content')
    <div class="row">
        {{-- HEADER INFO CABANG --}}
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1">{{ $branch->name }}</h3>
                            <p class="mb-0 opacity-75"><i class="mdi mdi-map-marker me-1"></i>
                                {{ $branch->address ?? 'Alamat belum diset' }}</p>
                        </div>
                        <div class="text-end">
                            <h1 class="fw-bold mb-0">{{ $employees->count() }}</h1>
                            <small class="opacity-75">Total Karyawan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR KARYAWAN --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Daftar Karyawan di {{ $branch->name }}</h4>

                        {{-- EXPORT LAPORAN BUTTONS --}}
                        @if(in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
                            <form method="GET" class="d-flex gap-2 align-items-center p-2 rounded justify-content-end"
                                style="background: #f8f9fa; border: 1px solid #dee2e6;">
                                <label class="small fw-bold text-dark me-1">Periode:</label> {{-- Label Explicit Black --}}
                                <select name="month" class="form-select form-select-sm text-dark border-secondary"
                                    style="width: 110px;">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-select form-select-sm text-dark border-secondary"
                                    style="width: 80px;">
                                    @foreach(range(date('Y'), date('Y') - 2) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                                <div class="btn-group btn-group-sm">
                                    <button type="submit" formaction="{{ route('branches.export.pdf', $branch->id) }}"
                                        class="btn btn-danger text-white btn-sm" title="Export PDF">
                                        <i class="mdi mdi-file-pdf"></i> PDF
                                    </button>
                                    <button type="submit" formaction="{{ route('branches.export.excel', $branch->id) }}"
                                        class="btn btn-success text-white btn-sm" title="Export Excel">
                                        <i class="mdi mdi-file-excel"></i> Excel
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Karyawan</th>
                                    <th>Posisi</th>
                                    <th>Status Hari Ini</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    @php
                                        $att = $emp->attendances->first();
                                    @endphp
                                    <tr>
                                        {{-- NAMA & FOTO (KLIK UNTUK KE PROFIL) --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- Link pada Foto --}}
                                                <a href="{{ route('users.show', $emp->id) }}" class="text-decoration-none">
                                                    @if($emp->profile_photo_path)
                                                        <img src="{{ asset('storage/' . $emp->profile_photo_path) }}"
                                                            class="rounded-circle me-3" width="40" height="40"
                                                            style="object-fit: cover">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                                            style="width: 40px; height: 40px; font-weight: bold;">
                                                            {{ substr($emp->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </a>

                                                <div>
                                                    {{-- Link pada Nama --}}
                                                    <a href="{{ route('users.show', $emp->id) }}"
                                                        class="text-decoration-none text-dark">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="fw-bold hover-text-primary">{{ $emp->name }}</div>
                                                            @php $rank = $emp->calculateRank(); @endphp
                                                            <span class="badge shadow-sm" style="background-color: {{ $rank['color'] }}; color: #000; font-size: 8px; font-weight: 800; padding: 1px 4px;">
                                                                {{ $emp->rank_title ?? 'Novice' }}
                                                            </span>
                                                        </div>
                                                    </a>
                                                    <small class="text-muted">{{ $emp->email }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- POSISI --}}
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $emp->division->name ?? '-' }}
                                            </span>
                                        </td>

                                        {{-- STATUS HARI INI --}}
                                        <td>
                                            @if($att)
                                                @php
                                                    $isLeave = $att->attendance_type == 'leave' || in_array(strtolower($att->presence_status), ['izin', 'sakit', 'cuti', 'libur', 'dinas luar']);
                                                @endphp

                                                @if($isLeave)
                                                    @php
                                                        $statusLower = strtolower($att->presence_status);
                                                        $badgeClass = match ($statusLower) {
                                                            'sakit' => 'bg-info',
                                                            'izin' => 'bg-warning text-dark',
                                                            'cuti' => 'bg-secondary',
                                                            'libur' => 'bg-purple',
                                                            'dinas luar' => 'bg-purple text-white',
                                                            'wfh' => 'bg-success',
                                                            default => 'bg-warning text-dark'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $att->presence_status }}</span>
                                                @elseif($att->check_out_time)
                                                    <span class="badge bg-primary">Pulang</span>
                                                @else
                                                    <span class="badge bg-success">Hadir (Online)</span>
                                                @endif

                                            @elseif($emp->today_leave)
                                                @php
                                                    $leaveType = $emp->today_leave->type;
                                                    $badgeClass = match ($leaveType) {
                                                        'sakit' => 'bg-info',
                                                        'izin' => 'bg-warning text-dark',
                                                        'cuti' => 'bg-secondary',
                                                        'wfh' => 'bg-success',
                                                        'telat' => 'bg-warning text-dark',
                                                        'libur' => 'bg-purple',
                                                        default => 'bg-secondary'
                                                    };

                                                    $label = ucfirst($leaveType);
                                                    if ($leaveType == 'wfh')
                                                        $label = 'WFH / Dinas';
                                                    if ($leaveType == 'telat')
                                                        $label = 'Izin Telat';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ $label }}
                                                </span>

                                            @else
                                                <span class="badge bg-danger">Belum Hadir / Alpha</span>
                                            @endif
                                        </td>

                                        {{-- JAM MASUK --}}
                                        <td class="text-success fw-bold">
                                            {{ $att ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '-' }}
                                        </td>

                                        {{-- JAM PULANG --}}
                                        <td class="text-primary fw-bold">
                                            {{ ($att && $att->check_out_time) ? \Carbon\Carbon::parse($att->check_out_time)->format('H:i') : '-' }}
                                        </td>

                                        {{-- AKSI --}}
                                        <td>
                                            {{-- PERBAIKAN: Menggunakan route 'my.team.attendance' agar melihat history user tsb
                                            --}}
                                            <a href="{{ route('team.branch.employee.history', ['branchId' => $branch->id, 'employeeId' => $emp->id]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="mdi mdi-history me-1"></i> Riwayat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="mdi mdi-account-off fs-1 d-block mb-2"></i>
                                            Tidak ada karyawan aktif di cabang ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Efek hover agar nama user berubah warna saat disorot */
        .hover-text-primary:hover {
            color: #0d6efd !important;
            /* Warna biru Bootstrap */
            transition: color 0.2s ease-in-out;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
            color: white !important;
        }
    </style>
@endsection