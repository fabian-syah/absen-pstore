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
                            <small class="text-muted">ID Transaksi:
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
                            </table>
                        </div>
                        <div class="col-6 text-end">
                            <div class="p-3 bg-light rounded d-inline-block text-start mb-2" style="min-width: 200px;">
                                <small class="text-muted d-block">Status Pembayaran</small>
                                @if($salary->status == 'paid')
                                    <h5 class="fw-bold text-success mb-0"><i class="mdi mdi-check-circle"></i> LUNAS (PAID)</h5>
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
                                            {{ $salary->user->employeeSalary->bank_account_number ?? '-' }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Payroll --}}
                    @if($salary->notes)
                        <div class="alert alert-secondary mb-4 p-3 border-start border-4 border-secondary">
                            <h6 class="fw-bold text-secondary mb-1"><i class="mdi mdi-note-text-outline"></i> Catatan Payroll
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
                                            {{ number_format($salary->employee_basic_salary, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->employee_position_allowance > 0)
                                    <tr>
                                        <td class="ps-4">Tunjangan Jabatan</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->employee_position_allowance, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->meal_allowance > 0)
                                    <tr>
                                        <td class="ps-4">Uang Makan</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->meal_allowance, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->transport_allowance > 0)
                                    <tr>
                                        <td class="ps-4">Uang Transport</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->transport_allowance, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->overtime > 0)
                                    <tr>
                                        <td class="ps-4">Lembur (Overtime)</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->overtime, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->bonus > 0)
                                    <tr>
                                        <td class="ps-4">Bonus / Insentif</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->bonus, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->other_income > 0)
                                    <tr>
                                        <td class="ps-4">Lain-lain (Pendapatan)</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->other_income, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>

                            <thead class="bg-light border-top border-bottom">
                                <tr>
                                    <th class="py-3 ps-4 text-uppercase text-secondary">POTONGAN (DEDUCTION)</th>
                                    <th class="py-3 text-end pe-4 text-uppercase text-secondary">JUMLAH (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($salary->bpjs_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan BPJS</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->bpjs_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->late_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Keterlambatan</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->late_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->loan_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Pinjaman / Kasbon</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->loan_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->other_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Lain-lain (Potongan)</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->other_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>

                            <tfoot class="bg-primary text-white">
                                <tr>
                                    <th class="py-3 ps-4 text-uppercase">TOTAL GAJI BERSIH (TAKE HOME PAY)</th>
                                    <th class="py-3 text-end pe-4 h4 mb-0">Rp
                                        {{ number_format($salary->total_salary, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- FOOTER TANDA TANGAN --}}
                    <div class="row mt-5 pt-4">
                        <div class="col-4 text-center">
                            <p class="mb-5">Penerima,</p>
                            <div class="mt-5 border-top pt-2 mx-auto" style="width: 150px;">
                                <p class="fw-bold mb-0">{{ $salary->user->name }}</p>
                            </div>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4 text-center">
                            <p class="mb-5">Jakarta, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>Finance & HRD,</p>
                            <div class="mt-5 border-top pt-2 mx-auto" style="width: 150px;">
                                <p class="fw-bold mb-0">Admin Payroll</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center text-muted small border-top pt-3">
                        <p>Ini adalah slip gaji elektronik yang dihasilkan secara otomatis oleh sistem.<br>Segala bentuk kesalahan data dapat dikonfirmasikan ke bagian HRD.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact;
            }
            .card {
                box-shadow: none !important;
                border: none !important;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .row {
                margin: 0 !important;
            }
            .col-md-8 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
@endsection