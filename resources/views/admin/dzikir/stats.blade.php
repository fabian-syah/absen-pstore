@extends('layout.master')

@section('title', 'Total Dzikir User')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Statistik Dzikir User</h4>

                    <form method="GET" action="{{ route('admin.dzikir.stats') }}" class="mb-4 d-flex flex-wrap align-items-end">
                        <div class="form-group mr-3 mb-2">
                            <label for="filter">Filter</label>
                            <select name="filter" id="filter" class="form-control" onchange="toggleFilterFields()">
                                <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Semua Waktu (All Time)</option>
                                <option value="24hours" {{ $filter == '24hours' ? 'selected' : '' }}>24 Jam Terakhir</option>
                                <option value="daily" {{ $filter == 'daily' ? 'selected' : '' }}>Harian</option>
                                <option value="monthly" {{ $filter == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            </select>
                        </div>
                        
                        <div class="form-group mr-3 mb-2" id="date-field" style="display: {{ $filter == 'daily' ? 'block' : 'none' }};">
                            <label for="date">Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ $date }}">
                        </div>

                        <div class="form-group mr-3 mb-2" id="month-field" style="display: {{ $filter == 'monthly' ? 'block' : 'none' }};">
                            <label for="month">Bulan</label>
                            <input type="month" name="month" id="month" class="form-control" value="{{ $month }}">
                        </div>

                        <div class="form-group mb-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Nama User</th>
                                    <th>Nama Zikir</th>
                                    <th>Kategori</th>
                                    <th>Total Dzikir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats as $key => $stat)
                                    <tr>
                                        <td>
                                            @if($key == 0)
                                                <i class="mdi mdi-medal text-warning" style="font-size: 1.5rem;"></i>
                                            @elseif($key == 1)
                                                <i class="mdi mdi-medal" style="color: silver; font-size: 1.5rem;"></i>
                                            @elseif($key == 2)
                                                <i class="mdi mdi-medal" style="color: #cd7f32; font-size: 1.5rem;"></i>
                                            @else
                                                {{ $key + 1 }}
                                            @endif
                                        </td>
                                        <td>{{ $stat->user->name ?? 'User Tidak Ditemukan' }}</td>
                                        <td>{{ $stat->zikir->title ?? '-' }}</td>
                                        <td>
                                            @if($stat->zikir && $stat->zikir->category)
                                                <span class="badge badge-info">{{ implode(', ', array_map('ucfirst', $stat->zikir->category)) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="font-weight-bold text-success">{{ number_format($stat->total_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data dzikir untuk filter ini.</td>
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

@push('scripts')
<script>
    function toggleFilterFields() {
        var filter = document.getElementById('filter').value;
        if (filter === 'daily') {
            document.getElementById('date-field').style.display = 'block';
            document.getElementById('month-field').style.display = 'none';
        } else if (filter === 'monthly') {
            document.getElementById('date-field').style.display = 'none';
            document.getElementById('month-field').style.display = 'block';
        } else {
            document.getElementById('date-field').style.display = 'none';
            document.getElementById('month-field').style.display = 'none';
        }
    }
</script>
@endpush
@endsection
