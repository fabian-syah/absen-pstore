@extends('layout.master')

@section('title', 'Dashboard')

@section('heading')
    <div class="d-flex align-items-center">
        <i class="mdi mdi-home-variant-outline text-primary me-2 fs-4"></i>
        <span>Dashboard Ringkasan</span>
    </div>
@endsection

@section('content')
    {{-- Notifikasi Khusus: Ultah & Ramadhan --}}
    @if (isset($birthdayData) && $birthdayData)
        @include('dashboard.partials.birthday_card')
    @endif

    {{-- Dashboard Statistik Cards --}}
    <div class="row g-3 mb-4">
        @if (auth()->user()->role == 'admin')
            <div class="col-6 col-md-3">
                <div class="card bg-white p-3 border-start border-primary border-4 shadow-sm h-100">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Total Karyawan</small>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <h3 class="fw-bold mb-0 count-up" data-target="{{ $totalUsers }}">0</h3>
                        <div class="bg-light-primary rounded p-2"><i class="mdi mdi-account-group text-primary"></i></div>
                    </div>
                </div>
            </div>
            {{-- Duplicate for other admin stats --}}
        @endif
    </div>

    {{-- MAIN ACTION & STATUS --}}
    <div class="row g-4">
        {{-- SISI KIRI: STATUS & ABSEN --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4" style="background: #fff; overflow: hidden;">
                <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="mdi mdi-fingerprint me-2 text-primary"></i>Sesi Absensi Hari Ini</h5>
                    <div class="text-end">
                        <h4 class="fw-bold mb-0 text-primary" id="realtime-clock">--:--:--</h4>
                        <small class="text-muted">{{ date('d M Y') }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    {{-- Status Absensi Logic --}}
                    @if ($myAttendanceToday)
                        <div class="status-box p-4 rounded-3 text-center" style="background: #f8fdfa; border: 1px dashed #28a745;">
                            <i class="mdi mdi-check-circle text-success display-4"></i>
                            <h4 class="fw-bold mt-2">Anda Sudah Berada di Kantor</h4>
                            <p class="text-muted">Masuk Pukul: {{ $myAttendanceToday->check_in_time->format('H:i') }}</p>
                            
                            @if (!$myAttendanceToday->check_out_time)
                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" 
                                   class="btn btn-danger btn-lg px-5 rounded-pill shadow mt-3 w-100">
                                   <i class="mdi mdi-logout me-2"></i>Absen Pulang Sekarang
                                </a>
                            @else
                                <div class="alert alert-info py-2">Anda telah menyelesaikan sesi hari ini.</div>
                            @endif
                        </div>
                    @else
                        <div class="status-box p-4 rounded-3 text-center bg-light border border-dashed">
                            <i class="mdi mdi-clock-alert text-warning display-4"></i>
                            <h4 class="fw-bold mt-2">Belum Ada Sesi Aktif</h4>
                            <p class="text-muted">Silahkan lakukan absen masuk melalui Security atau Mandiri.</p>
                            <div class="d-grid gap-2 d-md-flex justify-content-center">
                                <a href="{{ route('self.attend.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow">
                                    <i class="mdi mdi-camera-account me-2"></i>Absen Masuk Mandiri
                                </a>
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                                    <i class="mdi mdi-file-document-edit me-2"></i>Ajukan Izin/Sakit
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- GALLERY MOMENTS --}}
            <div class="mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="mdi mdi-camera-outline me-2 text-danger"></i>Momen Kerja Bulan Ini
                </h5>
                <div class="d-flex gap-3 overflow-auto pb-3 custom-scrollbar">
                    @forelse ($attendanceGallery as $item)
                        @if ($item->photo_path)
                            <div class="gallery-card-modern flex-shrink-0">
                                <img src="{{ Storage::url($item->photo_path) }}" class="rounded shadow-sm" style="width: 130px; height: 180px; object-fit: cover;">
                                <div class="mt-2 small fw-bold text-center">{{ $item->check_in_time->format('d M') }}</div>
                            </div>
                        @endif
                    @empty
                        <div class="text-muted small p-4 border rounded text-center w-100 bg-white">Belum ada foto kerja.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- SISI KANAN: ID CARD & LEADERBOARD --}}
        <div class="col-lg-4">
            {{-- MINI ID CARD --}}
            <div class="card card-id-modern mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px;">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center mb-4">
                        <div class="photo-box bg-white p-1 rounded shadow-sm">
                            <img src="{{ Auth::user()->profile_photo_path ? Storage::url(Auth::user()->profile_photo_path) : asset('assets/images/user.png') }}" 
                                 class="rounded" style="width: 60px; height: 75px; object-fit: cover;">
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold mb-0 text-truncate" style="max-width: 150px;">{{ Auth::user()->name }}</h5>
                            <small class="opacity-75 d-block">{{ Auth::user()->division->name ?? 'Staff' }}</small>
                            <span class="badge bg-warning text-dark mt-2" style="font-size: 9px;">PSTORE ID</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div id="dashboard-qrcode-small"></div>
                        <div class="text-end">
                            <small class="d-block opacity-50 small">ID NUMBER</small>
                            <h4 class="fw-bold mb-0" style="letter-spacing: 2px;">{{ substr(Auth::user()->phone ?? '000000', -6) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MINI LEADERBOARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-trophy text-warning me-2"></i>Top Absensi Bulan Ini</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($leaderboard->take(5) as $rank)
                            <li class="list-group-item d-flex align-items-center border-0 px-4 py-3">
                                <span class="fw-bold me-3 text-muted">#{{ $loop->iteration }}</span>
                                <img src="{{ $rank->user->profile_photo_path ? Storage::url($rank->user->profile_photo_path) : asset('assets/images/user.png') }}" 
                                     class="rounded-circle me-3" width="35" height="35" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small fw-bold">{{ Str::limit($rank->user->name, 15) }}</h6>
                                    <small class="text-muted">{{ $rank->total_attendance }} Hari</small>
                                </div>
                                <div class="badge bg-light text-success rounded-pill">{{ $rank->avg_arrival_display }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-light-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-light-success { background-color: rgba(40, 167, 69, 0.1); }
    .status-box i { line-height: 1; }
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .gallery-card-modern:hover img { transform: translateY(-5px); transition: all 0.3s; }
</style>
@endpush