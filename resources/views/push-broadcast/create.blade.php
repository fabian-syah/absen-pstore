@extends('layout.master')

@section('title', 'Push Notification Broadcast')
@section('heading', 'Kirim Push Notification')

@section('content')
<div class="row">
    <div class="col-lg-8 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-bell-ring me-2"></i>Kirim Push Notification ke Device
                </h5>
            </div>
            <div class="card-body">
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('push-broadcast.send') }}" method="POST" id="pushForm">
                    @csrf

                    {{-- Judul Notifikasi --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Notifikasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}"
                               placeholder="Contoh: Pengumuman Penting" required maxlength="255">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Maksimal 255 karakter</small>
                    </div>

                    {{-- Isi Pesan --}}
                    <div class="mb-3">
                        <label for="body" class="form-label fw-bold">Isi Pesan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  id="body" name="body" rows="4"
                                  placeholder="Tulis pesan yang ingin dikirim ke device user..." required maxlength="1000">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted"><span id="charCount">0</span>/1000 karakter</small>
                    </div>

                    {{-- Target Penerima --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Penerima <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target" id="targetAll"
                                   value="all" {{ old('target', 'all') == 'all' ? 'checked' : '' }}>
                            <label class="form-check-label" for="targetAll">
                                <i class="mdi mdi-account-group me-1"></i>Semua User (yang punya token)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target" id="targetBranch"
                                   value="branch" {{ old('target') == 'branch' ? 'checked' : '' }}>
                            <label class="form-check-label" for="targetBranch">
                                <i class="mdi mdi-store me-1"></i>Per Cabang
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target" id="targetRole"
                                   value="role" {{ old('target') == 'role' ? 'checked' : '' }}>
                            <label class="form-check-label" for="targetRole">
                                <i class="mdi mdi-shield-account me-1"></i>Per Role
                            </label>
                        </div>
                        @error('target')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Pilih Cabang (hidden by default) --}}
                    <div class="mb-3" id="branchSection" style="display: none;">
                        <label for="branch_id" class="form-label fw-bold">Pilih Cabang</label>
                        <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Pilih Role (hidden by default) --}}
                    <div class="mb-3" id="roleSection" style="display: none;">
                        <label class="form-label fw-bold">Pilih Role</label>
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]"
                                       value="{{ $role }}" id="role_{{ $role }}"
                                       {{ is_array(old('roles')) && in_array($role, old('roles')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_{{ $role }}">
                                    {{ ucfirst($role) }}
                                </label>
                            </div>
                        @endforeach
                        @error('roles')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnSend">
                            <i class="mdi mdi-send me-2"></i>Kirim Push Notification
                        </button>
                        <a href="{{ route('broadcast.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Panel --}}
    <div class="col-lg-4 col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0"><i class="mdi mdi-information me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="mdi mdi-check-circle text-success me-1"></i>
                        Push notification dikirim langsung ke device user
                    </li>
                    <li class="mb-2">
                        <i class="mdi mdi-check-circle text-success me-1"></i>
                        Hanya user yang sudah login & punya token yang menerima
                    </li>
                    <li class="mb-2">
                        <i class="mdi mdi-check-circle text-success me-1"></i>
                        Hasil pengiriman (berhasil/gagal) akan ditampilkan setelah kirim
                    </li>
                    <li class="mb-2">
                        <i class="mdi mdi-alert text-warning me-1"></i>
                        Token yang expired akan otomatis dihapus
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetRadios = document.querySelectorAll('input[name="target"]');
    const branchSection = document.getElementById('branchSection');
    const roleSection = document.getElementById('roleSection');
    const bodyTextarea = document.getElementById('body');
    const charCount = document.getElementById('charCount');
    const pushForm = document.getElementById('pushForm');
    const btnSend = document.getElementById('btnSend');

    // Toggle sections based on target
    function toggleSections() {
        const selected = document.querySelector('input[name="target"]:checked').value;
        branchSection.style.display = selected === 'branch' ? 'block' : 'none';
        roleSection.style.display = selected === 'role' ? 'block' : 'none';
    }

    targetRadios.forEach(radio => {
        radio.addEventListener('change', toggleSections);
    });

    // Initialize on page load
    toggleSections();

    // Character counter
    bodyTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
    charCount.textContent = bodyTextarea.value.length;

    // Confirm before send
    pushForm.addEventListener('submit', function(e) {
        if (!confirm('Yakin ingin mengirim push notification ini ke semua target?')) {
            e.preventDefault();
            return;
        }
        btnSend.disabled = true;
        btnSend.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i>Mengirim...';
    });
});
</script>
@endsection
