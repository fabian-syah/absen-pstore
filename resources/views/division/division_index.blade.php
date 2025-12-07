@extends('layout.master')

@section('title')
    Data Divisi
@endsection

@section('heading')
    Manajemen Divisi
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Daftar Divisi</h4>

                    {{-- Container Flex untuk Tombol Tambah & Search --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        
                        {{-- Tombol Tambah Data --}}
                        <a href="{{ route('divisions.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Divisi Baru
                        </a>

                        {{-- Form Pencarian --}}
                        <form action="{{ route('divisions.index') }}" method="GET" class="d-flex">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama divisi..." value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i> Cari
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('divisions.index') }}" class="btn btn-outline-danger" title="Reset">
                                        <i class="mdi mdi-close"></i>
                                    </a>
                                @endif
                            </div>
                        </form>

                    </div>

                    {{-- Notifikasi Sukses --}}
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Notifikasi Gagal Hapus (karena relasi) --}}
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Nama Divisi </th>
                                    <th> Dibuat Pada </th>
                                    <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop data divisi --}}
                                @forelse ($divisions as $key => $division)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> {{ $division->name }} </td>
                                        <td> {{ $division->created_at->format('d M Y') }} </td>
                                        <td>
                                            {{-- TOMBOL LIHAT (BARU) --}}
                                            <a href="{{ route('divisions.show', $division->id) }}"
                                                class="btn btn-inverse-info btn-icon" title="Lihat Anggota">
                                                <i class="mdi mdi-eye"></i>
                                            </a>

                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('divisions.edit', $division->id) }}"
                                                class="btn btn-inverse-warning btn-icon">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            {{-- Tombol Hapus (WAJIB pakai form) --}}
                                            <form action="{{ route('divisions.destroy', $division->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus divisi ini? User terkait akan kehilangan divisi.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-icon">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Jika data kosong --}}
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            @if(request('search'))
                                                <p class="text-muted mb-0">Divisi "{{ request('search') }}" tidak ditemukan.</p>
                                                <a href="{{ route('divisions.index') }}">Reset Pencarian</a>
                                            @else
                                                <p class="text-muted mb-0">Belum ada data divisi.</p>
                                            @endif
                                        </td>
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