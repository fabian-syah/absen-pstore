@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="card-title mb-1">Daftar Karyawan: {{ $branch->name }}</h4>
                        <p class="text-muted mb-0">{{ $branch->address }}</p>
                    </div>
                    <a href="{{ route('branch-salary.index') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left"></i> Kembali
                    </a>
                </div>

                <form action="{{ route('branch-salary.show', $branch->id) }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari karyawan..." value="{{ $search }}">
                        <button class="btn btn-info text-white" type="submit">Cari</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Status Gaji ({{ date('F Y') }})</th>
                                <th>Gaji Terakhir</th>
                                <th>Status Payroll</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    $salaryThisMonth = $user->salaries->where('month', date('m'))->where('year', date('Y'))->first();
                                    $latestSalary = $user->salaries->first(); // Sudah diurutkan desc di controller
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-sm rounded-circle me-2">
                                            @else
                                                <div class="img-sm rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2 text-white">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="fw-bold mb-0">{{ $user->name }}</p>
                                                <small class="text-muted">{{ $user->login_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-opacity-info">{{ $user->division->name ?? '-' }}</span></td>
                                    
                                    {{-- Status Bulan Ini --}}
                                    <td>
                                        @if($salaryThisMonth)
                                            <span class="badge badge-success">Sudah Digaji</span>
                                            <small class="d-block text-muted mt-1">
                                                Rp {{ number_format($salaryThisMonth->total_amount, 0, ',', '.') }}
                                            </small>
                                        @else
                                            <span class="badge badge-warning">Belum Digaji</span>
                                        @endif
                                    </td>

                                    {{-- Gaji Terakhir --}}
                                    <td>
                                        @if($latestSalary)
                                            <div class="fw-bold text-dark">Rp {{ number_format($latestSalary->total_amount, 0, ',', '.') }}</div>
                                            <small class="text-muted">{{ $latestSalary->month }}/{{ $latestSalary->year }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- Status Payroll Terakhir --}}
                                    <td>
                                        @if($latestSalary)
                                            @if($latestSalary->status == 'paid')
                                                <span class="badge badge-outline-success"><i class="mdi mdi-check"></i> Paid</span>
                                            @else
                                                <span class="badge badge-outline-warning"><i class="mdi mdi-clock"></i> Pending</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if(!$salaryThisMonth)
                                            <a href="{{ route('salaries.create', ['user_id' => $user->id]) }}" class="btn btn-sm btn-success text-white">
                                                <i class="mdi mdi-cash-register"></i> Payroll
                                            </a>
                                        @else
                                            <a href="{{ route('salaries.edit', $salaryThisMonth->id) }}" class="btn btn-sm btn-warning">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                        @endif
                                        <a href="{{ route('attendance.summary.user', $user->id) }}" class="btn btn-sm btn-info text-white icon-btn">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection