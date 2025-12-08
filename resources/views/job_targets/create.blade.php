@extends('layout.master')

@section('title', 'Buat Target Baru')
@section('heading', 'Form Target Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('job-targets.index') }}" class="btn btn-light bg-white shadow-sm mb-3 border-0 rounded-3">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>

        <div class="card card-rounded shadow-sm border-0">
            <div class="card-body p-5">
                <h4 class="card-title text-primary fw-bold mb-4">✨ Buat Target & Pencapaian</h4>

                <form action="{{ route('job-targets.store') }}" method="POST">
                    @csrf
                    
                    {{-- SECTION 1: TIPE & CABANG --}}
                    <div class="bg-light rounded-4 p-4 mb-4">
                        <label class="fw-bold text-dark mb-3"><i class="mdi mdi-bullseye-arrow me-1"></i> Tipe Target</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small text-muted mb-1">Jenis Target</label>
                                <select name="type" id="create_type" class="form-select form-select-lg border-0 shadow-sm" onchange="toggleBranchSelect()">
                                    <option value="personal" selected>👤 Pribadi (Personal)</option>
                                    <option value="achievement">🏆 Pencapaian (Achievement)</option>
                                    
                                    @if(in_array(auth()->user()->role, ['admin', 'leader', 'audit']))
                                        <option value="team">🏢 Target Cabang / Tim</option>
                                    @endif
                                </select>
                            </div>

                            {{-- INPUT PILIH CABANG (Muncul jika Tipe = Team) --}}
                            @if(in_array(auth()->user()->role, ['admin', 'leader', 'audit']))
                                <div class="col-md-6 d-none" id="branch_select_container">
                                    <label class="small text-muted mb-1">Target Untuk Cabang</label>
                                    
                                    @if(auth()->user()->role == 'leader')
                                        {{-- KHUSUS LEADER: Cuma 1 Cabang (Readonly) --}}
                                        <input type="text" class="form-control form-control-lg border-0 shadow-sm bg-white" value="{{ auth()->user()->branch->name ?? '-' }}" readonly>
                                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                        <small class="text-muted fst-italic">*Anda hanya dapat membuat target untuk cabang Anda sendiri.</small>
                                    @else
                                        {{-- ADMIN & AUDIT: Dropdown Pilih Cabang --}}
                                        <select name="branch_id" class="form-select form-select-lg border-0 shadow-sm">
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION 2: PERIODE WAKTU (Style Kartu Pilihan) --}}
                    <div class="mb-4">
                        <label class="fw-bold text-dark mb-3"><i class="mdi mdi-calendar-clock me-1"></i> Periode Waktu</label>
                        
                        <div class="row g-3 mb-3">
                            {{-- Radio Button Styled as Cards --}}
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="period_type" id="pt_daily" value="daily" checked onchange="togglePeriodInput()">
                                <label class="btn btn-outline-light text-dark w-100 p-3 shadow-sm border text-start" for="pt_daily">
                                    <i class="mdi mdi-calendar-today text-primary mb-1 d-block fs-4"></i>
                                    <span class="fw-bold">Harian</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="period_type" id="pt_monthly" value="monthly" onchange="togglePeriodInput()">
                                <label class="btn btn-outline-light text-dark w-100 p-3 shadow-sm border text-start" for="pt_monthly">
                                    <i class="mdi mdi-calendar-month text-success mb-1 d-block fs-4"></i>
                                    <span class="fw-bold">Bulanan</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="period_type" id="pt_yearly" value="yearly" onchange="togglePeriodInput()">
                                <label class="btn btn-outline-light text-dark w-100 p-3 shadow-sm border text-start" for="pt_yearly">
                                    <i class="mdi mdi-calendar text-warning mb-1 d-block fs-4"></i>
                                    <span class="fw-bold">Tahunan</span>
                                </label>
                            </div>
                        </div>

                        {{-- INPUT TANGGAL DINAMIS --}}
                        <div class="bg-light rounded-4 p-4">
                            <div id="input_daily" class="period-input">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted">Dari Tanggal</label>
                                        <input type="date" name="daily_start" class="form-control border-0 shadow-sm" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Sampai Tanggal</label>
                                        <input type="date" name="daily_end" class="form-control border-0 shadow-sm" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                            <div id="input_monthly" class="period-input d-none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted">Dari Bulan</label>
                                        <input type="month" name="monthly_start" class="form-control border-0 shadow-sm" value="{{ date('Y-m') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Sampai Bulan</label>
                                        <input type="month" name="monthly_end" class="form-control border-0 shadow-sm" value="{{ date('Y-m') }}">
                                    </div>
                                </div>
                            </div>
                            <div id="input_yearly" class="period-input d-none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted">Dari Tahun</label>
                                        <input type="number" name="yearly_start" class="form-control border-0 shadow-sm" value="{{ date('Y') }}" min="2020">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Sampai Tahun</label>
                                        <input type="number" name="yearly_end" class="form-control border-0 shadow-sm" value="{{ date('Y') }}" min="2020">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: DETAIL --}}
                    <div class="mb-4">
                        <label class="fw-bold text-dark mb-3"><i class="mdi mdi-file-document-edit me-1"></i> Detail Target</label>
                        <div class="form-floating mb-3">
                            <input type="text" name="title" class="form-control border-0 shadow-sm bg-light" id="floatingInput" placeholder="Judul" required>
                            <label for="floatingInput">Judul Target (Contoh: Penjualan 100 Unit)</label>
                        </div>
                        <div class="form-floating">
                            <textarea name="description" class="form-control border-0 shadow-sm bg-light" placeholder="Deskripsi" id="floatingTextarea" style="height: 120px" required></textarea>
                            <label for="floatingTextarea">Deskripsi Lengkap & KPI</label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('job-targets.index') }}" class="btn btn-light btn-lg px-4 rounded-3">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5 rounded-3 fw-bold shadow-sm">Simpan Target</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleBranchSelect() {
        let type = document.getElementById('create_type').value;
        let branchContainer = document.getElementById('branch_select_container');
        
        if(branchContainer) {
            if (type === 'team') {
                branchContainer.classList.remove('d-none');
            } else {
                branchContainer.classList.add('d-none');
            }
        }
    }

    function togglePeriodInput() {
        document.getElementById('input_daily').classList.add('d-none');
        document.getElementById('input_monthly').classList.add('d-none');
        document.getElementById('input_yearly').classList.add('d-none');

        if (document.getElementById('pt_daily').checked) document.getElementById('input_daily').classList.remove('d-none');
        else if (document.getElementById('pt_monthly').checked) document.getElementById('input_monthly').classList.remove('d-none');
        else if (document.getElementById('pt_yearly').checked) document.getElementById('input_yearly').classList.remove('d-none');
    }
</script>
@endsection