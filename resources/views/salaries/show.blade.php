@extends('layout.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- TOMBOL AKSI (Hanya muncul di layar, tidak saat print) --}}
            <div class="d-flex justify-content-between mb-3 no-print">
                <a href="{{ route('my-salary.index') }}" class="btn btn-light shadow-sm"><i class="mdi mdi-arrow-left"></i>
                    Kembali</a>
                <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="mdi mdi-printer"></i> Cetak
                    / PDF</button>
            </div>

            <div class="card shadow-lg border-0" id="payslip">
                <div class="card-body p-5">

                    {{-- HEADER PERUSAHAAN --}}
                    <div class="border-bottom pb-4 mb-4 d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="fw-bold text-dark mb-1">PSTORE</h2>
                            <p class="text-muted mb-0">Divisi Finance & HRD</p>
                            <small class="text-muted">Jl. Raya Condet No.1, Jakarta Timur</small>
                        </div>
                        <div class="text-end">
                            <h4 class="text-uppercase fw-bold text-primary mb-1">SLIP GAJI</h4>
                            <p class="mb-0 fw-bold text-dark">
                                Periode:
                                {{ \Carbon\Carbon::createFromDate($salary->year, $salary->month, 1)->isoFormat('MMMM Y') }}
                            </p>
                            <div class="alert alert-info py-1 px-2 mt-2 mb-0 d-inline-block"
                                style="font-size: 0.75rem; border-left: 3px solid #0dcaf0;">
                                <i class="mdi mdi-information-outline me-1"></i>
                                Cutoff: 26
                                {{ \Carbon\Carbon::createFromDate($salary->year, $salary->month, 1)->subMonth()->isoFormat('MMM') }}
                                - 25
                                {{ \Carbon\Carbon::createFromDate($salary->year, $salary->month, 1)->isoFormat('MMM') }}
                            </div>
                            <small class="text-muted d-block mt-1">ID Transaksi:
                                #PAY-{{ $salary->id }}-{{ $salary->year }}{{ $salary->month }}</small>
                        </div>
                    </div>

                    {{-- INFO KARYAWAN --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="text-muted ps-0" width="100">Nama</td>
                                    <td class="fw-bold text-dark">: {{ $salary->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">ID Karyawan</td>
                                    <td class="fw-bold text-dark">: {{ $salary->user->login_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Jabatan</td>
                                    <td class="fw-bold text-dark">: {{ $salary->user->division->name ?? 'Staff' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Cabang</td>
                                    <td class="fw-bold text-dark">: {{ $salary->user->branch->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-6 text-end">
                            <div class="p-3 bg-light rounded d-inline-block text-start mb-2" style="min-width: 200px;">
                                <small class="text-muted d-block">Status Pembayaran</small>
                                @php
                                    $isPaid = $salary->status == 'paid';
                                    if (!$isPaid && $salary->status == 'pending' && $salary->published_at) {
                                        if (\Carbon\Carbon::parse($salary->published_at)->startOfDay()->lte(now())) {
                                            $isPaid = true;
                                        }
                                    }
                                @endphp
                                @if($isPaid)
                                    <h5 class="fw-bold text-success mb-0"><i class="mdi mdi-check-circle"></i> LUNAS (PAID)
                                    </h5>
                                    <small
                                        class="text-muted">{{ $salary->published_at ? \Carbon\Carbon::parse($salary->published_at)->format('d M Y') : '-' }}</small>
                                @else
                                    <h5 class="fw-bold text-warning mb-0"><i class="mdi mdi-clock"></i> PENDING</h5>
                                @endif
                            </div>

                            <div class="p-3 bg-white border rounded d-inline-block text-start" style="min-width: 200px;">
                                <small class="text-muted d-block">Metode Pembayaran</small>
                                <div class="fw-bold text-dark">
                                    @if($salary->payment_method == 'transfer')
                                        <i class="mdi mdi-bank text-primary"></i> Transfer Bank
                                    @else
                                        <i class="mdi mdi-cash text-success"></i> Tunai (Cash)
                                    @endif
                                </div>
                                @if($salary->payment_method == 'transfer' && $salary->user->employeeSalary)
                                    <div class="mt-1 small border-top pt-1">
                                        <div class="fw-bold">{{ $salary->user->employeeSalary->bank_name ?? '-' }}</div>
                                        <div class="font-monospace text-muted">
                                            {{ $salary->user->employeeSalary->bank_account_number ?? '-' }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Payroll --}}
                    @if($salary->notes)
                        <div class="alert alert-secondary mb-4 p-3 border-start border-4 border-secondary">
                            <h6 class="fw-bold text-secondary mb-1"><i class="mdi mdi-note-text-outline"></i> Catatan
                                Payroll
                            </h6>
                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $salary->notes }}</p>
                        </div>
                    @endif

                    {{-- TABEL RINCIAN --}}
                    <div class="table-responsive mb-4 border rounded">
                        <table class="table table-borderless mb-0">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="py-3 ps-4 text-uppercase text-secondary">PENERIMAAN (INCOME)</th>
                                    <th class="py-3 text-end pe-4 text-uppercase text-secondary">JUMLAH (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($salary->employee_basic_salary > 0)
                                    <tr>
                                        <td class="ps-4">Gaji Pokok / Insentif Tetap</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->employee_basic_salary, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->employee_position_allowance > 0)
                                    <tr>
                                        <td class="ps-4">Tunjangan Jabatan</td>
                                        <td class="text-end pe-4">Rp
                                            {{ number_format($salary->employee_position_allowance, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->employee_owner_privilege > 0)
                                    <tr>
                                        <td class="ps-4">Privilege Owner</td>
                                        <td class="text-end pe-4">Rp
                                            {{ number_format($salary->employee_owner_privilege, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->promotor_bonus > 0)
                                    <tr>
                                        <td class="ps-4">Bonus / Insentif Tambahan</td>
                                        <td class="text-end pe-4 text-success">Rp
                                            {{ number_format($salary->promotor_bonus, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->dispensation_amount > 0)
                                    <tr>
                                        <td class="ps-4">Dispensasi / Lainnya <br><small
                                                class="text-muted fst-italic">({{ $salary->dispensation_note }})</small>
                                        </td>
                                        <td class="text-end pe-4">Rp
                                            {{ number_format($salary->dispensation_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            {{-- POTONGAN --}}
                            <thead class="bg-light border-top border-bottom">
                                <tr>
                                    <th class="py-3 ps-4 text-uppercase text-danger">POTONGAN (DEDUCTION)</th>
                                    <th class="py-3 text-end pe-4 text-uppercase text-danger">JUMLAH (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($salary->alpha_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Alpha ({{ $salary->alpha_days }} Hari)</td>
                                        <td class="text-end pe-4 text-danger">(Rp
                                            {{ number_format($salary->alpha_deduction, 0, ',', '.') }})
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->late_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Telat ({{ $salary->late_days }} Kali)</td>
                                        <td class="text-end pe-4 text-danger">(Rp
                                            {{ number_format($salary->late_deduction, 0, ',', '.') }})
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->cuti_lebih_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Cuti Lebih ({{ $salary->cuti_lebih_days ?? 0 }} Hari)
                                            <br><small class="text-muted fst-italic">(Melebihi jatah 12 hari/tahun)</small></td>
                                        <td class="text-end pe-4 text-danger fw-bold">(Rp
                                            {{ number_format($salary->cuti_lebih_deduction, 0, ',', '.') }})
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->kasbon_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Kasbon / Hutang</td>
                                        <td class="text-end pe-4 text-danger fw-bold">(Rp
                                            {{ number_format($salary->kasbon_deduction, 0, ',', '.') }})
                                        </td>
                                    </tr>
                                @endif

                                @if($salary->other_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Lain <br><small
                                                class="text-muted fst-italic">({{ $salary->other_deduction_note }})</small>
                                        </td>
                                        <td class="text-end pe-4 text-danger">(Rp
                                            {{ number_format($salary->other_deduction, 0, ',', '.') }})
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- TOTAL AKHIR --}}
                    <div class="row justify-content-end mb-5">
                        <div class="col-md-6">
                            <div class="bg-primary text-white p-4 rounded shadow-sm text-center">
                                <h6 class="text-white-50 text-uppercase mb-2">TAKE HOME PAY (DITERIMA BERSIH)</h6>
                                <h2 class="fw-bold mb-0 display-5">Rp
                                    {{ number_format($salary->total_amount, 0, ',', '.') }}
                                </h2>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER / TTD --}}
                    <div class="row mt-5 pt-4">
                        <div class="col-6 text-center">
                            <p class="mb-5 text-muted">Penerima,</p>
                            <br>
                            <p class="fw-bold text-dark text-decoration-underline">{{ $salary->user->name }}</p>
                        </div>
                        <div class="col-6 text-center">
                            <p class="mb-5 text-muted">Finance / HRD,</p>
                            <br>
                            <p class="fw-bold text-dark text-decoration-underline">
                                {{ $salary->created_by_user->name ?? 'Admin PStore' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-center mt-5 pt-4 border-top">
                        <small class="text-muted fst-italic">Dokumen ini sah dan dicetak secara otomatis oleh sistem
                            Absensi
                            PStore.</small>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {

            .no-print,
            .sidebar,
            .navbar,
            footer {
                display: none !important;
            }

            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endsection