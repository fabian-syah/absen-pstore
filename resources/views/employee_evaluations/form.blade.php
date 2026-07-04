@extends('layout.master')

@section('title', 'Isi Rapor Karyawan')
@section('heading', 'Evaluasi: ' . $employee->name)

@push('styles')
<style>
    .form-label {
        font-weight: 600;
        color: #334155;
    }
    .score-input {
        width: 100px;
        text-align: center;
        font-weight: bold;
        font-size: 1.1rem;
        color: #0f172a;
    }
    .note-input {
        border-radius: 8px;
    }
    .evaluation-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .btn-submit {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        font-weight: bold;
        padding: 10px 30px;
        border-radius: 50px;
        transition: transform 0.2s ease;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
    }
    .criteria-row {
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.5rem 0;
    }
    .criteria-row:hover {
        background-color: #f8fafc;
    }
    .criteria-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('employee-evaluations.index') }}" class="btn btn-light shadow-sm rounded-circle p-2 me-3">
                    <i class="mdi mdi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Penilaian Kinerja</h4>
                    <p class="text-muted mb-0 small">Karyawan: <strong>{{ $employee->name }}</strong></p>
                </div>
            </div>
            
            @if($evaluation)
                @php
                    $badgeColor = 'bg-secondary';
                    if (in_array($evaluation->grade, ['A+', 'A'])) $badgeColor = 'bg-success';
                    elseif (in_array($evaluation->grade, ['B+', 'B'])) $badgeColor = 'bg-primary';
                    elseif ($evaluation->grade == 'C') $badgeColor = 'bg-warning text-dark';
                    elseif ($evaluation->grade == 'D') $badgeColor = 'bg-danger';
                @endphp
                <div class="badge {{ $badgeColor }} px-3 py-2 rounded-pill fs-6 shadow-sm">
                    Grade {{ $evaluation->grade }} <span class="mx-1 fw-normal">|</span> {{ number_format($evaluation->average_score, 1) }}
                </div>
            @endif
        </div>

        <div class="card evaluation-card">
            <div class="card-body p-4 p-md-5">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('employee-evaluations.form', $employee->id) }}" method="GET" class="row g-3 mb-4 p-3 bg-white border rounded-3 shadow-sm align-items-end">
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">Pilih Bulan</label>
                        <select name="month" class="form-select form-select-sm shadow-none text-dark bg-white border" style="color: #334155 !important; background-color: #ffffff !important;" onchange="this.form.submit()">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">Pilih Tahun</label>
                        <select name="year" class="form-select form-select-sm shadow-none text-dark bg-white border" style="color: #334155 !important; background-color: #ffffff !important;" onchange="this.form.submit()">
                            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>

                <form action="{{ route('employee-evaluations.store', $employee->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                        <i class="mdi mdi-information me-2"></i> <strong>Instruksi:</strong> Berikan nilai skala <strong>0 - 100</strong>. Jika ada kriteria yang tidak relevan, biarkan kosong.
                    </div>

                    {{-- Kriteria Fix --}}
                    @php
                        $criteria = [
                            ['name' => 'kecerdasan', 'label' => 'Kecerdasan', 'icon' => 'mdi-brain', 'color' => '#8b5cf6'],
                            ['name' => 'amanah', 'label' => 'Amanah', 'icon' => 'mdi-shield-check', 'color' => '#10b981'],
                            ['name' => 'sosial_media', 'label' => 'Sosial Media', 'icon' => 'mdi-youtube', 'color' => '#ef4444'],
                            ['name' => 'kepemimpinan', 'label' => 'Kepemimpinan', 'icon' => 'mdi-account-tie', 'color' => '#3b82f6'],
                            ['name' => 'data_ketelitian', 'label' => 'Data & Ketelitian', 'icon' => 'mdi-clipboard-text', 'color' => '#f59e0b'],
                            ['name' => 'komunikasi', 'label' => 'Komunikasi', 'icon' => 'mdi-forum', 'color' => '#06b6d4'],
                            ['name' => 'kedisiplinan', 'label' => 'Kedisiplinan', 'icon' => 'mdi-calendar-clock', 'color' => '#ec4899'],
                        ];
                    @endphp

                    @foreach($criteria as $c)
                        <div class="criteria-row row align-items-center">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <div class="criteria-title">
                                    <div style="width: 35px; height: 35px; background-color: {{ $c['color'] }}15; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="mdi {{ $c['icon'] }} fs-5" style="color: {{ $c['color'] }}"></i>
                                    </div>
                                    {{ $c['label'] }}
                                </div>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0 d-flex justify-content-md-center">
                                <input type="number" name="{{ $c['name'] }}_score" class="form-control score-input shadow-none" placeholder="0-100" min="0" max="100" value="{{ old($c['name'] . '_score', $evaluation ? $evaluation->{$c['name'] . '_score'} : '') }}">
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="{{ $c['name'] }}_note" class="form-control note-input shadow-none" placeholder="Catatan tambahan (Opsional)" value="{{ old($c['name'] . '_note', $evaluation ? $evaluation->{$c['name'] . '_note'} : '') }}">
                            </div>
                        </div>
                    @endforeach

                    {{-- Kriteria Custom --}}
                    <div class="criteria-row row align-items-center bg-light rounded-3 p-3 mt-4" style="border: 1px dashed #cbd5e1;">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-muted mb-0"><i class="mdi mdi-plus-box text-primary me-2"></i> Kriteria Tambahan (Custom)</h6>
                            <small class="text-muted">Isi jika ada kriteria lain di luar yang telah ditentukan.</small>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <input type="text" name="custom_title" class="form-control shadow-none fw-bold" placeholder="Nama Kriteria..." value="{{ old('custom_title', $evaluation ? $evaluation->custom_title : '') }}">
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0 d-flex justify-content-md-center">
                            <input type="number" name="custom_score" class="form-control score-input shadow-none" placeholder="0-100" min="0" max="100" value="{{ old('custom_score', $evaluation ? $evaluation->custom_score : '') }}">
                        </div>
                        <div class="col-md-7">
                            <input type="text" name="custom_note" class="form-control note-input shadow-none" placeholder="Catatan tambahan (Opsional)" value="{{ old('custom_note', $evaluation ? $evaluation->custom_note : '') }}">
                        </div>
                    </div>

                    {{-- Hasil Akhir Penilaian --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between bg-white p-3 rounded-3 mt-4" style="border: 1px solid #e2e8f0;">
                        <div class="mb-3 mb-md-0">
                            <h6 class="mb-0 fw-bold text-dark">Hasil Akhir Penilaian</h6>
                            <small class="text-muted">Dihitung otomatis, tapi bisa Anda ubah manual</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <label class="small text-muted mb-1 d-block text-md-end">Total Nilai</label>
                                <input type="number" id="input_average_score" name="average_score" class="form-control form-control-sm text-center fw-bold shadow-none" style="width: 100px; font-size: 1.1rem; color: #0f172a;" step="0.1" min="0" max="100" value="{{ old('average_score', $evaluation ? $evaluation->average_score : '') }}">
                            </div>
                            <div>
                                <label class="small text-muted mb-1 d-block text-md-end">Grade</label>
                                <input type="text" id="input_grade" name="grade" class="form-control form-control-sm text-center fw-bold shadow-none" style="width: 80px; font-size: 1.1rem; color: #0f172a;" value="{{ old('grade', $evaluation ? $evaluation->grade : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-submit shadow-lg">
                            <i class="mdi mdi-content-save me-1"></i> Simpan Penilaian
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scoreInputs = document.querySelectorAll('.score-input');
        const inputAverage = document.getElementById('input_average_score');
        const inputGrade = document.getElementById('input_grade');
        let isManuallyEdited = false;

        // Jika user mengetik manual di input hasil akhir, jangan dioverride otomatis lagi
        inputAverage.addEventListener('input', () => isManuallyEdited = true);
        inputGrade.addEventListener('input', () => isManuallyEdited = true);

        function calculateGrade() {
            if (isManuallyEdited) return; // Jangan ubah jika sudah diketik manual

            let total = 0;
            let count = 0;

            scoreInputs.forEach(input => {
                if (input.value !== '') {
                    // Batasi nilai max 100 dan min 0
                    if (parseFloat(input.value) > 100) input.value = 100;
                    if (parseFloat(input.value) < 0) input.value = 0;
                    
                    total += parseFloat(input.value);
                    count++;
                }
            });

            if (count > 0) {
                let average = total / count;
                let grade = 'D';

                if (average >= 95) grade = 'A+';
                else if (average >= 90) grade = 'A';
                else if (average >= 85) grade = 'B+';
                else if (average >= 80) grade = 'B';
                else if (average >= 70) grade = 'C';

                // Format average to max 1 decimal if needed
                inputAverage.value = average % 1 === 0 ? average : average.toFixed(1);
                inputGrade.value = grade;
            } else {
                // Jangan kosongkan jika form sedang dimuat dengan data lama
                if (!inputAverage.defaultValue) {
                    inputAverage.value = '';
                    inputGrade.value = '';
                }
            }
        }

        scoreInputs.forEach(input => {
            input.addEventListener('input', calculateGrade);
        });

        // Hitung otomatis saat halaman dimuat jika belum ada isian
        if (!inputAverage.value) {
            calculateGrade();
        }
    });
</script>
@endpush
