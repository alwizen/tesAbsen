<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Absensi Pegawai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #061E48 0%, #D1B06C 50%, #B5E0E9 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 30px;
            box-shadow:
                0 30px 90px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.3) inset;
            text-align: center;
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .icon-wrapper {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #D1B06C 0%, #061E48 100%);
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 15px 35px rgba(209, 176, 108, 0.4);
            animation: iconPulse 3s ease-in-out infinite;
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 15px 35px rgba(209, 176, 108, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 20px 45px rgba(209, 176, 108, 0.6);
            }
        }

        .icon-wrapper img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        h1 {
            font-size: 28px;
            background: linear-gradient(135deg, #061E48 0%, #D1B06C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 35px;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.5;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 25px;
            text-align: left;
        }

        .input-label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            padding-left: 5px;
        }

        input {
            width: 100%;
            padding: 18px 20px;
            font-size: 18px;
            text-align: left;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #1e293b;
            font-weight: 600;
            letter-spacing: 1px;
        }

        input::placeholder {
            color: #cbd5e1;
            letter-spacing: 0;
            font-weight: 500;
        }

        input:focus {
            border-color: #D1B06C;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(209, 176, 108, 0.1);
            transform: translateY(-2px);
        }

        .btn-submit {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #061E48 0%, #0f3d8a 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(6, 30, 72, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(6, 30, 72, 0.4);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .alert {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
            animation: fadeIn 0.3s ease;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="icon-wrapper">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Logo_Badan_Gizi_Nasional_%282024%29.png"
                alt="Logo">
        </div>

        <h1>Login Absensi</h1>
        <p class="subtitle">Silakan masukkan nomor HP Anda yang terdaftar untuk melanjutkan absensi</p>

        @if(session('error'))
        <div class="alert">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('mobile.login') }}" method="POST">
            @csrf
            <div class="input-wrapper">
                <label class="input-label" for="phone">Nomor HP</label>
                <input type="tel" id="phone" name="phone" placeholder="Contoh: 08123456789" required autocomplete="tel"
                    autofocus>
            </div>

            <button type="submit" class="btn-submit">
                Lanjutkan
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <p class="footer-text">Sistem Absensi Pegawai Berbasis Lokasi</p>
    </div>

</body>

</html>