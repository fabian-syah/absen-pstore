@extends('layout.master')

@section('title')
    Daftar Persetujuan Izin (Pending)
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Verifikasi Izin & Keterlambatan</h4>
                        
                        <div>
                            {{-- TOMBOL MENUJU HISTORY --}}
                            <a href="{{ route('leave-requests.history') }}" class="btn btn-inverse-info btn-sm me-2">
                                <i class="mdi mdi-history"></i> Lihat Riwayat
                            </a>

                            {{-- Tombol Buat Baru --}}
                            @if (in_array(auth()->user()->role, ['user_biasa', 'leader']))
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm">
                                    <i class="mdi mdi-plus"></i> Ajukan Baru
                                </a>
                            @endif
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
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
                                        {{-- KOLOM USER --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-warning rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{ $req->user->division->name ?? '-' }} | 
                                                        <span class="text-primary fw-bold">{{ $req->user->branch->name ?? 'Pusat' }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        {{-- KOLOM TIPE --}}
                                        <td>
                                             @if ($req->type == 'sakit') <span class="badge bg-danger text-white">Sakit</span>
                                             @elseif($req->type == 'izin') <span class="badge bg-info text-white">Izin</span>
                                             @elseif($req->type == 'wfh') <span class="badge bg-primary text-white">WFH</span>
                                             @elseif($req->type == 'cuti') <span class="badge bg-success text-white">Cuti</span>
                                             @else <span class="badge bg-warning text-dark">Telat</span>
                                             @endif
                                        </td>

                                        {{-- KOLOM TANGGAL --}}
                                        <td>
                                            @if ($req->type == 'telat')
                                                {{ $req->start_date->format('d/m/Y') }} <br>
                                                <strong class="text-danger">{{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}</strong>
                                            @else
                                                {{ $req->start_date->format('d M') }}
                                                @if ($req->end_date && $req->end_date != $req->start_date) - {{ $req->end_date->format('d M') }} @endif
                                            @endif
                                        </td>

                                        <td>{{ $req->reason }}</td>
                                        
                                        <td>
                                            @if($req->file_proof)
                                            <a href="{{ asset('storage/' . $req->file_proof) }}" target="_blank" class="btn btn-inverse-secondary btn-icon btn-sm"><i class="mdi mdi-eye"></i></a>
                                            @else - @endif
                                        </td>

                                        {{-- STATUS (Pasti Pending karena di filter controller) --}}
                                        <td><span class="badge badge-opacity-warning">Menunggu</span></td>

                                        {{-- AKSI (APPROVE / REJECT / CANCEL) --}}
                                        <td>
                                            {{-- User Batalkan --}}
                                            @if (auth()->id() == $req->user_id)
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan?')">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-light btn-sm text-danger"><i class="mdi mdi-close-circle"></i> Batal</button>
                                                </form>
                                            @endif

                                       {{--  --}} --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                <form action="{{ route('leave-requests.approve', $req->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-success btn-sm p-2"><i class="mdi mdi-check"></i></button>
                                                </form>
                                                <form action="{{ route('leave-requests.reject', $req->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-danger btn-sm p-2"><i class="mdi mdi-close"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4">Tidak ada pengajuan pending.</td></tr>
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