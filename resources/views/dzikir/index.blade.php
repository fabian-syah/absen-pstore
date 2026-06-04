@extends('layout.master')

@section('title', 'Zikir Online')

@push('styles')
<script>
    tailwind.config = {
      darkMode: "class",
      corePlugins: {
        preflight: false, // Prevent Tailwind from overriding Bootstrap styles in the master layout
      },
      theme: {
        extend: {
          "colors": {
            "tertiary-fixed-dim": "#ffb693",
            "inverse-on-surface": "#303130",
            "surface-variant": "#343535",
            "outline": "#8c9291",
            "on-tertiary-fixed-variant": "#793104",
            "on-error-container": "#ffdad6",
            "on-tertiary-fixed": "#351000",
            "primary-fixed": "#cee7e7",
            "secondary": "#d5baff",
            "surface-dim": "#121413",
            "inverse-surface": "#e3e2e1",
            "surface-container-highest": "#343535",
            "primary-container": "#0f2626",
            "tertiary": "#ffb693",
            "on-error": "#690005",
            "error-container": "#93000a",
            "surface": "#121413",
            "surface-container-low": "#1b1c1c",
            "on-tertiary-container": "#cd7142",
            "on-surface-variant": "#c2c8c7",
            "secondary-container": "#5d15b7",
            "secondary-fixed-dim": "#d5baff",
            "outline-variant": "#424848",
            "primary-fixed-dim": "#b3cbcb",
            "secondary-fixed": "#ecdcff",
            "background": "#121413",
            "primary": "#b3cbcb",
            "on-secondary-container": "#c7a5ff",
            "surface-container-high": "#292a2a",
            "on-primary-fixed-variant": "#344b4b",
            "on-primary-fixed": "#071f1f",
            "on-secondary-fixed": "#270057",
            "on-surface": "#e3e2e1",
            "surface-container": "#1f2020",
            "on-secondary": "#42008a",
            "on-tertiary": "#561f00",
            "on-background": "#e3e2e1",
            "surface-bright": "#383939",
            "on-secondary-fixed-variant": "#5d15b7",
            "on-primary": "#1e3434",
            "surface-container-lowest": "#0d0e0e",
            "on-primary-container": "#768e8e",
            "surface-tint": "#b3cbcb",
            "tertiary-fixed": "#ffdbcc",
            "tertiary-container": "#401500",
            "error": "#ffb4ab",
            "inverse-primary": "#4c6362"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "stack-gap-md": "16px",
            "grid-gutter": "12px",
            "container-padding": "20px",
            "card-padding": "20px",
            "stack-gap-lg": "24px"
          },
          "fontFamily": {
            "headline-md": ["Manrope"],
            "body-md": ["Manrope"],
            "label-sm": ["Manrope"],
            "body-lg": ["Manrope"],
            "headline-sm": ["Manrope"],
            "display-lg": ["Manrope"]
          },
          "fontSize": {
            "headline-md": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
            "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
            "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
            "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "500"}],
            "headline-sm": ["16px", {"lineHeight": "24px", "letterSpacing": "0.05em", "fontWeight": "600"}],
            "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}]
          }
        }
      }
    }
</script>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .zikir-menu-wrapper {
        font-family: "Manrope", sans-serif;
        -webkit-font-smoothing: antialiased;
        min-height: max(884px, 100dvh);
        padding: 0;
    }
    .spiritual-bg {
        background-image: linear-gradient(rgba(18, 20, 19, 0.85), rgba(18, 20, 19, 0.92)), url('{{ asset('images/islamic_bg.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }
    .glass-card {
        background: rgba(31, 32, 32, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .gradient-pagi {
        background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
    }
    .gradient-petang {
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
    }
    .gradient-sholat {
        background: linear-gradient(135deg, #0d9488 0%, #0891b2 100%);
    }
    .material-symbols-outlined {
        font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24;
    }
    /* Hide scrollbar for category list */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper zikir-menu-wrapper spiritual-bg text-on-surface">
    <!-- Header / TopAppBar -->
    <header class="w-full z-10 flex justify-between items-center px-container-padding py-4 bg-transparent pt-8">
        <div class="flex items-center gap-2" data-widget="pushmenu" role="button">
            <span class="material-symbols-outlined text-on-surface" style="cursor: pointer;">menu</span>
        </div>
        <h1 class="text-headline-sm font-headline-sm tracking-widest text-on-surface" style="color: white !important;">ZIKIR</h1>
        <div class="w-6"></div> <!-- Spacer for symmetry -->
    </header>

    <main class="pt-4 pb-32 px-container-padding">
        <!-- Bento Grid Section -->
        <section class="grid grid-cols-2 gap-grid-gutter mb-stack-gap-lg">
            <!-- Semua Zikir -->
            <a href="#" class="text-decoration-none">
                <div class="glass-card rounded-xl p-card-padding flex flex-col justify-between aspect-square active:scale-95 transition-transform duration-150 h-full">
                    <span class="material-symbols-outlined text-primary text-3xl">history</span>
                    <div>
                        <h3 class="text-body-lg font-body-lg text-white">Dzikir umum</h3>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">{{ $zikirUmum }} dzikir</p>
                    </div>
                </div>
            </a>
            
            <!-- Kesukaanku -->
            <a href="#" class="text-decoration-none">
                <div class="glass-card rounded-xl p-card-padding flex flex-col justify-between aspect-square active:scale-95 transition-transform duration-150 h-full">
                    <span class="material-symbols-outlined text-secondary text-3xl">star</span>
                    <div>
                        <h3 class="text-body-lg font-body-lg text-white">Kesukaanku</h3>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">{{ $totalFavorites > 0 ? $totalFavorites . ' favorit' : 'Tidak ada favorit' }}</p>
                    </div>
                </div>
            </a>

            <!-- Zikir Ba'da Sholat (Full Width/Special) -->
            <a href="#" class="col-span-2 text-decoration-none">
                <div class="gradient-sholat rounded-xl p-card-padding flex items-center justify-between shadow-lg active:scale-95 transition-transform duration-150">
                    <div class="flex flex-col">
                        <span class="material-symbols-outlined text-white/90 text-3xl mb-4">auto_awesome</span>
                        <h3 class="text-body-lg font-body-lg text-white">{{ $currentPrayerName }}</h3>
                        <p class="text-label-sm font-label-sm text-white/70">{{ $currentPrayerTime ? $currentPrayerTime : ($zikirSholat . ' dzikir') }}</p>
                    </div>
                    <div class="h-16 w-16 rounded-full border-2 border-white/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-2xl">arrow_forward_ios</span>
                    </div>
                </div>
            </a>

            <!-- Zikir Pagi -->
            <a href="#" class="text-decoration-none">
                <div class="gradient-pagi rounded-xl p-card-padding flex flex-col justify-between aspect-square shadow-lg active:scale-95 transition-transform duration-150 h-full">
                    <span class="material-symbols-outlined text-white/90 text-3xl">light_mode</span>
                    <div>
                        <h3 class="text-body-lg font-body-lg text-white">Zikir pagi</h3>
                        <p class="text-label-sm font-label-sm text-white/70">{{ $zikirPagi }} dzikir</p>
                    </div>
                </div>
            </a>

            <!-- Zikir Petang -->
            <a href="#" class="text-decoration-none">
                <div class="gradient-petang rounded-xl p-card-padding flex flex-col justify-between aspect-square shadow-lg active:scale-95 transition-transform duration-150 h-full">
                    <span class="material-symbols-outlined text-white/90 text-3xl">dark_mode</span>
                    <div>
                        <h3 class="text-body-lg font-body-lg text-white">Zikir petang</h3>
                        <p class="text-label-sm font-label-sm text-white/70">{{ $zikirPetang }} dzikir</p>
                    </div>
                </div>
            </a>
        </section>

        <!-- Kolom Kategori -->
        <section class="mb-stack-gap-lg">
            <h2 class="text-headline-sm font-headline-sm mb-4 text-on-surface-variant uppercase tracking-wider" style="color: #c2c8c7;">Kolom kategori</h2>
            <div class="flex overflow-x-auto gap-3 no-scrollbar pb-2" style="white-space: nowrap;">
                <button class="flex-shrink-0 px-6 py-3 rounded-full glass-card text-label-sm font-label-sm border-primary/20 text-primary">Dzikir umum</button>
                <button class="flex-shrink-0 px-6 py-3 rounded-full glass-card text-label-sm font-label-sm border-white/5 text-on-surface-variant">Dzikir pagi</button>
                <button class="flex-shrink-0 px-6 py-3 rounded-full glass-card text-label-sm font-label-sm border-white/5 text-on-surface-variant">Dzikir petang</button>
                <button class="flex-shrink-0 px-6 py-3 rounded-full glass-card text-label-sm font-label-sm border-white/5 text-on-surface-variant">Dzikir sholat 5 waktu</button>
            </div>
        </section>

        <!-- Aktivitas Anda -->
        <section class="mb-stack-gap-lg">
            <h2 class="text-headline-sm font-headline-sm mb-4 text-on-surface-variant uppercase tracking-wider" style="color: #c2c8c7;">Aktivitas Anda</h2>
            <div class="flex gap-grid-gutter">
                <!-- Aktivitas 1 -->
                <div class="flex-1 glass-card rounded-xl p-card-padding flex flex-col gap-4">
                    <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                    <div>
                        <h3 class="text-body-md font-body-md leading-tight text-white">{{ $recentActivity ? $recentActivity->zikir->title : 'Belum ada' }}</h3>
                        <p class="text-label-sm font-label-sm text-on-surface-variant mt-1">{{ $recentActivity ? $recentActivity->last_read_at->diffForHumans() : 'Belum ada aktivitas' }}</p>
                    </div>
                </div>
                
                <!-- Aktivitas 2 -->
                <div class="flex-1 glass-card rounded-xl p-card-padding flex flex-col gap-4">
                    <div class="h-10 w-10 rounded-full bg-secondary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-secondary">library_books</span>
                    </div>
                    <div>
                        <h3 class="text-body-md font-body-md text-white">Koleksi</h3>
                        <p class="text-label-sm font-label-sm text-on-surface-variant mt-1">{{ $totalCollection }} / {{ $totalZikir > 0 ? $totalZikir : 454 }}</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Bottom Navigation Shell -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 pb-4 pt-4 bg-surface/10 backdrop-blur-xl border-t border-white/5" style="background: rgba(18,20,19,0.5);">
        <!-- Home / Active -->
        <a href="{{ route('dashboard') }}" class="text-decoration-none">
            <button class="flex flex-col items-center justify-center bg-primary-container/30 text-on-primary-container rounded-full p-3 scale-90">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1; color: #b3cbcb;">home</span>
            </button>
        </a>
        
        <!-- Central Action Button -->
        <div class="relative -top-8">
            <button class="h-16 w-16 rounded-full bg-primary flex items-center justify-center shadow-2xl shadow-primary/20 active:scale-90 transition-transform">
                <span class="material-symbols-outlined text-on-primary text-3xl" style="color: #1e3434;">more_horiz</span>
            </button>
        </div>
        
        <!-- Close / Exit -->
        <button class="flex flex-col items-center justify-center text-on-surface-variant p-3 hover:text-primary transition-colors" onclick="history.back()">
            <span class="material-symbols-outlined" style="color: #c2c8c7;">close</span>
        </button>
    </nav>
</div>
@endsection

@push('scripts')
<script>
    // Subtle parallax and interaction logic
    document.addEventListener('mousemove', (e) => {
      const bg = document.querySelector('.spiritual-bg');
      if(bg) {
          const amount = 10;
          const x = (e.clientX / window.innerWidth - 0.5) * amount;
          const y = (e.clientY / window.innerHeight - 0.5) * amount;
          bg.style.backgroundPosition = `calc(50% + ${x}px) calc(50% + ${y}px)`;
      }
    });

    // Tap feedback for cards
    document.querySelectorAll('.active\\:scale-95').forEach(card => {
      card.addEventListener('touchstart', () => {
        card.style.opacity = '0.8';
      });
      card.addEventListener('touchend', () => {
        card.style.opacity = '1';
      });
    });
</script>
@endpush