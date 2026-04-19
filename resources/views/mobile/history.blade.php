<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Absensi</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/bgn.svg') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#061E48">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    <link rel="manifest" href="/manifest.json">

    <!-- Tailwind CSS (for Laravel Pagination Styling Compatibility) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #061E48 0%, #0f3d8a 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-info {
            display: flex;
            flex-direction: column;
        }

        .header-title {
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .header-subtitle {
            font-size: 13px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:active {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(0.95);
        }

        main {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .history-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 300px;
        }

        th,
        td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge.success {
            background: #d1fae5;
            color: #059669;
        }

        .pagination-container {
            margin-top: 15px;
        }

        .pagination-container nav div {
            font-size: 12px;
        }

        .pagination-container nav span,
        .pagination-container nav a {
            padding: 8px 12px;
            border-radius: 8px;
            margin-right: 2px;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-info">
            <span class="header-title">Riwayat Absensi</span>
            <span class="header-subtitle">{{ $employee->name }}</span>
        </div>
        <a href="{{ route('mobile.tap') }}" class="btn-back">Kembali</a>
    </header>

    <main>
        <div class="history-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Masuk</th>
                            <th>Pulang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('d M Y') }}</td>
                            <td>
                                @if($attendance->check_in_at)
                                <span class="status-badge success">{{
                                    \Carbon\Carbon::parse($attendance->check_in_at)->format('H:i') }}</span>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($attendance->check_out_at)
                                <span class="status-badge success">{{
                                    \Carbon\Carbon::parse($attendance->check_out_at)->format('H:i') }}</span>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada riwayat
                                absensi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                {{ $attendances->links() }}
            </div>
        </div>
    </main>

</body>

</html>