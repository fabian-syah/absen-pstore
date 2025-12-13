@extends('layout.master')
@section('title', 'Buat Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        {{-- Tombol Kembali (Opsional, agar mudah navigasi) --}}
        <a href="{{ route('job-targets.index') }}" class="btn btn-light bg-white shadow-sm mb-3 border-0 rounded-3 text-dark fw-bold">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="mdi mdi-pencil-plus text-primary mdi-24px"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">Buat Target / Pencapaian</h4>
                </div>
                
                <form action="{{ route('job-targets.store') }}" method="POST">
                    @csrf
                    
                    {{-- 1. PILIH TIPE --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2 text-dark">Jenis Data</label>
                        {{-- FIX: Hapus bg-light, tambah border-secondary agar kontras dan teks jelas --}}
                        <select name="type" id="typeSelect" class="form-select form-select-lg fw-bold border-secondary text-dark" onchange="toggleFormElements()">
                            <option value="personal_target">🎯 Target Pribadi</option>
                            <option value="personal_achievement">🏅 Pencapaian Pribadi</option>
                            
                            {{-- LOGIC: HANYA LEADER YANG BISA NAMBAH TARGET CABANG --}}
                            @if(auth()->user()->role == 'leader')
                                <option value="team_target">🏢 Target Cabang / Tim</option>
                                <option value="team_achievement">🏆 Pencapaian Cabang / Tim</option>
                            @endif
                        </select>
                    </div>

                    {{-- 2. PILIH CABANG (Khusus Leader) --}}
                    {{-- Input hidden atau select otomatis untuk Leader --}}
                    @if(auth()->user()->role == 'leader')
                        <div class="mb-4 d-none" id="branchSelectGroup">
                            <label class="fw-bold mb-2">Cabang Anda</label>
                            {{-- Karena Leader hanya punya 1 cabang, kita buat Readonly agar tidak salah pilih --}}
                            <input type="text" class="form-control fw-bold bg-light" value="{{ auth()->user()->branch->name ?? 'Cabang Tidak Terdeteksi' }}" readonly>
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        </div>
                    @endif

                    {{-- 3. LEVEL PENTING (BINTANG) - Hanya untuk Target --}}
                    <div class="mb-4" id="starLevelGroup">
                        <label class="fw-bold mb-2 d-block">Tingkat Prioritas (Level Bintang)</label>
                        <div class="d-flex gap-3">
                            {{-- LEVEL 1 --}}
                            <input type="radio" class="btn-check" name="star_level" id="star1" value="1" checked>
                            <label class="btn btn-outline-secondary rounded-3 p-3 flex-fill text-start star-option" for="star1">
                                <i class="mdi mdi-star-outline fs-4 d-block mb-1"></i> 
                                <span class="fw-bold d-block">Level 1</span>
                                <small>Standar</small>
                            </label>

                            {{-- LEVEL 2 --}}
                            <input type="radio" class="btn-check" name="star_level" id="star2" value="2">
                            <label class="btn btn-outline-warning rounded-3 p-3 flex-fill text-start star-option" for="star2">
                                <i class="mdi mdi-star-half fs-4 d-block mb-1"></i> 
                                <span class="fw-bold d-block">Level 2</span>
                                <small>Penting</small>
                            </label>

                            {{-- LEVEL 3 (CUSTOM STYLE DI BAWAH) --}}
                            <input type="radio" class="btn-check" name="star_level" id="star3" value="3">
                            <label class="btn btn-outline-warning rounded-3 p-3 flex-fill text-start star-option level-3-label" for="star3">
                                <div class="d-flex mb-1">
                                    <i class="mdi mdi-star fs-4"></i> 
                                    <i class="mdi mdi-star fs-4"></i> 
                                    <i class="mdi mdi-star fs-4"></i> 
                                </div>
                                <span class="fw-bold d-block text-uppercase">Level 3</span>
                                <small class="fw-bold">Prioritas Utama!</small>
                            </label>
                        </div>
                    </div>

                    {{-- 4. PERIODE (Radio Button Harian/Bulanan/Tahunan) --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2">Periode Waktu</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="period_type" id="p_daily" value="daily" checked onclick="toggleDates('daily')">
                            <label class="btn btn-outline-primary py-2 fw-bold" for="p_daily">Harian</label>

                            <input type="radio" class="btn-check" name="period_type" id="p_monthly" value="monthly" onclick="toggleDates('monthly')">
                            <label class="btn btn-outline-primary py-2 fw-bold" for="p_monthly">Bulanan</label>

                            <input type="radio" class="btn-check" name="period_type" id="p_yearly" value="yearly" onclick="toggleDates('yearly')">
                            <label class="btn btn-outline-primary py-2 fw-bold" for="p_yearly">Tahunan</label>
                        </div>

                        {{-- Input Tanggal Dinamis --}}
                        <div class="mt-3 bg-white p-4 rounded-3 border shadow-sm">
                            <div id="date_daily">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="small fw-bold text-muted">Dari Tanggal</label>
                                        <input type="date" name="daily_start" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small fw-bold text-muted">Sampai Tanggal</label>
                                        <input type="date" name="daily_end" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                            <div id="date_monthly" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="small fw-bold text-muted">Dari Bulan</label>
                                        <input type="month" name="monthly_start" class="form-control" value="{{ date('Y-m') }}">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small fw-bold text-muted">Sampai Bulan</label>
                                        <input type="month" name="monthly_end" class="form-control" value="{{ date('Y-m') }}">
                                    </div>
                                </div>
                            </div>
                            <div id="date_yearly" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="small fw-bold text-muted">Dari Tahun</label>
                                        <input type="number" name="yearly_start" class="form-control" value="{{ date('Y') }}">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small fw-bold text-muted">Sampai Tahun</label>
                                        <input type="number" name="yearly_end" class="form-control" value="{{ date('Y') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. DETAIL TEXT --}}
                    <div class="mb-3">
                        <label class="fw-bold mb-2">Judul</label>
                        <input type="text" name="title" class="form-control form-control-lg fw-bold border-secondary" placeholder="Contoh: Penjualan 100 Unit iPhone" required>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold mb-2">Deskripsi Detail</label>
                        <textarea name="description" class="form-control border-secondary" rows="4" placeholder="Jelaskan detail target, KPI, atau catatan pencapaian..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5 shadow-lg text-white">
                        <i class="mdi mdi-check-circle me-1"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- CSS KHUSUS AGAR LEVEL 3 MENYALA SAAT DIPILIH --}}
<style>
    /* Styling Normal Radio Button Label */
    .star-option {
        transition: all 0.3s ease;
        border-width: 2px;
    }

    /* Style Khusus Level 3 Saat Belum Dipilih */
    .level-3-label {
        border-color: #FFD700;
        color: #bfa800;
    }

    /* Style Level 3 SAAT DIPILIH (Active State) - JELAS BANGET */
    #star3:checked + .level-3-label {
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%) !important; /* Warna Emas */
        color: #000 !important; /* Teks Hitam */
        border-color: #d4af37 !important;
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4) !important; /* Efek Glow */
        transform: translateY(-2px);
    }

    /* Style Level 2 Saat Dipilih */
    #star2:checked + label {
        background-color: #ffc107 !important;
        color: #000 !important;
        border-color: #ffc107 !important;
    }

    /* Style Level 1 Saat Dipilih */
    #star1:checked + label {
        background-color: #6c757d !important;
        color: #fff !important;
        border-color: #6c757d !important;
    }
</style>

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