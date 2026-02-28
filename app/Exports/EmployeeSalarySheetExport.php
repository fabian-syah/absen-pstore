<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; // IMPORT BARU
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // IMPORT BARU

class EmployeeSalarySheetExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents, WithColumnFormatting
{
    protected $category;
    protected $filters;
    protected $sheetTitle;
    protected $group;

    public function __construct($category, $filters, $sheetTitle, $group = 'all')
    {
        $this->category = $category;
        $this->filters = $filters;
        $this->sheetTitle = $sheetTitle;
        $this->group = $group;
    }

    public function query()
    {
        $query = User::with([
            'branch',
            'division',
            'employeeSalary',
            'salaries' => function ($q) {
                $q->latest();
            }
        ])
            ->where('users.is_active', true)
            ->whereNotIn('users.role', ['admin', 'admin_gaji']);

        // --- PUSAT / CABANG GROUPING FILTER ---
        $pusatList = [
            'AppleLux',
            'Arcis & Debs',
            'Cleaning service',
            'Dokter Pstore',
            'Driver pstore',
            'Finance',
            'Inventory',
            'keluarga Pstore',
            'Managament',
            'Marketing Creative',
            'Masjid abdurrohman bin auf',
            'Mega pstore',
            'Ps arwana',
            'PS bakery',
            'PS big jakarta',
            'PS catering',
            'PS new jakarta',
            'Pskontraktor',
            'Pstore Lenteng Agung',
            'Pstore Peduli',
            'Pstore Qcell jakarta',
            'Shopee',
            'Security Jakarta',
            'Team Audit',
            'Team Creative',
            'Tiktok'
        ];

        if ($this->group === 'pusat') {
            $query->whereHas('branch', function ($q) use ($pusatList) {
                $q->whereIn('name', $pusatList);
            });
        } elseif ($this->group === 'cabang') {
            $query->whereHas('branch', function ($q) use ($pusatList) {
                $q->whereNotIn('name', $pusatList);
            });
        }

        // 1. Filter Pencarian Global (Search)
        if (isset($this->filters['search']) && $this->filters['search'] != null) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.login_id', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // 2. Filter Cabang
        if (isset($this->filters['branch_id']) && $this->filters['branch_id'] != null) {
            $query->where('users.branch_id', $this->filters['branch_id']);
        }

        // 3. Filter Divisi
        if (isset($this->filters['division_id']) && $this->filters['division_id'] != null) {
            $query->where('users.division_id', $this->filters['division_id']);
        }

        // 4. Logika Kategori
        if ($this->category === 'all') {
            if (isset($this->filters['category']) && $this->filters['category'] != null) {
                if ($this->filters['category'] == 'unset') {
                    $query->doesntHave('employeeSalary');
                } else {
                    $query->whereHas('employeeSalary', function ($q) {
                        $q->where('category', $this->filters['category']);
                    });
                }
            }
        } elseif ($this->category === 'unset') {
            $query->doesntHave('employeeSalary');
        } else {
            $query->whereHas('employeeSalary', function ($q) {
                $q->where('category', $this->category);
            });
        }

        return $query
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->orderBy('branches.name', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('users.*'); // Ensure we return User models, not mixed attributes from join
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Login ID',
            'Email',
            'Divisi',
            'Pusat',
            'Cabang',
            'Kategori Gaji',
            'Gaji Pokok (Bulanan)',
            'Tunjangan Jabatan',
            'Privilege Owner',
            'Gaji Harian (Freelance)',
            'Insentif (Promotor)',
            'Total Master Gaji (Estimasi)',
            'Potongan Alpha',
            'Potongan Telat',
            'Potongan Cuti Lebih',
            'Potongan Kasbon',
            'Potongan Lainnya',
            'Catatan Potongan Lainnya',
            'Bonus / Insentif Tambahan',
            'Dispensasi',
            'Catatan Dispensasi',
            'Gaji Harus Diterima',
            'Nama Bank',
            'No. Rekening',
            'Catatan',
        ];
    }

    public function map($user): array
    {
        $salary = $user->employeeSalary;

        $categoryLabel = 'Belum Diatur';
        $basicSalary = 0;
        $positionAllowance = 0;
        $ownerPrivilege = 0;
        $dailySalary = 0;
        $promotorBonus = 0;
        $totalMaster = 0;
        $potonganAlpha = 0;
        $potonganTelat = 0;
        $potonganCutiLebih = 0;
        $gajiHarusDiterima = 0;
        $bankName = '-';
        $accountNumber = '-';
        $notes = '-';

        if ($salary) {
            $bankName = $salary->bank_name;
            $accountNumber = $salary->bank_account_number;
            $notes = $salary->notes;

            if ($salary->category == 'employee') {
                $categoryLabel = 'Karyawan Tetap';
                $basicSalary = $salary->basic_salary;
                $positionAllowance = $salary->position_allowance;
                $ownerPrivilege = $salary->owner_privilege;
                $totalMaster = $basicSalary + $positionAllowance + $ownerPrivilege;
            } elseif ($salary->category == 'freelance') {
                $categoryLabel = 'Freelance';
                $dailySalary = $salary->daily_salary;
                $totalMaster = $dailySalary; // Per hari
            } elseif ($salary->category == 'promotor') {
                $categoryLabel = 'Promotor';
                $promotorBonus = $salary->promotor_bonus;
                $totalMaster = $promotorBonus;
            }
        }

        // --- HITUNG ALPHA & TELAT REAL-TIME DARI DATA ABSENSI ---
        $alphaCount = 0;
        $lateCount = 0;

        if ($totalMaster > 0) {
            $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
            $now = Carbon::now($branchTimezone);
            $currentMonth = $now->month;
            $currentYear = $now->year;

            // CUTOFF: 26 bulan kemarin - 25 bulan ini
            $monthStartDate = Carbon::createFromDate($currentYear, $currentMonth, 1, $branchTimezone)->subMonth()->day(26)->startOfDay();
            $monthEndDate = Carbon::createFromDate($currentYear, $currentMonth, 1, $branchTimezone)->day(25)->endOfDay();
            $today = Carbon::now($branchTimezone)->startOfDay();
            $limitDate = ($monthEndDate->gt($now)) ? $today : $monthEndDate;

            // Query Attendance
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('check_in_time', [
                    $monthStartDate->copy()->subDays(2)->startOfDay(),
                    $monthEndDate->copy()->addDays(2)->endOfDay()
                ])->get();

            // Query Leaves
            $leaves = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where(function ($query) use ($monthStartDate, $monthEndDate) {
                    $s = $monthStartDate->format('Y-m-d');
                    $e = $monthEndDate->format('Y-m-d');
                    $query->whereBetween('start_date', [$s, $e])
                        ->orWhereBetween('end_date', [$s, $e])
                        ->orWhere(function ($q) use ($s, $e) {
                            $q->where('start_date', '<=', $s)
                                ->where('end_date', '>=', $e);
                        });
                })->get();

            // HITUNG TELAT
            $telatFisik = $attendances->filter(function ($a) use ($monthStartDate, $limitDate, $branchTimezone) {
                $attDate = Carbon::parse($a->check_in_time)->timezone($branchTimezone)->startOfDay();
                $isInRange = $attDate->between($monthStartDate, $limitDate);
                $isTelat = $a->is_late_checkin || $a->status === 'late' || str_contains(strtolower($a->presence_status ?? ''), 'telat');
                return $isInRange && $isTelat;
            })->count();

            $izinTelat = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'telat')->where('status', 'approved')
                ->whereBetween('start_date', [$monthStartDate, $monthEndDate])->count();

            $lateCount = $telatFisik + $izinTelat;

            // HITUNG ALPHA (loop setiap hari kerja)
            $period = CarbonPeriod::create($monthStartDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

            foreach ($period as $date) {
                $currentDateStr = $date->format('Y-m-d');

                $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                    return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
                })->sortBy(fn($a) => $a->attendance_type == 'system' ? 1 : 0)->first();

                $leave = $leaves->filter(function ($l) use ($date) {
                    return $date->between(
                        Carbon::parse($l->start_date)->startOfDay(),
                        Carbon::parse($l->end_date ?? $l->start_date)->endOfDay()
                    );
                });

                if (!$att && $leave->isEmpty()) {
                    $alphaCount++;
                } else if ($att) {
                    $status = strtolower($att->presence_status ?? '');
                    if ($status === 'alpha') {
                        $alphaCount++;
                    }
                }
            }

            // Hitung Potongan (Rumus: Fixed/31 x Alpha, Fixed/93 x Telat)
            if ($alphaCount > 0) {
                $potonganAlpha = (int) floor(($totalMaster / 31) * $alphaCount);
            }
            if ($lateCount > 0) {
                $potonganTelat = (int) floor(($totalMaster / 93) * $lateCount);
            }
        }

        // Cuti lebih, Kasbon, Potongan Lain, Bonus, Dispensasi -> ambil dari payroll terakhir
        $potonganKasbon = 0;
        $potonganLainnya = 0;
        $catatanPotonganLainnya = '-';
        $bonusInsentif = 0;
        $dispensasi = 0;
        $catatanDispensasi = '-';

        if ($user->salaries->isNotEmpty()) {
            $latestSalary = $user->salaries->first();
            $potonganCutiLebih = $latestSalary->cuti_lebih_deduction ?? 0;
            $potonganKasbon = $latestSalary->kasbon_deduction ?? 0;
            $potonganLainnya = $latestSalary->other_deduction ?? 0;
            $catatanPotonganLainnya = $latestSalary->other_deduction_note ?? '-';
            $bonusInsentif = $latestSalary->promotor_bonus ?? 0;
            $dispensasi = $latestSalary->dispensation_amount ?? 0;
            $catatanDispensasi = $latestSalary->dispensation_note ?? '-';
        }

        $totalPotongan = $potonganAlpha + $potonganTelat + $potonganCutiLebih + $potonganKasbon + $potonganLainnya;
        $totalTambahan = $bonusInsentif + $dispensasi;
        $gajiHarusDiterima = $totalMaster + $totalTambahan - $totalPotongan;

        // --- PUSAT / CABANG GROUPING ---
        $branchName = $user->branch->name ?? '-';
        $pusatList = [
            'AppleLux',
            'Arcis & Debs',
            'Cleaning service',
            'Dokter Pstore',
            'Driver pstore',
            'Finance',
            'Inventory',
            'keluarga Pstore',
            'Managament',
            'Marketing Creative',
            'Masjid abdurrohman bin auf',
            'Mega pstore',
            'Ps arwana',
            'PS bakery',
            'PS big jakarta',
            'PS catering',
            'PS new jakarta',
            'Pskontraktor',
            'Pstore Lenteng Agung',
            'Pstore Peduli',
            'Pstore Qcell jakarta',
            'Shopee',
            'Security Jakarta',
            'Team Audit',
            'Team Creative',
            'Tiktok'
        ];

        $isPusat = false;
        // Check case-insensitively
        foreach ($pusatList as $pusatBranch) {
            if (strtolower(trim($branchName)) === strtolower(trim($pusatBranch))) {
                $isPusat = true;
                break;
            }
        }

        $pusatCol = $isPusat ? $branchName : '-';
        $cabangCol = !$isPusat ? $branchName : '-';

        return [
            $user->name,
            $user->login_id ?? '-',
            $user->email,
            $user->division->name ?? '-',
            $pusatCol,
            $cabangCol,
            $categoryLabel,
            $basicSalary,
            $positionAllowance,
            $ownerPrivilege,
            $dailySalary,
            $promotorBonus,
            $totalMaster,
            $potonganAlpha,
            $potonganTelat,
            $potonganCutiLebih,
            $potonganKasbon,
            $potonganLainnya,
            $catatanPotonganLainnya,
            $bonusInsentif,
            $dispensasi,
            $catatanDispensasi,
            $gajiHarusDiterima,
            $bankName,
            $accountNumber . ' ',
            $notes,
        ];
    }

    // FUNCTION BARU: FORMAT RUPIAH
    public function columnFormats(): array
    {
        return [
            'H' => '"Rp " #,##0', // Gaji Pokok
            'I' => '"Rp " #,##0', // Tunjangan
            'J' => '"Rp " #,##0', // Privilege
            'K' => '"Rp " #,##0', // Gaji Harian
            'L' => '"Rp " #,##0', // Insentif
            'M' => '"Rp " #,##0', // Total
            'N' => '"Rp " #,##0', // Potongan Alpha
            'O' => '"Rp " #,##0', // Potongan Telat
            'P' => '"Rp " #,##0', // Potongan Cuti Lebih
            'Q' => '"Rp " #,##0', // Potongan Kasbon
            'R' => '"Rp " #,##0', // Potongan Lainnya
            // S = Catatan Potongan Lainnya (text)
            'T' => '"Rp " #,##0', // Bonus / Insentif Tambahan
            'U' => '"Rp " #,##0', // Dispensasi
            // V = Catatan Dispensasi (text)
            'W' => '"Rp " #,##0', // Gaji Harus Diterima
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling kosong karena dihandle registerEvents (Table)
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // 1. Ambil Dimensi Data
                $dimension = $event->sheet->getDelegate()->calculateWorksheetDimension();

                // 2. Buat Nama Tabel Unik
                $tableName = str_replace(' ', '', $this->sheetTitle) . '_' . uniqid();

                // 3. Buat Object Tabel
                $table = new Table();
                $table->setName($tableName);
                $table->setRange($dimension);
                $table->setShowTotalsRow(false);

                // 4. Pilih Gaya Tabel (Biru)
                $tableStyle = new TableStyle();
                $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM2);
                $table->setStyle($tableStyle);

                // 5. Masukkan Tabel
                $event->sheet->getDelegate()->addTable($table);

                // 6. Freeze Header
                $event->sheet->getDelegate()->freezePane('A2');

                // 7. Auto Size Kolom (A sampai Z karena nambah kolom baru)
                foreach (range('A', 'Z') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}