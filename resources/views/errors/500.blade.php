@extends('layout.master')

@section('title', 'Server Error')

@section('content')
<div class="container text-center py-5">
    <div class="display-1 text-danger mb-4"><i class="mdi mdi-alert-octagon-outline"></i> 500</div>
    <h2 class="fw-bold text-dark">Whoops! Galat Server Internal</h2>
    <p class="text-muted mb-4">Kami menemukan masalah internal. Silakan coba beberapa saat lagi atau hubungi administrator.</p>
    <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="mdi mdi-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
