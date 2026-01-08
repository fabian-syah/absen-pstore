<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PStore Absensi</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

    <style>
        .auth-page {
            background: #f8f9fa;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .auth-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(0, 0, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 0, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 0, 0, 0.03) 0%, transparent 50%);
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .login-card:hover {
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: #000;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .logo-container i {
            font-size: 2rem;
            color: white;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .btn-login {
            background: #000;
            border: 2px solid #000;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-login:hover {
            background: #333;
            border-color: #333;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .auth-link {
            color: #000;
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .auth-link:hover {
            color: #333;
            text-decoration: underline;
        }

        .form-check-input:checked {
            background-color: #000;
            border-color: #000;
        }

        .form-check-label {
            font-size: 14px;
            color: #64748b;
        }

        .input-group-text {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        /* Geometric pattern */
        .geometric-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.03;
            background-image:
                linear-gradient(30deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(150deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(30deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(150deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(60deg, transparent 74%, #000 75%, #000 75%, transparent 76%),
                linear-gradient(60deg, transparent 74%, #000 75%, #000 75%, transparent 76%);
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-card {
                margin: 1rem;
                padding: 2rem 1.5rem !important;
            }

            .logo-container {
                width: 60px;
                height: 60px;
            }

            .logo-container i {
                font-size: 1.5rem;
            }
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            z-index: 10;
        }

        .password-container {
            position: relative;
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-size: 14px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .text-muted {
            color: #64748b !important;
        }

        .text-primary {
            color: #000 !important;
        }

        .border-bottom {
            border-bottom: 2px solid #000 !important;
        }

        .login-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #000;
        }

        .login-info h6 {
            color: #000;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .login-info ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #666;
        }

        .login-info li {
            margin-bottom: 4px;
        }

        .input-group.focused {
            position: relative;
        }

        .input-group.focused::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #000;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>

<body>
    <div class="container-scroller auth-page">
        <div class="geometric-pattern"></div>

        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth px-0">
                <div class="row w-100 mx-0 justify-content-center">
                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-8">
                        <div class="auth-form-light text-center py-5 px-4 px-sm-5 login-card">
                            <div class="brand-logo">
                                <div class="logo-container">
                                    <i class="mdi mdi-fingerprint"></i>
                                </div>
                                <h3 class="text-primary mb-1 fw-bold">PSTORE</h3>
                                <p class="text-muted">Sistem Absensi Digital</p>
                            </div>

                            <h4 class="fw-bold mb-3 text-dark">Selamat Datang</h4>
                            <p class="text-muted mb-4">Masuk dengan ID Login Anda</p>

                            <!-- Informasi Login -->
                            <div class="login-info text-start">
                                <h6><i class="mdi mdi-information-outline me-2"></i>Cara Login:</h6>
                                <ul>
                                    <li>Gunakan <strong>ID Login</strong> yang telah diberikan</li>
                                    <li>Password tidak case-sensitive (BESAR/kecil sama)</li>
                                </ul>
                            </div>

                            {{-- Tampilkan Error --}}
                            @error('login_id')
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @enderror

                            @error('password')
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @enderror

                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                    <i class="mdi mdi-check-circle-outline me-2"></i>
                                    {{ session('status') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form class="pt-3" action="{{ route('login.submit') }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="mdi mdi-account-outline"></i>
                                        </span>
                                        <input type="text" class="form-control" id="login_id" name="login_id"
                                            placeholder="Masukkan ID Login Anda" value="{{ old('login_id') }}" required
                                            autofocus>
                                    </div>
                                    @error('login_id')
                                        <small class="text-danger mt-1">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <div class="input-group password-container">
                                        <span class="input-group-text bg-transparent">
                                            <i class="mdi mdi-lock-outline"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword()">
                                            <i class="mdi mdi-eye-outline" id="password-icon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <small class="text-danger mt-1">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <button type="submit" class="btn btn-login w-100">
                                        <i class="mdi mdi-login me-2"></i>
                                        MASUK SISTEM
                                    </button>
                                </div>

                                {{-- <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input" name="remember"
                                                {{ old('remember') ? 'checked' : '' }}>
                                            Ingat saya
                                        </label>
                                    </div>
                                    <a href="{{ route('password.request') }}" class="auth-link">
                                        Lupa password?
                                    </a>
                                </div> --}}

                                <div class="text-center mt-4 pt-3 border-top">
                                    <p class="text-muted small mb-0">
                                        &copy; {{ date('Y') }} PStore Absensi System. All rights reserved.
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('mdi-eye-outline');
                passwordIcon.classList.add('mdi-eye-off-outline');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('mdi-eye-off-outline');
                passwordIcon.classList.add('mdi-eye-outline');
            }
        }

        // Add loading state to form
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i>Memproses...';
            btn.disabled = true;
        });

        // Add input focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                this.parentElement.parentElement.classList.remove('focused');
            });
        });

        // Auto focus on login_id field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const loginIdField = document.getElementById('login_id');
            if (loginIdField) {
                loginIdField.focus();
            }
        });

        // Clear error messages when user starts typing
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const errorElement = this.parentElement.parentElement.querySelector('.text-danger');
                if (errorElement) {
                    errorElement.remove();
                }

                const alertElement = document.querySelector('.alert');
                if (alertElement) {
                    alertElement.remove();
                }
            });
        });
    </script>
</body>

</html>
