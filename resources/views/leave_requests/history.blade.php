@extends('layout.master')

@section('title')
    Riwayat Izin
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Riwayat Pengajuan (Selesai)</h4>
                        
                        {{-- TOMBOL KEMBALI KE DAFTAR PENDING --}}
                        <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar Aktif
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Bukti</th>
                                    <th>Status Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{ $req->user->division->name ?? '-' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            {{-- Badge Tipe --}}
                                            <span class="badge bg-light text-dark border">{{ ucfirst($req->type) }}</span>
                                        </td>

                                        <td>
                                            @if ($req->type == 'telat')
                                                {{ $req->start_date->format('d/m/Y') }} <span class="text-muted">({{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }})</span>
                                            @else
                                                {{ $req->start_date->format('d M') }}
                                            @endif
                                        </td>

                                        <td class="text-muted">{{ Str::limit($req->reason, 30) }}</td>
                                        
                                        <td>
                                            @if($req->file_proof)
                                                <a href="{{ asset('storage/' . $req->file_proof) }}" target="_blank" class="text-primary"><i class="mdi mdi-link"></i> Lihat</a>
                                            @else - @endif
                                        </td>

                                        <td>
                                            @if ($req->status == 'approved')
                                                <span class="badge badge-opacity-success"><i class="mdi mdi-check-circle"></i> Disetujui</span>
                                            @elseif($req->status == 'rejected')
                                                <span class="badge badge-opacity-danger"><i class="mdi mdi-close-circle"></i> Ditolak</span>
                                            @elseif($req->status == 'cancelled')
                                                <span class="badge badge-opacity-secondary">Dibatalkan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">Belum ada riwayat data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $requests->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection