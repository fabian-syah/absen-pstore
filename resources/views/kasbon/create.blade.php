@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Form Pengajuan Kasbon</h4>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('kasbon.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label>Nama Peminjam</label>
                        <select name="user_id" class="form-control select2">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (old('user_id') == $u->id || $u->id == auth()->id()) ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Judul Kasbon</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Total Uang (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="amount" id="rupiah" class="form-control" value="{{ old('amount') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jatuh Tempo</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Metode Penerimaan</label>
                        <div>
                            <input type="radio" name="payment_method" value="cash" onclick="toggleBank(false)" checked> Tunai
                            <input type="radio" name="payment_method" value="transfer" onclick="toggleBank(true)" class="ms-3"> Transfer
                        </div>
                    </div>

                    <div class="form-group" id="bankDetails" style="display:none">
                        <label>Info Rekening</label>
                        <input type="text" name="payment_details" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="description_1" class="form-control" required>{{ old('description_1') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Bukti Foto (Wajib)</label>
                        <input type="file" name="photo_1" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Ajukan</button>
                    <a href="{{ route('kasbon.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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

    function toggleBank(isTransfer) {
        document.getElementById('bankDetails').style.display = isTransfer ? 'block' : 'none';
        document.querySelector('input[name="payment_details"]').required = isTransfer;
    }
</script>
@endsection