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

                {{-- Search Karyawan di Cabang Ini --}}
                <form action="{{ route('branch-salary.show', $branch->id) }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari karyawan di cabang ini..." value="{{ $search }}">
                        <button class="btn btn-info text-white" type="submit">Cari</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Status Gaji ({{ date('F Y') }})</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
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
                                    <td>
                                        <span class="badge badge-opacity-info">{{ $user->division->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Cek apakah sudah ada gaji bulan ini
                                            $salaryThisMonth = $user->salaries->first();
                                        @endphp
                                        
                                        @if($salaryThisMonth)
                                            <span class="badge badge-success">
                                                <i class="mdi mdi-check-circle"></i> Sudah Digaji
                                            </span>
                                            <small class="d-block text-muted mt-1">
                                                Rp {{ number_format($salaryThisMonth->total_amount, 0, ',', '.') }}
                                            </small>
                                        @else
                                            <span class="badge badge-warning">Belum Digaji</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{-- TOMBOL PAYROLL (Link ke Create Salary dengan User ID Terpilih) --}}
                                        @if(!$salaryThisMonth)
                                            <a href="{{ route('salaries.create', ['user_id' => $user->id]) }}" class="btn btn-sm btn-success text-white">
                                                <i class="mdi mdi-cash-register"></i> Payroll
                                            </a>
                                        @else
                                            <a href="{{ route('salaries.edit', $salaryThisMonth->id) }}" class="btn btn-sm btn-warning">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                        @endif

                                        {{-- TOMBOL LIHAT HISTORY --}}
                                        <a href="{{ route('attendance.summary.user', $user->id) }}" class="btn btn-sm btn-info text-white icon-btn" title="Lihat History Absen & Gaji">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">Tidak ada karyawan ditemukan di cabang ini.</td>
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