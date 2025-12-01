@extends('layout.master')

@section('title', 'Buat Target Baru')
@section('heading', 'Buat Job Desk / Target')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Target Baru</h4>
                    <form action="{{ route('job-targets.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label>Judul Pekerjaan / Target <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="Contoh: Penjualan 100 Unit">
                        </div>

                        <div class="form-group mb-3">
                            <label>Ditugaskan Kepada <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select select2" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} - {{ $user->division->name ?? 'N/A' }} ({{ $user->branch->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="deadline" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Prioritas</label>
                            <select name="priority" class="form-select">
                                <option value="low">Rendah</option>
                                <option value="medium" selected>Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Deskripsi Detail</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan Target</button>
                        <a href="{{ route('job-targets.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection