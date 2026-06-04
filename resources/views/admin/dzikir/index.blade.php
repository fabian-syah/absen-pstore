@extends('layout.master')

@section('title', 'Setting Dzikir')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Setting Dzikir</h4>
                        <a href="{{ route('admin.dzikir.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus btn-icon-prepend"></i> Tambah Dzikir
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Target Default</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($zikirs as $key => $zikir)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $zikir->title }}</td>
                                        <td>
                                            @if($zikir->category == 'pagi')
                                                <span class="badge badge-info">Pagi</span>
                                            @elseif($zikir->category == 'petang')
                                                <span class="badge badge-warning">Petang</span>
                                            @else
                                                <span class="badge badge-secondary">Semua</span>
                                            @endif
                                        </td>
                                        <td>{{ $zikir->default_target }}</td>
                                        <td>
                                            <a href="{{ route('admin.dzikir.edit', $zikir->id) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form action="{{ route('admin.dzikir.destroy', $zikir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus dzikir ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data dzikir.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
