@extends('layout.master')
@section('title', 'Buat Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-5">
                <h4 class="fw-bold mb-4">✨ Buat Target / Pencapaian Baru</h4>
                
                <form action="{{ route('job-targets.store') }}" method="POST">
                    @csrf
                    
                    {{-- 1. PILIH TIPE --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2">Jenis Data</label>
                        <select name="type" id="typeSelect" class="form-select form-select-lg fw-bold bg-light border-0" onchange="toggleFormElements()">
                            <option value="personal_target">🎯 Target Pribadi</option>
                            <option value="personal_achievement">🏅 Pencapaian Pribadi</option>
                            @if($canCreateTeam)
                                <option value="team_target">🏢 Target Cabang / Tim</option>
                                <option value="team_achievement">🏆 Pencapaian Cabang / Tim</option>
                            @endif
                        </select>
                    </div>

                    {{-- 2. PILIH CABANG (Jika Team) --}}
                    @if($canCreateTeam)
                        <div class="mb-4 d-none" id="branchSelectGroup">
                            <label class="fw-bold mb-2">Pilih Cabang</label>
                            <select name="branch_id" class="form-select border-secondary">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- 3. LEVEL PENTING (BINTANG) - Hanya untuk Target --}}
                    <div class="mb-4" id="starLevelGroup">
                        <label class="fw-bold mb-2 d-block">Tingkat Prioritas (Level Bintang)</label>
                        <div class="d-flex gap-3">
                            <input type="radio" class="btn-check" name="star_level" id="star1" value="1" checked>
                            <label class="btn btn-outline-secondary rounded-3 p-3 flex-fill text-start" for="star1">
                                <i class="mdi mdi-star text-muted fs-4"></i> 
                                <div class="fw-bold">Level 1</div>
                                <small>Standar</small>
                            </label>

                            <input type="radio" class="btn-check" name="star_level" id="star2" value="2">
                            <label class="btn btn-outline-warning rounded-3 p-3 flex-fill text-start" for="star2">
                                <i class="mdi mdi-star text-warning fs-4"></i> 
                                <div class="fw-bold">Level 2</div>
                                <small>Penting</small>
                            </label>

                            <input type="radio" class="btn-check" name="star_level" id="star3" value="3">
                            <label class="btn btn-outline-warning rounded-3 p-3 flex-fill text-start bg-white shadow-sm" for="star3" style="border: 2px solid #FFD700;">
                                <i class="mdi mdi-star text-warning fs-4"></i> 
                                <i class="mdi mdi-star text-warning fs-4"></i> 
                                <i class="mdi mdi-star text-warning fs-4"></i> 
                                <div class="fw-bold text-dark">Level 3</div>
                                <small class="text-dark fw-bold">Prioritas Utama!</small>
                            </label>
                        </div>
                    </div>

                    {{-- 4. PERIODE (Radio Button Harian/Bulanan/Tahunan) --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2">Periode Waktu</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="period_type" id="p_daily" value="daily" checked onclick="toggleDates('daily')">
                            <label class="btn btn-outline-primary py-2" for="p_daily">Harian</label>

                            <input type="radio" class="btn-check" name="period_type" id="p_monthly" value="monthly" onclick="toggleDates('monthly')">
                            <label class="btn btn-outline-primary py-2" for="p_monthly">Bulanan</label>

                            <input type="radio" class="btn-check" name="period_type" id="p_yearly" value="yearly" onclick="toggleDates('yearly')">
                            <label class="btn btn-outline-primary py-2" for="p_yearly">Tahunan</label>
                        </div>

                        {{-- Input Tanggal Dinamis --}}
                        <div class="mt-3 bg-light p-3 rounded-3 border">
                            {{-- Logic input date sama seperti sebelumnya, sesuaikan ID --}}
                            <div id="date_daily">
                                <div class="row">
                                    <div class="col"><label class="small">Dari</label><input type="date" name="daily_start" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                    <div class="col"><label class="small">Sampai</label><input type="date" name="daily_end" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                </div>
                            </div>
                            <div id="date_monthly" class="d-none">
                                <div class="row">
                                    <div class="col"><input type="month" name="monthly_start" class="form-control" value="{{ date('Y-m') }}"></div>
                                    <div class="col"><input type="month" name="monthly_end" class="form-control" value="{{ date('Y-m') }}"></div>
                                </div>
                            </div>
                            <div id="date_yearly" class="d-none">
                                <div class="row">
                                    <div class="col"><input type="number" name="yearly_start" class="form-control" value="{{ date('Y') }}"></div>
                                    <div class="col"><input type="number" name="yearly_end" class="form-control" value="{{ date('Y') }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. DETAIL TEXT --}}
                    <div class="mb-3">
                        <input type="text" name="title" class="form-control form-control-lg fw-bold" placeholder="Judul Target / Pencapaian" required>
                    </div>
                    <div class="mb-4">
                        <textarea name="description" class="form-control" rows="4" placeholder="Deskripsi detail, KPI, atau catatan pencapaian..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5 shadow-lg">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFormElements() {
        let type = document.getElementById('typeSelect').value;
        let starGroup = document.getElementById('starLevelGroup');
        let branchGroup = document.getElementById('branchSelectGroup');

        // Sembunyikan Bintang jika Achievement
        if (type.includes('achievement')) {
            starGroup.classList.add('d-none');
        } else {
            starGroup.classList.remove('d-none');
        }

        // Tampilkan Branch Select jika Team (dan element ada)
        if (branchGroup) {
            if (type.includes('team')) {
                branchGroup.classList.remove('d-none');
            } else {
                branchGroup.classList.add('d-none');
            }
        }
    }

    function toggleDates(period) {
        document.getElementById('date_daily').classList.add('d-none');
        document.getElementById('date_monthly').classList.add('d-none');
        document.getElementById('date_yearly').classList.add('d-none');
        document.getElementById('date_' + period).classList.remove('d-none');
    }
</script>
@endsection