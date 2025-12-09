@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Data Pembayaran Cicilan</h4>
                <p class="text-muted mb-4">
                    Peminjam: <strong>{{ $installment->cashAdvance->user->name }}</strong><br>
                    ID Transaksi: #INS-{{ $installment->id }}
                </p>

                <form action="{{ route('kasbon.installment.update', $installment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Edit Nominal --}}
                    <div class="form-group">
                        <label>Nominal Pembayaran (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="amount_paid" class="form-control" value="{{ $installment->amount_paid }}" required>
                        </div>
                        <small class="text-danger">
                            <i class="mdi mdi-alert-circle"></i> 
                            Hati-hati: Mengubah nominal yang sudah "Diterima" akan otomatis menghitung ulang sisa hutang user.
                        </small>
                    </div>

                    {{-- Edit Penerima --}}
                    <div class="form-group">
                        <label>Diterima Oleh</label>
                        <input type="text" name="received_by" class="form-control" value="{{ $installment->received_by }}" required>
                    </div>

                    {{-- Edit Status --}}
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" {{ $installment->status == 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                            <option value="approved" {{ $installment->status == 'approved' ? 'selected' : '' }}>Approved (Diterima)</option>
                            <option value="rejected" {{ $installment->status == 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                        </select>
                    </div>

                    {{-- Edit Catatan --}}
                    <div class="form-group">
                        <label>Catatan Admin (Opsional)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Alasan penolakan atau catatan tambahan">{{ $installment->note }}</textarea>
                    </div>

                    {{-- Bukti Saat Ini --}}
                    <div class="mt-3 p-3 bg-light rounded border">
                        <label class="mb-2 font-weight-bold">Bukti Bayar Saat Ini:</label><br>
                        <a href="{{ asset('storage/'.$installment->payment_proof) }}" target="_blank">
                            <img src="{{ asset('storage/'.$installment->payment_proof) }}" class="img-thumbnail" style="height: 150px; object-fit: cover;">
                        </a>
                        <div class="mt-2">
                            <a href="{{ asset('storage/'.$installment->payment_proof) }}" target="_blank" class="btn btn-sm btn-info text-white">Lihat Full Size</a>
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-3 text-end">
                        <a href="{{ route('kasbon.show', $installment->cash_advance_id) }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection