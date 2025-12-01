@extends('layout.master')

@section('title')
    Daftar Izin & Cuti
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Daftar Pengajuan Izin</h4>
                        <div>
                            {{-- Tombol History untuk Audit/Admin --}}
                            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                <a href="{{ route('audit.late.history') }}" class="btn btn-inverse-info btn-sm me-2">
                                    <i class="mdi mdi-history"></i> Riwayat Semua
                                </a>
                            @endif

                            {{-- Tombol Ajukan Baru --}}
                            @if (in_array(auth()->user()->role, ['user_biasa', 'leader', 'admin', 'audit', 'security']))
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
                                    <th>Status & Approver</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        {{-- 1. Info User --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2"
                                                    style="width: 35px; height: 35px; font-weight:bold;">
                                                    {{ substr($req->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $req->user->name }}</span>
                                                    <small class="text-muted" style="font-size:11px;">
                                                        {{ $req->user->division->name ?? '-' }} | 
                                                        {{ $req->user->branch->name ?? 'Pusat' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. Tipe Izin --}}
                                        <td>
                                            @php
                                                $badges = [
                                                    'sakit' => 'danger',
                                                    'izin' => 'info',
                                                    'wfh' => 'primary',
                                                    'cuti' => 'success',
                                                    'telat' => 'warning'
                                                ];
                                                $badgeColor = $badges[$req->type] ?? 'secondary';
                                                $label = $req->type == 'wfh' ? 'WFH / Dinas' : ucfirst($req->type);
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }} text-white">{{ $label }}</span>
                                        </td>

                                        {{-- 3. Waktu --}}
                                        <td>
                                            @if ($req->type == 'telat')
                                                <div class="text-dark" style="font-size: 13px;">
                                                    {{ $req->start_date->format('d/m/Y') }}<br>
                                                    <strong class="text-danger">
                                                        <i class="mdi mdi-clock"></i> 
                                                        {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}
                                                    </strong>
                                                </div>
                                            @else
                                                <div class="text-dark" style="font-size: 13px;">
                                                    {{ $req->start_date->format('d M') }}
                                                    @if ($req->end_date && $req->end_date != $req->start_date)
                                                        - {{ $req->end_date->format('d M') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 4. Alasan --}}
                                        <td class="text-wrap" style="max-width: 200px;">{{ $req->reason }}</td>

                                        {{-- 5. Bukti --}}
                                        <td>
                                            @if ($req->file_proof)
                                                <a href="{{ asset('storage/' . $req->file_proof) }}" target="_blank"
                                                    class="btn btn-inverse-secondary btn-icon btn-sm">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @else - @endif
                                        </td>

                                        {{-- 6. STATUS & APPROVER (Bagian ini yang diperbaiki) --}}
                                        <td>
                                            @if ($req->status == 'pending')
                                                <span class="badge badge-opacity-warning">Menunggu</span>
                                            @elseif ($req->status == 'approved')
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-success mb-1">Disetujui</span>
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: <strong>{{ $req->approver->name ?? '-' }}</strong>
                                                    </small>
                                                </div>
                                            @elseif ($req->status == 'rejected')
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-danger mb-1">Ditolak</span>
                                                    <small class="text-muted" style="font-size: 10px;">
                                                        Oleh: <strong>{{ $req->approver->name ?? '-' }}</strong>
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge badge-opacity-secondary">Dibatalkan</span>
                                            @endif
                                        </td>

                                        {{-- 7. Aksi --}}
                                        <td>
                                            {{-- User Batalkan Sendiri --}}
                                            @if (auth()->id() == $req->user_id && $req->status == 'pending')
                                                <form action="{{ route('leave-requests.cancel', $req->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Batalkan pengajuan?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger">
                                                        <i class="mdi mdi-close-circle"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Admin/Audit Approve & Reject --}}
                                            @if (in_array(auth()->user()->role, ['admin', 'audit']) && $req->status == 'pending')
                                                <form action="{{ route('late.approve', $req->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm p-2" title="Setujui" onclick="return confirm('Setujui?')">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('late.reject', $req->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-danger btn-sm p-2" title="Tolak" onclick="return confirm('Tolak?')">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="mdi mdi-file-document-outline text-muted" style="font-size: 40px;"></i>
                                                <p class="text-muted mt-2">Belum ada data pengajuan.</p>
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