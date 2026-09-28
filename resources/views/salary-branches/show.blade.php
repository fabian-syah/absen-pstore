@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 flex-column flex-sm-row gap-3 align-items-start align-items-sm-center">
                        <div>
                            <h4 class="card-title mb-1">Daftar Karyawan: {{ $branch->name }}</h4>
                            <p class="text-muted mb-0">{{ $branch->address }}</p>
                        </div>
                        <a href="{{ route('branch-salary.index') }}" class="btn btn-light">
                            <i class="mdi mdi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <form action="{{ route('branch-salary.show', $branch->id) }}" method="GET" class="mb-4">
                        <div class="row g-2">
                            <div class="col-6 col-sm-6 col-md-3">
                                <select name="month" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        @php $mPad = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                        <option value="{{ $mPad }}" {{ (isset($month) ? $month : date('m')) == $mPad ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-3">
                                <select name="year" class="form-select">
                                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ (isset($year) ? $year : date('Y')) == $y ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari karyawan..."
                                        value="{{ $search }}">
                                    <button class="btn btn-info text-white" type="submit">Filter & Cari</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- DESKTOP TABLE VIEW --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Karyawan</th>
                                    <th>Divisi</th>
                                    <th>Status Gaji ({{ \Carbon\Carbon::createFromFormat('m', $month)->locale('id')->isoFormat('MMMM') }} {{ $year }})</th>
                                    <th>Metode Bayar</th>
                                    <th>Gaji Bulan Ini</th>
                                    <th>Bonus & THR</th>
                                    <th>Status Payroll</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $salaryThisMonth = $user->salaries->where('month', $month)->where('year', $year)->first();
                                        $bonusThisMonth = $user->bonuses->first(); // Sudah di-filter month & year
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="text-decoration-none text-dark">
                                                <div class="d-flex align-items-center">
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
                                            @else
                                                <span class="badge badge-warning mb-1">Belum Digaji</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($salaryThisMonth)
                                                @if($salaryThisMonth->payment_method == 'transfer')
                                                    <span class="badge badge-opacity-primary"><i class="mdi mdi-bank"></i>
                                                        Transfer</span>
                                                @else
                                                    <span class="badge badge-opacity-success"><i class="mdi mdi-cash"></i> Tunai</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- Gaji Bulan Ini --}}
                                        <td>
                                            @if($salaryThisMonth)
                                                <div class="fw-bold text-dark">Rp
                                                    {{ number_format($salaryThisMonth->total_amount, 0, ',', '.') }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- Bonus & THR (Bulan Aktif) --}}
                                        <td class="align-middle">
                                            @if($bonusThisMonth)
                                                @if($bonusThisMonth->bonus_amount > 0)
                                                    <div class="mb-1">
                                                        <span class="badge bg-info text-dark px-2 py-1 me-1"><i class="mdi mdi-star"></i> Bonus</span>
                                                        <span class="fw-bold text-dark fs-6 d-block mt-1">Rp {{ number_format($bonusThisMonth->bonus_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                                @if($bonusThisMonth->thr_amount > 0)
                                                    <div class="{{ $bonusThisMonth->bonus_amount > 0 ? 'mt-2' : '' }}">
                                                        <span class="badge bg-primary text-white px-2 py-1 me-1"><i class="mdi mdi-wallet-giftcard"></i> THR</span>
                                                        <span class="fw-bold text-dark d-block mt-1">Rp {{ number_format($bonusThisMonth->thr_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- Status Payroll Bulan Ini --}}
                                        <td>
                                            @if($salaryThisMonth)
                                                @if($salaryThisMonth->status == 'paid')
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

                                                @if($salaryThisMonth)
                                                    <a href="{{ route('salaries.show', $salaryThisMonth->id) }}"
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
                                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada data karyawan ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE CARD LIST VIEW --}}
                    <div class="d-md-none">
                        @forelse($users as $user)
                            @php
                                $salaryThisMonth = $user->salaries->where('month', $month)->where('year', $year)->first();
                                $bonusThisMonth = $user->bonuses->first();
                            @endphp
                            <div class="card mb-3 border shadow-sm rounded-3 overflow-hidden">
                                <div class="card-body p-3">
                                    {{-- Header: Profil & Status Gaji --}}
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2 pb-2 border-bottom">
                                        <div>
                                            <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none text-dark">
                                                <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                                            </a>
                                            <small class="text-muted d-block">ID: {{ $user->login_id ?? '-' }}</small>
                                            <span class="badge badge-opacity-info mt-1" style="font-size: 0.75rem;">{{ $user->division->name ?? 'Non-Divisi' }}</span>
                                        </div>
                                        <div class="text-end">
                                            @if($salaryThisMonth)
                                                <span class="badge badge-success py-1 px-2" style="font-size: 0.75rem;">Sudah Digaji</span>
                                            @else
                                                <span class="badge badge-warning py-1 px-2" style="font-size: 0.75rem;">Belum Digaji</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Gaji & Status Info Box --}}
                                    <div class="bg-light rounded-3 p-2 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted small fw-semibold">Gaji Bulan Ini:</span>
                                            <span class="fw-bold text-dark">
                                                {{ $salaryThisMonth ? 'Rp ' . number_format($salaryThisMonth->total_amount, 0, ',', '.') : '-' }}
                                            </span>
                                        </div>

                                        @if($bonusThisMonth && ($bonusThisMonth->bonus_amount > 0 || $bonusThisMonth->thr_amount > 0))
                                            <div class="pt-1 border-top mt-1">
                                                @if($bonusThisMonth->bonus_amount > 0)
                                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                                        <span class="badge bg-info text-dark"><i class="mdi mdi-star"></i> Bonus:</span>
                                                        <span class="fw-bold text-dark">Rp {{ number_format($bonusThisMonth->bonus_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                                @if($bonusThisMonth->thr_amount > 0)
                                                    <div class="d-flex justify-content-between align-items-center small">
                                                        <span class="badge bg-primary text-white"><i class="mdi mdi-wallet-giftcard"></i> THR:</span>
                                                        <span class="fw-bold text-dark">Rp {{ number_format($bonusThisMonth->thr_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center pt-1 border-top mt-1">
                                            <span class="text-muted small">Metode / Status:</span>
                                            <div class="d-flex gap-1">
                                                @if($salaryThisMonth)
                                                    @if($salaryThisMonth->payment_method == 'transfer')
                                                        <span class="badge badge-opacity-primary"><i class="mdi mdi-bank"></i> Transfer</span>
                                                    @else
                                                        <span class="badge badge-opacity-success"><i class="mdi mdi-cash"></i> Tunai</span>
                                                    @endif

                                                    @if($salaryThisMonth->status == 'paid')
                                                        <span class="badge badge-outline-success"><i class="mdi mdi-check"></i> Paid</span>
                                                    @else
                                                        <span class="badge badge-outline-warning"><i class="mdi mdi-clock"></i> Pending</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="d-flex gap-2">
                                        @if(!$salaryThisMonth)
                                            <a href="{{ route('salaries.create', ['user_id' => $user->id, 'month' => $month, 'year' => $year]) }}"
                                                class="btn btn-sm btn-success text-white flex-grow-1 fw-bold py-2 d-flex align-items-center justify-content-center gap-1 rounded-3">
                                                <i class="mdi mdi-cash-register"></i> Payroll
                                            </a>
                                        @else
                                            <a href="{{ route('salaries.show', $salaryThisMonth->id) }}"
                                                class="btn btn-sm btn-primary text-white flex-grow-1 fw-bold py-2 d-flex align-items-center justify-content-center gap-1 rounded-3">
                                                <i class="mdi mdi-file-document-outline"></i> Struk
                                            </a>
                                        @endif

                                        <a href="{{ route('bonuses.create', ['user_id' => $user->id, 'month' => $month, 'year' => $year]) }}"
                                           class="btn {{ $bonusThisMonth ? 'btn-outline-warning' : 'btn-warning text-dark' }} btn-sm flex-grow-1 fw-bold py-2 d-flex align-items-center justify-content-center gap-1 rounded-3">
                                            <i class="mdi mdi-star{{ $bonusThisMonth ? '-outline' : '' }}"></i> {{ $bonusThisMonth ? 'Edit Bonus' : 'Bonus & THR' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">Tidak ada data karyawan ditemukan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection