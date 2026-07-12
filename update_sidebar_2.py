import re

file_path = r'd:\bian\pstore\absensi-pstore\resources\views\layout\sidebar.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# The new HTML structure for Riwayat
new_riwayat_html = '''        {{-- =================================== --}}
        {{-- MENU RIWAYAT (GABUNGAN) --}}
        {{-- =================================== --}}
        <li class="nav-item nav-category">Riwayat Lengkap</li>
        
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.history') }}">
                    <i class="menu-icon mdi mdi-clock-outline"></i>
                    <span class="menu-title">Riwayat Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leave-requests.personal-history') }}">
                    <i class="menu-icon mdi mdi-hospital-box-outline"></i>
                    <span class="menu-title">Riwayat Izin/Sakit</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leave-requests.cuti-history') }}">
                    <i class="menu-icon mdi mdi-wallet-travel"></i>
                    <span class="menu-title">Riwayat Cuti</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('employment-history.index') }}">
                    <i class="menu-icon mdi mdi-source-branch"></i>
                    <span class="menu-title">Riwayat Divisi/Cabang</span>
                </a>
            </li>
        @endif
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('violations.index') }}">
                <i class="menu-icon mdi mdi-alert-circle-outline"></i>
                <span class="menu-title">Riwayat Pelanggaran</span>
            </a>
        </li>
        
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.history') }}">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Riwayat Scan</span>
                </a>
            </li>
        @endif
        
        @if (in_array(auth()->user()->role, ['audit', 'leader', 'admin']))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('employee-evaluations.history') }}">
                    <i class="menu-icon mdi mdi-clipboard-text-clock-outline"></i>
                    <span class="menu-title">Riwayat Evaluasi (Tim)</span>
                </a>
            </li>
        @endif
'''

# Find the collapse block and replace it
# The collapse block starts at <li class="nav-item">...href="#ui-riwayat" and ends at </li> before {{-- RINGKASAN TAHUNAN --}}

pattern = re.compile(r'\{\{-- MENU RIWAYAT \(GABUNGAN\) --\}\}\s*\{\{-- =================================== --\}\}\s*<li class="nav-item">.*?</a>\s*<div class="collapse" id="ui-riwayat">.*?</div>\s*</li>', re.DOTALL)

if pattern.search(content):
    content = pattern.sub('{{-- MENU RIWAYAT (GABUNGAN) --}}\n        {{-- =================================== --}}\n' + new_riwayat_html.strip(), content)
else:
    print("Pattern not found!")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Done!")
