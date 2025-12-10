@extends('layout.master')

@section('title')
    Top Absen - {{ $branch->name }}
@endsection

@section('heading')
    <div class="d-flex align-items-center">
        <a href="{{ route('branch-leaderboard.index') }}" class="btn btn-light btn-icon rounded-circle me-3 shadow-sm">
            <i class="mdi mdi-arrow-left text-dark"></i>
        </a>
        <div>
            <h3 class="fw-bold mb-0">Leaderboard: {{ $branch->name }}</h3>
            <p class="text-muted small mb-0">Periode: {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
        </div>
    </div>
@endsection

@section('content')

{{-- ======================== --}}
{{-- 1. PODIUM SECTION (TOP 3) --}}
{{-- ======================== --}}
<div class="row mb-5 animate-enter">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%); overflow: hidden;">
            <div class="card-body pt-5 pb-5">
                
                @if($top3->isEmpty())
                    <div class="text-center py-5">
                        <i class="mdi mdi-trophy-broken text-muted display-1 opacity-25"></i>
                        <p class="mt-3 text-muted">Belum ada data absensi terverifikasi bulan ini.</p>
                    </div>
                @else
                    <div class="podium-container">
                        {{-- RANK 2 (Silver) --}}
                        <div class="podium-col order-1">
                            @if(isset($top3[1]))
                                <div class="podium-user rank-2">
                                    <div class="avatar-ring silver">
                                        <div class="rank-badge">2</div>
                                        @if($top3[1]->user->profile_photo_path)
                                            <img src="{{ Storage::url($top3[1]->user->profile_photo_path) }}" class="user-img">
                                        @else
                                            <div class="user-placeholder">{{ substr($top3[1]->user->name, 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="user-info mt-3 text-center">
                                        <h6 class="fw-bold text-dark mb-0">{{ $top3[1]->user->name }}</h6>
                                        <small class="text-muted d-block">{{ $top3[1]->user->division->name ?? '-' }}</small>
                                        <div class="stat-badge bg-secondary bg-opacity-10 text-secondary mt-2">
                                            <i class="mdi mdi-check-decagram"></i> {{ $top3[1]->total_attendance }} Verified
                                        </div>
                                    </div>
                                    <div class="podium-block silver-block"></div>
                                </div>
                            @endif
                        </div>

                        {{-- RANK 1 (Gold) --}}
                        <div class="podium-col order-2">
                            @if(isset($top3[0]))
                                <div class="podium-user rank-1">
                                    <div class="crown-icon animate-bounce"><i class="mdi mdi-crown text-warning"></i></div>
                                    <div class="avatar-ring gold">
                                        <div class="rank-badge">1</div>
                                        @if($top3[0]->user->profile_photo_path)
                                            <img src="{{ Storage::url($top3[0]->user->profile_photo_path) }}" class="user-img">
                                        @else
                                            <div class="user-placeholder">{{ substr($top3[0]->user->name, 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="user-info mt-3 text-center">
                                        <h5 class="fw-bold text-dark mb-0">{{ $top3[0]->user->name }}</h5>
                                        <small class="text-muted d-block fw-semibold">{{ $top3[0]->user->division->name ?? '-' }}</small>
                                        <div class="stat-badge bg-warning text-dark mt-2 shadow-sm">
                                            <i class="mdi mdi-trophy"></i> {{ $top3[0]->total_attendance }} Verified
                                        </div>
                                    </div>
                                    <div class="podium-block gold-block">
                                        <span class="winner-label">CHAMPION</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- RANK 3 (Bronze) --}}
                        <div class="podium-col order-3">
                            @if(isset($top3[2]))
                                <div class="podium-user rank-3">
                                    <div class="avatar-ring bronze">
                                        <div class="rank-badge">3</div>
                                        @if($top3[2]->user->profile_photo_path)
                                            <img src="{{ Storage::url($top3[2]->user->profile_photo_path) }}" class="user-img">
                                        @else
                                            <div class="user-placeholder">{{ substr($top3[2]->user->name, 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="user-info mt-3 text-center">
                                        <h6 class="fw-bold text-dark mb-0">{{ $top3[2]->user->name }}</h6>
                                        <small class="text-muted d-block">{{ $top3[2]->user->division->name ?? '-' }}</small>
                                        <div class="stat-badge bg-warning bg-opacity-10 text-warning mt-2 border-warning" style="color: #A0522D !important; border-color: #A0522D !important;">
                                            <i class="mdi mdi-check-decagram"></i> {{ $top3[2]->total_attendance }} Verified
                                        </div>
                                    </div>
                                    <div class="podium-block bronze-block"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ======================== --}}
{{-- 2. LIST SECTION (RANK 4+) --}}
{{-- ======================== --}}
@if($others->count() > 0)
<div class="row animate-enter" style="animation-delay: 0.2s;">
    <div class="col-12">
        <h5 class="fw-bold mb-3 ms-2"><i class="mdi mdi-format-list-numbered me-2"></i>Peringkat Selanjutnya</h5>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="10%">Rank</th>
                                <th>Karyawan</th>
                                <th class="text-center">Total Kehadiran</th>
                                <th class="text-center">Rata-rata Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($others as $index => $row)
                            <tr>
                                <td class="ps-4">
                                    <div class="rank-circle text-muted fw-bold">#{{ $index + 4 }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($row->user->profile_photo_path)
                                            <img src="{{ Storage::url($row->user->profile_photo_path) }}" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-secondary me-3 fw-bold" style="width: 40px; height: 40px;">
                                                {{ substr($row->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 text-dark fw-semibold">{{ $row->user->name }}</h6>
                                            <small class="text-muted">{{ $row->user->division->name ?? 'No Division' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                        {{ $row->total_attendance }} Verified
                                    </span>
                                </td>
                                <td class="text-center text-muted font-monospace">
                                    {{ \Carbon\Carbon::parse($row->avg_arrival_time)->format('H:i') }}
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

@endsection

@push('styles')
<style>
    /* ANIMATIONS */
    .animate-enter { animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-bounce { animation: bounce 2s infinite; }
    @keyframes bounce { 0%, 20%, 50%, 80%, 100% {transform: translateX(-50%) translateY(0) rotate(-10deg);} 40% {transform: translateX(-50%) translateY(-10px) rotate(-10deg);} 60% {transform: translateX(-50%) translateY(-5px) rotate(-10deg);} }

    /* PODIUM LAYOUT */
    .podium-container { display: flex; align-items: flex-end; justify-content: center; gap: 15px; padding-bottom: 20px; }
    .podium-col { width: 30%; max-width: 250px; display: flex; justify-content: center; }
    .podium-user { width: 100%; display: flex; flex-direction: column; align-items: center; position: relative; }

    /* AVATAR RINGS */
    .avatar-ring { position: relative; border-radius: 50%; padding: 5px; display: inline-block; box-shadow: 0 10px 20px rgba(0,0,0,0.15); transition: transform 0.3s; }
    .podium-user:hover .avatar-ring { transform: scale(1.05); }
    .user-img, .user-placeholder { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 4px solid white; }
    .user-placeholder { background: #eee; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: #888; }

    /* SPECIFIC COLORS */
    .gold { background: linear-gradient(135deg, #FFD700, #FDB931); }
    .silver { background: linear-gradient(135deg, #E0E0E0, #BDBDBD); }
    .bronze { background: linear-gradient(135deg, #CD7F32, #A0522D); }

    /* SIZES */
    .rank-1 .avatar-ring { width: 110px; height: 110px; padding: 6px; }
    .rank-1 .user-img, .rank-1 .user-placeholder { width: 98px; height: 98px; }
    .rank-2 .avatar-ring, .rank-3 .avatar-ring { width: 90px; height: 90px; }

    /* BADGES & ICONS */
    .rank-badge { position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 28px; height: 28px; background: #222; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border: 2px solid white; z-index: 5; }
    .crown-icon { position: absolute; top: -35px; left: 50%; transform: translateX(-50%) rotate(-10deg); font-size: 32px; z-index: 10; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); }
    .stat-badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }

    /* PODIUM BLOCKS */
    .podium-block { width: 100%; border-radius: 12px 12px 0 0; margin-top: 15px; position: relative; }
    .gold-block { height: 140px; background: linear-gradient(to bottom, #FFD700, #ffec8b); box-shadow: inset 0 -20px 40px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; }
    .silver-block { height: 100px; background: linear-gradient(to bottom, #E0E0E0, #f5f5f5); }
    .bronze-block { height: 70px; background: linear-gradient(to bottom, #CD7F32, #eabc94); }
    
    .winner-label { color: #8a6d00; font-weight: 900; letter-spacing: 2px; font-size: 18px; opacity: 0.5; transform: rotate(-90deg); position: absolute; bottom: 40px; }

    /* TABLE */
    .rank-circle { width: 30px; height: 30px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; }

    @media (max-width: 768px) {
        .podium-container { gap: 5px; }
        .user-img, .user-placeholder { width: 60px; height: 60px; }
        .rank-1 .user-img { width: 70px; height: 70px; }
        .rank-1 .avatar-ring { width: 82px; height: 82px; }
        .avatar-ring { width: 72px; height: 72px; }
    }
</style>
@endpush