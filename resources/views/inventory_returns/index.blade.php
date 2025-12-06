@extends('layout.master')

@section('title', 'Riwayat Pengembalian')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title mb-1">Riwayat Pengembalian Inventaris</h4>
                        <p class="card-description mb-0 text-muted">Verifikasi barang yang dikembalikan oleh user.</p>
                    </div>
                </div>
                
                {{-- Alert Notifications --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th style="min-width: 140px;">Dokumentasi Aset (Awal)</th> {{-- FOTO MASTER --}}
                                <th>Barang</th>
                                <th>Pengembali & Penerima</th> {{-- USER & RECEIVER --}}
                                <th style="min-width: 100px;">Bukti Return</th> {{-- FOTO SAAT RETURN --}}
                                <th>Catatan</th>
                                <th>Status</th>
                                <th style="min-width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                            <tr>
                                {{-- 1. TANGGAL --}}
                                <td>
                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($return->return_date)->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($return->return_date)->format('H:i') }} WIB</small>
                                </td>

                                {{-- 2. DOKUMENTASI AWAL (REFERENSI) --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Foto Fisik Barang (Master) --}}
                                        <div class="text-center">
                                            @if($return->inventory && $return->inventory->item_photo_path)
                                                <img src="{{ asset('storage/'.$return->inventory->item_photo_path) }}" 
                                                     class="cursor-pointer"
                                                     style="width: 45px; height: 45px; border-radius: 6px; object-fit: cover; border: 1px solid #dee2e6;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#imagePreviewModal"
                                                     data-bs-image="{{ asset('storage/'.$return->inventory->item_photo_path) }}"
                                                     title="Foto Fisik Barang (Master)">
                                            @else
                                                <div class="bg-secondary d-flex align-items-center justify-content-center text-white rounded" 
                                                     style="width: 45px; height: 45px;" title="Tidak ada foto barang"><i class="mdi mdi-image-off"></i></div>
                                            @endif
                                            <div style="font-size: 9px;" class="text-muted mt-1">Brg Awal</div>
                                        </div>

                                        {{-- Foto User+Barang (Master) --}}
                                        <div class="text-center">
                                            @if($return->inventory && $return->inventory->user_item_photo_path)
                                                <img src="{{ asset('storage/'.$return->inventory->user_item_photo_path) }}" 
                                                     class="cursor-pointer"
                                                     style="width: 45px; height: 45px; border-radius: 6px; object-fit: cover; border: 2px solid #57B657;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#imagePreviewModal"
                                                     data-bs-image="{{ asset('storage/'.$return->inventory->user_item_photo_path) }}"
                                                     title="Foto User Bersama Barang (Master)">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center text-muted border rounded" 
                                                     style="width: 45px; height: 45px;" title="Tidak ada foto user"><i class="mdi mdi-account-off"></i></div>
                                            @endif
                                            <div style="font-size: 9px;" class="text-success mt-1">User Awal</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 3. DETAIL BARANG --}}
                                <td>
                                    <span class="fw-bold text-primary">{{ $return->inventory->item_name ?? 'Barang Dihapus' }}</span>
                                    <br>
                                    <small class="text-muted">SN: {{ $return->inventory->serial_number ?? '-' }}</small>
                                </td>

                                {{-- 4. PENGEMBALI & PENERIMA --}}
                                <td>
                                    {{-- User yang mengembalikan --}}
                                    @if($return->user)
                                        <div class="fw-bold text-dark">{{ $return->user->name }}</div>
                                        <div class="text-muted small mb-2"><i class="mdi mdi-map-marker-radius"></i> {{ $return->user->branch->name ?? 'Non-Cabang' }}</div>
                                    @else
                                        <span class="text-danger fst-italic">User Terhapus</span>
                                    @endif

                                    {{-- PENERIMA FISIK (INFO PENTING) --}}
                                    @if($return->receiver_name)
                                        <div class="d-inline-block bg-info bg-opacity-10 text-info px-2 py-1 rounded small border border-info">
                                            <i class="mdi mdi-hand-right me-1"></i> 
                                            Diterima: <strong>{{ $return->receiver_name }}</strong>
                                        </div>
                                    @else
                                        <div class="text-danger small fst-italic">Nama penerima tidak dicatat</div>
                                    @endif
                                </td>

                                {{-- 5. BUKTI RETURN (FOTO SAAT INI) --}}
                                <td>
                                    <img src="{{ asset('storage/'.$return->photo_path) }}" 
                                         alt="Bukti" 
                                         class="img-thumbnail clickable-image shadow-sm"
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #ffaf00;"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imagePreviewModal"
                                         data-bs-image="{{ asset('storage/'.$return->photo_path) }}"
                                         title="Bukti Kondisi Saat Dikembalikan">
                                </td>

                                {{-- 6. CATATAN --}}
                                <td style="max-width: 150px;">
                                    <div class="text-wrap" style="font-size: 0.9rem;">
                                        {{ $return->note ?? '-' }}
                                    </div>
                                </td>

                                {{-- 7. STATUS --}}
                                <td>
                                    @if($return->status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="mdi mdi-clock-outline"></i> Pending</span>
                                    @elseif($return->status == 'approved')
                                        <span class="badge bg-success"><i class="mdi mdi-check-all"></i> Approved</span>
                                        <div style="font-size: 10px;" class="text-muted mt-1">Oleh: {{ $return->admin->name ?? 'Admin' }}</div>
                                    @else
                                        <span class="badge bg-danger"><i class="mdi mdi-close-circle"></i> Ditolak</span>
                                        <div style="font-size: 10px;" class="text-muted mt-1">Oleh: {{ $return->admin->name ?? 'Admin' }}</div>
                                    @endif
                                </td>

                                {{-- 8. AKSI (APPROVE & REJECT) --}}
                                <td>
                                    @if($return->status == 'pending')
                                        <div class="d-flex gap-2">
                                            {{-- Tombol Approve --}}
                                            <form action="{{ route('inventory-returns.approve', $return->id) }}" method="POST" onsubmit="return confirm('KONFIRMASI APPROVE:\n\nPastikan barang fisik sudah diterima oleh {{ $return->receiver_name }} dan kondisinya sesuai.\n\nStatus barang akan menjadi AVAILABLE di gudang.')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm text-white shadow-sm" title="Setujui Pengembalian">
                                                    <i class="mdi mdi-check"></i> Approve
                                                </button>
                                            </form>

                                            {{-- Tombol Reject (Trigger Modal) --}}
                                            <button type="button" class="btn btn-danger btn-sm text-white shadow-sm" title="Tolak Pengembalian" 
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal" 
                                                    data-id="{{ $return->id }}" data-item="{{ $return->inventory->item_name ?? '-' }}">
                                                <i class="mdi mdi-close"></i> Tolak
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic"><i class="mdi mdi-lock"></i> Selesai</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-file-document-box-multiple-outline" style="font-size: 3rem;"></i>
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

{{-- 1. MODAL PREVIEW IMAGE --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Preview Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center bg-light rounded m-2">
                <img id="previewImage" src="" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

{{-- 2. MODAL REJECT (ALASAN PENOLAKAN) --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="mdi mdi-alert-circle-outline"></i> Tolak Pengembalian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 text-small">
                        Anda akan menolak pengembalian untuk barang: <br>
                        <strong id="rejectItemName" class="text-dark"></strong>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_note" class="form-control" rows="4" required 
                                  placeholder="Contoh: Foto bukti buram, barang fisik belum sampai, atau salah input."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function(){
        
        // Logic Modal Preview Image
        var imageModal = document.getElementById('imagePreviewModal');
        imageModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var imageUrl = button.getAttribute('data-bs-image');
            var modalImage = imageModal.querySelector('#previewImage');
            modalImage.src = imageUrl;
        });
        
        // Bersihkan gambar saat modal tutup (hemat memori)
        imageModal.addEventListener('hidden.bs.modal', function () {
            var modalImage = imageModal.querySelector('#previewImage');
            modalImage.src = '';
        });

        // Logic Modal Reject
        var rejectModal = document.getElementById('rejectModal');
        rejectModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var itemName = button.getAttribute('data-item');
            
            var form = document.getElementById('rejectForm');
            // Pastikan route ini sesuai dengan web.php kamu
            form.action = '/inventory-returns/' + id + '/reject'; 
            
            document.getElementById('rejectItemName').textContent = itemName;
        });
    });
</script>
@endsection