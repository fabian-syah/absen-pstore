@extends('layout.master')

@section('title', 'Hasil Push Notification')
@section('heading', 'Hasil Pengiriman Push Notification')

@section('content')
<div class="row">
    {{-- Summary Card --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-bell-check me-2"></i>Ringkasan Pengiriman
                </h5>
            </div>
            <div class="card-body">
                {{-- Pesan yang dikirim --}}
                <div class="alert alert-light border mb-4">
                    <div class="d-flex align-items-start">
                        <i class="mdi mdi-message-text mdi-24px text-primary me-3 mt-1"></i>
                        <div>
                            <h6 class="mb-1">{{ $title }}</h6>
                            <p class="mb-0 text-muted">{{ $body }}</p>
                        </div>
                    </div>
                </div>

                {{-- Statistik --}}
                <div class="row text-center mb-4">
                    <div class="col-md-4 col-12 mb-3">
                        <div class="card border" style="background: #f0f4ff;">
                            <div class="card-body py-3">
                                <h2 class="mb-1" style="color: #0d6efd;">{{ $results['total'] }}</h2>
                                <small style="color: #333;">Total Target</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <div class="card border" style="background: #e8f5e9;">
                            <div class="card-body py-3">
                                <h2 class="mb-1" style="color: #2e7d32;">{{ count($results['success']) }}</h2>
                                <small style="color: #333;">Berhasil Terkirim</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <div class="card border" style="background: #ffebee;">
                            <div class="card-body py-3">
                                <h2 class="mb-1" style="color: #c62828;">{{ count($results['failed']) }}</h2>
                                <small style="color: #333;">Gagal Terkirim</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                @php
                    $successPercent = $results['total'] > 0 ? round((count($results['success']) / $results['total']) * 100) : 0;
                    $failedPercent = $results['total'] > 0 ? round((count($results['failed']) / $results['total']) * 100) : 0;
                @endphp
                <div class="progress mb-4" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $successPercent }}%">
                        {{ $successPercent }}% Berhasil
                    </div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $failedPercent }}%">
                        {{ $failedPercent }}% Gagal
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Berhasil --}}
    @if(count($results['success']) > 0)
    <div class="col-lg-6 col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="mdi mdi-check-circle me-2"></i>Berhasil ({{ count($results['success']) }})
                </h6>
                <span class="badge bg-white text-success">{{ count($results['success']) }} user</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Cabang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['success'] as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="mdi mdi-check-circle text-success me-1"></i>
                                    {{ $item['name'] }}
                                </td>
                                <td><small class="text-muted">{{ $item['branch'] ?? '-' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Daftar Gagal --}}
    @if(count($results['failed']) > 0)
    <div class="col-lg-6 col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="mdi mdi-close-circle me-2"></i>Gagal ({{ count($results['failed']) }})
                </h6>
                <span class="badge bg-white text-danger">{{ count($results['failed']) }} user</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Cabang</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['failed'] as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="mdi mdi-close-circle text-danger me-1"></i>
                                    {{ $item['name'] }}
                                </td>
                                <td><small class="text-muted">{{ $item['branch'] ?? '-' }}</small></td>
                                <td><small class="text-danger">{{ $item['reason'] }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Action Buttons --}}
    <div class="col-12">
        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('push-broadcast.create') }}" class="btn btn-primary">
                <i class="mdi mdi-send me-2"></i>Kirim Lagi
            </a>
            <a href="{{ route('broadcast.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left me-2"></i>Kembali ke Broadcast
            </a>
        </div>
    </div>
</div>
@endsection
