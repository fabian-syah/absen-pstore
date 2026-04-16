@php
    use Illuminate\Support\Facades\Storage;
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row w-100">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo"
                    style="width: 150px; height: auto;" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 45px; height: auto;" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text">@yield('heading')</h1>
                <h3 class="welcome-sub-text">{{ Auth::user()->role }} - {{ Auth::user()->division->name ?? 'N/A' }}</h3>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            {{-- Fullscreen Button --}}
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link" href="javascript:void(0)" onclick="toggleFullScreen()">
                    <i class="mdi mdi-fullscreen"></i> Fullscreen
                </a>
            </li>

            {{-- Search - Untuk Admin, Audit, LEADER, dan ADMIN GAJI --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'admin_gaji']))
                <li class="nav-item">
                    <div class="search-form position-relative">
                        <i class="icon-search position-absolute search-icon"></i>
                        <input type="search" class="form-control search-input" id="globalSearch"
                            data-url="{{ route('search') }}" placeholder="Cari user..." autocomplete="off">
                        <div class="search-results dropdown-menu" id="searchResults"></div>
                    </div>
                </li>
            @endif

            {{-- Broadcast Notifications --}}
            <li class="nav-item dropdown notification-dropdown">
                <a class="nav-link position-relative d-flex align-items-center justify-content-center"
                    id="broadcastDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell notification-icon"></i>
                    <span class="notification-badge" id="broadcastCount" style="display: none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                    aria-labelledby="broadcastDropdown" style="min-width: 380px; max-width: 400px;">
                    <div class="dropdown-header px-4 py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-semibold">Broadcast Notifications</h6>
                                <small class="text-muted" id="broadcastTotal">0 unread</small>
                            </div>
                            <i class="mdi mdi-bullhorn text-primary" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <div id="broadcastList" style="max-height: 400px; overflow-y: auto;">
                        <div class="dropdown-item text-center py-5">
                            <div class="spinner-border text-primary mb-2" role="status"
                                style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0">Loading broadcasts...</p>
                        </div>
                    </div>
                    <div class="dropdown-divider m-0"></div>
                    <a href="javascript:void(0)" class="dropdown-item text-center py-3 text-primary fw-medium"
                        id="viewAllBroadcasts">
                        <i class="mdi mdi-bullhorn-outline me-1"></i>View All Broadcasts
                    </a>
                </div>
            </li>

            {{-- FITUR CHAT MULTI-BRANCH (TEKS & FOTO) --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon-mail icon-lg"></i>
                    {{-- Dot Merah Utama (Total Unread dari semua cabang) --}}
                    <span class="notification-badge bg-danger" id="mainChatBadge"
                        style="display: none; top: 5px; right: 5px;">0</span>
                </a>

                {{-- Dropdown Container --}}
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list p-0"
                    aria-labelledby="messageDropdown" style="width: 380px; min-width: 380px; height: 500px;">

                    {{-- Wrapper untuk layout --}}
                    <div class="d-flex flex-column h-100 w-100">

                        {{-- =========================== --}}
                        {{-- VIEW 1: DAFTAR CABANG --}}
                        {{-- =========================== --}}
                        <div id="branchListView" class="d-flex flex-column h-100 w-100">
                            <div class="p-3 border-bottom bg-primary text-white">
                                <h6 class="mb-0 fw-bold"><i class="mdi mdi-forum-outline me-2"></i>Pilih Grup Cabang
                                </h6>
                            </div>

                            {{-- Area ini akan SCROLLABLE jika cabangnya banyak --}}
                            <div id="branchListBody" class="flex-grow-1" style="overflow-y: auto; background: #fff;">
                                <div class="text-center text-muted mt-5 pt-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <p class="small mt-2">Memuat daftar cabang...</p>
                                </div>
                            </div>
                        </div>

                        {{-- =========================== --}}
                        {{-- VIEW 2: ROOM CHAT --}}
                        {{-- =========================== --}}
                        <div id="chatRoomView" class="d-none flex-column h-100 w-100">
                            {{-- Header Chat Room --}}
                            <div
                                class="p-3 border-bottom d-flex align-items-center justify-content-between bg-primary text-white">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" id="backToBranchList"
                                        class="btn btn-sm btn-outline-light border-0 p-1 me-1">
                                        <i class="mdi mdi-arrow-left fs-6"></i>
                                    </button>
                                    <div>
                                        <h6 class="mb-0 fw-bold" id="activeBranchName">Loading...</h6>
                                        <small style="font-size: 10px; opacity: 0.9;" id="activeBranchTimezone">
                                            <i class="mdi mdi-clock-outline me-1"></i>Asia/Jakarta
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Body Chat --}}
                            <div id="chatBody" class="p-3 flex-grow-1" style="overflow-y: auto; background: #eef1f6;">
                                {{-- Pesan akan dirender disini via JS --}}
                            </div>

                            {{-- Footer Input --}}
                            <div class="p-2 border-top bg-white">
                                {{-- Preview File --}}
                                <div id="filePreviewArea" class="px-2 pb-2 d-none">
                                    <div
                                        class="d-inline-flex align-items-center bg-light border rounded-pill px-3 py-1">
                                        <i class="mdi mdi-image text-success me-2"></i>
                                        <span id="fileNamePreview" class="small text-muted"
                                            style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">img.jpg</span>
                                        <button type="button" id="cancelFileBtn"
                                            class="btn btn-sm text-danger ms-2 p-0"><i
                                                class="mdi mdi-close"></i></button>
                                    </div>
                                </div>

                                <form id="chatForm" class="d-flex align-items-center gap-2"
                                    enctype="multipart/form-data">
                                    {{-- Hidden Inputs --}}
                                    <input type="hidden" id="activeBranchId" name="branch_id">
                                    <input type="file" id="chatImageInput" name="image" accept="image/*" class="d-none">

                                    {{-- Buttons --}}
                                    <button type="button" id="triggerFileBtn"
                                        class="btn btn-light btn-sm rounded-circle border p-2" title="Kirim Foto">
                                        <i class="mdi mdi-paperclip text-muted" style="font-size: 16px;"></i>
                                    </button>

                                    <input type="text" id="chatInput" name="message"
                                        class="form-control form-control-sm border bg-light"
                                        placeholder="Ketik pesan..." autocomplete="off" style="border-radius: 20px;">

                                    <button type="submit" class="btn btn-primary btn-sm rounded-circle p-2 shadow-sm"
                                        style="width: 36px; height: 36px;">
                                        <i class="mdi mdi-send" style="font-size: 16px;"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </li>

            {{-- User Profile --}}
            {{-- Rank & XP Global Display --}}
            <li class="nav-item d-none d-sm-flex align-items-center me-3">
                @php 
                    $rankData = Auth::user()->calculateRank(); 
                    $progress = Auth::user()->getRankProgress();
                    $isDarkText = in_array($rankData['level'], [5, 7, 8, 12, 14, 16, 19]);
                @endphp
                <div class="rank-header-card d-flex align-items-center px-3 py-1 rounded-pill" 
                     style="background: #f8f9fa; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                    <div class="rank-icon-circle me-2 d-flex align-items-center justify-content-center shadow-sm {{ $rankData['effect_class'] }}" 
                         style="width: 28px; height: 28px; background: {{ $rankData['color'] }}; border-radius: 50%; color: {{ $isDarkText ? '#000' : '#fff' }}; font-size: 14px; border: 2px solid #fff;">
                        <i class="mdi {{ $rankData['icon'] }}"></i>
                    </div>
                    <div class="rank-info-mini d-flex flex-column" style="min-width: 80px;">
                        <div class="d-flex justify-content-between align-items-center line-height-1">
                            <span class="fw-bold text-dark mb-0" style="font-size: 11px;">{{ Auth::user()->rank_title }}</span>
                            <span class="text-muted fw-bold" style="font-size: 9px; opacity: 0.8;">{{ number_format(Auth::user()->xp) }} XP</span>
                        </div>
                        <div class="progress mt-1" style="height: 4px; background-color: #e9ecef; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: {{ $progress }}%; background-color: {{ $rankData['color'] }};"></div>
                        </div>
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link p-0" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="position-relative d-inline-block">
                        @if (Auth::user()->profile_photo_path)
                            <img class="img-xs rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                alt="Profile image"
                                style="object-fit: cover; border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }}; padding: 1px;">
                        @else
                            <div class="profile-initial-nav"
                                style="border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }};">
                                {{ getInitials(Auth::user()->name) }}
                            </div>
                        @endif

                        @if(Auth::user()->is_verified)
                            <span
                                class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                style="bottom: -2px; right: -2px; width: 14px; height: 14px; border: 1px solid white;">
                                <i class="mdi mdi-check-decagram text-primary" style="font-size: 10px;"></i>
                            </span>
                        @endif
                    </div>
                </a>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <div class="position-relative d-inline-block mb-2">
                            @if (Auth::user()->profile_photo_path)
                                <img class="img-md rounded-circle"
                                    src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image"
                                    style="width: 60px; height: 60px; object-fit: cover; border: {{ Auth::user()->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                            @else
                                <div class="profile-initial-dropdown"
                                    style="border: {{ Auth::user()->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                    {{ getInitials(Auth::user()->name) }}
                                </div>
                            @endif

                            @if(Auth::user()->is_verified)
                                <span
                                    class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                    style="bottom: 0; right: 0; width: 20px; height: 20px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <i class="mdi mdi-check-decagram text-primary" style="font-size: 14px;"></i>
                                </span>
                            @endif
                        </div>

                        <p class="mb-1 mt-1 fw-semibold d-flex align-items-center justify-content-center gap-1">
                            {{ Auth::user()->name }}
                            @if(Auth::user()->is_verified)
                                <i class="mdi mdi-check-decagram text-primary" title="Verified"
                                    style="font-size: 14px;"></i>
                            @endif
                        </p>
                        <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                        <small class="text-muted d-block">{{ Auth::user()->role }} - {{ Auth::user()->division->name ?? 'N/A' }}</small>
                        <div class="mt-2 text-center">
                            @php $rank = Auth::user()->calculateRank(); @endphp
                            <span class="badge shadow-sm {{ $rank['effect_class'] }}" 
                                  style="background-color: {{ $rank['color'] }}; color: {{ in_array($rank['level'], [5, 7, 8, 12, 14, 16, 19]) ? '#000' : '#fff' }}; font-size: 10px; font-weight: 800; border: 1px solid #fff; padding: 5px 10px;">
                                <i class="mdi {{ $rank['icon'] }} me-1"></i> {{ Auth::user()->rank_title }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile
                    </a>
                    <a class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages
                    </a>
                    <a class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> FAQ
                    </a>

                    <div class="dropdown-divider"></div>

                    <a href="{{ route('logout') }}" class="dropdown-item"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ==========================================
        // 1. GLOBAL SEARCH LOGIC
        // ==========================================
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout = null;

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
                            searchResults.innerHTML = '<div class="dropdown-item text-danger">Error loading results</div>';
                            searchResults.classList.add('show');
                        });
                }, 500);
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
            if (!results || results.length === 0) {
                searchResults.innerHTML = '<div class="dropdown-item text-muted py-3 text-center">No results found</div>';
            } else {
                let html = '';
                results.forEach(item => {
                    html += `
                        <a href="${item.url}" class="dropdown-item py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="mdi ${item.icon} text-primary" style="font-size: 20px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-dark" style="font-size: 14px; font-weight: 600;">${escapeHtml(item.title)}</h6>
                                    <small class="text-muted" style="font-size: 12px; white-space: normal;">${escapeHtml(item.description)}</small>
                                </div>
                            </div>
                        </a>
                    `;
                });
                searchResults.innerHTML = html;
            }
            searchResults.classList.add('show');
        }

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
                broadcastCount.style.display = 'flex';
                broadcastTotal.textContent = unreadCount + ' unread';
            } else {
                broadcastCount.style.display = 'none';
                broadcastTotal.textContent = 'No unread';
            }

            if (broadcasts.length === 0) {
                broadcastList.innerHTML = `
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="mdi mdi-bullhorn-outline"></i>
                    </div>
                    <h6 class="text-muted mb-1">No Broadcasts</h6>
                    <p class="text-muted small mb-0">You're all caught up!</p>
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
                    <a class="dropdown-item broadcast-item py-3 ${broadcast.is_read ? '' : 'unread'}" href="${detailUrl}">
                        <div class="d-flex align-items-start">
                            <div class="broadcast-icon me-3 ${broadcast.priority_color}">
                                <i class="${broadcast.priority_icon}"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="broadcast-title mb-0 fw-semibold">${escapeHtml(broadcast.title)}</h6>
                                    ${broadcast.is_read ? '' : '<span class="unread-dot"></span>'}
                                </div>
                                <p class="broadcast-message text-muted mb-2">${escapeHtml(shortMessage)}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="broadcast-read-more">
                                        Read more <i class="mdi mdi-arrow-right"></i>
                                    </span>
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
                <div class="empty-icon mb-3 text-danger">
                    <i class="mdi mdi-alert-circle-outline"></i>
                </div>
                <h6 class="text-danger mb-1">Failed to Load</h6>
                <p class="text-muted small mb-0">Please try again later</p>
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

        loadBroadcastNotifications();
        setInterval(loadBroadcastNotifications, 30000);
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
        let currentView = 'list'; // 'list' or 'room'
        let currentBranchId = null;
        let branchInterval = null;
        let messageInterval = null;

        // --- HANDLER DROPDOWN ---
        if (messageDropdown) {
            messageDropdown.addEventListener('show.bs.dropdown', function () {
                isDropdownOpen = true;
                if (currentView === 'list') {
                    loadBranchList();
                    branchInterval = setInterval(loadBranchList, 5000); // Polling list cabang
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

        // Prevent Close on Click Inside
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
            if (branches.length === 0) {
                branchListBody.innerHTML = '<div class="text-center text-muted mt-5 pt-3"><i class="mdi mdi-office-building-remove fs-1"></i><p class="small">Anda tidak terhubung ke cabang manapun.</p></div>';
                return;
            }

            let html = '';
            branches.forEach(branch => {
                // Badge Unread
                let badgeHtml = '';
                if (branch.unread_count > 0) {
                    badgeHtml = `<span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px;">${branch.unread_count}</span>`;
                }

                html += `
                    <div class="p-3 border-bottom d-flex align-items-center branch-item" 
                         onclick="openChatRoom(${branch.id}, '${escapeHtml(branch.name)}', '${branch.timezone}')"
                         style="cursor: pointer; transition: background 0.2s;">
                        
                        <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-office-building"></i>
                        </div>
                        
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-dark fw-bold text-truncate" style="max-width: 180px; font-size: 14px;">${escapeHtml(branch.name)}</h6>
                                ${badgeHtml}
                            </div>
                            <small class="text-muted text-truncate d-block" style="font-size: 12px;">${escapeHtml(branch.last_message)}</small>
                        </div>
                    </div>
                `;
            });
            branchListBody.innerHTML = html;
        }

        function updateMainBadge(count) {
            if (count > 0) {
                mainChatBadge.textContent = count > 99 ? '99+' : count;
                mainChatBadge.style.display = 'flex';
            } else {
                mainChatBadge.style.display = 'none';
            }
        }

        // --- 2. ROOM CHAT LOGIC ---

        // Fungsi global agar bisa dipanggil dari HTML onclick
        window.openChatRoom = function (branchId, branchName, timezone) {
            // Switch View
            currentView = 'room';
            currentBranchId = branchId;

            // UI Updates
            branchListView.classList.remove('d-flex');
            branchListView.classList.add('d-none');

            chatRoomView.classList.remove('d-none');
            chatRoomView.classList.add('d-flex');

            // Set Header Info
            activeBranchName.textContent = branchName;
            activeBranchTimezone.textContent = timezone;
            activeBranchId.value = branchId;

            // Clear Old Chat & Load New
            chatBody.innerHTML = '<div class="text-center text-muted mt-5 pt-5"><div class="spinner-border spinner-border-sm text-primary"></div><p class="small mt-2">Memuat pesan...</p></div>';

            // Stop List Interval, Start Message Interval
            clearInterval(branchInterval);
            loadMessages(branchId);
            messageInterval = setInterval(() => loadMessages(branchId), 3000);
        };

        // Back Button Logic
        if (backToBranchList) {
            backToBranchList.addEventListener('click', function () {
                // Switch View back to List
                currentView = 'list';
                currentBranchId = null;

                chatRoomView.classList.remove('d-flex');
                chatRoomView.classList.add('d-none');

                branchListView.classList.remove('d-none');
                branchListView.classList.add('d-flex');

                // Stop Message Interval, Start List Interval
                clearInterval(messageInterval);
                loadBranchList(); // Immediate refresh to update unread counts
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
            if (messages.length === 0) {
                chatBody.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small"><i class="mdi mdi-chat-processing-outline fs-1 mb-2"></i><p>Belum ada obrolan di cabang ini.</p></div>';
                return;
            }

            let html = '';
            messages.forEach(msg => {
                let imageHtml = '';
                if (msg.image_url) {
                    imageHtml = `
                        <div class="mb-1">
                            <a href="${msg.image_url}" target="_blank">
                                <img src="${msg.image_url}" class="rounded border shadow-sm" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                            </a>
                        </div>
                    `;
                }
                let textHtml = msg.message ? `<div>${escapeHtml(msg.message)}</div>` : '';

                if (msg.is_me) {
                    // PESAN SENDIRI
                    html += `
                        <div class="d-flex justify-content-end mb-3">
                            <div class="text-end" style="max-width: 85%;">
                                <div class="bg-primary text-white px-3 py-2 rounded-3 shadow-sm text-start d-inline-block" style="border-bottom-right-radius: 4px !important;">
                                    ${imageHtml}
                                    ${textHtml}
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 10px;">${msg.time}</div>
                            </div>
                        </div>
                    `;
                } else {
                    // PESAN ORANG LAIN
                    html += `
                        <div class="d-flex justify-content-start mb-3">
                            <div class="me-2 mt-1">
                                ${msg.user_avatar
                            ? `<img src="/storage/${msg.user_avatar}" class="rounded-circle border" style="width: 28px; height: 28px; object-fit: cover;">`
                            : `<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 10px;">${msg.user_name.charAt(0)}</div>`
                        }
                            </div>
                            <div style="max-width: 85%;">
                                <small class="d-block text-dark fw-bold mb-1" style="font-size: 11px;">${msg.user_name}</small>
                                <div class="bg-white text-dark px-3 py-2 rounded-3 shadow-sm border d-inline-block" style="border-top-left-radius: 4px !important;">
                                    ${imageHtml}
                                    ${textHtml}
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 10px;">${msg.time}</div>
                            </div>
                        </div>
                    `;
                }
            });

            // Auto scroll logic (simple)
            // Cek apakah user sedang scroll ke atas
            const isScrolledBottom = (chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop) < 150;

            chatBody.innerHTML = html;

            // Scroll ke bawah jika di posisi bawah atau chat baru dibuka
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
                    chatInput.placeholder = "Tambahkan caption...";
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

                // Validasi Client
                if (!formData.get('message').trim() && (!formData.get('image') || formData.get('image').size === 0)) return;

                // Optimistic Clear
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
                        alert("Gagal mengirim.");
                    });
            });
        }

        // --- 4. UTILITIES ---
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

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;

            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        // --- 5. AUTO LOAD BADGE ON PAGE LOAD ---
        // Panggil sekali saat load halaman agar badge merah di navbar muncul
        fetch('{{ route('chat.branches') }}')
            .then(res => res.json())
            .then(data => updateMainBadge(data.total_unread))
            .catch(e => { });
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

<style>
    /* =======================================================
       MODERN HEADER NAVIGATION STYLING
       ======================================================= */
    
    /* Navbar Base */
    .navbar {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08) !important;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(13, 110, 253, 0.1);
        transition: all 0.3s ease;
    }

    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    /* Welcome Text */
    .welcome-text {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700 !important;
        font-size: 1.5rem !important;
        margin: 0 !important;
    }

    .welcome-sub-text {
        color: #6c757d !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        margin: 0.25rem 0 0 0 !important;
        text-transform: capitalize;
    }

    /* --- MODERN SEARCH STYLING --- */
    .search-form { 
        position: relative; 
        margin-right: 20px; 
    }
    
    .search-icon { 
        left: 16px; 
        top: 50%; 
        transform: translateY(-50%); 
        color: #6c757d; 
        z-index: 10; 
        pointer-events: none;
        transition: color 0.3s ease;
    }
    
    .search-input { 
        border-radius: 20px; 
        border: 2px solid transparent;
        padding: 10px 16px 10px 42px; 
        background: #f8f9fa; 
        width: 300px; 
        height: 42px; 
        font-size: 14px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .search-input:focus { 
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1), 0 4px 12px rgba(0, 0, 0, 0.08);
        background: white; 
        outline: none;
        transform: translateY(-1px);
    }

    .search-input:focus + .search-icon {
        color: #0d6efd;
    }
    
    .search-results { 
        position: absolute; 
        top: calc(100% + 8px); 
        left: 0; 
        right: 0; 
        z-index: 1050; 
        background: white; 
        border: none;
        border-radius: 12px; 
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12); 
        max-height: 400px; 
        overflow-y: auto; 
        display: none;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .search-results.show { display: block; }
    
    .search-results .dropdown-item { 
        padding: 14px 18px; 
        border-bottom: 1px solid #f1f3f5; 
        white-space: normal;
        transition: all 0.2s ease;
    }
    
    .search-results .dropdown-item:last-child { border-bottom: none; }
    
    .search-results .dropdown-item:hover { 
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.03) 100%);
        transform: translateX(4px);
    }

    /* --- MODERN NOTIFICATION STYLING --- */
    .notification-dropdown .nav-link { 
        width: 44px; 
        height: 44px; 
        border-radius: 12px;
        background: #f8f9fa; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-dropdown .nav-link:hover { 
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }
    
    .notification-icon { 
        font-size: 22px; 
        color: #495057;
        transition: color 0.3s ease;
    }

    .notification-dropdown .nav-link:hover .notification-icon {
        color: #0d6efd;
    }
    
    .notification-badge { 
        position: absolute; 
        top: -6px; 
        right: -6px; 
        background: linear-gradient(135deg, #ff4757 0%, #dc3545 100%); 
        color: white; 
        border-radius: 10px; 
        padding: 3px 7px; 
        font-size: 10px; 
        font-weight: 700; 
        min-width: 20px; 
        height: 20px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4); 
        border: 2px solid white; 
        animation: badge-pulse 2s ease-in-out infinite;
    }
    
    @keyframes badge-pulse { 
        0%, 100% { transform: scale(1); opacity: 1; } 
        50% { transform: scale(1.15); opacity: 0.9; } 
    }
    
    /* Dropdown Styling */
    .navbar-dropdown {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12) !important;
        overflow: hidden;
        animation: slideDown 0.3s ease-out;
    }

    .dropdown-header { 
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
        color: white !important;
        padding: 1.25rem 1.5rem !important;
    }
    
    .dropdown-header h6 { 
        color: white !important;
        font-weight: 700 !important;
        margin: 0 !important;
    }
    
    .dropdown-header small { 
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .dropdown-header .mdi { 
        color: white !important;
        opacity: 0.95;
    }
    
    /* Broadcast Items */
    .broadcast-item { 
        border-left: 3px solid transparent; 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    
    .broadcast-item:hover { 
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.06) 0%, transparent 100%);
        border-left-color: #0d6efd;
        transform: translateX(2px);
    }
    
    .broadcast-item.unread { 
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.02) 100%);
        border-left-color: #0d6efd;
    }
    
    .broadcast-icon { 
        width: 42px; 
        height: 42px; 
        border-radius: 10px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 20px; 
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .broadcast-item:hover .broadcast-icon {
        transform: scale(1.1);
    }
    
    .broadcast-icon.text-danger { 
        background: linear-gradient(135deg, #ffebee 0%, #ffe0e3 100%);
        color: #dc3545;
    }
    
    .broadcast-icon.text-warning { 
        background: linear-gradient(135deg, #fff3e0 0%, #ffe8cc 100%);
        color: #ff9800;
    }
    
    .broadcast-icon.text-info { 
        background: linear-gradient(135deg, #e3f2fd 0%, #d1e7fc 100%);
        color: #2196f3;
    }
    
    .broadcast-title { 
        font-size: 14px; 
        color: #212529; 
        line-height: 1.5;
        font-weight: 600;
    }
    
    .broadcast-message { 
        font-size: 13px; 
        line-height: 1.5;
        margin: 0; 
        display: -webkit-box; 
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical; 
        overflow: hidden;
        color: #6c757d;
    }
    
    .broadcast-read-more { 
        color: #0d6efd;
        font-size: 12px; 
        font-weight: 600; 
        transition: all 0.2s ease;
    }
    
    .broadcast-item:hover .broadcast-read-more { 
        color: #0a58ca;
        text-decoration: underline;
    }
    
    .broadcast-time { 
        color: #6c757d; 
        font-size: 11px; 
        white-space: nowrap;
    }
    
    .unread-dot { 
        width: 10px; 
        height: 10px; 
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border-radius: 50%; 
        display: inline-block; 
        margin-left: 8px; 
        flex-shrink: 0;
        box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
        animation: pulse-dot 2s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    /* Empty States */
    .empty-state { 
        padding: 48px 24px;
        text-align: center;
    }
    
    .empty-icon { 
        font-size: 72px; 
        color: #dee2e6; 
        line-height: 1;
        margin-bottom: 16px;
    }
    
    .empty-icon.text-danger { color: #dc3545; }
    
    .empty-state h6 { 
        font-size: 16px; 
        margin-bottom: 8px;
        font-weight: 600;
        color: #495057;
    }
    
    .empty-state p { 
        font-size: 13px;
        color: #6c757d;
    }
    
    /* Scrollbar for Dropdowns */
    #broadcastList::-webkit-scrollbar { width: 6px; }
    #broadcastList::-webkit-scrollbar-track { background: #f8f9fa; }
    #broadcastList::-webkit-scrollbar-thumb { 
        background: linear-gradient(180deg, #0d6efd, #0a58ca);
        border-radius: 3px;
    }
    #broadcastList::-webkit-scrollbar-thumb:hover { 
        background: linear-gradient(180deg, #0a58ca, #084298);
    }

    /* --- MODERN PROFILE STYLING --- */
    .user-dropdown .nav-link {
        transition: all 0.3s ease;
        padding: 4px !important;
        border-radius: 50%;
    }

    .user-dropdown .nav-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }

    .img-xs {
        transition: transform 0.3s ease;
    }

    .profile-initial-nav { 
        width: 40px; 
        height: 40px; 
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 700;
        font-size: 14px; 
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
    }
    
    .profile-initial-nav:hover { 
        transform: scale(1.1);
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.4);
    }
    
    .profile-initial-dropdown { 
        width: 64px; 
        height: 64px; 
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 700;
        font-size: 20px; 
        margin: 0 auto; 
        border: 3px solid #fff; 
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.3);
    }

    .dropdown-item {
        transition: all 0.2s ease;
        padding: 0.75rem 1.5rem !important;
    }

    .dropdown-item:hover {
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.08) 0%, transparent 100%) !important;
        transform: translateX(4px);
    }

    .dropdown-item-icon {
        transition: transform 0.2s ease;
    }

    .dropdown-item:hover .dropdown-item-icon {
        transform: scale(1.1);
    }

    /* --- CHAT STYLING --- */
    .branch-item { 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 8px;
        margin: 4px 8px;
    }

    .branch-item:hover { 
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.06) 0%, rgba(13, 110, 253, 0.02) 100%);
        transform: translateX(4px);
    }

    /* Fullscreen Button */
    .nav-link {
        transition: all 0.2s ease;
    }

    .nav-link:hover {
        color: #0d6efd !important;
        transform: translateY(-1px);
    }

    /* Mobile Responsiveness */
    @media (max-width: 991px) {
        .search-form { 
            margin: 12px 0; 
            width: 100%;
        }
        
        .search-input { 
            width: 100%;
        }
        
        .notification-dropdown .dropdown-menu { 
            min-width: 340px !important;
            max-width: 90vw !important;
        }

        .welcome-text {
            font-size: 1.25rem !important;
        }

        .welcome-sub-text {
            font-size: 0.75rem !important;
        }
    }

    @media (max-width: 768px) {
        .notification-dropdown .dropdown-menu { 
            min-width: 320px !important;
        }

        .navbar {
            padding: 0.75rem 1rem !important;
        }
    }

    /* Smooth Transitions for All Interactive Elements */
    .navbar * {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>