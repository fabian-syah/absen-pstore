<div class="modal fade" id="payModal{{ $loan->id }}" tabindex="-1" aria-labelledby="payModalLabel{{ $loan->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payModalLabel{{ $loan->id }}">Bayar Cicilan Kasbon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('kasbon.installment.store', $loan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Info Sisa Hutang --}}
                    <div class="alert alert-info">
                        Sisa Kewajiban: <strong>Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</strong>
                    </div>

                    {{-- Input Nominal dengan Format Rupiah --}}
                    <div class="form-group mb-3">
                        <label>Nominal Pembayaran (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            {{-- Kita pakai 'onkeyup' agar otomatis format rupiah saat diketik --}}
                            <input type="text" 
                                   name="amount_paid" 
                                   class="form-control" 
                                   placeholder="Masukkan nominal..." 
                                   required
                                   onkeyup="this.value = formatRupiahGlobal(this.value)">
                        </div>
                        <small class="text-muted">Masukkan nominal yang Anda bayar hari ini.</small>
                    </div>

                    {{-- Input Bukti --}}
                    <div class="form-group mb-3">
                        <label>Bukti Pembayaran (Transfer/Struk)</label>
                        <input type="file" name="payment_proof" class="form-control" required>
                        <small class="text-info">Format: JPG, PNG, PDF. Max 2MB.</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>