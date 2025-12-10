@extends('layout.master')

@section('title')
    Top Absensi Cabang
@endsection

@section('heading')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h3 class="fw-bold mb-0">Leaderboard Cabang</h3>
            <p class="text-muted small mb-0">Peringkat kerajinan karyawan per cabang</p>
        </div>
        <span class="badge bg-light text-dark border">
            <i class="mdi mdi-calendar-month me-1"></i> {{ date('F Y') }}
        </span>
    </div>
@endsection

@section('content')
<div class="row g-4">
    @forelse($branches as $branch)
    <div class="col-xl-4 col-md-6 grid-margin stretch-card">
        <div class="card card-action hover-float h-100 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Header Kartu --}}
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <div class="icon-rounded bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3" style="width: 50px; height: 50px;">
                        <i class="mdi mdi-office-building fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ Str::limit($branch->name, 20) }}</h5>
                        <small class="text-muted">
                            <i class="mdi mdi-account-group me-1"></i>{{ $branch->total_employees }} Karyawan
                        </small>
                    </div>
                </div>
            </div>

            {{-- Body Kartu: Mini Leaderboard Top 3 --}}
            <div class="card-body px-4 py-3">
                <div class="mini-leaderboard-container mt-2 mb-2">
                    @if($branch->top_employees->isEmpty())
                        <div class="text-center py-4 bg-light rounded-3">
                            <i class="mdi mdi-trophy-broken text-muted fs-3 opacity-50"></i>
                            <p class="text-muted small mb-0 mt-2">Belum ada data</p>
                        </div>
                    @else
                        <div class="d-flex justify-content-center align-items-end gap-3" style="min-height: 120px;">
                            
                            {{-- RANK 2 --}}
                            <div class="mini-podium rank-2 text-center" style="width: 30%;">
                                @if(isset($branch->top_employees[1]))
                                    <div class="avatar-ring silver mb-1 mx-auto">
                                        @if($branch->top_employees[1]->user->profile_photo_path)
                                            <img src="{{ Storage::url($branch->top_employees[1]->user->profile_photo_path) }}" class="mini-user-img">
                                        @else
                                            <div class="mini-user-placeholder">{{ substr($branch->top_employees[1]->user->name, 0, 1) }}</div>
                                        @endif
                                        <span class="mini-badge">2</span>
                                    </div>
                                    <div class="mini-name text-truncate fw-bold text-dark" style="font-size: 0.75rem;">
                                        {{ explode(' ', $branch->top_employees[1]->user->name)[0] }}
                                    </div>
                                    <div class="mini-score text-muted" style="font-size: 0.65rem;">
                                        {{ $branch->top_employees[1]->total_attendance }} H
                                    </div>
                                @endif
                            </div>

                            {{-- RANK 1 --}}
                            <div class="mini-podium rank-1 text-center" style="width: 35%; margin-bottom: 10px;">
                                @if(isset($branch->top_employees[0]))
                                    <div class="position-relative">
                                        <i class="mdi mdi-crown text-warning position-absolute start-50 translate-middle-x" style="top: -18px; font-size: 1.2rem; transform: translateX(-50%) rotate(-10deg);"></i>
                                        <div class="avatar-ring gold mb-1 mx-auto">
                                            @if($branch->top_employees[0]->user->profile_photo_path)
                                                <img src="{{ Storage::url($branch->top_employees[0]->user->profile_photo_path) }}" class="mini-user-img">
                                            @else
                                                <div class="mini-user-placeholder">{{ substr($branch->top_employees[0]->user->name, 0, 1) }}</div>
                                            @endif
                                            <span class="mini-badge">1</span>
                                        </div>
                                    </div>
                                    <div class="mini-name text-truncate fw-bold text-dark" style="font-size: 0.8rem;">
                                        {{ explode(' ', $branch->top_employees[0]->user->name)[0] }}
                                    </div>
                                    <div class="mini-score text-warning fw-bold bg-warning bg-opacity-10 px-2 rounded-pill d-inline-block" style="font-size: 0.65rem;">
                                        {{ $branch->top_employees[0]->total_attendance }} H
                                    </div>
                                @endif
                            </div>

                            {{-- RANK 3 --}}
                            <div class="mini-podium rank-3 text-center" style="width: 30%;">
                                @if(isset($branch->top_employees[2]))
                                    <div class="avatar-ring bronze mb-1 mx-auto">
                                        @if($branch->top_employees[2]->user->profile_photo_path)
                                            <img src="{{ Storage::url($branch->top_employees[2]->user->profile_photo_path) }}" class="mini-user-img">
                                        @else
                                            <div class="mini-user-placeholder">{{ substr($branch->top_employees[2]->user->name, 0, 1) }}</div>
                                        @endif
                                        <span class="mini-badge">3</span>
                                    </div>
                                    <div class="mini-name text-truncate fw-bold text-dark" style="font-size: 0.75rem;">
                                        {{ explode(' ', $branch->top_employees[2]->user->name)[0] }}
                                    </div>
                                    <div class="mini-score text-muted" style="font-size: 0.65rem;">
                                        {{ $branch->top_employees[2]->total_attendance }} H
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Footer Kartu --}}
            <div class="card-footer bg-white border-top border-light p-3">
                <a href="{{ route('branch-leaderboard.show', $branch->id) }}" class="btn btn-primary btn-sm w-100 shadow-sm py-2 rounded-3 fw-bold">
                    <i class="mdi mdi-trophy-outline me-2"></i>Lihat Detail Leaderboard
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-warning text-center">
            <i class="mdi mdi-alert-circle-outline me-2"></i> Tidak ada cabang yang tersedia.
        </div>
    </div>
    @endforelse
</div>
@endsection

@push('styles')
<style>
    .hover-float { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .hover-float:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    
    .icon-rounded { display: flex; align-items: center; justify-content: center; }

    /* Mini Podium Styles */
    .mini-user-img, .mini-user-placeholder {
        width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid white;
    }
    .rank-1 .mini-user-img, .rank-1 .mini-user-placeholder { width: 55px; height: 55px; } /* Juara 1 lebih besar */

    .mini-user-placeholder {
        background: #f0f0f0; display: flex; align-items: center; justify-content: center; 
        font-weight: bold; color: #888; font-size: 14px;
    }

    .avatar-ring { position: relative; display: inline-block; padding: 2px; border-radius: 50%; }
    .gold { background: linear-gradient(135deg, #FFD700, #FDB931); }
    .silver { background: linear-gradient(135deg, #E0E0E0, #BDBDBD); }
    .bronze { background: linear-gradient(135deg, #CD7F32, #A0522D); }

    .mini-badge {
        position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%);
        width: 16px; height: 16px; background: #333; color: white;
        border-radius: 50%; font-size: 9px; display: flex; align-items: center; justify-content: center;
        border: 1px solid white; font-weight: bold;
    }
</style>
@endpush