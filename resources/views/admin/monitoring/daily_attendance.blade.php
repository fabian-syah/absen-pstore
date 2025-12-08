@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-dark">Monitoring Absensi Karyawan</h3>
            <p class="text-muted">Pantau karyawan yang belum checkout dan riwayat absensi bulanan.</p>
        </div>
    </div>

    {{-- =================================== --}}
    {{-- BAGIAN 1: FILTER BULAN & TAHUN      --}}
    {{-- =================================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form action="{{ route('admin.monitoring.daily') }}" method="GET" class="row align-items-end">
                        
                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Pilih Bulan</label>
                            <select name="month" class="form-control form-select">
                                @php
                                    $months = [
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ];
                                @endphp
                                @foreach($months as $key => $val)
                                    <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>
                                        {{ $val }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Pilih Tahun</label>
                            <select name="year" class="form-control form-select">
                                @for($y = 2025; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary text-white w-100">
                                <i class="mdi mdi-filter"></i> Tampilkan Data
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- BAGIAN 2: DAFTAR BELUM ABSEN PULANG (ALERT)  --}}
    {{-- ============================================ --}}
    @if($pendingCheckouts->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0 fw-bold"><i class="mdi mdi-alert-circle-outline"></i> Karyawan Belum Absen Pulang</h5>
                    <small>Daftar karyawan dengan sesi aktif (Check In tapi belum Check Out)</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Karyawan</th>
                                    <th>Cabang</th>
                                    <th>Waktu Check In</th>
                                    <th>Tanggal</th>
                                    <th>Durasi Kerja (Berjalan)</th>
                                    <th>Metode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingCheckouts as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $item->user->profile_photo_path ? asset('storage/'.$item->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($item->user->name) }}" 
                                                 alt="profile" class="img-sm rounded-circle me-2"/>
                                            <span class="fw-bold text-danger">{{ $item->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $item->branch->name ?? '-' }}</td>
                                    <td class="fw-bold">{{ $item->check_in_time->format('H:i') }}</td>
                                    <td>
                                        @if($item->check_in_time->isToday())
                                            <span class="badge badge-success">Hari Ini</span>
                                        @else
                                            <span class="badge badge-warning text-dark">{{ $item->check_in_time->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">
                                        {{ $item->check_in_time->diffForHumans() }}
                                    </td>
                                    <td>
                                        <span class="badge badge-outline-secondary">{{ strtoupper($item->attendance_type) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- =================================== --}}
    {{-- BAGIAN 3: TABEL RIWAYAT UTAMA       --}}
    {{-- =================================== --}}
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Riwayat Absensi - {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}</h4>
                        <span class="badge badge-info">{{ $attendanceHistory->count() }} Data Ditemukan</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Karyawan</th>
                                    <th>Cabang</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceHistory as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        {{ $item->check_in_time->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $item->user->profile_photo_path ? asset('storage/'.$item->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($item->user->name) }}" 
                                                 alt="profile" class="img-sm rounded-circle me-2"/>
                                            <div>
                                                <p class="mb-0 fw-bold">{{ $item->user->name }}</p>
                                                <small class="text-muted">{{ $item->user->division->name ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->branch->name ?? '-' }}</td>
                                    <td class="text-success fw-bold">
                                        {{ $item->check_in_time ? $item->check_in_time->format('H:i') : '-' }}
                                        @if($item->is_late_checkin)
                                            <span class="badge badge-danger ms-1" style="font-size: 10px;">Telat</span>
                                        @endif
                                    </td>
                                    <td class="text-danger fw-bold">
                                        {{ $item->check_out_time ? $item->check_out_time->format('H:i') : '-' }}
                                        @if(!$item->check_out_time)
                                            <span class="badge badge-opacity-warning ms-1 text-dark" style="font-size: 10px;">Belum</span>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        @if($item->attendance_type == 'self')
                                            <div class="badge badge-opacity-info">Mandiri</div>
                                        @elseif($item->attendance_type == 'scan')
                                            <div class="badge badge-opacity-warning">Scan Security</div>
                                            <div class="mt-1 small text-muted">By: {{ $item->scanner->name ?? '-' }}</div>
                                        @else
                                            <div class="badge badge-secondary">{{ $item->attendance_type }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $item->verification_badge_color }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($item->photo_path)
                                                <a href="{{ asset('storage/'.$item->photo_path) }}" target="_blank" title="Foto Masuk">
                                                    <i class="mdi mdi-login text-success" style="font-size: 20px;"></i>
                                                </a>
                                            @endif
                                            @if($item->photo_out_path)
                                                <a href="{{ asset('storage/'.$item->photo_out_path) }}" target="_blank" title="Foto Pulang">
                                                    <i class="mdi mdi-logout text-danger" style="font-size: 20px;"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="mdi mdi-calendar-blank text-muted" style="font-size: 40px;"></i>
                                        <p class="text-muted mt-2">Tidak ada data absensi untuk periode ini.</p>
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

</div>
@endsection