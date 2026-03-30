@extends('layout.master')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="container text-center py-5">
    <div class="display-1 text-muted mb-4"><i class="mdi mdi-alert-circle-outline"></i> 404</div>
    <h2 class="fw-bold text-dark">Whoops! Halaman Tidak Ditemukan</h2>
    <p class="text-muted mb-4">Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
    <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4">
        <i class="mdi mdi-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
