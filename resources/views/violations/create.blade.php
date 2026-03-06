@extends('layout.master')

@section('title', 'Input Pelanggaran Baru')

@section('content')
    <style>
        /* ============================================
           VIOLATION FORM - PREMIUM UI
           ============================================ */
        .violation-form-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .violation-form-header {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            padding: 28px 32px;
            color: white;
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
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .violation-form-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: 10%;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.05);
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
            opacity: 0.85;
            font-size: 0.88rem;
        }

        .violation-form-body {
            padding: 32px;
        }

        /* Section Titles */
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
            color: #dc3545;
        }

        /* Custom Input Group */
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
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
            background-color: #fff;
        }

        .v-form-group textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .v-form-group .form-hint {
            font-size: 0.78rem;
            color: #9ca3af;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
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

        /* Searchable Dropdown */
        .search-select-wrapper {
            position: relative;
        }

        .search-select-trigger {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fdfdfd;
            transition: all 0.3s ease;
            min-height: 48px;
        }

        .search-select-trigger:hover {
            border-color: #adb5bd;
        }

        .search-select-trigger.active {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .search-select-trigger .placeholder-text {
            color: #9ca3af;
        }

        .search-select-trigger .selected-text {
            color: #212529;
            font-weight: 500;
        }

        .search-select-trigger .trigger-icon {
            color: #6c757d;
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }

        .search-select-trigger.active .trigger-icon {
            transform: rotate(180deg);
        }

        .search-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 2px solid #dc3545;
            border-top: none;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            z-index: 1050;
            display: none;
            max-height: 320px;
            overflow: hidden;
        }

        .search-select-dropdown.show {
            display: block;
            animation: dropdownSlide 0.2s ease;
        }

        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-select-search {
            padding: 12px 14px;
            border-bottom: 1px solid #f0f0f0;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
        }

        .search-select-search input {
            width: 100%;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 14px 10px 36px;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-select-search input:focus {
            border-color: #dc3545;
        }

        .search-select-search .search-icon {
            position: absolute;
            left: 26px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
        }

        .search-select-options {
            max-height: 240px;
            overflow-y: auto;
            padding: 6px;
        }

        .search-select-options::-webkit-scrollbar {
            width: 6px;
        }

        .search-select-options::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 10px;
        }

        .search-option {
            padding: 10px 14px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-option:hover {
            background: #f8f9fa;
        }

        .search-option.selected {
            background: #fff5f5;
            border: 1px solid #f8d7da;
        }

        .search-option .opt-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc3545, #a71d2a);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .search-option .opt-info {
            flex: 1;
            min-width: 0;
        }

        .search-option .opt-name {
            font-weight: 600;
            font-size: 0.88rem;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-option .opt-meta {
            font-size: 0.75rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-no-results {
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 0.88rem;
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
            border-color: #dc3545;
            background: #fff5f5;
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
            color: #dc3545;
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

        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 12px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            margin-top: 8px;
        }

        .btn-submit-violation {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            color: white;
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

        .btn-submit-violation:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(220, 53, 69, 0.35);
            color: white;
        }

        .btn-cancel-violation {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-cancel-violation:hover {
            background: #e9ecef;
            color: #212529;
        }

        /* Info Banner */
        .info-banner {
            background: linear-gradient(135deg, #e8f4fd 0%, #d1ecf1 100%);
            border: 1px solid #bee5eb;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.84rem;
            color: #0c5460;
        }

        .info-banner i {
            font-size: 1.2rem;
            margin-top: 1px;
            flex-shrink: 0;
        }

        /* Error state */
        .v-form-group.has-error .form-control,
        .v-form-group.has-error .search-select-trigger {
            border-color: #dc3545;
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
                    <h4><i class="mdi mdi-alert-decagram me-2"></i>Catat Pelanggaran Baru</h4>
                    <p>Isi form berikut untuk mencatat pelanggaran karyawan</p>
                </div>

                {{-- Body --}}
                <div class="violation-form-body">
                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4" style="font-size: 0.88rem;">
                            <i class="mdi mdi-alert-circle me-1"></i>
                            <strong>Oops!</strong> Mohon periksa kembali data berikut:
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Info Banner --}}
                    <div class="info-banner">
                        <i class="mdi mdi-information-outline"></i>
                        <div>
                            Masa berlaku pelanggaran otomatis:
                            <strong>Berat</strong> = 1 Tahun,
                            <strong>Sedang</strong> = 6 Bulan,
                            <strong>Ringan</strong> = 1 Bulan.
                        </div>
                    </div>

                    <form action="{{ route('violations.store') }}" method="POST" enctype="multipart/form-data"
                        id="violationForm">
                        @csrf

                        {{-- SECTION 1: Data Karyawan --}}
                        <div class="form-section-title">
                            <i class="mdi mdi-account-alert"></i> Data Karyawan
                        </div>

                        <div class="v-form-group {{ $errors->has('user_id') ? 'has-error' : '' }}">
                            <label>Nama Karyawan (Pelanggar) <span class="required-dot"></span></label>
                            <input type="hidden" name="user_id" id="selectedUserId" value="{{ old('user_id') }}" required>

                            <div class="search-select-wrapper" id="userSearchSelect">
                                <div class="search-select-trigger" id="searchTrigger" onclick="toggleDropdown()">
                                    <span class="placeholder-text" id="triggerText">🔍 Ketik nama atau klik untuk mencari
                                        karyawan...</span>
                                    <i class="mdi mdi-chevron-down trigger-icon"></i>
                                </div>
                                <div class="search-select-dropdown" id="searchDropdown">
                                    <div class="search-select-search">
                                        <i class="mdi mdi-magnify search-icon"></i>
                                        <input type="text" id="searchInput" placeholder="Cari nama, divisi, atau cabang..."
                                            autocomplete="off">
                                    </div>
                                    <div class="search-select-options" id="optionsList">
                                        @foreach($users as $user)
                                            <div class="search-option" data-value="{{ $user->id }}"
                                                data-name="{{ $user->name }}" data-division="{{ $user->division->name ?? '-' }}"
                                                data-branch="{{ $user->branch->name ?? '-' }}" onclick="selectOption(this)">
                                                <div class="opt-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                                <div class="opt-info">
                                                    <div class="opt-name">{{ $user->name }}</div>
                                                    <div class="opt-meta">{{ $user->division->name ?? '-' }} ·
                                                        {{ $user->branch->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('user_id')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- SECTION 2: Detail Pelanggaran --}}
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-file-document-edit-outline"></i> Detail Pelanggaran
                        </div>

                        {{-- Kategori --}}
                        <div class="v-form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                            <label>Tingkat Pelanggaran <span class="required-dot"></span></label>
                            <div class="category-selector">
                                <label class="category-option cat-berat">
                                    <input type="radio" name="category" value="berat" {{ old('category', 'berat') == 'berat' ? 'checked' : '' }} required>
                                    <div class="category-card">
                                        <span class="cat-icon mdi mdi-alert-octagon"></span>
                                        <div class="cat-label">BERAT</div>
                                        <div class="cat-desc">Berlaku 1 Tahun</div>
                                    </div>
                                </label>
                                <label class="category-option cat-sedang">
                                    <input type="radio" name="category" value="sedang" {{ old('category') == 'sedang' ? 'checked' : '' }}>
                                    <div class="category-card">
                                        <span class="cat-icon mdi mdi-alert"></span>
                                        <div class="cat-label">SEDANG</div>
                                        <div class="cat-desc">Berlaku 6 Bulan</div>
                                    </div>
                                </label>
                                <label class="category-option cat-ringan">
                                    <input type="radio" name="category" value="ringan" {{ old('category') == 'ringan' ? 'checked' : '' }}>
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
                        <div class="v-form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                            <label>Judul Pelanggaran <span class="required-dot"></span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}"
                                placeholder="Contoh: Datang Terlambat > 1 Jam" required>
                            @error('title')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="v-form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                            <label>Kronologi / Deskripsi <span class="required-dot"></span></label>
                            <textarea class="form-control" name="description" rows="4"
                                placeholder="Jelaskan kronologi kejadian secara detail..."
                                required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keterangan --}}
                        <div class="v-form-group">
                            <label>Keterangan / Sanksi <span class="text-muted fw-normal"
                                    style="font-size: 0.78rem;">(opsional)</span></label>
                            <input type="text" class="form-control" name="notes" value="{{ old('notes') }}"
                                placeholder="Contoh: Diberikan SP1, Potong Gaji 10%, dll">
                        </div>

                        {{-- SECTION 3: Bukti --}}
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-camera"></i> Bukti Foto
                        </div>

                        <div class="v-form-group {{ $errors->has('photo') ? 'has-error' : '' }}">
                            <div class="photo-upload-zone" id="uploadZone">
                                <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg"
                                    required>
                                <i class="mdi mdi-cloud-upload upload-icon d-block"></i>
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;" id="uploadLabel">Klik atau Drag
                                    foto bukti ke sini</div>
                                <div class="text-muted" style="font-size: 0.78rem;">Format: JPG, PNG · Maks 2MB</div>
                            </div>
                            <div class="photo-preview-container text-center mt-3" id="photoPreview">
                                <img id="previewImg" src="" class="img-fluid">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePhoto()">
                                        <i class="mdi mdi-close"></i> Hapus Foto
                                    </button>
                                </div>
                            </div>
                            @error('photo')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="form-actions">
                            <button type="submit" class="btn btn-submit-violation">
                                <i class="mdi mdi-content-save-check"></i> Simpan Pelanggaran
                            </button>
                            <a href="{{ route('violations.index') }}" class="btn btn-cancel-violation">
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
        // ============================================
        // SEARCHABLE DROPDOWN
        // ============================================
        let isDropdownOpen = false;

        function toggleDropdown() {
            if (isDropdownOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        }

        function openDropdown() {
            const dropdown = document.getElementById('searchDropdown');
            const trigger = document.getElementById('searchTrigger');
            dropdown.classList.add('show');
            trigger.classList.add('active');
            isDropdownOpen = true;

            // Focus search input
            setTimeout(() => {
                document.getElementById('searchInput').focus();
            }, 100);
        }

        function closeDropdown() {
            const dropdown = document.getElementById('searchDropdown');
            const trigger = document.getElementById('searchTrigger');
            dropdown.classList.remove('show');
            trigger.classList.remove('active');
            isDropdownOpen = false;
        }

        function selectOption(el) {
            const userId = el.dataset.value;
            const userName = el.dataset.name;
            const userDiv = el.dataset.division;
            const userBranch = el.dataset.branch;

            // Set hidden input
            document.getElementById('selectedUserId').value = userId;

            // Update trigger text
            const triggerText = document.getElementById('triggerText');
            triggerText.className = 'selected-text';
            triggerText.innerHTML = `<strong>${userName}</strong> <span style="color:#6c757d;font-size:0.82rem;">· ${userDiv} · ${userBranch}</span>`;

            // Mark selected
            document.querySelectorAll('.search-option').forEach(opt => opt.classList.remove('selected'));
            el.classList.add('selected');

            closeDropdown();
        }

        // Search filter
        document.getElementById('searchInput').addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const options = document.querySelectorAll('.search-option');
            let hasResults = false;

            options.forEach(opt => {
                const name = opt.dataset.name.toLowerCase();
                const division = opt.dataset.division.toLowerCase();
                const branch = opt.dataset.branch.toLowerCase();
                const match = name.includes(query) || division.includes(query) || branch.includes(query);

                opt.style.display = match ? 'flex' : 'none';
                if (match) hasResults = true;
            });

            // Show/hide no results message
            let noResEl = document.getElementById('noResults');
            if (!hasResults) {
                if (!noResEl) {
                    noResEl = document.createElement('div');
                    noResEl.id = 'noResults';
                    noResEl.className = 'search-no-results';
                    noResEl.innerHTML = '<i class="mdi mdi-account-search-outline d-block" style="font-size:2rem;color:#dee2e6;"></i>Karyawan tidak ditemukan';
                    document.getElementById('optionsList').appendChild(noResEl);
                }
                noResEl.style.display = 'block';
            } else if (noResEl) {
                noResEl.style.display = 'none';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('userSearchSelect');
            if (!wrapper.contains(e.target) && isDropdownOpen) {
                closeDropdown();
            }
        });

        // Prevent form submission closing
        document.getElementById('searchInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // ============================================
        // PHOTO UPLOAD PREVIEW
        // ============================================
        document.getElementById('photoInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate size
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran foto melebihi 2MB. Silakan pilih foto yang lebih kecil.');
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
            document.getElementById('uploadLabel').textContent = 'Klik atau Drag foto bukti ke sini';
        }

        // ============================================
        // RESTORE OLD VALUE (if validation fails)
        // ============================================
        document.addEventListener('DOMContentLoaded', function () {
            const oldUserId = document.getElementById('selectedUserId').value;
            if (oldUserId) {
                const option = document.querySelector(`.search-option[data-value="${oldUserId}"]`);
                if (option) {
                    selectOption(option);
                }
            }
        });
    </script>
@endpush