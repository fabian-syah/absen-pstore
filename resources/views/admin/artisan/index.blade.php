@extends('layout.master')

@section('title', 'Artisan Command Dashboard')

@section('content')
<div class="content-wrapper">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <span class="badge bg-primary-light text-primary px-3 py-2 mb-2 rounded-pill font-weight-bold" style="background-color: rgba(13, 110, 253, 0.15);">
                                <i class="mdi mdi-console-line mr-1"></i> Developer Console
                            </span>
                            <h2 class="font-weight-bold mb-1" style="color: #60a5fa;">Artisan Web GUI</h2>
                            <p class="text-slate-400 mb-0 small" style="color: #94a3b8;">
                                Jalankan perintah Artisan Laravel Anda dengan satu kali klik tanpa perlu membuka Terminal SSH/CMD.
                            </p>
                        </div>
                        <div class="mt-3 mt-md-0 d-flex align-items-center">
                            <div class="d-flex align-items-center bg-dark px-3 py-2 rounded-pill shadow-sm" style="background-color: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(255, 255, 255, 0.1);">
                                <span class="position-relative d-inline-flex mr-2">
                                    <span class="ping-dot"></span>
                                    <span class="ping-dot-pulse"></span>
                                </span>
                                <small class="font-weight-bold" style="color: #34d399; margin-left: 8px;">System Active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Predefined Quick Commands Column -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-2 rounded-lg bg-light-primary mr-3" style="background-color: rgba(13, 110, 253, 0.08); border-radius: 10px;">
                            <i class="mdi mdi-play-network-outline text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h4 class="card-title font-weight-bold mb-0">Quick Action Commands</h4>
                            <p class="text-muted small mb-0">Klik tombol di bawah untuk langsung mengeksekusi perintah Artisan.</p>
                        </div>
                    </div>

                    <div class="accordion" id="commandAccordion" style="border: none;">
                        @foreach($predefinedCommands as $index => $category)
                            <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px; overflow: hidden; background-color: #f8fafc;">
                                <h2 class="accordion-header" id="heading-{{ $index }}">
                                    <button class="accordion-button @if($index !== 0) collapsed @endif font-weight-bold" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse-{{ $index }}" 
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                            aria-controls="collapse-{{ $index }}"
                                            style="background-color: #f8fafc; color: #1e293b; box-shadow: none; padding: 16px 20px;">
                                        <i class="mdi {{ $category['icon'] }} mr-2" style="color: {{ $category['color'] }}; font-size: 1.1rem; margin-right: 10px;"></i>
                                        {{ $category['category'] }}
                                    </button>
                                </h2>
                                <div id="collapse-{{ $index }}" 
                                     class="accordion-collapse collapse @if($index === 0) show @endif" 
                                     aria-labelledby="heading-{{ $index }}" 
                                     data-bs-parent="#commandAccordion">
                                    <div class="accordion-body bg-white p-3">
                                        <div class="row">
                                            @foreach($category['commands'] as $cmd)
                                                <div class="col-12 mb-3">
                                                    <div class="p-3 border rounded-lg h-100 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center hover-card" 
                                                         style="border-radius: 10px; transition: all 0.2s ease-in-out; border-color: #e2e8f0 !important;">
                                                        <div class="mb-3 mb-sm-0 mr-3">
                                                            <div class="font-weight-bold text-dark mb-1 d-flex align-items-center">
                                                                {{ $cmd['name'] }}
                                                                <code class="ml-2 bg-light px-2 py-0.5 rounded text-primary small border" style="font-size: 0.75rem; margin-left: 8px;">php artisan {{ $cmd['command'] }}</code>
                                                            </div>
                                                            <p class="text-muted small mb-0" style="line-height: 1.4;">{{ $cmd['desc'] }}</p>
                                                        </div>
                                                        <button class="btn btn-sm px-3 py-2 btn-run-command font-weight-bold" 
                                                                data-command="{{ $cmd['command'] }}" 
                                                                style="background-color: {{ $category['color'] }}; color: #fff; border-radius: 8px; white-space: nowrap; transition: all 0.2s;">
                                                            <i class="mdi mdi-play mr-1"></i> Jalankan
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Console & Custom Command Column -->
        <div class="col-lg-5 mb-4">
            <div class="d-flex flex-column h-100">
                <!-- Custom Command Input -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded-lg bg-light-warning mr-3" style="background-color: rgba(253, 126, 20, 0.08); border-radius: 10px;">
                                <i class="mdi mdi-keyboard-outline text-warning" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h4 class="card-title font-weight-bold mb-0">Custom Artisan Command</h4>
                                <p class="text-muted small mb-0">Tulis perintah kustom secara manual dengan argumen standar.</p>
                            </div>
                        </div>

                        <form id="customCommandForm" class="mt-3">
                            @csrf
                            <div class="input-group mb-2 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text border-0 bg-light font-weight-bold text-muted" style="border-radius: 10px 0 0 10px;">
                                    php artisan
                                </span>
                                <input type="text" 
                                       id="customCommandInput" 
                                       class="form-control border-0 bg-light py-2.5 font-monospace text-dark" 
                                       placeholder="config:cache" 
                                       style="box-shadow: none;">
                                <button type="submit" 
                                        class="btn btn-primary px-4 border-0 font-weight-bold" 
                                        style="border-radius: 0 10px 10px 0;">
                                    <i class="mdi mdi-play"></i> Run
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="mdi mdi-shield-outline text-success"></i> Hanya perintah aman yang diizinkan (misal: <code>config:*</code>, <code>cache:*</code>, <code>route:*</code>, <code>migrate</code>).
                            </small>
                        </form>
                    </div>
                </div>

                <!-- Retro Terminal Console Output -->
                <div class="card border-0 shadow-sm flex-grow-1" style="border-radius: 15px; background-color: #0b0e14; overflow: hidden;">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center px-4 py-3" style="background-color: #121824;">
                        <div class="d-flex align-items-center">
                            <span class="terminal-btn-red mr-1"></span>
                            <span class="terminal-btn-yellow mr-1"></span>
                            <span class="terminal-btn-green mr-3"></span>
                            <span class="font-monospace text-slate-300 small font-weight-bold" style="color: #94a3b8; font-size: 0.8rem; margin-left: 8px;">
                                <i class="mdi mdi-console mr-1"></i> output_terminal.log
                            </span>
                        </div>
                        <div class="d-flex">
                            <button id="clearConsoleBtn" class="btn btn-xs btn-outline-secondary py-1 px-2 font-monospace" style="font-size: 0.75rem; border-color: rgba(255,255,255,0.1); color: #94a3b8; border-radius: 6px;">
                                <i class="mdi mdi-delete-sweep"></i> Clear
                            </button>
                            <button id="copyConsoleBtn" class="btn btn-xs btn-outline-secondary py-1 px-2 font-monospace ml-2" style="font-size: 0.75rem; border-color: rgba(255,255,255,0.1); color: #94a3b8; border-radius: 6px; margin-left: 6px;">
                                <i class="mdi mdi-content-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column" style="height: 400px;">
                        <div id="terminalOutput" class="font-monospace flex-grow-1 overflow-auto p-3" 
                             style="background-color: #080b10; color: #38bdf8; border-radius: 10px; font-size: 0.85rem; line-height: 1.6; white-space: pre-wrap; word-break: break-all;">
<span style="color: #64748b;">// Menunggu perintah dijalankan...
// Output dari perintah Artisan akan muncul di sini secara interaktif.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ping Status Animation */
    .ping-dot {
        height: 10px;
        width: 10px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
    }
    .ping-dot-pulse {
        position: absolute;
        top: 0;
        left: 0;
        height: 10px;
        width: 10px;
        background-color: #10b981;
        border-radius: 50%;
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    @keyframes ping {
        75%, 100% {
            transform: scale(2.5);
            opacity: 0;
        }
    }

    /* Terminal Dots */
    .terminal-btn-red, .terminal-btn-yellow, .terminal-btn-green {
        height: 12px;
        width: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    .terminal-btn-red { background-color: #ef4444; }
    .terminal-btn-yellow { background-color: #f59e0b; }
    .terminal-btn-green { background-color: #10b981; }

    /* Custom Transitions & Hover States */
    .hover-card:hover {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .btn-run-command:hover {
        filter: brightness(1.1);
        transform: scale(1.03);
    }
    #terminalOutput::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    #terminalOutput::-webkit-scrollbar-track {
        background: #080b10;
    }
    #terminalOutput::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 3px;
    }
    #terminalOutput::-webkit-scrollbar-thumb:hover {
        background: #334155;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const terminalOutput = document.getElementById('terminalOutput');
        const clearConsoleBtn = document.getElementById('clearConsoleBtn');
        const copyConsoleBtn = document.getElementById('copyConsoleBtn');
        const customCommandForm = document.getElementById('customCommandForm');
        const customCommandInput = document.getElementById('customCommandInput');

        // Fungsi mencetak log di terminal dengan timestamp
        function printToTerminal(text, type = 'info') {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
            let prefix = `<span style="color: #64748b;">[${timeStr}]</span> `;
            
            let color = '#38bdf8'; // Default blue
            if (type === 'success') color = '#34d399'; // Green
            if (type === 'error') color = '#f87171'; // Red
            if (type === 'warn') color = '#fbbf24'; // Yellow
            if (type === 'sys') color = '#a78bfa'; // Purple

            const formattedText = `<div class="mb-1">${prefix}<span style="color: ${color};">${text}</span></div>`;
            
            // Jika terminal masih default, bersihkan dulu
            if (terminalOutput.innerHTML.includes('// Menunggu perintah')) {
                terminalOutput.innerHTML = '';
            }

            terminalOutput.innerHTML += formattedText;
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }

        // Jalankan perintah via Ajax
        function executeCommand(commandString) {
            printToTerminal(`Menjalankan perintah: <strong>php artisan ${commandString}</strong>`, 'sys');
            
            // Disable all buttons during execution
            const runButtons = document.querySelectorAll('.btn-run-command, #customCommandForm button');
            runButtons.forEach(btn => btn.disabled = true);

            fetch('{{ route("admin.artisan.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ command: commandString })
            })
            .then(response => {
                // Parse JSON
                return response.json().then(data => {
                    return { status: response.status, data: data };
                });
            })
            .then(res => {
                const data = res.data;
                if (res.status === 200 && data.success) {
                    printToTerminal(`Berhasil mengeksekusi dengan Kode Keluar: 0`, 'success');
                    printToTerminal(data.output, 'info');
                    
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Perintah berhasil dijalankan.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    const errorMsg = data.output || 'Gagal mengeksekusi perintah.';
                    printToTerminal(`Gagal mengeksekusi perintah!`, 'error');
                    printToTerminal(errorMsg, 'error');
                    
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.output || 'Perintah ditolak atau bermasalah.',
                        icon: 'error',
                        confirmButtonText: 'Oke'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                printToTerminal(`Sistem Error: Gagal menghubungi server web atau koneksi terputus.`, 'error');
                
                Swal.fire({
                    title: 'System Error!',
                    text: 'Tidak dapat terhubung ke server.',
                    icon: 'error',
                    confirmButtonText: 'Oke'
                });
            })
            .finally(() => {
                // Re-enable all buttons
                runButtons.forEach(btn => btn.disabled = false);
            });
        }

        // Handle Quick Action Click
        document.querySelectorAll('.btn-run-command').forEach(button => {
            button.addEventListener('click', function() {
                const command = this.getAttribute('data-command');
                
                // Konfirmasi khusus jika perintah berpotensi melakukan migrasi database
                if (command.includes('migrate') || command.includes('seed')) {
                    Swal.fire({
                        title: 'Yakin menjalankan migrasi?',
                        text: "Tindakan ini akan mempengaruhi database produksi Anda!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Jalankan!',
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

        // Handle Custom Command Form Submit
        customCommandForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const command = customCommandInput.value.trim();
            if (command === '') {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Tuliskan perintah terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'Oke'
                });
                return;
            }
            
            executeCommand(command);
            customCommandInput.value = ''; // Clear input
        });

        // Clear Console Button
        clearConsoleBtn.addEventListener('click', function() {
            terminalOutput.innerHTML = '<span style="color: #64748b;">// Console cleared.<br>// Menunggu perintah dijalankan...</span>';
            Swal.fire({
                title: 'Cleared',
                text: 'Terminal output dibersihkan.',
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        });

        // Copy Console Button
        copyConsoleBtn.addEventListener('click', function() {
            const textToCopy = terminalOutput.innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Output terminal berhasil disalin.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
            });
        });
    });
</script>
@endpush
@endsection
