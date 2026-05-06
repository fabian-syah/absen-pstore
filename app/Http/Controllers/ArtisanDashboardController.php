<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArtisanDashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Artisan Web GUI.
     */
    public function index()
    {
        // Pastikan hanya super admin yang bisa mengakses
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Daftar perintah bawaan yang siap pakai (quick-click)
        $predefinedCommands = [
            [
                'category' => 'Optimization & Performance',
                'icon' => 'mdi-rocket-launch-outline',
                'color' => '#0d6efd',
                'commands' => [
                    [
                        'name' => 'Optimize All',
                        'command' => 'optimize',
                        'desc' => 'Cache konfigurasi dan rute untuk performa maksimal.',
                    ],
                    [
                        'name' => 'Clear All Optimizations',
                        'command' => 'optimize:clear',
                        'desc' => 'Hapus semua file cache hasil optimasi.',
                    ],
                ]
            ],
            [
                'category' => 'Configuration Management',
                'icon' => 'mdi-cog-outline',
                'color' => '#198754',
                'commands' => [
                    [
                        'name' => 'Cache Config',
                        'command' => 'config:cache',
                        'desc' => 'Gabungkan konfigurasi ke dalam satu file cache agar load web cepat.',
                    ],
                    [
                        'name' => 'Clear Config Cache',
                        'command' => 'config:clear',
                        'desc' => 'Hapus file cache konfigurasi (baca ulang file .env secara langsung).',
                    ],
                ]
            ],
            [
                'category' => 'Application Cache',
                'icon' => 'mdi-cached',
                'color' => '#fd7e14',
                'commands' => [
                    [
                        'name' => 'Clear App Cache',
                        'command' => 'cache:clear',
                        'desc' => 'Bersihkan seluruh cache aplikasi (data session, query, dll).',
                    ],
                ]
            ],
            [
                'category' => 'Routing & Views',
                'icon' => 'mdi-routes',
                'color' => '#6f42c1',
                'commands' => [
                    [
                        'name' => 'Cache Routes',
                        'command' => 'route:cache',
                        'desc' => 'Cache rute-rute web untuk performa registrasi URL cepat.',
                    ],
                    [
                        'name' => 'Clear Routes Cache',
                        'command' => 'route:clear',
                        'desc' => 'Hapus cache pendaftaran rute.',
                    ],
                    [
                        'name' => 'Cache Blade Views',
                        'command' => 'view:cache',
                        'desc' => 'Pre-compile seluruh file tampilan Blade.',
                    ],
                    [
                        'name' => 'Clear Blade Views',
                        'command' => 'view:clear',
                        'desc' => 'Hapus kompilasi cache tampilan Blade.',
                    ],
                ]
            ],
            [
                'category' => 'Database Utilities',
                'icon' => 'mdi-database-outline',
                'color' => '#dc3545',
                'commands' => [
                    [
                        'name' => 'Run Migrations',
                        'command' => 'migrate --force',
                        'desc' => 'Jalankan migrasi database yang belum dieksekusi (dengan flag force).',
                    ],
                    [
                        'name' => 'Database Seeding',
                        'command' => 'db:seed --force',
                        'desc' => 'Jalankan seeder untuk mengisi data awal database.',
                    ],
                ]
            ],
            [
                'category' => 'Version Control (Git)',
                'icon' => 'mdi-git',
                'color' => '#f1502f',
                'commands' => [
                    [
                        'name' => 'Git Pull Updates',
                        'command' => 'git pull',
                        'desc' => 'Tarik pembaruan kode terbaru dari repositori GitHub secara otomatis.',
                    ],
                ]
            ],
        ];

        return view('admin.artisan.index', compact('predefinedCommands'));
    }

    /**
     * Jalankan perintah Artisan yang dipilih/diinputkan.
     */
    public function run(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'output' => 'Error: Unauthorized access.'
            ], 403);
        }

        $commandString = trim($request->input('command'));

        if (empty($commandString)) {
            return response()->json([
                'success' => false,
                'output' => 'Error: Perintah tidak boleh kosong.'
            ], 400);
        }

        if (!$this->isCommandSafe($commandString)) {
            return response()->json([
                'success' => false,
                'output' => "Error: Perintah '" . htmlspecialchars($commandString) . "' dilarang demi keamanan sistem."
            ], 400);
        }

        // Khusus untuk perintah git pull
        if ($commandString === 'git pull') {
            try {
                Log::info('Git Pull run by Admin', [
                    'admin_id' => Auth::id(),
                    'admin_name' => Auth::user()->name,
                ]);

                // Cek apakah shell_exec diaktifkan di server Hostinger
                $disabledFunctions = explode(',', ini_get('disable_functions'));
                if (!function_exists('shell_exec') || in_array('shell_exec', array_map('trim', $disabledFunctions))) {
                    return response()->json([
                        'success' => false,
                        'output' => "Gagal menjalankan git pull!\n\nError: Fungsi shell_exec() dinonaktifkan pada server Hostinger Anda demi alasan keamanan.\n\nCara Mengaktifkan di Hostinger hPanel Anda:\n1. Masuk ke hPanel Hostinger.\n2. Buka menu Advanced -> PHP Configuration.\n3. Pilih tab PHP Options.\n4. Cari bagian 'disable_functions' dan hapus kata 'shell_exec' dari daftar tersebut.\n5. Klik Save / Simpan di bagian bawah.\n6. Refresh halaman web ini dan coba klik tombol Git Pull kembali."
                    ]);
                }

                $output = shell_exec('git pull 2>&1');

                return response()->json([
                    'success' => true,
                    'exit_code' => 0,
                    'output' => $output ?: "Git pull berhasil dijalankan tanpa output tekstual."
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'output' => "Gagal menjalankan git pull!\nError: " . $e->getMessage()
                ], 500);
            }
        }

        try {
            Log::info('Artisan Command Web GUI run by Admin', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'command' => $commandString
            ]);

            // Eksekusi perintah artisan
            // Pisahkan command name dengan arguments jika diinput
            $parts = explode(' ', $commandString);
            $command = $parts[0];
            
            // Parsing argumen & opsi sederhana
            $parameters = [];
            for ($i = 1; $i < count($parts); $i++) {
                $part = $parts[$i];
                if (str_starts_with($part, '--')) {
                    $optParts = explode('=', substr($part, 2));
                    $optName = $optParts[0];
                    $optVal = isset($optParts[1]) ? $optParts[1] : true;
                    $parameters['--' . $optName] = $optVal;
                } elseif (str_starts_with($part, '-')) {
                    $optName = substr($part, 1);
                    $parameters['-' . $optName] = true;
                } else {
                    // Positional argument
                    $parameters[] = $part;
                }
            }

            // Jalankan command
            $exitCode = Artisan::call($command, $parameters);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
                'output' => $output ?: "Perintah berhasil dijalankan tanpa output tekstual."
            ]);

        } catch (\Exception $e) {
            Log::error('Artisan Command Web GUI Error', [
                'admin_id' => Auth::id(),
                'command' => $commandString,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'output' => "Gagal mengeksekusi perintah!\nError: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi validasi keamanan perintah.
     */
    private function isCommandSafe($commandString)
    {
        $commandString = trim($commandString);
        if (empty($commandString)) {
            return false;
        }

        if ($commandString === 'git pull') {
            return true;
        }

        $parts = explode(' ', $commandString);
        $baseCommand = strtolower($parts[0]);

        // Daftar kata yang dilarang keras untuk mencegah exploitasi, infinite loop, atau penghapusan data masal
        $blacklist = [
            'tinker', 'wipe', 'fresh', 'work', 'listen', 'serve', 'interactive', 'completion', 
            'key:generate', 'down', 'up', 'vendor:publish', 'storage:link', 'make:'
        ];

        foreach ($blacklist as $badWord) {
            if (str_contains($baseCommand, $badWord)) {
                return false;
            }
        }

        // Daftar awalan perintah yang diizinkan (Whitelisted)
        $whitelistedPrefixes = [
            'config:', 'cache:', 'route:', 'view:', 'optimize', 'migrate', 'db:seed', 'schedule:run', 'auth:clear-resets'
        ];

        foreach ($whitelistedPrefixes as $prefix) {
            if (str_starts_with($baseCommand, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
