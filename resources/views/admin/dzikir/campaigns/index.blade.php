@extends('layout.master')

@section('title', 'Campaign Zikir')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Campaign Zikir</h4>
                        <a href="{{ route('admin.dzikir-campaign.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus btn-icon-prepend"></i> Tambah Campaign
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
                                    <th>Target</th>
                                    <th>Progres</th>
                                    <th>Status</th>
                                    <th>Periode</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $key => $campaign)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $campaign->title }} {{ $campaign->emoji }}</td>
                                        <td>{{ number_format($campaign->target, 0, ',', '.') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress" style="width: 100px; height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: {{ $campaign->progress_percent }}%"
                                                         aria-valuenow="{{ $campaign->progress_percent }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small>{{ $campaign->progress_percent }}%</small>
                                            </div>
                                            <small class="text-muted">{{ number_format($campaign->current_count, 0, ',', '.') }}</small>
                                        </td>
                                        <td>
                                            @if($campaign->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Non-aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($campaign->start_date && $campaign->end_date)
                                                <small>{{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}</small>
                                            @elseif($campaign->start_date)
                                                <small>Mulai {{ $campaign->start_date->format('d/m/Y') }}</small>
                                            @else
                                                <small class="text-muted">Tanpa batas</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.dzikir-campaign.edit', $campaign->id) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form action="{{ route('admin.dzikir-campaign.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus campaign ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada campaign.</td>
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
