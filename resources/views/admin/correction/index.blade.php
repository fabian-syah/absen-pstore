@extends('layout.master') 
{{-- Pastikan layout utamanya sesuai (misal: layout.master atau layouts.app) --}}

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Koreksi Absensi</h2>
                    <p class="mb-md-0">Hapus data absen salah atau reset jam pulang (Khusus Admin).</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Filter Tanggal --}}
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body py-3">
                <form action="{{ route('admin.correction.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <label>Pilih Tanggal:</label>
                    <input type="date" name="date" class="form-control w-auto" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary text-white">Cari</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Data --}}
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Data Absensi: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th class="text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $item->user->name ?? 'User Terhapus' }}</span>
                                            <small class="text-muted">{{ $item->branch->name ?? 'Tanpa Cabang' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">{{ $item->check_in_time->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        @if($item->check_out_time)
                                            <span class="text-primary fw-bold">{{ $item->check_out_time->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">- Belum Pulang -</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status == 'late')
                                            <span class="badge badge-danger">Telat</span>
                                        @elseif($item->status == 'present')
                                            <span class="badge badge-success">Hadir</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $item->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            
                                            {{-- TOMBOL 1: RESET CHECKOUT (Hanya muncul jika sudah absen pulang) --}}
                                            @if($item->check_out_time)
                                                <form action="{{ route('admin.correction.reset-checkout', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mereset jam pulang {{ $item->user->name }}? User harus absen pulang lagi nanti.');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-warning text-dark" title="Hapus jam pulang saja">
                                                        <i class="mdi mdi-restore"></i> Reset Pulang
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- TOMBOL 2: HAPUS TOTAL --}}
                                            <form action="{{ route('admin.correction.destroy', $item->id) }}" method="POST" onsubmit="return confirm('HATI-HATI! Data absensi {{ $item->user->name }} akan dihapus PERMANEN. Lanjutkan?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger text-white" title="Hapus seluruh data hari ini">
                                                    <i class="mdi mdi-delete"></i> Hapus Data
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-calendar-blank" style="font-size: 2rem;"></i><br>
                                        Tidak ada data absensi pada tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection