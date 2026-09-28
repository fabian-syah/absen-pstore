<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0">Update Hasil Pekerjaan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <form id="actionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 11px; letter-spacing: 0.05em;">Target yang Dinilai</small>
                        <div class="fw-bold text-dark" id="actionTargetTitle">Memuat...</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Status Pencapaian</label>
                        <select name="outcome" class="form-select border text-dark fw-semibold" required>
                            <option value="">-- Pilih Hasil Akhir --</option>
                            <option value="Melampaui Ekspektasi">Melampaui Ekspektasi (Luar Biasa)</option>
                            <option value="Tercapai Sempurna">Tercapai Sempurna (Sesuai Target)</option>
                            <option value="Tercapai Sebagian">Tercapai Sebagian (Belum Maksimal)</option>
                            <option value="Gagal Tercapai">Gagal Tercapai / Dibatalkan</option>
                            <option value="Target Diubah">Target Diubah / Revisi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Evaluasi / Keterangan Pencapaian</label>
                        <textarea name="completion_description" class="form-control border" style="height: 100px; resize: vertical;" required placeholder="Tuliskan catatan hasil pekerjaan atau kendala yang dihadapi..."></textarea>
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Bukti Foto Hasil (Opsional)</label>
                        <input type="file" name="evidence_photo" class="form-control border" accept="image/*">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">Format JPG/PNG, maksimal ukuran 2MB.</small>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-3 shadow-sm">Simpan Hasil</button>
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