@extends('layout.master')

@section('title', 'Job Desk & Target')
@section('heading', 'Manajemen Target & Pencapaian')

@section('content')

{{-- ========================================================= --}}
{{-- HEADER & TOMBOL UTAMA (SATU AJA)                          --}}
{{-- ========================================================= --}}
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Overview Target</h4>
        
        {{-- INI SATU-SATUNYA TOMBOL BUAT TARGET --}}
        <button type="button" class="btn btn-primary btn-lg text-white fw-bold" onclick="openCreateModal()">
            <i class="mdi mdi-plus-circle me-1"></i> Buat Target Baru
        </button>
    </div>
</div>

{{-- ========================================================= --}}
{{-- BAGIAN 1: TARGET TIM / CABANG (Tab: Harian, Bulanan, Tahunan) --}}
{{-- ========================================================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-rounded">
            {{-- Hapus tombol di sini, sisa Judul saja --}}
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="mdi mdi-domain me-2"></i>Target Cabang / Tim</h5>
            </div>
            <div class="card-body">
                {{-- TABS PERIODE --}}
                <ul class="nav nav-tabs" id="branchTab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#branch-daily">Harian</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#branch-monthly">Bulanan</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#branch-yearly">Tahunan</a></li>
                </ul>

                <div class="tab-content pt-3">
                    @foreach(['daily', 'monthly', 'yearly'] as $period)
                        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="branch-{{ $period }}">
                            {{-- SUB SECTION: ON GOING --}}
                            <h6 class="text-warning fw-bold mt-2"><i class="mdi mdi-timer-sand"></i> On Going</h6>
                            @php
                                $ongoing = $teamTargets->where('period', $period)->where('status', '!=', 'completed');
                            @endphp
                            @include('job_targets.partials.target_list', ['targets' => $ongoing, 'allow_action' => true])

                            <hr>
                            
                            {{-- SUB SECTION: SELESAI / HISTORY --}}
                            <h6 class="text-success fw-bold"><i class="mdi mdi-check-circle"></i> Selesai / Riwayat</h6>
                            @php
                                $completed = $teamTargets->where('period', $period)->where('status', 'completed');
                            @endphp
                            @include('job_targets.partials.target_list', ['targets' => $completed, 'allow_action' => false])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- BAGIAN 2: TARGET PRIBADI (Private) --}}
{{-- ========================================================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-rounded border-info">
            {{-- Hapus tombol di sini, sisa Judul saja --}}
            <div class="card-header bg-info text-white">
                <h5 class="mb-0 text-white"><i class="mdi mdi-account-lock me-2"></i>Target Pribadi (Private)</h5>
            </div>
            <div class="card-body">
                @if($personalTargets->count() > 0)
                     @include('job_targets.partials.target_list', ['targets' => $personalTargets, 'allow_action' => true])
                @else
                    <div class="text-center text-muted py-3">Belum ada target pribadi.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- BAGIAN 3: PENCAPAIAN (Achievement) --}}
{{-- ========================================================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-rounded">
            {{-- Hapus tombol di sini, sisa Judul saja --}}
            <div class="card-header bg-success text-white">
                <h5 class="mb-0 text-white"><i class="mdi mdi-trophy me-2"></i>Pencapaian (Achievements)</h5>
            </div>
            <div class="card-body">
                 <ul class="nav nav-tabs" id="achieveTab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#achieve-daily">Harian</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#achieve-monthly">Bulanan</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#achieve-yearly">Tahunan</a></li>
                </ul>

                <div class="tab-content pt-3">
                    @foreach(['daily', 'monthly', 'yearly'] as $period)
                        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="achieve-{{ $period }}">
                             @php $achieveData = $achievements->where('period', $period); @endphp
                             @include('job_targets.partials.target_list', ['targets' => $achieveData, 'allow_action' => true])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL CREATE TARGET --}}
{{-- ========================================================= --}}
<div class="modal fade" id="createTargetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Target / Pencapaian Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('job-targets.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- 1. PILIH TIPE (Disini User milih mau buat Personal/Team/Achievement) --}}
                    <div class="form-group mb-3">
                        <label class="fw-bold">Tipe Target</label>
                        <select name="type" id="create_type" class="form-select" onchange="toggleTeamSelect()">
                            <option value="personal" selected>Pribadi (Khusus Diri Sendiri)</option>
                            <option value="achievement">Pencapaian (Achievement)</option>
                            
                            {{-- Hanya Admin/Audit/Leader yang bisa pilih Team --}}
                            @if(in_array(auth()->user()->role, ['admin', 'leader', 'audit']))
                                <option value="team">Target Cabang / Tim</option>
                            @endif
                        </select>
                    </div>

                    {{-- 2. ASSIGN TO (Hanya Muncul jika Team dipilih) --}}
                    @if(in_array(auth()->user()->role, ['admin', 'leader', 'audit']))
                        <div class="form-group mb-3 d-none" id="team_user_select">
                            <label>Tugaskan Kepada (Opsional - Default Diri Sendiri)</label>
                            <select name="user_id" class="form-select">
                                <option value="{{ auth()->user()->id }}">-- Diri Sendiri --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} - {{ $u->branch->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- 3. KATEGORI WAKTU --}}
                    <div class="form-group mb-3">
                        <label class="fw-bold">Kategori Waktu</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="period_type" value="daily" id="pt_daily" checked onchange="togglePeriodInput()">
                                <label class="form-check-label" for="pt_daily">Harian</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="period_type" value="monthly" id="pt_monthly" onchange="togglePeriodInput()">
                                <label class="form-check-label" for="pt_monthly">Bulanan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="period_type" value="yearly" id="pt_yearly" onchange="togglePeriodInput()">
                                <label class="form-check-label" for="pt_yearly">Tahunan</label>
                            </div>
                        </div>
                    </div>

                    {{-- 4. INPUT TANGGAL DINAMIS --}}
                    <div id="input_daily" class="period-input">
                        <div class="row">
                            <div class="col-6"><label>Dari Tanggal</label><input type="date" name="daily_start" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            <div class="col-6"><label>Sampai Tanggal</label><input type="date" name="daily_end" class="form-control" value="{{ date('Y-m-d') }}"></div>
                        </div>
                    </div>
                    <div id="input_monthly" class="period-input d-none">
                        <div class="row">
                            <div class="col-6"><label>Dari Bulan</label><input type="month" name="monthly_start" class="form-control" value="{{ date('Y-m') }}"></div>
                            <div class="col-6"><label>Sampai Bulan</label><input type="month" name="monthly_end" class="form-control" value="{{ date('Y-m') }}"></div>
                        </div>
                    </div>
                    <div id="input_yearly" class="period-input d-none">
                        <div class="row">
                            <div class="col-6"><label>Dari Tahun</label><input type="number" name="yearly_start" class="form-control" value="{{ date('Y') }}" min="2020"></div>
                            <div class="col-6"><label>Sampai Tahun</label><input type="number" name="yearly_end" class="form-control" value="{{ date('Y') }}" min="2020"></div>
                        </div>
                    </div>

                    {{-- 5. DETAIL --}}
                    <div class="form-group mt-3">
                        <label>Judul Target</label>
                        <input type="text" name="title" class="form-control" required placeholder="Contoh: Penjualan 100 Unit">
                    </div>
                    <div class="form-group mt-3">
                        <label>Deskripsi Detail</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="text-muted small mt-2">
                        Dibuat oleh: {{ auth()->user()->name }} | Tanggal: {{ date('d M Y') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Target</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL AKSI (5 TOMBOL) --}}
{{-- ========================================================= --}}
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">Update Hasil Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="actionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p class="fw-bold" id="actionTargetTitle">Judul Target...</p>
                    
                    {{-- Pilihan Outcome --}}
                    <div class="mb-3">
                        <label class="mb-2">Bagaimana hasil target ini?</label>
                        <select name="outcome" class="form-select fw-bold" required>
                            <option value="">-- Pilih Hasil --</option>
                            <option value="exceeded" class="text-primary">Melebihi Target (Exceeded)</option>
                            <option value="achieved" class="text-success">Target Tercapai (Achieved)</option>
                            <option value="partial" class="text-warning">Tercapai Sebagian (Partial)</option>
                            <option value="failed" class="text-danger">Gagal Tercapai (Failed)</option>
                            <option value="changed" class="text-secondary">Target Dirubah (Changed)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Keterangan Hasil <span class="text-danger">*</span></label>
                        <textarea name="completion_description" class="form-control" rows="3" required placeholder="Jelaskan kenapa hasilnya demikian..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Foto Bukti (Opsional - Otomatis Dikompres)</label>
                        <input type="file" name="evidence_photo" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Simpan Hasil</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
    // 1. Logic Modal Create (Default 'personal' atau sesuai parameter)
    function openCreateModal(type = 'personal') {
        // Set dropdown value (Default ke personal jika kosong)
        let select = document.getElementById('create_type');
        select.value = type;
        
        // Trigger event change manual untuk menampilkan/sembunyikan tim select
        toggleTeamSelect();
        
        var myModal = new bootstrap.Modal(document.getElementById('createTargetModal'));
        myModal.show();
    }

    function toggleTeamSelect() {
        let type = document.getElementById('create_type').value;
        let teamSelect = document.getElementById('team_user_select');
        if(teamSelect) {
            if (type === 'team') {
                teamSelect.classList.remove('d-none');
            } else {
                teamSelect.classList.add('d-none');
            }
        }
    }

    function togglePeriodInput() {
        // Sembunyikan semua
        document.getElementById('input_daily').classList.add('d-none');
        document.getElementById('input_monthly').classList.add('d-none');
        document.getElementById('input_yearly').classList.add('d-none');

        // Tampilkan yang dipilih
        if (document.getElementById('pt_daily').checked) {
            document.getElementById('input_daily').classList.remove('d-none');
        } else if (document.getElementById('pt_monthly').checked) {
            document.getElementById('input_monthly').classList.remove('d-none');
        } else if (document.getElementById('pt_yearly').checked) {
            document.getElementById('input_yearly').classList.remove('d-none');
        }
    }

    // 2. Logic Modal Action
    function openActionModal(id, title) {
        document.getElementById('actionTargetTitle').innerText = title;
        // Set Action URL
        let form = document.getElementById('actionForm');
        form.action = "/job-targets/" + id + "/update-outcome";
        
        var myModal = new bootstrap.Modal(document.getElementById('actionModal'));
        myModal.show();
    }
</script>

@endsection