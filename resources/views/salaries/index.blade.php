@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Manajemen Gaji</h4>

                        {{-- TOMBOL CREATE: HANYA MUNCUL JIKA ROLE ADMIN_GAJI --}}
                        @if(auth()->user()->role == 'admin_gaji')
                            <a href="{{ route('salaries.create') }}" class="btn btn-primary text-white">
                                <i class="mdi mdi-plus"></i> Buat Gaji Baru
                            </a>
                        @endif
                    </div>

                    {{-- Filter --}}
                    <form method="GET" action="{{ route('salaries.index') }}" class="row mb-4">
                        <div class="col-md-3">
                            <select name="month" class="form-control">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ $month == sprintf('%02d', $i) ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="year" class="form-control">
                                @for ($y = 2024; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100">Filter</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Cabang</th>
                                    <th>Kategori</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Total Gaji</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaries as $salary)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($salary->user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $salary->user->profile_photo_path) }}"
                                                        alt="profile" class="img-sm rounded-circle me-2">
                                                @else
                                                    <div
                                                        class="img-sm rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2 text-white">
                                                        {{ substr($salary->user->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="mb-0 fw-bold">{{ $salary->user->name }}</p>
                                                    <small class="text-muted">{{ $salary->user->login_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $salary->user->branch->name ?? '-' }}</td>
                                        <td>
                                            @if($salary->category == 'promotor')
                                                <span class="badge badge-info">Promotor</span>
                                            @elseif($salary->category == 'freelance')
                                                <span class="badge badge-warning">Freelance</span>
                                            @else
                                                <span class="badge badge-success">Karyawan</span>
                                            @endif
                                        </td>
                                        <td>{{ $salary->month }} / {{ $salary->year }}</td>
                                        <td>
                                            @php
                                                $isPaid = $salary->status == 'paid';
                                                if (!$isPaid && $salary->status == 'pending' && $salary->published_at) {
                                                    if (\Carbon\Carbon::parse($salary->published_at)->startOfDay()->lte(now())) {
                                                        $isPaid = true;
                                                    }
                                                }
                                            @endphp
                                            @if($isPaid)
                                                <span class="badge badge-success">Lunas</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-success">Rp
                                            {{ number_format($salary->total_amount, 0, ',', '.') }}</td>
                                        <td>
                                            {{-- SEMUA BISA LIHAT DETAIL --}}
                                            <a href="{{ route('salaries.show', $salary->id) }}"
                                                class="btn btn-sm btn-info icon-btn" title="Lihat Detail"><i
                                                    class="mdi mdi-eye"></i></a>

                                            {{-- TOMBOL EDIT & DELETE: HANYA MUNCUL JIKA ROLE ADMIN_GAJI --}}
                                            @if(auth()->user()->role == 'admin_gaji')
                                                <a href="{{ route('salaries.edit', $salary->id) }}"
                                                    class="btn btn-sm btn-warning icon-btn" title="Edit"><i
                                                        class="mdi mdi-pencil"></i></a>
                                                <form action="{{ route('salaries.destroy', $salary->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger icon-btn" title="Hapus"><i
                                                            class="mdi mdi-delete"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data gaji untuk periode ini.</td>
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