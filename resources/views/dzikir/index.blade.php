@extends('layout.master')

@section('title', 'Zikir Online')

@section('content')
    <div class="content-wrapper zikir-menu-wrapper" style="min-height: calc(100vh - 70px); padding: 20px 15px;">
        
        <div class="container-fluid" style="position: relative; z-index: 1; max-width: 600px; margin: 0 auto; color: white;">
            
            <h5 class="text-uppercase font-weight-bold mb-4 mt-2" style="letter-spacing: 1.5px; font-size: 0.9rem; opacity: 0.9;">
                Zikir
            </h5>

            <div class="row custom-gx mb-4">
                {{-- Semua Zikir --}}
                <div class="col-6 mb-3">
                    <a href="#" class="text-decoration-none text-white">
                        <div class="zikir-card card-dark">
                            <div class="icon-wrapper mb-3">
                                <i class="mdi mdi-dots-horizontal-circle-outline icon-lg"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1">Semua zikir</h6>
                            <p class="text-muted small mb-0">{{ $totalZikir }} dzikir</p>
                        </div>
                    </a>
                </div>

                {{-- Kesukaanku --}}
                <div class="col-6 mb-3">
                    <a href="#" class="text-decoration-none text-white">
                        <div class="zikir-card card-dark">
                            <div class="icon-wrapper mb-3">
                                <i class="mdi mdi-star icon-lg"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1">Kesukaanku</h6>
                            <p class="text-muted small mb-0">{{ $totalFavorites > 0 ? $totalFavorites . ' favorit' : 'Tidak ada favorit' }}</p>
                        </div>
                    </a>
                </div>

                {{-- Zikir Pagi --}}
                <div class="col-6 mb-3">
                    <a href="#" class="text-decoration-none text-white">
                        <div class="zikir-card card-dark">
                            <div class="icon-wrapper mb-3">
                                <i class="mdi mdi-white-balance-sunny icon-lg" style="color: #facc15;"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1">Zikir pagi</h6>
                            <p class="text-muted small mb-0">{{ $zikirPagi }} dzikir</p>
                        </div>
                    </a>
                </div>

                {{-- Zikir Petang --}}
                <div class="col-6 mb-3">
                    <a href="#" class="text-decoration-none text-white">
                        <div class="zikir-card card-dark">
                            <div class="icon-wrapper mb-3">
                                <i class="mdi mdi-moon-waning-crescent icon-lg" style="color: #38bdf8;"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1">Zikir petang</h6>
                            <p class="text-muted small mb-0">{{ $zikirPetang }} dzikir</p>
                        </div>
                    </a>
                </div>
            </div>

            <h5 class="font-weight-bold mb-3 mt-4" style="font-size: 1rem; opacity: 0.95;">
                Aktivitas Anda
            </h5>

            <div class="row custom-gx">
                {{-- Aktivitas Terakhir --}}
                <div class="col-6 mb-3">
                    <a href="#" class="text-decoration-none text-white">
                        <div class="zikir-card card-dark">
                            <div class="icon-wrapper mb-3">
                                <div class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="mdi mdi-check" style="font-size: 1.2rem; font-weight: bold;"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1" style="line-height: 1.3;">
                                {{ $recentActivity ? $recentActivity->zikir->title : 'Belum ada' }}
                            </h6>
                            <p class="text-muted small mb-0">
                                {{ $recentActivity ? $recentActivity->last_read_at->diffForHumans() : 'Belum ada aktivitas' }}
                            </p>
                        </div>
                    </a>
                </div>

                {{-- Koleksi --}}
                <div class="col-6 mb-3">
                    <a href="#" class="text-decoration-none text-white">
                        <div class="zikir-card card-dark">
                            <div class="icon-wrapper mb-3 text-center" style="position: relative;">
                                <i class="mdi mdi-book-open-page-variant icon-lg" style="color: #4ade80;"></i>
                                {{-- Sparkles effect --}}
                                <i class="mdi mdi-star-four-points" style="position: absolute; top: -5px; right: 0; font-size: 10px; color: #facc15;"></i>
                                <i class="mdi mdi-star-four-points" style="position: absolute; bottom: 0; left: -5px; font-size: 8px; color: #facc15;"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1">Koleksi</h6>
                            <p class="text-muted small mb-0">{{ $totalCollection }} / {{ $totalZikir > 0 ? $totalZikir : 454 }}</p>
                        </div>
                    </a>
                </div>
            </div>
            
        </div>
    </div>

    @push('styles')
        <style>
            .zikir-menu-wrapper {
                position: relative;
                overflow: hidden;
            }
            .custom-gx {
                margin-left: -8px;
                margin-right: -8px;
            }
            .custom-gx > [class*="col-"] {
                padding-left: 8px;
                padding-right: 8px;
            }
            .zikir-card {
                border-radius: 20px;
                padding: 20px;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                min-height: 160px;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .zikir-card:active {
                transform: scale(0.97);
            }
            .card-dark {
                background-color: #2a3038;
                border: 1px solid rgba(255, 255, 255, 0.05);
            }
            .icon-lg {
                font-size: 2rem;
            }
            .icon-wrapper {
                margin-top: auto;
                margin-bottom: auto !important;
            }
            .text-muted {
                color: #94a3b8 !important;
            }
        </style>
    @endpush
@endsection