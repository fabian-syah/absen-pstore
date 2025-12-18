@extends('layout.master')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-dark mb-0">Manajemen Kasbon</h3>
            <p class="text-muted">Kelola pengajuan dan pembayaran hutang karyawan.</p>
        </div>
        <a href="{{ route('kasbon.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="mdi mdi-plus-circle-outline"></i> Buat Pengajuan Baru
        </a>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Karyawan</th>
                            <th class="py-3">Divisi / Cabang</th>
                            <th class="py-3 text-end">Total Pinjam</th>
                            <th class="py-3 text-end">Sudah Bayar</th>
                            <th class="py-3 text-end">Sisa Hutang</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasbons as $k)
                        <tr>
                            <td>{{ $k->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $k->user_name }}</div>
                                <small class="text-muted">{{ $k->user->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $k->division }}</span>
                                <span class="badge bg-light text-dark border">{{ $k->branch }}</span>
                            </td>
                            <td class="text-end fw-bold text-primary">Rp {{ number_format($k->amount, 0,',','.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($k->total_paid, 0,',','.') }}</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($k->remaining_amount, 0,',','.') }}</td>
                            <td class="text-center">
                                @if($k->status == 'pending')
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pending</span>
                                @elseif($k->status == 'approved')
                                    <span class="badge bg-info text-white px-3 py-2 rounded-pill">Aktif/Nyicil</span>
                                @elseif($k->status == 'paid')
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill">Lunas</span>
                                @else
                                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill">Ditolak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('kasbon.show', $k->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    Detail / Bayar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Belum ada data kasbon.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection