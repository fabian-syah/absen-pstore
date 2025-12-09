<div class="modal fade" id="payModal{{ $loan->id }}" tabindex="-1" aria-labelledby="payModalLabel{{ $loan->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="payModalLabel{{ $loan->id }}">Bayar Cicilan Kasbon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('kasbon.installment.store', $loan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    
                    {{-- [BARU] Nama Peminjam (Penerima Kasbon) --}}
                    <div class="mb-3 p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Nama Peminjam:</span>
                            <span class="font-weight-bold text-dark">{{ $loan->user->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Sisa Kewajiban:</span>
                            <span class="font-weight-bold text-danger">Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Input Nominal dengan Format Rupiah --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nominal Pembayaran (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text font-weight-bold">Rp</span>
                            {{-- Script 'formatRupiahModal' dipanggil langsung di sini --}}
                            <input type="text" 
                                   name="amount_paid" 
                                   class="form-control form-control-lg font-weight-bold text-primary" 
                                   placeholder="0" 
                                   required
                                   autocomplete="off"
                                   onkeyup="formatRupiahModal(this)">
                        </div>
                        <small class="text-muted">Ketik angka saja (cth: 200000), otomatis menjadi 200.000</small>
                    </div>

                    {{-- Input Bukti --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Bukti Pembayaran (Transfer/Struk)</label>
                        <input type="file" name="payment_proof" class="form-control" required>
                        <small class="text-info">Format: JPG, PNG, PDF. Max 2MB.</small>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-send"></i> Kirim Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>