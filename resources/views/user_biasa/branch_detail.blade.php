@extends('layout.master')

@section('title', 'Detail Cabang')

@section('content')
<div class="container-fluid px-0">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-primary fw-bold">{{ $branch->name }}</h4>
            <p class="text-muted small mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $branch->address ?? 'Alamat belum diatur' }}</p>
        </div>
        <a href="{{ route('team.my-branches') }}" class="btn btn-light btn-sm rounded-pill border">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- CARD STATISTIK ABSENSI HARI INI --}}
    <div class="row mb-4">
        {{-- Card: Masuk --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1">Masuk</p>
                            <h3 class="fw-bold text-success mb-0">{{ $statsCounts['Masuk'] }}</h3>
                        </div>
                        <div class="icon-shape bg-success bg-opacity-10 text-success rounded p-2">
                            <i class="mdi mdi-account-check fs-4"></i>
                        </div>
                    </div>
                    {{-- List Nama (Accordion/Dropdown Simple) --}}
                    @if(count($attendanceGroups['Masuk']) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMasuk">
                                Lihat Siapa <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2" id="collapseMasuk">
                                <ul class="list-unstyled small mb-0">
                                    @foreach($attendanceGroups['Masuk'] as $emp)
                                        <li class="mb-1 text-dark"><i class="mdi mdi-circle-small text-success me-1"></i>{{ $emp->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: Izin --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1">Izin</p>
                            <h3 class="fw-bold text-warning mb-0">{{ $statsCounts['Izin'] }}</h3>
                        </div>
                        <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded p-2">
                            <i class="mdi mdi-file-document-outline fs-4"></i>
                        </div>
                    </div>
                    @if(count($attendanceGroups['Izin']) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIzin">
                                Lihat Siapa <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2" id="collapseIzin">
                                <ul class="list-unstyled small mb-0">
                                    @foreach($attendanceGroups['Izin'] as $emp)
                                        <li class="mb-1 text-dark"><i class="mdi mdi-circle-small text-warning me-1"></i>{{ $emp->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: Sakit --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1">Sakit</p>
                            <h3 class="fw-bold text-info mb-0">{{ $statsCounts['Sakit'] }}</h3>
                        </div>
                        <div class="icon-shape bg-info bg-opacity-10 text-info rounded p-2">
                            <i class="mdi mdi-hospital-box-outline fs-4"></i>
                        </div>
                    </div>
                    @if(count($attendanceGroups['Sakit']) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSakit">
                                Lihat Siapa <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2" id="collapseSakit">
                                <ul class="list-unstyled small mb-0">
                                    @foreach($attendanceGroups['Sakit'] as $emp)
                                        <li class="mb-1 text-dark"><i class="mdi mdi-circle-small text-info me-1"></i>{{ $emp->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: Cuti --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1">Cuti</p>
                            <h3 class="fw-bold text-primary mb-0">{{ $statsCounts['Cuti'] }}</h3>
                        </div>
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded p-2">
                            <i class="mdi mdi-beach fs-4"></i>
                        </div>
                    </div>
                    @if(count($attendanceGroups['Cuti']) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCuti">
                                Lihat Siapa <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2" id="collapseCuti">
                                <ul class="list-unstyled small mb-0">
                                    @foreach($attendanceGroups['Cuti'] as $emp)
                                        <li class="mb-1 text-dark"><i class="mdi mdi-circle-small text-primary me-1"></i>{{ $emp->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: WFH / Dinas --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1">WFH / Dinas</p>
                            <h3 class="fw-bold text-secondary mb-0">{{ $statsCounts['WFH / Dinas Luar'] }}</h3>
                        </div>
                        <div class="icon-shape bg-secondary bg-opacity-10 text-secondary rounded p-2">
                            <i class="mdi mdi-laptop-account fs-4"></i>
                        </div>
                    </div>
                    @if(count($attendanceGroups['WFH / Dinas Luar']) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWFH">
                                Lihat Siapa <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2" id="collapseWFH">
                                <ul class="list-unstyled small mb-0">
                                    @foreach($attendanceGroups['WFH / Dinas Luar'] as $emp)
                                        <li class="mb-1 text-dark"><i class="mdi mdi-circle-small text-secondary me-1"></i>{{ $emp->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: Alpha / Belum Absen --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1">Belum Absen</p>
                            <h3 class="fw-bold text-danger mb-0">{{ $statsCounts['Alpha / Belum Absen'] }}</h3>
                        </div>
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded p-2">
                            <i class="mdi mdi-account-remove-outline fs-4"></i>
                        </div>
                    </div>
                    @if(count($attendanceGroups['Alpha / Belum Absen']) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAlpha">
                                Lihat Siapa <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2" id="collapseAlpha">
                                <ul class="list-unstyled small mb-0">
                                    @foreach($attendanceGroups['Alpha / Belum Absen'] as $emp)
                                        <li class="mb-1 text-dark"><i class="mdi mdi-circle-small text-danger me-1"></i>{{ $emp->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- LIST KARYAWAN LENGKAP (TABEL) --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">Daftar Karyawan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 text-uppercase font-size-11">Nama Karyawan</th>
                            <th class="text-uppercase font-size-11">Divisi</th>
                            <th class="text-uppercase font-size-11">Status Hari Ini</th>
                            <th class="text-uppercase font-size-11">Waktu Masuk</th>
                            <th class="text-end pe-4 text-uppercase font-size-11">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $att = $emp->attendances->first();
                                $status = 'Alpha / Belum Absen';
                                $badgeClass = 'bg-danger bg-opacity-10 text-danger';

                                if($att) {
                                    $status = 'Hadir';
                                    $badgeClass = 'bg-success bg-opacity-10 text-success';
                                    if($att->is_late_checkin) {
                                        $status = 'Terlambat';
                                        $badgeClass = 'bg-warning bg-opacity-10 text-warning';
                                    }
                                } elseif ($emp->today_leave) {
                                    $type = ucfirst($emp->today_leave->type);
                                    if($type == 'Wfh') $type = 'WFH';
                                    $status = $type;
                                    
                                    if($type == 'Sakit') $badgeClass = 'bg-info bg-opacity-10 text-info';
                                    elseif($type == 'Izin') $badgeClass = 'bg-warning bg-opacity-10 text-warning';
                                    elseif($type == 'Cuti') $badgeClass = 'bg-primary bg-opacity-10 text-primary';
                                    elseif($type == 'WFH') $badgeClass = 'bg-secondary bg-opacity-10 text-secondary';
                                }
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-primary bg-opacity-10 text-primary fw-bold">
                                                {{ substr($emp->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-dark fw-bold">{{ $emp->name }}</h6>
                                            <small class="text-muted">{{ $emp->login_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $emp->division->name ?? '-' }}</span></td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>
                                    @if($att)
                                        <span class="fw-bold text-dark">{{ $att->check_in_time->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('team.branch.employee.history', ['branchId' => $branch->id, 'employeeId' => $emp->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        Riwayat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-account-off fs-1 d-block mb-2"></i>
                                    Belum ada karyawan di cabang ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection