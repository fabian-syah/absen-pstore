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
            
            {{-- 1. Fullscreen Button --}}
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link" href="javascript:void(0)" onclick="toggleFullScreen()" title="Fullscreen">
                    <i class="mdi mdi-fullscreen"></i>
                </a>
            </li>

            {{-- 2. THEME PICKER (FITUR BARU) --}}
            <li class="nav-item dropdown">
                <a class="nav-link" id="themeDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Ganti Tema">
                    <i class="mdi mdi-palette-swatch theme-icon-spin text-primary" style="font-size: 22px;"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="themeDropdown">
                    <div class="dropdown-header text-center">
                        <p class="mb-0 font-weight-medium">Pilih Tema</p>
                    </div>
                    <div class="dropdown-divider"></div>
                    
                    <a class="dropdown-item theme-item" onclick="changeTheme('default')">
                        <div class="theme-color-preview" style="background: linear-gradient(135deg, #667eea, #764ba2);"></div> Default (Purple)
                    </a>
                    <a class="dropdown-item theme-item" onclick="changeTheme('ocean')">
                        <div class="theme-color-preview" style="background: linear-gradient(135deg, #00c6ff, #0072ff);"></div> Ocean Blue
                    </a>
                    <a class="dropdown-item theme-item" onclick="changeTheme('nature')">
                        <div class="theme-color-preview" style="background: linear-gradient(135deg, #11998e, #38ef7d);"></div> Nature Green
                    </a>
                    <a class="dropdown-item theme-item" onclick="changeTheme('sunset')">
                        <div class="theme-color-preview" style="background: linear-gradient(135deg, #FF512F, #DD2476);"></div> Sunset Orange
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item theme-item" onclick="changeTheme('midnight')">
                        <div class="theme-color-preview" style="background: #2b2b40; border: 1px solid #fff;"></div> Midnight (Dark)
                    </a>
                </div>
            </li>

            {{-- 3. Search (Admin/Audit) --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit']))
                <li class="nav-item">
                    <div class="search-form position-relative">
                        <i class="icon-search position-absolute search-icon"></i>
                        <input type="search" class="form-control search-input" id="globalSearch"
                            data-url="{{ route('search') }}" placeholder="Search users..."
                            autocomplete="off">
                        <div class="search-results dropdown-menu" id="searchResults"></div>
                    </div>
                </li>
            @endif

            {{-- 4. Notifications & Profile (Sama seperti sebelumnya) --}}
            {{-- ... Copy paste sisa menu profile dan notifikasi disini ... --}}
             <li class="nav-item dropdown user-dropdown">
                <a class="nav-link p-0" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="position-relative d-inline-block">
                         @if (Auth::user()->profile_photo_path)
                            <img class="img-xs rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image" style="object-fit: cover; border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }}; padding: 1px;">
                        @else
                            <div class="profile-initial-nav" style="border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }};">{{ getInitials(Auth::user()->name) }}</div>
                        @endif
                         @if(Auth::user()->is_verified)
                            <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" style="bottom: -2px; right: -2px; width: 14px; height: 14px; border: 1px solid white;"><i class="mdi mdi-check-decagram text-primary" style="font-size: 10px;"></i></span>
                        @endif
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <div class="position-relative d-inline-block mb-2">
                             @if (Auth::user()->profile_photo_path)
                                <img class="img-md rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image" style="width: 60px; height: 60px; object-fit: cover; border: {{ Auth::user()->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                            @else
                                <div class="profile-initial-dropdown" style="border: {{ Auth::user()->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">{{ getInitials(Auth::user()->name) }}</div>
                            @endif
                        </div>
                        <p class="mb-1 mt-1 fw-semibold">{{ Auth::user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile</a>
                    <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>