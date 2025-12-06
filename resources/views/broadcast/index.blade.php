@extends('layout.master')

@section('title', 'Broadcast')
@section('heading', 'Pesan Broadcast')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Pesan Broadcast</h5>
                @if(auth()->user()->role == 'admin')
                <a href="{{ route('broadcast.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-2"></i>Buat Broadcast
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($broadcasts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pesan</th>
                                    <th>Prioritas</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($broadcasts as $broadcast)
                                <tr>
                                    <td>
                                        <strong>{{ $broadcast->title }}</strong>
                                        @if($broadcast->priority == 'high')
                                            <i class="mdi mdi-alert text-danger ms-1" title="Prioritas Tinggi"></i>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($broadcast->message, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $broadcast->priority == 'high' ? 'danger' : ($broadcast->priority == 'medium' ? 'warning' : 'secondary') }}">
                                            {{ $broadcast->priority }}
                                        </span>
                                    </td>
                                    <td>{{ $broadcast->creator->name ?? 'Unknown' }}</td>
                                    <td>{{ $broadcast->published_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('broadcast.show', $broadcast) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            @if(auth()->user()->role == 'admin')
                                            <a href="{{ route('broadcast.edit', $broadcast) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('broadcast.destroy', $broadcast) }}" method="POST" class="d-inline">
                                                @csrf 
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus broadcast?')" title="Hapus">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $broadcasts->links('pagination::bootstrap-5') }}
                @else
                    <div class="text-center py-5">
                        <i class="mdi mdi-bullhorn-outline display-4 text-muted"></i>
                        <h4 class="text-muted mt-3">Belum ada broadcast</h4>
                        <p class="text-muted mb-4">Tidak ada pesan broadcast untuk saat ini</p>
                        @if(auth()->user()->role == 'admin')
                        <a href="{{ route('broadcast.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus me-2"></i>Buat Broadcast Pertama
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection