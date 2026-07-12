@extends('layout.master')

@section('title', 'Riwayat Rapor Saya')
@section('heading', 'Riwayat Rapor Saya')

@push('styles')
<style>
    .history-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        margin-bottom: 2rem;
    }

    .info-card {
        background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
        border-radius: 16px;
        padding: 1.5rem;
        border: none;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card info-card">
            <div class="d-flex align-items-center">
                <div class="me-4">
                    @if(Auth::user()->profile_photo_path)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Foto" class="rounded-circle shadow-sm" width="70" height="70" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center shadow-sm" style="width: 70px; height: 70px; font-size: 24px; font-weight: bold;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h4 class="mb-1 fw-bold text-dark">{{ Auth::user()->name }}</h4>
                    <p class="mb-2 text-muted">
                        <i class="mdi mdi-office-building-marker me-1"></i> {{ Auth::user()->branch->name ?? 'Pusat' }} | 
                        <i class="mdi mdi-account-badge-outline me-1"></i> {{ str_replace('_', ' ', Auth::user()->role) }}
                    </p>
                    <div class="badge bg-white text-primary border px-3 py-2">
                        <i class="mdi mdi-calendar-check me-1"></i> 
                        Evaluasi Terakhir: 
                        @if($evaluations->first())
                            {{ \Carbon\Carbon::parse($evaluations->first()->evaluation_date)->translatedFormat('d F Y') }}
                        @else
                            Belum Ada Evaluasi
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card history-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="px-4 py-3 border-0">TANGGAL</th>
                                <th class="px-4 py-3 border-0">BULAN / TAHUN</th>
                                <th class="px-4 py-3 border-0 text-center">SKOR RATA-RATA</th>
                                <th class="px-4 py-3 border-0 text-center">GRADE</th>
                                <th class="px-4 py-3 border-0 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluations as $eval)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($eval->evaluation_date)->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-secondary">{{ \Carbon\Carbon::parse($eval->evaluation_date)->translatedFormat('F Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="fw-bold text-primary fs-5">{{ number_format($eval->average_score, 1) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $gradeColor = 'bg-secondary';
                                            if (in_array($eval->grade, ['A+', 'A'])) $gradeColor = 'bg-success';
                                            elseif (in_array($eval->grade, ['B+', 'B'])) $gradeColor = 'bg-primary';
                                            elseif ($eval->grade == 'C') $gradeColor = 'bg-warning text-dark';
                                            elseif ($eval->grade == 'D') $gradeColor = 'bg-danger';
                                        @endphp
                                        <span class="badge {{ $gradeColor }}">{{ $eval->grade }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('employee-evaluations.form', $eval->user_id) }}?date={{ $eval->evaluation_date }}" class="btn btn-sm btn-light border shadow-sm me-1">
                                            <i class="mdi mdi-eye text-primary"></i> Lihat
                                        </a>
                                        <a href="{{ route('employee-evaluations.export-pdf', ['user_id' => $eval->user_id, 'id' => $eval->id]) }}" target="_blank" class="btn btn-sm btn-light border shadow-sm">
                                            <i class="mdi mdi-file-pdf-box text-danger"></i> Unduh PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="mdi mdi-inbox-remove" style="font-size: 3rem;"></i>
                                            <p class="mt-2">Anda belum memiliki riwayat rapor (evaluasi).</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($evaluations->hasPages())
                <div class="d-flex justify-content-center p-3 border-top">
                    {{ $evaluations->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
