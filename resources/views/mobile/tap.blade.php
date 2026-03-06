<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Absensi Mobile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

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

        .btn-logout {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout:active {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(0.95);
        }

        /* End Header */

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

        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #D1B06C 0%, #facc15 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(209, 176, 108, 0.3);
        }

        .user-details h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .user-details p {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        /* Camera Section */
        .camera-container {
            background: white;
            border-radius: 20px;
            padding: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .camera-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 3/4;
            background: #cbd5e1;
            border-radius: 15px;
            overflow: hidden;
            border: 3px solid #f1f5f9;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #video-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
            /* Mirror mode for selfie */
        }

        #image-preview {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .btn-capture {
            margin-top: 15px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #D1B06C;
            position: relative;
            cursor: pointer;
            outline: none;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(209, 176, 108, 0.3);
        }

        .btn-capture::after {
            content: '';
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #D1B06C;
            transition: all 0.2s;
        }

        .btn-capture:active {
            transform: scale(0.9);
        }

        .btn-capture:active::after {
            background: #b49354;
            transform: scale(0.95);
        }

        .action-buttons {
            display: none;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 16px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-retake {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-checkout {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-done {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(100, 116, 139, 0.3);
            cursor: not-allowed;
        }

        /* Location Info */
        .location-card {
            background: white;
            border-radius: 20px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            font-size: 13px;
            color: #64748b;
        }

        .location-icon {
            width: 40px;
            height: 40px;
            background: #e0f2fe;
            color: #0284c7;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .location-text {
            flex: 1;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #fef3c7;
            color: #d97706;
            margin-bottom: 4px;
        }

        .status-badge.success {
            background: #d1fae5;
            color: #059669;
        }

        .status-badge.error {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Add Canvas for Logic but hide from UI */
        #canvas {
            display: none;
        }

        /* Minimap Container */
        #map {
            width: 100%;
            height: 250px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-top: -10px;
            z-index: 1;
            /* Keep map behind sticky header */
        }

        /* Overlay Loading */
        #loader {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        #loader.show {
            opacity: 1;
            pointer-events: all;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #061E48;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #loader-text {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-info">
            <span class="header-title">Absensi Mobile</span>
            <span class="header-subtitle">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
        </div>
        <form action="{{ route('mobile.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </header>

    <main>
        <div class="welcome-card">
            <div class="avatar">{{ substr($employee->name, 0, 1) }}</div>
            <div class="user-details">
                <h2>{{ $employee->name }}</h2>
                <p>{{ $employee->department->name ?? 'Departemen' }} • {{ $employee->employee_number }}</p>
            </div>
        </div>

        <div class="location-card">
            <div class="location-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <div class="location-text">
                <span class="status-badge" id="loc-status">Mencari Lokasi...</span>
                <div id="loc-coords" style="font-weight: 600; color: #334155;">Akses lokasi diperlukan</div>
            </div>
        </div>

        <div id="error-message" class="alert-error"></div>

        <div id="map"></div>

        <div class="camera-container" style="background: transparent; box-shadow: none; padding: 0;">
            @if($attendanceStatus === 'none')
            <button id="btn-submit-att" class="btn btn-submit"
                style="width: 100%; padding: 16px; font-size: 16px; border-radius: 14px; display: none;">Check In
                (Masuk)</button>
            @elseif($attendanceStatus === 'in')
            <button id="btn-submit-att" class="btn btn-checkout"
                style="width: 100%; padding: 16px; font-size: 16px; border-radius: 14px; display: none;">Check Out
                (Pulang)</button>
            @elseif($attendanceStatus === 'done')
            <button id="btn-submit-att" class="btn btn-done" disabled
                style="width: 100%; padding: 16px; font-size: 16px; border-radius: 14px; display: block;">Sudah Absen
                Masuk & Pulang</button>
            @endif
        </div>
    </main>

    <div id="loader">
        <div class="spinner"></div>
        <div id="loader-text">Memproses Absensi...</div>
    </div>

    <!-- Script Kamera dan Geo -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnSubmit = document.getElementById('btn-submit-att');
            const locStatus = document.getElementById('loc-status');
            const locCoords = document.getElementById('loc-coords');
            const errorMsg = document.getElementById('error-message');
            const loader = document.getElementById('loader');

            let currentLat = null;
            let currentLng = null;

            // Inisialisasi Peta Leaflet
            let settingLat = @json($setting ? $setting -> latitude : null);
            let settingLng = @json($setting ? $setting -> longitude : null);
            let settingRadius = @json($setting ? $setting -> radius : null);

            const map = L.map('map').setView([settingLat || -6.200000, settingLng || 106.816666], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let userMarker = null;
            let officeCircle = null;
            let officeMarker = null;

            if (settingLat !== null && settingLng !== null) {
                officeMarker = L.marker([settingLat, settingLng]).addTo(map).bindPopup("Lokasi Kantor");
                if (settingRadius !== null) {
                    officeCircle = L.circle([settingLat, settingLng], {
                        color: '#0284c7',
                        fillColor: '#0284c7',
                        fillOpacity: 0.1,
                        radius: settingRadius
                    }).addTo(map);
                }
            }

            // Helper to compute distance in meters
            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3;
                const p1 = lat1 * Math.PI / 180;
                const p2 = lat2 * Math.PI / 180;
                const dp = (lat2 - lat1) * Math.PI / 180;
                const dl = (lon2 - lon1) * Math.PI / 180;

                const a = Math.sin(dp / 2) * Math.sin(dp / 2) +
                    Math.cos(p1) * Math.cos(p2) *
                    Math.sin(dl / 2) * Math.sin(dl / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                return R * c;
            }

            // 1. Dapatkan Lokasi GPS
            function requestLocation() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            currentLat = position.coords.latitude;
                            currentLng = position.coords.longitude;

                            if (settingLat !== null && settingLng !== null && settingRadius !== null) {
                                const dist = calculateDistance(settingLat, settingLng, currentLat, currentLng);
                                if (dist <= settingRadius) {
                                    locStatus.textContent = 'on site';
                                    locStatus.className = 'status-badge success';
                                } else {
                                    locStatus.textContent = 'diluar radius';
                                    locStatus.className = 'status-badge error';
                                }
                            } else {
                                locStatus.textContent = 'Lokasi Ditemukan';
                                locStatus.className = 'status-badge success';
                            }

                            locCoords.textContent = `${currentLat.toFixed(5)}, ${currentLng.toFixed(5)}`;

                            // Update User Marker
                            if (!userMarker) {
                                userMarker = L.marker([currentLat, currentLng], {
                                    icon: L.icon({
                                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                        iconSize: [25, 41],
                                        iconAnchor: [12, 41],
                                        popupAnchor: [1, -34],
                                        shadowSize: [41, 41]
                                    })
                                }).addTo(map).bindPopup("Lokasi Anda").openPopup();
                            } else {
                                userMarker.setLatLng([currentLat, currentLng]);
                            }

                            // Sesuaikan Tampilan Peta
                            if (officeCircle) {
                                map.fitBounds(officeCircle.getBounds().extend(userMarker.getLatLng()), { padding: [20, 20] });
                            } else {
                                map.setView([currentLat, currentLng], 17);
                            }

                            // Tampilkan tombol submit setelah lokasi didapat (hanya jika belum full absen)
                            @if ($attendanceStatus !== 'done')
                                btnSubmit.style.display = 'block';
                            @endif
                        },
                        (error) => {
                            let msg = 'Gagal akses lokasi';
                            if (error.code === 1) msg = 'Akses lokasi ditolak pengguna';
                            locStatus.textContent = 'Error';
                            locStatus.className = 'status-badge error';
                            locCoords.textContent = msg;
                            showError('Silakan izinkan akses lokasi (GPS) pada browser Anda untuk absensi.');
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                    locStatus.textContent = 'Tidak Didukung';
                    locStatus.className = 'status-badge error';
                    locCoords.textContent = 'Browser tidak mendukung GPS';
                }
            }

            // Tampilkan error umum
            function showError(message) {
                errorMsg.textContent = message;
                errorMsg.style.display = 'block';
                setTimeout(() => { errorMsg.style.display = 'none'; }, 5000);
            }

            // Init
            requestLocation();

            // SUBMIT ABSENSI
            btnSubmit.addEventListener('click', async () => {
                if (btnSubmit.disabled) return;

                if (!currentLat || !currentLng) {
                    showError('Data lokasi belum ditemukan.');
                    return;
                }

                loader.classList.add('show');

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch('/api/attendance/mobile-tap', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            image_token: "", // Foto ditiadakan, kirim empty string
                            latitude: currentLat,
                            longitude: currentLng
                        })
                    });

                    const data = await response.json();

                    loader.classList.remove('show');

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: data.message || 'Gagal mengirim data absensi.',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    }

                } catch (err) {
                    console.error(err);
                    loader.classList.remove('show');
                    Swal.fire({
                        title: 'Error Jaringan',
                        text: 'Terjadi kesalahan jaringan.',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        });
    </script>
</body>

</html>