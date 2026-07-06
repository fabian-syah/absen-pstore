@extends('layout.master')

@section('title', 'Riwayat Evaluasi Harian')
@section('heading', 'Riwayat Evaluasi Harian')

@push('styles')
<style>
    .history-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card history-card">
            <div class="card-body">
                <form action="{{ route('employee-evaluations.history') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">Pilih Tanggal</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">Pilih Cabang</label>
                        <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch_id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
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
                                <th class="px-4 py-3 border-0">KARYAWAN</th>
                                <th class="px-4 py-3 border-0">CABANG</th>
                                <th class="px-4 py-3 border-0">SKOR RATA-RATA</th>
                                <th class="px-4 py-3 border-0">GRADE</th>
                                <th class="px-4 py-3 border-0">PENILAI</th>
                                <th class="px-4 py-3 border-0 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$branch_id)
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="mdi mdi-domain" style="font-size: 3rem;"></i>
                                            <p class="mt-2">Silakan pilih cabang terlebih dahulu untuk melihat data evaluasi.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @forelse($evaluations as $eval)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($eval->evaluation_date)->translatedFormat('d M Y') }}</div>
                                            <div class="small text-muted">{{ $eval->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                @if($eval->user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $eval->user->profile_photo_path) }}" alt="Foto" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                        {{ substr($eval->user->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $eval->user->name }}</div>
                                                    <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $eval->user->role) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-light text-secondary border">
                                                {{ $eval->user->branch->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="fw-bold text-primary fs-5">{{ number_format($eval->average_score, 1) }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $gradeColor = 'bg-secondary';
                                                if (in_array($eval->grade, ['A+', 'A'])) $gradeColor = 'bg-success';
                                                elseif (in_array($eval->grade, ['B+', 'B'])) $gradeColor = 'bg-primary';
                                                elseif ($eval->grade == 'C') $gradeColor = 'bg-warning text-dark';
                                                elseif ($eval->grade == 'D') $gradeColor = 'bg-danger';
                                            @endphp
                                            <span class="badge {{ $gradeColor }}">{{ $eval->grade }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-muted small">
                                            {{ $eval->assessor->name ?? 'Sistem' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('employee-evaluations.form', $eval->user->id) }}?date={{ $eval->evaluation_date }}" class="btn btn-sm btn-light border shadow-sm">
                                                <i class="mdi mdi-eye text-primary"></i> Lihat
                                            </a>
                                            <a href="{{ route('employee-evaluations.export-pdf', ['user_id' => $eval->user->id, 'date' => $eval->evaluation_date]) }}" class="btn btn-sm btn-light border shadow-sm">
                                                <i class="mdi mdi-file-pdf-box text-danger"></i> PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="mdi mdi-inbox-remove" style="font-size: 3rem;"></i>
                                                <p class="mt-2">Belum ada data evaluasi pada tanggal ini untuk cabang yang dipilih.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($evaluations, 'hasPages') && $evaluations->hasPages())
                <div class="d-flex justify-content-center p-3 border-top">
                    {{ $evaluations->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
