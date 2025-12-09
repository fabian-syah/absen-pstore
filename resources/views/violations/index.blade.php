@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Riwayat Pelanggaran</h4>
                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                        <a href="{{ route('violations.create') }}" class="btn btn-primary btn-sm text-white">
                            <i class="mdi mdi-plus"></i> Tambah Pelanggaran
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Nama Pelanggar</th>
                                <th>Judul</th>
                                <th>Deskripsi & Ket</th>
                                <th>Bukti Foto</th>
                                <th>Pelapor</th>
                                <th>Tanggal</th>
                                @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $v)
                                <tr>
                                    <td>
                                        @if($v->category == 'berat')
                                            <label class="badge badge-danger">BERAT</label>
                                        @elseif($v->category == 'sedang')
                                            <label class="badge badge-warning">SEDANG</label>
                                        @else
                                            <label class="badge badge-info">RINGAN</label>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">{{ $v->user->name ?? 'User Terhapus' }}</td>
                                    <td>{{ $v->title }}</td>
                                    <td>
                                        <p class="mb-1">{{ Str::limit($v->description, 50) }}</p>
                                        <small class="text-muted">Ket: {{ $v->notes ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($v->photo_path)
                                            <a href="{{ asset('storage/' . $v->photo_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $v->photo_path) }}" alt="Bukti" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            </a>
                                        @else
                                            <span class="text-muted text-small">Tidak ada foto</span>
                                        @endif
                                    </td>
                                    <td>{{ $v->reporter->name ?? 'Sistem' }}</td>
                                    <td>{{ $v->created_at->format('d M Y') }}</td>
                                    
                                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                        <td>
                                            <a href="{{ route('violations.edit', $v->id) }}" class="btn btn-warning btn-sm icon-btn">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('violations.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm icon-btn">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pelanggaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection