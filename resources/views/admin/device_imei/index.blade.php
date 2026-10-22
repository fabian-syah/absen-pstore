@extends('layout.master')

@section('title', 'Rubah Tipe HP Berdasarkan IMEI')

@push('styles')
<style>
    /* Styling Adaptif Light dan Dark Theme */
    .imei-card {
        border-radius: 14px;
        transition: all 0.2s ease-in-out;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
    }

    .imei-card-header {
        border-bottom: 1px solid #f1f5f9;
        background: transparent;
        padding: 1.1rem 1.4rem;
    }

    .imei-badge-super {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 0.72rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
    }

    .imei-history-pill-old {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-weight: 600;
        font-size: 0.78rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-block;
    }

    .imei-history-pill-new {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        font-size: 0.78rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-block;
    }

    .imei-quick-chip {
        cursor: pointer;
        padding: 0.3rem 0.65rem;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        color: #334155;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        user-select: none;
    }

    .imei-quick-chip:hover, .imei-quick-chip:focus {
        background-color: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
        outline: none;
    }

    .imei-quick-chip.active {
        background-color: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
    }

    .imei-timeline-item {
        position: relative;
        padding-left: 28px;
        padding-bottom: 20px;
    }

    .imei-timeline-item:last-child {
        padding-bottom: 0;
    }

    .imei-timeline-item::before {
        content: "";
        position: absolute;
        left: 8px;
        top: 8px;
        bottom: 0;
        width: 2px;
        background-color: #cbd5e1;
    }

    .imei-timeline-item:last-child::before {
        display: none;
    }

    .imei-timeline-dot {
        position: absolute;
        left: 0;
        top: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: #0d6efd;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 2px #93c5fd;
    }

    .imei-timeline-dot.initial {
        background-color: #10b981;
        box-shadow: 0 0 0 2px #a7f3d0;
    }

    /* Penyesuaian Dark Theme (Otomatis & Class) */
    @media (prefers-color-scheme: dark) {
        .imei-card {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        .imei-card-header {
            border-bottom-color: #334155;
        }

        .imei-card input.form-control,
        .imei-card select.form-select,
        .imei-card textarea.form-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }

        .imei-card input.form-control:focus,
        .imei-card select.form-select:focus,
        .imei-card textarea.form-control:focus {
            background-color: #0f172a;
            border-color: #38bdf8;
            color: #f8fafc;
        }

        .imei-quick-chip {
            background-color: #0f172a;
            color: #cbd5e1;
            border-color: #334155;
        }

        .imei-quick-chip:hover, .imei-quick-chip:focus {
            background-color: #38bdf8;
            color: #0f172a;
            border-color: #38bdf8;
        }

        .imei-timeline-item::before {
            background-color: #475569;
        }

        .imei-timeline-dot {
            border-color: #1e293b;
            box-shadow: 0 0 0 2px #3b82f6;
        }

        .imei-timeline-dot.initial {
            box-shadow: 0 0 0 2px #059669;
        }
    }

    /* Fallback jika ada class dark di body */
    body.dark-mode .imei-card,
    body.theme-dark .imei-card,
    [data-theme="dark"] .imei-card,
    [data-bs-theme="dark"] .imei-card {
        background-color: #1e293b;
        border-color: #334155;
        color: #f8fafc;
    }

    body.dark-mode .imei-quick-chip,
    body.theme-dark .imei-quick-chip,
    [data-theme="dark"] .imei-quick-chip,
    [data-bs-theme="dark"] .imei-quick-chip {
        background-color: #0f172a;
        color: #cbd5e1;
        border-color: #334155;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    {{-- Header Halaman --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="font-weight-bold mb-0 text-dark" style="letter-spacing: -0.3px;">
                            <i class="mdi mdi-cellphone-cog text-primary mr-1"></i>Rubah Tipe & Spesifikasi IMEI
                        </h4>
                        <span class="imei-badge-super">Super Admin</span>
                    </div>
                    <p class="text-muted small mb-0">
                        Pencarian nomor IMEI, koreksi tipe HP, storage, dan pencatatan nama CS yang salah input.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('device-corrections.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                        <i class="mdi mdi-refresh mr-1"></i>Reset Pencarian
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" style="border-radius: 10px;">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle-outline mr-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" style="border-radius: 10px;">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-alert-circle-outline mr-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" style="border-radius: 10px;">
            <div class="d-flex align-items-center mb-1">
                <i class="mdi mdi-alert mr-2"></i>
                <strong>Periksa kembali formulir koreksi:</strong>
            </div>
            <ul class="mb-0 small pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Baris Utama: Form Search IMEI & Form Koreksi --}}
    <div class="row">
        {{-- Kolom Kiri: Search IMEI & Form Koreksi Spesifikasi --}}
        <div class="col-12 col-lg-7 mb-4">
            {{-- Card 1: Search IMEI --}}
            <div class="card imei-card shadow-sm mb-3">
                <div class="imei-card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark small text-uppercase">
                        <i class="mdi mdi-magnify text-primary mr-1"></i>Pencarian IMEI
                    </span>
                    <span class="text-muted small">Input 8 sampai 20 digit IMEI</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <form action="{{ route('device-corrections.index') }}" method="GET" class="mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-right-0 text-muted">
                                <i class="mdi mdi-barcode-scan"></i>
                            </span>
                            <input type="text" 
                                   name="imei" 
                                   id="searchImeiInput"
                                   class="form-control form-control-lg border-left-0" 
                                   placeholder="Contoh: 353272713197612" 
                                   value="{{ request('imei', $searchImei) }}" 
                                   required 
                                   autocomplete="off"
                                   aria-label="Nomor IMEI">
                            <button class="btn btn-primary px-4 fw-semibold" type="submit" style="min-height: 44px;">
                                <i class="mdi mdi-magnify mr-1"></i>Cari IMEI
                            </button>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                        <small class="text-muted mr-2">Contoh cepat:</small>
                        <button type="button" class="imei-quick-chip" onclick="fillSearch('353272713197612')">
                            353272713197612
                        </button>
                    </div>
                </div>
            </div>

            {{-- Card 2: Form Rubah Tipe & Spesifikasi --}}
            <div class="card imei-card shadow-sm">
                <div class="imei-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="font-weight-bold text-dark small text-uppercase">
                            <i class="mdi mdi-square-edit-outline text-primary mr-1"></i>Formulir Koreksi Tipe & Spesifikasi
                        </span>
                    </div>
                    @if($foundDevice)
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-weight-bold">
                            Unit Terdaftar
                        </span>
                    @elseif($searched)
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-weight-bold">
                            Unit Baru
                        </span>
                    @endif
                </div>

                <div class="card-body p-3 p-md-4">
                    {{-- Banner Status Hasil Pencarian --}}
                    @if($searched && $foundDevice)
                        <div class="p-3 mb-3 rounded-3" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-success font-weight-bold small text-uppercase mb-1">
                                        <i class="mdi mdi-check-circle mr-1"></i>Data Saat Ini Terdaftar
                                    </div>
                                    <h5 class="font-weight-bold text-dark mb-1">
                                        {{ $foundDevice->brand }} {{ $foundDevice->type }} ({{ $foundDevice->storage }})
                                    </h5>
                                    <div class="small text-muted">
                                        <span>IMEI: <code>{{ $foundDevice->imei }}</code></span>
                                        @if($foundDevice->color)
                                            <span class="mx-1">&bull;</span>
                                            <span>Warna: {{ $foundDevice->color }}</span>
                                        @endif
                                        @if($foundDevice->last_cs_name)
                                            <span class="mx-1">&bull;</span>
                                            <span>CS Terakhir: <strong>{{ $foundDevice->last_cs_name }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('device-corrections.track', $foundDevice->imei) }}" class="btn btn-sm btn-outline-success rounded-3">
                                    <i class="mdi mdi-history mr-1"></i>Lihat Riwayat
                                </a>
                            </div>
                        </div>
                    @elseif($searched && !$foundDevice)
                        <div class="p-3 mb-3 rounded-3" style="background-color: #eff6ff; border: 1px solid #bfdbfe;">
                            <div class="text-primary font-weight-bold small text-uppercase mb-1">
                                <i class="mdi mdi-information-outline mr-1"></i>IMEI Belum Ada di Master
                            </div>
                            <p class="text-muted small mb-0">
                                IMEI <strong>{{ $cleanSearchImei }}</strong> belum tercatat di sistem. Anda dapat langsung mendaftarkannya dan mencatat nama CS yang keliru saat proses input awal.
                            </p>
                        </div>
                    @endif

                    <form action="{{ route('device-corrections.store-or-update') }}" method="POST">
                        @csrf

                        {{-- Input IMEI --}}
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small" for="formImei">
                                Nomor IMEI <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="imei" 
                                   id="formImei" 
                                   class="form-control" 
                                   placeholder="Masukkan 8-20 digit IMEI"
                                   value="{{ old('imei', $foundDevice->imei ?? $cleanSearchImei) }}" 
                                   required
                                   {{ $foundDevice ? 'readonly' : '' }}>
                            <small class="text-muted">Pastikan nomor IMEI sudah sesuai dengan unit fisik perangkat.</small>
                        </div>

                        {{-- Jika unit belum ada dan ingin mencatat data lama yang salah --}}
                        @if(!$foundDevice)
                            <div class="p-3 mb-3 rounded-3" style="background-color: #fff7ed; border: 1px dashed #fdba74;">
                                <label class="form-label font-weight-bold text-dark small mb-1">
                                    Tipe yang Sebelumnya Salah Diinput CS (Opsional):
                                </label>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <input type="text" 
                                               name="previous_type" 
                                               class="form-control form-control-sm" 
                                               placeholder="Contoh: iPhone 14"
                                               value="{{ old('previous_type') }}">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" 
                                               name="previous_storage" 
                                               class="form-control form-control-sm" 
                                               placeholder="Contoh: 128 GB"
                                               value="{{ old('previous_storage') }}">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Isi jika CS sempat membuat nota atau data dengan tipe lama yang salah, agar riwayat lama tetap terekam.
                                </small>
                            </div>
                        @else
                            {{-- Hidden data lama jika update --}}
                            <input type="hidden" name="previous_type" value="{{ $foundDevice->type }}">
                            <input type="hidden" name="previous_storage" value="{{ $foundDevice->storage }}">
                        @endif

                        {{-- Brand & Tipe HP Baru yang Benar --}}
                        <div class="row mb-3">
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="form-label font-weight-bold text-dark small" for="brandSelect">
                                    Brand / Merk <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="brand" 
                                       id="brandSelect" 
                                       class="form-control" 
                                       placeholder="Contoh: Apple"
                                       value="{{ old('brand', $foundDevice->brand ?? 'Apple') }}" 
                                       required>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <button type="button" class="imei-quick-chip" onclick="setBrand('Apple')">Apple</button>
                                    <button type="button" class="imei-quick-chip" onclick="setBrand('Samsung')">Samsung</button>
                                    <button type="button" class="imei-quick-chip" onclick="setBrand('Xiaomi')">Xiaomi</button>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label font-weight-bold text-dark small" for="typeInput">
                                    Tipe HP yang Benar (Model) <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="type" 
                                       id="typeInput" 
                                       class="form-control" 
                                       placeholder="Contoh: iPhone 13 / iPhone 14 Pro"
                                       value="{{ old('type', $foundDevice->type ?? '') }}" 
                                       required>
                                <small class="text-muted">Ketik nama model lengkap yang benar.</small>
                            </div>
                        </div>

                        {{-- Storage (GB) & Warna --}}
                        <div class="row mb-3">
                            <div class="col-12 col-md-6 mb-2 mb-md-0">
                                <label class="form-label font-weight-bold text-dark small" for="storageInput">
                                    Kapasitas Storage / GB <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="storage" 
                                       id="storageInput" 
                                       class="form-control" 
                                       placeholder="Contoh: 128 GB"
                                       value="{{ old('storage', $foundDevice->storage ?? '128 GB') }}" 
                                       required>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <button type="button" class="imei-quick-chip" onclick="setStorage('64 GB')">64 GB</button>
                                    <button type="button" class="imei-quick-chip" onclick="setStorage('128 GB')">128 GB</button>
                                    <button type="button" class="imei-quick-chip" onclick="setStorage('256 GB')">256 GB</button>
                                    <button type="button" class="imei-quick-chip" onclick="setStorage('512 GB')">512 GB</button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label font-weight-bold text-dark small" for="colorInput">
                                    Warna Unit (Opsional)
                                </label>
                                <input type="text" 
                                       name="color" 
                                       id="colorInput" 
                                       class="form-control" 
                                       placeholder="Contoh: Midnight / Starlight / Blue"
                                       value="{{ old('color', $foundDevice->color ?? '') }}">
                            </div>
                        </div>

                        {{-- Nama CS yang Salah Input (MANUAL INPUT SESUAI PERMINTAAN) --}}
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small" for="csNameInput">
                                Nama CS yang Salah Input <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">
                                    <i class="mdi mdi-account-alert"></i>
                                </span>
                                <input type="text" 
                                       name="cs_name_mistake" 
                                       id="csNameInput" 
                                       class="form-control" 
                                       placeholder="Contoh: Dimas (CS Store Condet) / Rian CS"
                                       value="{{ old('cs_name_mistake') }}" 
                                       required>
                            </div>
                            <small class="text-muted">Ketik nama Customer Service atau staf yang melakukan kesalahan input awal data perangkat.</small>
                        </div>

                        {{-- Catatan / Alasan Koreksi --}}
                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold text-dark small" for="reasonInput">
                                Alasan / Catatan Koreksi
                            </label>
                            <textarea name="correction_reason" 
                                      id="reasonInput" 
                                      rows="2" 
                                      class="form-control" 
                                      placeholder="Contoh: CS salah pilih varian tipe saat cetak nota fisik, unit sebenarnya adalah iPhone 13 128 GB.">{{ old('correction_reason') }}</textarea>
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold rounded-3 shadow-sm" style="min-height: 44px;">
                                <i class="mdi mdi-content-save-check mr-1"></i>Simpan Perubahan & Catat Riwayat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Timeline Riwayat IMEI Tertarget & Riwayat Terbaru --}}
        <div class="col-12 col-lg-5">
            {{-- Card Riwayat Khusus IMEI yang Sedang Dicari --}}
            @if($searched && ($foundDevice || $orphanHistories->isNotEmpty()))
                @php
                    $activeHistories = $foundDevice ? $foundDevice->histories : $orphanHistories;
                @endphp
                <div class="card imei-card shadow-sm mb-3">
                    <div class="imei-card-header d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark small text-uppercase">
                            <i class="mdi mdi-timeline-clock-outline text-primary mr-1"></i>Riwayat IMEI {{ $cleanSearchImei }}
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ $activeHistories->count() }} Perubahan
                        </span>
                    </div>
                    <div class="card-body p-3">
                        @if($activeHistories->isEmpty())
                            <div class="text-center py-4 text-muted small">
                                <i class="mdi mdi-information-outline fs-3 d-block mb-1"></i>
                                Belum ada riwayat perubahan tercatat untuk IMEI ini.
                            </div>
                        @else
                            <div class="imei-timeline">
                                @foreach($activeHistories as $history)
                                    <div class="imei-timeline-item">
                                        <div class="imei-timeline-dot {{ $history->action_type == 'input_awal' ? 'initial' : '' }}"></div>
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <strong class="text-dark small">
                                                {{ $history->action_type == 'input_awal' ? 'Pendaftaran Awal' : 'Koreksi Tipe HP' }}
                                            </strong>
                                            <small class="text-muted" style="font-size: 0.72rem;">
                                                {{ $history->created_at ? $history->created_at->format('d/m/Y H:i') : '-' }}
                                            </small>
                                        </div>

                                        {{-- Perbandingan Sebelum vs Sesudah --}}
                                        @if($history->old_type)
                                            <div class="p-2 mb-2 rounded-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.78rem;">
                                                <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                                    <span class="text-muted">Sebelumnya:</span>
                                                    <span class="imei-history-pill-old">
                                                        {{ $history->old_type }} ({{ $history->old_storage ?? '-' }})
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center flex-wrap gap-1">
                                                    <span class="text-muted">Dikoreksi ke:</span>
                                                    <span class="imei-history-pill-new">
                                                        {{ $history->new_type }} ({{ $history->new_storage }})
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-2 mb-2 rounded-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.78rem;">
                                                <span class="text-muted">Unit:</span>
                                                <span class="imei-history-pill-new">
                                                    {{ $history->new_type }} ({{ $history->new_storage }})
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Detail CS yang Salah Input & Super Admin yang Koreksi --}}
                                        <div class="small text-muted mb-1" style="font-size: 0.74rem;">
                                            @if($history->cs_name_mistake)
                                                <div class="text-danger font-weight-bold">
                                                    <i class="mdi mdi-alert-circle-outline mr-1"></i>CS yang salah input: {{ $history->cs_name_mistake }}
                                                </div>
                                            @endif
                                            <div>
                                                <i class="mdi mdi-shield-account-outline mr-1"></i>Super Admin: {{ $history->admin->name ?? 'Super Admin' }}
                                            </div>
                                            @if($history->correction_reason)
                                                <div class="text-muted font-italic mt-1">
                                                    "{{ $history->correction_reason }}"
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Card Audit Log: Riwayat Koreksi Terbaru Seluruh Sistem --}}
            <div class="card imei-card shadow-sm">
                <div class="imei-card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark small text-uppercase">
                        <i class="mdi mdi-history text-primary mr-1"></i>Log Koreksi Terbaru
                    </span>
                    <span class="text-muted small">Semua Cabang</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.78rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2 px-3 border-0">IMEI & Perubahan</th>
                                    <th class="py-2 px-3 border-0">CS yang Salah</th>
                                    <th class="py-2 px-3 border-0 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentHistories as $rec)
                                    <tr>
                                        <td class="py-2 px-3">
                                            <a href="{{ route('device-corrections.index', ['imei' => $rec->imei]) }}" 
                                               class="font-weight-bold text-primary text-decoration-none">
                                                <code>{{ $rec->imei }}</code>
                                            </a>
                                            <div class="mt-1">
                                                @if($rec->old_type)
                                                    <span class="text-danger text-decoration-line-through">{{ $rec->old_type }}</span>
                                                    <i class="mdi mdi-arrow-right text-muted mx-1"></i>
                                                @endif
                                                <strong class="text-success">{{ $rec->new_type }}</strong>
                                                <small class="text-muted">({{ $rec->new_storage }})</small>
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                {{ $rec->created_at ? $rec->created_at->diffForHumans() : '-' }}
                                            </small>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="font-weight-bold text-dark d-block">
                                                {{ $rec->cs_name_mistake ?: 'Tidak dicatat' }}
                                            </span>
                                            <small class="text-muted">Oleh: {{ $rec->admin->name ?? 'Admin' }}</small>
                                        </td>
                                        <td class="py-2 px-3 text-center align-middle">
                                            <a href="{{ route('device-corrections.track', $rec->imei) }}" 
                                               class="btn btn-sm btn-outline-primary px-2 py-1 rounded-2" 
                                               title="Lacak Riwayat Lengkap"
                                               style="font-size: 0.72rem;">
                                                <i class="mdi mdi-radar mr-1"></i>Lacak
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-folder-open-outline fs-4 d-block mb-1"></i>
                                            Belum ada riwayat koreksi tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($recentHistories->hasPages())
                        <div class="p-3 border-top">
                            {{ $recentHistories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function fillSearch(imei) {
        document.getElementById('searchImeiInput').value = imei;
    }

    function setBrand(brand) {
        document.getElementById('brandSelect').value = brand;
    }

    function setStorage(storage) {
        document.getElementById('storageInput').value = storage;
    }
</script>
@endpush
@endsection
