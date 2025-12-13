@extends('layout.master')

@section('title', 'Target & Pencapaian')
@section('heading', 'Dashboard Target Pstore')

@section('content')

{{-- HEADER & BUTTON CREATE --}}
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Manajemen Target & Prestasi</h3>
        <p class="text-muted mb-0">Monitor performa Cabang {{ auth()->user()->branch->name ?? 'Pusat' }} dan Individu.</p>
    </div>
    <div>
        {{-- Tombol Create --}}
        <a href="{{ route('job-targets.create') }}" class="btn btn-primary btn-lg shadow-sm rounded-4 px-4 fw-bold">
            <i class="mdi mdi-plus-circle-outline me-1"></i> Buat Target / Pencapaian
        </a>
    </div>
</div>

{{-- SECTION 1: CABANG / TIM (Target + Pencapaian) --}}
{{-- Card ini berisi Data Gabungan Target & Achievement Tim --}}
<div class="card card-rounded shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
            <i class="mdi mdi-office-building text-primary mdi-24px"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark">🏢 Target & Pencapaian Cabang</h5>
            <small class="text-muted">Fokus tim dan riwayat keberhasilan cabang ini</small>
        </div>
    </div>
    <div class="card-body p-4">
        {{-- Include Tabs & Filter untuk Cabang --}}
        @include('job_targets.partials.period_tabs', ['idPrefix' => 'branch', 'dataCollection' => $teamData])
    </div>
</div>

{{-- SECTION 2: PRIBADI (Target + Pencapaian) --}}
{{-- Card ini berisi Data Gabungan Target & Achievement Pribadi --}}
<div class="card card-rounded shadow-sm border-0 mb-5">
    <div class="card-header bg-gradient-info text-white border-bottom py-3 d-flex align-items-center">
        <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3">
            <i class="mdi mdi-account-star text-white mdi-24px"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-white">👤 Target & Pencapaian Pribadi</h5>
            <small class="text-white opacity-75">Daftar tanggung jawab dan prestasi Anda</small>
        </div>
    </div>
    <div class="card-body p-4">
        {{-- Include Tabs & Filter untuk Pribadi --}}
        @include('job_targets.partials.period_tabs', ['idPrefix' => 'personal', 'dataCollection' => $personalData])
    </div>
</div>

{{-- MODAL UPDATE STATUS --}}
@include('job_targets.partials.modal_update')

{{-- STYLE CSS KHUSUS --}}
<style>
    /* Card Styling */
    .card-rounded { 
        border-radius: 16px; 
        overflow: hidden; 
    }
    
    /* Header Gradient Personal */
    .bg-gradient-info { 
        background: linear-gradient(45deg, #198ae3, #4b49ac); 
    }
    
    /* STYLE MEWAH BINTANG (LEVEL 1-3) */
    .star-badge-3 { 
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); 
        color: #000; 
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); 
        border: 1px solid #d4af37; 
    }
    .star-badge-2 { 
        background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%); 
        color: #333; 
        border: 1px solid #b0b0b0; 
    }
    .star-badge-1 { 
        background: #f8f9fa; 
        color: #6c757d; 
        border: 1px solid #dee2e6; 
    }
    
    /* Animasi Glow untuk Level 3 */
    .star-animation { 
        animation: glow 2s infinite; 
    }
    @keyframes glow { 
        0% { box-shadow: 0 0 5px #FFD700; } 
        50% { box-shadow: 0 0 15px #FFD700; } 
        100% { box-shadow: 0 0 5px #FFD700; } 
    }

    /* Style Tab Custom */
    .nav-pills-custom .nav-link { 
        background: #f8f9fa; 
        color: #6c757d; 
        border: 1px solid #e9ecef; 
        transition: all 0.3s;
    }
    .nav-pills-custom .nav-link.active { 
        background: #4b49ac; 
        color: #fff; 
        border-color: #4b49ac; 
        box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2);
    }
</style>

{{-- JAVASCRIPT LOGIC FILTERING --}}
<script>
    /**
     * Fungsi untuk memfilter baris tabel berdasarkan input user.
     * @param {string} containerId - ID unik kombinasi prefix dan periode (contoh: 'branch-daily')
     */
    function applyFilter(containerId) {
        // 1. Ambil Container Filter (Tempat input berada) & Container Data (Tempat list item berada)
        let filterBox = document.getElementById('filter-container-' + containerId);
        let dataContainer = document.getElementById('data-container-' + containerId);
        
        if (!filterBox || !dataContainer) return;

        // 2. Ambil Nilai Input (Jika ada)
        let dateInput = filterBox.querySelector('.filter-input-date');
        let monthInput = filterBox.querySelector('.filter-input-month');
        let yearInput = filterBox.querySelector('.filter-input-year');

        let dateVal = dateInput ? dateInput.value : '';
        let monthVal = monthInput ? monthInput.value : '';
        let yearVal = yearInput ? yearInput.value : '';

        // 3. Loop Semua Item di Container Data
        let items = dataContainer.querySelectorAll('.filterable-item');
        
        items.forEach(item => {
            // Ambil data attribut dari setiap baris (Format dari backend: YYYY-MM-DD)
            let itemDate = item.getAttribute('data-date');   
            let itemMonth = item.getAttribute('data-month'); // YYYY-MM
            let itemYear = item.getAttribute('data-year');   // YYYY

            let show = true;

            // --- LOGIKA FILTER (AND Logic) ---
            
            // A. Filter Harian (Date Specific)
            // Jika user mengisi tanggal lengkap, data harus match persis
            if (dateVal && itemDate !== dateVal) {
                show = false;
            }
            
            // B. Filter Bulanan
            // Jika Tanggal kosong TAPI Bulan diisi -> Cocokkan Bulan
            if (!dateVal && monthVal && itemMonth !== monthVal) {
                show = false;
            }

            // C. Filter Tahunan
            // Jika Tanggal & Bulan kosong TAPI Tahun diisi -> Cocokkan Tahun
            if (!dateVal && !monthVal && yearVal && itemYear !== yearVal) {
                show = false;
            }

            // --- EKSEKUSI ---
            if (show) {
                item.classList.remove('d-none'); // Tampilkan
            } else {
                item.classList.add('d-none');    // Sembunyikan
            }
        });

        // 4. Handle Pesan "Tidak Ada Data" (Empty State)
        // Cek setiap tbody di dalam container data (karena ada 2 tabel: On Going & History)
        let tables = dataContainer.querySelectorAll('tbody');
        tables.forEach(tbody => {
            // Hitung berapa baris yang terlihat (tidak punya class d-none)
            let visibleRows = tbody.querySelectorAll('.filterable-item:not(.d-none)');
            let msgRow = tbody.querySelector('.no-data-message');
            
            if (msgRow) {
                if (visibleRows.length === 0) {
                    msgRow.classList.remove('d-none'); // Munculkan pesan kosong
                } else {
                    msgRow.classList.add('d-none');    // Sembunyikan pesan kosong
                }
            }
        });
    }

    /**
     * Fungsi untuk mereset filter ke kondisi awal (tampilkan semua).
     */
    function resetFilter(containerId) {
        let filterBox = document.getElementById('filter-container-' + containerId);
        let dataContainer = document.getElementById('data-container-' + containerId);

        if (!filterBox || !dataContainer) return;

        // 1. Kosongkan semua input
        let inputs = filterBox.querySelectorAll('input');
        inputs.forEach(input => input.value = '');

        // 2. Tampilkan semua baris item
        let items = dataContainer.querySelectorAll('.filterable-item');
        items.forEach(item => item.classList.remove('d-none'));

        // 3. Sembunyikan pesan "Tidak ada data"
        let msgs = dataContainer.querySelectorAll('.no-data-message');
        msgs.forEach(msg => msg.classList.add('d-none'));
    }
</script>

@endsection