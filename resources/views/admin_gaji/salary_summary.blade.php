@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">🏦 Ringkasan Pengeluaran Master Gaji</h3>
                    <p class="text-muted mb-0">Tampilan total gaji kotor (sebelum potongan) per masing-masing cabang.</p>
                </div>
            </div>

            {{-- STATS CARDS --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm bg-primary text-white p-3 rounded-4">
                        <div class="card-body py-2">
                            <p class="mb-1 opacity-75">Total Seluruh Karyawan (Tersetting)</p>
                            <h2 class="fw-bold mb-0">{{ number_format($grandTotalEmployees, 0, ',', '.') }} Orang</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm bg-dark text-white p-3 rounded-4">
                        <div class="card-body py-2">
                            <p class="mb-1 opacity-75">Taksiran Pengeluaran Gaji Kotor (All Branch)</p>
                            <h2 class="fw-bold mb-0">Rp {{ number_format($grandTotalSalary, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLE CARD --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">📋 Daftar Pengeluaran Per Cabang</h5>
                    <div class="search-box position-relative">
                        <i class="mdi mdi-magnify position-absolute" style="top: 10px; left: 12px; color: #6c757d;"></i>
                        <input type="text" id="branchSearch" class="form-control form-control-sm ps-5 border-light" placeholder="Cari Nama Cabang..." style="border-radius: 20px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="summaryTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 fw-bold text-dark py-3" style="width: 50px;">NO</th>
                                    <th class="fw-bold text-dark py-3">CABANG / LOKASI</th>
                                    <th class="fw-bold text-dark py-3 text-center">JUMLAH KARYAWAN</th>
                                    <th class="fw-bold text-dark py-3 text-end pe-4">TOTAL GAJI (MASTER)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse($summary as $row)
                                <tr class="branch-row">
                                    <td class="ps-4">{{ $no++ }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-light text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="mdi mdi-office-building"></i>
                                            </div>
                                            <span class="fw-bold text-secondary branch-name">{{ $row->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-info border text-dark fw-bold px-3 py-2 rounded-3" style="font-size: 0.9rem;">
                                            {{ $row->employee_count }} <span class="small fw-normal">Org</span>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <h6 class="fw-bold text-dark mb-0">Rp {{ number_format($row->total_gross_salary, 0, ',', '.') }}</h6>
                                        <small class="text-muted" style="font-size: 0.75rem;">Estimasi Gaji Kotor</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="mdi mdi-alert-circle-outline text-muted fs-1 d-block mb-2"></i>
                                        <h5 class="text-muted">Data Tidak Tersedia</h5>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">TOTAL KESELURUHAN ({{ $summary->count() }} Cabang)</span>
                    <span class="fw-bold text-primary fs-5">Rp {{ number_format($grandTotalSalary, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-info { background-color: #e0f2fe; color: #0369a1; }
    .table thead th { border: none !important; font-size: 0.75rem; letter-spacing: 0.5px; }
    .table tbody td { border-bottom: 1px solid #f8f9fa; }
    .table tbody tr:hover { background-color: #fcfcfc; }
    #summaryTable .branch-name { font-size: 1rem; }
    .search-box input:focus { border-color: #4B49AC; box-shadow: none; }
</style>

<script>
    document.getElementById('branchSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('.branch-row');
        
        rows.forEach(row => {
            let name = row.querySelector('.branch-name').innerText.toLowerCase();
            if (name.includes(value)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>
@endsection
