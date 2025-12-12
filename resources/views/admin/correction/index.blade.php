@extends('layouts.app') 

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>Koreksi Absensi</h2>
                    <p class="mb-md-0">Hapus data absen atau reset jam pulang user.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Tanggal --}}
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body py-3">
                <form action="{{ route('admin.correction.index') }}" method="GET" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary text-white">Cari Data</button>
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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Cabang</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6>{{ $item->user->name ?? 'User Terhapus' }}</h6>
                                                <small class="text-muted">{{ $item->user->role ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->branch->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-success">{{ $item->check_in_time->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        @if($item->check_out_time)
                                            <span class="badge badge-warning">{{ $item->check_out_time->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted">- Belum Pulang -</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->status_label }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            {{-- TOMBOL 1: RESET CHECKOUT (Hanya muncul jika sudah absen pulang) --}}
                                            @if($item->check_out_time)
                                                <form action="{{ route('admin.correction.reset-checkout', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mereset jam pulang? User harus absen pulang lagi nanti.');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Reset Jam Pulang">
                                                        <i class="mdi mdi-restore"></i> Reset Pulang
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- TOMBOL 2: HAPUS TOTAL --}}
                                            <form action="{{ route('admin.correction.destroy', $item->id) }}" method="POST" class="ms-2" onsubmit="return confirm('HATI-HATI! Data ini akan hilang permanen. Lanjutkan?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger text-white" title="Hapus Data Permanen">
                                                    <i class="mdi mdi-delete"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data absensi pada tanggal ini.</td>
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