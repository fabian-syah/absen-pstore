@extends('layout.master')

@section('content')
    <style>
        .fc {
            font-family: inherit;
        }
        .fc .fc-toolbar-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
        }
        .fc .fc-button {
            background-color: #4b49ac;
            border-color: #4b49ac;
            border-radius: 8px !important;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 6px 14px;
            transition: all 0.2s;
        }
        .fc .fc-button:hover {
            background-color: #3a38a0;
            transform: translateY(-1px);
        }
        .fc .fc-button-active {
            background-color: #2d2b8e !important;
            border-color: #2d2b8e !important;
        }
        .fc .fc-daygrid-day-number {
            font-weight: 700;
            color: #475569;
            padding: 8px 10px;
        }
        .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(75, 73, 172, 0.06) !important;
        }
        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            color: #4b49ac;
        }
        .fc .fc-event {
            border-radius: 8px !important;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            border: none !important;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .fc .fc-event:hover {
            transform: scale(1.03);
        }
        .fc .fc-col-header-cell-cushion {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            color: #64748b;
            padding: 12px 8px;
        }
        .fc th {
            border: none !important;
            background: #f8fafc;
        }
        .fc td {
            border-color: #f1f5f9 !important;
        }
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Mobile Calendar Overrides */
        @media (max-width: 767.98px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 8px;
            }
            .fc .fc-toolbar-title {
                font-size: 1rem;
            }
            .fc .fc-button {
                font-size: 0.7rem;
                padding: 4px 10px;
            }
            .fc .fc-daygrid-day-number {
                font-size: 0.75rem;
                padding: 4px 6px;
            }
            .fc .fc-event {
                font-size: 0.6rem;
                padding: 2px 4px;
            }
            .fc .fc-col-header-cell-cushion {
                font-size: 0.6rem;
                padding: 8px 4px;
            }
        }

        /* Event Detail Popup */
        .event-popup-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1050;
            backdrop-filter: blur(4px);
        }
        .event-popup-overlay.show { display: flex; align-items: center; justify-content: center; }
        .event-popup {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            max-width: 420px;
            width: 92%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            animation: popIn 0.25s ease;
        }
        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <div class="container-fluid px-3 px-md-4">
        {{-- HEADER (Responsive) --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div>
                <h2 class="fw-bold text-dark mb-1" style="font-size: clamp(1.1rem, 3vw, 1.5rem);">
                    <i class="mdi mdi-calendar-month text-primary me-2"></i>Kalender Kasbon
                </h2>
                <p class="text-muted mb-0 small">Jadwal potongan cicilan per bulan</p>
            </div>
            <a href="{{ route('kasbon.index') }}" class="btn btn-light border fw-bold rounded-pill px-3 px-md-4 shadow-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        {{-- LEGEND --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3 px-4">
                <div class="d-flex gap-4 align-items-center flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-dot" style="background: #f59e0b;"></span>
                        <span class="small fw-bold text-muted">Belum Dibayar</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-dot" style="background: #10b981;"></span>
                        <span class="small fw-bold text-muted">Sudah Dibayar</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CALENDAR --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-2 p-md-4">
                <div id="kasbon-calendar"></div>
            </div>
        </div>
    </div>

    {{-- EVENT DETAIL POPUP --}}
    <div class="event-popup-overlay" id="eventPopup">
        <div class="event-popup">
            <div class="text-center mb-3">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     id="popupIcon"
                     style="width: 60px; height: 60px; background: #fff3cd;">
                    <i class="mdi mdi-cash-clock text-warning fs-2"></i>
                </div>
                <h5 class="fw-bold mb-1" id="popupTitle">Cicilan ke-1</h5>
                <small class="text-muted" id="popupUser">Nama Karyawan</small>
            </div>
            <div class="bg-light rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small fw-bold">Nominal Cicilan</span>
                    <span class="fw-bold text-dark" id="popupAmount">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small fw-bold">Total Pinjaman</span>
                    <span class="fw-bold text-dark" id="popupTotal">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small fw-bold">Sisa Hutang</span>
                    <span class="fw-bold text-danger" id="popupSisa">Rp 0</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small fw-bold">Status</span>
                    <span id="popupStatus" class="badge rounded-pill bg-warning text-dark px-3 fw-bold">Belum</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="#" id="popupDetailBtn" class="btn btn-primary fw-bold flex-grow-1 rounded-pill">
                    <i class="mdi mdi-eye me-1"></i> Lihat Detail
                </a>
                <button class="btn btn-light border fw-bold rounded-pill px-4" onclick="closePopup()">Tutup</button>
            </div>
        </div>
    </div>

    {{-- FullCalendar CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('kasbon-calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan'
                },
                height: 'auto',
                dayMaxEvents: 3,
                events: '{{ route("kasbon.calendar.data") }}',
                eventClick: function(info) {
                    var props = info.event.extendedProps;
                    document.getElementById('popupTitle').textContent = 'Cicilan ke-' + props.installment_order;
                    document.getElementById('popupUser').textContent = props.user_name;
                    document.getElementById('popupAmount').textContent = 'Rp ' + Number(props.amount).toLocaleString('id-ID');
                    document.getElementById('popupTotal').textContent = 'Rp ' + Number(props.total_pinjaman).toLocaleString('id-ID');
                    document.getElementById('popupSisa').textContent = 'Rp ' + Number(props.sisa_hutang).toLocaleString('id-ID');
                    document.getElementById('popupDetailBtn').href = '/kasbon/' + props.kasbon_id + '/detail';

                    var statusBadge = document.getElementById('popupStatus');
                    var iconEl = document.getElementById('popupIcon');
                    if (props.is_paid) {
                        statusBadge.className = 'badge rounded-pill bg-success text-white px-3 fw-bold';
                        statusBadge.textContent = 'Sudah Dibayar';
                        iconEl.style.background = '#d1e7dd';
                        iconEl.innerHTML = '<i class="mdi mdi-check-decagram text-success fs-2"></i>';
                    } else {
                        statusBadge.className = 'badge rounded-pill bg-warning text-dark px-3 fw-bold';
                        statusBadge.textContent = 'Belum Dibayar';
                        iconEl.style.background = '#fff3cd';
                        iconEl.innerHTML = '<i class="mdi mdi-cash-clock text-warning fs-2"></i>';
                    }

                    document.getElementById('eventPopup').classList.add('show');
                }
            });

            calendar.render();
        });

        function closePopup() {
            document.getElementById('eventPopup').classList.remove('show');
        }
        // Close on overlay click
        document.getElementById('eventPopup').addEventListener('click', function(e) {
            if (e.target === this) closePopup();
        });
    </script>
@endsection
