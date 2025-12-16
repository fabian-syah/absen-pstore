@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Detail Gaji</h4>
                
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    @if($salary->user->profile_photo_path)
                        <img src="{{ asset('storage/' . $salary->user->profile_photo_path) }}" alt="profile" class="img-lg rounded-circle me-3">
                    @else
                        <div class="img-lg rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3 text-white fs-4">
                            {{ substr($salary->user->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="mb-0">{{ $salary->user->name }}</h3>
                        <p class="text-muted mb-0">{{ $salary->user->branch->name ?? '-' }} | {{ ucfirst($salary->category) }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6 text-muted">Periode</div>
                    <div class="col-6 fw-bold">{{ $salary->month }} / {{ $salary->year }}</div>
                </div>

                @if($salary->category == 'promotor')
                    <div class="row mb-2">
                        <div class="col-6">Gaji Pokok</div>
                        <div class="col-6 text-end">Rp {{ number_format($salary->promotor_basic_salary, 0, ',', '.') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Bonus</div>
                        <div class="col-6 text-end">Rp {{ number_format($salary->promotor_bonus, 0, ',', '.') }}</div>
                    </div>

                @elseif($salary->category == 'freelance')
                    <div class="row mb-2">
                        <div class="col-6">Kehadiran (Valid)</div>
                        <div class="col-6 text-end">{{ $salary->freelance_attendance_count }} Hari</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Gaji Per Hari</div>
                        <div class="col-6 text-end">Rp {{ number_format($salary->freelance_daily_salary, 0, ',', '.') }}</div>
                    </div>

                @elseif($salary->category == 'employee')
                    <div class="row mb-2">
                        <div class="col-6">Gaji Pokok</div>
                        <div class="col-6 text-end">Rp {{ number_format($salary->employee_basic_salary, 0, ',', '.') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Tunjangan Jabatan</div>
                        <div class="col-6 text-end">Rp {{ number_format($salary->employee_position_allowance, 0, ',', '.') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Previlage Owner</div>
                        <div class="col-6 text-end">Rp {{ number_format($salary->employee_owner_privilege, 0, ',', '.') }}</div>
                    </div>
                @endif

                <hr>
                <div class="row mb-4">
                    <div class="col-6 fs-5 fw-bold text-black">Total Terima</div>
                    <div class="col-6 fs-4 fw-bold text-success text-end">Rp {{ number_format($salary->total_amount, 0, ',', '.') }}</div>
                </div>

                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block">Catatan:</small>
                    <p class="mb-0">{{ $salary->notes ?? '-' }}</p>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Kembali</a>
                    <a href="{{ route('salaries.edit', $salary->id) }}" class="btn btn-warning text-dark">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection