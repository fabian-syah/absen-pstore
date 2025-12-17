@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1 text-primary"><i class="mdi mdi-wallet me-2"></i>Riwayat Gaji Saya</h4>
                        <p class="text-muted small mb-0">Daftar slip gaji yang telah diterbitkan.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 rounded-start">Periode</th>
                                <th class="py-3">Tanggal Terbit</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Total Diterima (THP)</th>
                                <th class="py-3 text-center rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salaries as $salary)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">
                                            {{ \Carbon\Carbon::createFromDate($salary->year, $salary->month, 1)->isoFormat('MMMM Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <i class="mdi mdi-calendar-check text-muted me-1"></i>
                                        {{ $salary->published_at ? \Carbon\Carbon::parse($salary->published_at)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($salary->status == 'paid')
                                            <span class="badge bg-success rounded-pill px-3"><i class="mdi mdi-check-circle me-1"></i> Lunas</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill px-3"><i class="mdi mdi-clock-outline me-1"></i> Diproses</span>
                                        @endif
                                    </td>
                                    <td>
                                        <h5 class="fw-bold text-primary mb-0">
                                            Rp {{ number_format($salary->total_amount, 0, ',', '.') }}
                                        </h5>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('my-salary.show', $salary->id) }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold shadow-sm">
                                            <i class="mdi mdi-file-document-outline me-1"></i> Lihat Struk
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-light rounded-circle p-3 mb-2">
                                                <i class="mdi mdi-file-hidden text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="text-muted fw-bold">Belum ada riwayat gaji</h6>
                                            <small class="text-muted">Slip gaji akan muncul di sini setelah diterbitkan.</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    {{ $salaries->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection