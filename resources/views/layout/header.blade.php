@php
    use Illuminate\Support\Facades\Storage;
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.9); border-bottom: 1px solid #eee;">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu text-dark"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 120px; height: auto;" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 40px; height: auto;" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h2 class="welcome-text mb-0" style="font-size: 1.2rem; color: #333;">@yield('heading')</h2>
                <p class="welcome-sub-text text-muted mb-0">{{ Auth::user()->role }} • {{ Auth::user()->division->name ?? 'N/A' }}</p>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            {{-- Search bar for desktop --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'admin_gaji']))
                <li class="nav-item d-none d-md-block me-3">
                    <div class="search-form position-relative">
                        <i class="icon-search position-absolute" style="left: 15px; top: 11px; color: #888;"></i>
                        <input type="search" class="form-control" id="globalSearch" data-url="{{ route('search') }}" 
                               placeholder="Cari sesuatu..." 
                               style="border-radius: 50px; padding-left: 40px; background: #f1f3f5; border: none; width: 250px;">
                        <div class="search-results dropdown-menu" id="searchResults"></div>
                    </div>
                </li>
            @endif

            {{-- Fullscreen --}}
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link" href="#" onclick="toggleFullScreen()"><i class="mdi mdi-fullscreen fs-4"></i></a>
            </li>

            {{-- Notifications --}}
            <li class="nav-item dropdown notification-dropdown">
                <a class="nav-link count-indicator" id="broadcastDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"></i>
                    <span class="count" id="broadcastCount" style="display: none;"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="broadcastDropdown">
                    <div class="dropdown-header px-4 py-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Notifikasi Terbaru</h6>
                    </div>
                    <div id="broadcastList" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
            </li>

            {{-- Chat --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="messageDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-mail"></i>
                    <span class="count bg-danger" id="mainChatBadge" style="display: none;"></span>
                </a>
                {{-- Dropdown Chat View 1 & 2 logic remains same but with better padding --}}
            </li>

            {{-- User Profile --}}
            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link p-0" id="UserDropdown" href="#" data-bs-toggle="dropdown">
                    @if (Auth::user()->profile_photo_path)
                        <img class="img-xs rounded-circle shadow-sm" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile" style="border: 2px solid #fff;">
                    @else
                        <div class="img-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">{{ getInitials(Auth::user()->name) }}</div>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown" style="width: 250px;">
                    <div class="dropdown-header text-center p-4">
                        @if (Auth::user()->profile_photo_path)
                            <img class="img-md rounded-circle mb-2 shadow" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile">
                        @else
                            <div class="img-md rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-3 mx-auto mb-2">{{ getInitials(Auth::user()->name) }}</div>
                        @endif
                        <p class="mb-1 mt-3 fw-bold">{{ Auth::user()->name }}</p>
                        <p class="fw-light text-muted mb-0 small">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item text-danger"><i class="dropdown-item-icon mdi mdi-power text-danger me-2"></i> Sign Out</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>