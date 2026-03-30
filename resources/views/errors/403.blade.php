@extends('layout.master')

@section('title', 'Akses Ditolak')

@section('content')
<div class="container text-center py-5">
    <div class="display-1 text-danger mb-4"><i class="mdi mdi-shield-lock-outline"></i> 403</div>
    <h2 class="fw-bold text-dark">Akses Ditolak</h2>
    <p class="text-muted mb-4">Maaf, peran (role) Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="mdi mdi-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
