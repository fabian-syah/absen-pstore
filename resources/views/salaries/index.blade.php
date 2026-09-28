@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <h4 class="card-title mb-0">Manajemen Gaji</h4>

                        {{-- TOMBOL CREATE: HANYA MUNCUL JIKA ROLE ADMIN_GAJI --}}
                        @if(auth()->user()->role == 'admin_gaji')
                            <a href="{{ route('salaries.create') }}" class="btn btn-primary text-white">
                                <i class="mdi mdi-plus"></i> Buat Gaji Baru
                            </a>
                        @endif
                    </div>

                    {{-- Filter --}}
                    <form method="GET" action="{{ route('salaries.index') }}" class="row g-2 mb-4">
                        <div class="col-6 col-md-3">
                            <select name="month" class="form-select">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ $month == sprintf('%02d', $i) ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="year" class="form-select">
                                @for ($y = 2024; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-dark w-100">Filter</button>
                        </div>
                    </form>

                    {{-- DESKTOP TABLE VIEW --}}
                    <div class="table-responsive d-none d-md-block">
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
                                        <td colspan="7" class="text-center">Belum ada data gaji untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE CARD LIST VIEW --}}
                    <div class="d-md-none">
                        @forelse($salaries as $salary)
                            @php
                                $isPaid = $salary->status == 'paid';
                                if (!$isPaid && $salary->status == 'pending' && $salary->published_at) {
                                    if (\Carbon\Carbon::parse($salary->published_at)->startOfDay()->lte(now())) {
                                        $isPaid = true;
                                    }
                                }
                            @endphp
                            <div class="card mb-3 border shadow-sm rounded-3 overflow-hidden">
                                <div class="card-body p-3">
                                    {{-- User info & status --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($salary->user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $salary->user->profile_photo_path) }}"
                                                    alt="profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                                                    style="width: 40px; height: 40px; font-size: 14px;">
                                                    {{ substr($salary->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="mb-0 fw-bold text-dark">{{ $salary->user->name }}</p>
                                                <small class="text-muted">ID: {{ $salary->user->login_id ?? '-' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($isPaid)
                                                <span class="badge badge-success">Lunas</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Details --}}
                                    <div class="d-flex flex-wrap align-items-center justify-content-between small text-muted mb-2">
                                        <div>
                                            <i class="mdi mdi-store-outline me-1"></i>{{ $salary->user->branch->name ?? '-' }}
                                            <span class="mx-1">•</span>
                                            <i class="mdi mdi-calendar me-1"></i>{{ $salary->month }} / {{ $salary->year }}
                                        </div>
                                        <div>
                                            @if($salary->category == 'promotor')
                                                <span class="badge badge-info" style="font-size: 0.7rem;">Promotor</span>
                                            @elseif($salary->category == 'freelance')
                                                <span class="badge badge-warning" style="font-size: 0.7rem;">Freelance</span>
                                            @else
                                                <span class="badge badge-success" style="font-size: 0.7rem;">Karyawan</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Total Gaji Box --}}
                                    <div class="bg-light rounded-3 p-2 mb-3 d-flex justify-content-between align-items-center">
                                        <span class="text-muted small fw-semibold">Total Gaji:</span>
                                        <span class="fw-bold text-success fs-6">Rp {{ number_format($salary->total_amount, 0, ',', '.') }}</span>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('salaries.show', $salary->id) }}"
                                            class="btn btn-sm btn-info text-white flex-grow-1 fw-bold py-2 d-flex align-items-center justify-content-center gap-1 rounded-3">
                                            <i class="mdi mdi-eye"></i> Detail
                                        </a>

                                        @if(auth()->user()->role == 'admin_gaji')
                                            <a href="{{ route('salaries.edit', $salary->id) }}"
                                                class="btn btn-sm btn-warning text-dark fw-bold px-3 py-2 d-flex align-items-center justify-content-center gap-1 rounded-3">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('salaries.destroy', $salary->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger text-white fw-bold px-3 py-2 d-flex align-items-center justify-content-center gap-1 rounded-3">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-muted">Belum ada data gaji untuk periode ini.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection