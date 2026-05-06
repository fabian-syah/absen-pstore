@extends('layout.master')

@section('title', 'Artisan Web GUI - Developer Cockpit')

@section('content')
<!-- Google Fonts Import for Premium Typography -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap');

    /* CSS Reset/Overrides for Flat Blue & White Light Theme */
    .artisan-dashboard {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }

    /* Flat Light Theme Cards */
    .light-theme-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        transition: all 0.25s ease-in-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .light-theme-card:hover {
        transform: translateY(-2px);
        border-color: #93c5fd !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.05);
    }

    /* Clean Flat Header Card */
    .header-card {
        background-color: #f0f7ff;
        border: 1px solid #bfdbfe !important;
        border-radius: 16px !important;
    }

    /* Uniform Blue & White Category Tabs */
    .custom-pills .nav-link {
        color: #475569;
        background-color: transparent;
        border-radius: 10px;
        padding: 10px 18px !important;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .custom-pills .nav-link:hover {
        color: #0d6efd;
        background-color: #e0f2fe;
    }

    .custom-pills .nav-link.active {
        color: #ffffff !important;
        background-color: #0d6efd !important;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.15) !important;
        border: 1px solid #0d6efd;
    }

    /* Flat Command Grid Card */
    .command-item-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
    }

    .command-item-card:hover {
        background-color: #f8fafc;
        border-color: #bfdbfe;
    }

    /* Solid Blue Button */
    .btn-solid-blue {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }

    .btn-solid-blue:hover {
        background-color: #0b5ed7 !important;
        transform: translateY(-1px);
    }

    /* Obsidian Terminal */
    .obsidian-terminal {
        background-color: #0f172a;
        border: 1px solid #1e293b;
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.15);
    }

    .terminal-body {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: 0.825rem;
        line-height: 1.6;
        color: #38bdf8;
        background-color: #070a13;
        height: 480px;
        overflow-y: auto;
        position: relative;
    }

    /* Custom Scrollbar for Terminal */
    .terminal-body::-webkit-scrollbar {
        width: 6px;
    }
    .terminal-body::-webkit-scrollbar-track {
        background: #070a13;
    }
    .terminal-body::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 4px;
    }

    /* Blue Status Ping Dot */
    .status-pulse {
        width: 8px;
        height: 8px;
        background-color: #0d6efd;
        border-radius: 50%;
        display: inline-block;
        position: relative;
    }
    .status-pulse::after {
        content: '';
        width: 100%;
        height: 100%;
        background-color: #0d6efd;
        border-radius: 50%;
        position: absolute;
        top: 0; left: 0;
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.5); opacity: 1; }
        80%, 100% { transform: scale(2.5); opacity: 0; }
    }

    /* Responsive gaps */
    .gap-2 { gap: 8px; }
</style>

<div class="content-wrapper artisan-dashboard pb-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card header-card border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <span class="badge px-3 py-2 mb-2 rounded-pill font-weight-bold" style="background-color: rgba(13, 110, 253, 0.08); color: #0d6efd;">
                                <i class="mdi mdi-shield-crown mr-1"></i> Super Admin Dashboard
                            </span>
                            <h2 class="font-weight-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Artisan Web GUI</h2>
                            <p class="text-muted mb-0 small" style="font-size: 0.88rem; line-height: 1.5;">
                                Panel kendali developer instan. Jalankan cache, optimasi sistem, migrasi database, dan tarik pembaruan Git secara langsung tanpa login SSH.
                            </p>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0 d-flex justify-content-md-end align-items-center">
                            <div class="d-flex align-items-center px-3 py-2 rounded-pill shadow-xs" style="background-color: #ffffff; border: 1px solid #bfdbfe;">
                                <span class="status-pulse mr-2"></span>
                                <small class="font-weight-bold text-primary" style="margin-left: 8px; font-size: 0.8rem;">Cockpit Active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Cockpit Health Panel -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="card light-theme-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="p-2.5 rounded-lg mr-3" style="background-color: rgba(13, 110, 253, 0.06); border-radius: 12px; padding: 10px;">
                        <i class="mdi mdi-language-php text-primary" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block small" style="font-size: 0.72rem;">PHP Version</small>
                        <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ PHP_VERSION }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="card light-theme-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="p-2.5 rounded-lg mr-3" style="background-color: rgba(13, 110, 253, 0.06); border-radius: 12px; padding: 10px;">
                        <i class="mdi mdi-laravel text-primary" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block small" style="font-size: 0.72rem;">Laravel Version</small>
                        <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">v{{ app()->version() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="card light-theme-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="p-2.5 rounded-lg mr-3" style="background-color: rgba(13, 110, 253, 0.06); border-radius: 12px; padding: 10px;">
                        <i class="mdi mdi-server text-primary" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block small" style="font-size: 0.72rem;">Environment</small>
                        <span class="font-weight-bold text-primary text-capitalize" style="font-size: 0.9rem;">{{ app()->environment() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="card light-theme-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="p-2.5 rounded-lg mr-3" style="background-color: rgba(13, 110, 253, 0.06); border-radius: 12px; padding: 10px;">
                        <i class="mdi mdi-git text-primary" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block small" style="font-size: 0.72rem;">Repository</small>
                        <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">GitHub Main</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="row">
        <!-- Predefined Quick Commands (Left / Responsive Top) -->
        <div class="col-lg-7 mb-4">
            <div class="card light-theme-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-2 text-white mr-3 shadow-xs" style="border-radius: 10px; background-color: #0d6efd; padding: 10px;">
                            <i class="mdi mdi-playlist-play" style="font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0">Daftar Kontrol Perintah</h4>
                            <p class="text-muted small mb-0">Pilih kategori dan jalankan perintah sekali klik.</p>
                        </div>
                    </div>

                    <!-- Category Tab Pills -->
                    <ul class="nav nav-pills custom-pills mb-4 d-flex flex-wrap gap-2 p-1.5" id="commandTabs" role="tablist" style="background-color: #f1f5f9; border-radius: 12px; border: none;">
                        @foreach($predefinedCommands as $index => $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($index === 0) active @endif border-0" 
                                        id="tab-{{ $index }}" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#panel-{{ $index }}" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="panel-{{ $index }}" 
                                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    <i class="mdi {{ $category['icon'] }} mr-1"></i> {{ $category['category'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Category Tab Content -->
                    <div class="tab-content" id="commandTabsContent">
                        @foreach($predefinedCommands as $index => $category)
                            <div class="tab-pane fade @if($index === 0) show active @endif" 
                                 id="panel-{{ $index }}" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-{{ $index }}">
                                <div class="row">
                                    @foreach($category['commands'] as $cmd)
                                        <div class="col-12 mb-3">
                                            <div class="p-3 command-item-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                                                <div class="mb-3 mb-sm-0 pr-sm-3">
                                                    <div class="font-weight-bold text-dark mb-1 d-flex flex-wrap align-items-center">
                                                        <span style="font-size: 0.95rem;">{{ $cmd['name'] }}</span>
                                                        <span class="badge bg-light text-primary border rounded font-monospace py-1 px-2 small style-chip" style="font-size: 0.72rem; margin-left: 8px; border-color: #bfdbfe !important;">
                                                            php artisan {{ $cmd['command'] }}
                                                        </span>
                                                    </div>
                                                    <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.4;">{{ $cmd['desc'] }}</p>
                                                </div>
                                                <button class="btn btn-sm btn-solid-blue px-3 py-2 w-100 w-sm-auto" 
                                                        data-command="{{ $cmd['command'] }}">
                                                    <i class="mdi mdi-play mr-1"></i> Jalankan
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Console & Custom Command (Right / Responsive Bottom) -->
        <div class="col-lg-5 mb-4">
            <div class="d-flex flex-column h-100">
                <!-- Custom Command Card -->
                <div class="card light-theme-card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded-lg mr-3 text-primary shadow-xs" style="background-color: rgba(13, 110, 253, 0.06); border-radius: 10px; padding: 10px;">
                                <i class="mdi mdi-console-line" style="font-size: 1.4rem;"></i>
                            </div>
                            <div>
                                <h4 class="font-weight-bold text-dark mb-0">Custom Command</h4>
                                <p class="text-muted small mb-0">Jalankan perintah kustom buatan Anda secara manual.</p>
                            </div>
                        </div>

                        <form id="customCommandForm" class="mt-3">
                            @csrf
                            <div class="input-group mb-2 shadow-xs" style="border-radius: 10px; overflow: hidden; border: 1px solid #cbd5e1;">
                                <span class="input-group-text border-0 bg-light font-weight-bold text-muted small" style="border-radius: 10px 0 0 10px;">
                                    php artisan
                                </span>
                                <input type="text" 
                                       id="customCommandInput" 
                                       class="form-control border-0 bg-light py-2.5 font-monospace text-dark" 
                                       placeholder="cache:clear" 
                                       style="box-shadow: none; font-size: 0.85rem;">
                                <button type="submit" 
                                        class="btn btn-primary px-4 border-0 font-weight-bold" 
                                        style="border-radius: 0 10px 10px 0; background-color: #0d6efd;">
                                    <i class="mdi mdi-play"></i> Run
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                                <i class="mdi mdi-shield-check text-primary mr-1"></i> Hanya perintah aman yang divalidasi sistem yang dapat tereksekusi.
                            </small>
                        </form>
                    </div>
                </div>

                <!-- Obsidian Terminal Card (Matches Requested 'Dark Terminal') -->
                <div class="card obsidian-terminal border-0 flex-grow-1 d-flex flex-column">
                    <!-- Terminal Header -->
                    <div class="d-flex justify-content-between align-items-center px-4 py-3" style="background-color: #090d16; border-bottom: 1px solid #1e293b;">
                        <div class="d-flex align-items-center">
                            <span class="mr-1" style="width: 10px; height: 10px; border-radius: 50%; background-color: #38bdf8; display: inline-block;"></span>
                            <span class="mr-1" style="width: 10px; height: 10px; border-radius: 50%; background-color: #38bdf8; display: inline-block;"></span>
                            <span class="mr-3" style="width: 10px; height: 10px; border-radius: 50%; background-color: #38bdf8; display: inline-block;"></span>
                            <span class="font-monospace small font-weight-bold" style="color: #94a3b8; font-size: 0.75rem; margin-left: 6px;">
                                <i class="mdi mdi-console mr-1"></i> console_output.log
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <!-- Terminal FontSize Adjuster -->
                            <div class="btn-group btn-group-sm mr-2" style="margin-right: 12px;">
                                <button type="button" id="zoomOutBtn" class="btn p-1 text-muted" title="Zoom Out" style="background: none; border: none; color: #94a3b8 !important;">
                                    <i class="mdi mdi-magnify-minus-outline" style="font-size: 1.1rem;"></i>
                                </button>
                                <button type="button" id="zoomInBtn" class="btn p-1 text-muted ml-1" title="Zoom In" style="background: none; border: none; color: #94a3b8 !important;">
                                    <i class="mdi mdi-magnify-plus-outline" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                            <button id="clearConsoleBtn" class="btn py-1 px-2.5 font-monospace" style="font-size: 0.72rem; background: rgba(255,255,255,0.04); color: #cbd5e1; border: 1px solid #1e293b; border-radius: 8px;">
                                <i class="mdi mdi-delete-outline"></i> Clear
                            </button>
                            <button id="copyConsoleBtn" class="btn py-1 px-2.5 font-monospace ml-1" style="font-size: 0.72rem; background: rgba(255,255,255,0.04); color: #cbd5e1; border: 1px solid #1e293b; border-radius: 8px; margin-left: 6px;">
                                <i class="mdi mdi-content-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Terminal Log Area -->
                    <div class="card-body p-0 d-flex flex-column flex-grow-1">
                        <div id="terminalOutput" class="terminal-body p-4 flex-grow-1">
<span style="color: #64748b;">// System initialized. Menunggu perintah dijalankan...
// Output dari perintah Artisan akan tercetak di sini dengan rapi.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const terminalOutput = document.getElementById('terminalOutput');
        const clearConsoleBtn = document.getElementById('clearConsoleBtn');
        const copyConsoleBtn = document.getElementById('copyConsoleBtn');
        const customCommandForm = document.getElementById('customCommandForm');
        const customCommandInput = document.getElementById('customCommandInput');
        const zoomInBtn = document.getElementById('zoomInBtn');
        const zoomOutBtn = document.getElementById('zoomOutBtn');

        let currentFontSize = 0.825; // in rem

        // Zoom In Terminal Text
        zoomInBtn.addEventListener('click', function() {
            if (currentFontSize < 1.1) {
                currentFontSize += 0.05;
                terminalOutput.style.fontSize = currentFontSize + 'rem';
            }
        });

        // Zoom Out Terminal Text
        zoomOutBtn.addEventListener('click', function() {
            if (currentFontSize > 0.65) {
                currentFontSize -= 0.05;
                terminalOutput.style.fontSize = currentFontSize + 'rem';
            }
        });

        // Print To Terminal Log
        function printToTerminal(text, type = 'info') {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
            let prefix = `<span style="color: #64748b;">[${timeStr}]</span> `;
            
            let color = '#38bdf8'; // Default blue
            if (type === 'success') color = '#34d399'; // Emerald Green
            if (type === 'error') color = '#f87171'; // Red Coral
            if (type === 'warn') color = '#f59e0b'; // Amber Gold
            if (type === 'sys') color = '#a78bfa'; // Violet Purple

            const formattedText = `<div class="mb-2">${prefix}<span style="color: ${color};">${text}</span></div>`;
            
            if (terminalOutput.innerHTML.includes('// System initialized')) {
                terminalOutput.innerHTML = '';
            }

            terminalOutput.innerHTML += formattedText;
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }

        // Execute Command via AJAX
        function executeCommand(commandString) {
            printToTerminal(`Menjalankan perintah: <strong>php artisan ${commandString}</strong>`, 'sys');
            
            // Disable all buttons to prevent double execution
            const runButtons = document.querySelectorAll('.btn-run-command, .btn-solid-blue, #customCommandForm button');
            runButtons.forEach(btn => btn.disabled = true);

            fetch('{{ route("admin.artisan.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ command: commandString })
            })
            .then(response => response.json().then(data => ({ status: response.status, data })))
            .then(res => {
                const data = res.data;
                if (res.status === 200 && data.success) {
                    printToTerminal(`Selesai mengeksekusi (Kode Keluar: 0)`, 'success');
                    printToTerminal(data.output, 'info');
                    
                    Swal.fire({
                        title: 'Eksekusi Berhasil!',
                        text: `php artisan ${commandString} berhasil dijalankan.`,
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    const errorMsg = data.output || 'Gagal mengeksekusi perintah.';
                    printToTerminal(`Perintah gagal dijalankan atau dibatalkan!`, 'error');
                    printToTerminal(errorMsg, 'error');
                    
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.output || 'Perintah ditolak oleh sistem keamanan.',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                printToTerminal(`Koneksi Terputus: Tidak dapat menghubungi server web lokal.`, 'error');
                
                Swal.fire({
                    title: 'Kesalahan Sistem!',
                    text: 'Tidak dapat berkomunikasi dengan server.',
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                });
            })
            .finally(() => {
                // Re-enable buttons
                runButtons.forEach(btn => btn.disabled = false);
            });
        }

        // Click handler for Quick Commands
        document.querySelectorAll('.btn-solid-blue').forEach(button => {
            button.addEventListener('click', function() {
                const command = this.getAttribute('data-command');
                
                // Special safety confirmation for critical database operations
                if (command.includes('migrate') || command.includes('seed')) {
                    Swal.fire({
                        title: 'Konfirmasi Migrasi Database',
                        text: "Apakah Anda yakin? Tindakan ini dapat memodifikasi skema tabel database produksi Anda secara permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#475569',
                        confirmButtonText: 'Ya, Eksekusi!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executeCommand(command);
                        }
                    });
                } else {
                    executeCommand(command);
                }
            });
        });

        // Submit Custom Command form
        customCommandForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const command = customCommandInput.value.trim();
            if (command === '') {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Silakan tulis perintah kustom Anda terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'Oke'
                });
                return;
            }
            
            executeCommand(command);
            customCommandInput.value = '';
        });

        // Clear Console
        clearConsoleBtn.addEventListener('click', function() {
            terminalOutput.innerHTML = '<span style="color: #64748b;">// Console cleared.<br>// Menunggu perintah dijalankan...</span>';
            Swal.fire({
                title: 'Dibersihkan',
                text: 'Output konsol berhasil dibersihkan.',
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        });

        // Copy Console Text
        copyConsoleBtn.addEventListener('click', function() {
            const textToCopy = terminalOutput.innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                Swal.fire({
                    title: 'Disalin!',
                    text: 'Log terminal telah berhasil disalin.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            }).catch(err => {
                console.error('Gagal menyalin text: ', err);
            });
        });
    });
</script>
@endpush
@endsection
