@extends('layout.master')
@section('title', 'Buat Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ url()->previous() }}" class="btn btn-light bg-white shadow-sm mb-3 border-0 rounded-3 text-dark fw-bold">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="mdi mdi-account-plus text-primary mdi-24px"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Beri Target Baru</h4>
                        <small class="text-muted">Tentukan target untuk tim atau diri sendiri</small>
                    </div>
                </div>
                
                <form action="{{ route('job-targets.store') }}" method="POST">
                    @csrf
                    
                    {{-- Hidden: Redirect ke Branch jika ada (Dari Menu Cabang) --}}
                    @if(request('branch_id'))
                        <input type="hidden" name="redirect_to_branch" value="{{ request('branch_id') }}">
                        <input type="hidden" name="target_branch_id" value="{{ request('branch_id') }}">
                    @endif

                    {{-- 2. JENIS TARGET --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2 text-dark small text-uppercase ls-1">Jenis Target</label>
                        <select name="type" id="typeSelect" class="form-select fw-bold border-secondary text-dark" onchange="toggleAssignmentType()">
                            <option value="personal_target" selected>🎯 Target Pekerjaan (Job Desk)</option>
                            <option value="personal_achievement">🏅 Pencapaian / Prestasi (Individu)</option>
                            
                            {{-- Opsi TARGET CABANG/TIM --}}
                            {{-- LOGIKA: Muncul jika Leader, ATAU jika Admin/Audit sedang akses via Menu Cabang (ada request branch_id) --}}
                            @if(auth()->user()->role == 'leader' || (in_array(auth()->user()->role, ['admin', 'audit']) && request('branch_id')))
                                <option value="team_target" {{ request('type_preselect') == 'team' ? 'selected' : '' }}>🏢 Target Global Cabang (Tim)</option>
                                <option value="team_achievement">🏆 Pencapaian Tim (Cabang)</option>
                            @endif
                        </select>
                    </div>

                    {{-- 1. BAGIAN ASSIGNMENT (Dinamis) --}}
                    {{-- LOGIKA: Hanya muncul jika Controller mengirimkan data $branchMembers --}}
                    {{-- Controller sudah memfilter: Jika menu utama, $branchMembers dikosongkan untuk Admin/Audit --}}
                    @if(isset($branchMembers) && count($branchMembers) > 0)
                        
                        {{-- A. Jika Target Personal -> Pilih Orang --}}
                        <div id="userAssignmentRow" class="mb-4 bg-light p-3 rounded-3 border border-primary border-opacity-25">
                            <label class="fw-bold mb-2 text-primary small text-uppercase ls-1">
                                <i class="mdi mdi-account-arrow-right me-1"></i> Tugaskan Kepada (Penerima)
                            </label>
                            <select name="assign_user_id" class="form-select form-select-lg fw-bold border-primary shadow-none text-dark">
                                <option value="{{ auth()->user()->id }}">👤 Saya Sendiri (Pribadi)</option>
                                
                                {{-- Loop Members --}}
                                @foreach($branchMembers as $member)
                                    <option value="{{ $member->id }}" {{ request('assign_user_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} - {{ $member->division->name ?? 'Staff' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-2 d-block fst-italic">* Target ini akan masuk ke Job Desk individu yang dipilih.</small>
                        </div>

                        {{-- B. Jika Target Tim -> Info Cabang --}}
                        <div id="branchAssignmentRow" class="mb-4 bg-soft-warning p-3 rounded-3 border border-warning border-opacity-25 d-none">
                            <label class="fw-bold mb-2 text-warning text-dark small text-uppercase ls-1">
                                <i class="mdi mdi-bank me-1"></i> Ditugaskan Ke Lingkup Cabang
                            </label>

                            <div class="d-flex align-items-center bg-white p-3 rounded border">
                                <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3">
                                    <i class="mdi mdi-office-building text-warning mdi-24px"></i>
                                </div>
                                <div>
                                    @if(request('branch_id') && isset($branches) && $branches->first())
                                        <h5 class="mb-0 fw-bold text-dark">{{ $branches->first()->name }}</h5>
                                        <small class="text-muted">Target berlaku untuk seluruh tim di cabang ini.</small>
                                    @elseif(auth()->user()->branch)
                                        <h5 class="mb-0 fw-bold text-dark">{{ auth()->user()->branch->name }}</h5>
                                        <small class="text-muted">Target berlaku untuk seluruh tim di cabang ini.</small>
                                    @else
                                        <h5 class="mb-0 fw-bold text-dark">Seluruh Tim</h5>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- Jika Staff Biasa / Admin via Menu Utama (Hanya diri sendiri) --}}
                        <input type="hidden" name="assign_user_id" value="{{ auth()->user()->id }}">
                    @endif

                    {{-- 3. LEVEL & PERIODE (Sama seperti sebelumnya) --}}
                    <div class="mb-4" id="starLevelGroup">
                        <label class="fw-bold mb-2 d-block small text-uppercase ls-1">Prioritas (Level)</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star1" value="1" checked>
                                <label class="btn btn-outline-secondary w-100 h-100 rounded-3 p-3 text-start star-option" for="star1">Lvl 1 <small class="d-block">Standar</small></label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star2" value="2">
                                <label class="btn btn-outline-warning w-100 h-100 rounded-3 p-3 text-start star-option" for="star2">Lvl 2 <small class="d-block">Penting</small></label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star3" value="3">
                                <label class="btn btn-outline-warning w-100 h-100 rounded-3 p-3 text-start star-option level-3-label" for="star3">Lvl 3 <small class="d-block fw-bold">UTAMA!</small></label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase ls-1">Periode</label>
                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="period_type" id="p_daily" value="daily" checked onclick="toggleDates('daily')"><label class="btn btn-outline-primary py-2 fw-bold" for="p_daily">Harian</label>
                            <input type="radio" class="btn-check" name="period_type" id="p_monthly" value="monthly" onclick="toggleDates('monthly')"><label class="btn btn-outline-primary py-2 fw-bold" for="p_monthly">Bulanan</label>
                            <input type="radio" class="btn-check" name="period_type" id="p_yearly" value="yearly" onclick="toggleDates('yearly')"><label class="btn btn-outline-primary py-2 fw-bold" for="p_yearly">Tahunan</label>
                        </div>
                        <div class="bg-light p-3 rounded-3 border">
                            <div id="date_daily"><div class="row g-2"><div class="col-6"><input type="date" name="daily_start" class="form-control" value="{{ date('Y-m-d') }}"></div><div class="col-6"><input type="date" name="daily_end" class="form-control" value="{{ date('Y-m-d') }}"></div></div></div>
                            <div id="date_monthly" class="d-none"><div class="row g-2"><div class="col-6"><input type="month" name="monthly_start" class="form-control" value="{{ date('Y-m') }}"></div><div class="col-6"><input type="month" name="monthly_end" class="form-control" value="{{ date('Y-m') }}"></div></div></div>
                            <div id="date_yearly" class="d-none"><div class="row g-2"><div class="col-6"><input type="number" name="yearly_start" class="form-control" value="{{ date('Y') }}"></div><div class="col-6"><input type="number" name="yearly_end" class="form-control" value="{{ date('Y') }}"></div></div></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-uppercase ls-1">Judul Target</label>
                        <input type="text" name="title" class="form-control form-control-lg fw-bold border-secondary" required>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase ls-1">Deskripsi</label>
                        <textarea name="description" class="form-control border-secondary" rows="4" required></textarea>
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
    .level-3-label { border-color: #FFD700; color: #bfa800; }
    #star3:checked + .level-3-label { background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%) !important; color: #000 !important; border-color: #d4af37 !important; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,215,0,0.4); }
    #star2:checked + label { background-color: #ffc107 !important; color: #000 !important; }
    #star1:checked + label { background-color: #6c757d !important; color: #fff !important; }
    .hover-scale:hover { transform: scale(1.01); transition: transform 0.2s; }
    .bg-soft-warning { background-color: #fff8e1; }
</style>

<script>
    function toggleAssignmentType() {
        let type = document.getElementById('typeSelect').value;
        let userRow = document.getElementById('userAssignmentRow');
        let branchRow = document.getElementById('branchAssignmentRow');
        let starGroup = document.getElementById('starLevelGroup');

        if (userRow && branchRow) {
            if (type.includes('team')) {
                userRow.classList.add('d-none');
                branchRow.classList.remove('d-none');
            } else {
                userRow.classList.remove('d-none');
                branchRow.classList.add('d-none');
            }
        }

        if (type.includes('achievement')) { 
            starGroup.classList.add('d-none'); 
        } else { 
            starGroup.classList.remove('d-none'); 
        }
    }

    function toggleDates(period) {
        document.getElementById('date_daily').classList.add('d-none');
        document.getElementById('date_monthly').classList.add('d-none');
        document.getElementById('date_yearly').classList.add('d-none');
        document.getElementById('date_' + period).classList.remove('d-none');
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleAssignmentType();
    });
</script>
@endsection