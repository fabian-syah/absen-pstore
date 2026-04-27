<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') | PStore System</title>

    {{-- Meta Token & Manifest --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">

    {{-- CSS Assets --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/select.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

    @stack('styles')

    <style>
        /* ==========================================================
            MODERN DESIGN SYSTEM & UI ENHANCEMENTS
           ========================================================== */

        /* CSS Variables - Design Tokens */
        :root {
            /* ==========================================================
               BLUE THEME - DEFAULT PSTORE
               ========================================================== */

            /* Primary Colors - Blue */
            --pstore-primary: #0d6efd;
            --pstore-primary-dark: #0a58ca;
            --pstore-primary-darker: #084298;
            --pstore-primary-light: rgba(13, 110, 253, 0.1);

            /* Accents */
            --pstore-accent: #6c757d;
            --pstore-accent-light: #f8f9fa;

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            --gradient-soft: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);
            --gradient-bg: linear-gradient(180deg, #FAFAFA 0%, #F4F7FE 100%);

            /* Neutrals */
            --pstore-dark: #1A2E22;
            /* Dark Green-Black */
            --pstore-gray-900: #1b2620;
            /* Soft Black */
            --pstore-gray-700: #495057;
            --pstore-gray-500: #6c757d;
            --pstore-gray-300: #dee2e6;
            --pstore-gray-100: #f8f9fa;

            /* Spacing Scale */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;

            /* Border Radius */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;

            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --shadow-xl: 0 12px 40px rgba(0, 0, 0, 0.15);
            --shadow-primary: 0 4px 16px rgba(0, 105, 62, 0.25);

            /* Layout */
            --sidebar-width: 245px;
            --header-height: 70px;

            /* Transitions */
            --transition-fast: 0.15s ease;
            --transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Global Styles */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--pstore-gray-900);
            overflow-x: hidden;
        }

        /* Layout Structure */
        @media (min-width: 992px) {

            .container-scroller,
            .page-body-wrapper {
                overflow: visible !important;
            }

            .sidebar {
                position: fixed !important;
                top: var(--header-height);
                height: calc(100vh - var(--header-height));
                overflow-y: auto;
                z-index: 1000;
                width: var(--sidebar-width);
                transition: width var(--transition-base);
            }

            .main-panel {
                margin-left: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
                min-height: calc(100vh - var(--header-height));
                display: flex;
                flex-direction: column;
                transition: margin-left var(--transition-base), width var(--transition-base);
            }

            body.sidebar-icon-only .sidebar {
                width: 70px;
            }

            body.sidebar-icon-only .main-panel {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }

        /* Content Wrapper */
        .content-wrapper {
            padding: var(--spacing-lg) var(--spacing-xl) !important;
            background: transparent;
            flex-grow: 1;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modern Card Styling */
        .card {
            border: none !important;
            background: #ffffff;
            box-shadow: var(--shadow-md) !important;
            border-radius: var(--radius-md) !important;
            transition: all var(--transition-base);
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            box-shadow: var(--shadow-lg) !important;
            transform: translateY(-2px);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity var(--transition-base);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-header {
            background: #ffffff !important;
            border-bottom: 1px solid var(--pstore-gray-300) !important;
            padding: var(--spacing-lg) var(--spacing-xl) !important;
        }

        .card-body {
            padding: var(--spacing-xl) !important;
        }

        .card-title {
            font-weight: 700 !important;
            color: var(--pstore-gray-900) !important;
            margin-bottom: var(--spacing-md) !important;
            font-size: 1.25rem !important;
        }

        /* Custom Scrollbar - Global */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.03);
            border-radius: var(--radius-full);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            transition: background var(--transition-base);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--pstore-primary-dark), var(--pstore-primary-darker));
        }

        /* Enhanced Buttons */
        .btn-primary {
            background: var(--gradient-primary) !important;
            border: none !important;
            box-shadow: var(--shadow-primary) !important;
            font-weight: 600 !important;
            transition: all var(--transition-base) !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.3) !important;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Form Inputs */
        .form-control:focus {
            border-color: var(--pstore-primary) !important;
            box-shadow: 0 0 0 0.2rem var(--pstore-primary-light) !important;
        }

        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: var(--gradient-soft) !important;
            color: var(--pstore-gray-900) !important;
            font-weight: 600 !important;
            border: none !important;
            padding: var(--spacing-md) var(--spacing-lg) !important;
            font-size: 0.875rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all var(--transition-fast);
        }

        .table tbody tr:hover {
            background: var(--pstore-gray-100);
            transform: scale(1.01);
        }

        /* Badges */
        .badge {
            padding: 0.35rem 0.75rem !important;
            border-radius: var(--radius-sm) !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
        }

        .badge-primary {
            background: var(--gradient-primary) !important;
        }

        /* Alerts */
        .alert {
            border: none !important;
            border-radius: var(--radius-md) !important;
            box-shadow: var(--shadow-sm) !important;
        }

        /* Page Title Animation */
        .page-header {
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading States */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: var(--radius-sm);
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Mobile Responsiveness */
        @media (max-width: 991px) {
            .content-wrapper {
                padding: var(--spacing-md) !important;
                padding-bottom: 90px !important;
            }

            .card-body {
                padding: var(--spacing-lg) !important;
            }
        }

        /* ============================================ */
        /* MOBILE BOTTOM NAVIGATION BAR                  */
        /* ============================================ */
        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 991px) {

            /* === ENTRANCE ANIMATION === */
            @keyframes bottomNavSlideUp {
                from {
                    transform: translateY(100%);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            @keyframes bottomNavItemPop {
                0% {
                    transform: scale(0.5);
                    opacity: 0;
                }

                60% {
                    transform: scale(1.08);
                }

                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            @keyframes activeIndicatorGrow {
                from {
                    width: 0;
                    opacity: 0;
                }

                to {
                    width: 32px;
                    opacity: 1;
                }
            }

            @keyframes iconBounce {

                0%,
                100% {
                    transform: translateY(-2px);
                }

                50% {
                    transform: translateY(-5px);
                }
            }

            @keyframes absenPulse {

                0%,
                100% {
                    box-shadow: 0 4px 14px rgba(0, 105, 62, 0.45);
                }

                50% {
                    box-shadow: 0 4px 22px rgba(0, 105, 62, 0.65), 0 0 0 8px rgba(0, 105, 62, 0.08);
                }
            }

            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 12px;
                left: 12px;
                right: 12px;
                z-index: 1050;
                background: rgba(255, 255, 255, 0.55);
                border: 1px solid rgba(255, 255, 255, 0.6);
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.12),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8),
                    inset 0 -1px 0 rgba(255, 255, 255, 0.3);
                padding: 8px 4px;
                padding-bottom: max(8px, env(safe-area-inset-bottom));
                backdrop-filter: blur(24px) saturate(180%);
                -webkit-backdrop-filter: blur(24px) saturate(180%);
                border-radius: 22px;
                animation: bottomNavSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            .mobile-bottom-nav .nav-item {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 4px 2px;
                text-decoration: none;
                color: #8a9bad;
                font-size: 10px;
                font-weight: 500;
                line-height: 1.2;
                transition: color 0.25s ease, transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
                position: relative;
                gap: 3px;
                -webkit-tap-highlight-color: transparent;
                animation: bottomNavItemPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) backwards;
            }

            /* Stagger entrance for each nav item */
            .mobile-bottom-nav .nav-item:nth-child(1) {
                animation-delay: 0.1s;
            }

            .mobile-bottom-nav .nav-item:nth-child(2) {
                animation-delay: 0.2s;
            }

            .mobile-bottom-nav .nav-item:nth-child(3) {
                animation-delay: 0.3s;
            }

            .mobile-bottom-nav .nav-item:nth-child(4) {
                animation-delay: 0.4s;
            }

            .mobile-bottom-nav .nav-item i {
                font-size: 22px;
                transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.25s ease;
                display: block;
            }

            .mobile-bottom-nav .nav-item span {
                transition: all 0.25s ease;
            }

            /* Press-down tap effect */
            .mobile-bottom-nav .nav-item:active {
                transform: scale(0.88);
                transition: transform 0.1s ease;
            }

            .mobile-bottom-nav .nav-item:hover {
                color: var(--pstore-primary);
            }

            .mobile-bottom-nav .nav-item:hover i {
                transform: translateY(-2px);
            }

            .mobile-bottom-nav .nav-item.active {
                color: var(--pstore-primary);
            }

            .mobile-bottom-nav .nav-item.active i {
                animation: iconBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s 1;
                transform: translateY(-2px);
                filter: drop-shadow(0 2px 4px rgba(0, 105, 62, 0.3));
            }

            .mobile-bottom-nav .nav-item.active span {
                font-weight: 700;
            }

            /* Active indicator line with grow animation */
            .mobile-bottom-nav .nav-item.active::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                height: 3px;
                background: linear-gradient(90deg, var(--pstore-primary), var(--pstore-accent));
                border-radius: 0 0 4px 4px;
                animation: activeIndicatorGrow 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s forwards;
                width: 0;
            }

            /* Absen button - special center floating button */
            .mobile-bottom-nav .nav-item.nav-absen {
                position: relative;
            }

            .mobile-bottom-nav .nav-item.nav-absen .absen-bubble {
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, var(--pstore-primary) 0%, var(--pstore-primary-dark) 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 14px rgba(0, 105, 62, 0.45);
                margin-bottom: 2px;
                transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
                margin-top: -12px;
                border: 3px solid #fff;
                animation: absenPulse 2.5s ease-in-out infinite;
            }

            .mobile-bottom-nav .nav-item.nav-absen .absen-bubble i {
                font-size: 22px;
                color: #fff;
                transform: none !important;
            }

            .mobile-bottom-nav .nav-item.nav-absen:active .absen-bubble {
                transform: scale(0.9) !important;
                transition: transform 0.1s ease;
            }

            .mobile-bottom-nav .nav-item.nav-absen:hover .absen-bubble,
            .mobile-bottom-nav .nav-item.nav-absen.active .absen-bubble {
                transform: scale(1.08);
                box-shadow: 0 6px 18px rgba(0, 105, 62, 0.55);
                animation: none;
            }

            .mobile-bottom-nav .nav-item.nav-absen span {
                color: #8a9bad;
                font-size: 10px;
            }

            .mobile-bottom-nav .nav-item.nav-absen.active span {
                color: var(--pstore-primary);
                font-weight: 700;
            }


        }

        /* Utility Classes */
        .gradient-text {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .hover-lift {
            transition: transform var(--transition-base);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
        }

        /* ==========================================================
           GAMIFICATION EFFECTS (RANK SYSTEM) - INSPIRED BY REFERENCE IMAGE
           ========================================================== */
        
        /* Rank Text Styling */
        .rank-name-premium {
            text-shadow: 0 0 10px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.2);
            letter-spacing: 0.5px;
            text-transform: capitalize;
            position: relative;
            display: inline-block;
        }

        .rank-icon-3d {
            filter: url(#remove-white-bg); /* Global Transparency Fix */
        }
        
        /* 1. Base/Foundation Tiers */
        .rank-foundation {
            border: 2px solid rgba(255,255,255,0.1);
        }

        /* 2. Elite Tiers (Glint/Shine) */
        .rank-elite {
            position: relative;
            overflow: hidden !important;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }
        
        /* Platinum Specific (Tier 7) - Better than Gold */
        .rank-platinum {
            box-shadow: 0 0 20px rgba(186, 230, 253, 0.6), inset 0 0 10px rgba(255,255,255,0.5) !important;
            border: 2px solid #FFF !important;
            animation: platinumGlow 3s ease-in-out infinite alternate !important;
        }
        @keyframes platinumGlow {
            0% { filter: brightness(1) drop-shadow(0 0 2px #BAE6FD); transform: scale(1); }
            100% { filter: brightness(1.2) drop-shadow(0 0 10px #BAE6FD); transform: scale(1.05); }
        }

        .rank-elite::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -150%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.4) 50%,
                rgba(255,255,255,0) 100%
            );
            transform: rotate(45deg);
            animation: silverGlint 4s infinite;
        }
        @keyframes silverGlint {
            0% { left: -150%; }
            30% { left: 150%; }
            100% { left: 150%; }
        }

        /* 3. Masterclass Tiers (Crystalline/Aura) */
        .rank-masterclass {
            position: relative;
            box-shadow: 0 0 15px currentColor;
            animation: crystalShine 3s linear infinite;
        }
        @keyframes crystalShine {
            0% { filter: hue-rotate(0deg) brightness(1); }
            50% { filter: hue-rotate(15deg) brightness(1.2); }
            100% { filter: hue-rotate(0deg) brightness(1); }
        }

        /* 4. Legend (Tier 16) - Golden Wings/White Glow */
        .rank-legend {
            animation: legendFloat 3s ease-in-out infinite;
            box-shadow: 0 0 25px #FFFBEB, inset 0 0 10px #FFFBEB;
            border: 2px solid #FEF3C7 !important;
        }
        @keyframes legendFloat {
            0%, 100% { transform: translateY(0) scale(1.05); }
            50% { transform: translateY(-5px) scale(1.1); }
        }

        /* 5. Mythic (Tier 17) - Dark Nebula/Star Clusters */
        .rank-mythic {
            background: radial-gradient(circle at center, #581C87 0%, #170728 100%) !important;
            box-shadow: 0 0 20px #7C3AED !important;
            overflow: hidden !important;
        }
        .rank-mythic::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, #fff 1px, transparent 1px);
            background-size: 15px 15px;
            opacity: 0.3;
            animation: starsMove 10s linear infinite;
        }
        @keyframes starsMove {
            from { transform: scale(1) rotate(0deg); }
            to { transform: scale(1.5) rotate(15deg); }
        }

        /* 6. Immortal (Tier 18) - Living Crimson Flame */
        .rank-immortal {
            background: linear-gradient(to top, #7F1D1D, #EF4444) !important;
            box-shadow: 0 0 25px #EF4444, inset 0 0 10px #7F1D1D !important;
            position: relative;
        }
        .rank-immortal::after {
            content: '';
            position: absolute;
            inset: -5px;
            background: inherit;
            filter: url(#rank-fire) blur(4px);
            opacity: 0.8;
            z-index: -1;
            animation: immortalFire 2s steps(10) infinite;
        }
        @keyframes immortalFire {
            from { filter: url(#rank-fire) hue-rotate(0deg); }
            to { filter: url(#rank-fire) hue-rotate(15deg); }
        }

        /* 7. Celestial (Tier 19) - Divine Radiance */
        .rank-celestial {
            background: #BAE6FD !important;
            box-shadow: 0 0 30px #0EA5E9, 0 0 60px rgba(14, 165, 233, 0.4) !important;
            border: 3px solid #FFF !important;
            filter: url(#rank-glow);
            animation: celestialPulse 3s ease-in-out infinite alternate;
        }
        @keyframes celestialPulse {
            from { transform: scale(1.05); filter: url(#rank-glow) brightness(1); }
            to { transform: scale(1.15); filter: url(#rank-glow) brightness(1.4); }
        }

        /* 8. Eternal (Tier 20) - The Singularity */
        .rank-eternal {
            background: #000 !important;
            border: 3px solid #F59E0B !important;
            box-shadow: 0 0 40px rgba(245, 158, 11, 0.6), inset 0 0 30px #000 !important;
            position: relative;
            transform: scale(1.2);
            animation: eternalFloat 4s ease-in-out infinite;
        }
        .rank-eternal::before {
            content: '';
            position: absolute;
            inset: -8px;
            background: conic-gradient(from 0deg, transparent, #F59E0B, transparent, #F59E0B, transparent);
            animation: rotateGlow 2s linear infinite;
            border-radius: inherit;
            z-index: -1;
            filter: blur(8px);
        }
        @keyframes eternalFloat {
            0%, 100% { transform: scale(1.2) translateY(0); }
            50% { transform: scale(1.25) translateY(-5px); }
        }
        .rank-eternal i {
            color: #F59E0B !important;
            animation: floatGold 3s ease-in-out infinite alternate;
        }
        @keyframes rotateGlow {
            100% { transform: rotate(360deg); }
        }
        @keyframes floatGold {
            from { transform: translateY(0) scale(1); filter: drop-shadow(0 0 2px #F59E0B); }
            to { transform: translateY(-3px) scale(1.1); filter: drop-shadow(0 0 8px #F59E0B); }
        }
    </style>
</head>

<body class="with-welcome-text">
    {{-- GAMIFICATION SVG FILTERS --}}
    <svg style="visibility: hidden; position: absolute;" width="0" height="0" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <filter id="rank-fire">
                <feTurbulence type="fractalNoise" baseFrequency="0.05 0.2" numOctaves="3" result="noise" />
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="10" />
            </filter>
            <filter id="rank-glow">
                <feGaussianBlur in="SourceGraphic" stdDeviation="4" result="blur" />
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="glow" />
                <feComposite in="SourceGraphic" in2="glow" operator="over" />
            </filter>
        </defs>
    </svg>
    <div class="container-scroller">

        {{-- Include Header --}}
        @include('layout.header')

        <div class="container-fluid page-body-wrapper">

            {{-- Include Sidebar --}}
            @include('layout.sidebar')

            <div class="main-panel">
                <div class="content-wrapper">
                    {{-- Konten Utama Halaman --}}
                    @yield('content')
                </div>

                {{-- Include Footer --}}
                @include('layout.footer')
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MOBILE BOTTOM NAVIGATION BAR --}}
    {{-- ============================================ --}}
    @php
        $isHome = request()->is('/') || request()->routeIs('dashboard') || request()->routeIs('dashboard.index');
        $isAbsen = request()->routeIs('self.attend.*');
        $isIzin = request()->routeIs('leave-requests.create');
        $isProfile = request()->routeIs('profile.*');
    @endphp
    <nav class="mobile-bottom-nav">
        {{-- Home --}}
        <a href="{{ url('/') }}" class="nav-item {{ $isHome ? 'active' : '' }}">
            <i class="mdi mdi-home{{ $isHome ? '' : '-outline' }}"></i>
            <span>Home</span>
        </a>

        {{-- Absen → menuju halaman absen mandiri (clock-in) --}}
        <a href="{{ route('self.attend.create') }}" class="nav-item nav-absen {{ $isAbsen ? 'active' : '' }}">
            <div class="absen-bubble">
                <i class="mdi mdi-fingerprint"></i>
            </div>
            <span>Absen</span>
        </a>

        {{-- Izin → menuju form pengajuan izin --}}
        <a href="{{ route('leave-requests.create') }}" class="nav-item {{ $isIzin ? 'active' : '' }}">
            <i class="mdi mdi-file-document-edit{{ $isIzin ? '' : '-outline' }}"></i>
            <span>Izin</span>
        </a>



        {{-- Profile --}}
        <a href="{{ route('profile.edit') }}" class="nav-item {{ $isProfile ? 'active' : '' }}">
            <i class="mdi mdi-account{{ $isProfile ? '' : '-outline' }}"></i>
            <span>Profile</span>
        </a>
    </nav>

    {{-- JS Assets --}}
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/todolist.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    @if(request()->is('/') || request()->routeIs('dashboard') || request()->routeIs('dashboard.index'))
        <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    @endif

    {{-- Firebase Notification Logic --}}
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>

    <script>
        var firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };
        console.log("🔥 Firebase Config:", firebaseConfig); // DEBUG LOG

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const messaging = firebase.messaging();

        @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin' || auth()->user()->role == 'admin_gaji'))
            function sendTokenToServer(token) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch("{{ route('update.fcm.token') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ token: token })
                }).then(response => response.json())
                    .then(data => console.log("FCM Token status:", data.message))
                    .catch(err => console.log("Gagal menyimpan token.", err));
            }

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js?v=' + Date.now())
                    .then(function (registration) {
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                // Firebase FCM Token
                                messaging.getToken({
                                    vapidKey: "{{ config('services.firebase.vapid_key') }}",
                                    serviceWorkerRegistration: registration
                                })
                                    .then((currentToken) => {
                                        if (currentToken) sendTokenToServer(currentToken);
                                    }).catch(err => console.log("FCM Token error suppressed."));

                            }
                        });
                    }).catch(err => console.log("Service Worker registration suppressed."));
            }

            messaging.onMessage((payload) => {
                console.log("🔥 Foreground Message Received:", payload); // DEBUG LOG
                const title = payload.notification ? payload.notification.title : "Notifikasi Baru";
                const body = payload.notification ? payload.notification.body : "Cek dashboard.";
                const url = payload.data ? payload.data.click_action : '/';

                if (Notification.permission === 'granted') {
                    var notif = new Notification(title, {
                        body: body,
                        icon: 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png',
                        tag: 'audit-alert-' + Date.now()
                    });
                    notif.onclick = function () {
                        window.focus();
                        window.location.href = url;
                        this.close();
                    };
                }
            });
        @endif
    </script>

    @stack('scripts')
    
    <!-- SVG Filters for Gamification -->
    <svg style="position: absolute; width: 0; height: 0;" xmlns="http://www.w3.org/2000/svg">
        <filter id="remove-white-bg">
            <!-- Matrix that targets white (1,1,1) and makes it transparent -->
            <feColorMatrix type="matrix" values="1 0 0 0 0
                                               0 1 0 0 0
                                               0 0 1 0 0
                                               -1.5 -1.5 -1.5 1 0.2"/>
        </filter>
        <filter id="rank-glow">
            <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
            <feMerge>
                <feMergeNode in="coloredBlur"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </svg>
    @if(auth()->check() && auth()->user()->role == 'audit')
        <div id="audit-notif-blocker" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 999999; backdrop-filter: blur(15px); color: white; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
            <div id="audit-content-request" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 40px; border-radius: 24px; border: 2px solid #3b82f6; max-width: 500px; box-shadow: 0 0 50px rgba(59, 130, 246, 0.5);">
                <div style="font-size: 80px; margin-bottom: 20px;">🔔</div>
                <h2 style="font-weight: 800; margin-bottom: 15px; color: #fff;">NOTIFIKASI WAJIB AKTIF</h2>
                <p style="font-size: 16px; color: #cbd5e1; line-height: 1.6; margin-bottom: 30px;">
                    Sebagai <b>Audit</b>, Anda wajib mengaktifkan notifikasi browser untuk memantau absensi masuk secara real-time.
                </p>
                <button onclick="forceEnableNotif()" style="background: #3b82f6; color: white; border: none; padding: 15px 40px; border-radius: 50px; font-weight: bold; font-size: 18px; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);">
                    AKTIFKAN SEKARANG
                </button>
            </div>

            <div id="audit-content-denied" style="display: none; background: linear-gradient(135deg, #2d0a0a 0%, #1a0505 100%); padding: 40px; border-radius: 24px; border: 2px solid #ef4444; max-width: 600px; box-shadow: 0 0 50px rgba(239, 68, 68, 0.3);">
                <div style="font-size: 60px; margin-bottom: 20px;">🚫</div>
                <h2 style="font-weight: 800; margin-bottom: 15px; color: #fff;">NOTIFIKASI DIBLOKIR</h2>
                <p style="font-size: 15px; color: #fca5a5; margin-bottom: 25px;">
                    Anda telah memblokir izin notifikasi. Ikuti langkah di bawah untuk membukanya kembali agar bisa masuk ke sistem:
                </p>
                
                <div id="denied-guide-container" style="text-align: left; background: rgba(0,0,0,0.3); padding: 20px; border-radius: 15px; margin-bottom: 25px;">
                    <!-- Content will be injected by JS based on OS -->
                </div>

                <script>
                    function getOSGuide() {
                        var userAgent = window.navigator.userAgent,
                            platform = window.navigator.platform,
                            macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'],
                            windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
                            iosPlatforms = ['iPhone', 'iPad', 'iPod'],
                            os = null;

                        if (macosPlatforms.indexOf(platform) !== -1) {
                            return `
                                <p style="font-weight: bold; color: #fff; margin-bottom: 10px;"><i class="mdi mdi-apple me-2"></i>Panduan MacBook (Safari/Chrome):</p>
                                <ol style="color: #cbd5e1; font-size: 13px; padding-left: 20px;">
                                    <li><b>Safari:</b> Klik menu <b>Safari</b> di baris atas -> <b>Settings</b> -> <b>Websites</b> -> <b>Notifications</b>. Cari 'absenps.com' dan ubah ke <b>Allow</b>.</li>
                                    <li><b>Chrome:</b> Klik ikon <b>Gembok (🔒)</b> di sebelah URL -> <b>Site Settings</b> -> <b>Notifications</b> -> <b>Allow</b>.</li>
                                    <li>Pastikan mode <b>Do Not Disturb</b> (Fokus) di MacBook Anda sedang OFF.</li>
                                </ol>
                            `;
                        } else if (iosPlatforms.indexOf(platform) !== -1) {
                            return `
                                <p style="font-weight: bold; color: #fff; margin-bottom: 10px;"><i class="mdi mdi-cellphone-iphone me-2"></i>Panduan iPhone (iOS):</p>
                                <ol style="color: #cbd5e1; font-size: 13px; padding-left: 20px;">
                                    <li>Buka aplikasi <b>Settings</b> di iPhone Anda.</li>
                                    <li>Pilih menu <b>Notifications</b>.</li>
                                    <li>Cari aplikasi <b>'PStore System'</b> (atau nama PWA ini).</li>
                                    <li>Aktifkan <b>Allow Notifications</b>.</li>
                                    <li><i>Catatan: Notifikasi iPhone hanya jalan jika web ini sudah Anda 'Add to Home Screen'.</i></li>
                                </ol>
                            `;
                        } else if (/Android/.test(userAgent)) {
                            return `
                                <p style="font-weight: bold; color: #fff; margin-bottom: 10px;"><i class="mdi mdi-android me-2"></i>Panduan HP Android (Chrome):</p>
                                <ol style="color: #cbd5e1; font-size: 13px; padding-left: 20px;">
                                    <li>Klik titik tiga <b>(⋮)</b> di pojok kanan atas browser.</li>
                                    <li>Klik ikon <b>(ⓘ) Info</b> -> <b>Permissions</b> atau <b>Site Settings</b>.</li>
                                    <li>Pilih <b>Notifications</b> dan klik <b>Allow</b>.</li>
                                    <li>Pastikan mode <b>Hemat Baterai</b> tidak memblokir notifikasi.</li>
                                </ol>
                            `;
                        } else {
                            return `
                                <p style="font-weight: bold; color: #fff; margin-bottom: 10px;"><i class="mdi mdi-microsoft-windows me-2"></i>Panduan Windows (PC/Laptop):</p>
                                <ol style="color: #cbd5e1; font-size: 13px; padding-left: 20px;">
                                    <li>Klik ikon <b>Gembok (🔒)</b> di sebelah URL web.</li>
                                    <li>Cari menu <b>Notifications</b> (Notifikasi).</li>
                                    <li>Ubah statusnya menjadi <b>Allow</b> (Izinkan).</li>
                                    <li>Jika tidak ada, klik <b>Site Settings</b> lalu cari Notifikasi.</li>
                                </ol>
                            `;
                        }
                    }
                    document.getElementById('denied-guide-container').innerHTML = getOSGuide();
                </script>

                <button onclick="location.reload()" style="background: #ef4444; color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: bold; cursor: pointer;">
                    SAYA SUDAH IZINKAN, REFRESH SEKARANG
                </button>
            </div>
        </div>
        <script>
            function checkAuditNotif() {
                var blocker = document.getElementById('audit-notif-blocker');
                var reqContent = document.getElementById('audit-content-request');
                var deniedContent = document.getElementById('audit-content-denied');

                if (Notification.permission === 'granted') {
                    blocker.style.display = 'none';
                    document.body.style.overflow = 'auto';
                } else {
                    blocker.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    
                    if (Notification.permission === 'denied') {
                        reqContent.style.display = 'none';
                        deniedContent.style.display = 'block';
                    } else {
                        reqContent.style.display = 'block';
                        deniedContent.style.display = 'none';
                    }
                }
            }

            function forceEnableNotif() {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        location.reload();
                    } else {
                        checkAuditNotif();
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', checkAuditNotif);
            setInterval(checkAuditNotif, 2000);
        </script>
    @endif

    {{-- Push Notification Registration (ALL ROLES) --}}
    @if(auth()->check())
    <script>
        (function() {
            if (!('serviceWorker' in navigator)) return;

            navigator.serviceWorker.register('/sw.js?v=' + Date.now()).then(function(registration) {
                console.log('SW Registered:', registration.scope);

                // --- AUTO RESET JIKA VAPID KEY BERUBAH ---
                const CURRENT_VAPID = 'BJyXyV7_f7K8oRz8n0U4v6f1B8n2m9s8k7j6h5g4f3d2s1a0q9w8e7r6t5y4u3i2o1p';
                if (localStorage.getItem('last_vapid_key') !== CURRENT_VAPID) {
                    registration.pushManager.getSubscription().then(sub => {
                        if (sub) sub.unsubscribe();
                        localStorage.setItem('last_vapid_key', CURRENT_VAPID);
                        console.log('VAPID Changed, Unsubscribed old.');
                    });
                }

                Notification.requestPermission().then(function(permission) {
                    if (permission !== 'granted') return;

                    // 1. Firebase Messaging (Only for specific roles)
                    @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin' || auth()->user()->role == 'admin_gaji'))
                    messaging.getToken({
                        vapidKey: "{{ config('services.firebase.vapid_key') }}",
                        serviceWorkerRegistration: registration
                    }).then(function(token) {
                        if (token) sendTokenToServer(token);
                    }).catch(function(e) { console.log('FCM Token Error:', e); });
                    @endif

                    // 2. VAPID Web Push (For ALL roles)
                    if ('PushManager' in window) {
                        var vapidKey = 'BJyXyV7_f7K8oRz8n0U4v6f1B8n2m9s8k7j6h5g4f3d2s1a0q9w8e7r6t5y4u3i2o1p';
                        var padding = '='.repeat((4 - vapidKey.length % 4) % 4);
                        var base64 = (vapidKey + padding).replace(/\-/g, '+').replace(/_/g, '/');
                        var rawData = atob(base64);
                        var outputArray = new Uint8Array(rawData.length);
                        for (var i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);

                        registration.pushManager.getSubscription().then(function(sub) {
                            if (sub) return sub;
                            return registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: outputArray });
                        }).then(function(subscription) {
                            fetch('/push-subscription', {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json', 
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                                },
                                body: JSON.stringify(subscription)
                            }).then(function(r) { return r.json(); }).then(function(d) { console.log('Push Saved:', d); });
                        }).catch(function(e) { console.log('Push Error:', e); });
                    }
                });
            }).catch(function(e) { console.log('SW Error:', e); });
        })();
    </script>
    @endif
</body>

</html>