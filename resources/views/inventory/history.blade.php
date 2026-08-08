@extends('layout.master')

@section('title', $pageTitle ?? 'Riwayat Inventaris')

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $pageTitle ?? 'Riwayat Inventaris Saya' }}</h4>

                    {{-- TOMBOL NAVIGASI FILTER --}}
                    <div class="btn-group mb-4" role="group">
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-info">
                            <i class="mdi mdi-account-box"></i> Kembali ke Inventaris
                        </a>
                    </div>

                    {{-- NAV TABS --}}
                    <ul class="nav nav-tabs border-bottom-0" id="inventoryHistoryTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-top" id="dipakai-tab" data-bs-toggle="tab" data-bs-target="#dipakai" type="button" role="tab" aria-controls="dipakai" aria-selected="true" style="font-weight: bold; color: #17a2b8;">
                                <i class="mdi mdi-package-variant"></i> Sedang Dipakai
                                <span class="badge bg-info ms-2">{{ $activeInventories->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-top" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false" style="font-weight: bold; color: #ffc107;">
                                <i class="mdi mdi-clock-outline"></i> Menunggu ACC
                                <span class="badge bg-warning text-dark ms-2">{{ $pendingReturns->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-top" id="selesai-tab" data-bs-toggle="tab" data-bs-target="#selesai" type="button" role="tab" aria-controls="selesai" aria-selected="false" style="font-weight: bold; color: #28a745;">
                                <i class="mdi mdi-check-circle-outline"></i> Sudah Dikembalikan
                                <span class="badge bg-success ms-2">{{ $approvedReturns->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border rounded-bottom p-3 bg-white" id="inventoryHistoryTabContent">
                        
                        {{-- TAB 1: SEDANG DIPAKAI --}}
                        <div class="tab-pane fade show active" id="dipakai" role="tabpanel" aria-labelledby="dipakai-tab">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>SN</th>
                                            <th>Kondisi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activeInventories as $item)
                                            <tr>
                                                <td><span class="fw-bold">{{ $item->item_name }}</span></td>
                                                <td>{{ ucfirst($item->category) }}</td>
                                                <td>{{ $item->serial_number ?: '-' }}</td>
                                                <td><label class="badge badge-secondary">{{ $item->condition }}</label></td>
                                                <td>
                                                    <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-inverse-info btn-icon btn-sm" title="Lihat Detail">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-inverse-primary btn-icon btn-sm" title="Kembalikan" data-bs-toggle="modal" data-bs-target="#returnModal" data-id="{{ $item->id }}" data-name="{{ $item->item_name }}" data-user="{{ auth()->user()->name }}">
                                                        <i class="mdi mdi-keyboard-return"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">Tidak ada barang yang sedang Anda pakai.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 2: PENDING ACC --}}
                        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>Tgl Pengajuan</th>
                                            <th>Penerima (Fisik)</th>
                                            <th>Catatan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingReturns as $return)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $return->inventory->item_name ?? 'Barang Dihapus' }}</div>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</td>
                                                <td>{{ $return->receiver_name }}</td>
                                                <td>{{ $return->note ?: '-' }}</td>
                                                <td><label class="badge badge-warning">Pending ACC</label></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">Tidak ada pengajuan pengembalian yang menunggu ACC.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 3: SELESAI --}}
                        <div class="tab-pane fade" id="selesai" role="tabpanel" aria-labelledby="selesai-tab">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>Tgl Pengajuan</th>
                                            <th>Penerima (Fisik)</th>
                                            <th>Di-ACC Oleh</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($approvedReturns as $return)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $return->inventory->item_name ?? 'Barang Dihapus' }}</div>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</td>
                                                <td>{{ $return->receiver_name }}</td>
                                                <td>{{ $return->admin->name ?? '-' }}</td>
                                                <td><label class="badge badge-success">Selesai</label></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">Tidak ada riwayat barang yang sudah selesai dikembalikan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL RETURN --}}
    <div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengembalian Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="returnForm" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline"></i>
                            <strong>Proses:</strong> Upload bukti & nama penerima. Status akan menjadi
                            <strong>Pending</strong>.
                        </div>
                        <div class="form-group mb-3">
                            <label>Nama Barang</label>
                            <input type="text" id="modalItemName" class="form-control bg-light" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label>Pemilik Saat Ini</label>
                            <input type="text" id="modalUserName" class="form-control bg-light" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label>Nama Penerima (Fisik) <span class="text-danger">*</span></label>
                            <input type="text" name="receiver_name" class="form-control" required placeholder="Contoh: Pak Budi (Security) / Bu Siti (HRD)">
                        </div>
                        <div class="form-group mb-3">
                            <label>Bukti Foto <span class="text-danger">*</span></label>
                            <input type="file" name="return_photo" class="form-control" required accept="image/*">
                        </div>
                        <div class="form-group mb-3">
                            <label>Catatan</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Kondisi barang..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ajukan Pengembalian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var returnModal = document.getElementById('returnModal');
            if (returnModal) {
                returnModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var name = button.getAttribute('data-name');
                    var user = button.getAttribute('data-user');
                    var form = document.getElementById('returnForm');
                    
                    form.action = '/inventory/' + id + '/return';
                    document.getElementById('modalItemName').value = name;
                    document.getElementById('modalUserName').value = user;
                });
            }
        </script>
    @endpush
@endsection
