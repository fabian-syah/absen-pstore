@extends('layout.master')

@section('title')
    Daftar Izin
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Daftar Izin & Keterlambatan</h4>
                        {{-- Tombol Buat Baru (Hanya User Biasa/Leader) --}}
                        @if (in_array(auth()->user()->role, ['user_biasa', 'leader']))
                            <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Ajukan Baru
                            </a>
                        @endif
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
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted"
                                                        style="font-size:11px;">{{ $req->user->division->name ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($req->type == 'sakit')
                                                <span class="badge bg-danger text-white">Sakit</span>
                                            @elseif($req->type == 'izin')
                                                <span class="badge bg-info text-white">Izin</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Telat</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($req->type == 'sakit')
                                                <span class="badge bg-danger text-white">Sakit</span>
                                            @elseif($req->type == 'izin')
                                                <span class="badge bg-info text-white">Izin</span>
                                            @elseif($req->type == 'wfh')
                                                {{-- Tambahkan kondisi khusus untuk WFH --}}
                                                <span class="badge bg-primary text-white">WFH / Dinas</span>
                                            @elseif($req->type == 'cuti')
                                                {{-- Tambahkan kondisi untuk Cuti juga biar aman --}}
                                                <span class="badge bg-success text-white">Cuti</span>
                                            @else
                                                {{-- Sisanya baru dianggap Telat --}}
                                                <span class="badge bg-warning text-dark">Telat</span>
                                            @endif
                                        </td>
                                        <td class="text-wrap" style="max-width: 200px;">{{ $req->reason }}</td>
                                        <td>
                                            <a href="{{ asset('storage/' . $req->file_proof) }}" target="_blank"
                                                class="btn btn-inverse-secondary btn-icon btn-sm">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                        </td>
                                        <td>
                                            @if ($req->status == 'approved')
                                                <span class="badge badge-opacity-success">Disetujui</span>
                                            @elseif($req->status == 'rejected')
                                                <span class="badge badge-opacity-danger">Ditolak</span>
                                            @elseif($req->status == 'cancelled')
                                                <span class="badge badge-opacity-secondary">Batal</span>
                                            @else
                                                <span class="badge badge-opacity-warning">Menunggu</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- USER: BATALKAN / SAMPAI KANTOR --}}
                                            @if (auth()->id() == $req->user_id && !in_array($req->status, ['cancelled', 'rejected']))
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Konfirmasi tindakan ini?')">
                                                    @csrf @method('PATCH')
                                                    @if ($req->type == 'telat' && $req->status == 'approved')
                                                        <button type="submit" class="btn btn-success btn-sm text-white"
                                                            title="Saya sudah sampai">
                                                            <i class="mdi mdi-check-circle"></i> Sampai
                                                        </button>
                                                    @elseif($req->status == 'pending')
                                                        <button type="submit" class="btn btn-light btn-sm text-danger"
                                                            title="Batalkan Pengajuan">
                                                            <i class="mdi mdi-close-circle"></i> Batal
                                                        </button>
                                                    @endif
                                                </form>
                                            @endif

                                            {{-- ADMIN/AUDIT: APPROVE & REJECT --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']) && $req->status == 'pending')
                                                <form action="{{ route('leave-requests.approve', $req->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-success btn-sm p-2" title="Setujui"><i
                                                            class="mdi mdi-check"></i></button>
                                                </form>
                                                <form action="{{ route('leave-requests.reject', $req->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-danger btn-sm p-2" title="Tolak"><i
                                                            class="mdi mdi-close"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Belum ada data pengajuan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
