@extends('layout.master')
@section('title', 'Buat Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        {{-- Tombol Kembali --}}
        <a href="{{ route('job-targets.index') }}" class="btn btn-light bg-white shadow-sm mb-3 border-0 rounded-3 text-dark fw-bold">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="mdi mdi-pencil-plus text-primary mdi-24px"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Buat Target Baru</h4>
                        <small class="text-muted">Isi form di bawah ini dengan lengkap</small>
                    </div>
                </div>
                
                <form action="{{ route('job-targets.store') }}" method="POST">
                    @csrf
                    
                    {{-- 1. JENIS DATA --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2 text-dark small text-uppercase ls-1">Jenis Data</label>
                        <select name="type" id="typeSelect" class="form-select form-select-lg fw-bold border-secondary text-dark shadow-none" onchange="toggleFormElements()">
                            <option value="personal_target">🎯 Target Pribadi</option>
                            <option value="personal_achievement">🏅 Pencapaian Pribadi</option>
                            @if(auth()->user()->role == 'leader')
                                <option value="team_target">🏢 Target Cabang / Tim</option>
                                <option value="team_achievement">🏆 Pencapaian Cabang / Tim</option>
                            @endif
                        </select>
                    </div>

                    {{-- 2. CABANG (Leader Only) --}}
                    @if(auth()->user()->role == 'leader')
                        <div class="mb-4 d-none" id="branchSelectGroup">
                            <label class="fw-bold mb-2 small text-uppercase ls-1">Cabang Anda</label>
                            <input type="text" class="form-control fw-bold bg-light" value="{{ auth()->user()->branch->name ?? 'Cabang Tidak Terdeteksi' }}" readonly>
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        </div>
                    @endif

                    {{-- 3. LEVEL PRIORITAS --}}
                    <div class="mb-4" id="starLevelGroup">
                        <label class="fw-bold mb-2 d-block small text-uppercase ls-1">Prioritas (Level)</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star1" value="1" checked>
                                <label class="btn btn-outline-secondary w-100 h-100 rounded-3 p-3 text-start star-option" for="star1">
                                    <i class="mdi mdi-star-outline fs-4 d-block mb-1"></i> 
                                    <span class="fw-bold d-block small">Level 1</span>
                                    <small style="font-size: 10px">Standar</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star2" value="2">
                                <label class="btn btn-outline-warning w-100 h-100 rounded-3 p-3 text-start star-option" for="star2">
                                    <i class="mdi mdi-star-half fs-4 d-block mb-1"></i> 
                                    <span class="fw-bold d-block small">Level 2</span>
                                    <small style="font-size: 10px">Penting</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star3" value="3">
                                <label class="btn btn-outline-warning w-100 h-100 rounded-3 p-3 text-start star-option level-3-label" for="star3">
                                    <div class="d-flex mb-1 gap-1">
                                        <i class="mdi mdi-star"></i><i class="mdi mdi-star"></i><i class="mdi mdi-star"></i>
                                    </div>
                                    <span class="fw-bold d-block small text-uppercase">Level 3</span>
                                    <small class="fw-bold" style="font-size: 10px">Utama!</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 4. PERIODE WAKTU --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase ls-1">Periode Waktu</label>
                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="period_type" id="p_daily" value="daily" checked onclick="toggleDates('daily')">
                            <label class="btn btn-outline-primary py-2 fw-bold" for="p_daily">Harian</label>

                            <input type="radio" class="btn-check" name="period_type" id="p_monthly" value="monthly" onclick="toggleDates('monthly')">
                            <label class="btn btn-outline-primary py-2 fw-bold" for="p_monthly">Bulanan</label>

                            <input type="radio" class="btn-check" name="period_type" id="p_yearly" value="yearly" onclick="toggleDates('yearly')">
                            <label class="btn btn-outline-primary py-2 fw-bold" for="p_yearly">Tahunan</label>
                        </div>

                        {{-- Input Tanggal --}}
                        <div class="bg-light p-3 rounded-3 border">
                            <div id="date_daily">
                                <div class="row g-2">
                                    <div class="col-6"><label class="small text-muted fw-bold">Dari</label><input type="date" name="daily_start" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                    <div class="col-6"><label class="small text-muted fw-bold">Sampai</label><input type="date" name="daily_end" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                </div>
                            </div>
                            <div id="date_monthly" class="d-none">
                                <div class="row g-2">
                                    <div class="col-6"><label class="small text-muted fw-bold">Dari Bulan</label><input type="month" name="monthly_start" class="form-control" value="{{ date('Y-m') }}"></div>
                                    <div class="col-6"><label class="small text-muted fw-bold">Sampai Bulan</label><input type="month" name="monthly_end" class="form-control" value="{{ date('Y-m') }}"></div>
                                </div>
                            </div>
                            <div id="date_yearly" class="d-none">
                                <div class="row g-2">
                                    <div class="col-6"><label class="small text-muted fw-bold">Dari Tahun</label><input type="number" name="yearly_start" class="form-control" value="{{ date('Y') }}"></div>
                                    <div class="col-6"><label class="small text-muted fw-bold">Sampai Tahun</label><input type="number" name="yearly_end" class="form-control" value="{{ date('Y') }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. DETAIL TEXT --}}
                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-uppercase ls-1">Judul Target</label>
                        <input type="text" name="title" class="form-control form-control-lg fw-bold border-secondary" placeholder="Contoh: Penjualan 50 Unit" required>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase ls-1">Deskripsi Detail</label>
                        <textarea name="description" class="form-control border-secondary" rows="4" placeholder="Jelaskan detail KPI atau langkah pencapaian..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5 shadow-lg text-white hover-scale">
                        <i class="mdi mdi-check-circle me-1"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 1px; }
    .star-option { border-width: 2px; transition: all 0.2s; }
    
    /* Level 3 Active State - GOLD */
    .level-3-label { border-color: #FFD700; color: #bfa800; }
    #star3:checked + .level-3-label {
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%) !important;
        color: #000 !important; border-color: #d4af37 !important;
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4) !important;
        transform: translateY(-2px);
    }
    #star2:checked + label { background-color: #ffc107 !important; color: #000 !important; border-color: #ffc107 !important; }
    #star1:checked + label { background-color: #6c757d !important; color: #fff !important; border-color: #6c757d !important; }
    
    .hover-scale:hover { transform: scale(1.02); transition: transform 0.2s; }
</style>

<script>
    function toggleFormElements() {
        let type = document.getElementById('typeSelect').value;
        let starGroup = document.getElementById('starLevelGroup');
        let branchGroup = document.getElementById('branchSelectGroup');

        if (type.includes('achievement')) { starGroup.classList.add('d-none'); } else { starGroup.classList.remove('d-none'); }
        if (branchGroup) { if (type.includes('team')) { branchGroup.classList.remove('d-none'); } else { branchGroup.classList.add('d-none'); } }
    }
    function toggleDates(period) {
        document.getElementById('date_daily').classList.add('d-none');
        document.getElementById('date_monthly').classList.add('d-none');
        document.getElementById('date_yearly').classList.add('d-none');
        document.getElementById('date_' + period).classList.remove('d-none');
    }
</script>
@endsection