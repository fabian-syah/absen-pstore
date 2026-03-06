@extends('layout.master')

@section('title', 'Edit Pelanggaran')

@section('content')
    <style>
        .violation-form-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .violation-form-header {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            padding: 28px 32px;
            color: #212529;
            position: relative;
            overflow: hidden;
        }

        .violation-form-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
        }

        .violation-form-header h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: -0.3px;
        }

        .violation-form-header p {
            margin: 6px 0 0;
            opacity: 0.75;
            font-size: 0.88rem;
        }

        .violation-form-body {
            padding: 32px;
        }

        .form-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6c757d;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-title i {
            font-size: 1rem;
            color: #ffc107;
        }

        .v-form-group {
            margin-bottom: 22px;
        }

        .v-form-group label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #343a40;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .v-form-group label .required-dot {
            width: 6px;
            height: 6px;
            background: #dc3545;
            border-radius: 50%;
            display: inline-block;
        }

        .v-form-group .form-control,
        .v-form-group .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #fdfdfd;
        }

        .v-form-group .form-control:focus,
        .v-form-group .form-select:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.15);
            background-color: #fff;
        }

        .v-form-group textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Category Selector Cards */
        .category-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        @media (max-width: 576px) {
            .category-selector {
                grid-template-columns: 1fr;
            }
        }

        .category-option {
            position: relative;
            cursor: pointer;
        }

        .category-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .category-option .category-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 16px 14px;
            text-align: center;
            transition: all 0.3s ease;
            background: #fdfdfd;
        }

        .category-option .category-card:hover {
            border-color: #adb5bd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .category-option input:checked+.category-card {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .category-option.cat-berat input:checked+.category-card {
            border-color: #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe3e3 100%);
        }

        .category-option.cat-sedang input:checked+.category-card {
            border-color: #ffc107;
            background: linear-gradient(135deg, #fffbeb 0%, #fff3cd 100%);
        }

        .category-option.cat-ringan input:checked+.category-card {
            border-color: #0dcaf0;
            background: linear-gradient(135deg, #f0fdff 0%, #d1ecf1 100%);
        }

        .category-card .cat-icon {
            font-size: 1.8rem;
            margin-bottom: 6px;
            display: block;
        }

        .category-card .cat-label {
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.5px;
        }

        .category-card .cat-desc {
            font-size: 0.72rem;
            color: #6c757d;
            margin-top: 4px;
        }

        .cat-berat .cat-icon {
            color: #dc3545;
        }

        .cat-sedang .cat-icon {
            color: #ffc107;
        }

        .cat-ringan .cat-icon {
            color: #0dcaf0;
        }

        /* Photo Upload */
        .photo-upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fdfdfd;
            position: relative;
        }

        .photo-upload-zone:hover {
            border-color: #ffc107;
            background: #fffbeb;
        }

        .photo-upload-zone.has-file {
            border-color: #198754;
            background: #f0fdf4;
        }

        .photo-upload-zone .upload-icon {
            font-size: 2.5rem;
            color: #dee2e6;
            margin-bottom: 8px;
            transition: color 0.3s;
        }

        .photo-upload-zone:hover .upload-icon {
            color: #ffc107;
        }

        .photo-upload-zone.has-file .upload-icon {
            color: #198754;
        }

        .photo-upload-zone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .photo-preview-container {
            display: none;
            margin-top: 14px;
        }

        .photo-preview-container.show {
            display: block;
        }

        .photo-preview-container img {
            max-height: 160px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* User Info Badge */
        .user-info-badge {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 14px 18px;
        }

        .user-info-badge .uib-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .user-info-badge .uib-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #212529;
        }

        .user-info-badge .uib-meta {
            font-size: 0.78rem;
            color: #6c757d;
        }

        /* Existing Photo */
        .existing-photo {
            display: inline-block;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 2px solid #e9ecef;
        }

        .existing-photo img {
            display: block;
        }

        /* Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            margin-top: 8px;
        }

        .btn-submit-edit {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
            border: none;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-submit-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(255, 193, 7, 0.4);
            color: #212529;
        }

        .btn-cancel-edit {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-cancel-edit:hover {
            background: #e9ecef;
            color: #212529;
        }

        .v-form-group .error-text {
            color: #dc3545;
            font-size: 0.78rem;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .violation-form-body {
                padding: 20px 16px;
            }

            .violation-form-header {
                padding: 22px 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="violation-form-card card">
                {{-- Header --}}
                <div class="violation-form-header">
                    <h4><i class="mdi mdi-pencil-box-outline me-2"></i>Edit Data Pelanggaran</h4>
                    <p>Perbarui informasi pelanggaran karyawan</p>
                </div>

                {{-- Body --}}
                <div class="violation-form-body">
                    @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4" style="font-size: 0.88rem;">
                            <i class="mdi mdi-alert-circle me-1"></i>
                            <strong>Oops!</strong> Mohon periksa kembali:
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('violations.update', $violation->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- SECTION: Data Karyawan --}}
                        <div class="form-section-title">
                            <i class="mdi mdi-account-alert"></i> Data Karyawan
                        </div>

                        <div class="v-form-group">
                            <label>Nama Karyawan</label>
                            <div class="user-info-badge">
                                <div class="uib-avatar">{{ strtoupper(substr($violation->user->name, 0, 2)) }}</div>
                                <div>
                                    <div class="uib-name">{{ $violation->user->name }}</div>
                                    <div class="uib-meta">{{ $violation->user->division->name ?? '-' }} ·
                                        {{ $violation->user->branch->name ?? '-' }}</div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                                <i class="mdi mdi-lock-outline"></i> Nama karyawan tidak dapat diubah saat edit.
                            </small>
                        </div>

                        {{-- SECTION: Detail --}}
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-file-document-edit-outline"></i> Detail Pelanggaran
                        </div>

                        {{-- Kategori --}}
                        <div class="v-form-group">
                            <label>Tingkat Pelanggaran <span class="required-dot"></span></label>
                            <div class="category-selector">
                                <label class="category-option cat-berat">
                                    <input type="radio" name="category" value="berat" {{ old('category', $violation->category) == 'berat' ? 'checked' : '' }} required>
                                    <div class="category-card">
                                        <span class="cat-icon mdi mdi-alert-octagon"></span>
                                        <div class="cat-label">BERAT</div>
                                        <div class="cat-desc">Berlaku 1 Tahun</div>
                                    </div>
                                </label>
                                <label class="category-option cat-sedang">
                                    <input type="radio" name="category" value="sedang" {{ old('category', $violation->category) == 'sedang' ? 'checked' : '' }}>
                                    <div class="category-card">
                                        <span class="cat-icon mdi mdi-alert"></span>
                                        <div class="cat-label">SEDANG</div>
                                        <div class="cat-desc">Berlaku 6 Bulan</div>
                                    </div>
                                </label>
                                <label class="category-option cat-ringan">
                                    <input type="radio" name="category" value="ringan" {{ old('category', $violation->category) == 'ringan' ? 'checked' : '' }}>
                                    <div class="category-card">
                                        <span class="cat-icon mdi mdi-alert-circle-outline"></span>
                                        <div class="cat-label">RINGAN</div>
                                        <div class="cat-desc">Berlaku 1 Bulan</div>
                                    </div>
                                </label>
                            </div>
                            @error('category')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Judul --}}
                        <div class="v-form-group">
                            <label>Judul Pelanggaran <span class="required-dot"></span></label>
                            <input type="text" class="form-control" name="title"
                                value="{{ old('title', $violation->title) }}" required>
                            @error('title')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="v-form-group">
                            <label>Kronologi / Deskripsi <span class="required-dot"></span></label>
                            <textarea class="form-control" name="description" rows="4"
                                required>{{ old('description', $violation->description) }}</textarea>
                            @error('description')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keterangan --}}
                        <div class="v-form-group">
                            <label>Keterangan / Sanksi <span class="text-muted fw-normal"
                                    style="font-size: 0.78rem;">(opsional)</span></label>
                            <input type="text" class="form-control" name="notes"
                                value="{{ old('notes', $violation->notes) }}">
                        </div>

                        {{-- SECTION: Bukti --}}
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-camera"></i> Bukti Foto
                        </div>

                        <div class="v-form-group">
                            @if($violation->photo_path)
                                <label class="mb-2">Foto Saat Ini</label>
                                <div class="existing-photo mb-3">
                                    <img src="{{ asset('storage/' . $violation->photo_path) }}" width="180" class="rounded">
                                </div>
                            @endif

                            <label>Ganti Bukti Foto <span class="text-muted fw-normal"
                                    style="font-size: 0.78rem;">(opsional)</span></label>
                            <div class="photo-upload-zone" id="uploadZone">
                                <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg">
                                <i class="mdi mdi-cloud-upload upload-icon d-block"></i>
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;" id="uploadLabel">Klik untuk pilih
                                    foto baru</div>
                                <div class="text-muted" style="font-size: 0.78rem;">Format: JPG, PNG · Maks 2MB</div>
                            </div>
                            <div class="photo-preview-container text-center mt-3" id="photoPreview">
                                <img id="previewImg" src="" class="img-fluid">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePhoto()">
                                        <i class="mdi mdi-close"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @error('photo')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="form-actions">
                            <button type="submit" class="btn btn-submit-edit">
                                <i class="mdi mdi-content-save-check"></i> Update Pelanggaran
                            </button>
                            <a href="{{ route('violations.index') }}" class="btn btn-cancel-edit">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('photoInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran foto melebihi 2MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (ev) {
                    document.getElementById('previewImg').src = ev.target.result;
                    document.getElementById('photoPreview').classList.add('show');
                    document.getElementById('uploadZone').classList.add('has-file');
                    document.getElementById('uploadLabel').textContent = file.name;
                };
                reader.readAsDataURL(file);
            }
        });

        function removePhoto() {
            document.getElementById('photoInput').value = '';
            document.getElementById('photoPreview').classList.remove('show');
            document.getElementById('uploadZone').classList.remove('has-file');
            document.getElementById('uploadLabel').textContent = 'Klik untuk pilih foto baru';
        }
    </script>
@endpush