@extends('layout.master')

@section('title', 'Riwayat Pengembalian')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Riwayat Pengembalian Inventaris</h4>
                <p class="card-description">Daftar barang yang dikembalikan. Setujui untuk melepas barang dari user.</p>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal Req</th>
                                <th>Barang</th>
                                <th>Dikembalikan Oleh</th>
                                <th>Diproses Oleh</th>
                                <th>Bukti Foto</th>
                                <th>Catatan</th>
                                <th>Status</th>
                                <th>Aksi</th>
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
                                    <div class="fw-bold">{{ $return->user->name ?? 'User Dihapus' }}</div>
                                    <small class="text-muted">{{ $return->user->branch->name ?? '-' }}</small>
                                </td>
                                <td>
                                    {{-- Kolom Admin yang memproses --}}
                                    @if($return->admin)
                                        {{ $return->admin->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ asset('storage/'.$return->photo_path) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$return->photo_path) }}" 
                                             alt="Bukti" 
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                    </a>
                                </td>
                                <td style="max-width: 200px; white-space: normal;">
                                    {{ $return->note ?? '-' }}
                                </td>
                                <td>
                                    @if($return->status == 'pending')
                                        <label class="badge badge-warning">Menunggu Verifikasi</label>
                                    @elseif($return->status == 'approved')
                                        <label class="badge badge-success">Approved</label>
                                    @else
                                        <label class="badge badge-danger">Ditolak</label>
                                    @endif
                                </td>
                                <td>
                                    @if($return->status == 'pending')
                                        <form action="{{ route('inventory-returns.approve', $return->id) }}" method="POST" onsubmit="return confirm('Yakin barang sudah diterima fisik? Status barang akan menjadi Available.')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm text-white" title="Setujui">
                                                <i class="mdi mdi-check-circle"></i> Approve
                                            </button>
                                        </form>
                                    @else
                                        <i class="mdi mdi-check text-success"></i> Selesai
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">Belum ada data pengembalian.</td>
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