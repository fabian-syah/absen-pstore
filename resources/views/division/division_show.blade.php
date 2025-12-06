@extends('layout.master')

@section('title')
    Detail Divisi
@endsection

@section('heading')
    Detail Anggota Divisi
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Divisi: {{ $division->name }}</h4>
                            <p class="text-muted mb-0">Total Anggota: {{ $members->total() }} Orang</p>
                        </div>
                        <a href="{{ route('divisions.index') }}" class="btn btn-light btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th> # </th>
                                    <th> Nama Anggota </th>
                                    <th> Role </th>
                                    <th> Cabang (Lokasi Kerja) </th>
                                    <th> Status </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($members as $key => $member)
                                    <tr>
                                        <td> {{ $members->firstItem() + $key }} </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- Avatar Kecil --}}
                                                <div class="me-2">
                                                    @if($member->profile_photo_path)
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($member->profile_photo_path) }}" 
                                                             alt="foto" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center" 
                                                             style="width: 35px; height: 35px; font-size: 12px;">
                                                            {{ substr($member->name, 0, 2) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="fw-bold">{{ $member->name }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $member->login_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($member->role == 'admin')
                                                <span class="badge badge-danger">Super Admin</span>
                                            @elseif($member->role == 'audit')
                                                <span class="badge badge-warning">Audit</span>
                                            @elseif($member->role == 'leader')
                                                <span class="badge badge-success">Leader</span>
                                            @else
                                                <span class="badge badge-info">Karyawan</span>
                                            @endif
                                        </td>
                                        <td> 
                                            {{ $member->branch->name ?? 'Tidak ada (Pusat)' }} 
                                        </td>
                                        <td>
                                            <span class="badge badge-outline-primary">Aktif</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="mdi mdi-account-off text-muted display-4"></i>
                                            <p class="text-muted mt-2">Belum ada anggota di divisi ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $members->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection