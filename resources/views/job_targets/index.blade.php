@extends('layout.master')

@section('title', 'Target & Pencapaian')
@section('heading', 'Dashboard Target Pstore')

@section('content')

{{-- HEADER & BUTTON --}}
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Manajemen Target & Prestasi</h3>
        <p class="text-muted mb-0">Monitor performa Cabang {{ auth()->user()->branch->name ?? 'Pusat' }} dan Individu.</p>
    </div>
    <div>
        <a href="{{ route('job-targets.create') }}" class="btn btn-primary btn-lg shadow-sm rounded-4 px-4 fw-bold">
            <i class="mdi mdi-plus-circle-outline me-1"></i> Buat Target / Pencapaian
        </a>
    </div>
</div>

{{-- SECTION 1: CABANG / TIM (Target + Pencapaian) --}}
<div class="card card-rounded shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
            <i class="mdi mdi-office-building text-primary mdi-24px"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark">🏢 Target & Pencapaian Cabang</h5>
            <small class="text-muted">Fokus tim dan riwayat keberhasilan cabang ini</small>
        </div>
    </div>
    <div class="card-body p-4">
        {{-- Kita kirim data gabungan $teamData --}}
        @include('job_targets.partials.period_tabs', ['idPrefix' => 'branch', 'dataCollection' => $teamData])
    </div>
</div>

{{-- SECTION 2: PRIBADI (Target + Pencapaian) --}}
<div class="card card-rounded shadow-sm border-0 mb-5">
    <div class="card-header bg-gradient-info text-white border-bottom py-3 d-flex align-items-center">
        <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3">
            <i class="mdi mdi-account-star text-white mdi-24px"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-white">👤 Target & Pencapaian Pribadi</h5>
            <small class="text-white opacity-75">Daftar tanggung jawab dan prestasi Anda</small>
        </div>
    </div>
    <div class="card-body p-4">
        {{-- Kita kirim data gabungan $personalData --}}
        @include('job_targets.partials.period_tabs', ['idPrefix' => 'personal', 'dataCollection' => $personalData])
    </div>
</div>

{{-- MODAL UPDATE --}}
@include('job_targets.partials.modal_update')

<style>
    .card-rounded { border-radius: 16px; overflow: hidden; }
    .bg-gradient-info { background: linear-gradient(45deg, #198ae3, #4b49ac); }
    
    /* STYLE MEWAH BINTANG */
    .star-badge-3 { background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); color: #000; box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); border: 1px solid #d4af37; }
    .star-badge-2 { background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%); color: #333; border: 1px solid #b0b0b0; }
    .star-badge-1 { background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
    .star-animation { animation: glow 2s infinite; }
    @keyframes glow { 0% { box-shadow: 0 0 5px #FFD700; } 50% { box-shadow: 0 0 15px #FFD700; } 100% { box-shadow: 0 0 5px #FFD700; } }
</style>

@endsection