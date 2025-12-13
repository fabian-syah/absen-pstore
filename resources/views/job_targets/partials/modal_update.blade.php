<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark">Update Hasil Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="actionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 d-flex align-items-center mb-4">
                        <i class="mdi mdi-information-outline me-2 fs-4"></i>
                        <div>
                            <small class="text-uppercase fw-bold text-info" style="font-size: 10px;">Target</small>
                            <div class="fw-bold text-dark" id="actionTargetTitle">...</div>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <select name="outcome" class="form-select fw-bold border-secondary text-dark" required>
                            <option value="">-- Pilih Hasil Akhir --</option>
                            <option value="Melampaui Ekspektasi">🚀 Melampaui Ekspektasi (Luar Biasa)</option>
                            <option value="Tercapai Sempurna">✅ Tercapai Sempurna (Sesuai)</option>
                            <option value="Tercapai Sebagian">⚠️ Tercapai Sebagian (Kurang)</option>
                            <option value="Gagal Tercapai">❌ Gagal Tercapai / Batal</option>
                            <option value="Target Diubah">🔄 Target Diubah / Revisi</option>
                        </select>
                        <label>Status Pencapaian</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea name="completion_description" class="form-control border-secondary" style="height: 100px" required placeholder="Ket"></textarea>
                        <label>Evaluasi / Keterangan</label>
                    </div>

                    <div>
                        <label class="small fw-bold text-muted mb-1">Bukti Foto (Opsional)</label>
                        <input type="file" name="evidence_photo" class="form-control border-secondary">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 pe-4 bg-transparent">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm">Simpan Hasil</button>
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