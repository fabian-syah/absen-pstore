                                        <td class="ps-4">Tunjangan Jabatan</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->employee_position_allowance, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->meal_allowance > 0)
                                    <tr>
                                        <td class="ps-4">Uang Makan</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->meal_allowance, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->transport_allowance > 0)
                                    <tr>
                                        <td class="ps-4">Uang Transport</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->transport_allowance, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->overtime > 0)
                                    <tr>
                                        <td class="ps-4">Lembur (Overtime)</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->overtime, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->bonus > 0)
                                    <tr>
                                        <td class="ps-4">Bonus / Insentif</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->bonus, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->other_income > 0)
                                    <tr>
                                        <td class="ps-4">Lain-lain (Pendapatan)</td>
                                        <td class="text-end pe-4 fw-bold">Rp
                                            {{ number_format($salary->other_income, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>

                            <thead class="bg-light border-top border-bottom">
                                <tr>
                                    <th class="py-3 ps-4 text-uppercase text-secondary">POTONGAN (DEDUCTION)</th>
                                    <th class="py-3 text-end pe-4 text-uppercase text-secondary">JUMLAH (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($salary->bpjs_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan BPJS</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->bpjs_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->late_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Keterlambatan</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->late_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->loan_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Potongan Pinjaman / Kasbon</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->loan_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif

                                @if($salary->other_deduction > 0)
                                    <tr>
                                        <td class="ps-4">Lain-lain (Potongan)</td>
                                        <td class="text-end pe-4 fw-bold text-danger">- Rp
                                            {{ number_format($salary->other_deduction, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>

                            <tfoot class="bg-primary text-white">
                                <tr>
                                    <th class="py-3 ps-4 text-uppercase">TOTAL GAJI BERSIH (TAKE HOME PAY)</th>
                                    <th class="py-3 text-end pe-4 h4 mb-0">Rp
                                        {{ number_format($salary->total_salary, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- FOOTER TANDA TANGAN --}}
                    <div class="row mt-5 pt-4">
                        <div class="col-4 text-center">
                            <p class="mb-5">Penerima,</p>
                            <div class="mt-5 border-top pt-2 mx-auto" style="width: 150px;">
                                <p class="fw-bold mb-0">{{ $salary->user->name }}</p>
                            </div>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4 text-center">
                            <p class="mb-5">Jakarta, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>Finance & HRD,</p>
                            <div class="mt-5 border-top pt-2 mx-auto" style="width: 150px;">
                                <p class="fw-bold mb-0">Admin Payroll</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center text-muted small border-top pt-3">
                        <p>Ini adalah slip gaji elektronik yang dihasilkan secara otomatis oleh sistem.<br>Segala bentuk kesalahan data dapat dikonfirmasikan ke bagian HRD.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
            }
            .card {
                box-shadow: none !important;
                border: none !important;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .row {
                margin: 0 !important;
            }
            .col-md-8 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
@endsection