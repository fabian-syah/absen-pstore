@extends('layout.master')

@section('content')
<style>
    /* Custom Style untuk Radio Button Modern */
    .payment-option-input {
        display: none; /* Sembunyikan radio asli */
    }

    .payment-option-card {
        cursor: pointer;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        text-align: center;
        height: 100%;
        background-color: #fff;
    }

    .payment-option-card:hover {
        border-color: #b1b7c1;
        background-color: #f8f9fa;
    }

    /* State ketika dipilih */
    .payment-option-input:checked + .payment-option-card {
        border-color: #4b49ac; /* Warna Primary Theme */
        background-color: #f0f0ff;
        color: #4b49ac;
    }

    .payment-option-input:checked + .payment-option-card i {
        color: #4b49ac;
    }

    .icon-lg {
        font-size: 2.5rem;
        margin-bottom: 10px;
        color: #6c757d;
        transition: color 0.3s;
    }
    
    /* Animasi Form Bank */
    #bankDetails {
        transition: all 0.4s ease-in-out;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-4 border-bottom">
                    <h4 class="mb-0 fw-bold text-dark"><i class="mdi mdi-cash-plus me-2 text-primary"></i>Form Pengajuan Kasbon</h4>
                    <p class="text-muted mb-0 small mt-1">Isi formulir di bawah untuk mengajukan pinjaman karyawan.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kasbon.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- 1. IDENTITAS PEMINJAM --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small text-muted letter-spacing-1">Informasi Peminjam</label>
                            @if (auth()->user()->role == 'admin')
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-account-search"></i></span>
                                    <select name="user_id" class="form-select form-select-lg">
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">
                                                {{ $u->name }} - {{ $u->division->name ?? ($u->division ?? '-') }} ({{ $u->branch->name ?? ($u->branch ?? '-') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-info mt-1 d-block"><i class="mdi mdi-information-outline"></i> Admin Mode: Anda sedang mengajukan untuk karyawan lain.</small>
                            @else
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-account"></i></span>
                                    <input type="text" class="form-control form-control-lg bg-light text-dark fw-bold"
                                        value="{{ auth()->user()->name }} ({{ auth()->user()->division->name ?? (auth()->user()->division ?? 'Umum') }})"
                                        readonly>
                                </div>
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            @endif
                        </div>

                        {{-- 2. NOMINAL --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nominal Pengajuan</label>
                            <div class="input-group input-group-lg shadow-sm rounded overflow-hidden">
                                <span class="input-group-text bg-primary text-white fw-bold px-4">Rp</span>
                                <input type="text" name="amount" id="rupiah"
                                    class="form-control fw-bold text-dark" 
                                    style="font-size: 1.5rem; letter-spacing: 1px;"
                                    placeholder="0" required autocomplete="off">
                            </div>
                            <small class="text-muted ms-1">Masukkan angka tanpa titik (otomatis diformat).</small>
                        </div>

                        {{-- 3. METODE PENCAIRAN (UI MODERN) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">Metode Pencairan Dana</label>
                            <div class="row g-3">
                                {{-- Opsi Tunai --}}
                                <div class="col-md-6">
                                    <input type="radio" name="payment_method" id="methodCash" value="cash" class="payment-option-input" checked onchange="toggleBank(false)">
                                    <label for="methodCash" class="payment-option-card d-block w-100">
                                        <i class="mdi mdi-wallet icon-lg d-block"></i>
                                        <span class="fw-bold fs-5">Tunai (Cash)</span>
                                        <small class="d-block text-muted mt-1">Terima uang langsung</small>
                                    </label>
                                </div>

                                {{-- Opsi Transfer --}}
                                <div class="col-md-6">
                                    <input type="radio" name="payment_method" id="methodTransfer" value="transfer" class="payment-option-input" onchange="toggleBank(true)">
                                    <label for="methodTransfer" class="payment-option-card d-block w-100">
                                        <i class="mdi mdi-bank icon-lg d-block"></i>
                                        <span class="fw-bold fs-5">Transfer Bank</span>
                                        <small class="d-block text-muted mt-1">Kirim ke rekening</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- 4. DETAIL REKENING (Hidden by default with Animation) --}}
                        <div id="bankDetails" class="mb-4 p-4 bg-light rounded-3 border border-dashed" style="display: none;">
                            <h6 class="fw-bold mb-3 text-primary"><i class="mdi mdi-card-account-details me-2"></i>Informasi Rekening Tujuan</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Nama Bank</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="Cth: BCA, Mandiri">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small text-muted">Nomor Rekening</label>
                                    <input type="number" name="account_number" class="form-control fw-bold" placeholder="Cth: 1234567890">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Atas Nama</label>
                                    <input type="text" name="account_name" class="form-control" placeholder="Nama Pemilik">
                                </div>
                            </div>
                        </div>

                        {{-- 5. KETERANGAN --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Keperluan / Keterangan</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan alasan pengajuan kasbon secara singkat..." required></textarea>
                        </div>

                        {{-- 6. UPLOAD BUKTI --}}
                        <div class="mb-5">
                            <label class="form-label fw-bold mb-2">Lampiran Dokumen (Foto/Struk)</label>
                            <div class="p-3 border rounded bg-white">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-muted mb-1">Bukti 1 (Wajib)</label>
                                        <input type="file" name="photo_1" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted mb-1">Bukti 2 (Wajib)</label>
                                        <input type="file" name="photo_2" class="form-control form-control-sm" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('kasbon.index') }}" class="btn btn-light btn-lg px-4 me-md-2 fw-bold text-muted">Batal</a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                                <i class="mdi mdi-send me-2"></i>Kirim Pengajuan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Format Rupiah
    const rupiah = document.getElementById('rupiah');
    rupiah.addEventListener('keyup', function(e) {
        rupiah.value = formatRupiah(this.value);
    });

    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Toggle Bank dengan Animasi Fade
    function toggleBank(show) {
        const el = document.getElementById('bankDetails');
        const inputs = el.querySelectorAll('input');
        
        if (show) {
            // Tampilkan dengan efek fadeIn
            el.style.display = 'block';
            el.style.opacity = 0;
            setTimeout(() => {
                el.style.opacity = 1;
            }, 50);
            
            // Set required
            inputs.forEach(i => i.required = true);
        } else {
            // Sembunyikan
            el.style.display = 'none';
            el.style.opacity = 0;
            
            // Hapus required
            inputs.forEach(i => i.required = false);
        }
    }
</script>
@endsection