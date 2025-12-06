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
                    <span class="text-muted">Klik gambar untuk memperbesar.</span>
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

                                {{-- 3. Penanggung Jawab --}}
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

                                {{-- 5. Bukti Foto (MODIFIKASI DISINI) --}}
                                <td>
                                    <img src="{{ asset('storage/'.$return->photo_path) }}" 
                                         alt="Bukti Return" 
                                         class="img-thumbnail clickable-image"
                                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imagePreviewModal"
                                         data-bs-image="{{ asset('storage/'.$return->photo_path) }}"
                                         title="Klik untuk memperbesar">
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
                     {{ $returns->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW IMAGE (Pop Up) --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Bukti Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light">
                <img id="previewImage" src="" alt="Preview" class="img-fluid" style="max-height: 80vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT KHUSUS HALAMAN INI --}}
<script>
    document.addEventListener("DOMContentLoaded", function(){
        var imageModal = document.getElementById('imagePreviewModal');
        
        // Event Listener saat modal akan dibuka
        imageModal.addEventListener('show.bs.modal', function (event) {
            // Tombol (gambar) yang memicu modal
            var button = event.relatedTarget;
            // Ambil URL gambar dari atribut data-bs-image
            var imageUrl = button.getAttribute('data-bs-image');
            
            // Update src gambar di dalam modal
            var modalImage = imageModal.querySelector('#previewImage');
            modalImage.src = imageUrl;
        });
        
        // Reset src saat modal ditutup (opsional, untuk kebersihan memori)
        imageModal.addEventListener('hidden.bs.modal', function () {
            var modalImage = imageModal.querySelector('#previewImage');
            modalImage.src = '';
        });
    });
</script>

@endsection