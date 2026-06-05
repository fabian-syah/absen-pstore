@extends('layout.master')

@section('title', 'Edit Campaign Zikir')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Edit Campaign</h4>
                    <p class="card-description">Perbarui detail campaign zikir</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" method="POST" action="{{ route('admin.dzikir-campaign.update', $campaign->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="title">Judul Campaign</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $campaign->title) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="emoji">Emoji (opsional)</label>
                            <input type="text" class="form-control" id="emoji" name="emoji" value="{{ old('emoji', $campaign->emoji) }}">
                        </div>

                        <div class="form-group">
                            <label for="arabic_text">Teks Arab (opsional)</label>
                            <textarea class="form-control" id="arabic_text" name="arabic_text" rows="3" dir="rtl" style="font-size: 1.2rem;">{{ old('arabic_text', $campaign->arabic_text) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="latin_text">Cara Baca / Latin (opsional)</label>
                            <textarea class="form-control" id="latin_text" name="latin_text" rows="3">{{ old('latin_text', $campaign->latin_text) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="target">Target Global</label>
                            <input type="number" class="form-control" id="target" name="target" value="{{ old('target', $campaign->target) }}" required min="1">
                        </div>

                        <div class="form-group">
                            <label for="current_count">Progres Saat Ini</label>
                            <input type="number" class="form-control" id="current_count" name="current_count" value="{{ old('current_count', $campaign->current_count) }}" min="0">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai (opsional)</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $campaign->start_date ? $campaign->start_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai (opsional)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $campaign->end_date ? $campaign->end_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', $campaign->is_active) ? 'checked' : '' }}>
                                    Campaign Aktif
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">Simpan Perubahan</button>
                        <a href="{{ route('admin.dzikir-campaign.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
