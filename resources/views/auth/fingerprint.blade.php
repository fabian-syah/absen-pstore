@extends('layout.master-mini')

@section('content')
    <div class="content-wrapper d-flex align-items-center justify-content-center auth theme-one"
        style="background-image: url('{{ asset('assets/images/auth/login_bg.jpg') }}'); background-size: cover;">
        <div class="row w-100">
            <div class="col-lg-4 mx-auto">
                <div class="auto-form-wrapper text-center pt-5 pb-5">

                    <h3 class="font-weight-bold mb-4">Fingerprint Login</h3>
                    <p class="text-muted mb-5">Touch the sensor to verify identity</p>

                    <form action="{{ route('fingerprint.authenticate') }}" method="POST" id="fingerprintForm">
                        @csrf

                        <div class="fingerprint-container d-flex justify-content-center align-items-center mb-4">
                            {{-- Fingerprint Icon Button --}}
                            <div id="scan-btn" class="fingerprint-sensor">
                                <i class="mdi mdi-fingerprint"
                                    style="font-size: 80px; color: #ccc; transition: all 0.3s;"></i>
                                <div class="scan-line"></div>
                            </div>
                        </div>

                        <p id="status-text" class="text-small text-muted font-weight-bold">Ready to Scan</p>
                    </form>

                    <div class="mt-5">
                        <a href="{{ route('login') }}" class="text-small text-primary">Back to Password Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fingerprint-sensor {
            width: 120px;
            height: 120px;
            border: 2px dashed #ccc;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .fingerprint-sensor:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }

        .fingerprint-sensor:hover i {
            color: #007bff !important;
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 4px;
            background: #00ce68;
            top: -10px;
            /* Start hidden */
            box-shadow: 0 0 10px #00ce68;
            opacity: 0;
        }

        /* Animation Class */
        .scanning {
            border-color: #00ce68 !important;
            border-style: solid;
        }

        .scanning i {
            color: #00ce68 !important;
        }

        .scanning .scan-line {
            opacity: 1;
            animation: scanMove 1.5s infinite linear;
        }

        @keyframes scanMove {
            0% {
                top: 0;
            }

            50% {
                top: 100%;
            }

            100% {
                top: 0;
            }
        }
    </style>

    <script>
        document.getElementById('scan-btn').addEventListener('click', function () {
            var sensor = this;
            var statusText = document.getElementById('status-text');

            // Start Scanning Effect
            sensor.classList.add('scanning');
            statusText.innerText = "Scanning Biometric...";
            statusText.classList.remove('text-muted');
            statusText.classList.add('text-success');

            // Simulate 2 seconds delay then submit
            setTimeout(function () {
                statusText.innerText = "Verified! Logging in...";
                document.getElementById('fingerprintForm').submit();
            }, 2000);
        });
    </script>
@endsection