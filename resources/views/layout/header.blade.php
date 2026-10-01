@php
    use Illuminate\Support\Facades\Storage;
@endphp

{{-- =========================================================================
     MODERN AESTHETIC RESPONSIVE HEADER
     - Clean SaaS layout with 1px slate borders and solid white background
     - Zero gradients, zero purple/violet, zero AI-cliché animations
     - Fully responsive with auto-adapting mobile offcanvas & dropdown constraints
     ========================================================================= --}}
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-center flex-row w-100 modern-navbar">
    {{-- BRAND / LOGO & SIDEBAR MINIMIZE WRAPPER --}}
    <div class="navbar-brand-wrapper d-flex align-items-center justify-content-between">
        <div class="brand-inner d-flex align-items-center gap-2">
            {{-- Mobile Sidebar Drawer Toggle (Left Hamburger) --}}
            <button class="navbar-toggler modern-toggle-btn d-inline-flex d-lg-none" type="button" id="mobileHeaderSidebarToggle" title="Buka Menu" aria-label="Buka Menu">
                <i class="mdi mdi-menu"></i>
            </button>

            {{-- Desktop Sidebar Minimize Toggle --}}
            <button class="navbar-toggler modern-toggle-btn d-none d-lg-inline-flex" type="button" data-bs-toggle="minimize" title="Perkecil / Perbesar Menu" aria-label="Toggle Sidebar">
                <i class="mdi mdi-menu"></i>
            </button>

            {{-- Brand Logos --}}
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ pstoreLogoHorizontalDataUri() }}" 
                     onerror="this.onerror=null; this.src='{{ asset('assets/images/logo-pstore.png') }}';" 
                     alt="PStore Logo" 
                     width="160"
                     height="48"
                     style="height: 48px; max-height: 48px; width: auto; max-width: 165px; object-fit: contain; display: block;"
                     class="brand-logo-img" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ pstoreFaviconDataUri() }}" 
                     onerror="this.onerror=null; this.src='{{ asset('assets/images/logo-pstore.png') }}';" 
                     alt="PStore Logo" 
                     width="44"
                     height="44"
                     style="height: 44px; max-height: 44px; width: auto; object-fit: contain; display: block;"
                     class="brand-logo-mini-img" />
            </a>
        </div>
    </div>

    {{-- NAVBAR MENU WRAPPER --}}
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between flex-grow-1">
        {{-- HEADING / TITLE (DESKTOP) --}}
        <div class="header-heading-box d-none d-lg-flex flex-column justify-content-center">
            <h1 class="header-page-title mb-0">@yield('heading')</h1>
            <div class="header-page-meta d-flex align-items-center gap-2 mt-1 flex-wrap">
                <span class="header-role-badge">
                    <i class="mdi mdi-shield-check me-1"></i>{{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}
                </span>
                <span class="header-division-badge">
                    <i class="mdi mdi-office-building-marker me-1"></i>{{ Auth::user()->division->name ?? 'Headquarters' }}
                </span>
            </div>
        </div>

        {{-- ACTIONS & USER CONTROLS --}}
        <div class="header-actions-box d-flex align-items-center gap-2 ms-auto">
            {{-- Search Bar (Admin, Audit, Leader, Admin Gaji) --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'admin_gaji']))
                <div class="modern-search-box position-relative d-none d-md-block">
                    <i class="mdi mdi-magnify search-icon"></i>
                    <input type="search" class="form-control modern-search-input" id="globalSearch"
                        data-url="{{ route('search') }}" placeholder="Cari user / data..." autocomplete="off">
                    <kbd class="search-kbd-chip">Ctrl K</kbd>
                    <div class="search-results dropdown-menu modern-dropdown-pane" id="searchResults"></div>
                </div>

                {{-- Mobile Search Trigger Button --}}
                <button class="modern-action-btn d-inline-flex d-md-none" type="button" id="mobileSearchTrigger" title="Cari user / data..." aria-label="Cari user atau data">
                    <i class="mdi mdi-magnify"></i>
                </button>
            @endif

            {{-- Fullscreen Toggle (Desktop) --}}
            <button class="modern-action-btn d-none d-lg-inline-flex" type="button" onclick="toggleFullScreen()" title="Mode Layar Penuh" aria-label="Toggle Fullscreen">
                <i class="mdi mdi-fullscreen"></i>
            </button>

            {{-- Broadcast Notifications --}}
            <div class="dropdown notification-dropdown">
                <button class="modern-action-btn position-relative" type="button" id="broadcastDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false" title="Pemberitahuan Broadcast">
                    <i class="mdi mdi-bell-outline"></i>
                    <span class="modern-badge modern-badge-danger" id="broadcastCount" style="display: none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end modern-dropdown-pane broadcast-dropdown-pane shadow-sm p-0"
                    aria-labelledby="broadcastDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2.5 border-bottom bg-white">
                        <div>
                            <h6 class="mb-0 fw-bold text-slate-900" style="font-size: 13px;">Broadcast Notifications</h6>
                            <small class="text-muted" id="broadcastTotal" style="font-size: 11px;">0 unread</small>
                        </div>
                        <span class="badge bg-slate-100 text-slate-700 rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 600;">PStore Info</span>
                    </div>
                    <div id="broadcastList" class="custom-dropdown-scroll" style="max-height: 380px; overflow-y: auto;">
                        <div class="dropdown-item text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <p class="text-muted small mb-0">Memuat broadcast...</p>
                        </div>
                    </div>
                    <div class="dropdown-footer border-top p-2 text-center bg-white">
                        <a href="javascript:void(0)" class="text-primary small fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" id="viewAllBroadcasts">
                            <i class="mdi mdi-bullhorn-outline"></i> Lihat Semua Broadcast
                        </a>
                    </div>
                </div>
            </div>

            {{-- Multi-Branch Chat --}}
            <div class="dropdown chat-dropdown">
                <button class="modern-action-btn position-relative" type="button" id="messageDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false" title="Pesan Antar Cabang">
                    <i class="mdi mdi-message-text-outline"></i>
                    <span class="modern-badge modern-badge-danger" id="mainChatBadge" style="display: none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end modern-dropdown-pane chat-dropdown-pane shadow-sm p-0"
                    aria-labelledby="messageDropdown">
                    <div class="d-flex flex-column h-100 w-100">
                        {{-- VIEW 1: DAFTAR CABANG --}}
                        <div id="branchListView" class="d-flex flex-column h-100 w-100">
                            <div class="px-3 py-2.5 border-bottom bg-white d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-0 fw-bold text-slate-900" style="font-size: 13px;">Pesan Cabang</h6>
                                    <small class="text-muted" style="font-size: 11px;">Pilih grup cabang untuk mengobrol</small>
                                </div>
                                <span class="badge bg-blue-50 text-blue-600 rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 600; background: #eff6ff; color: #2563eb;">Live</span>
                            </div>
                            <div id="branchListBody" class="flex-grow-1 custom-dropdown-scroll" style="overflow-y: auto; background: #ffffff;">
                                <div class="text-center text-muted mt-5 pt-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <p class="small mt-2">Memuat daftar cabang...</p>
                                </div>
                            </div>
                        </div>

                        {{-- VIEW 2: ROOM CHAT --}}
                        <div id="chatRoomView" class="d-none flex-column h-100 w-100">
                            <div class="px-3 py-2.5 border-bottom d-flex align-items-center justify-content-between bg-white">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" id="backToBranchList" class="btn btn-sm btn-icon-ghost" title="Kembali ke Daftar">
                                        <i class="mdi mdi-arrow-left fs-6"></i>
                                    </button>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-slate-900" id="activeBranchName" style="font-size: 13px;">Loading...</h6>
                                        <small class="text-muted" style="font-size: 10px;" id="activeBranchTimezone">
                                            <i class="mdi mdi-clock-outline me-1"></i>Asia/Jakarta
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div id="chatBody" class="p-3 flex-grow-1 custom-dropdown-scroll" style="overflow-y: auto; background: #f8fafc;">
                                {{-- Rendered by JS --}}
                            </div>

                            <div class="p-2 border-top bg-white">
                                <div id="filePreviewArea" class="px-2 pb-2 d-none">
                                    <div class="d-inline-flex align-items-center bg-slate-100 border rounded-pill px-2.5 py-1">
                                        <i class="mdi mdi-image text-emerald-600 me-1.5" style="color: #059669; font-size: 14px;"></i>
                                        <span id="fileNamePreview" class="small text-muted" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px;">img.jpg</span>
                                        <button type="button" id="cancelFileBtn" class="btn btn-sm text-danger ms-2 p-0 border-0 bg-transparent">
                                            <i class="mdi mdi-close" style="font-size: 14px;"></i>
                                        </button>
                                    </div>
                                </div>

                                <form id="chatForm" class="d-flex align-items-center gap-1.5" enctype="multipart/form-data">
                                    <input type="hidden" id="activeBranchId" name="branch_id">
                                    <input type="file" id="chatImageInput" name="image" accept="image/*" class="d-none">

                                    <button type="button" id="triggerFileBtn" class="btn btn-sm btn-icon-ghost" title="Kirim Foto">
                                        <i class="mdi mdi-paperclip text-slate-500" style="font-size: 18px;"></i>
                                    </button>

                                    <input type="text" id="chatInput" name="message" class="form-control form-control-sm modern-chat-input"
                                        placeholder="Ketik pesan..." autocomplete="off">

                                    <button type="submit" class="btn btn-primary btn-sm rounded-circle modern-chat-send-btn" title="Kirim">
                                        <i class="mdi mdi-send" style="font-size: 14px;"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="header-divider d-none d-sm-block"></div>

            {{-- User Profile Pill Trigger --}}
            <div class="dropdown user-dropdown">
                <button class="user-pill-btn d-flex align-items-center gap-2" type="button" id="UserDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar-wrap position-relative flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; max-width: 36px; overflow: visible;">
                        <div class="user-avatar-circle" style="width: 36px; height: 36px; min-width: 36px; max-width: 36px; border-radius: 50%; overflow: hidden; border: 1.5px solid #e2e8f0; position: relative;">
                            @if (Auth::user()->profile_photo_path)
                                <img class="user-avatar-img"
                                    src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                    alt="{{ Auth::user()->name }}"
                                    width="36" height="36"
                                    style="width: 36px; height: 36px; min-width: 36px; max-width: 36px; object-fit: cover; display: block; border-radius: 50%;">
                            @else
                                <div class="user-avatar-initials" style="width: 36px; height: 36px; border-radius: 50%;">
                                    {{ getInitials(Auth::user()->name) }}
                                </div>
                            @endif
                        </div>

                        @if(Auth::user()->is_verified)
                            <span class="user-verified-badge" title="Verified Account">
                                <i class="mdi mdi-check-decagram"></i>
                            </span>
                        @endif
                    </div>
                    <div class="user-pill-text d-none d-xl-flex flex-column text-start">
                        <span class="user-pill-name text-truncate">{{ Str::limit(Auth::user()->name, 15) }}</span>
                        <span class="user-pill-role text-truncate">{{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}</span>
                    </div>
                    <i class="mdi mdi-chevron-down user-pill-chevron d-none d-sm-inline-block"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end modern-dropdown-pane user-dropdown-pane shadow-sm p-0"
                    aria-labelledby="UserDropdown">
                    <div class="user-dropdown-header p-3 border-bottom bg-slate-50">
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-header-avatar-wrap position-relative flex-shrink-0" style="width: 46px; height: 46px; min-width: 46px; max-width: 46px; overflow: visible;">
                                <div class="user-header-avatar-circle" style="width: 46px; height: 46px; min-width: 46px; max-width: 46px; border-radius: 50%; overflow: hidden; border: 2px solid #ffffff; position: relative;">
                                    @if (Auth::user()->profile_photo_path)
                                        <img class="user-header-avatar"
                                            src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                            alt="{{ Auth::user()->name }}"
                                            width="46" height="46"
                                            style="width: 46px; height: 46px; min-width: 46px; max-width: 46px; object-fit: cover; display: block; border-radius: 50%;">
                                    @else
                                        <div class="user-header-initials" style="width: 46px; height: 46px; border-radius: 50%;">
                                            {{ getInitials(Auth::user()->name) }}
                                        </div>
                                    @endif
                                </div>
                                @if(Auth::user()->is_verified)
                                    <span class="user-header-verified" title="Verified Account">
                                        <i class="mdi mdi-check-decagram"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="overflow-hidden flex-grow-1">
                                <h6 class="mb-0 fw-bold text-slate-900 text-truncate d-flex align-items-center gap-1" style="font-size: 13.5px;">
                                    {{ Auth::user()->name }}
                                    @if(Auth::user()->is_verified)
                                        <i class="mdi mdi-check-decagram text-primary" style="font-size: 15px;"></i>
                                    @endif
                                </h6>
                                <small class="text-muted d-block text-truncate mt-0.5" style="font-size: 11px;">{{ Auth::user()->email }}</small>
                                <div class="user-dropdown-badges d-flex align-items-center gap-2 mt-2 flex-wrap">
                                    <span class="dropdown-role-badge">
                                        <i class="mdi mdi-shield-account me-1"></i>{{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}
                                    </span>
                                    <span class="dropdown-division-badge">
                                        <i class="mdi mdi-office-building me-1"></i>{{ Auth::user()->division->name ?? 'Headquarters' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="py-1">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item modern-dropdown-item d-flex align-items-center gap-2.5 px-3 py-2">
                            <i class="mdi mdi-account-outline text-slate-500" style="font-size: 18px;"></i>
                            <span class="text-slate-700" style="font-size: 13px;">Profil Saya</span>
                        </a>
                        @if(Route::has('chat.index'))
                            <a href="{{ route('chat.index') }}" class="dropdown-item modern-dropdown-item d-flex align-items-center gap-2.5 px-3 py-2">
                                <i class="mdi mdi-message-text-outline text-slate-500" style="font-size: 18px;"></i>
                                <span class="text-slate-700" style="font-size: 13px;">Pesan</span>
                            </a>
                        @endif
                    </div>

                    <div class="dropdown-divider my-0"></div>

                    <div class="p-1">
                        <a href="{{ route('logout') }}" class="dropdown-item modern-dropdown-item dropdown-item-danger text-danger d-flex align-items-center gap-2.5 px-3 py-2 rounded-2"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout text-danger" style="font-size: 18px;"></i>
                            <span class="fw-semibold" style="font-size: 13px;">Keluar</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- =========================================================================
     MOBILE SEARCH OVERLAY
     - Modern full-width sliding search for mobile/tablet devices
     ========================================================================= --}}
@if (in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'admin_gaji']))
    <div class="mobile-search-overlay" id="mobileSearchOverlay" aria-modal="true" role="dialog">
        <div class="mobile-search-header d-flex align-items-center gap-2 px-3 py-2 border-bottom bg-white">
            <button type="button" class="modern-action-btn mobile-search-back-btn flex-shrink-0" id="mobileSearchClose" aria-label="Tutup pencarian" title="Kembali">
                <i class="mdi mdi-arrow-left"></i>
            </button>
            <div class="position-relative flex-grow-1">
                <i class="mdi mdi-magnify mobile-search-icon"></i>
                <input type="search" class="form-control mobile-search-input" id="mobileGlobalSearch"
                    data-url="{{ route('search') }}" placeholder="Cari user, divisi, cabang..." autocomplete="off">
                <button type="button" class="mobile-search-clear-btn" id="mobileSearchClear" aria-label="Hapus teks" title="Hapus">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
        </div>

        <div class="mobile-search-body" id="mobileSearchBody">
            {{-- Initial State --}}
            <div class="mobile-search-state py-5 px-3 text-center" id="mobileSearchInitialState">
                <div class="mobile-search-state-icon mb-2">
                    <i class="mdi mdi-magnify text-slate-300" style="font-size: 38px;"></i>
                </div>
                <div class="fw-semibold text-slate-700" style="font-size: 13.5px;">Cari Data Sistem</div>
                <p class="text-muted small mb-0 mt-1" style="font-size: 11.5px;">Ketik minimal 2 karakter untuk mencari nama user, email, pengumuman, divisi, atau cabang.</p>
            </div>

            {{-- Loading State --}}
            <div class="mobile-search-state py-5 px-3 text-center d-none" id="mobileSearchLoadingState">
                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                <p class="text-muted small mb-0">Mencari data...</p>
            </div>

            {{-- Empty State --}}
            <div class="mobile-search-state py-5 px-3 text-center d-none" id="mobileSearchEmptyState">
                <div class="mobile-search-state-icon mb-2">
                    <i class="mdi mdi-account-search-outline text-slate-300" style="font-size: 38px;"></i>
                </div>
                <div class="fw-semibold text-slate-700" style="font-size: 13.5px;">Tidak Ada Data Ditemukan</div>
                <p class="text-muted small mb-0 mt-1" style="font-size: 11.5px;">Tidak ditemukan data yang sesuai dengan kata kunci Anda.</p>
            </div>

            {{-- Results Container --}}
            <div class="mobile-search-results-list d-none" id="mobileSearchResultsList">
                {{-- Populated by JS --}}
            </div>
        </div>
    </div>
    <div class="mobile-search-backdrop" id="mobileSearchBackdrop"></div>
@endif

{{-- =========================================================================
     JAVASCRIPT LOGIC
     - Search with keyboard shortcut (Ctrl+K)
     - Broadcast Notifications Polling & UI
     - Multi-Branch Chat Polling & UI
     - Fullscreen Toggle
     ========================================================================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ==========================================
        // 1. GLOBAL SEARCH LOGIC (DESKTOP & MOBILE)
        // ==========================================
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout = null;

        // Desktop Search
        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function () {
                const query = this.value;
                const url = this.getAttribute('data-url');
                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    searchResults.classList.remove('show');
                    searchResults.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`${url}?q=${encodeURIComponent(query)}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Network error');
                            return response.json();
                        })
                        .then(data => {
                            renderSearchResults(data.results);
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            searchResults.innerHTML = '<div class="dropdown-item text-danger py-2 small">Error loading results</div>';
                            searchResults.classList.add('show');
                        });
                }, 400);
            });

            searchInput.addEventListener('focus', function () {
                if (this.value.length >= 2 && searchResults.innerHTML !== '') {
                    searchResults.classList.add('show');
                }
            });

            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.remove('show');
                }
            });
        }

        function renderSearchResults(results) {
            if (!searchResults) return;
            if (!results || results.length === 0) {
                searchResults.innerHTML = '<div class="dropdown-item text-muted py-3 text-center small">Tidak ada data ditemukan</div>';
            } else {
                let html = '';
                results.forEach(item => {
                    const typeLabel = item.type ? item.type.toUpperCase() : 'DATA';
                    html += `
                        <a href="${item.url}" class="dropdown-item modern-search-item px-3 py-2.5 d-flex align-items-center gap-2.5 border-bottom">
                            <div class="search-item-icon-box d-flex align-items-center justify-content-center rounded-2">
                                <i class="mdi ${item.icon || 'mdi-magnify'} text-primary" style="font-size: 18px;"></i>
                            </div>
                            <div class="overflow-hidden flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <span class="text-slate-900 fw-semibold text-truncate" style="font-size: 13px;">${escapeHtml(item.title)}</span>
                                    <span class="badge bg-slate-100 text-slate-600 rounded-pill" style="font-size: 9px; font-weight: 600;">${escapeHtml(typeLabel)}</span>
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 11px;">${escapeHtml(item.description)}</div>
                            </div>
                        </a>
                    `;
                });
                searchResults.innerHTML = html;
            }
            searchResults.classList.add('show');
        }

        // ==========================================
        // 1.1 MOBILE SEARCH LOGIC
        // ==========================================
        const mobileSearchTrigger = document.getElementById('mobileSearchTrigger');
        const mobileSearchOverlay = document.getElementById('mobileSearchOverlay');
        const mobileSearchBackdrop = document.getElementById('mobileSearchBackdrop');
        const mobileSearchClose = document.getElementById('mobileSearchClose');
        const mobileSearchInput = document.getElementById('mobileGlobalSearch');
        const mobileSearchClear = document.getElementById('mobileSearchClear');
        const mobileSearchResultsList = document.getElementById('mobileSearchResultsList');
        const mobileSearchInitialState = document.getElementById('mobileSearchInitialState');
        const mobileSearchLoadingState = document.getElementById('mobileSearchLoadingState');
        const mobileSearchEmptyState = document.getElementById('mobileSearchEmptyState');
        const sidebarMobileSearchTrigger = document.getElementById('sidebarMobileSearchTrigger');
        let mobileSearchTimeout = null;

        function openMobileSearch() {
            if (!mobileSearchOverlay) return;
            mobileSearchOverlay.classList.add('active');
            if (mobileSearchBackdrop) mobileSearchBackdrop.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Tutup sidebar mobile jika sedang terbuka
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) sidebar.classList.remove('active');
            if (backdrop) backdrop.classList.remove('show');
            document.body.classList.remove('sidebar-drawer-open');

            setTimeout(() => {
                if (mobileSearchInput) {
                    mobileSearchInput.focus();
                }
            }, 100);
        }

        function closeMobileSearch() {
            if (!mobileSearchOverlay) return;
            mobileSearchOverlay.classList.remove('active');
            if (mobileSearchBackdrop) mobileSearchBackdrop.classList.remove('active');
            document.body.style.overflow = '';
            if (mobileSearchInput) {
                mobileSearchInput.blur();
            }
        }

        // Expose globally
        window.openMobileSearch = openMobileSearch;
        window.closeMobileSearch = closeMobileSearch;

        if (mobileSearchTrigger) {
            mobileSearchTrigger.addEventListener('click', openMobileSearch);
        }

        if (sidebarMobileSearchTrigger) {
            sidebarMobileSearchTrigger.addEventListener('click', openMobileSearch);
        }

        if (mobileSearchClose) {
            mobileSearchClose.addEventListener('click', closeMobileSearch);
        }

        if (mobileSearchBackdrop) {
            mobileSearchBackdrop.addEventListener('click', closeMobileSearch);
        }

        if (mobileSearchClear && mobileSearchInput) {
            mobileSearchClear.addEventListener('click', function () {
                mobileSearchInput.value = '';
                mobileSearchClear.style.display = 'none';
                if (mobileSearchInitialState) mobileSearchInitialState.classList.remove('d-none');
                if (mobileSearchLoadingState) mobileSearchLoadingState.classList.add('d-none');
                if (mobileSearchEmptyState) mobileSearchEmptyState.classList.add('d-none');
                if (mobileSearchResultsList) {
                    mobileSearchResultsList.classList.add('d-none');
                    mobileSearchResultsList.innerHTML = '';
                }
                mobileSearchInput.focus();
            });
        }

        if (mobileSearchInput) {
            mobileSearchInput.addEventListener('input', function () {
                const query = this.value.trim();
                const url = this.getAttribute('data-url');
                clearTimeout(mobileSearchTimeout);

                if (query.length > 0) {
                    if (mobileSearchClear) mobileSearchClear.style.display = 'inline-flex';
                } else {
                    if (mobileSearchClear) mobileSearchClear.style.display = 'none';
                }

                if (query.length < 2) {
                    if (mobileSearchInitialState) mobileSearchInitialState.classList.remove('d-none');
                    if (mobileSearchLoadingState) mobileSearchLoadingState.classList.add('d-none');
                    if (mobileSearchEmptyState) mobileSearchEmptyState.classList.add('d-none');
                    if (mobileSearchResultsList) {
                        mobileSearchResultsList.classList.add('d-none');
                        mobileSearchResultsList.innerHTML = '';
                    }
                    return;
                }

                if (mobileSearchInitialState) mobileSearchInitialState.classList.add('d-none');
                if (mobileSearchEmptyState) mobileSearchEmptyState.classList.add('d-none');
                if (mobileSearchResultsList) mobileSearchResultsList.classList.add('d-none');
                if (mobileSearchLoadingState) mobileSearchLoadingState.classList.remove('d-none');

                mobileSearchTimeout = setTimeout(() => {
                    fetch(`${url}?q=${encodeURIComponent(query)}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Network error');
                            return response.json();
                        })
                        .then(data => {
                            if (mobileSearchLoadingState) mobileSearchLoadingState.classList.add('d-none');
                            renderMobileSearchResults(data.results);
                        })
                        .catch(error => {
                            console.error('Mobile search error:', error);
                            if (mobileSearchLoadingState) mobileSearchLoadingState.classList.add('d-none');
                            if (mobileSearchEmptyState) {
                                mobileSearchEmptyState.classList.remove('d-none');
                                const p = mobileSearchEmptyState.querySelector('p');
                                if (p) p.textContent = 'Terjadi kesalahan saat memuat data.';
                            }
                        });
                }, 350);
            });
        }

        function renderMobileSearchResults(results) {
            if (!mobileSearchResultsList) return;

            if (!results || results.length === 0) {
                if (mobileSearchEmptyState) {
                    mobileSearchEmptyState.classList.remove('d-none');
                    const p = mobileSearchEmptyState.querySelector('p');
                    if (p) p.textContent = 'Tidak ditemukan data yang sesuai dengan kata kunci Anda.';
                }
                mobileSearchResultsList.classList.add('d-none');
                mobileSearchResultsList.innerHTML = '';
                return;
            }

            if (mobileSearchEmptyState) mobileSearchEmptyState.classList.add('d-none');

            let html = '';
            results.forEach(item => {
                const typeLabel = item.type ? item.type.toUpperCase() : 'DATA';
                html += `
                    <a href="${item.url}" class="mobile-search-result-item">
                        <div class="mobile-search-icon-box">
                            <i class="mdi ${item.icon || 'mdi-magnify'}"></i>
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-0.5">
                                <div class="text-slate-900 fw-semibold text-truncate" style="font-size: 13px;">${escapeHtml(item.title)}</div>
                                <span class="badge" style="font-size: 9.5px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 1.5px 6px; border-radius: 4px;">${escapeHtml(typeLabel)}</span>
                            </div>
                            <div class="text-muted text-truncate" style="font-size: 11px;">${escapeHtml(item.description)}</div>
                        </div>
                        <i class="mdi mdi-chevron-right text-slate-400" style="font-size: 18px; flex-shrink: 0;"></i>
                    </a>
                `;
            });

            mobileSearchResultsList.innerHTML = html;
            mobileSearchResultsList.classList.remove('d-none');
        }

        // Global Shortcut Ctrl+K / Cmd+K
        window.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (window.innerWidth < 768) {
                    openMobileSearch();
                } else if (searchInput) {
                    searchInput.focus();
                }
            } else if (e.key === 'Escape') {
                if (mobileSearchOverlay && mobileSearchOverlay.classList.contains('active')) {
                    closeMobileSearch();
                }
            }
        });

        // Close mobile search on resize to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768 && mobileSearchOverlay && mobileSearchOverlay.classList.contains('active')) {
                closeMobileSearch();
            }
        });

        // ==========================================
        // 2. BROADCAST NOTIFICATIONS LOGIC
        // ==========================================
        const broadcastDropdown = document.getElementById('broadcastDropdown');
        const broadcastList = document.getElementById('broadcastList');
        const broadcastCount = document.getElementById('broadcastCount');
        const broadcastTotal = document.getElementById('broadcastTotal');
        const viewAllBroadcasts = document.getElementById('viewAllBroadcasts');

        function loadBroadcastNotifications() {
            fetch('{{ route('broadcast.notifications') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    updateBroadcastUI(data);
                })
                .catch(error => {
                    console.error('Error loading broadcasts:', error);
                    showBroadcastError();
                });
        }

        function updateBroadcastUI(data) {
            const broadcasts = data.broadcasts || [];
            const unreadCount = data.unread_count || 0;

            if (unreadCount > 0) {
                broadcastCount.textContent = unreadCount > 99 ? '99+' : unreadCount;
                broadcastCount.style.display = 'inline-flex';
                broadcastTotal.textContent = unreadCount + ' unread';
            } else {
                broadcastCount.style.display = 'none';
                broadcastTotal.textContent = 'No unread';
            }

            if (broadcasts.length === 0) {
                broadcastList.innerHTML = `
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-2">
                            <i class="mdi mdi-bullhorn-outline" style="font-size: 40px; color: #cbd5e1;"></i>
                        </div>
                        <h6 class="text-slate-700 mb-1 fw-semibold" style="font-size: 13px;">Belum Ada Broadcast</h6>
                        <p class="text-muted small mb-0">Semua pengumuman telah Anda baca.</p>
                    </div>
                `;
            } else {
                const baseUrl = "{{ route('broadcast.show', ':id') }}";
                const broadcastItems = broadcasts.map(broadcast => {
                    const detailUrl = baseUrl.replace(':id', broadcast.id);
                    const limit = 80;
                    const shortMessage = broadcast.message.length > limit
                        ? broadcast.message.substring(0, limit) + '...'
                        : broadcast.message;

                    return `
                        <a class="broadcast-item ${broadcast.is_read ? '' : 'unread'}" href="${detailUrl}">
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="broadcast-icon ${broadcast.priority_color}">
                                    <i class="${broadcast.priority_icon}"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="broadcast-title mb-0 text-truncate">${escapeHtml(broadcast.title)}</h6>
                                        ${broadcast.is_read ? '' : '<span class="unread-dot"></span>'}
                                    </div>
                                    <p class="broadcast-message mb-1.5">${escapeHtml(shortMessage)}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="broadcast-read-more">Baca detail <i class="mdi mdi-arrow-right"></i></span>
                                        <small class="broadcast-time">${formatTimeAgo(broadcast.published_at)}</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('');
                broadcastList.innerHTML = broadcastItems;
            }
        }

        function showBroadcastError() {
            broadcastList.innerHTML = `
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-2 text-danger">
                        <i class="mdi mdi-alert-circle-outline" style="font-size: 36px;"></i>
                    </div>
                    <h6 class="text-danger mb-1 fw-semibold" style="font-size: 13px;">Gagal Memuat</h6>
                    <p class="text-muted small mb-0">Silakan coba beberapa saat lagi</p>
                </div>
            `;
        }

        if (viewAllBroadcasts) {
            viewAllBroadcasts.addEventListener('click', function () {
                @if (auth()->user()->role == 'admin')
                    window.location.href = '{{ route('broadcast.index') }}';
                @else
                    alert('Fitur View All untuk user biasa belum aktif.');
                @endif
            });
        }

        // Defer initial notification load
        setTimeout(function() {
            loadBroadcastNotifications();
            setInterval(loadBroadcastNotifications, 60000);
        }, 1500);

        if (broadcastDropdown) {
            broadcastDropdown.addEventListener('click', function () {
                loadBroadcastNotifications();
            });
        }

        // ==========================================
        // 3. MULTI-BRANCH CHAT LOGIC (2 Views)
        // ==========================================
        const messageDropdown = document.getElementById('messageDropdown');
        const mainChatBadge = document.getElementById('mainChatBadge');

        // Views
        const branchListView = document.getElementById('branchListView');
        const branchListBody = document.getElementById('branchListBody');
        const chatRoomView = document.getElementById('chatRoomView');
        const backToBranchList = document.getElementById('backToBranchList');

        // Chat Elements
        const activeBranchName = document.getElementById('activeBranchName');
        const activeBranchTimezone = document.getElementById('activeBranchTimezone');
        const activeBranchId = document.getElementById('activeBranchId');
        const chatBody = document.getElementById('chatBody');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatImageInput = document.getElementById('chatImageInput');
        const triggerFileBtn = document.getElementById('triggerFileBtn');
        const filePreviewArea = document.getElementById('filePreviewArea');
        const fileNamePreview = document.getElementById('fileNamePreview');
        const cancelFileBtn = document.getElementById('cancelFileBtn');

        // State variables
        let isDropdownOpen = false;
        let currentView = 'list';
        let currentBranchId = null;
        let branchInterval = null;
        let messageInterval = null;

        if (messageDropdown) {
            messageDropdown.addEventListener('show.bs.dropdown', function () {
                isDropdownOpen = true;
                if (currentView === 'list') {
                    loadBranchList();
                    branchInterval = setInterval(loadBranchList, 5000);
                } else if (currentView === 'room' && currentBranchId) {
                    loadMessages(currentBranchId);
                    messageInterval = setInterval(() => loadMessages(currentBranchId), 3000);
                }
            });

            messageDropdown.addEventListener('hide.bs.dropdown', function () {
                isDropdownOpen = false;
                clearInterval(branchInterval);
                clearInterval(messageInterval);
            });
        }

        // Prevent Close on Click Inside Chat Pane
        const msgDropdownMenu = document.querySelector('.dropdown-menu[aria-labelledby="messageDropdown"]');
        if (msgDropdownMenu) {
            msgDropdownMenu.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // --- 1. LIST CABANG LOGIC ---
        function loadBranchList() {
            if (!isDropdownOpen && currentView !== 'list') return;

            fetch('{{ route('chat.branches') }}')
                .then(res => res.json())
                .then(data => {
                    renderBranchList(data.branches);
                    updateMainBadge(data.total_unread);
                })
                .catch(err => console.error(err));
        }

        function renderBranchList(branches) {
            if (!branches || branches.length === 0) {
                branchListBody.innerHTML = `
                    <div class="text-center text-muted mt-5 pt-3">
                        <i class="mdi mdi-office-building-remove fs-1 text-slate-300"></i>
                        <p class="small mt-2">Anda tidak terhubung ke cabang manapun.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            branches.forEach(branch => {
                let badgeHtml = '';
                if (branch.unread_count > 0) {
                    badgeHtml = `<span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px; font-weight: 700;">${branch.unread_count}</span>`;
                }

                html += `
                    <div class="p-3 border-bottom d-flex align-items-center branch-item" 
                         onclick="openChatRoom(${branch.id}, '${escapeHtml(branch.name)}', '${branch.timezone}')">
                        <div class="branch-avatar me-3">
                            <i class="mdi mdi-office-building"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-slate-900 fw-semibold text-truncate" style="max-width: 180px; font-size: 13px;">${escapeHtml(branch.name)}</h6>
                                ${badgeHtml}
                            </div>
                            <small class="text-muted text-truncate d-block" style="font-size: 11px;">${escapeHtml(branch.last_message || 'Belum ada obrolan')}</small>
                        </div>
                    </div>
                `;
            });
            branchListBody.innerHTML = html;
        }

        function updateMainBadge(count) {
            if (count > 0) {
                mainChatBadge.textContent = count > 99 ? '99+' : count;
                mainChatBadge.style.display = 'inline-flex';
            } else {
                mainChatBadge.style.display = 'none';
            }
        }

        // --- 2. ROOM CHAT LOGIC ---
        window.openChatRoom = function (branchId, branchName, timezone) {
            currentView = 'room';
            currentBranchId = branchId;

            branchListView.classList.remove('d-flex');
            branchListView.classList.add('d-none');

            chatRoomView.classList.remove('d-none');
            chatRoomView.classList.add('d-flex');

            activeBranchName.textContent = branchName;
            activeBranchTimezone.textContent = timezone;
            activeBranchId.value = branchId;

            chatBody.innerHTML = '<div class="text-center text-muted mt-5 pt-5"><div class="spinner-border spinner-border-sm text-primary"></div><p class="small mt-2">Memuat pesan...</p></div>';

            clearInterval(branchInterval);
            loadMessages(branchId);
            messageInterval = setInterval(() => loadMessages(branchId), 3000);
        };

        if (backToBranchList) {
            backToBranchList.addEventListener('click', function () {
                currentView = 'list';
                currentBranchId = null;

                chatRoomView.classList.remove('d-flex');
                chatRoomView.classList.add('d-none');

                branchListView.classList.remove('d-none');
                branchListView.classList.add('d-flex');

                clearInterval(messageInterval);
                loadBranchList();
                branchInterval = setInterval(loadBranchList, 5000);
            });
        }

        function loadMessages(branchId) {
            if (currentView !== 'room') return;

            fetch(`{{ route('messages.index') }}?branch_id=${branchId}`)
                .then(res => res.json())
                .then(data => {
                    renderChat(data.messages);
                })
                .catch(err => console.error(err));
        }

        function renderChat(messages) {
            if (!messages || messages.length === 0) {
                chatBody.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small py-5">
                        <i class="mdi mdi-chat-processing-outline fs-1 mb-2 text-slate-300"></i>
                        <p class="mb-0">Belum ada obrolan di cabang ini.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            messages.forEach(msg => {
                let imageHtml = '';
                if (msg.image_url) {
                    imageHtml = `
                        <div class="mb-1">
                            <a href="${msg.image_url}" target="_blank">
                                <img src="${msg.image_url}" class="rounded border" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                            </a>
                        </div>
                    `;
                }
                let textHtml = msg.message ? `<div>${escapeHtml(msg.message)}</div>` : '';

                if (msg.is_me) {
                    html += `
                        <div class="d-flex justify-content-end mb-2.5">
                            <div class="text-end" style="max-width: 85%;">
                                <div class="chat-bubble-me text-start d-inline-block">
                                    ${imageHtml}
                                    ${textHtml}
                                </div>
                                <div class="chat-time-label mt-1 text-muted text-end">${msg.time}</div>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="d-flex justify-content-start mb-2.5">
                            <div class="me-2 mt-0.5">
                                ${msg.user_avatar
                                    ? `<img src="/storage/${msg.user_avatar}" class="rounded-circle border" style="width: 26px; height: 26px; object-fit: cover;">`
                                    : `<div class="rounded-circle bg-slate-200 text-slate-700 d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 10px;">${msg.user_name.charAt(0)}</div>`
                                }
                            </div>
                            <div style="max-width: 85%;">
                                <small class="d-block text-slate-700 fw-semibold mb-1" style="font-size: 11px;">${msg.user_name}</small>
                                <div class="chat-bubble-other d-inline-block">
                                    ${imageHtml}
                                    ${textHtml}
                                </div>
                                <div class="chat-time-label mt-1 text-muted">${msg.time}</div>
                            </div>
                        </div>
                    `;
                }
            });

            const isScrolledBottom = (chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop) < 150;
            chatBody.innerHTML = html;

            if (isScrolledBottom || messages.length <= 5) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        // --- 3. SEND MESSAGE LOGIC ---
        if (triggerFileBtn) triggerFileBtn.addEventListener('click', () => chatImageInput.click());

        if (chatImageInput) {
            chatImageInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    filePreviewArea.classList.remove('d-none');
                    fileNamePreview.textContent = this.files[0].name;
                    chatInput.placeholder = "Tambahkan keterangan foto...";
                }
            });
        }

        if (cancelFileBtn) {
            cancelFileBtn.addEventListener('click', resetFileInput);
        }

        function resetFileInput() {
            chatImageInput.value = '';
            filePreviewArea.classList.add('d-none');
            chatInput.placeholder = "Ketik pesan...";
        }

        if (chatForm) {
            chatForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                if (!formData.get('message').trim() && (!formData.get('image') || formData.get('image').size === 0)) return;

                chatInput.value = '';
                resetFileInput();

                fetch('{{ route('messages.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) alert(data.error);
                        else {
                            loadMessages(currentBranchId);
                            chatBody.scrollTop = chatBody.scrollHeight;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Gagal mengirim pesan.");
                    });
            });
        }

        // Utilities
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Baru saja';
            if (diffMins < 60) return `${diffMins}m lalu`;
            if (diffHours < 24) return `${diffHours}j lalu`;
            if (diffDays < 7) return `${diffDays}h lalu`;

            return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
        }

        // Auto load badge
        setTimeout(function() {
            fetch('{{ route('chat.branches') }}')
                .then(res => res.json())
                .then(data => updateMainBadge(data.total_unread))
                .catch(e => { });
        }, 2000);
    });

    // Fullscreen Toggle
    function toggleFullScreen() {
        if (!document.fullscreenElement &&
            !document.webkitFullscreenElement &&
            !document.mozFullScreenElement &&
            !document.msFullscreenElement) {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }
</script>

{{-- =========================================================================
     MODERN AESTHETIC CSS STYLES
     - Strict 1px slate-200 border system
     - Solid clean white surfaces, no gradients
     - No purple/violet tints, zero pulsating badge animations
     - Modern compact height overrides for Star Admin template
     ========================================================================= --}}
<style>
    :root {
        --header-height: 70px !important;
        --modern-header-height: 70px;
        --modern-sidebar-width: 245px;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-300: #cbd5e1;
        --slate-400: #94a3b8;
        --slate-500: #64748b;
        --slate-600: #475569;
        --slate-700: #334155;
        --slate-800: #1e293b;
        --slate-900: #0f172a;
        --primary-blue: #2563eb;
        --primary-blue-hover: #1d4ed8;
        --danger-red: #ef4444;
    }

    @media (max-width: 991px) {
        :root {
            --header-height: 58px !important;
            --modern-header-height: 58px;
        }
    }

    /* --- OVERRIDES FOR STAR ADMIN TEMPLATE BULKY HEIGHTS --- */
    .navbar.default-layout.modern-navbar {
        height: var(--modern-header-height) !important;
        background: #ffffff !important;
        border-bottom: 1px solid var(--slate-200) !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
        transition: none !important;
        z-index: 1030;
    }

    .navbar.default-layout.modern-navbar .navbar-brand-wrapper {
        height: var(--modern-header-height) !important;
        width: var(--modern-sidebar-width) !important;
        background: #ffffff !important;
        border-right: 1px solid var(--slate-200) !important;
        padding: 0 1.25rem !important;
        transition: width 0.2s ease, padding 0.2s ease;
    }

    body.sidebar-icon-only .navbar.default-layout.modern-navbar .navbar-brand-wrapper {
        width: 70px !important;
        padding: 0 0.5rem !important;
        justify-content: center !important;
    }

    .navbar.default-layout.modern-navbar .navbar-menu-wrapper {
        height: var(--modern-header-height) !important;
        width: calc(100% - var(--modern-sidebar-width)) !important;
        background: #ffffff !important;
        padding: 0 1.5rem !important;
        transition: width 0.2s ease;
    }

    body.sidebar-icon-only .navbar.default-layout.modern-navbar .navbar-menu-wrapper {
        width: calc(100% - 70px) !important;
    }

    /* Adjust main body and sidebar offsets to match the clean header */
    .page-body-wrapper {
        padding-top: var(--modern-header-height) !important;
        min-height: calc(100vh - var(--modern-header-height)) !important;
    }

    @media (min-width: 992px) {
        .sidebar {
            top: var(--modern-header-height) !important;
            height: calc(100vh - var(--modern-header-height)) !important;
        }
        .main-panel {
            min-height: calc(100vh - var(--modern-header-height)) !important;
        }
    }

    @media (max-width: 991px) {
        .navbar.default-layout.modern-navbar .navbar-brand-wrapper {
            width: auto !important;
            border-right: none !important;
            padding: 0 0.75rem !important;
        }
        .navbar.default-layout.modern-navbar .navbar-menu-wrapper {
            width: auto !important;
            padding: 0 0.75rem !important;
        }
    }

    @media (max-width: 575px) {
        .navbar.default-layout.modern-navbar .navbar-brand-wrapper {
            padding: 0 0.5rem !important;
        }
        .navbar.default-layout.modern-navbar .navbar-menu-wrapper {
            padding: 0 0.5rem !important;
        }
        .brand-logo-mini-img {
            height: 38px !important;
            max-height: 38px !important;
        }
        .header-actions-box {
            gap: 6px !important;
        }
        .modern-action-btn {
            width: 34px !important;
            height: 34px !important;
        }
        .modern-action-btn i {
            font-size: 18px !important;
        }
        .user-pill-btn {
            padding: 2px !important;
        }
        .user-avatar-wrap {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            max-width: 34px !important;
        }
        .user-avatar-img,
        img.user-avatar-img {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            max-width: 34px !important;
        }
        .broadcast-dropdown-pane,
        .chat-dropdown-pane,
        .user-dropdown-pane {
            position: fixed !important;
            top: 58px !important;
            left: 10px !important;
            right: 10px !important;
            width: auto !important;
            max-width: none !important;
            transform: none !important;
        }
        .chat-dropdown-pane {
            height: calc(100vh - 130px) !important;
            max-height: 520px !important;
        }
    }

    /* --- BRAND LOGO STYLING --- */
    .brand-logo-img {
        height: 48px !important;
        max-height: 48px !important;
        width: auto !important;
        max-width: 165px !important;
        object-fit: contain !important;
        display: block !important;
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.04));
        transition: transform 0.2s ease;
    }

    .brand-logo-img:hover {
        transform: scale(1.02);
    }

    .brand-logo-mini-img {
        height: 44px !important;
        max-height: 44px !important;
        width: auto !important;
        object-fit: contain !important;
        display: block !important;
    }

    .navbar-brand.brand-logo {
        display: inline-flex !important;
        align-items: center !important;
        padding: 0 !important;
        margin: 0 !important;
        text-decoration: none !important;
    }

    .navbar-brand.brand-logo-mini {
        display: none !important;
        align-items: center !important;
        padding: 0 !important;
        margin: 0 !important;
        text-decoration: none !important;
    }

    body.sidebar-icon-only .navbar-brand.brand-logo {
        display: none !important;
    }

    body.sidebar-icon-only .navbar-brand.brand-logo-mini {
        display: inline-flex !important;
    }

    @media (max-width: 991px) {
        .navbar-brand.brand-logo {
            display: none !important;
        }
        .navbar-brand.brand-logo-mini {
            display: inline-flex !important;
        }
    }

    /* --- TOGGLE BUTTONS --- */
    .modern-toggle-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        background: transparent;
        color: var(--slate-600);
        padding: 0;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .modern-toggle-btn:hover {
        background: var(--slate-100);
        color: var(--slate-900);
        border-color: var(--slate-200);
    }

    .modern-toggle-btn i {
        font-size: 20px;
        line-height: 1;
    }

    /* --- HEADING AREA --- */
    .header-heading-box {
        min-width: 0;
        max-width: 480px;
        flex-shrink: 1;
        overflow: hidden;
    }

    .header-page-title {
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: var(--slate-900) !important;
        letter-spacing: -0.015em;
        line-height: 1.25;
        margin: 0 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .header-page-meta {
        font-size: 11px;
        gap: 8px !important;
        margin-top: 4px !important;
    }

    .header-role-badge {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: 3px 9px;
        border-radius: 6px;
        background: #0f172a !important;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        line-height: 1.25;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
    }

    .header-division-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        line-height: 1.25;
        white-space: nowrap;
    }

    /* --- SEARCH BAR --- */
    .modern-search-box {
        width: 240px;
        transition: width 0.2s ease;
    }

    .modern-search-input {
        height: 36px;
        border-radius: 8px !important;
        border: 1px solid var(--slate-200) !important;
        background: var(--slate-50) !important;
        font-size: 12.5px !important;
        color: var(--slate-900) !important;
        padding: 0 48px 0 32px !important;
        transition: all 0.15s ease !important;
    }

    .modern-search-input:focus {
        background: #ffffff !important;
        border-color: var(--primary-blue) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08) !important;
        outline: none !important;
        width: 280px;
    }

    .modern-search-box .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-400);
        font-size: 16px;
        pointer-events: none;
    }

    .search-kbd-chip {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        font-weight: 600;
        color: var(--slate-500);
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: 4px;
        padding: 2px 4px;
        line-height: 1;
        pointer-events: none;
        font-family: inherit;
    }

    .modern-search-input:focus ~ .search-kbd-chip {
        opacity: 0.4;
    }

    .search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        width: 100%;
        min-width: 280px;
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: 10px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        max-height: 360px;
        overflow-y: auto;
        display: none;
        z-index: 1050;
    }

    .search-results.show {
        display: block;
    }

    .modern-search-item {
        transition: background 0.15s ease;
        text-decoration: none;
    }

    .modern-search-item:hover {
        background: var(--slate-50);
    }

    .search-item-icon-box {
        width: 32px;
        height: 32px;
        background: var(--slate-100);
        flex-shrink: 0;
    }

    /* --- MOBILE SEARCH OVERLAY & RESULTS --- */
    .mobile-search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        max-height: 85vh;
        background: #ffffff;
        z-index: 1070;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
        border-bottom: 1px solid var(--slate-200);
        display: flex;
        flex-direction: column;
        transform: translateY(-105%);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease, visibility 0.2s ease;
        opacity: 0;
        visibility: hidden;
    }

    .mobile-search-overlay.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }

    .mobile-search-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 1065;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .mobile-search-backdrop.active {
        opacity: 1;
        visibility: visible;
    }

    .mobile-search-header {
        height: 56px;
        min-height: 56px;
    }

    .mobile-search-input {
        height: 38px !important;
        border-radius: 8px !important;
        border: 1px solid var(--slate-200) !important;
        background: var(--slate-50) !important;
        font-size: 13px !important;
        color: var(--slate-900) !important;
        padding-left: 36px !important;
        padding-right: 36px !important;
        transition: all 0.15s ease !important;
    }

    .mobile-search-input:focus {
        background: #ffffff !important;
        border-color: var(--primary-blue) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08) !important;
        outline: none !important;
    }

    .mobile-search-icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-400);
        font-size: 18px;
        pointer-events: none;
        line-height: 1;
    }

    .mobile-search-clear-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 22px;
        height: 22px;
        border: none;
        background: var(--slate-200);
        color: var(--slate-600);
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        transition: all 0.15s ease;
    }

    .mobile-search-clear-btn:hover {
        background: var(--slate-300);
        color: var(--slate-900);
    }

    .mobile-search-body {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        max-height: calc(85vh - 56px);
        background: #ffffff;
    }

    .mobile-search-result-item {
        padding: 11px 14px;
        border-bottom: 1px solid var(--slate-100);
        text-decoration: none !important;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: background 0.15s ease;
    }

    .mobile-search-result-item:hover,
    .mobile-search-result-item:active {
        background: var(--slate-50);
    }

    .mobile-search-icon-box {
        width: 36px;
        height: 36px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 19px;
    }

    /* --- ACTION BUTTONS (FULLSCREEN, BROADCAST, CHAT) --- */
    .modern-action-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--slate-200);
        background: #ffffff;
        color: var(--slate-600);
        padding: 0;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .modern-action-btn:hover,
    .modern-action-btn:focus {
        background: var(--slate-50);
        color: var(--slate-900);
        border-color: var(--slate-300);
    }

    .modern-action-btn:active {
        background: var(--slate-100);
        transform: scale(0.97);
    }

    .modern-action-btn i {
        font-size: 19px;
        line-height: 1;
    }

    .modern-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        font-size: 9.5px;
        font-weight: 700;
        height: 17px;
        min-width: 17px;
        padding: 0 4px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        line-height: 1;
    }

    .modern-badge-danger {
        background: var(--danger-red);
        color: #ffffff;
    }

    /* Divider */
    .header-divider {
        width: 1px;
        height: 24px;
        background: var(--slate-200);
        margin: 0 4px;
    }

    /* --- USER PILL BUTTON --- */
    .user-pill-btn {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px 3px 3px;
        border-radius: 9999px;
        border: 1px solid var(--slate-200);
        background: #ffffff;
        color: var(--slate-900);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .user-pill-btn:hover {
        background: var(--slate-50);
        border-color: var(--slate-300);
    }

    .user-avatar-wrap {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        max-width: 36px !important;
        position: relative !important;
        overflow: visible !important;
        flex-shrink: 0 !important;
    }

    .user-avatar-circle {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        overflow: hidden !important;
        border: 1.5px solid var(--slate-200);
        position: relative;
    }

    .user-avatar-img,
    img.user-avatar-img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }

    .user-avatar-initials {
        width: 100% !important;
        height: 100% !important;
        background: var(--slate-800);
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-verified-badge {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 15px;
        height: 15px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        font-size: 13px;
        line-height: 1;
        z-index: 5;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    }

    .user-pill-text {
        line-height: 1.2;
    }

    .user-pill-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--slate-900);
    }

    .user-pill-role {
        font-size: 10px;
        font-weight: 700;
        color: var(--primary-blue);
        letter-spacing: 0.04em;
    }

    .user-pill-chevron {
        font-size: 14px;
        color: var(--slate-400);
        margin-left: 2px;
    }

    /* --- DROPDOWN MENUS --- */
    .modern-dropdown-pane {
        background: #ffffff !important;
        border: 1px solid var(--slate-200) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04) !important;
        margin-top: 6px !important;
        overflow: hidden;
    }

    .broadcast-dropdown-pane {
        width: 360px;
        max-width: calc(100vw - 24px);
    }

    .chat-dropdown-pane {
        width: 380px;
        max-width: calc(100vw - 24px);
        height: 480px;
    }

    .user-dropdown-pane {
        width: 290px;
    }

    .user-header-avatar-wrap {
        width: 46px !important;
        height: 46px !important;
        min-width: 46px !important;
        max-width: 46px !important;
        position: relative !important;
        overflow: visible !important;
        flex-shrink: 0 !important;
    }

    .user-header-avatar-circle {
        width: 46px !important;
        height: 46px !important;
        border-radius: 50% !important;
        overflow: hidden !important;
        border: 2px solid #ffffff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.12);
        position: relative;
    }

    .user-header-avatar,
    img.user-header-avatar {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }

    .user-header-initials {
        width: 100% !important;
        height: 100% !important;
        background: var(--slate-800);
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-header-verified {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 18px;
        height: 18px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        font-size: 15px;
        line-height: 1;
        z-index: 5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
    }

    .user-dropdown-badges {
        gap: 8px !important;
        row-gap: 6px !important;
    }

    .dropdown-role-badge {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: 3px 9px;
        border-radius: 6px;
        background: #0f172a !important;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        line-height: 1.3;
        white-space: nowrap;
    }

    .dropdown-division-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        line-height: 1.3;
        white-space: nowrap;
    }

    .modern-dropdown-item {
        transition: background 0.15s ease;
        padding: 8px 14px !important;
        text-decoration: none;
    }

    .modern-dropdown-item:hover {
        background: var(--slate-50) !important;
    }

    .dropdown-item-danger:hover {
        background: #fef2f2 !important;
    }

    /* --- BROADCAST ITEM STYLING --- */
    .broadcast-item {
        border-left: 3px solid transparent;
        padding: 10px 14px !important;
        transition: background 0.15s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
        border-bottom: 1px solid var(--slate-100);
    }

    .broadcast-item:hover {
        background: var(--slate-50) !important;
    }

    .broadcast-item.unread {
        background: var(--slate-50);
        border-left-color: var(--primary-blue);
    }

    .broadcast-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .broadcast-icon.text-danger {
        background: #fef2f2;
        color: var(--danger-red) !important;
    }

    .broadcast-icon.text-warning {
        background: #fffbeb;
        color: #d97706 !important;
    }

    .broadcast-icon.text-info {
        background: #eff6ff;
        color: var(--primary-blue) !important;
    }

    .broadcast-title {
        font-size: 12.5px;
        color: var(--slate-900);
        font-weight: 600;
    }

    .broadcast-message {
        font-size: 11.5px;
        color: var(--slate-500);
        margin: 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .broadcast-read-more {
        color: var(--primary-blue);
        font-size: 11px;
        font-weight: 600;
    }

    .broadcast-time {
        color: var(--slate-400);
        font-size: 10.5px;
    }

    .unread-dot {
        width: 7px;
        height: 7px;
        background: var(--primary-blue);
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    /* --- CHAT STYLING --- */
    .branch-item {
        padding: 10px 14px;
        border-bottom: 1px solid var(--slate-100);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .branch-item:hover {
        background: var(--slate-50);
    }

    .branch-avatar {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: var(--slate-100);
        color: var(--primary-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        border: 1px solid var(--slate-200);
    }

    .btn-icon-ghost {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid transparent;
        background: transparent;
        color: var(--slate-600);
    }

    .btn-icon-ghost:hover {
        background: var(--slate-100);
        color: var(--slate-900);
    }

    .modern-chat-input {
        border-radius: 9999px !important;
        border: 1px solid var(--slate-200) !important;
        background: var(--slate-50) !important;
        font-size: 12px !important;
        padding-left: 14px !important;
        padding-right: 14px !important;
        height: 34px !important;
    }

    .modern-chat-input:focus {
        background: #ffffff !important;
        border-color: var(--primary-blue) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08) !important;
    }

    .modern-chat-send-btn {
        width: 34px !important;
        height: 34px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: var(--primary-blue) !important;
        border-color: var(--primary-blue) !important;
        color: #ffffff !important;
        flex-shrink: 0;
    }

    .modern-chat-send-btn:hover {
        background: var(--primary-blue-hover) !important;
        border-color: var(--primary-blue-hover) !important;
    }

    .chat-bubble-me {
        background: var(--primary-blue) !important;
        color: #ffffff !important;
        border-radius: 12px 12px 3px 12px !important;
        padding: 7px 11px;
        font-size: 12.5px;
        line-height: 1.4;
    }

    .chat-bubble-other {
        background: #ffffff !important;
        color: var(--slate-900) !important;
        border: 1px solid var(--slate-200);
        border-radius: 12px 12px 12px 3px !important;
        padding: 7px 11px;
        font-size: 12.5px;
        line-height: 1.4;
    }

    .chat-time-label {
        font-size: 10px;
    }

    /* Scrollbars */
    .custom-dropdown-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .custom-dropdown-scroll::-webkit-scrollbar-track {
        background: var(--slate-50);
    }

    .custom-dropdown-scroll::-webkit-scrollbar-thumb {
        background: var(--slate-300);
        border-radius: 9999px;
    }

    .custom-dropdown-scroll::-webkit-scrollbar-thumb:hover {
        background: var(--slate-400);
    }
</style>