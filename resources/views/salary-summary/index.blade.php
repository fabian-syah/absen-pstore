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

                    {{-- FILTER TAHUN & USER (Hanya muncul u/ Admin) --}}
                    <div class="bg-light p-4 rounded-3 mb-5 border border-light-subtle shadow-sm">
                        <form method="GET" action="{{ route('salary-summary.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark"><i class="mdi mdi-calendar"></i> Pilih Tahun</label>
                                <select name="year" class="form-select bg-white text-dark shadow-sm">
                                    @for ($y = 2024; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            {{-- Admin bisa pilih karyawan lain --}}
                            @if(in_array(auth()->user()->role, ['admin', 'admin_gaji', 'owner']))
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-dark"><i class="mdi mdi-account-search"></i> Pilih Karyawan</label>
                                    <select name="user_id" class="form-select select2 text-dark shadow-sm w-100">
                                        <option value="">-- Semua Karyawan (Kumulatif) --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                                {{ $user->login_id }} - {{ $user->name }} ({{ $user->branch->name ?? 'Pusat' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                    <i class="mdi mdi-filter-variant me-1"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
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
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "-- Pilih Karyawan --",
                allowClear: true
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endpush