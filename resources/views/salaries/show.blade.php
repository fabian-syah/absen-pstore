@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        
        {{-- AREA PRINT --}}
        <div class="card shadow" id="printableArea">
            <div class="card-body p-5 border">
                
                {{-- HEADER PERUSAHAAN --}}
                <div class="d-flex justify-content-between border-bottom pb-4 mb-4">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">PSTORE</h2>
                        <p class="text-muted mb-0">Divisi Finance & HRD</p>
                    </div>
                    <div class="text-end">
                        <h4 class="mb-1 text-uppercase fw-bold">Slip Gaji Karyawan</h4>
                        <p class="mb-0 text-muted">Periode: {{ \Carbon\Carbon::createFromDate($salary->year, $salary->month, 1)->isoFormat('MMMM Y') }}</p>
                    </div>
                </div>

                {{-- INFO KARYAWAN --}}
                <div class="row mb-5">
                    <div class="col-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-secondary ps-0" width="120">Nama</td>
                                <td class="fw-bold text-dark">: {{ $salary->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-0">ID Karyawan</td>
                                <td class="fw-bold text-dark">: {{ $salary->user->login_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-0">Jabatan</td>
                                <td class="fw-bold text-dark">: {{ $salary->user->division->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-secondary" width="120">Cabang</td>
                                <td class="fw-bold text-dark">: {{ $salary->user->branch->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Status</td>
                                <td class="fw-bold text-success">: LUNAS / DIBAYARKAN</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Tgl Cetak</td>
                                <td class="fw-bold text-dark">: {{ date('d F Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- TABEL RINCIAN --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead style="background-color: #f3f3f3;">
                            <tr>
                                <th class="text-center py-3" width="50%">PENERIMAAN (INCOME)</th>
                                <th class="text-center py-3" width="50%">POTONGAN (DEDUCTION)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                {{-- KOLOM KIRI: INCOMES --}}
                                <td class="align-top p-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Gaji Pokok</span>
                                        <span class="fw-bold">Rp {{ number_format($salary->employee_basic_salary, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tunjangan Jabatan</span>
                                        <span class="fw-bold">Rp {{ number_format($salary->employee_position_allowance, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Privilege Owner</span>
                                        <span class="fw-bold">Rp {{ number_format($salary->employee_owner_privilege, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Bonus / Insentif</span>
                                        <span class="fw-bold text-success">Rp {{ number_format($salary->promotor_bonus, 0, ',', '.') }}</span>
                                    </div>
                                    @if($salary->dispensation_amount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Dispensasi (Lain-lain)</span>
                                        <span class="fw-bold text-primary">Rp {{ number_format($salary->dispensation_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </td>

                                {{-- KOLOM KANAN: DEDUCTIONS --}}
                                <td class="align-top p-4">
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Alpha ({{ $salary->alpha_days }} Hari)</span>
                                        <span>(Rp {{ number_format($salary->alpha_deduction, 0, ',', '.') }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Telat ({{ $salary->late_days }} Kali)</span>
                                        <span>(Rp {{ number_format($salary->late_deduction, 0, ',', '.') }})</span>
                                    </div>
                                    @if($salary->kasbon_deduction > 0)
                                    <div class="d-flex justify-content-between mb-2 text-danger fw-bold">
                                        <span>Potongan Kasbon</span>
                                        <span>(Rp {{ number_format($salary->kasbon_deduction, 0, ',', '.') }})</span>
                                    </div>
                                    @endif
                                    @if($salary->other_deduction > 0)
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Potongan Lain</span>
                                        <span>(Rp {{ number_format($salary->other_deduction, 0, ',', '.') }})</span>
                                    </div>
                                    <small class="text-muted d-block text-end fst-italic">{{ $salary->other_deduction_note }}</small>
                                    @endif
                                </td>
                            </tr>
                            
                            {{-- SUB TOTAL --}}
                            <tr style="background-color: #fafafa;">
                                <td class="text-end fw-bold py-3">
                                    @php
                                        $totalIncome = $salary->employee_basic_salary + $salary->employee_position_allowance + $salary->employee_owner_privilege + $salary->promotor_bonus + $salary->dispensation_amount;
                                    @endphp
                                    Total: Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold py-3 text-danger">
                                    @php
                                        $totalDeduction = $salary->alpha_deduction + $salary->late_deduction + $salary->kasbon_deduction + $salary->other_deduction;
                                    @endphp
                                    Total: Rp {{ number_format($totalDeduction, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- TAKE HOME PAY --}}
                <div class="row justify-content-center mb-5">
                    <div class="col-md-10 text-center">
                        <div class="p-4 border border-primary rounded bg-light">
                            <h5 class="text-primary fw-bold mb-2">TAKE HOME PAY (DITERIMA BERSIH)</h5>
                            <h1 class="display-4 fw-bold text-dark mb-0">Rp {{ number_format($salary->total_amount, 0, ',', '.') }}</h1>
                            <p class="text-muted fst-italic mt-2 text-capitalize">
                                Terbilang: {{ NumberFormatter::create('id', NumberFormatter::SPELLOUT)->format($salary->total_amount) }} Rupiah
                            </p>
                        </div>
                    </div>
                </div>

                {{-- TANDA TANGAN --}}
                <div class="row mt-5 pt-4">
                    <div class="col-4 text-center">
                        <p class="mb-5">Diterima Oleh,</p>
                        <br><br>
                        <p class="fw-bold text-decoration-underline">{{ $salary->user->name }}</p>
                    </div>
                    <div class="col-4"></div>
                    <div class="col-4 text-center">
                        <p class="mb-5">Disetujui Oleh,</p>
                        <br><br>
                        <p class="fw-bold text-decoration-underline">{{ auth()->user()->name }}</p>
                        <small>Finance / HRD</small>
                    </div>
                </div>

            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="d-flex justify-content-center mt-4 gap-2 no-print">
            <button onclick="window.print()" class="btn btn-dark btn-lg shadow">
                <i class="mdi mdi-printer"></i> Cetak PDF
            </button>
            <a href="{{ route('branch-salary.index') }}" class="btn btn-light btn-lg border">
                Kembali ke Menu
            </a>
        </div>
        
    </div>
</div>

<style>
    @media print {
        /* Sembunyikan elemen navigasi saat print */
        .no-print, nav, .navbar, .sidebar, footer, .container-scroller > .container-fluid > .row > .col-md-3 {
            display: none !important;
        }
        body {
            background-color: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .content-wrapper {
            background: white !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        /* Pastikan background color tercetak (misal baris selang-seling) */
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact;
    }
</style>
@endsection