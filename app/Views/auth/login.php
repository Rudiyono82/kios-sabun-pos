<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login POS Kios Sabun & Parfum') ?></title>
    
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            max-width: 980px;
            width: 100%;
            position: relative;
        }

        /* 1. ANIMASI AMBIENT PROGRESS BAR PADA SCREEN LOGIN */
        .ambient-progress-bar {
            height: 4px;
            width: 100%;
            background: rgba(2, 132, 199, 0.12);
            position: absolute;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 10;
        }
        .ambient-progress-bar::after {
            content: "";
            position: absolute;
            top: 0;
            left: -50%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #38bdf8, #0284c7, #2563eb, transparent);
            animation: ambientSlide 2.2s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes ambientSlide {
            0% { left: -50%; }
            100% { left: 100%; }
        }

        /* Brand Badge Logo */
        .brand-badge-login {
            width: 44px;
            height: 44px;
            min-width: 44px;
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(13, 110, 253, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .brand-badge-login:hover {
            transform: scale(1.05);
        }
        .brand-title-login {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            letter-spacing: 0.4px;
        }
        .brand-subtitle-login {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            display: block;
            margin-top: 2px;
        }
        
        /* Panel Kiri: Form Login User */
        .form-section {
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Panel Kanan: Background Kios Sabun & Parfum Laundry */
        .banner-section {
            background: linear-gradient(rgba(15, 23, 42, 0.65), rgba(3, 105, 161, 0.75)), 
                        url('https://images.unsplash.com/photo-1583947215259-38e31be8751f?q=80&w=1200&auto=format&fit=crop') center/cover no-repeat;
            color: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
        }
        .btn-login {
            background: linear-gradient(90deg, #0284c7 0%, #0369a1 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
            transition: all 0.25s;
        }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45);
            color: white;
        }
        .btn-login:disabled {
            opacity: 0.85;
            cursor: not-allowed;
        }
        .badge-attempt {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }
        .clock-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 14px 20px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        .feature-pill {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        /* Shake animation for failed login */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
        .shake-form {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- AMBIENT PROGRESS BAR AT TOP OF CARD -->
    <div class="ambient-progress-bar"></div>

    <div class="row g-0">
        <!-- SISI KIRI: PANEL LOGIN USER -->
        <div class="col-12 col-md-6 form-section" id="form-container">
            <!-- BRAND LOGO SESUAI GAYA FOTO SEBELAH KANAN -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="brand-badge-login">
                    <i class="bi bi-droplet-half fs-4 text-white"></i>
                </div>
                <div>
                    <div class="brand-title-login">KIOS SABUN &amp; PARFUM</div>
                    <span class="brand-subtitle-login">METLAND CILEUNGSI</span>
                </div>
            </div>

            <div class="mb-3">
                <h4 class="fw-bold text-dark mb-1">Selamat Datang, Silahkan Login</h4>
                <p class="text-muted small">Akses mesin kasir Point of Sales & operasional toko.</p>
            </div>

            <!-- Pesan Error / Alert Dinamis -->
            <div id="alert-container">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2 px-3 border-0 shadow-sm mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2 px-3 border-0 shadow-sm mb-3" role="alert">
                        <i class="bi bi-check-circle-fill me-1"></i> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Indikator Kesempatan Login (Setara VB6: Kesempatan Login Anda : 3 Kali) -->
            <div class="mb-3">
                <span class="badge-attempt" id="badge-attempt">
                    <i class="bi bi-stopwatch"></i> Kesempatan Login Anda: <span id="span-attempts"><?= esc($remaining_attempts) ?></span> Kali
                </span>
            </div>

            <form action="<?= base_url('auth/process') ?>" method="POST" id="formLogin">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Kode User</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person fs-5"></i></span>
                        <input type="text" name="kode_user" id="input-kode-user" class="form-control border-start-0 ps-0 text-uppercase" placeholder="KASIR1 / ADMIN" autofocus required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-key fs-5"></i></span>
                        <input type="password" name="password" id="input-password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                        <button class="btn btn-outline-secondary border-start-0 border" type="button" id="btn-toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. ANIMASI PROGRESS BAR SETELAH KLIK LOGIN -->
                <div id="login-progress-wrapper" class="d-none mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-primary d-flex align-items-center gap-2" id="login-progress-text">
                            <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            <span id="login-progress-status">Memverifikasi kredensial...</span>
                        </span>
                        <span class="small fw-bold font-monospace text-muted" id="login-progress-percent">0%</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 999px; background-color: #e2e8f0; overflow: hidden;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="login-progress-bar" role="progressbar" style="width: 0%; transition: width 0.35s ease, background-color 0.3s ease;"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100 d-flex align-items-center justify-content-center gap-2 mb-3" id="btnSubmitLogin">
                    <i class="bi bi-box-arrow-in-right fs-5" id="btn-icon"></i>
                    <span id="btn-text">Login ke Program</span>
                </button>

                <!-- Akun Bantuan Cepat -->
                <div class="p-3 bg-light rounded-3 border text-center small text-muted">
                    <span class="fw-semibold text-dark">Akun Login Bawaan:</span><br>
                    Kasir: <code>KASIR1</code> (pass: <code>kasir123</code>) &bull; Admin: <code>ADMIN</code> (pass: <code>admin123</code>)
                </div>
            </form>
        </div>

        <!-- SISI KANAN: BACKGROUND GAMBAR KIOS SABUN & PARFUM LAUNDRY -->
        <div class="col-12 col-md-6 banner-section">
            <div>
                <span class="badge bg-white bg-opacity-25 px-3 py-2 rounded-pill fw-medium mb-3">
                    <i class="bi bi-patch-check-fill text-warning me-1"></i> Web POS Modern KSP
                </span>
                <h3 class="fw-bold mb-2">Pusat Refill Sabun, Deterjen & Parfum Laundry</h3>
                <p class="text-white-50 small mb-4">
                    Melayani penjualan eceran, grosir per dus/jerigen, dan penukaran jerigen kosong 5 Liter pelanggan.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="feature-pill"><i class="bi bi-recycle text-success"></i> Tukar Jerigen 5L @Rp 1.000</span>
                    <span class="feature-pill"><i class="bi bi-upc-scan text-info"></i> Barcode Scanner</span>
                    <span class="feature-pill"><i class="bi bi-gem text-warning"></i> Poin Member</span>
                </div>
            </div>

            <div>
                <!-- Jam & Tanggal Sinkron Server Real-time -->
                <div class="clock-badge mb-3">
                    <i class="bi bi-clock-history fs-3 text-warning"></i>
                    <div>
                        <div class="fw-bold fs-5" id="live-time">--:--:-- WIB</div>
                        <small class="text-white-50" id="live-date"><?= date('l, d F Y') ?></small>
                    </div>
                </div>

                <div class="small text-white-50 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-geo-alt-fill text-danger"></i> Metland Cileungsi Blok BD.04</span>
                    <span>Versi 2.0 Web CI4</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Jam Real-time
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('live-time').innerText = timeStr + ' WIB';
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Toggle Password Visibility
    const btnToggle = document.getElementById('btn-toggle-password');
    const inputPass = document.getElementById('input-password');
    btnToggle.addEventListener('click', function() {
        if (inputPass.type === 'password') {
            inputPass.type = 'text';
            btnToggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
        } else {
            inputPass.type = 'password';
            btnToggle.innerHTML = '<i class="bi bi-eye"></i>';
        }
    });

    // ========================================================
    // LOGIKA ANIMASI PROGRESS BAR SETELAH KLIK LOGIN
    // ========================================================
    const formLogin = document.getElementById('formLogin');
    const progressWrapper = document.getElementById('login-progress-wrapper');
    const progressBar = document.getElementById('login-progress-bar');
    const progressStatus = document.getElementById('login-progress-status');
    const progressPercent = document.getElementById('login-progress-percent');
    const btnSubmit = document.getElementById('btnSubmitLogin');
    const btnIcon = document.getElementById('btn-icon');
    const btnText = document.getElementById('btn-text');
    const alertContainer = document.getElementById('alert-container');
    const spanAttempts = document.getElementById('span-attempts');
    const formContainer = document.getElementById('form-container');

    formLogin.addEventListener('submit', function(e) {
        e.preventDefault();

        const kodeUser = document.getElementById('input-kode-user').value.trim();
        const password = inputPass.value.trim();

        if (!kodeUser || !password) {
            return;
        }

        // 1. Tampilkan Progress Bar & Ubah Tombol ke Loading State
        progressWrapper.classList.remove('d-none');
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
        progressBar.style.width = '20%';
        progressPercent.innerText = '20%';
        progressStatus.innerText = 'Memvalidasi kredensial pengguna...';

        btnSubmit.disabled = true;
        btnIcon.className = 'spinner-border spinner-border-sm';
        btnText.innerText = 'Memproses Login...';

        // 2. Simulasi Tahap Step 1 -> 50%
        setTimeout(() => {
            progressBar.style.width = '55%';
            progressPercent.innerText = '55%';
            progressStatus.innerText = 'Memeriksa otorisasi hak akses...';
        }, 300);

        // 3. Kirim AJAX ke Server
        const formData = new FormData(formLogin);

        fetch(formLogin.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async (response) => {
            const data = await response.json().catch(() => null);

            if (response.ok && data && data.status === 'success') {
                // Sukses Login -> 100% Hijau
                progressBar.style.width = '85%';
                progressPercent.innerText = '85%';
                progressStatus.innerText = 'Sinkronisasi sesi & database POS...';

                setTimeout(() => {
                    progressBar.style.width = '100%';
                    progressBar.className = 'progress-bar bg-success';
                    progressPercent.innerText = '100%';
                    progressStatus.innerText = 'Login Berhasil! Mengalihkan...';

                    btnSubmit.className = 'btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 mb-3';
                    btnIcon.className = 'bi bi-check-circle-fill fs-5';
                    btnText.innerText = 'Berhasil Masuk!';

                    setTimeout(() => {
                        window.location.href = data.redirect || '<?= base_url('dashboard') ?>';
                    }, 500);
                }, 400);

            } else {
                // Gagal Login -> Merah & Tampilkan Pesan Error
                const errMsg = (data && data.message) ? data.message : 'Gagal melakukan login. Silakan periksa kembali data Anda.';
                
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-danger';
                progressPercent.innerText = 'Gagal';
                progressStatus.innerText = 'Autentikasi Gagal!';

                // Update Sisa Percobaan jika ada
                if (data && typeof data.remaining_attempts !== 'undefined') {
                    spanAttempts.innerText = data.remaining_attempts;
                }

                // Shake Efek
                formContainer.classList.add('shake-form');
                setTimeout(() => formContainer.classList.remove('shake-form'), 600);

                // Tampilkan Alert Error
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2 px-3 border-0 shadow-sm mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> ${errMsg}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                `;

                // Kembalikan Tombol setelah 1 detik
                setTimeout(() => {
                    btnSubmit.disabled = false;
                    btnSubmit.className = 'btn btn-login w-100 d-flex align-items-center justify-content-center gap-2 mb-3';
                    btnIcon.className = 'bi bi-box-arrow-in-right fs-5';
                    btnText.innerText = 'Login ke Program';
                    progressWrapper.classList.add('d-none');
                    progressBar.style.width = '0%';
                }, 1200);
            }
        })
        .catch((err) => {
            // Fallback jika terjadi error koneksi
            formLogin.submit();
        });
    });
</script>
</body>
</html>
