@extends('layout.master')

@section('title', 'Update Target')
@section('heading', 'Update Progres Pekerjaan')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title">Detail Target</h4>
                        <span class="badge {{ $jobTarget->priority == 'high' ? 'bg-danger' : 'bg-info' }}">
                            Prioritas: {{ ucfirst($jobTarget->priority) }}
                        </span>
                    </div>

                    <form action="{{ route('job-targets.update', $jobTarget->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- AREA READONLY (INFO) UNTUK USER BIASA --}}
                        <div class="form-group mb-3">
                            <label>Judul Pekerjaan</label>
                            <input type="text" name="title" class="form-control" value="{{ $jobTarget->title }}" 
                                {{ auth()->user()->role == 'user_biasa' ? 'readonly' : '' }}>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $jobTarget->start_date->format('Y-m-d') }}"
                                        {{ auth()->user()->role == 'user_biasa' ? 'readonly' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Deadline</label>
                                    <input type="date" name="deadline" class="form-control" value="{{ $jobTarget->deadline->format('Y-m-d') }}"
                                        {{ auth()->user()->role == 'user_biasa' ? 'readonly' : '' }}>
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->role != 'user_biasa')
                            <div class="form-group mb-3">
                                <label>Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3">{{ $jobTarget->description }}</textarea>
                            </div>
                        @else
                            <div class="alert alert-secondary mb-3">
                                <strong>Deskripsi:</strong><br>
                                {{ $jobTarget->description ?? 'Tidak ada deskripsi' }}
                            </div>
                        @endif

                        <hr>
                        <h5 class="mb-3">Update Progres</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Status Saat Ini</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $jobTarget->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $jobTarget->status == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                        <option value="completed" {{ $jobTarget->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                        @if(auth()->user()->role != 'user_biasa')
                                            <option value="canceled" {{ $jobTarget->status == 'canceled' ? 'selected' : '' }}>Dibatalkan</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Persentase Progres (0-100%)</label>
                                    <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ $jobTarget->progress }}">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Update Progres</button>
                        <a href="{{ route('job-targets.index') }}" class="btn btn-light">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection