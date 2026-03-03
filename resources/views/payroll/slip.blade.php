{{-- resources/views/payroll/slip.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Slip Gaji - {{ $employee->name }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            color: #000;
            font-size: 9px;
            background: #fff;
        }

        /* ── Tombol print (tidak ikut cetak) ── */
        .no-print {
            text-align: center;
            padding: 10px;
            background: #f3f4f6;
            border-bottom: 1px solid #ddd;
        }

        .no-print button {
            padding: 6px 20px;
            background: #0d80dd;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

        /* ── Slip wrapper ── */
        .slip-wrapper {
            width: 190mm;
            min-height: 135mm;
            /* setengah A4 kurang margin */
            margin: 6mm auto;
            border: 1px solid #333;
            padding: 8px 12px;
        }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 1.5px solid #0d80dd;
            padding-bottom: 5px;
            margin-bottom: 6px;
            position: relative;
        }

        .logo {
            position: absolute;
            left: 0;
            height: 30px;
            width: auto;
            max-width: 100px;
        }

        .header-content {
            text-align: center;
            width: 100%;
            line-height: 1.3;
        }

        .header-content .title {
            font-weight: 700;
            font-size: 10px;
        }

        .header-content .addr {
            font-size: 8.5px;
            color: #444;
        }

        .header-content .slip {
            font-weight: 700;
            font-size: 9px;
            margin-top: 2px;
        }

        /* ── Info karyawan (2 kolom) ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px 12px;
            margin-bottom: 6px;
            font-size: 8.5px;
        }

        .info-row {
            display: flex;
            gap: 3px;
        }

        .info-label {
            width: 90px;
            font-weight: bold;
            flex-shrink: 0;
        }

        /* ── Tabel rincian ── */
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        .calc-table th {
            background: #f0f4ff;
            padding: 3px 4px;
            text-align: left;
            border-bottom: 1px solid #c7d2fe;
            font-size: 8px;
        }

        .calc-table td {
            padding: 2.5px 4px;
            border-bottom: 1px dotted #e5e7eb;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .text-danger {
            color: #dc2626;
        }

        .text-success {
            color: #059669;
        }

        /* Section label baris */
        .sec-row td {
            background: #f8faff;
            font-weight: bold;
            color: #1e40af;
            font-size: 8px;
            padding: 2px 4px;
            border-bottom: 1px solid #dbeafe;
        }

        /* Total row */
        .total-row td {
            font-weight: bold;
            border-top: 1.5px solid #555;
            border-bottom: none;
            padding-top: 4px;
            font-size: 9.5px;
        }

        /* ── Tanda tangan ringkas ── */
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 8.5px;
        }

        .sig-box {
            text-align: center;
            width: 45%;
        }

        .sig-line {
            /* border-top: 1px solid #333; */
            margin-top: 28px;
            padding-top: 2px;
        }

        /* ── Note ── */
        .note {
            font-size: 7.5px;
            color: #999;
            text-align: center;
            margin-top: 6px;
            border-top: 1px dotted #ccc;
            padding-top: 4px;
        }

        /* ── Print settings ── */
        @page {
            size: A4 portrait;
            margin: 10mm 10mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .slip-wrapper {
                width: 100%;
                margin: 0;
                /* border: 1px solid #333; */
                padding: 7px 10px;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    @php
        $baseSalary = (int) ($detail->base_salary ?? 0);
        $allowances = (int) ($detail->allowances ?? 0);
        $bonuses = (int) ($detail->bonuses ?? 0);
        $lateDeduction = (int) ($detail->late_deduction ?? 0);
        $otherDeductions = (int) ($detail->other_deductions ?? 0);
        $netSalary = (int) ($detail->net_salary ?? 0);

        $workDays = (int) ($detail->total_work_days ?? 0);
        $workHours = (float) ($detail->total_work_hours ?? 0);
        $lateMinutes = (int) ($detail->total_late_minutes ?? 0);

        $dailyRate = (int) ($detail->daily_rate ?? 0);
        $hourlyRate = (int) ($detail->hourly_rate ?? 0);
        $salaryType = $department->salary_type ?? 'daily';

        $periodeText =
            \Carbon\Carbon::parse($payroll->period_start)->translatedFormat('d F Y') .
            ' – ' .
            \Carbon\Carbon::parse($payroll->period_end)->translatedFormat('d F Y');

        $tanggalCetak = $tanggal_cetak ?? now()->translatedFormat('d F Y');
    @endphp

    {{-- Tombol print --}}
    <div class="no-print">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="slier">

        {{-- HEADER --}}
        <div class="header">
            <img src="{{ asset('img/bgn_light.png') }}" alt="Logo" class="logo">
            <div class="header-content">
                <div class="title">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)</div>
                <div class="addr">{{ $app_address }}</div>
                <div class="slip">SLIP GAJI &nbsp;|&nbsp; {{ $periodeText }}</div>
                <div style="font-size:8.5px;">{{ $app_city }}, {{ $tanggalCetak }}</div>
            </div>
        </div>

        {{-- INFO KARYAWAN — 2 kolom --}}
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span>: {{ $employee->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Karyawan</span>
                <span>: {{ $employee->employee_number ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jabatan</span>
                <span>: {{ $department->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Upah</span>
                <span>: {{ $salaryType === 'daily' ? 'Harian' : 'Per Jam' }}</span>
            </div>
        </div>

        {{-- TABEL RINCIAN --}}
        <table class="calc-table">
            <thead>
                <tr>
                    <th style="width:60%">RINCIAN</th>
                    <th class="amount">NOMINAL</th>
                </tr>
            </thead>
            <tbody>
                {{-- Kehadiran --}}
                <tr class="sec-row">
                    <td colspan="2">Kehadiran</td>
                </tr>
                <tr>
                    <td>
                        Hari Masuk × Tarif
                        @if ($salaryType === 'daily')
                            ({{ $workDays }} hr × Rp {{ number_format($dailyRate, 0, ',', '.') }})
                        @else
                            ({{ number_format($workHours, 1, ',', '.') }} jam × Rp
                            {{ number_format($hourlyRate, 0, ',', '.') }})
                        @endif
                    </td>
                    <td class="amount">Rp {{ number_format($baseSalary, 0, ',', '.') }}</td>
                </tr>

                {{-- Pendapatan tambahan --}}
                @if ($allowances > 0 || $bonuses > 0)
                    <tr class="sec-row">
                        <td colspan="2">Tambahan</td>
                    </tr>
                    @if ($allowances > 0)
                        <tr>
                            <td>Tunjangan</td>
                            <td class="amount text-success">+ Rp {{ number_format($allowances, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if ($bonuses > 0)
                        <tr>
                            <td>Bonus / PJ</td>
                            <td class="amount text-success">+ Rp {{ number_format($bonuses, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endif

                {{-- Potongan --}}
                @if ($lateDeduction > 0 || $otherDeductions > 0)
                    <tr class="sec-row">
                        <td colspan="2">Potongan</td>
                    </tr>
                    @if ($lateDeduction > 0)
                        <tr>
                            <td class="text-danger">
                                Keterlambatan{{ $lateMinutes > 0 ? ' (' . $lateMinutes . ' mnt)' : '' }}
                            </td>
                            <td class="amount text-danger">- Rp {{ number_format($lateDeduction, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if ($otherDeductions > 0)
                        <tr>
                            <td class="text-danger">Lainnya (Cashbon, dll)</td>
                            <td class="amount text-danger">- Rp {{ number_format($otherDeductions, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endif

                {{-- Total --}}
                <tr class="total-row">
                    <td>TOTAL GAJI DITERIMA</td>
                    <td class="amount text-success">Rp {{ number_format($netSalary, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Catatan (jika ada) --}}
        @if (!empty($detail->calculation_notes))
            <div style="font-size:8px; margin-top:5px; color:#555;">
                <strong>Catatan:</strong> {{ $detail->calculation_notes }}
            </div>
        @endif

        {{-- TANDA TANGAN --}}
        <div class="signature">

            <div class="sig-box">
                Staff Akuntan
                <div class="sig-line">{{ $accountantName ?? '______________________' }}</div>
            </div>
            <div class="sig-box">
                Menerima,
                <div class="sig-line">{{ $employee->name }}</div>
            </div>
        </div>

        <div class="note">
            Dicetak otomatis {{ $tanggalCetak }} &bull; Hubungi Admin jika ada pertanyaan
        </div>

    </div>

</body>

</html>
