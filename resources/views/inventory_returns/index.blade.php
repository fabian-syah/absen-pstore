@extends('layout.master')

@section('title', 'Riwayat Pengembalian')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Riwayat Pengembalian Inventaris</h4>
                <p class="card-description">Daftar barang yang telah dikembalikan oleh karyawan.</p>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Dikembalikan Oleh</th>
                                <th>Diproses Oleh</th>
                                <th>Bukti Foto</th>
                                <th>Catatan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($return->return_date)->translatedFormat('d M Y') }}</td>
                                <td>
                                    <span class="fw-bold">{{ $return->inventory->item_name ?? 'Barang Dihapus' }}</span>
                                    <br>
                                    <small class="text-muted">{{ $return->inventory->serial_number ?? '-' }}</small>
                                </td>
                                <td>
                                    {{ $return->user->name ?? 'User Dihapus' }}
                                    <br><small class="text-muted">{{ $return->user->branch->name ?? '-' }}</small>
                                </td>
                                <td>{{ $return->admin->name ?? 'System' }}</td>
                                <td>
                                    <a href="{{ asset('storage/'.$return->photo_path) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$return->photo_path) }}" 
                                             alt="Bukti" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                    </a>
                                </td>
                                <td style="max-width: 200px; white-space: normal;">
                                    {{ $return->note ?? '-' }}
                                </td>
                                <td>
                                    <label class="badge badge-success">Approved</label>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Belum ada data pengembalian.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection