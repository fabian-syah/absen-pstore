@extends('layout.master')

@section('title', 'Edit Dzikir')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .select2-container ul, .select2-container li, .select2-selection__rendered, span.select2-selection__choice { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }
        .select2-container--bootstrap-5 .select2-selection--multiple { background-color: #fff !important; border: 1px solid #ced4da !important; padding: 4px !important; display: flex !important; flex-wrap: wrap !important; align-items: center !important; }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice { background-color: #e9ecef !important; border-radius: 20px !important; padding: 2px 10px !important; margin: 2px 4px !important; font-size: 0.85rem !important; color: #333 !important; }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove { border: none !important; background: transparent !important; margin-right: 5px !important; color: #999 !important; }
    </style>

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Edit Dzikir</h4>
                    <p class="card-description">Ubah detail dzikir</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" method="POST" action="{{ route('admin.dzikir.update', $dzikir->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="title">Judul Dzikir</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $dzikir->title) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori Dzikir</label>
                            <select class="form-select select2-multi" id="category" name="category[]" multiple="multiple" style="width: 100%" required>
                                @php
                                    $selectedCategories = old('category', is_array($dzikir->category) ? $dzikir->category : [$dzikir->category]);
                                @endphp
                                <option value="umum" {{ in_array('umum', $selectedCategories) ? 'selected' : '' }}>Dzikir Umum</option>
                                <option value="pagi" {{ in_array('pagi', $selectedCategories) ? 'selected' : '' }}>Dzikir Pagi</option>
                                <option value="petang" {{ in_array('petang', $selectedCategories) ? 'selected' : '' }}>Dzikir Petang</option>
                                <option value="sholat" {{ in_array('sholat', $selectedCategories) ? 'selected' : '' }}>Dzikir Sholat 5 Waktu</option>
                            </select>
                            <small class="form-text text-muted">Pilih maksimal 3 kategori. Tahan tombol Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu.</small>
                        </div>

                        <div class="form-group" id="prayer_time_div" style="{{ in_array('sholat', $selectedCategories) ? '' : 'display: none;' }}">
                            <label for="prayer_time">Waktu Sholat</label>
                            <select class="form-control" id="prayer_time" name="prayer_time">
                                <option value="semua" {{ old('prayer_time', $dzikir->prayer_time) == 'semua' ? 'selected' : '' }}>Semua Waktu Sholat</option>
                                <option value="subuh" {{ old('prayer_time', $dzikir->prayer_time) == 'subuh' ? 'selected' : '' }}>Setelah Subuh</option>
                                <option value="dzuhur" {{ old('prayer_time', $dzikir->prayer_time) == 'dzuhur' ? 'selected' : '' }}>Setelah Dzuhur</option>
                                <option value="ashar" {{ old('prayer_time', $dzikir->prayer_time) == 'ashar' ? 'selected' : '' }}>Setelah Ashar</option>
                                <option value="maghrib" {{ old('prayer_time', $dzikir->prayer_time) == 'maghrib' ? 'selected' : '' }}>Setelah Maghrib</option>
                                <option value="isya" {{ old('prayer_time', $dzikir->prayer_time) == 'isya' ? 'selected' : '' }}>Setelah Isya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="arabic_text">Teks Arab</label>
                            <textarea class="form-control" id="arabic_text" name="arabic_text" rows="5" dir="rtl" style="font-size: 1.2rem;">{{ old('arabic_text', $dzikir->arabic_text) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="latin_text">Cara Baca (Latin)</label>
                            <textarea class="form-control" id="latin_text" name="latin_text" rows="4">{{ old('latin_text', $dzikir->latin_text) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="translation">Terjemahan</label>
                            <textarea class="form-control" id="translation" name="translation" rows="4">{{ old('translation', $dzikir->translation) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="default_target">Target Default</label>
                            <input type="number" class="form-control" id="default_target" name="default_target" value="{{ old('default_target', $dzikir->default_target) }}" required min="1">
                        </div>

                        <div class="form-group">
                            <label for="information">Informasi / Fadhilah (Keutamaan)</label>
                            <textarea class="form-control" id="information" name="information" rows="3">{{ old('information', $dzikir->information) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">Update</button>
                        <a href="{{ route('admin.dzikir.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-multi').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Pilih kategori zikir...",
            closeOnSelect: false,
            allowClear: true,
            maximumSelectionLength: 3,
            language: {
                maximumSelected: function (e) {
                    return "Maksimal 3 kategori yang bisa dipilih";
                }
            }
        });

        // Toggle prayer time on change
        $('#category').on('change', function() {
            var selected = $(this).val() || [];
            var prayerTimeDiv = document.getElementById('prayer_time_div');
            if (selected.includes('sholat')) {
                prayerTimeDiv.style.display = 'block';
            } else {
                prayerTimeDiv.style.display = 'none';
            }
        });
        
        // Trigger on load
        $('#category').trigger('change');
    });
</script>
@endpush
