@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card shadow-sm rounded-lg border-0">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 border-bottom pb-4">
                        <div>
                            <h4 class="card-title fw-bold text-primary mb-2">Ringkasan Gaji Tahunan</h4>
                            <p class="text-muted mb-0"><i class="mdi mdi-calendar-range me-1"></i> Periode Cutoff: Tgl 26 (Bulan Lalu) - Tgl 25 (Bulan Ini)</p>
                        </div>
                        <div class="mt-3 mt-md-0 d-flex align-items-center bg-primary text-white px-4 py-2 rounded-pill shadow-sm">
                            <i class="mdi mdi-cash-multiple me-2 fs-5"></i>
                            <span class="fw-bold fs-6">Total Tahun {{ $year }}: Rp {{ number_format($totalAnnual, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- FILTER TAHUN & USER (Hanya muncul u/ Admin/Audit/Leader) --}}
                    <div class="bg-light p-3 p-md-4 rounded-3 mb-4 border border-light-subtle shadow-sm">
                        <form method="GET" action="{{ route('salary-summary.index') }}" class="row g-3">
                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="form-label fw-bold text-dark"><i class="mdi mdi-calendar"></i> Pilih Tahun</label>
                                <select name="year" class="form-select bg-white text-dark shadow-sm">
                                    @for ($y = 2024; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            {{-- Admin/Audit/Leader bisa pilih cabang --}}
                            @if(in_array(auth()->user()->role, ['admin', 'admin_gaji', 'owner', 'audit', 'leader']))
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label class="form-label fw-bold text-dark"><i class="mdi mdi-store"></i> Pilih Cabang</label>
                                    <select name="branch_id" class="form-select select2 bg-white text-dark shadow-sm" onchange="this.form.submit()">
                                        <option value="">-- Semua Cabang {{ !in_array(auth()->user()->role, ['admin', 'admin_gaji', 'owner']) ? '(Kelolaan)' : '' }} --</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold text-dark"><i class="mdi mdi-account-search"></i> Pilih Karyawan</label>
                                    <select name="user_id" class="form-select select2 text-dark shadow-sm w-100">
                                        <option value="">-- Semua Karyawan (Kumulatif) --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                                {{ $user->login_id }} - {{ $user->name }} {{ !$user->is_active ? '(INACTIVE)' : '' }} ({{ $user->branch->name ?? 'Pusat' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1 fw-bold shadow-sm">
                                    <i class="mdi mdi-filter-variant me-1"></i> Tampilkan
                                </button>
                                <a href="{{ route('salary-summary.index') }}" class="btn btn-light border flex-grow-1 fw-bold shadow-sm">
                                    <i class="mdi mdi-refresh me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    {{-- DESKTOP TABLE VIEW --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle border">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center text-white" style="width: 5%">No</th>
                                    <th class="text-white" style="width: 15%">Bulan Gaji</th>
                                    <th class="text-white" style="width: 20%">Periode Absensi (Cutoff)</th>
                                    <th class="text-center text-white" style="width: 15%">Kategori</th>
                                    <th class="text-end text-white" style="width: 15%">Gaji Pokok</th>
                                    <th class="text-end text-white" style="width: 15%">Bonus & THR</th>
                                    <th class="text-end text-white" style="width: 15%">Total Diterima</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary as $item)
                                    <tr>
                                        <td class="text-center fw-medium">{{ $item['month_num'] }}</td>
                                        <td class="fw-bold text-dark">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded p-2 me-2 text-center text-primary" style="width: 40px;">
                                                    <i class="mdi mdi-calendar-month text-primary mb-0 fs-5"></i>
                                                </div>
                                                {{ $item['month_name'] }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><i class="mdi mdi-clock-outline text-muted"></i> {{ $item['period_string'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($item['data'])
                                                @if($item['data']->category == 'promotor')
                                                    <span class="badge bg-info bg-opacity-25 text-dark border border-info rounded-pill px-3 py-2 fw-bold">Promotor</span>
                                                @elseif($item['data']->category == 'freelance')
                                                    <span class="badge bg-warning bg-opacity-25 text-dark border border-warning rounded-pill px-3 py-2 fw-bold">Freelance</span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-25 text-dark border border-success rounded-pill px-3 py-2 fw-bold">Karyawan</span>
                                                @endif
                                            @elseif($item['amount'] > 0 || $item['bonus_amount'] > 0 || $item['thr_amount'] > 0)
                                                <span class="badge bg-primary bg-opacity-25 text-dark border border-primary rounded-pill px-3 py-2 fw-bold">Total Gabungan</span>
                                            @else
                                                <span class="text-muted fw-bold">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold {{ $item['amount'] > 0 ? 'text-success' : 'text-muted' }} fs-6">
                                            @if($item['amount'] > 0)
                                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end fs-6">
                                            @if($item['bonus_amount'] > 0 || $item['thr_amount'] > 0)
                                                @if($item['bonus_amount'] > 0)
                                                    <div class="text-info fw-bold mb-1" title="Bonus"><i class="mdi mdi-star-circle-outline"></i> Rp {{ number_format($item['bonus_amount'], 0, ',', '.') }}</div>
                                                @endif
                                                @if($item['thr_amount'] > 0)
                                                    <div class="text-warning fw-bold" title="THR"><i class="mdi mdi-wallet-giftcard"></i> Rp {{ number_format($item['thr_amount'], 0, ',', '.') }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold {{ ($item['amount'] + $item['bonus_amount'] + $item['thr_amount']) > 0 ? 'text-primary' : 'text-muted' }} fs-5">
                                            @if(($item['amount'] + $item['bonus_amount'] + $item['thr_amount']) > 0)
                                                Rp {{ number_format($item['amount'] + $item['bonus_amount'] + $item['thr_amount'], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold fs-5">
                                    <td colspan="6" class="text-end py-3 text-dark">GRAND TOTAL DIBAYARKAN TAHUN {{ $year }}</td>
                                    <td class="text-end py-3 text-primary fs-4">Rp {{ number_format($totalAnnual, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- MOBILE CARD LIST VIEW --}}
                    <div class="d-md-none">
                        @foreach($summary as $item)
                            @php
                                $totalItem = $item['amount'] + $item['bonus_amount'] + $item['thr_amount'];
                            @endphp
                            <div class="card mb-3 border shadow-sm rounded-3 overflow-hidden">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-2 text-center text-primary" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="mdi mdi-calendar-month text-primary fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $item['month_name'] }}</h6>
                                                <small class="text-muted" style="font-size: 11px;">Bulan ke-{{ $item['month_num'] }}</small>
                                            </div>
                                        </div>
                                        <div>
                                            @if($item['data'])
                                                @if($item['data']->category == 'promotor')
                                                    <span class="badge bg-info bg-opacity-25 text-dark border border-info rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">Promotor</span>
                                                @elseif($item['data']->category == 'freelance')
                                                    <span class="badge bg-warning bg-opacity-25 text-dark border border-warning rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">Freelance</span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-25 text-dark border border-success rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">Karyawan</span>
                                                @endif
                                            @elseif($totalItem > 0)
                                                <span class="badge bg-primary bg-opacity-25 text-dark border border-primary rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">Total Gabungan</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <i class="mdi mdi-clock-outline me-1"></i>Cutoff: {{ $item['period_string'] }}
                                    </div>

                                    <div class="bg-light rounded-3 p-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted small">Gaji Pokok:</span>
                                            <span class="fw-bold {{ $item['amount'] > 0 ? 'text-dark' : 'text-muted' }}">
                                                {{ $item['amount'] > 0 ? 'Rp ' . number_format($item['amount'], 0, ',', '.') : '-' }}
                                            </span>
                                        </div>

                                        @if($item['bonus_amount'] > 0 || $item['thr_amount'] > 0)
                                            <div class="pt-1 border-top mt-1">
                                                @if($item['bonus_amount'] > 0)
                                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                                        <span class="text-info fw-semibold"><i class="mdi mdi-star"></i> Bonus:</span>
                                                        <span class="fw-bold text-dark">Rp {{ number_format($item['bonus_amount'], 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                                @if($item['thr_amount'] > 0)
                                                    <div class="d-flex justify-content-between align-items-center small">
                                                        <span class="text-warning fw-semibold"><i class="mdi mdi-wallet-giftcard"></i> THR:</span>
                                                        <span class="fw-bold text-dark">Rp {{ number_format($item['thr_amount'], 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center pt-1 border-top mt-1">
                                            <span class="text-dark small fw-bold">Total Diterima:</span>
                                            <span class="fw-bold {{ $totalItem > 0 ? 'text-primary' : 'text-muted' }} fs-6">
                                                {{ $totalItem > 0 ? 'Rp ' . number_format($totalItem, 0, ',', '.') : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Mobile Grand Total Card --}}
                        <div class="card border-0 bg-primary text-white shadow rounded-3 p-3 mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="opacity-75 d-block">Grand Total Tahun {{ $year }}</small>
                                    <span class="fw-bold fs-5">Rp {{ number_format($totalAnnual, 0, ',', '.') }}</span>
                                </div>
                                <i class="mdi mdi-cash-multiple fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 45px;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            background-color: #fff; /* Paksa dropdown berwarna putih terang */
            color: #212529 !important; /* Teks default hitam */
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #212529 !important; /* Paksa teks pada dropdown tidak samar/abu-abu */
            font-weight: 500;
        }
        .select2-results__option {
            color: #212529 !important; /* Teks pada list pilihan drop down */
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk Karyawan & Cabang
            $('.select2').each(function() {
                var placeholder = $(this).attr('name') == 'branch_id' ? "-- Pilih Cabang --" : "-- Pilih Karyawan --";
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true
                });
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endpush