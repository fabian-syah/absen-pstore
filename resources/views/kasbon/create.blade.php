@extends('layout.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow rounded-lg">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold">Form Pengajuan Kasbon</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kasbon.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Identitas --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted text-uppercase small">Informasi Peminjam</label>
                            @if(auth()->user()->role == 'admin')
                                <select name="user_id" class="form-select form-select-lg mb-2">
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} - {{ $u->division ?? 'Divisi Umum' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-info">*Admin Mode: Pilih karyawan yang mengajukan.</small>
                            @else
                                <input type="text" class="form-control form-control-lg bg-light" value="{{ auth()->user()->name }} ({{ auth()->user()->division ?? 'Umum' }} - {{ auth()->user()->branch ?? 'Pusat' }})" readonly>
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            @endif
                        </div>

                        {{-- Nominal --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Total Uang Yang Diajukan</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white fw-bold">Rp</span>
                                <input type="text" name="amount" id="rupiah" class="form-control fw-bold text-primary" placeholder="0" required autocomplete="off">
                            </div>
                            <small class="text-muted">Masukkan nominal tanpa titik.</small>
                        </div>

                        {{-- Keterangan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Keterangan / Keperluan</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Biaya berobat sakit gigi, service motor dinas, dll..." required></textarea>
                        </div>

                        {{-- Metode Terima Uang --}}
                        <div class="mb-4 bg-light p-3 rounded border">
                            <label class="form-label fw-bold d-block mb-2">Metode Pencairan Dana</label>
                            
                            <div class="d-flex gap-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="methodCash" value="cash" checked onclick="toggleBank(false)">
                                    <label class="form-check-label" for="methodCash">Tunai (Cash)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="methodTransfer" value="transfer" onclick="toggleBank(true)">
                                    <label class="form-check-label" for="methodTransfer">Transfer Bank</label>
                                </div>
                            </div>

                            {{-- Form Bank (Hidden by default) --}}
                            <div id="bankDetails" style="display: none;">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" name="bank_name" class="form-control" placeholder="Nama Bank (BCA/Mandiri)">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="account_number" class="form-control" placeholder="Nomor Rekening">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="account_name" class="form-control" placeholder="Atas Nama">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bukti Foto --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Foto Bukti 1 (Wajib)</label>
                                <input type="file" name="photo_1" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Foto Bukti 2 (Wajib)</label>
                                <input type="file" name="photo_2" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">AJUKAN KASBON</button>
                            <a href="{{ route('kasbon.index') }}" class="btn btn-light text-muted">Batal</a>
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
    rupiah.addEventListener('keyup', function(e){
        rupiah.value = formatRupiah(this.value);
    });

    function formatRupiah(angka, prefix){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split   = number_string.split(','),
        sisa    = split[0].length % 3,
        rupiah  = split[0].substr(0, sisa),
        ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Toggle Bank
    function toggleBank(show) {
        const el = document.getElementById('bankDetails');
        const inputs = el.querySelectorAll('input');
        if(show) {
            el.style.display = 'block';
            inputs.forEach(i => i.required = true);
        } else {
            el.style.display = 'none';
            inputs.forEach(i => i.required = false);
        }
    }
</script>
@endsection