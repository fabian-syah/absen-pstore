{{-- MODAL EXPORT RESIGN & NON KARYAWAN --}}
<div class="modal fade" id="exportResignModal" tabindex="-1" aria-labelledby="exportResignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="exportResignModalLabel">
                    <i class="mdi mdi-file-export-outline text-primary fs-4 me-2"></i> Export Data Resign & Non Karyawan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formExportResign">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                        <i class="mdi mdi-information-outline fs-5 me-2 flex-shrink-0"></i>
                        <div>
                            Pilih rentang tanggal untuk mengekspor daftar nama karyawan yang <strong>Resign (EX Karyawan)</strong> atau <strong>Non Karyawan</strong>.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
                            <input type="date" class="form-control" id="modal_export_start_date" value="2026-09-01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="modal_export_end_date" value="2026-10-31" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Kategori Data</label>
                        <select class="form-select" id="modal_export_category">
                            <option value="all" selected>Semua (Resign & Non Karyawan)</option>
                            <option value="resign">Hanya Karyawan Resign / EX Karyawan</option>
                            <option value="non_karyawan">Hanya User Non Karyawan</option>
                        </select>
                    </div>

                    <div class="bg-light p-2 rounded small text-muted">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Format Kolom:</span>
                            <span class="fw-semibold">Nama, ID/NIK, Kategori, Cabang, Divisi, Tanggal, Ket</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" onclick="submitResignExport('excel')">
                        <i class="mdi mdi-file-excel me-1"></i> Download Excel (.xlsx)
                    </button>
                    <button type="button" class="btn btn-danger btn-sm fw-bold shadow-sm" onclick="submitResignExport('pdf')">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submitResignExport(type) {
        const start = document.getElementById('modal_export_start_date').value;
        const end = document.getElementById('modal_export_end_date').value;
        const category = document.getElementById('modal_export_category').value;

        if (!start || !end) {
            alert('Silakan tentukan tanggal mulai dan tanggal selesai!');
            return;
        }

        let baseUrl = type === 'excel'
            ? "{{ route('reports.resign-nonkaryawan.export-excel') }}"
            : "{{ route('reports.resign-nonkaryawan.export-pdf') }}";

        const url = `${baseUrl}?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}&category=${encodeURIComponent(category)}`;
        window.location.href = url;
    }
</script>
