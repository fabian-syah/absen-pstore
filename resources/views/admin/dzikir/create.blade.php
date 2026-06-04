@extends('layout.master')

@section('title', 'Tambah Dzikir')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Tambah Dzikir Baru</h4>
                    <p class="card-description">Masukkan detail dzikir</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" method="POST" action="{{ route('admin.dzikir.store') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="title">Judul Dzikir</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Dzikir Petang 1">
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori Dzikir</label>
                            <select class="form-control" id="category" name="category" required onchange="togglePrayerTime()">
                                <option value="umum" {{ old('category') == 'umum' ? 'selected' : '' }}>Dzikir Umum</option>
                                <option value="pagi" {{ old('category') == 'pagi' ? 'selected' : '' }}>Dzikir Pagi</option>
                                <option value="petang" {{ old('category') == 'petang' ? 'selected' : '' }}>Dzikir Petang</option>
                                <option value="sholat" {{ old('category') == 'sholat' ? 'selected' : '' }}>Dzikir Sholat 5 Waktu</option>
                            </select>
                        </div>

                        <div class="form-group" id="prayer_time_div" style="{{ old('category') == 'sholat' ? '' : 'display: none;' }}">
                            <label for="prayer_time">Waktu Sholat</label>
                            <select class="form-control" id="prayer_time" name="prayer_time">
                                <option value="semua" {{ old('prayer_time') == 'semua' ? 'selected' : '' }}>Semua Waktu Sholat</option>
                                <option value="subuh" {{ old('prayer_time') == 'subuh' ? 'selected' : '' }}>Setelah Subuh</option>
                                <option value="dzuhur" {{ old('prayer_time') == 'dzuhur' ? 'selected' : '' }}>Setelah Dzuhur</option>
                                <option value="ashar" {{ old('prayer_time') == 'ashar' ? 'selected' : '' }}>Setelah Ashar</option>
                                <option value="maghrib" {{ old('prayer_time') == 'maghrib' ? 'selected' : '' }}>Setelah Maghrib</option>
                                <option value="isya" {{ old('prayer_time') == 'isya' ? 'selected' : '' }}>Setelah Isya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="arabic_text">Teks Arab</label>
                            <textarea class="form-control" id="arabic_text" name="arabic_text" rows="5" dir="rtl" style="font-size: 1.2rem;">{{ old('arabic_text') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="latin_text">Cara Baca (Latin)</label>
                            <textarea class="form-control" id="latin_text" name="latin_text" rows="4">{{ old('latin_text') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="translation">Terjemahan</label>
                            <textarea class="form-control" id="translation" name="translation" rows="4">{{ old('translation') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="default_target">Target Default</label>
                            <input type="number" class="form-control" id="default_target" name="default_target" value="{{ old('default_target', 1) }}" required min="1">
                            <small class="form-text text-muted">Berapa kali dzikir ini dianjurkan untuk dibaca.</small>
                        </div>

                        <div class="form-group">
                            <label for="information">Informasi / Fadhilah (Keutamaan)</label>
                            <textarea class="form-control" id="information" name="information" rows="3">{{ old('information') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                        <a href="{{ route('admin.dzikir.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePrayerTime() {
        var category = document.getElementById('category').value;
        var prayerTimeDiv = document.getElementById('prayer_time_div');
        if (category === 'sholat') {
            prayerTimeDiv.style.display = 'block';
        } else {
            prayerTimeDiv.style.display = 'none';
        }
    }
</script>
@endpush
