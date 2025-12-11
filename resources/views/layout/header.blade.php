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
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo"
                    style="width: 45px; height: auto;" />
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

            {{-- Search - Untuk Admin, Audit, DAN LEADER --}}
            {{-- [UPDATE] Leader ditambahkan di sini --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
                <li class="nav-item">
                    <div class="search-form position-relative">
                        <i class="icon-search position-absolute search-icon"></i>
                        <input type="search" class="form-control search-input" id="globalSearch"
                            data-url="{{ route('search') }}" placeholder="Cari user..."
                            autocomplete="off">
                        <div class="search-results dropdown-menu" id="searchResults"></div>
                    </div>
                </li>
            @endif

            {{-- Broadcast Notifications --}}
            <li class="nav-item dropdown notification-dropdown">
                <a class="nav-link position-relative d-flex align-items-center justify-content-center" 
                   id="broadcastDropdown" 
                   href="#" 
                   data-bs-toggle="dropdown">
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
                            <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0">Loading broadcasts...</p>
                        </div>
                    </div>
                    <div class="dropdown-divider m-0"></div>
                    <a href="javascript:void(0)" class="dropdown-item text-center py-3 text-primary fw-medium" id="viewAllBroadcasts">
                        <i class="mdi mdi-bullhorn-outline me-1"></i>View All Broadcasts
                    </a>
                </div>
            </li>

            {{-- User Profile --}}
            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link p-0" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="position-relative d-inline-block">
                        @if (Auth::user()->profile_photo_path)
                            <img class="img-xs rounded-circle" 
                                 src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                 alt="Profile image"
                                 style="object-fit: cover; border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }}; padding: 1px;">
                        @else
                            <div class="profile-initial-nav" 
                                 style="border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }};">
                                {{ getInitials(Auth::user()->name) }}
                            </div>
                        @endif

                        @if(Auth::user()->is_verified)
                            <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
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
                                <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                      style="bottom: 0; right: 0; width: 20px; height: 20px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <i class="mdi mdi-check-decagram text-primary" style="font-size: 14px;"></i>
                                </span>
                            @endif
                        </div>

                        <p class="mb-1 mt-1 fw-semibold d-flex align-items-center justify-content-center gap-1">
                            {{ Auth::user()->name }}
                            @if(Auth::user()->is_verified)
                                <i class="mdi mdi-check-decagram text-primary" title="Verified" style="font-size: 14px;"></i>
                            @endif
                        </p>
                        <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                        <small class="text-muted">{{ Auth::user()->role }} -
                            {{ Auth::user()->division->name ?? 'N/A' }}</small>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // 1. GLOBAL SEARCH LOGIC (ADMIN, AUDIT, LEADER)
        // ==========================================
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout = null;

        if (searchInput && searchResults) {
            // Event Listener Typing
            searchInput.addEventListener('input', function() {
                const query = this.value;
                const url = this.getAttribute('data-url');

                // Clear debounce
                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    searchResults.classList.remove('show');
                    searchResults.innerHTML = '';
                    return;
                }

                // Debounce 500ms (tunggu user selesai ngetik)
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

            // Tampilkan hasil saat input di-klik (jika sudah ada isinya)
            searchInput.addEventListener('focus', function() {
                if (this.value.length >= 2 && searchResults.innerHTML !== '') {
                    searchResults.classList.add('show');
                }
            });

            // Sembunyikan jika klik di luar area search
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.remove('show');
                }
            });
        }

        // Render Search Results HTML
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

        // ==========================================
        // 3. UTILITY FUNCTIONS (Shared)
        // ==========================================
        
        // Mencegah XSS Injection
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

        // ==========================================
        // 4. INIT
        // ==========================================

        // View All Listener
        if(viewAllBroadcasts) {
            viewAllBroadcasts.addEventListener('click', function() {
                @if (auth()->user()->role == 'admin')
                    window.location.href = '{{ route('broadcast.index') }}';
                @else
                    showAllBroadcastsModal();
                @endif
            });
        }

        function showAllBroadcastsModal() {
            alert('Fitur View All untuk user biasa belum aktif.');
        }

        // Init Notification Load
        loadBroadcastNotifications();
        // Refresh tiap 30 detik
        setInterval(loadBroadcastNotifications, 30000);
        // Refresh saat dropdown dibuka
        if (broadcastDropdown) {
            broadcastDropdown.addEventListener('click', function() {
                loadBroadcastNotifications();
            });
        }
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
    /* --- CSS TAMBAHAN UNTUK SEARCH --- */
    .search-form {
        position: relative;
        margin-right: 15px;
    }

    .search-icon {
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
        pointer-events: none;
    }

    .search-input {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 8px 15px 8px 40px;
        background: #f8f9fa;
        width: 300px;
        height: 38px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
        outline: none;
    }

    .search-results {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        max-height: 400px;
        overflow-y: auto;
        display: none;
    }

    .search-results.show {
        display: block;
    }

    .search-results .dropdown-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        white-space: normal; /* Agar text panjang turun ke bawah */
    }

    .search-results .dropdown-item:last-child {
        border-bottom: none;
    }

    .search-results .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    /* --- CSS LAMA (BROADCAST DLL) --- */
    .notification-dropdown .nav-link {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #f8f9fa;
        transition: all 0.3s ease;
        position: relative;
    }

    .notification-dropdown .nav-link:hover {
        background: #e9ecef;
        transform: scale(1.05);
    }

    .notification-icon {
        font-size: 20px;
        color: #495057;
    }

    .notification-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: linear-gradient(135deg, #ff4757 0%, #dc3545 100%);
        color: white;
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);
        border: 2px solid white;
        animation: badge-pulse 2s ease-in-out infinite;
    }

    @keyframes badge-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .dropdown-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .dropdown-header h6 { color: white; }
    .dropdown-header small { color: rgba(255, 255, 255, 0.8); }
    .dropdown-header .mdi { color: white; opacity: 0.9; }

    .broadcast-item {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .broadcast-item:hover {
        background: #f8f9fa;
        border-left-color: #667eea;
    }

    .broadcast-item.unread {
        background: #f0f4ff;
        border-left-color: #667eea;
    }

    .broadcast-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .broadcast-icon.text-danger { background: #ffebee; color: #dc3545; }
    .broadcast-icon.text-warning { background: #fff3e0; color: #ff9800; }
    .broadcast-icon.text-info { background: #e3f2fd; color: #2196f3; }

    .broadcast-title { font-size: 14px; color: #212529; line-height: 1.4; }
    
    .broadcast-message {
        font-size: 13px;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .broadcast-read-more {
        color: #667eea;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .broadcast-item:hover .broadcast-read-more { color: #764ba2; }
    .broadcast-time { color: #6c757d; font-size: 11px; white-space: nowrap; }

    .unread-dot {
        width: 8px;
        height: 8px;
        background: #667eea;
        border-radius: 50%;
        display: inline-block;
        margin-left: 8px;
        flex-shrink: 0;
    }

    .empty-state { padding: 40px 20px; }
    .empty-icon { font-size: 64px; color: #dee2e6; line-height: 1; }
    .empty-icon.text-danger { color: #dc3545; }
    .empty-state h6 { font-size: 16px; margin-bottom: 4px; }
    .empty-state p { font-size: 13px; }

    #broadcastList::-webkit-scrollbar { width: 6px; }
    #broadcastList::-webkit-scrollbar-track { background: #f8f9fa; }
    #broadcastList::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
    #broadcastList::-webkit-scrollbar-thumb:hover { background: #a0aec0; }

    .profile-initial-nav {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .profile-initial-nav:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .profile-initial-dropdown {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
        margin: 0 auto;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    @media (max-width: 768px) {
        .search-form { margin: 10px 0; width: 100%; }
        .search-input { width: 100%; }
        .notification-dropdown .dropdown-menu { min-width: 320px !important; }
    }
</style>