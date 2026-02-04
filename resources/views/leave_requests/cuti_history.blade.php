@extends('layout.master')

@section('title')
    Riwayat Cuti Saya
@endsection

@section('heading')
    <h4 class="mb-0 fw-bold">Riwayat Cuti Saya</h4>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- CARDS SUMMARY --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Jatah Cuti Tahunan</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $user->yearly_leave_limit ?? 10 }} <small
                                    class="fs-6">Hari</small></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-dark-50 mb-1 small text-uppercase fw-bold">Cuti Terpakai</h6>
                            <h2 class="fw-bold text-dark mb-0 display-6">{{ $user->leave_taken ?? 0 }} <small
                                    class="fs-6">Hari</small></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Sisa Cuti</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $user->leave_balance ?? 10 }} <small
                                    class="fs-6">Hari</small></h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAIL TABEL --}}
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="mdi mdi-history me-2"></i> Detail
                            Pengajuan Cuti</h5>
                        <a href="{{ route('leave-requests.create') }}"
                            class="btn btn-sm btn-primary rounded-pill shadow-sm">
                            <i class="mdi mdi-plus me-1"></i> Ajukan Cuti
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">Tanggal Pengajuan</th>
                                    <th>Periode Cuti</th>
                                    <th class="text-center">Durasi</th>
                                    <th>Alasan</th>
                                    <th class="text-center">Status</th>
                                    <th>Approver</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    @php
                                        // Hitung durasi
                                        $startDate = \Carbon\Carbon::parse($req->start_date);
                                        $endDate = $req->end_date ? \Carbon\Carbon::parse($req->end_date) : $startDate;
                                        $duration = $startDate->diffInDays($endDate) + 1;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $req->created_at->format('d M Y') }}</div>
                                            <small class="text-muted">{{ $req->created_at->format('H:i') }} WIB</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-primary">
                                                    {{ $startDate->translatedFormat('d M Y') }}
                                                </span>
                                                @if($duration > 1)
                                                    <small class="text-muted">
                                                        s/d {{ $endDate->translatedFormat('d M Y') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-soft-primary text-primary border border-primary rounded-pill px-3">
                                                {{ $duration }} Hari
                                            </span>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-muted text-wrap" style="max-width: 200px; font-size: 0.9rem;">
                                                {{ Str::limit($req->reason, 50) }}
                                            </p>
                                        </td>
                                        <td class="text-center">
                                            @if ($req->status == 'approved')
                                                <span class="badge bg-success rounded-pill px-3"><i
                                                        class="mdi mdi-check-circle me-1"></i> Disetujui</span>
                                            @elseif($req->status == 'rejected')
                                                <span class="badge bg-danger rounded-pill px-3"><i
                                                        class="mdi mdi-close-circle me-1"></i> Ditolak</span>
                                            @elseif($req->status == 'cancelled')
                                                <span class="badge bg-secondary rounded-pill px-3">Dibatalkan</span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-3"><i
                                                        class="mdi mdi-clock-outline me-1"></i> Menunggu</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->approver)
                                                <span class="fw-bold text-dark small"><i class="mdi mdi-account-check me-1"></i>
                                                    {{ $req->approver->name }}</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($req->status == 'pending')
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini? Saldo cuti akan dikembalikan.');">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                        <i class="mdi mdi-close me-1"></i> Batal
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="mdi mdi-calendar-remove text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">Belum ada riwayat cuti.</p>
                                                <a href="{{ route('leave-requests.create') }}"
                                                    class="btn btn-primary btn-sm rounded-pill mt-2">Ajukan Cuti Sekarang</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-4 pb-4 d-flex justify-content-end">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection