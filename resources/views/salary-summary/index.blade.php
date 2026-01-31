@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title">Ringkasan Gaji Tahunan</h4>
                            <p class="text-muted">Periode Cutoff: Tgl 26 (Bulan Lalu) - Tgl 25 (Bulan Ini)</p>
                        </div>
                        <div class="badge badge-success fs-5 p-2">
                            Total Tahun {{ $year }}: Rp {{ number_format($totalAnnual, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- FILTER TAHUN & USER (Hanya muncul u/ Admin) --}}
                    <form method="GET" action="{{ route('salary-summary.index') }}" class="row mb-4 bg-light p-3 rounded">
                        <div class="col-md-3">
                            <label class="fw-bold">Pilih Tahun</label>
                            <select name="year" class="form-control">
                                @for ($y = 2024; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Admin bisa pilih karyawan lain --}}
                        @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                            <div class="col-md-4">
                                <label class="fw-bold">Pilih Karyawan</label>
                                <select name="user_id" class="form-control">
                                    <option value="">-- Semua Karyawan --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} - {{ $user->branch->name ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 text-white">Tampilkan</button>
                        </div>
                    </form>

                    <h5 class="mb-3 text-info">
                        <i class="mdi mdi-account"></i>
                        Ringkasan Milik: <span
                            class="fw-bold text-dark">{{ $targetUser ? $targetUser->name : 'Semua Karyawan (Total)' }}</span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th style="width: 15%">Bulan Gaji</th>
                                    <th style="width: 25%">Periode Absensi (Cutoff)</th>
                                    <th style="width: 15%">Kategori</th>
                                    <th style="width: 20%" class="text-end">Total Diterima</th>
                                    <th style="width: 10%" class="text-center">Status</th>
                                    <th style="width: 10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary as $item)
                                    <tr>
                                        <td>{{ $item['month_num'] }}</td>
                                        <td class="fw-bold">{{ $item['month_name'] }}</td>
                                        <td class="text-muted">
                                            <i class="mdi mdi-calendar-clock"></i> {{ $item['period_string'] }}
                                        </td>
                                        <td>
                                            @if($item['data'])
                                                @if($item['data']->category == 'promotor')
                                                    <span class="badge badge-info">Promotor</span>
                                                @elseif($item['data']->category == 'freelance')
                                                    <span class="badge badge-warning">Freelance</span>
                                                @else
                                                    <span class="badge badge-success">Karyawan</span>
                                                @endif
                                            @elseif($item['amount'] > 0)
                                                <span class="badge badge-primary">Total Aggregated</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold {{ $item['amount'] > 0 ? 'text-success' : '' }}">
                                            Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if($item['data'])
                                                <i class="mdi mdi-check-circle text-success fs-4" title="Sudah Dibayarkan"></i>
                                            @elseif($item['amount'] > 0)
                                                <i class="mdi mdi-cash-multiple text-primary fs-4" title="Total Gaji"></i>
                                            @else
                                                <i class="mdi mdi-minus-circle-outline text-muted fs-4" title="Belum Ada Data"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($item['data'])
                                                <a href="{{ route('salaries.show', $item['data']->id) }}"
                                                    class="btn btn-sm btn-outline-info">
                                                    Detail
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                {{-- Baris Total --}}
                                <tr class="table-warning fw-bold">
                                    <td colspan="4" class="text-end">GRAND TOTAL TAHUN {{ $year }}</td>
                                    <td class="text-end text-black fs-5">Rp {{ number_format($totalAnnual, 0, ',', '.') }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection