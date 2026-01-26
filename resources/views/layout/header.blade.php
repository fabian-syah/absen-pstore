@php
    use Illuminate\Support\Facades\Storage;
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row w-100" style="backdrop-filter: blur(15px); background: rgba(255, 255, 255, 0.85); border-bottom: 1px solid rgba(0,0,0,0.05); box-shadow: 0 2px 20px rgba(0,0,0,0.02);">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start" style="background: transparent;">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize" style="color: #333;">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 140px; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 40px; height: auto;" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text" style="color: #1F1F1F; font-size: 1.5rem; letter-spacing: -0.5px;">@yield('heading')</h1>
                <h3 class="welcome-sub-text" style="color: #4B49AC; font-weight: 700; font-size: 0.85rem;">
                    <span class="badge" style="background: rgba(75, 73, 172, 0.1); color: #4B49AC; border-radius: 6px;">{{ strtoupper(Auth::user()->role) }}</span> 
                    <span class="ms-2 text-muted" style="font-weight: 400;">— {{ Auth::user()->division->name ?? 'PStore Core' }}</span>
                </h3>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            {{-- Fullscreen Button --}}
            <li class="nav-item d-none d-lg-block me-2">
                <a class="nav-link btn-header-action" href="javascript:void(0)" onclick="toggleFullScreen()" title="Toggle Fullscreen">
                    <i class="mdi mdi-fullscreen" style="font-size: 22px; color: #555;"></i>
                </a>
            </li>

            {{-- Search - Untuk Admin, Audit, LEADER, dan ADMIN GAJI --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'admin_gaji']))
                <li class="nav-item d-none d-md-block">
                    <div class="search-form-ios position-relative">
                        <i class="mdi mdi-magnify position-absolute search-icon-ios"></i>
                        <input type="search" class="form-control" id="globalSearch"
                            data-url="{{ route('search') }}" placeholder="Cari rekan kerja..."
                            autocomplete="off">
                        <div class="search-results dropdown-menu shadow-lg border-0" id="searchResults" style="border-radius: 15px; margin-top: 10px;"></div>
                    </div>
                </li>
            @endif

            {{-- Broadcast Notifications --}}
            <li class="nav-item dropdown notification-dropdown">
                <a class="nav-link position-relative d-flex align-items-center justify-content-center btn-header-action" 
                    id="broadcastDropdown" 
                    href="#" 
                    data-bs-toggle="dropdown">
                    <i class="mdi mdi-bell-outline" style="font-size: 22px; color: #555;"></i>
                    <span class="notification-dot" id="broadcastCount" style="display: none;"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0 shadow-lg border-0"
                    aria-labelledby="broadcastDropdown" style="min-width: 350px; border-radius: 20px; overflow: hidden;">
                    <div class="dropdown-header px-4 py-3" style="background: #4B49AC;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-white">Broadcast Alert</h6>
                            <span class="badge bg-white text-primary rounded-pill" id="broadcastTotal" style="font-size: 10px;">0 New</span>
                        </div>
                    </div>
                    <div id="broadcastList" style="max-height: 380px; overflow-y: auto; background: #fff;">
                        <div class="dropdown-item text-center py-5">
                            <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div>
                            <p class="text-muted small mb-0">Sinkronisasi pesan...</p>
                        </div>
                    </div>
                    <a href="javascript:void(0)" class="dropdown-item text-center py-3 text-primary fw-bold border-top" id="viewAllBroadcasts" style="font-size: 12px; background: #f8f9fa;">
                        LIHAT SEMUA BROADCAST <i class="mdi mdi-chevron-right ms-1"></i>
                    </a>
                </div>
            </li>

            {{-- CHAT MULTI-BRANCH --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle btn-header-action" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-email-outline" style="font-size: 22px; color: #555;"></i>
                    <span class="chat-badge-status" id="mainChatBadge" style="display: none;"></span> 
                </a>
                
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list p-0 shadow-lg border-0" 
                     aria-labelledby="messageDropdown" 
                     style="width: 350px; border-radius: 20px; height: 480px; overflow: hidden;">
                    
                    <div class="d-flex flex-column h-100 w-100">
                        <div id="branchListView" class="d-flex flex-column h-100 w-100">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: #fdfdfd;">
                                <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-forum-outline me-2 text-primary"></i>Pesan Cabang</h6>
                                <i class="mdi mdi-dots-vertical text-muted"></i>
                            </div>
                            <div id="branchListBody" class="flex-grow-1" style="overflow-y: auto; background: #fff;">
                                <div class="text-center text-muted mt-5 pt-3">
                                    <div class="spinner-grow text-primary" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
                                </div>
                            </div>
                        </div>

                        <div id="chatRoomView" class="d-none flex-column h-100 w-100">
                            <div class="p-3 border-bottom d-flex align-items-center justify-content-between text-white" style="background: #4B49AC;">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" id="backToBranchList" class="btn btn-sm text-white p-0">
                                        <i class="mdi mdi-chevron-left fs-4"></i>
                                    </button>
                                    <div>
                                        <h6 class="mb-0 fw-bold" id="activeBranchName" style="font-size: 13px;">...</h6>
                                        <small style="font-size: 9px; opacity: 0.8;" id="activeBranchTimezone">Online</small>
                                    </div>
                                </div>
                            </div>

                            <div id="chatBody" class="p-3 flex-grow-1" style="overflow-y: auto; background: #F5F7FF; background-image: radial-gradient(#d1d9ff 0.5px, transparent 0.5px); background-size: 15px 15px;"></div>

                            <div class="p-2 border-top bg-white">
                                <div id="filePreviewArea" class="px-2 pb-2 d-none">
                                    <div class="d-inline-flex align-items-center bg-light border rounded-pill px-3 py-1">
                                        <i class="mdi mdi-image text-success me-2"></i>
                                        <span id="fileNamePreview" class="small text-muted text-truncate" style="max-width: 120px;">...</span>
                                        <button type="button" id="cancelFileBtn" class="btn btn-sm text-danger ms-2 p-0"><i class="mdi mdi-close"></i></button>
                                    </div>
                                </div>
                                <form id="chatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                                    <input type="hidden" id="activeBranchId" name="branch_id">
                                    <input type="file" id="chatImageInput" name="image" accept="image/*" class="d-none">
                                    <button type="button" id="triggerFileBtn" class="btn btn-light btn-rounded-icon"><i class="mdi mdi-plus"></i></button>
                                    <input type="text" id="chatInput" name="message" class="form-control chat-input-ios" placeholder="Ketik pesan...">
                                    <button type="submit" class="btn btn-primary btn-send-ios"><i class="mdi mdi-send"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            {{-- User Profile --}}
            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link p-0 ms-2" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-frame position-relative">
                        @if (Auth::user()->profile_photo_path)
                            <img class="img-xs rounded-circle" 
                                 src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                 alt="Profile image"
                                 style="object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        @else
                            <div class="profile-initial-nav-ios">{{ getInitials(Auth::user()->name) }}</div>
                        @endif
                        <span class="status-indicator-online"></span>
                    </div>
                </a>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown border-0 shadow-lg" aria-labelledby="UserDropdown" style="border-radius: 20px; min-width: 280px; margin-top: 15px;">
                    <div class="dropdown-header text-center p-4" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-radius: 20px 20px 0 0;">
                        <div class="position-relative d-inline-block mb-3">
                            @if (Auth::user()->profile_photo_path)
                                <img class="img-lg rounded-circle shadow"
                                    src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile"
                                    style="width: 80px; height: 80px; border: 4px solid #fff;">
                            @else
                                <div class="profile-initial-dropdown-ios">{{ getInitials(Auth::user()->name) }}</div>
                            @endif
                        </div>
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 16px;">{{ Auth::user()->name }}</h6>
                        <p class="text-muted small mb-0 mt-1">{{ Auth::user()->email }}</p>
                    </div>

                    <div class="p-2">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item py-2 px-3 ios-item">
                            <i class="mdi mdi-account-circle-outline text-primary me-3 fs-5"></i> Pengaturan Profil
                        </a>
                        <a class="dropdown-item py-2 px-3 ios-item">
                            <i class="mdi mdi-help-circle-outline text-success me-3 fs-5"></i> Pusat Bantuan
                        </a>
                        <div class="dropdown-divider mx-3"></div>
                        <a href="{{ route('logout') }}" class="dropdown-item py-2 px-3 ios-item text-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-power me-3 fs-5"></i> Keluar Aplikasi
                        </a>
                    </div>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

<style>
    /* iOS STYLE SEARCH */
    .search-form-ios { width: 240px; margin-right: 20px; }
    .search-icon-ios { left: 15px; top: 50%; transform: translateY(-50%); color: #8E8E93; font-size: 18px; }
    .search-form-ios .form-control {
        background: rgba(118, 118, 128, 0.12);
        border: none;
        border-radius: 12px;
        padding: 8px 15px 8px 40px;
        font-size: 13px;
        color: #000;
        transition: all 0.3s;
    }
    .search-form-ios .form-control:focus { background: rgba(118, 118, 128, 0.2); box-shadow: none; width: 280px; }

    /* HEADER ACTIONS */
    .btn-header-action {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .btn-header-action:hover { background: rgba(0,0,0,0.04); }

    /* NOTIFICATION DOT */
    .notification-dot {
        position: absolute; top: 10px; right: 10px;
        width: 8px; height: 8px; background: #FF4747;
        border-radius: 50%; border: 2px solid #fff;
    }
    .chat-badge-status {
        position: absolute; top: 8px; right: 8px;
        width: 10px; height: 10px; background: #2ECC71;
        border-radius: 50%; border: 2px solid #fff;
    }

    /* PROFILE UI */
    .profile-initial-nav-ios {
        width: 36px; height: 36px; background: #4B49AC;
        color: #fff; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; font-weight: 700;
    }
    .status-indicator-online {
        position: absolute; bottom: 0; right: 0;
        width: 12px; height: 12px; background: #2ECC71;
        border: 2px solid #fff; border-radius: 50%;
    }
    .ios-item { border-radius: 10px; transition: all 0.2s; }
    .ios-item:hover { background: #F2F2F7 !important; color: inherit; }

    /* CHAT COMPONENTS */
    .btn-rounded-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .chat-input-ios { background: #F2F2F7; border: none; border-radius: 20px; padding: 10px 15px; font-size: 13px; }
    .btn-send-ios { width: 36px; height: 36px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; background: #4B49AC; }
</style>