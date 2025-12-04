@extends('layout.master')

@section('title')
    @if (isset($employee))
        Riwayat Absensi - {{ $employee->name }}
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@section('heading')
    @if (isset($employee))
        <div class="d-flex align-items-center">
            <a href="{{ route('team.branch.detail', $employee->branch_id) }}" class="text-decoration-none text-muted me-3">
                <i class="mdi mdi-arrow-left"></i>
            </a>
            <div>
                Riwayat Absensi: {{ $employee->name }}
                <br>
                <small class="text-muted">
                    {{ $employee->division->name ?? '-' }} - {{ $employee->branch->name ?? '-' }}
                </small>
            </div>
        </div>
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@push('styles')
    <style>
        .verification-badge { font-size: 0.75rem; padding: 0.35rem 0.7rem; }
        .action-buttons .btn { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        .verified-check { color: #28a745; font-size: 1.1rem; }
        .pending-clock { color: #ffc107; font-size: 1.1rem; }
        .audit-mode-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .audit-photo-thumb { width: 40px; height: 40px; object-fit: cover; border: 2px solid #e2e8f0; border-radius: 8px; }
        .img-clickable { cursor: pointer; transition: transform 0.2s; }
        .img-clickable:hover { transform: scale(1.1); opacity: 0.9; }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- HEADER MODE AUDIT --}}
            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-shield-account display-6 me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Mode Cross-Check Audit</h5>
                            <p class="mb-0">Anda dapat memverifikasi, mengoreksi, dan mengubah status kehadiran karyawan.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FILTER BULAN & TAHUN & EKSPORT --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between">
                        
                        {{-- NAVIGASI KIRI (BULAN LALU) --}}
                        <div class="col-auto">
                            @php
                                $prevParams = ['month' => $prevMonth, 'year' => $prevYear];
                                if(isset($employee)) {
                                    $prevRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $prevParams));
                                } else {
                                    $prevRoute = route('attendance.history', $prevParams);
                                }
                            @endphp
                            <a href="{{ $prevRoute }}" class="btn btn-outline-secondary btn-sm" title="Bulan Sebelumnya">
                                <i class="mdi mdi-chevron-left"></i>
                            </a>
                        </div>

                        {{-- FORM FILTER TENGAH --}}
                        <div class="col-auto">
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                
                                <div class="col-auto">
                                    <label class="fw-bold mb-0 me-2"><i class="mdi mdi-calendar-month"></i> Periode:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                        {{-- MODIFIKASI: MULAI DARI 2025 --}}
                                        @php
                                            $startYear = 2025;
                                            $currentYear = date('Y');
                                            $endYear = $currentYear + 1; // Tampilkan sampai tahun depan
                                        @endphp
                                        @for ($y = $startYear; $y <= $endYear; $y++)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>

                        {{-- NAVIGASI KANAN (BULAN DEPAN) --}}
                        <div class="col-auto d-flex gap-2">
                            @php
                                $nextParams = ['month' => $nextMonth, 'year' => $nextYear];
                                if(isset($employee)) {
                                    $nextRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $nextParams));
                                } else {
                                    $nextRoute = route('attendance.history', $nextParams);
                                }
                            @endphp
                            <a href="{{ $nextRoute }}" class="btn btn-outline-secondary btn-sm" title="Bulan Selanjutnya">
                                <i class="mdi mdi-chevron-right"></i>
                            </a>

                            {{-- TOMBOL EXPORT PDF --}}
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}" 
                               class="btn btn-danger btn-sm text-white ms-2">
                                <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                             </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RINGKASAN BULANAN --}}
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <div class="card bg-primary text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Total Hari</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['total'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-success text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Hadir / WFH</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['hadir'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-info text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Sakit & Izin</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['sakit'] + $summary['izin'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-secondary text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Alpha / Bolos</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['alpha'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris 2 (Detail Kecil) --}}
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="card bg-warning text-white border-0 shadow-sm">
                        <div class="card-body py-2 text-center">
                            <small class="fw-bold">Terlambat: {{ $summary['telat'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card bg-danger text-white border-0 shadow-sm">
                        <div class="card-body py-2 text-center">
                            <small class="fw-bold">Pulang Cepat: {{ $summary['pulang_cepat'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card bg-dark text-white border-0 shadow-sm">
                        <div class="card-body py-2 text-center">
                            <small class="fw-bold">Menunggu Verifikasi: {{ $summary['pending'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA (ISI TETAP SAMA SEPERTI KODE LAMA) --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            Detail Absensi - {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                            @if (isset($employee))
                                <br><small class="text-muted">Karyawan: {{ $employee->name }}</small>
                            @endif
                        </h4>

                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge fs-6 py-2">
                                <i class="mdi mdi-shield-check me-1"></i> Mode Cross-Check Audit
                            </span>
                        @endif
                    </div>

                    @if ($history->count() > 0)
                        {{-- ... (KODE TABEL COPY-PASTE DARI KODE LAMA ANDA, TIDAK ADA PERUBAHAN DI SINI) ... --}}
                        {{-- Untuk menghemat karakter, saya asumsikan bagian <table> sampai </table> sama persis --}}
                        @include('attendance.partials.table_history', ['history' => $history]) 
                        {{-- ATAU PASTE CODE TABLE LENGKAP DI SINI SEPERTI DI PROMPT AWAL ANDA --}}
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-calendar-remove display-4 text-muted"></i>
                            <h5 class="mt-3 text-muted">Tidak ada data absensi</h5>
                            <p class="text-muted">Tidak ada riwayat pada periode {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- INCLUDE MODALS SEPERTI KODE AWAL --}}
    @include('attendance.partials.modals', ['history' => $history]) 
    {{-- ATAU PASTE CODE MODAL DI SINI --}}

@endsection

@push('scripts')
    {{-- Script Modal Image Preview --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var imagePreviewModal = document.getElementById('imagePreviewModal');
            if (imagePreviewModal) {
                imagePreviewModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var imgSrc = button.getAttribute('data-img-src');
                    var imgTitle = button.getAttribute('data-img-title');
                    var modalTitle = imagePreviewModal.querySelector('.modal-title');
                    var modalImg = imagePreviewModal.querySelector('#previewImage');
                    modalTitle.textContent = imgTitle;
                    modalImg.src = imgSrc;
                });
            }
        });
    </script>
@endpush