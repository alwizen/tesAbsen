<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tap Kartu RFID</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
            padding: 60px 50px;
            width: 100%;
            max-width: 500px;
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
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #D1B06C 0%, #061E48 100%);
            border-radius: 50%;
            margin: 0 auto 30px;
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

        .icon-wrapper::before {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            border-radius: 50%;
            background: linear-gradient(135deg, #D1B06C 0%, #061E48 100%);
            opacity: 0.3;
            animation: ripple 2s ease-out infinite;
        }

        @keyframes ripple {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        .icon-wrapper svg {
            width: 50px;
            height: 50px;
            fill: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .icon-wrapper img {
            width: 65px;
            height: 65px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #061E48 0%, #D1B06C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 40px;
            font-size: 17px;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 22px 25px;
            font-size: 20px;
            text-align: center;
            border: 3px solid transparent;
            border-radius: 16px;
            outline: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(white, white) padding-box,
                linear-gradient(135deg, #D1B06C, #061E48) border-box;
            font-weight: 600;
            letter-spacing: 2px;
            color: #1e293b;
        }

        input::placeholder {
            color: #94a3b8;
            letter-spacing: 0;
            font-weight: 500;
        }

        input:focus {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(209, 176, 108, 0.3);
            background: linear-gradient(white, white) padding-box,
                linear-gradient(135deg, #D1B06C, #061E48) border-box;
        }

        .instruction {
            color: #64748b;
            font-size: 15px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 500;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            animation: pulseGlow 2s infinite;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        @keyframes pulseGlow {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            50% {
                transform: scale(1.1);
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 50px;
            border-radius: 30px;
            width: 90%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.4);
            animation: modalBounce 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
        }

        @keyframes modalBounce {
            0% {
                transform: scale(0.7) translateY(50px);
                opacity: 0;
            }

            50% {
                transform: scale(1.05) translateY(-10px);
            }

            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: iconBounce 0.6s ease-out;
        }

        @keyframes iconBounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .modal-icon.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        }

        .modal-icon.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 15px 40px rgba(239, 68, 68, 0.4);
        }

        .modal-icon::before {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            border-radius: 50%;
            background: inherit;
            opacity: 0.3;
            animation: modalRipple 1.5s ease-out infinite;
        }

        @keyframes modalRipple {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }

        .modal-icon svg {
            width: 45px;
            height: 45px;
            fill: white;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .modal-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .modal-message {
            font-size: 17px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 500;
        }

        .modal-close {
            background: linear-gradient(135deg, #D1B06C 0%, #061E48 100%);
            color: white;
            border: none;
            padding: 16px 50px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(209, 176, 108, 0.4);
            position: relative;
            overflow: hidden;
        }

        .modal-close::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .modal-close:hover::before {
            left: 100%;
        }

        .modal-close:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(209, 176, 108, 0.5);
        }

        .modal-close:active {
            transform: translateY(-1px);
        }

        /* Loading animation */
        .loading {
            display: none;
            margin-top: 25px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            width: 50px;
            height: 50px;
            margin: 0 auto;
            border: 5px solid rgba(209, 176, 108, 0.2);
            border-top: 5px solid #D1B06C;
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 45px 30px;
            }

            h1 {
                font-size: 26px;
            }

            .modal-content {
                padding: 40px 30px;
            }

            input {
                font-size: 18px;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="icon-wrapper">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Logo_Badan_Gizi_Nasional_%282024%29.png"
                alt="Logo Badan Gizi Nasional">
        </div>

        <h1>Absensi Karyawan</h1>
        <p class="subtitle">Tempelkan kartu RFID Anda pada reader</p>

        <form id="tapForm">
            <div class="input-wrapper">
                <input type="text" id="rfid_number" name="rfid_number" placeholder="Menunggu kartu..." autofocus
                    autocomplete="off">
            </div>
        </form>

        <div class="instruction">
            <span class="pulse-dot"></span>
            <span>Sistem siap menerima kartu</span>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-icon" id="modalIcon">
                <svg id="successIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="display: none;">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <svg id="errorIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="display: none;">
                    <path
                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
            <h2 class="modal-title" id="modalTitle"></h2>
            <p class="modal-message" id="modalMessage"></p>
            <button class="modal-close" id="modalClose">Tutup</button>
        </div>
    </div>

    <script>
        const form = document.getElementById('tapForm');
        const input = document.getElementById('rfid_number');
        const modal = document.getElementById('modal');
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalClose = document.getElementById('modalClose');
        const successIcon = document.getElementById('successIcon');
        const errorIcon = document.getElementById('errorIcon');
        const loading = document.getElementById('loading');

        let autoCloseTimer;

        function showModal(success, title, message) {
            clearTimeout(autoCloseTimer);

            modalIcon.className = 'modal-icon ' + (success ? 'success' : 'error');

            if (success) {
                successIcon.style.display = 'block';
                errorIcon.style.display = 'none';
            } else {
                successIcon.style.display = 'none';
                errorIcon.style.display = 'block';
            }

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modal.classList.add('show');

            autoCloseTimer = setTimeout(() => {
                hideModal();
            }, 1500);
        }

        function hideModal() {
            clearTimeout(autoCloseTimer);
            modal.classList.remove('show');
            input.value = '';
            input.focus();
        }

        modalClose.addEventListener('click', hideModal);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const rfid = input.value.trim();
            if (!rfid) return;

            loading.classList.add('show');
            input.disabled = true;

            fetch('/api/attendance/tap', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rfid_number: rfid
                    })
                })
                .then(res => res.json())
                .then(data => {
                    loading.classList.remove('show');
                    input.disabled = false;

                    if (data.success) {
                        showModal(true, 'Berhasil!', data.message);
                    } else {
                        showModal(false, 'Gagal', data.message ?? 'Gagal memproses tap kartu');
                    }
                })
                .catch(() => {
                    loading.classList.remove('show');
                    input.disabled = false;
                    showModal(false, 'Error', 'Koneksi ke server gagal. Silakan coba lagi.');
                });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                hideModal();
            }
        });
    </script>

</body>

</html>
