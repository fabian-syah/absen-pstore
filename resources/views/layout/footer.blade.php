{{-- Modern Footer with Gradient Border --}}
<style>
    .footer {
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
        border-top: 3px solid transparent;
        background-image:
            linear-gradient(#ffffff, #f8f9fa),
            linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
        background-origin: border-box;
        background-clip: padding-box, border-box;
        padding: 1.5rem 0 !important;
        margin-top: auto;
        position: relative;
        overflow: hidden;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(90deg,
                transparent 0%,
                rgba(13, 110, 253, 0.02) 50%,
                transparent 100%);
        pointer-events: none;
    }

    .footer span,
    .footer a {
        position: relative;
        z-index: 1;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .footer a {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-decoration: none !important;
        font-weight: 700 !important;
        position: relative;
        display: inline-block;
    }

    .footer a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .footer a:hover::after {
        width: 100%;
    }

    .footer a:hover {
        transform: translateY(-2px);
    }

    .footer .text-muted {
        color: #6c757d !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
    }

    @media (max-width: 576px) {
        .footer {
            padding: 1.25rem 1rem !important;
        }

        .footer span {
            font-size: 0.85rem !important;
        }
    }
</style>

<footer class="footer">
    <div class="d-sm-flex justify-content-between align-items-center text-center text-sm-start px-3">
        <span class="d-block mb-2 mb-sm-0">
            Dibuat dengan ❤️ oleh
            <a href="https://www.instagram.com/mcisreal_/" target="_blank">
                Bian
            </a>
        </span>
        <span class="text-muted small">
            © 2025 All Rights Reserved
        </span>
    </div>
</footer>