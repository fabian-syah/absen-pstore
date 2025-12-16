@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Gaji: {{ $salary->user->name }}</h4>
                <p class="text-muted">Periode: {{ $salary->month }} / {{ $salary->year }}</p>

                <form action="{{ route('salaries.update', $salary->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="category">Kategori Gaji</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="promotor" {{ $salary->category == 'promotor' ? 'selected' : '' }}>Promotor</option>
                            <option value="freelance" {{ $salary->category == 'freelance' ? 'selected' : '' }}>Freelance</option>
                            <option value="employee" {{ $salary->category == 'employee' ? 'selected' : '' }}>Karyawan Biasa</option>
                        </select>
                    </div>

                    <hr>

                    {{-- FORM PROMOTOR --}}
                    <div id="form-promotor" style="display: {{ $salary->category == 'promotor' ? 'block' : 'none' }};">
                        <h5 class="text-info">Detail Promotor</h5>
                        <div class="form-group">
                            <label>Gaji Pokok 1 Bulan</label>
                            <input type="number" name="promotor_basic_salary" class="form-control" value="{{ $salary->promotor_basic_salary }}">
                        </div>
                        <div class="form-group">
                            <label>Bonus</label>
                            <input type="number" name="promotor_bonus" class="form-control" value="{{ $salary->promotor_bonus }}">
                        </div>
                    </div>

                    {{-- FORM FREELANCE --}}
                    <div id="form-freelance" style="display: {{ $salary->category == 'freelance' ? 'block' : 'none' }};">
                        <h5 class="text-warning">Detail Freelance</h5>
                        <div class="alert alert-warning">Jumlah kehadiran akan dihitung ulang secara otomatis saat disimpan.</div>
                        <div class="form-group">
                            <label>Gaji Per Hari</label>
                            <input type="number" name="freelance_daily_salary" class="form-control" value="{{ $salary->freelance_daily_salary }}">
                        </div>
                    </div>

                    {{-- FORM KARYAWAN BIASA --}}
                    <div id="form-employee" style="display: {{ $salary->category == 'employee' ? 'block' : 'none' }};">
                        <h5 class="text-success">Detail Karyawan Biasa</h5>
                        <div class="form-group">
                            <label>Gaji Pokok (Max 6jt)</label>
                            <input type="number" name="employee_basic_salary" class="form-control" max="6000000" value="{{ $salary->employee_basic_salary }}">
                        </div>
                        <div class="form-group">
                            <label>Tunjangan Jabatan</label>
                            <input type="number" name="employee_position_allowance" class="form-control" value="{{ $salary->employee_position_allowance }}">
                        </div>
                        <div class="form-group">
                            <label>Previlage Owner</label>
                            <input type="number" name="employee_owner_privilege" class="form-control" value="{{ $salary->employee_owner_privilege }}">
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $salary->notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Update Gaji</button>
                    <a href="{{ route('salaries.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const formPromotor = document.getElementById('form-promotor');
        const formFreelance = document.getElementById('form-freelance');
        const formEmployee = document.getElementById('form-employee');

        categorySelect.addEventListener('change', function() {
            const val = this.value;
            formPromotor.style.display = 'none';
            formFreelance.style.display = 'none';
            formEmployee.style.display = 'none';

            if(val === 'promotor') formPromotor.style.display = 'block';
            if(val === 'freelance') formFreelance.style.display = 'block';
            if(val === 'employee') formEmployee.style.display = 'block';
        });
    });
</script>
@endsection