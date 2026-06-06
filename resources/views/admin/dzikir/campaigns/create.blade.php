@extends('layout.master')

@section('title', 'Tambah Campaign Zikir')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Tambah Campaign Baru</h4>
                    <p class="card-description">Buat campaign zikir baru untuk ditampilkan di carousel</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" method="POST" action="{{ route('admin.dzikir-campaign.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="zikir_id">Pilih Zikir (Opsional)</label>
                            <select class="form-control" id="zikir_id" name="zikir_id">
                                <option value="">-- Tanpa Zikir (Hanya Campaign Visual) --</option>
                                @foreach($zikirs as $z)
                                    <option value="{{ $z->id }}" {{ old('zikir_id') == $z->id ? 'selected' : '' }}>{{ $z->title }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Jika dipilih, zikir ini akan terhubung dengan progres campaign.</small>
                        </div>

                        <div class="form-group">
                            <label for="title">Judul Campaign</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Istighfar Bersama">
                        </div>

                        <div class="form-group">
                            <label for="emoji">Emoji (opsional)</label>
                            <input type="text" class="form-control" id="emoji" name="emoji" value="{{ old('emoji', '☝️') }}" placeholder="Contoh: ☝️ 🤲 📿">
                            <small class="form-text text-muted">Emoji yang muncul di samping judul campaign.</small>
                        </div>

                        <div class="form-group">
                            <label for="arabic_text">Teks Arab (opsional)</label>
                            <textarea class="form-control" id="arabic_text" name="arabic_text" rows="3" dir="rtl" style="font-size: 1.2rem;">{{ old('arabic_text') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="latin_text">Cara Baca / Latin (opsional)</label>
                            <textarea class="form-control" id="latin_text" name="latin_text" rows="3">{{ old('latin_text') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="target">Target Global</label>
                            <input type="number" class="form-control" id="target" name="target" value="{{ old('target', 1000000) }}" required min="1">
                            <small class="form-text text-muted">Target total bacaan. Contoh: 1000000 untuk 1 juta.</small>
                        </div>

                        <div class="form-group">
                            <label for="current_count">Progres Awal (opsional)</label>
                            <input type="number" class="form-control" id="current_count" name="current_count" value="{{ old('current_count', 0) }}" min="0">
                            <small class="form-text text-muted">Jumlah bacaan yang sudah tercapai (default 0).</small>
                        </div>



                        <div class="form-group">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    Campaign Aktif
                                </label>
                            </div>
                            <small class="form-text text-muted">Jika diaktifkan, campaign akan muncul di carousel pengguna.</small>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                        <a href="{{ route('admin.dzikir-campaign.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
