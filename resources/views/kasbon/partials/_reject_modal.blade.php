{{-- Reject Modal Partial --}}
<div class="modal fade" id="rejectModal{{ $ins->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kasbon.installment.reject', $ins->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-body p-4 text-center">
                <h6 class="fw-bold mb-3">Tolak Pembayaran Ini?</h6>
                <p class="small text-muted mb-3">Dana tidak akan mengurangi hutang user.</p>
                <input type="text" name="reason" class="form-control mb-3" placeholder="Alasan penolakan (Wajib)..." required>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Permanen</button>
                </div>
            </div>
        </form>
    </div>
</div>
