@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1 text-primary"><i class="mdi mdi-wallet me-2"></i>Riwayat Gaji Saya
                            </h4>
                            <p class="text-muted small mb-0">Daftar slip gaji yang telah diterbitkan.</p>
                        </div>
                    </div>

                    {{-- Filter Section --}}
                    <form action="{{ route('my-salary.index') }}" method="GET" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Bulan</label>
                                <select name="month" class="form-select form-select-sm">
                                    <option value="">Semua Bulan</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Tahun</label>
                                <select name="year" class="form-select form-select-sm">
                                    <option value="">Semua Tahun</option>
                                    @foreach(range(date('Y'), 2023) as $y)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Cabang</label>
                                    <select name="branch_id" class="form-select form-select-sm">
                                        <option value="">Semua Cabang</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Cari Karyawan</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Nama Karyawan..." value="{{ request('search') }}">
                                </div>
                            @endif

                           <div class="col-md-2 d-flex gap-1">
    {{-- Tombol Filter bisa dilihat semua orang --}}
    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
        <i class="mdi mdi-filter"></i> Filter
    </button>

    {{-- Tombol Export Excel dibungkus pengecekan Role --}}
    @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
        <a href="{{ route('my-salary.export', request()->all()) }}" class="btn btn-success btn-sm"
            title="Export Excel">
            <i class="mdi mdi-file-excel"></i>
        </a>
    @endif
</div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4 rounded-start">Periode</th>
                                    @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                                        <th class="py-3">Nama Karyawan</th>
                                        <th class="py-3">Cabang</th>
                                    @endif
                                    <th class="py-3">Tanggal Terbit</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3">Total Diterima (THP)</th>
                                    <th>Metode Bayar</th>
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
                                        @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                                            <td>
                                                <div class="fw-bold text-dark">{{ $salary->user->name ?? '-' }}</div>
                                                <small class="text-muted">{{ $salary->user->division->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light-info text-dark fw-bold border border-info rounded-pill px-3" style="background-color: #e0f7fa;">
                                                    {{ $salary->user->branch->name ?? '-' }}
                                                </span>
                                            </td>
                                        @endif
                                        <td>
                                            <i class="mdi mdi-calendar-check text-muted me-1"></i>
                                            {{ $salary->published_at ? \Carbon\Carbon::parse($salary->published_at)->format('d M Y') : '-' }}
                                        </td>
                                        <td>
                                            @if($salary->status == 'paid')
                                                <span class="badge bg-success rounded-pill px-3"><i
                                                        class="mdi mdi-check-circle me-1"></i> Lunas</span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-3"><i
                                                        class="mdi mdi-clock-outline me-1"></i> Diproses</span>
                                            @endif
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-primary mb-0">
                                                Rp {{ number_format($salary->total_amount, 0, ',', '.') }}
                                            </h5>
                                        </td>
                                        <td>
                                            @if($salary->payment_method == 'transfer')
                                                <span class="badge badge-opacity-primary"><i class="mdi mdi-bank"></i> Transfer</span>
                                            @else
                                                <span class="badge badge-opacity-success"><i class="mdi mdi-cash"></i> Tunai</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('my-salary.show', $salary->id) }}"
                                                class="btn btn-outline-primary btn-sm rounded-pill fw-bold shadow-sm">
                                                <i class="mdi mdi-file-document-outline me-1"></i> Lihat Struk
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ in_array(auth()->user()->role, ['admin', 'admin_gaji']) ? 7 : 5 }}"
                                            class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle p-3 mb-2">
                                                    <i class="mdi mdi-file-hidden text-muted" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted fw-bold">Belum ada riwayat gaji</h6>
                                                <small class="text-muted">Slip gaji akan muncul di sini setelah
                                                    diterbitkan.</small>
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