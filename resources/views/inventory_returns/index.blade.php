@extends('layout.master')

@section('title', 'Riwayat Pengembalian')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Riwayat Pengembalian Inventaris</h4>
                <p class="card-description">
                    Daftar barang yang dikembalikan. 
                    <span class="text-muted">Setujui permintaan untuk mengubah status barang menjadi Available di gudang.</span>
                </p>
                
                {{-- Alert Sukses --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Alert Error --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal Req</th>
                                <th>Barang</th>
                                {{-- UPDATE: Judul Kolom Diperjelas --}}
                                <th>Penanggung Jawab</th> 
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
                                {{-- 1. Tanggal --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($return->return_date)->translatedFormat('d M Y') }}
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($return->return_date)->format('H:i') }} WIB</small>
                                </td>

                                {{-- 2. Detail Barang --}}
                                <td>
                                    <span class="fw-bold text-primary">{{ $return->inventory->item_name ?? 'Barang Dihapus' }}</span>
                                    <br>
                                    <small class="text-muted">SN: {{ $return->inventory->serial_number ?? '-' }}</small>
                                </td>

                                {{-- 3. Penanggung Jawab (User yg mengembalikan) --}}
                                <td>
                                    @if($return->user)
                                        <div class="fw-bold">{{ $return->user->name }}</div>
                                        <small class="text-muted">
                                            <i class="mdi mdi-map-marker-outline"></i> {{ $return->user->branch->name ?? 'Pusat' }}
                                        </small>
                                    @else
                                        <span class="text-danger fst-italic">User Terhapus</span>
                                    @endif
                                </td>

                                {{-- 4. Admin Eksekutor --}}
                                <td>
                                    @if($return->admin)
                                        <div class="fw-bold">{{ $return->admin->name }}</div>
                                        <small class="text-success">Admin</small>
                                    @else
                                        <span class="text-muted fst-italic">- Menunggu -</span>
                                    @endif
                                </td>

                                {{-- 5. Bukti Foto --}}
                                <td>
                                    <a href="{{ asset('storage/'.$return->photo_path) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$return->photo_path) }}" 
                                             alt="Bukti Return" 
                                             class="img-thumbnail"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    </a>
                                </td>

                                {{-- 6. Catatan --}}
                                <td style="max-width: 200px; white-space: normal;">
                                    {{ $return->note ?? '-' }}
                                </td>

                                {{-- 7. Status Badge --}}
                                <td>
                                    @if($return->status == 'pending')
                                        <label class="badge badge-warning text-dark">
                                            <i class="mdi mdi-clock-outline"></i> PendingVerif
                                        </label>
                                    @elseif($return->status == 'approved')
                                        <label class="badge badge-success">
                                            <i class="mdi mdi-check-circle"></i> Approved
                                        </label>
                                    @else
                                        <label class="badge badge-danger">Ditolak</label>
                                    @endif
                                </td>

                                {{-- 8. Tombol Aksi --}}
                                <td>
                                    @if($return->status == 'pending')
                                        <form action="{{ route('inventory-returns.approve', $return->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang fisik sudah diterima? \n\nSetelah disetujui:\n1. Barang akan lepas dari {{ $return->user->name ?? 'User' }}.\n2. Status barang menjadi AVAILABLE (Gudang).')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm text-white shadow-sm" title="Terima Barang">
                                                <i class="mdi mdi-check"></i> Approve
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">
                                            <i class="mdi mdi-check-all text-success"></i> Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-file-document-box-outline" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Belum ada data pengembalian inventaris.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection