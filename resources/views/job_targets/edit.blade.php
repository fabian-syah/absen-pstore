@extends('layout.master')
@section('title', 'Edit Data')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ url()->previous() }}" class="btn btn-light bg-white shadow-sm mb-3 border-0 rounded-3 text-dark fw-bold">
            <i class="mdi mdi-arrow-left me-1"></i> Batal Edit
        </a>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <h4 class="fw-bold mb-4 text-dark border-bottom pb-3">✏️ Edit Target / Pencapaian</h4>
                
                <form action="{{ route('job-targets.update', $jobTarget->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase">Jenis Data</label>
                        <input type="text" class="form-control bg-light fw-bold" value="{{ ucfirst(str_replace('_', ' ', $jobTarget->type)) }}" readonly>
                    </div>

                    @if(!Str::contains($jobTarget->type, 'achievement'))
                    <div class="mb-4">
                        <label class="fw-bold mb-2 d-block small text-uppercase">Prioritas</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star1" value="1" {{ $jobTarget->star_level == 1 ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100 h-100 rounded-3 p-3 text-start star-option" for="star1">Lvl 1</label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star2" value="2" {{ $jobTarget->star_level == 2 ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100 h-100 rounded-3 p-3 text-start star-option" for="star2">Lvl 2</label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="star_level" id="star3" value="3" {{ $jobTarget->star_level == 3 ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100 h-100 rounded-3 p-3 text-start star-option level-3-label" for="star3">Lvl 3</label>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase">Periode ({{ ucfirst($jobTarget->period) }})</label>
                        <div class="bg-light p-3 rounded-3 border">
                            @if($jobTarget->period == 'daily')
                                <div class="row g-2">
                                    <div class="col-6"><label>Dari</label><input type="date" name="daily_start" class="form-control" value="{{ $jobTarget->start_date->format('Y-m-d') }}"></div>
                                    <div class="col-6"><label>Sampai</label><input type="date" name="daily_end" class="form-control" value="{{ $jobTarget->deadline->format('Y-m-d') }}"></div>
                                </div>
                                <input type="hidden" name="period_type" value="daily">
                            @elseif($jobTarget->period == 'monthly')
                                <div class="row g-2">
                                    <div class="col-6"><label>Bulan Awal</label><input type="month" name="monthly_start" class="form-control" value="{{ $jobTarget->start_date->format('Y-m') }}"></div>
                                    <div class="col-6"><label>Bulan Akhir</label><input type="month" name="monthly_end" class="form-control" value="{{ $jobTarget->deadline->format('Y-m') }}"></div>
                                </div>
                                <input type="hidden" name="period_type" value="monthly">
                            @else
                                <div class="row g-2">
                                    <div class="col-6"><label>Tahun Awal</label><input type="number" name="yearly_start" class="form-control" value="{{ $jobTarget->start_date->format('Y') }}"></div>
                                    <div class="col-6"><label>Tahun Akhir</label><input type="number" name="yearly_end" class="form-control" value="{{ $jobTarget->deadline->format('Y') }}"></div>
                                </div>
                                <input type="hidden" name="period_type" value="yearly">
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-uppercase">Judul</label>
                        <input type="text" name="title" class="form-control form-control-lg fw-bold border-secondary" value="{{ $jobTarget->title }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase">Deskripsi</label>
                        <textarea name="description" class="form-control border-secondary" rows="4" required>{{ $jobTarget->description }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-3 rounded-3 fw-bold fs-5 shadow-sm text-dark">
                        <i class="mdi mdi-content-save me-1"></i> Update Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .star-option { border-width: 2px; transition: all 0.2s; }
    .level-3-label { border-color: #FFD700; color: #bfa800; }
    #star3:checked + .level-3-label {
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%) !important;
        color: #000 !important; border-color: #d4af37 !important;
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4) !important;
    }
    #star2:checked + label { background-color: #ffc107 !important; color: #000 !important; }
    #star1:checked + label { background-color: #6c757d !important; color: #fff !important; }
</style>
@endsection