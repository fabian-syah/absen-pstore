@extends('layout.master') {{-- Sesuaikan dengan master layout kamu --}}

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Master Data Absensi Seluruh User</h4>
                    <p class="card-description">
                        Monitoring semua status: Hadir, Sakit, Izin, Cuti, Alpha, WFH, dll.
                    </p>

                    {{-- Form Filter --}}
                    <form method="GET" action="{{ route('admin.attendance.all') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari Nama Karyawan..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="presence_status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="Hadir" {{ request('presence_status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="Sakit" {{ request('presence_status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="Izin" {{ request('presence_status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="Cuti" {{ request('presence_status') == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                    <option value="Alpha" {{ request('presence_status') == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                    <option value="Libur" {{ request('presence_status') == 'Libur' ? 'selected' : '' }}>Libur</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </div>
                    </form>

                    {{-- Tabel Data --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Karyawan</th>
                                    <th>Cabang</th>
                                    <th>Status</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Telat?</th>
                                    <th>Ket.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $item)
                                <tr>
                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $item->user->profile_photo_path) }}" alt="profile" class="img-sm rounded-circle me-2"/>
                                            @else
                                                <div class="img-sm rounded-circle me-2 bg-secondary d-flex justify-content-center align-items-center text-white">
                                                    {{ substr($item->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h6>{{ $item->user->name }}</h6>
                                                <small class="text-muted">{{ $item->user->login_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->branch ? $item->branch->name : '-' }}</td>
                                    <td>
                                        {{-- Logika Badge Status --}}
                                        @php
                                            $badgeClass = 'badge-secondary';
                                            if($item->presence_status == 'Hadir') $badgeClass = 'badge-success';
                                            elseif($item->presence_status == 'Sakit') $badgeClass = 'badge-warning';
                                            elseif($item->presence_status == 'Izin') $badgeClass = 'badge-info';
                                            elseif($item->presence_status == 'Alpha') $badgeClass = 'badge-danger';
                                            elseif($item->presence_status == 'Cuti') $badgeClass = 'badge-primary';
                                        @endphp
                                        <label class="badge {{ $badgeClass }}">
                                            {{ $item->presence_status }}
                                        </label>
                                    </td>
                                    <td>
                                        {{ $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if($item->check_out_time)
                                            {{ \Carbon\Carbon::parse($item->check_out_time)->format('H:i') }}
                                        @elseif($item->presence_status == 'Hadir' && !$item->check_out_time)
                                            <span class="text-warning fst-italic">Belum Pulang</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_late_checkin)
                                            <span class="text-danger">Ya</span>
                                        @else
                                            <span class="text-success">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-inverse-info btn-icon btn-sm" title="Lihat Detail">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Data absensi tidak ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $attendances->links() }} 
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection