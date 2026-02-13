<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $userName }}</title>
    <style>
        @page {
            size: 90mm 130mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .qr-card {
            width: 90mm;
            height: 130mm;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12mm 8mm;
            position: relative;
            overflow: hidden;
        }

        /* Decorative top bar */
        .qr-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6mm;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        /* Decorative bottom bar */
        .qr-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3mm;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .brand-logo {
            font-size: 18pt;
            font-weight: 800;
            color: #1a1a2e;
            letter-spacing: 2px;
            margin-bottom: 3mm;
            text-transform: uppercase;
        }

        .brand-logo span {
            color: #f59e0b;
        }

        .qr-wrapper {
            background: #ffffff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 5mm;
            margin: 3mm 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .qr-wrapper svg {
            display: block;
            width: 50mm !important;
            height: 50mm !important;
        }

        .user-name {
            font-size: 12pt;
            font-weight: 700;
            color: #1a1a2e;
            text-align: center;
            margin-top: 3mm;
            max-width: 70mm;
            word-wrap: break-word;
            line-height: 1.3;
        }

        .user-branch {
            font-size: 8pt;
            color: #6c757d;
            margin-top: 1mm;
            text-align: center;
        }

        .scan-label {
            font-size: 7pt;
            color: #adb5bd;
            margin-top: 3mm;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Print styles */
        @media print {
            body {
                background: transparent;
                min-height: auto;
            }

            .qr-card {
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                height: 100%;
                page-break-after: avoid;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Screen-only controls */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 999;
        }

        .btn-print,
        .btn-back {
            padding: 10px 24px;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .btn-print {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: #fff;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .btn-back {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-back:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    {{-- Screen-only buttons --}}
    <div class="print-controls no-print">
        <button class="btn-back" onclick="window.history.back()">← Kembali</button>
        <button class="btn-print" onclick="window.print()">📥 Simpan PDF</button>
    </div>

    <div class="qr-card">
        <div class="brand-logo">P<span>Store</span></div>

        <div class="qr-wrapper">
            {!! $qrSvg !!}
        </div>

        <div class="user-name">{{ $userName }}</div>
        <div class="user-branch">{{ $branchName }}</div>
        <div class="scan-label">Scan untuk Absensi</div>
    </div>

    <script>
        // Auto-trigger print dialog after slight delay
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>