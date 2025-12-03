@extends('layout.master')

@section('title', 'Permintaan Ganti KTP')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Permintaan Ganti KTP</h4>
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Divisi</th>
                                <th>KTP Lama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    {{ $user->name }}
                                    <br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>{{ $user->division->name ?? '-' }}</td>
                                <td>
                                    @if($user->ktp_photo_path)
                                        <a href="{{ asset('storage/' . $user->ktp_photo_path) }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="mdi mdi-eye"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-danger">Tidak ada file</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Tombol Approve --}}
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm text-white" 
                                                onclick="return confirm('Izinkan user ini mengganti KTP?')">
                                                <i class="mdi mdi-check"></i> Izinkan
                                            </button>
                                        </form>

                                        {{-- Tombol Reject --}}
                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm text-white" 
                                                onclick="return confirm('Tolak permintaan?')">
                                                <i class="mdi mdi-close"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="alert alert-info">Tidak ada permintaan ganti KTP saat ini.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection