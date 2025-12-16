@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Buat Data Gaji Baru</h4>
                <p class="card-description">
                    Input data gaji untuk Promotor, Freelance, atau Karyawan Biasa.
                </p>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('salaries.store') }}" method="POST">
                    @csrf
                    
                    {{-- Pilihan Periode --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Bulan</label>
                            <select name="month" id="month" class="form-control" required>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ date('m') == sprintf('%02d', $i) ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun</label>
                            <select name="year" id="year" class="form-control" required>
                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pilih Karyawan --}}
                    <div class="form-group">
                        <label for="user_id">Pilih Karyawan (Cari Nama/Cabang)</label>
                        {{-- Gunakan class 'select2' jika template Anda punya plugin select2, jika tidak, browser modern tetap bisa search sederhana --}}
                        <select name="user_id" id="user_id" class="form-control" required style="width:100%">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} - {{ $user->branch->name ?? 'No Branch' }} ({{ $user->division->name ?? 'No Div' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Kategori --}}
                    <div class="form-group">
                        <label for="category">Kategori Gaji</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="promotor">Promotor</option>
                            <option value="freelance">Freelance</option>
                            <option value="employee">Karyawan Biasa</option>
                        </select>
                    </div>

                    <hr>

                    {{-- FORM PROMOTOR --}}
                    <div id="form-promotor" style="display: none;">
                        <h5 class="text-info">Detail Promotor</h5>
                        <div class="form-group">
                            <label>Gaji Pokok 1 Bulan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="promotor_basic_salary" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Bonus</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="promotor_bonus" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>

                    {{-- FORM FREELANCE --}}
                    <div id="form-freelance" style="display: none;">
                        <h5 class="text-warning">Detail Freelance</h5>
                        <div class="alert alert-warning">
                            <i class="mdi mdi-information-outline"></i> 
                            Sistem akan otomatis menghitung jumlah kehadiran (Masuk & WFH) pada bulan & tahun yang dipilih.
                        </div>
                        
                        <div class="form-group">
                            <label>Total Kehadiran Terdeteksi (Otomatis)</label>
                            <input type="text" id="attendance_preview" class="form-control" readonly value="Pilih user & bulan dulu...">
                        </div>

                        <div class="form-group">
                            <label>Gaji Per Hari</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="freelance_daily_salary" class="form-control" placeholder="Contoh: 100000">
                            </div>
                        </div>
                    </div>

                    {{-- FORM KARYAWAN BIASA --}}
                    <div id="form-employee" style="display: none;">
                        <h5 class="text-success">Detail Karyawan Biasa</h5>
                        <div class="form-group">
                            <label>Gaji Pokok (Maksimal Rp 6.000.000)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="employee_basic_salary" class="form-control" max="6000000" placeholder="0">
                            </div>
                            <small class="text-danger">* Maksimal 6 Juta</small>
                        </div>
                        <div class="form-group">
                            <label>Tunjangan Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="employee_position_allowance" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Previlage Owner</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="employee_owner_privilege" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Simpan Gaji</button>
                    <a href="{{ route('salaries.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT PENGATUR TAMPILAN & AJAX --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const formPromotor = document.getElementById('form-promotor');
        const formFreelance = document.getElementById('form-freelance');
        const formEmployee = document.getElementById('form-employee');
        
        const userIdSelect = document.getElementById('user_id');
        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');
        const attendancePreview = document.getElementById('attendance_preview');

        // Fungsi Ganti Form
        categorySelect.addEventListener('change', function() {
            const val = this.value;
            formPromotor.style.display = 'none';
            formFreelance.style.display = 'none';
            formEmployee.style.display = 'none';

            if(val === 'promotor') formPromotor.style.display = 'block';
            if(val === 'freelance') {
                formFreelance.style.display = 'block';
                checkAttendance(); // Cek absen pas pilih freelance
            }
            if(val === 'employee') formEmployee.style.display = 'block';
        });

        // Fungsi Cek Absensi (AJAX)
        function checkAttendance() {
            const userId = userIdSelect.value;
            const month = monthSelect.value;
            const year = yearSelect.value;

            if(userId && month && year && categorySelect.value === 'freelance') {
                attendancePreview.value = "Sedang menghitung...";
                
                fetch(`{{ route('api.check-attendance') }}?user_id=${userId}&month=${month}&year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        attendancePreview.value = data.count + " Hari (Present + WFH)";
                    })
                    .catch(error => {
                        attendancePreview.value = "Gagal mengambil data";
                        console.error('Error:', error);
                    });
            }
        }

        // Trigger cek absen jika user/bulan/tahun berubah saat mode freelance aktif
        userIdSelect.addEventListener('change', checkAttendance);
        monthSelect.addEventListener('change', checkAttendance);
        yearSelect.addEventListener('change', checkAttendance);
    });
</script>
@endsection