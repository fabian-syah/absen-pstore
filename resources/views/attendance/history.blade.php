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
        Riwayat Absensi: {{ $employee->name }}
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@push('styles')
    <style>
        /* ==========================================================
           MODERN AESTHETIC RIWAYAT ABSENSI DESIGN SYSTEM
           - Clean white cards with 1px slate-200 border
           - Bento Metric Summary Cards with soft icon containers
           - Polished period toolbar with quick month jumpers
           - Mobile-first responsive table & badge system
           ========================================================== */

        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --emerald-600: #059669;
            --amber-500: #d97706;
            --rose-500: #e11d48;
            --purple-600: #7c3aed;
            --teal-600: #0d9488;
        }

        /* --- TOOLBAR & FILTER BAR --- */
        .history-toolbar-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
        }

        .period-pill-badge {
            background: var(--slate-100);
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .month-jumper-btn {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--slate-200);
            background: #ffffff;
            color: var(--slate-700);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.15s ease;
            font-size: 16px;
        }

        .month-jumper-btn:hover {
            background: var(--slate-100);
            color: var(--primary-blue);
            border-color: var(--slate-300);
        }

        .custom-filter-select {
            background-color: #ffffff !important;
            color: var(--slate-800) !important;
            border: 1px solid var(--slate-200) !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            height: 36px !important;
            padding: 4px 12px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
        }

        .custom-filter-select:focus {
            border-color: var(--primary-blue) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
            outline: none !important;
        }

        .btn-pdf-export {
            background: #ef4444;
            color: #ffffff !important;
            border: none;
            border-radius: 10px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.25);
            transition: all 0.15s ease;
            text-decoration: none !important;
        }

        .btn-pdf-export:hover {
            background: #dc2626;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
        }

        .btn-nav-emp-card {
            border: 1px solid var(--slate-200);
            color: var(--slate-700);
            background: #ffffff;
            border-radius: 10px;
            padding: 6px 12px;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-nav-emp-card:hover:not([disabled]) {
            background: var(--slate-100);
            color: var(--primary-blue);
            border-color: var(--slate-300);
        }

        /* --- PRIMARY KPI BENTO CARDS --- */
        .metric-card-bento {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            padding: 1.15rem 1.25rem 1rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .metric-card-bento:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -3px rgba(15, 23, 42, 0.07);
            border-color: #cbd5e1;
        }

        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }

        .metric-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--slate-500);
            margin: 0;
        }

        .metric-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .metric-icon-blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .metric-icon-emerald {
            background: #ecfdf5;
            color: #059669;
        }

        .metric-icon-sky {
            background: #f0f9ff;
            color: #0284c7;
        }

        .metric-icon-rose {
            background: #fff1f2;
            color: #e11d48;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.02em;
        }

        .metric-footer-text {
            font-size: 11.5px;
            font-weight: 500;
            color: var(--slate-500);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .metric-badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 10.5px;
            font-weight: 700;
        }

        .badge-pill-emerald {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .badge-pill-sky {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-pill-rose {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        /* --- SECONDARY METRICS STRIP --- */
        .secondary-stat-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
            transition: all 0.15s ease;
            height: 100%;
        }

        .secondary-stat-card:hover {
            border-color: var(--slate-300);
            background: var(--slate-50);
        }

        .secondary-stat-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .secondary-stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .sec-icon-amber {
            background: #fef3c7;
            color: #d97706;
        }

        .sec-icon-rose {
            background: #ffe4e6;
            color: #e11d48;
        }

        .sec-icon-teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        .sec-icon-slate {
            background: #f1f5f9;
            color: #64748b;
        }

        .secondary-stat-title {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--slate-600);
            margin: 0;
            line-height: 1.2;
        }

        .secondary-stat-value {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--slate-900);
            margin: 0;
            line-height: 1;
        }

        /* --- TABLE & LIST SECTION --- */
        .history-table-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .history-table-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--slate-200);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .history-table-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--slate-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modern-history-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-history-table thead th {
            background: var(--slate-50);
            color: var(--slate-600);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 12px 14px;
            border-bottom: 1px solid var(--slate-200);
            border-top: none;
            white-space: nowrap;
        }

        .modern-history-table tbody td {
            padding: 14px 14px;
            border-bottom: 1px solid var(--slate-100);
            border-top: none;
            vertical-align: middle;
            color: var(--slate-700);
            font-size: 13px;
        }

        .modern-history-table tbody tr {
            transition: background-color 0.12s ease;
        }

        .modern-history-table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Date Block */
        .date-tile-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-tile-badge {
            width: 44px;
            height: 44px;
            background: var(--slate-50);
            border: 1.5px solid var(--slate-200);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .date-tile-day {
            font-size: 16px;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1;
        }

        .date-tile-month {
            font-size: 9.5px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1;
            margin-top: 2px;
        }

        .date-tile-weekday {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--slate-800);
            line-height: 1.2;
        }

        .date-tile-year {
            font-size: 11px;
            color: var(--slate-500);
            margin-top: 1px;
        }

        /* Time Display */
        .time-cell-box {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .time-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 700;
            font-size: 13px;
        }

        .time-pill-success {
            color: #059669;
        }

        .time-pill-danger {
            color: #dc2626;
        }

        .time-pill-dark {
            color: var(--slate-800);
        }

        .time-schedule-hint {
            font-size: 11px;
            color: var(--slate-400);
            font-weight: 500;
        }

        /* Locations & Links */
        .location-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3.5px 9px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--slate-100);
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
            white-space: nowrap;
            text-decoration: none !important;
        }

        .location-pill:hover {
            color: var(--primary-blue);
            border-color: var(--slate-300);
        }

        .location-pill-maps {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .location-pill-maps:hover {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Photos & Clickable */
        .history-thumb-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            border: 1.5px solid var(--slate-200);
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            display: block;
        }

        .history-thumb-img:hover {
            transform: scale(1.12);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
            border-color: var(--primary-blue);
        }

        .history-thumb-empty {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--slate-100);
            border: 1px dashed var(--slate-300);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--slate-400);
            font-size: 18px;
        }

        /* Modern Presence Status Badges */
        .presence-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .presence-masuk {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .presence-wfh {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .presence-telat {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .presence-sakit {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .presence-izin {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }

        .presence-cuti {
            background: #faf5ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }

        .presence-libur {
            background: #f0fdfa;
            color: #0f766e;
            border: 1px solid #99f6e4;
        }

        .presence-alpha {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        .presence-pending {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        /* Verifier Box */
        .verifier-modern-chip {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 5px 9px;
            font-size: 11.5px;
            line-height: 1.3;
        }

        .late-approval-alert {
            margin-top: 4px;
            padding: 3px 6px;
            background: #fffbeb;
            border-left: 2.5px solid #f59e0b;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            color: #b45309;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        /* Method Chip */
        .method-icon-chip {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            color: var(--slate-600);
        }

        /* Mode Audit Alert */
        .audit-alert-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 1.5rem;
        }

        .audit-mode-badge {
            background: #0f172a;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            border-radius: 9999px;
            padding: 4px 12px;
        }

        /* Empty State */
        .empty-history-box {
            padding: 4rem 1.5rem;
            text-align: center;
            color: var(--slate-500);
        }

        .empty-history-icon {
            font-size: 48px;
            color: var(--slate-300);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        /* Mobile Adjustments */
        @media (max-width: 767px) {
            .history-toolbar-card {
                padding: 1rem;
            }

            .metric-value {
                font-size: 1.65rem;
            }

            .date-tile-badge {
                width: 38px;
                height: 38px;
            }

            .date-tile-day {
                font-size: 14px;
            }

            .history-thumb-img,
            .history-thumb-empty {
                width: 34px;
                height: 34px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        // Day names map in Indonesian
        $indoDays = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        // Month names map in Indonesian
        $indoMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Navigation URLs for 1-click Month Switching
        $prevMonthUrl = isset($employee)
            ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id, 'month' => $prevMonth, 'year' => $prevYear])
            : route('attendance.history', ['month' => $prevMonth, 'year' => $prevYear]);

        $nextMonthUrl = isset($employee)
            ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id, 'month' => $nextMonth, 'year' => $nextYear])
            : route('attendance.history', ['month' => $nextMonth, 'year' => $nextYear]);

        $pdfExportUrl = route('attendance.export.pdf', [
            'month' => $selectedMonth,
            'year' => $selectedYear,
            'employeeId' => isset($employee) ? $employee->id : null
        ]);
    @endphp

    <div class="row">
        <div class="col-12">

            {{-- EMPLOYEE CONTEXT HEADER (Ketika Admin / Audit melihat riwayat karyawan lain) --}}
            @if (isset($employee))
                <div class="d-flex align-items-center justify-content-between mb-3 bg-white p-3 rounded-3 border shadow-sm flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('team.branch.detail', $employee->branch_id) }}"
                            class="btn btn-sm btn-light btn-icon me-3 rounded-circle shadow-sm" title="Kembali ke Cabang">
                            <i class="mdi mdi-arrow-left text-primary"></i>
                        </a>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="mb-0 fw-bold text-slate-900" style="font-size: 15px;">{{ $employee->name }}</h5>
                                <span class="badge" style="font-size: 10px; font-weight: 700; background: #0f172a; color: #ffffff; padding: 3px 8px; border-radius: 6px;">
                                    <i class="mdi mdi-shield-check me-1"></i>{{ strtoupper(str_replace('_', ' ', $employee->role ?? 'karyawan')) }}
                                </span>
                            </div>
                            <small class="text-muted d-flex align-items-center gap-1 mt-0.5" style="font-size: 11.5px;">
                                <i class="mdi mdi-office-building text-primary"></i> {{ $employee->division->name ?? '-' }} <span class="text-slate-300">|</span> <i class="mdi mdi-map-marker text-rose-500"></i> {{ $employee->branch->name ?? '-' }}
                            </small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('team.branch.detail', $employee->branch_id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 px-3 py-1.5" style="border-radius: 8px; font-size: 12px; font-weight: 600;">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Cabang
                        </a>
                    </div>
                </div>
            @endif

            {{-- 1. ALERT MODE AUDIT (Khusus Audit / Admin saat cek karyawan lain) --}}
            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                <div class="audit-alert-box shadow-sm">
                    <i class="mdi mdi-shield-account fs-2 text-primary"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h6 class="fw-bold mb-0 text-slate-900" style="font-size: 14px;">Mode Cross-Check & Verifikasi Audit</h6>
                            <span class="badge audit-mode-badge"><i class="mdi mdi-shield-check me-1"></i>Audit Aktif</span>
                        </div>
                        <p class="mb-0 small text-slate-600" style="font-size: 12px;">
                            Anda memiliki otorisasi penuh untuk memverifikasi absensi, melakukan koreksi jam masuk/pulang, mengunggah bukti, atau menghapus data hari ini.
                        </p>
                    </div>
                </div>
            @endif

            {{-- 2. CONTROL TOOLBAR & FILTER PERIODE --}}
            <div class="history-toolbar-card mb-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                    {{-- KIRI: Pager Karyawan (jika ada) ATAU Periode Cut-Off Badge --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if(isset($employeeCount) && $employeeCount > 1)
                            <div class="d-flex align-items-center gap-1.5">
                                @if($prevEmployee)
                                    <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $prevEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                        class="btn-nav-emp-card" title="{{ $prevEmployee->name }}">
                                        <i class="mdi mdi-chevron-left"></i>
                                        <span class="d-none d-lg-inline">{{ Str::limit($prevEmployee->name, 14) }}</span>
                                    </a>
                                @else
                                    <button class="btn-nav-emp-card opacity-50" disabled><i class="mdi mdi-chevron-left"></i></button>
                                @endif

                                <div class="px-2 text-center">
                                    <span class="fw-bold text-slate-900 small">{{ $currentEmployeeIndex }} / {{ $employeeCount }}</span>
                                </div>

                                @if($nextEmployee)
                                    <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $nextEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                        class="btn-nav-emp-card" title="{{ $nextEmployee->name }}">
                                        <span class="d-none d-lg-inline">{{ Str::limit($nextEmployee->name, 14) }}</span>
                                        <i class="mdi mdi-chevron-right"></i>
                                    </a>
                                @else
                                    <button class="btn-nav-emp-card opacity-50" disabled><i class="mdi mdi-chevron-right"></i></button>
                                @endif
                            </div>
                        @else
                            <div class="period-pill-badge">
                                <i class="mdi mdi-calendar-range text-primary fs-6"></i>
                                <span>Periode Cut-Off: <strong>26 {{ substr($indoMonths[$prevMonth] ?? 'Bln', 0, 3) }} - 25 {{ substr($indoMonths[$selectedMonth] ?? 'Bln', 0, 3) }} {{ $selectedYear }}</strong></span>
                            </div>
                        @endif
                    </div>

                    {{-- TENGAH: Form Pemilih Bulan & Tahun dengan Quick Jumper (< dan >) --}}
                    <div class="d-flex align-items-center gap-1.5 justify-content-center">
                        {{-- Tombol Bulan Sebelumnya --}}
                        <a href="{{ $prevMonthUrl }}" class="month-jumper-btn" title="Bulan Sebelumnya">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>

                        <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                            method="GET" class="d-flex align-items-center gap-1.5 mb-0">
                            
                            @if(isset($employee))
                                <input type="hidden" name="employeeId" value="{{ $employee->id }}">
                            @endif

                            {{-- Select Bulan --}}
                            <select name="month" class="form-select form-select-sm custom-filter-select" style="min-width: 125px;" onchange="this.form.submit()">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ $indoMonths[$m] ?? date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Select Tahun --}}
                            <select name="year" class="form-select form-select-sm custom-filter-select" style="min-width: 88px;" onchange="this.form.submit()">
                                @for ($y = 2025; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>

                        {{-- Tombol Bulan Berikutnya --}}
                        <a href="{{ $nextMonthUrl }}" class="month-jumper-btn" title="Bulan Berikutnya">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </div>

                    {{-- KANAN: Tombol Ekspor PDF --}}
                    <div class="d-flex align-items-center justify-content-end">
                        <a href="{{ $pdfExportUrl }}" class="btn-pdf-export" title="Unduh Rekap Format PDF">
                            <i class="mdi mdi-file-pdf-box fs-5"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>

                </div>
            </div>

            {{-- 3. PRIMARY KPI SUMMARY CARDS (Bento Grid 4 Cards) --}}
            <div class="row g-3 mb-3">
                {{-- Total Hari Kerja --}}
                <div class="col-6 col-lg-3">
                    <div class="metric-card-bento">
                        <div>
                            <div class="metric-header">
                                <span class="metric-label">Total Hari</span>
                                <div class="metric-icon-box metric-icon-blue">
                                    <i class="mdi mdi-calendar-month-outline"></i>
                                </div>
                            </div>
                            <div class="metric-value">{{ $summary['total'] }}</div>
                        </div>
                        <div class="metric-footer-text">
                            <i class="mdi mdi-clock-outline"></i> Hari dalam periode
                        </div>
                    </div>
                </div>

                {{-- Hadir / WFH --}}
                <div class="col-6 col-lg-3">
                    <div class="metric-card-bento">
                        <div>
                            <div class="metric-header">
                                <span class="metric-label">Hadir / WFH</span>
                                <div class="metric-icon-box metric-icon-emerald">
                                    <i class="mdi mdi-account-check-outline"></i>
                                </div>
                            </div>
                            <div class="metric-value text-emerald">{{ $summary['present'] }}</div>
                        </div>
                        <div class="metric-footer-text">
                            @php
                                $rate = $summary['total'] > 0 ? round(($summary['present'] / $summary['total']) * 100) : 0;
                            @endphp
                            <span class="metric-badge-pill badge-pill-emerald">{{ $rate }}% Kehadiran</span>
                        </div>
                    </div>
                </div>

                {{-- Sakit & Izin --}}
                <div class="col-6 col-lg-3">
                    <div class="metric-card-bento">
                        <div>
                            <div class="metric-header">
                                <span class="metric-label">Sakit & Izin</span>
                                <div class="metric-icon-box metric-icon-sky">
                                    <i class="mdi mdi-file-document-edit-outline"></i>
                                </div>
                            </div>
                            <div class="metric-value">{{ $summary['sakit'] + $summary['izin'] }}</div>
                        </div>
                        <div class="metric-footer-text">
                            <span class="metric-badge-pill badge-pill-sky me-1">{{ $summary['sakit'] }} Sakit</span>
                            <span class="metric-badge-pill badge-pill-sky">{{ $summary['izin'] }} Izin</span>
                        </div>
                    </div>
                </div>

                {{-- Alpha (Tanpa Keterangan) --}}
                <div class="col-6 col-lg-3">
                    <div class="metric-card-bento">
                        <div>
                            <div class="metric-header">
                                <span class="metric-label">Alpha</span>
                                <div class="metric-icon-box metric-icon-rose">
                                    <i class="mdi mdi-alert-circle-outline"></i>
                                </div>
                            </div>
                            <div class="metric-value {{ $summary['alpha'] > 0 ? 'text-danger' : '' }}">{{ $summary['alpha'] }}</div>
                        </div>
                        <div class="metric-footer-text">
                            @if($summary['alpha'] > 0)
                                <span class="metric-badge-pill badge-pill-rose">Perlu Klarifikasi</span>
                            @else
                                <span class="text-success small fw-semibold"><i class="mdi mdi-check-circle me-0.5"></i>Tertib (Nihil)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. SECONDARY METRICS STRIP (4 Mini Metric Cards) --}}
            <div class="row g-3 mb-4">
                {{-- Terlambat --}}
                <div class="col-6 col-lg-3">
                    <div class="secondary-stat-card">
                        <div class="secondary-stat-left">
                            <div class="secondary-stat-icon sec-icon-amber">
                                <i class="mdi mdi-clock-alert-outline"></i>
                            </div>
                            <div>
                                <p class="secondary-stat-title">Terlambat</p>
                                <small class="text-muted" style="font-size: 10.5px;">Lewat jam masuk</small>
                            </div>
                        </div>
                        <div class="secondary-stat-value text-amber">{{ $summary['telat'] }}</div>
                    </div>
                </div>

                {{-- Pulang Cepat --}}
                <div class="col-6 col-lg-3">
                    <div class="secondary-stat-card">
                        <div class="secondary-stat-left">
                            <div class="secondary-stat-icon sec-icon-rose">
                                <i class="mdi mdi-run-fast"></i>
                            </div>
                            <div>
                                <p class="secondary-stat-title">Pulang Cepat</p>
                                <small class="text-muted" style="font-size: 10.5px;">Sebelum jam keluar</small>
                            </div>
                        </div>
                        <div class="secondary-stat-value text-danger">{{ $summary['pulang_cepat'] }}</div>
                    </div>
                </div>

                {{-- Libur --}}
                <div class="col-6 col-lg-3">
                    <div class="secondary-stat-card">
                        <div class="secondary-stat-left">
                            <div class="secondary-stat-icon sec-icon-teal">
                                <i class="mdi mdi-calendar-star-outline"></i>
                            </div>
                            <div>
                                <p class="secondary-stat-title">Hari Libur</p>
                                <small class="text-muted" style="font-size: 10.5px;">Off schedule</small>
                            </div>
                        </div>
                        <div class="secondary-stat-value text-teal">{{ $summary['libur'] }}</div>
                    </div>
                </div>

                {{-- Pending --}}
                <div class="col-6 col-lg-3">
                    <div class="secondary-stat-card">
                        <div class="secondary-stat-left">
                            <div class="secondary-stat-icon sec-icon-slate">
                                <i class="mdi mdi-clock-outline"></i>
                            </div>
                            <div>
                                <p class="secondary-stat-title">Pending</p>
                                <small class="text-muted" style="font-size: 10.5px;">Belum verifikasi</small>
                            </div>
                        </div>
                        <div class="secondary-stat-value">{{ $summary['pending'] }}</div>
                    </div>
                </div>
            </div>

            {{-- 5. DETAIL TABEL RIWAYAT ABSENSI --}}
            <div class="history-table-card">
                <div class="history-table-header">
                    <div class="d-flex align-items-center gap-2.5">
                        <h5 class="history-table-title">
                            <i class="mdi mdi-calendar-text text-primary fs-5"></i>
                            <span>Detail Absensi Harian</span>
                        </h5>
                        <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                            {{ count($history) }} Catatan
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted d-none d-sm-inline" style="font-size: 11.5px;">
                            <i class="mdi mdi-information-outline me-1"></i>Klik foto untuk memperbesar
                        </small>
                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge"><i class="mdi mdi-shield-check me-1"></i>Mode Audit</span>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table modern-history-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Tanggal & Hari</th>
                                <th>Masuk</th>
                                <th>Lokasi Masuk</th>
                                <th>Foto</th>
                                <th style="border-left: 1px dashed #e2e8f0;">Pulang</th>
                                <th>Lokasi Pulang</th>
                                <th>Foto</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Bukti Izin / Audit</th>
                                <th>Petugas & Verifikasi</th>
                                <th class="text-center">Metode</th>
                                @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                    <th class="text-end pe-4">Aksi Audit</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $att)
                                @php
                                    $displayDate = $att->check_in_time ?? $att->check_out_time ?? now();
                                    $dayEng = $displayDate->format('l');
                                    $dayId = $indoDays[$dayEng] ?? $dayEng;
                                    $monthShort = $indoMonths[$displayDate->month] ?? $displayDate->format('M');
                                    $monthShort = substr($monthShort, 0, 3);

                                    $fixedScheduleIn = $att->scheduled_check_in ?? ($att->user->check_in_start ?? ($att->user->workSchedule->start_time ?? null));
                                    $fixedScheduleOut = $att->scheduled_check_out ?? ($att->user->check_out_start ?? ($att->user->workSchedule->end_time ?? null));

                                    $isLateApproval = $att->check_in_time && $att->verified_by_user_id && $att->updated_at && $att->updated_at->gt($att->check_in_time->endOfDay());
                                    $approvalDelay = $isLateApproval ? $att->check_in_time->startOfDay()->diffInDays($att->updated_at->startOfDay()) : 0;
                                    $approverName = $att->verifier->name ?? null;

                                    $statusLwr = strtolower($att->presence_status ?? '');
                                    $presenceClass = match (true) {
                                        $statusLwr == 'masuk' => 'presence-masuk',
                                        str_contains($statusLwr, 'wfh') || str_contains($statusLwr, 'dinas') => 'presence-wfh',
                                        str_contains($statusLwr, 'telat') => 'presence-telat',
                                        $statusLwr == 'sakit' => 'presence-sakit',
                                        $statusLwr == 'izin' => 'presence-izin',
                                        $statusLwr == 'cuti' => 'presence-cuti',
                                        $statusLwr == 'libur' => 'presence-libur',
                                        $statusLwr == 'alpha' => 'presence-alpha',
                                        default => 'presence-pending',
                                    };

                                    $statusIcon = match (true) {
                                        $statusLwr == 'masuk' => 'mdi-check-circle-outline',
                                        str_contains($statusLwr, 'wfh') => 'mdi-home-outline',
                                        str_contains($statusLwr, 'telat') => 'mdi-clock-alert-outline',
                                        $statusLwr == 'sakit' => 'mdi-medical-bag',
                                        $statusLwr == 'izin' => 'mdi-file-document-edit-outline',
                                        $statusLwr == 'cuti' => 'mdi-beach',
                                        $statusLwr == 'libur' => 'mdi-calendar-star-outline',
                                        $statusLwr == 'alpha' => 'mdi-close-circle-outline',
                                        default => 'mdi-help-circle-outline',
                                    };

                                    $displayText = ucwords($att->presence_status ?? 'Pending');
                                    if ($displayText === 'Izin Telat') {
                                        $displayText = 'Telat Hadir';
                                    }
                                @endphp
                                <tr>
                                    {{-- 1. Tanggal & Hari --}}
                                    <td class="ps-4">
                                        <div class="date-tile-box">
                                            <div class="date-tile-badge">
                                                <span class="date-tile-day">{{ $displayDate->format('d') }}</span>
                                                <span class="date-tile-month">{{ $monthShort }}</span>
                                            </div>
                                            <div>
                                                <div class="date-tile-weekday">{{ $dayId }}</div>
                                                <div class="date-tile-year">{{ $displayDate->format('Y') }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 2. Jam Masuk --}}
                                    <td>
                                        <div class="time-cell-box">
                                            <div class="time-pill-badge {{ $att->is_calculated_late ? 'time-pill-danger' : ($att->check_in_time ? 'time-pill-success' : 'time-pill-dark') }}">
                                                <i class="mdi {{ $att->is_calculated_late ? 'mdi-clock-alert-outline' : ($att->check_in_time ? 'mdi-login-variant' : 'mdi-minus') }} fs-6"></i>
                                                <span>{{ $att->check_in_time ? $att->check_in_time->format('H:i') : '-' }}</span>
                                            </div>
                                            <span class="time-schedule-hint">
                                                Jadwal: {{ $fixedScheduleIn ? \Carbon\Carbon::parse($fixedScheduleIn)->format('H:i') : '-' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- 3. Lokasi Masuk --}}
                                    <td>
                                        @if($att->attendance_type == 'scan' || $att->scanner_user_id)
                                            <span class="location-pill" title="Absen terverifikasi scanner di kantor">
                                                <i class="mdi mdi-office-building text-primary"></i> Di Kantor
                                            </span>
                                        @elseif($att->latitude && $att->longitude)
                                            <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}"
                                                target="_blank" class="location-pill location-pill-maps" title="Buka Koordinat di Google Maps">
                                                <i class="mdi mdi-map-marker-radius"></i> Maps
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>

                                    {{-- 4. Foto Masuk --}}
                                    <td>
                                        @php
                                            $inPhoto = $att->photo_path ? asset('storage/' . $att->photo_path) : null;
                                        @endphp
                                        @if ($inPhoto)
                                            <img src="{{ $inPhoto }}" class="history-thumb-img"
                                                data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                data-img-src="{{ $inPhoto }}" data-img-title="Foto Absen Masuk - {{ $displayDate->format('d M Y') }}"
                                                alt="Foto Masuk">
                                        @else
                                            <div class="history-thumb-empty" title="Tidak ada foto">
                                                <i class="mdi mdi-camera-off"></i>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- 5. Jam Pulang --}}
                                    <td style="border-left: 1px dashed #e2e8f0;">
                                        @if ($att->check_out_time)
                                            <div class="time-cell-box">
                                                <div class="time-pill-badge time-pill-dark">
                                                    <i class="mdi mdi-logout-variant text-primary fs-6"></i>
                                                    <span>{{ $att->check_out_time->format('H:i') }}</span>
                                                </div>
                                                <span class="time-schedule-hint">
                                                    Jadwal: {{ $fixedScheduleOut ? \Carbon\Carbon::parse($fixedScheduleOut)->format('H:i') : '-' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                                Belum Out
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 6. Lokasi Pulang --}}
                                    <td>
                                        @if($att->attendance_type == 'scan' || $att->scanner_user_id)
                                            <span class="location-pill" title="Absen terverifikasi scanner di kantor">
                                                <i class="mdi mdi-office-building text-primary"></i> Di Kantor
                                            </span>
                                        @elseif($att->check_out_time && $att->latitude_out && $att->longitude_out)
                                            <a href="https://maps.google.com/?q={{ $att->latitude_out }},{{ $att->longitude_out }}"
                                                target="_blank" class="location-pill location-pill-maps" title="Buka Koordinat Pulang di Google Maps">
                                                <i class="mdi mdi-map-marker-radius"></i> Maps
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>

                                    {{-- 7. Foto Pulang --}}
                                    <td>
                                        @php
                                            $outPhoto = $att->photo_out_path ? asset('storage/' . $att->photo_out_path) : null;
                                        @endphp
                                        @if ($outPhoto)
                                            <img src="{{ $outPhoto }}" class="history-thumb-img"
                                                data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                data-img-src="{{ $outPhoto }}" data-img-title="Foto Absen Pulang - {{ $displayDate->format('d M Y') }}"
                                                alt="Foto Pulang">
                                        @else
                                            <div class="history-thumb-empty" title="Tidak ada foto">
                                                <i class="mdi mdi-camera-off"></i>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- 8. Status Kehadiran --}}
                                    <td class="text-center">
                                        <span class="presence-pill {{ $presenceClass }}">
                                            <i class="mdi {{ $statusIcon }}"></i>
                                            <span>{{ $displayText }}</span>
                                        </span>
                                    </td>

                                    {{-- 9. Bukti Izin / Audit --}}
                                    <td class="text-center">
                                        @php
                                            $leavePhoto = null;
                                            if ($att->relationLoaded('leaveRequest') && $att->leaveRequest && $att->leaveRequest->file_proof) {
                                                $leavePhoto = asset('storage/' . $att->leaveRequest->file_proof);
                                            } elseif ($att->leaveRequest && $att->leaveRequest->file_proof) {
                                                $leavePhoto = asset('storage/' . $att->leaveRequest->file_proof);
                                            }
                                        @endphp
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-1.5">
                                            @if ($leavePhoto)
                                                <div class="text-center">
                                                    <img src="{{ $leavePhoto }}" class="history-thumb-img mx-auto"
                                                        data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                        data-img-src="{{ $leavePhoto }}" data-img-title="Lampiran Bukti Izin"
                                                        alt="Bukti Izin">
                                                    <span class="badge mt-1" style="font-size: 9px; font-weight: 700; background: #fef3c7; color: #b45309; padding: 2px 5px; border-radius: 4px;">IZIN</span>
                                                </div>
                                            @endif

                                            @if ($att->audit_photo_path)
                                                <div class="text-center">
                                                    <img src="{{ asset('storage/' . $att->audit_photo_path) }}" class="history-thumb-img mx-auto"
                                                        data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                        data-img-src="{{ asset('storage/' . $att->audit_photo_path) }}" data-img-title="Lampiran Bukti Koreksi Audit"
                                                        alt="Bukti Audit">
                                                    <span class="badge mt-1" style="font-size: 9px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 5px; border-radius: 4px;">AUDIT</span>
                                                </div>
                                            @endif

                                            @if (!$leavePhoto && !$att->audit_photo_path)
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 10. Petugas & Verifikasi --}}
                                    <td>
                                        <div class="verifier-modern-chip">
                                            @if($att->verifier)
                                                <div class="fw-bold text-success d-flex align-items-center gap-1">
                                                    <i class="mdi mdi-check-decagram text-primary"></i>
                                                    <span>{{ $att->verifier->name }}</span>
                                                </div>
                                            @endif
                                            @if($att->scanner)
                                                <div class="text-muted" style="font-size: 11px;">
                                                    Scan: {{ $att->scanner->name }}
                                                </div>
                                            @endif
                                            @if(!$att->verifier && !$att->scanner)
                                                <div class="fw-semibold text-slate-700">System</div>
                                            @endif

                                            @if ($isLateApproval)
                                                <div class="late-approval-alert" title="Terlambat verifikasi {{ $approvalDelay }} hari setelah absen">
                                                    <i class="mdi mdi-clock-alert"></i>
                                                    <span>Telat Approve (+{{ $approvalDelay }}h)</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 11. Metode --}}
                                    <td class="text-center">
                                        <div class="method-icon-chip" title="Metode: {{ $att->attendance_type ?? 'system' }}">
                                            @if($att->attendance_type == 'scan')
                                                <i class="mdi mdi-qrcode-scan text-primary"></i>
                                            @elseif($att->attendance_type == 'self')
                                                <i class="mdi mdi-camera-front-variant text-info"></i>
                                            @elseif($att->attendance_type == 'manual')
                                                <i class="mdi mdi-pencil-box-outline text-warning"></i>
                                            @else
                                                <i class="mdi mdi-cog-outline text-secondary"></i>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 12. Aksi Audit (Khusus Admin / Audit) --}}
                                    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                        <td class="text-end pe-4">
                                            @if ($att->id)
                                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                                    @if ($att->status != 'verified')
                                                        <button type="button" class="btn btn-success text-white" data-bs-toggle="modal"
                                                            data-bs-target="#verifyModal{{ $att->id }}" title="Verifikasi Absen">
                                                            <i class="mdi mdi-check"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-primary text-white" data-bs-toggle="modal"
                                                        data-bs-target="#editAuditModal{{ $att->id }}" title="Koreksi Jam / Status">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('audit.delete_day') }}" method="POST" class="d-inline" onsubmit="return confirm('HAPUS BERSIH: Yakin ingin menghapus semua data (Absen dan Izin/Libur beserta fotonya) untuk tanggal ini? Halaman ini akan menjadi seperti belum pernah absen sama sekali.')">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                                        <input type="hidden" name="date" value="{{ $displayDate->format('Y-m-d') }}">
                                                        <button type="submit" class="btn btn-danger text-white rounded-end" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="Hapus Bersih Hari Ini">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                                    <button type="button" class="btn btn-info text-white"
                                                        data-bs-toggle="modal" data-bs-target="#createAuditModal{{ $loop->index }}" title="Input Manual">
                                                        <i class="mdi mdi-pencil me-1"></i>Edit
                                                    </button>
                                                    <form action="{{ route('audit.delete_day') }}" method="POST" class="d-inline" onsubmit="return confirm('HAPUS BERSIH: Yakin ingin membatalkan Izin/Libur khusus untuk tanggal ini? Data akan bersih dan kembali kosong.')">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                                        <input type="hidden" name="date" value="{{ $displayDate->format('Y-m-d') }}">
                                                        <button type="submit" class="btn btn-danger text-white rounded-end" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="Hapus Bersih Hari Ini">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin')) ? 12 : 11 }}" class="p-0">
                                        <div class="empty-history-box">
                                            <div class="empty-history-icon">
                                                <i class="mdi mdi-calendar-blank-outline"></i>
                                            </div>
                                            <h6 class="fw-bold text-slate-800 mb-1">Belum Ada Riwayat Absensi</h6>
                                            <p class="mb-0 small text-muted">Belum ditemukan catatan absensi atau izin pada periode bulan ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL PREVIEW IMAGE BESAR --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 mb-2.5 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title text-white fw-bold mb-0" style="font-size: 15px;">Preview Foto Absensi</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded-4 shadow-lg border border-2 border-white"
                        style="max-height: 82vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS AUDIT (Khusus Admin / Audit) --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
        @foreach ($history as $index => $att)
            @php
                $dateObj = $att->check_in_time ?? $att->check_out_time ?? now();
                $displayDateStr = $dateObj ? $dateObj->format('Y-m-d') : null;
            @endphp
            @if (!$att->id)
                {{-- Modal Input Manual --}}
                <div class="modal fade" id="createAuditModal{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow-lg">
                            <div class="modal-header bg-info text-white rounded-top-4">
                                <h6 class="modal-title fw-bold"><i class="mdi mdi-pencil-box-outline me-1"></i> Input / Koreksi Absensi</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.store.attendance') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body p-4">
                                    <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                    <input type="hidden" name="date" value="{{ $displayDateStr }}">
                                    
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Status Kehadiran</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk">✅ Masuk</option>
                                            <option value="WFH">🏠 WFH</option>
                                            <option value="Sakit">🤒 Sakit</option>
                                            <option value="Izin">📝 Izin</option>
                                            <option value="Libur">📅 Libur (Off Day)</option>
                                            <option value="Cuti">🏖️ Cuti</option>
                                            <option value="Alpha">❌ Alpha</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Unggah Bukti Foto</label>
                                        <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label small fw-bold">Catatan Audit</label>
                                        <textarea name="audit_note" class="form-control" rows="2" placeholder="Tulis alasan koreksi..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0 p-4">
                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                {{-- Modal Verifikasi --}}
                <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow-lg">
                            <div class="modal-header bg-success text-white rounded-top-4">
                                <h6 class="modal-title fw-bold"><i class="mdi mdi-check-decagram me-1"></i> Verifikasi Absensi</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Ubah Status</label>
                                        @if($att->latitude && $att->longitude)
                                            <div class="mb-2">
                                                <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}"
                                                    target="_blank" class="location-pill location-pill-maps">
                                                    <i class="mdi mdi-map-marker-radius"></i> Cek Koordinat Maps
                                                </a>
                                            </div>
                                        @endif
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk</option>
                                            <option value="WFH" {{ $att->presence_status == 'WFH' ? 'selected' : '' }}>🏠 WFH</option>
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>📝 Izin</option>
                                            <option value="Libur" {{ $att->presence_status == 'Libur' ? 'selected' : '' }}>📅 Libur (Off Day)</option>
                                            <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Unggah Bukti Audit</label>
                                        <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label small fw-bold">Catatan Audit</label>
                                        <textarea name="audit_note" class="form-control" rows="2" placeholder="Catatan audit...">{{ $att->audit_note }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0 p-4">
                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success text-white rounded-pill px-4 fw-bold">Verifikasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Koreksi Data --}}
                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white rounded-top-4">
                                <h6 class="modal-title fw-bold"><i class="mdi mdi-pencil me-1"></i> Koreksi Jam & Status</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    @if($att->latitude && $att->longitude)
                                        <div class="mb-3">
                                            <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank"
                                                class="location-pill location-pill-maps">
                                                <i class="mdi mdi-map-marker-radius"></i> Cek Lokasi Maps
                                            </a>
                                        </div>
                                    @endif
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control"
                                                value="{{ $att->check_in_time ? $att->check_in_time->format('H:i') : '' }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control"
                                                value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Status Kehadiran</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk</option>
                                            <option value="WFH" {{ $att->presence_status == 'WFH' ? 'selected' : '' }}>🏠 WFH</option>
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>📝 Izin</option>
                                            <option value="Libur" {{ $att->presence_status == 'Libur' ? 'selected' : '' }}>📅 Libur (Off Day)</option>
                                            <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                            <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-danger">Foto Bukti Audit</label>
                                        <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label small fw-bold">Alasan Koreksi</label>
                                        <textarea name="audit_note" class="form-control" rows="2"
                                            placeholder="Tulis alasan koreksi...">{{ $att->audit_note }}</textarea>
                                    </div>
                                    <input type="hidden" name="status" value="verified">
                                </div>
                                <div class="modal-footer border-top-0 pt-0 p-4">
                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Koreksi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var imagePreviewModal = document.getElementById('imagePreviewModal');
            if (imagePreviewModal) {
                imagePreviewModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var imgSrc = button.getAttribute('data-img-src');
                    var imgTitle = button.getAttribute('data-img-title');
                    var modalTitle = imagePreviewModal.querySelector('.modal-title');
                    var modalImg = imagePreviewModal.querySelector('#previewImage');
                    if (modalTitle && imgTitle) {
                        modalTitle.textContent = imgTitle;
                    }
                    if (modalImg && imgSrc) {
                        modalImg.src = imgSrc;
                    }
                });
            }
        });
    </script>
@endpush