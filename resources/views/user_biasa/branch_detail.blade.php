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
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">{{ $branch->name }}</h3>
                        <p class="mb-0 opacity-75"><i class="mdi mdi-map-marker me-1"></i> {{ $branch->address ?? 'Alamat belum diset' }}</p>
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
                <h4 class="card-title mb-4">Daftar Karyawan di {{ $branch->name }}</h4>
                
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
                                    {{-- NAMA & FOTO --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($emp->profile_photo_path)
                                                <img src="{{ asset('storage/'.$emp->profile_photo_path) }}" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                    {{ substr($emp->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $emp->name }}</div>
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

                                    {{-- STATUS HARI INI (LOGIKA BARU) --}}
                                    <td>
                                        {{-- Prioritas 1: Cek Absensi Real (Scan/Selfie) --}}
                                        @if($att)
                                            @if($att->check_out_time)
                                                <span class="badge bg-primary">Pulang</span>
                                            @else
                                                <span class="badge bg-success">Hadir (Online)</span>
                                            @endif
                                        
                                        {{-- Prioritas 2: Cek Izin/Sakit/Cuti/WFH (Dari Controller) --}}
                                        @elseif($emp->today_leave)
                                            @php
                                                $leaveType = $emp->today_leave->type;
                                                $badgeClass = match($leaveType) {
                                                    'sakit' => 'bg-info', // Biru muda
                                                    'izin' => 'bg-warning text-dark', // Kuning
                                                    'cuti' => 'bg-secondary', // Abu-abu
                                                    'wfh' => 'bg-success', // Hijau (Anggap kerja)
                                                    'telat' => 'bg-warning text-dark',
                                                    default => 'bg-secondary'
                                                };
                                                
                                                $label = ucfirst($leaveType);
                                                if($leaveType == 'wfh') $label = 'WFH / Dinas';
                                                if($leaveType == 'telat') $label = 'Izin Telat';
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $label }}
                                            </span>

                                        {{-- Prioritas 3: Sisanya Alpha --}}
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
@endsection