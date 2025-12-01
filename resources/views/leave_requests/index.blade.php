@extends('layout.master')

@section('title')
    Verifikasi Izin & Keterlambatan
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Verifikasi Izin & Keterlambatan</h4>
                        <div>
                            {{-- Tombol History --}}
                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                <a href="{{ route('audit.late.history') }}" class="btn btn-inverse-info btn-sm me-2">
                                    <i class="mdi mdi-history"></i> Lihat Riwayat
                                </a>
                            @endif

                            {{-- Tombol Ajukan Baru (untuk user biasa) --}}
                            @if (in_array(auth()->user()->role, ['user_biasa', 'leader']))
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm">
                                    <i class="mdi mdi-plus"></i> Ajukan Baru
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Debug Info --}}
                    <div class="alert alert-info mb-3">
                        <strong>Informasi Halaman:</strong><br>
                        • Hanya menampilkan pengajuan dengan status <strong>PENDING</strong><br>
                        • Total data: <strong>{{ $requests->total() }}</strong><br>
                        • Setelah approve/reject, data akan pindah ke halaman Riwayat
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{ $req->user->division->name ?? '-' }}
                                                        <span class="mx-1">|</span>
                                                        <span class="text-primary fw-bold">{{ $req->user->branch->name ?? 'Pusat' }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            @if ($req->type == 'sakit')
                                                <span class="badge bg-danger text-white">Sakit</span>
                                            @elseif($req->type == 'izin')
                                                <span class="badge bg-info text-white">Izin</span>
                                            @elseif($req->type == 'wfh')
                                                <span class="badge bg-primary text-white">WFH / Dinas</span>
                                            @elseif($req->type == 'cuti')
                                                <span class="badge bg-success text-white">Cuti</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Telat</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($req->type == 'telat')
                                                <div class="text-dark" style="font-size: 13px;">
                                                    <i class="mdi mdi-calendar"></i> {{ $req->start_date->format('d/m/Y') }}
                                                    <br>
                                                    <strong class="text-danger"><i class="mdi mdi-clock"></i>
                                                        {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}</strong>
                                                </div>
                                            @else
                                                <div class="text-dark" style="font-size: 13px;">
                                                    <i class="mdi mdi-calendar-range"></i>
                                                    {{ $req->start_date->format('d M') }}
                                                    @if ($req->end_date && $req->end_date != $req->start_date)
                                                        - {{ $req->end_date->format('d M') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-wrap" style="max-width: 200px;">{{ $req->reason }}</td>

                                        <td>
                                            @if ($req->file_proof)
                                                <a href="{{ asset('storage/' . $req->file_proof) }}" target="_blank"
                                                    class="btn btn-inverse-secondary btn-icon btn-sm">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-opacity-warning">Menunggu</span>
                                        </td>

                                        <td>
                                            {{-- USER: BATALKAN (hanya untuk pengaju) --}}
                                            @if (auth()->id() == $req->user_id)
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin membatalkan pengajuan?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger"
                                                        title="Batalkan Pengajuan">
                                                        <i class="mdi mdi-close-circle"></i> Batal
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- ADMIN/AUDIT: APPROVE & REJECT --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                <form action="{{ route('late.approve', $req->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Setujui pengajuan ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm p-2" title="Setujui">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('late.reject', $req->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Tolak pengajuan ini?')">
                                                    @csrf
                                                    <button class="btn btn-danger btn-sm p-2" title="Tolak">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <div class="bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="mdi mdi-check-all text-success" style="font-size: 40px;"></i>
                                                </div>
                                                <h4 class="fw-bold text-dark">Semua Beres!</h4>
                                                <p class="text-muted">Tidak ada pengajuan izin yang perlu diverifikasi saat ini.</p>
                                                @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                    <a href="{{ route('audit.late.history') }}" class="btn btn-inverse-info btn-sm mt-2">
                                                        <i class="mdi mdi-history"></i> Lihat Riwayat
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection