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
                                    <th>User & Cabang</th> <!-- Update Header -->
                                    <th>Tipe</th>
                                    <th>Waktu / Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Bukti</th>
                                    <th>Status & Approver</th> <!-- Update Header -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- KOLOM USER & CABANG --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{-- Menampilkan Divisi --}}
                                                        {{ $req->user->division->name ?? '-' }}
                                                        &bull; 
                                                        {{-- UPDATE: Menampilkan Cabang --}}
                                                        <span class="text-primary">{{ $req->user->branch->name ?? 'Pusat' }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ ucfirst($req->type) }}</span>
                                        </td>

                                        <td>
                                            @if ($req->type == 'telat')
                                                {{ $req->start_date->format('d/m/Y') }} 
                                                <br>
                                                <span class="text-muted" style="font-size: 11px;">
                                                    Jam: {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}
                                                </span>
                                            @else
                                                {{ $req->start_date->format('d M') }}
                                                @if($req->end_date)
                                                    - {{ \Carbon\Carbon::parse($req->end_date)->format('d M') }}
                                                @endif
                                            @endif
                                        </td>

                                        <td class="text-muted" style="max-width: 200px; white-space: normal; line-height: 1.2;">
                                            {{ Str::limit($req->reason, 40) }}
                                        </td>
                                        
                                        <td>
                                            @if($req->file_proof)
                                                <a href="{{ asset('storage/' . $req->file_proof) }}" target="_blank" class="text-primary" style="text-decoration: none;">
                                                    <i class="mdi mdi-link"></i> Lihat
                                                </a>
                                            @else - @endif
                                        </td>

                                        {{-- KOLOM STATUS & APPROVER --}}
                                        <td>
                                            @if ($req->status == 'approved')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-success mb-1" style="width: fit-content;">
                                                        <i class="mdi mdi-check-circle"></i> Disetujui
                                                    </span>
                                                    {{-- UPDATE: Menampilkan Nama Approver --}}
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: {{ $req->approver->name ?? 'Admin' }}
                                                    </small>
                                                </div>

                                            @elseif($req->status == 'rejected')
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-opacity-danger mb-1" style="width: fit-content;">
                                                        <i class="mdi mdi-close-circle"></i> Ditolak
                                                    </span>
                                                    {{-- UPDATE: Menampilkan Nama Rejector --}}
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: {{ $req->approver->name ?? 'Admin' }}
                                                    </small>
                                                </div>

                                            @elseif($req->status == 'cancelled')
                                                <span class="badge badge-opacity-secondary">Dibatalkan</span>
                                            @else
                                                <span class="badge badge-opacity-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">Belum ada riwayat data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4 d-flex justify-content-end">
                            {{ $requests->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection