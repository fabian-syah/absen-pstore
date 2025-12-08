@extends('layout.master')

@section('title', 'Buat Target Baru')
@section('heading', 'Form Target Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('job-targets.index') }}" class="btn btn-light bg-white shadow-sm mb-3 border-0 rounded-3 text-dark fw-bold">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>

        <div class="card card-rounded shadow-sm border-0">
            <div class="card-body p-5">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="mdi mdi-pencil-box-outline text-primary mdi-24px"></i>
                    </div>
                    <h4 class="card-title text-dark fw-bold mb-0">Buat Target & Pencapaian</h4>
                </div>

                <form action="{{ route('job-targets.store') }}" method="POST">
                    @csrf
                    
                    {{-- SECTION 1: TIPE & ASSIGNMENT --}}
                    {{-- Kita beri background abu-abu lembut, tapi INPUTNYA PUTIH agar JELAS --}}
                    <div class="bg-light rounded-4 p-4 mb-4 border">
                        <label class="fw-bold text-dark mb-3" style="font-size: 1.1rem">
                            <i class="mdi mdi-bullseye-arrow me-1 text-primary"></i> Tipe & Penerima
                        </label>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">Jenis Target</label>
                                {{-- Tambahkan bg-white dan text-dark --}}
                                <select name="type" id="create_type" class="form-select form-select-lg border-0 shadow-sm bg-white text-dark fw-bold" onchange="toggleBranchSelect()">
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
                                    <label class="small fw-bold text-muted mb-1">Target Untuk Cabang</label>
                                    
                                    @if(auth()->user()->role == 'leader')
                                        {{-- KHUSUS LEADER: Readonly tapi Background PUTIH agar terbaca --}}
                                        <input type="text" class="form-control form-control-lg border-0 shadow-sm bg-white text-dark fw-bold" value="{{ auth()->user()->branch->name ?? '-' }}" readonly>
                                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                        <small class="text-danger fw-bold mt-1 d-block" style="font-size: 0.75rem">*Otomatis terkunci ke cabang Anda.</small>
                                    @else
                                        {{-- ADMIN & AUDIT: Dropdown --}}
                                        <select name="branch_id" class="form-select form-select-lg border-0 shadow-sm bg-white text-dark fw-bold">
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION 2: PERIODE WAKTU --}}
                    <div class="mb-4">
                        <label class="fw-bold text-dark mb-3" style="font-size: 1.1rem">
                            <i class="mdi mdi-calendar-clock me-1 text-warning"></i> Periode Waktu
                        </label>
                        
                        <div class="row g-3 mb-3">
                            {{-- Radio Button Styled as Cards --}}
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="period_type" id="pt_daily" value="daily" checked onchange="togglePeriodInput()">
                                <label class="btn btn-outline-secondary bg-white text-dark w-100 p-3 shadow-sm border text-start h-100" for="pt_daily">
                                    <i class="mdi mdi-calendar-today text-primary mb-1 d-block fs-4"></i>
                                    <span class="fw-bold">Harian</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="period_type" id="pt_monthly" value="monthly" onchange="togglePeriodInput()">
                                <label class="btn btn-outline-secondary bg-white text-dark w-100 p-3 shadow-sm border text-start h-100" for="pt_monthly">
                                    <i class="mdi mdi-calendar-month text-success mb-1 d-block fs-4"></i>
                                    <span class="fw-bold">Bulanan</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="period_type" id="pt_yearly" value="yearly" onchange="togglePeriodInput()">
                                <label class="btn btn-outline-secondary bg-white text-dark w-100 p-3 shadow-sm border text-start h-100" for="pt_yearly">
                                    <i class="mdi mdi-calendar text-warning mb-1 d-block fs-4"></i>
                                    <span class="fw-bold">Tahunan</span>
                                </label>
                            </div>
                        </div>

                        {{-- INPUT TANGGAL DINAMIS --}}
                        {{-- Container tanggal juga diberi bg-light dan border agar terpisah visualnya --}}
                        <div class="bg-light rounded-4 p-4 border">
                            <div id="input_daily" class="period-input">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Dari Tanggal</label>
                                        <input type="date" name="daily_start" class="form-control border-0 shadow-sm bg-white text-dark" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Sampai Tanggal</label>
                                        <input type="date" name="daily_end" class="form-control border-0 shadow-sm bg-white text-dark" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                            <div id="input_monthly" class="period-input d-none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Dari Bulan</label>
                                        <input type="month" name="monthly_start" class="form-control border-0 shadow-sm bg-white text-dark" value="{{ date('Y-m') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Sampai Bulan</label>
                                        <input type="month" name="monthly_end" class="form-control border-0 shadow-sm bg-white text-dark" value="{{ date('Y-m') }}">
                                    </div>
                                </div>
                            </div>
                            <div id="input_yearly" class="period-input d-none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Dari Tahun</label>
                                        <input type="number" name="yearly_start" class="form-control border-0 shadow-sm bg-white text-dark" value="{{ date('Y') }}" min="2020">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Sampai Tahun</label>
                                        <input type="number" name="yearly_end" class="form-control border-0 shadow-sm bg-white text-dark" value="{{ date('Y') }}" min="2020">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: DETAIL --}}
                    <div class="mb-4">
                        <label class="fw-bold text-dark mb-3" style="font-size: 1.1rem">
                            <i class="mdi mdi-file-document-edit me-1 text-info"></i> Detail Target
                        </label>
                        
                        <div class="form-floating mb-3">
                            {{-- Input Title bg-white --}}
                            <input type="text" name="title" class="form-control border shadow-sm bg-white text-dark fw-bold" id="floatingInput" placeholder="Judul" required>
                            <label for="floatingInput" class="text-muted">Judul Target (Contoh: Penjualan 100 Unit)</label>
                        </div>
                        
                        <div class="form-floating">
                            {{-- Textarea bg-white --}}
                            <textarea name="description" class="form-control border shadow-sm bg-white text-dark" placeholder="Deskripsi" id="floatingTextarea" style="height: 120px" required></textarea>
                            <label for="floatingTextarea" class="text-muted">Deskripsi Lengkap & KPI</label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('job-targets.index') }}" class="btn btn-light btn-lg px-4 rounded-3 text-muted fw-bold border">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5 rounded-3 fw-bold shadow-lg text-white">
                            <i class="mdi mdi-check-circle me-1"></i> Simpan Target
                        </button>
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

<style>
    /* Custom CSS untuk memastikan radio button terlihat bagus saat aktif */
    .btn-check:checked + .btn-outline-secondary {
        background-color: #fff !important;
        border-color: #4b49ac !important; /* Warna Primary */
        color: #4b49ac !important;
        box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2) !important;
    }
    /* Pastikan input readonly tetap putih backgroundnya */
    input[readonly] {
        background-color: #fff !important;
        opacity: 1;
    }
</style>
@endsection