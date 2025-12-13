<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">Update Hasil Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="actionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <h5 class="fw-bold mb-3 text-primary" id="actionTargetTitle">...</h5>
                    
                    <div class="form-floating mb-3">
                        <select name="outcome" class="form-select fw-bold" required>
                            <option value="">-- Pilih Hasil Akhir --</option>
                            <option value="Melampaui Ekspektasi">🚀 Melampaui Ekspektasi (Keren Banget)</option>
                            <option value="Tercapai Sempurna">✅ Tercapai Sempurna (Sesuai Target)</option>
                            <option value="Tercapai Sebagian">⚠️ Tercapai Sebagian (Butuh Perbaikan)</option>
                            <option value="Gagal Tercapai">❌ Gagal Tercapai / Batal</option>
                            <option value="Target Diubah">🔄 Target Diubah / Revisi</option>
                        </select>
                        <label>Status Pencapaian</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea name="completion_description" class="form-control" style="height: 100px" required placeholder="Ket"></textarea>
                        <label>Evaluasi / Keterangan</label>
                    </div>

                    <div>
                        <label class="small fw-bold text-muted mb-1">Bukti Foto (Opsional)</label>
                        <input type="file" name="evidence_photo" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 pe-4">
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3">Simpan Hasil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openActionModal(id, title) {
        document.getElementById('actionTargetTitle').innerText = title;
        let form = document.getElementById('actionForm');
        form.action = "/job-targets/" + id + "/update-outcome";
        var myModal = new bootstrap.Modal(document.getElementById('actionModal'));
        myModal.show();
    }
</script>