@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Atur Gaji: {{ $user->name }}</h4>
                <form action="{{ route('employee-salaries.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label>Kategori Karyawan</label>
                        <select name="category" class="form-control">
                            <option value="employee" {{ ($user->employeeSalary->category ?? '') == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                            <option value="promotor" {{ ($user->employeeSalary->category ?? '') == 'promotor' ? 'selected' : '' }}>Promotor</option>
                            <option value="freelance" {{ ($user->employeeSalary->category ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Gaji Pokok</label>
                        <input type="number" name="basic_salary" class="form-control" value="{{ $user->employeeSalary->basic_salary ?? 0 }}">
                    </div>

                    <div class="form-group mb-3">
                        <label>Tunjangan Jabatan</label>
                        <input type="number" name="position_allowance" class="form-control" value="{{ $user->employeeSalary->position_allowance ?? 0 }}">
                    </div>

                    <div class="form-group mb-3">
                        <label>Privilege Owner</label>
                        <input type="number" name="owner_privilege" class="form-control" value="{{ $user->employeeSalary->owner_privilege ?? 0 }}">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Gaji Harian (Khusus Freelance)</label>
                        <input type="number" name="daily_salary" class="form-control" value="{{ $user->employeeSalary->daily_salary ?? 0 }}">
                    </div>

                    <button type="submit" class="btn btn-success text-white w-100">Simpan Master Gaji</button>
                    <a href="{{ route('employee-salaries.index') }}" class="btn btn-light w-100 mt-2">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection