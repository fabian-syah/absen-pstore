@extends('layout.master')

@section('title', 'Job Desk & Target')
@section('heading', 'Overview Target')

@section('content')

{{-- HEADER & STATS SIMPLE --}}
<div class="row mb-4 align-items-end">
    <div class="col-md-8">
        <h3 class="fw-bold text-dark">Manajemen Target</h3>
        <p class="text-muted mb-0">Monitor progres tim dan pencapaian pribadi Anda di sini.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        {{-- TOMBOL CREATE MENUJU HALAMAN BARU --}}
        <a href="{{ route('job-targets.create') }}" class="btn btn-primary btn-lg shadow-sm rounded-3 px-4 fw-bold">
            <i class="mdi mdi-plus me-1"></i> Buat Target
        </a>
    </div>
</div>

{{-- SECTION 1: TARGET CABANG / TIM --}}
<div class="row mb-5">
    <div class="col-12">
        <div class="card card-rounded shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="mdi mdi-domain text-primary mdi-24px"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Target Cabang / Tim</h5>
                    <small class="text-muted">Monitoring target divisi dan cabang</small>
                </div>
            </div>
            
            <div class="card-body p-4">
                {{-- TABS ESTETIK --}}
                <ul class="nav nav-pills nav-pills-custom mb-4" id="branchTab" role="tablist">
                    <li class="nav-item me-2">
                        <a class="nav-link active rounded-pill px-4 fw-bold" data-bs-toggle="pill" href="#branch-daily">Harian</a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link rounded-pill px-4 fw-bold" data-bs-toggle="pill" href="#branch-monthly">Bulanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 fw-bold" data-bs-toggle="pill" href="#branch-yearly">Tahunan</a>
                    </li>
                </ul>

                <div class="tab-content">
                    @foreach(['daily', 'monthly', 'yearly'] as $period)
                        <div class="tab-pane fade {{ $period == 'daily' ? 'show active' : '' }}" id="branch-{{ $period }}">
                            {{-- On Going --}}
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold small mb-3">🔥 Sedang Berjalan (On Going)</h6>
                                @php $ongoing = $teamTargets->where('period', $period)->where('status', '!=', 'completed'); @endphp
                                <div class="bg-light rounded-4 p-3 border-0">
                                    @include('job_targets.partials.target_list', ['targets' => $ongoing, 'allow_action' => true])
                                </div>
                            </div>
                            
                            {{-- History --}}
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold small mb-3">✅ Riwayat Selesai</h6>
                                @php $completed = $teamTargets->where('period', $period)->where('status', 'completed'); @endphp
                                @include('job_targets.partials.target_list', ['targets' => $completed, 'allow_action' => false])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 2: ROW UNTUK PRIBADI & ACHIEVEMENT (Split View agar rapi) --}}
<div class="row">
    {{-- KOLOM KIRI: TARGET PRIBADI --}}
    <div class="col-lg-6 mb-4">
        <div class="card card-rounded shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="mdi mdi-account-lock text-info mdi-24px"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Target Pribadi</h5>
                    <small class="text-muted">Hanya dapat dilihat oleh Anda</small>
                </div>
            </div>
            <div class="card-body p-0">
                @if($personalTargets->count() > 0)
                    <div class="p-3">
                        @include('job_targets.partials.target_list', ['targets' => $personalTargets, 'allow_action' => true])
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-clipboard-text-off mdi-48px text-light mb-2"></i>
                        <p>Belum ada target pribadi.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: PENCAPAIAN --}}
    <div class="col-lg-6 mb-4">
        <div class="card card-rounded shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="mdi mdi-trophy text-success mdi-24px"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Pencapaian</h5>
                    <small class="text-muted">Riwayat prestasi & awards</small>
                </div>
            </div>
            <div class="card-body p-3">
                <ul class="nav nav-pills nav-pills-custom mb-3 small" id="achieveTab" role="tablist">
                    <li class="nav-item me-1"><a class="nav-link active rounded-pill px-3" data-bs-toggle="pill" href="#achieve-daily">Harian</a></li>
                    <li class="nav-item me-1"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#achieve-monthly">Bulanan</a></li>
                    <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#achieve-yearly">Tahunan</a></li>
                </ul>

                <div class="tab-content">
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

{{-- MODAL AKSI (UPDATE HASIL) --}}
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-warning bg-opacity-10">
                <h5 class="modal-title text-warning fw-bold"><i class="mdi mdi-pencil-box me-1"></i> Update Hasil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="actionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <p class="fw-bold fs-5 text-dark mb-4" id="actionTargetTitle">Judul Target...</p>
                    
                    <div class="form-floating mb-3">
                        <select name="outcome" class="form-select border shadow-sm fw-bold text-dark bg-white" id="outcomeSelect" required>
                            <option value="">-- Pilih Hasil Akhir --</option>
                            <option value="exceeded">🚀 Melebihi Target (Exceeded)</option>
                            <option value="achieved">✅ Target Tercapai (Achieved)</option>
                            <option value="partial">⚠️ Tercapai Sebagian (Partial)</option>
                            <option value="failed">❌ Gagal Tercapai (Failed)</option>
                            <option value="changed">🔄 Target Dirubah (Changed)</option>
                        </select>
                        <label for="outcomeSelect">Status Pencapaian</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea name="completion_description" class="form-control border shadow-sm bg-white text-dark" style="height: 100px" id="descArea" required placeholder="Keterangan"></textarea>
                        <label for="descArea">Keterangan / Evaluasi</label>
                    </div>

                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-2">Foto Bukti (Opsional)</label>
                        <input type="file" name="evidence_photo" class="form-control border shadow-sm bg-white" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white fw-bold rounded-3 px-4 shadow-sm">Simpan Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- CSS KHUSUS --}}
<style>
    /* Styling Tabs */
    .nav-pills-custom .nav-link {
        color: #6c757d;
        background-color: transparent;
        transition: all 0.3s ease;
    }
    .nav-pills-custom .nav-link.active {
        background-color: #4b49ac; /* Warna Primary */
        color: #fff;
        box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2);
    }
    .card-rounded {
        border-radius: 15px !important;
        overflow: hidden;
    }

    /* FIX KONTRAS WARNA (PENTING) */
    .form-select option {
        color: #000000 !important;
        background-color: #ffffff !important;
    }
    .form-control:disabled, 
    .form-control[readonly] {
        background-color: #e9ecef !important;
        color: #212529 !important;
        opacity: 1;
        font-weight: 600;
    }
    
    /* Fix warna teks di dropdown modal */
    #outcomeSelect {
        color: #212529 !important;
    }
    
    /* Fix warna badge kuning agar teks hitam */
    .badge.bg-warning {
        color: #212529 !important;
    }
</style>

<script>
    function openActionModal(id, title) {
        document.getElementById('actionTargetTitle').innerText = title;
        let form = document.getElementById('actionForm');
        form.action = "/job-targets/" + id + "/update-outcome";
        var myModal = new bootstrap.Modal(document.getElementById('actionModal'));
        myModal.show();
    }
</script>

@endsection