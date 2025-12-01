<div class="modal fade" id="createTeamTargetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Target Tim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('job-targets.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="team">
                {{-- Period di-set via JS saat tombol ditekan --}}
                <input type="hidden" name="period" id="modalPeriodInput">

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Judul Target <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Ditugaskan Kepada <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Anggota Tim --</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} - {{ $member->division->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Mulai Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6 mb-3">
                            <label>Deadline <span class="text-danger">*</span></label>
                            <input type="date" name="deadline" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setModalPeriod(period) {
        document.getElementById('modalPeriodInput').value = period;
    }
</script>