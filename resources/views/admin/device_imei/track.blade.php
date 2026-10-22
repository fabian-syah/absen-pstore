@extends('layout.master')

@section('title', 'Lacak Riwayat Spesifikasi IMEI ' . $cleanImei)

@push('styles')
<style>
    .track-card {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
    }

    .track-header {
        border-bottom: 1px solid #f1f5f9;
        background: transparent;
        padding: 1.2rem 1.5rem;
    }

    .track-timeline {
        position: relative;
        padding-left: 20px;
    }

    .track-node {
        position: relative;
        padding-left: 36px;
        padding-bottom: 28px;
    }

    .track-node:last-child {
        padding-bottom: 0;
    }

    .track-node::before {
        content: "";
        position: absolute;
        left: 11px;
        top: 12px;
        bottom: 0;
        width: 2px;
        background-color: #cbd5e1;
    }

    .track-node:last-child::before {
        display: none;
    }

    .track-dot {
        position: absolute;
        left: 0;
        top: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #0d6efd;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 2px #93c5fd;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 11px;
    }

    .track-dot.initial {
        background-color: #10b981;
        box-shadow: 0 0 0 2px #a7f3d0;
    }

    .track-dot.corrected {
        background-color: #f59e0b;
        box-shadow: 0 0 0 2px #fde68a;
    }

    .track-box-diff {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
    }

    .pill-before {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
    }

    .pill-after {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
    }

    @media (prefers-color-scheme: dark) {
        .track-card {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        .track-header {
            border-bottom-color: #334155;
        }

        .track-box-diff {
            background-color: #0f172a;
            border-color: #334155;
        }

        .track-node::before {
            background-color: #475569;
        }

        .track-dot {
            border-color: #1e293b;
        }
    }

    body.dark-mode .track-card,
    body.theme-dark .track-card,
    [data-theme="dark"] .track-card,
    [data-bs-theme="dark"] .track-card {
        background-color: #1e293b;
        border-color: #334155;
        color: #f8fafc;
    }

    body.dark-mode .track-box-diff,
    body.theme-dark .track-box-diff,
    [data-theme="dark"] .track-box-diff,
    [data-bs-theme="dark"] .track-box-diff {
        background-color: #0f172a;
        border-color: #334155;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    {{-- Breadcrumb & Navigasi Atas --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <a href="{{ route('device-corrections.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 mb-2">
                        <i class="mdi mdi-arrow-left mr-1"></i>Kembali ke Pencarian
                    </a>
                    <h4 class="font-weight-bold mb-0 text-dark" style="letter-spacing: -0.3px;">
                        <i class="mdi mdi-radar text-primary mr-1"></i>Lacak Riwayat Spesifikasi IMEI
                    </h4>
                    <p class="text-muted small mb-0">
                        Nomor IMEI: <code>{{ $cleanImei }}</code>
                    </p>
                </div>

                <div>
                    <a href="{{ route('device-corrections.index', ['imei' => $cleanImei]) }}" class="btn btn-primary btn-sm rounded-3 fw-semibold">
                        <i class="mdi mdi-pencil mr-1"></i>Koreksi / Rubah Tipe
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Unit Utama Terkini --}}
    @if($device)
        <div class="card track-card shadow-sm mb-4">
            <div class="track-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-dark small text-uppercase">
                    <i class="mdi mdi-cellphone-check text-success mr-1"></i>Spesifikasi Terkini Unit
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    Aktif Terverifikasi
                </span>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-center">
                    <div class="col-12 col-md-8">
                        <span class="badge bg-primary text-white mb-2">{{ $device->brand }}</span>
                        <h3 class="font-weight-bold text-dark mb-1">
                            {{ $device->type }} <span class="text-primary font-weight-normal">({{ $device->storage }})</span>
                        </h3>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-2">
                            <div><i class="mdi mdi-barcode mr-1"></i>IMEI: <code>{{ $device->imei }}</code></div>
                            @if($device->color)
                                <div><i class="mdi mdi-palette-outline mr-1"></i>Warna: <strong>{{ $device->color }}</strong></div>
                            @endif
                            @if($device->last_cs_name)
                                <div><i class="mdi mdi-account-alert mr-1"></i>CS Terakhir: <strong>{{ $device->last_cs_name }}</strong></div>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mt-3 mt-md-0 text-md-end">
                        <div class="p-3 rounded-3 d-inline-block text-start" style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.76rem;">
                            <div class="text-muted mb-1">Pembaruan Terakhir:</div>
                            <strong class="text-dark d-block">{{ $device->updated_at ? $device->updated_at->format('d M Y, H:i') : '-' }}</strong>
                            <small class="text-muted">Oleh: {{ $device->updater->name ?? 'Super Admin' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Visual Timeline Perjalanan Spesifikasi IMEI --}}
    <div class="card track-card shadow-sm">
        <div class="track-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-dark small text-uppercase">
                <i class="mdi mdi-timeline-text-outline text-primary mr-1"></i>Timeline Riwayat & Koreksi Spesifikasi
            </span>
            <span class="text-muted small">Total {{ $histories->count() }} Catatan Perubahan</span>
        </div>

        <div class="card-body p-3 p-md-4">
            @if($histories->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="mdi mdi-timeline-clock fs-1 d-block mb-2 text-muted"></i>
                    <h5 class="font-weight-bold text-dark mb-1">Belum Ada Riwayat Perubahan</h5>
                    <p class="small text-muted mb-3">Unit ini belum memiliki riwayat koreksi tipe HP atau nama CS.</p>
                    <a href="{{ route('device-corrections.index', ['imei' => $cleanImei]) }}" class="btn btn-primary btn-sm rounded-3">
                        Lakukan Koreksi Sekarang
                    </a>
                </div>
            @else
                <div class="track-timeline">
                    @foreach($histories as $index => $history)
                        <div class="track-node">
                            <div class="track-dot {{ $history->action_type == 'input_awal' ? 'initial' : 'corrected' }}">
                                <i class="mdi {{ $history->action_type == 'input_awal' ? 'mdi-plus' : 'mdi-sync' }}"></i>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        {{ $history->action_type == 'input_awal' ? 'Input Pertama Kali (Registrasi Unit)' : 'Koreksi Tipe HP oleh Super Admin' }}
                                    </h6>
                                    <small class="text-muted">
                                        {{ $history->created_at ? $history->created_at->format('d F Y, H:i') : '-' }}
                                        ({{ $history->created_at ? $history->created_at->diffForHumans() : '' }})
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-light text-dark border">
                                        <i class="mdi mdi-shield-crown-outline text-warning mr-1"></i>Oleh: {{ $history->admin->name ?? 'Super Admin' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Box Perbandingan Data Sebelum vs Sesudah --}}
                            <div class="track-box-diff mb-2">
                                <div class="row g-2 align-items-center">
                                    @if($history->old_type)
                                        <div class="col-12 col-md-5">
                                            <span class="text-muted small d-block mb-1">Data Sebelumnya (Salah Input):</span>
                                            <div class="pill-before d-inline-block">
                                                <i class="mdi mdi-close-circle-outline mr-1"></i>
                                                {{ $history->old_type }} ({{ $history->old_storage ?? '-' }})
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-2 text-center text-muted">
                                            <i class="mdi mdi-arrow-right-bold fs-5 text-primary"></i>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <span class="text-muted small d-block mb-1">Data Dikoreksi Menjadi (Benar):</span>
                                            <div class="pill-after d-inline-block">
                                                <i class="mdi mdi-check-circle-outline mr-1"></i>
                                                {{ $history->new_type }} ({{ $history->new_storage }})
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <span class="text-muted small d-block mb-1">Data Terdaftar:</span>
                                            <div class="pill-after d-inline-block">
                                                <i class="mdi mdi-check-circle-outline mr-1"></i>
                                                {{ $history->new_brand }} {{ $history->new_type }} ({{ $history->new_storage }})
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Informasi CS Yang Salah Input --}}
                                @if($history->cs_name_mistake)
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="d-flex align-items-center text-danger small font-weight-bold">
                                            <i class="mdi mdi-account-alert mr-1 fs-6"></i>
                                            Nama CS yang Salah Input: <span class="text-dark ms-1">{{ $history->cs_name_mistake }}</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Catatan Alasan Koreksi --}}
                                @if($history->correction_reason)
                                    <div class="mt-2 text-muted small font-italic">
                                        <i class="mdi mdi-comment-text-outline mr-1"></i>"{{ $history->correction_reason }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
