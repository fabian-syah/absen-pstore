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
                        <div class="row gx-2">
                            <div class="col-md-3">
                                <select name="month" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        @php $mPad = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                        <option value="{{ $mPad }}" {{ (isset($month) ? $month : date('m')) == $mPad ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="year" class="form-select">
                                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ (isset($year) ? $year : date('Y')) == $y ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari karyawan..."
                                        value="{{ $search }}">
                                    <button class="btn btn-info text-white" type="submit">Filter & Cari</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Karyawan</th>
                                    <th>Divisi</th>
                                    <th>Status Gaji ({{ \Carbon\Carbon::createFromFormat('m', $month)->locale('id')->isoFormat('MMMM') }} {{ $year }})</th>
                                    <th>Metode Bayar</th>
                                    <th>Gaji Terakhir</th>
                                    <th>Status Payroll</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $salaryThisMonth = $user->salaries->where('month', $month)->where('year', $year)->first();
                                        $latestSalary = $user->salaries->first(); // Sudah diurutkan desc di controller
                                        $bonusThisMonth = $user->bonuses->first(); // Sudah di-filter month & year
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="text-decoration-none text-dark">
                                                <div class="d-flex align-items-center">
                                                    @if($user->profile_photo_path)
                                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                                            class="img-sm rounded-circle me-2">
                                                    @else
                                                        <div
                                                            class="img-sm rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2 text-white">
                                                            {{ substr($user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="fw-bold mb-0">{{ $user->name }}</p>
                                                        <small class="text-muted">{{ $user->login_id }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td><span class="badge badge-opacity-info">{{ $user->division->name ?? '-' }}</span>
                                        </td>

                                        {{-- Status Bulan Ini --}}
                                        <td class="align-middle">
                                            @if($salaryThisMonth)
                                                <span class="badge badge-success mb-1">Sudah Digaji</span>
                                                <small class="d-block text-muted">
                                                    Rp {{ number_format($salaryThisMonth->total_amount, 0, ',', '.') }}
                                                </small>
                                            @else
                                                <span class="badge badge-warning mb-1">Belum Digaji</span>
                                            @endif

                                            @if($bonusThisMonth)
                                                <div class="mt-2 d-flex gap-1 flex-wrap">
                                                    @if($bonusThisMonth->bonus_amount > 0)
                                                        <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-1"><i class="mdi mdi-gift me-1"></i> Bonus</span>
                                                    @endif
                                                    @if($bonusThisMonth->thr_amount > 0)
                                                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary px-2 py-1"><i class="mdi mdi-wallet-giftcard me-1"></i> THR</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            @if($latestSalary)
                                                @if($latestSalary->payment_method == 'transfer')
                                                    <span class="badge badge-opacity-primary"><i class="mdi mdi-bank"></i>
                                                        Transfer</span>
                                                @else
                                                    <span class="badge badge-opacity-success"><i class="mdi mdi-cash"></i> Tunai</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- Gaji Terakhir --}}
                                        <td>
                                            @if($latestSalary)
                                                <div class="fw-bold text-dark">Rp
                                                    {{ number_format($latestSalary->total_amount, 0, ',', '.') }}
                                                </div>
                                                <small
                                                    class="text-muted">{{ $latestSalary->month }}/{{ $latestSalary->year }}</small>
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
                                                    <span class="badge badge-outline-warning"><i class="mdi mdi-clock"></i>
                                                        Pending</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                @if(!$salaryThisMonth)
                                                    <a href="{{ route('salaries.create', ['user_id' => $user->id, 'month' => $month, 'year' => $year]) }}"
                                                        class="btn btn-sm btn-success text-white px-3">
                                                        <i class="mdi mdi-cash-register"></i> Payroll
                                                    </a>
                                                @endif

                                                @if($latestSalary)
                                                    <a href="{{ route('salaries.show', $latestSalary->id) }}"
                                                        class="btn btn-sm btn-primary text-white px-3">
                                                        <i class="mdi mdi-file-document-outline border-0"></i> Struk
                                                    </a>
                                                @endif

                                                <a href="{{ route('bonuses.create', ['user_id' => $user->id, 'month' => $month, 'year' => $year]) }}"
                                                class="btn {{ $bonusThisMonth ? 'btn-outline-warning' : 'btn-warning text-dark' }} btn-sm px-3" data-bs-toggle="tooltip" title="{{ $bonusThisMonth ? 'Edit Bonus/THR' : 'Input Bonus & THR' }}">
                                                    <i class="mdi mdi-star{{ $bonusThisMonth ? '-outline' : '' }}"></i> {{ $bonusThisMonth ? 'Edit Bonus' : 'Bonus & THR' }}
                                                </a>
                                            </div>
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