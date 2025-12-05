@extends('layout.master')

@section('title', 'Permintaan Ganti KTP')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Permintaan Ganti KTP</h4>
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Divisi</th>
                                <th class="text-center">KTP Lama</th>
                                <th class="text-center">KTP Baru (Draft)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>{{ $user->division->name ?? '-' }}</td>
                                
                                {{-- ======================= --}}
                                {{-- KOLOM KTP LAMA --}}
                                {{-- ======================= --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_path)
                                        {{-- Thumbnail --}}
                                        <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                             alt="Old KTP" 
                                             class="img-thumbnail modal-thumbnail"
                                             style="width: 80px; height: 50px; object-fit: cover; cursor: pointer;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#modalOldKtp{{ $user->id }}">
                                        
                                        {{-- MODAL POPUP FIX IOS & ANDROID --}}
                                        <div class="modal fade modal-ios-fix" id="modalOldKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 999999;">
                                                {{-- Hapus border dan background --}}
                                                <div class="modal-content bg-transparent border-0" 
                                                     style="background-color: transparent !important; box-shadow: none !important;">
                                                    
                                                    {{-- Gunakan Flexbox untuk memusatkan gambar --}}
                                                    <div class="modal-body p-0 d-flex justify-content-center align-items-center position-relative" 
                                                         style="min-height: 100vh;">
                                                        
                                                        {{-- Background Overlay untuk iOS --}}
                                                        <div class="modal-overlay-ios" 
                                                             style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 999998;">
                                                        </div>
                                                        
                                                        {{-- Tombol Close Floating --}}
                                                        <button type="button" class="btn-close btn-close-white position-absolute" 
                                                                data-bs-dismiss="modal" 
                                                                aria-label="Close"
                                                                style="z-index: 999999; top: 20px; right: 20px; background-color: rgba(0,0,0,0.7); padding: 12px; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                        </button>

                                                        {{-- Container gambar dengan overflow handling --}}
                                                        <div class="image-container-ios" 
                                                             style="position: relative; z-index: 999999; max-width: 90vw; max-height: 80vh;">
                                                            {{-- Gambar: width: auto (PENTING UNTUK IOS), max-width: 100% --}}
                                                            <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" 
                                                                 class="img-fluid ios-modal-image"
                                                                 style="width: auto; max-width: 100%; height: auto; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: block;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-secondary">Kosong</span>
                                    @endif
                                </td>

                                {{-- ======================= --}}
                                {{-- KOLOM KTP BARU --}}
                                {{-- ======================= --}}
                                <td class="text-center">
                                    @if($user->ktp_photo_temp_path)
                                        {{-- Thumbnail --}}
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                                 alt="New KTP" 
                                                 class="img-thumbnail modal-thumbnail border-success"
                                                 style="width: 80px; height: 50px; object-fit: cover; cursor: pointer; border-width: 2px;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#modalNewKtp{{ $user->id }}">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.6rem;">
                                                Baru
                                            </span>
                                        </div>

                                        {{-- MODAL POPUP FIX IOS & ANDROID --}}
                                        <div class="modal fade modal-ios-fix" id="modalNewKtp{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 999999;">
                                                <div class="modal-content bg-transparent border-0" 
                                                     style="background-color: transparent !important; box-shadow: none !important;">
                                                    
                                                    <div class="modal-body p-0 d-flex justify-content-center align-items-center position-relative" 
                                                         style="min-height: 100vh;">
                                                        
                                                        {{-- Background Overlay untuk iOS --}}
                                                        <div class="modal-overlay-ios" 
                                                             style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 999998;">
                                                        </div>
                                                        
                                                        <button type="button" class="btn-close btn-close-white position-absolute" 
                                                                data-bs-dismiss="modal" 
                                                                aria-label="Close"
                                                                style="z-index: 999999; top: 20px; right: 20px; background-color: rgba(0,0,0,0.7); padding: 12px; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                        </button>

                                                        <div class="image-container-ios" 
                                                             style="position: relative; z-index: 999999; max-width: 90vw; max-height: 80vh;">
                                                            <img src="{{ asset('storage/' . $user->ktp_photo_temp_path) }}" 
                                                                 class="img-fluid ios-modal-image"
                                                                 style="width: auto; max-width: 100%; height: auto; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: block;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-danger small">File Hilang</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('users.approve-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm text-white" 
                                                onclick="return confirm('Setujui penggantian KTP ini? Foto lama akan dihapus permanen.')">
                                                <i class="mdi mdi-check"></i> Setujui
                                            </button>
                                        </form>

                                        <form action="{{ route('users.reject-ktp', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm text-white" 
                                                onclick="return confirm('Tolak pengajuan ini?')">
                                                <i class="mdi mdi-close"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="alert alert-info mt-3">Tidak ada permintaan ganti KTP saat ini.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* FIX untuk iOS */
@supports (-webkit-overflow-scrolling: touch) {
    /* iOS specific fixes */
    .modal-ios-fix {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    
    .modal-ios-fix .modal-dialog {
        margin: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-width: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .modal-ios-fix .modal-content {
        width: 100% !important;
        height: 100% !important;
    }
    
    .ios-modal-image {
        -webkit-user-select: none !important;
        user-select: none !important;
        -webkit-touch-callout: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }
    
    /* Mencegah zoom pada gambar di iOS */
    .modal-thumbnail {
        -webkit-touch-callout: none !important;
    }
}

/* Fix untuk semua mobile */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-width: none !important;
    }
    
    .modal-body {
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
}
</style>

<!-- Tambahkan script untuk handle iOS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Detect iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    
    if (isIOS) {
        console.log('iOS detected, applying modal fixes');
        
        // Fix for modal backdrop
        const style = document.createElement('style');
        style.textContent = `
            .modal-backdrop.show {
                opacity: 0.8 !important;
            }
            .modal-open .modal {
                overflow-x: hidden;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            body.modal-open {
                overflow: hidden;
                position: fixed;
                width: 100%;
            }
        `;
        document.head.appendChild(style);
        
        // Force modal to top when shown
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
            });
            
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
            });
        });
    }
});
</script>
@endsection