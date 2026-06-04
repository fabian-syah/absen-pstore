@extends('layout.master')

@section('title', 'Edit Dzikir')

@section('content')
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
                            <select class="form-control" id="category" name="category" required>
                                <option value="semua" {{ old('category', $dzikir->category) == 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                                <option value="pagi" {{ old('category', $dzikir->category) == 'pagi' ? 'selected' : '' }}>Pagi</option>
                                <option value="petang" {{ old('category', $dzikir->category) == 'petang' ? 'selected' : '' }}>Petang</option>
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
