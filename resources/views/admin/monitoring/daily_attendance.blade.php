@extends('layout.master') {{-- Sesuaikan dengan layout utama kamu --}}

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Monitoring Absensi Hari Ini</h4>
                <p class="card-description">
                    Daftar karyawan yang sudah melakukan absen Masuk pada tanggal <b>{{ \Carbon\Carbon::now()->format('d M Y') }}</b>
                </p>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Karyawan</th>
                                <th>Cabang / Divisi</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Metode Absen</th> {{-- Kolom Khusus Metode --}}
                                <th>Status</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- Tampilkan foto profil kecil --}}
                                        <img src="{{ $item->user->profile_photo_path ? asset('storage/'.$item->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($item->user->name) }}" 
                                             alt="profile" class="img-sm rounded-circle me-2"/>
                                        <div>
                                            <p class="mb-0 fw-bold">{{ $item->user->name }}</p>
                                            <small class="text-muted">{{ $item->user->role }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $item->branch->name ?? '-' }} <br>
                                    <small class="text-muted">{{ $item->user->division->name ?? '-' }}</small>
                                </td>
                                <td class="text-success fw-bold">
                                    {{ $item->check_in_time ? $item->check_in_time->format('H:i') : '-' }}
                                    @if($item->is_late_checkin)
                                        <span class="badge badge-danger ms-1" style="font-size: 10px;">Telat</span>
                                    @endif
                                </td>
                                <td class="text-danger fw-bold">
                                    {{ $item->check_out_time ? $item->check_out_time->format('H:i') : '-' }}
                                </td>
                                
                                {{-- LOGIKA MENAMPILKAN METODE ABSEN --}}
                                <td>
                                    @if($item->attendance_type == 'self')
                                        <div class="badge badge-opacity-info">
                                            <i class="mdi mdi-cellphone-link"></i> Mandiri
                                        </div>
                                        <div class="mt-1 small text-muted">Device User</div>
                                    @elseif($item->attendance_type == 'scan')
                                        <div class="badge badge-opacity-warning">
                                            <i class="mdi mdi-qrcode-scan"></i> Security Scan
                                        </div>
                                        {{-- Tampilkan nama security yang scan --}}
                                        <div class="mt-1 small text-muted">
                                            Oleh: {{ $item->scanner->name ?? 'Unknown' }}
                                        </div>
                                    @else
                                        <div class="badge badge-secondary">Manual/Lainnya</div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-{{ $item->verification_badge_color }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->photo_path)
                                        <a href="{{ asset('storage/'.$item->photo_path) }}" target="_blank">
                                            <i class="mdi mdi-image text-primary" style="font-size: 20px;"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="mdi mdi-calendar-blank text-muted" style="font-size: 40px;"></i>
                                    <p class="text-muted">Belum ada data absensi hari ini.</p>
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