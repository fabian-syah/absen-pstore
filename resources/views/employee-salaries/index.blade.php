@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Master Data Gaji Karyawan</h4>
                <p class="text-muted">Atur Gaji Pokok & Tunjangan disini. Data ini akan otomatis muncul saat Payroll.</p>

                <div class="table-responsive mt-4">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Kategori</th>
                                <th>Gaji Pokok</th>
                                <th>Tunjangan</th>
                                <th>Privilege</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->branch->name ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($user->employeeSalary)
                                        <span class="badge badge-info">{{ ucfirst($user->employeeSalary->category) }}</span>
                                    @else
                                        <span class="badge badge-secondary">Belum Set</span>
                                    @endif
                                </td>
                                <td>
                                    Rp {{ number_format($user->employeeSalary->basic_salary ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    Rp {{ number_format($user->employeeSalary->position_allowance ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    Rp {{ number_format($user->employeeSalary->owner_privilege ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    <a href="{{ route('employee-salaries.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-pencil"></i> Atur Gaji
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection