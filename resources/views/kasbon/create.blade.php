@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Form Pengajuan Kasbon</h4>

                {{-- ==================================================== --}}
                {{-- [FIX] MENAMPILKAN ERROR JIKA VALIDASI GAGAL --}}
                {{-- ==================================================== --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {{-- ==================================================== --}}
                
                <form action="{{ route('kasbon.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label>Nama Peminjam</label>
                        <select name="user_id" class="form-control select2" required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (old('user_id') == $u->id || $u->id == auth()->id()) ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- JUDUL KASBON --}}
                    <div class="form-group">
                        <label>Judul Kasbon (Singkat)</label>
                        {{-- Tambahkan value="{{ old('title') }}" agar teks tidak hilang saat error --}}
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Biaya Rumah Sakit / Service Motor" required maxlength="100" value="{{ old('title') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            {{-- NOMINAL AUTO FORMAT RP --}}
                            <div class="form-group">
                                <label>Total Uang (Nominal)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="amount" id="rupiah" class="form-control" placeholder="0" required value="{{ old('amount') }}">
                                </div>
                                <small class="text-muted">Ketik angka saja, otomatis diformat.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jatuh Tempo (Batas Akhir)</label>
                                {{-- Pastikan memilih tanggal SETELAH hari ini (Besok/Lusa) --}}
                                <input type="date" name="due_date" class="form-control" required value="{{ old('due_date') }}">
                                <small class="text-info">Minimal H+1 dari hari ini.</small>
                            </div>
                        </div>
                    </div>

                    {{-- METODE PENERIMAAN --}}
                    <div class="form-group">
                        <label>Metode Penerimaan Uang</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="payment_method" value="cash" onclick="toggleBank(false)" {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}>
                                    Tunai (Cash)
                                </label>
                            </div>
                            <div class="form-check ms-3">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="payment_method" value="transfer" onclick="toggleBank(true)" {{ old('payment_method') == 'transfer' ? 'checked' : '' }}>
                                    Transfer Bank
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- INPUT REKENING (Hidden by default, unless old input was transfer) --}}
                    <div class="form-group" id="bankDetails" style="display: {{ old('payment_method') == 'transfer' ? 'block' : 'none' }};">
                        <label>Nama Bank & Nomor Rekening</label>
                        <input type="text" name="payment_details" class="form-control" placeholder="Contoh: BCA - 1234567890 a.n Fabian" value="{{ old('payment_details') }}">
                        <small class="text-info">Pastikan nama pemilik rekening sesuai.</small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan Lengkap (Alasan)</label>
                        <textarea name="description_1" class="form-control" rows="3" placeholder="Jelaskan secara rinci kenapa butuh kasbon ini..." required>{{ old('description_1') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Dokumen 1 (Wajib)</label>
                                <input type="file" name="photo_1" class="form-control" required>
                                <small class="text-muted">Max: 2MB (JPG/PNG)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Dokumen 2 (Opsional)</label>
                                <input type="file" name="photo_2" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Ajukan Kasbon</button>
                    <a href="{{ route('kasbon.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // SCRIPT AUTO FORMAT RUPIAH
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
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    // SCRIPT TOGGLE BANK
    function toggleBank(isTransfer) {
        const bankInput = document.getElementById('bankDetails');
        const detailInput = document.querySelector('input[name="payment_details"]');
        
        if (isTransfer) {
            bankInput.style.display = 'block';
            detailInput.required = true;
        } else {
            bankInput.style.display = 'none';
            detailInput.required = false;
            detailInput.value = '';
        }
    }
</script>
@endsection