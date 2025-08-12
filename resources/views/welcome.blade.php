<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Sistem Payroll KlikMedis - Solusi lengkap pengelolaan gaji, absensi, dan laporan keuangan perusahaan. Terintegrasi dengan perhitungan PPh 21, BPJS, dan otomatisasi payroll.">
        <meta name="keywords" content="payroll, sistem gaji, absensi, PPh 21, BPJS, laporan keuangan, HRIS, Indonesia">
        
        <title>KlikMedis Payroll - Sistem Pengelolaan Gaji Terintegrasi</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- AOS Animation -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

        <!-- Styles -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        
        <style>
            :root {
                --primary-color: #2563eb;
                --secondary-color: #1e40af;
                --accent-color: #3b82f6;
                --success-color: #10b981;
                --warning-color: #f59e0b;
                --danger-color: #ef4444;
                --dark-color: #1f2937;
                --light-color: #f8fafc;
                --text-color: #374151;
                --text-light: #6b7280;
            }
            
            body {
                font-family: 'Instrument Sans', sans-serif;
                line-height: 1.6;
                color: var(--text-color);
            }
            
            .hero-section {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                color: white;
                padding: 100px 0;
                position: relative;
                overflow: hidden;
            }
            
            .hero-section::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
                background-size: cover;
            }
            
            .hero-content {
                position: relative;
                z-index: 2;
            }
            
            .feature-card {
                background: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                height: 100%;
                border: 1px solid #e5e7eb;
            }
            
            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
                border-radius: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
                margin-bottom: 20px;
            }
            
            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
                border: none;
                padding: 12px 30px;
                border-radius: 25px;
                color: white;
                font-weight: 600;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }
            
            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
                color: white;
            }
            
            .btn-outline-custom {
                border: 2px solid var(--primary-color);
                background: transparent;
                color: var(--primary-color);
                padding: 12px 30px;
                border-radius: 25px;
                font-weight: 600;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }
            
            .btn-outline-custom:hover {
                background: var(--primary-color);
                color: white;
            }
            
            .navbar {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container">
                <a class="navbar-brand fw-bold fs-4" href="#">
                    <i class="fas fa-calculator text-primary me-2"></i>
                    KlikMedis Payroll
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Fitur</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#pricing">Harga</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Kontak</a>
                        </li>
                        @if (Route::has('login'))
                            @auth
                                <li class="nav-item">
                                    <a class="btn btn-primary-custom ms-2" href="{{ url('/dashboard') }}">Dashboard</a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                                </li>
                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="btn btn-primary-custom ms-2" href="{{ route('register') }}">Daftar</a>
                                    </li>
                                @endif
                            @endauth
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 hero-content" data-aos="fade-right">
                        <h1 class="display-4 fw-bold mb-4">
                            Sistem Payroll Terintegrasi untuk Perusahaan Modern
                        </h1>
                        <p class="lead mb-4">
                            Kelola gaji, absensi, dan laporan keuangan dengan mudah. 
                            Terintegrasi dengan perhitungan PPh 21, BPJS, dan otomatisasi payroll yang akurat.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4 py-3 fw-bold">
                                <i class="fas fa-rocket me-2"></i>
                                Mulai Gratis
                            </a>
                            <a href="#features" class="btn btn-outline-light btn-lg px-4 py-3">
                                <i class="fas fa-play me-2"></i>
                                Pelajari Lebih Lanjut
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <img src="https://via.placeholder.com/600x400/2563eb/ffffff?text=Payroll+Dashboard" 
                             alt="Payroll Dashboard" class="img-fluid rounded-3 shadow">
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-5">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-lg-8 mx-auto" data-aos="fade-up">
                        <h2 class="display-5 fw-bold mb-3">Fitur Lengkap Payroll System</h2>
                        <p class="lead text-muted">
                            Solusi komprehensif untuk mengelola semua aspek penggajian perusahaan Anda
                        </p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <!-- Employee Management -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Manajemen Karyawan</h4>
                            <p class="text-muted">
                                Kelola data karyawan dengan lengkap termasuk informasi pribadi, 
                                posisi, departemen, dan riwayat kerja dalam satu sistem terpusat.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Data karyawan terpusat</li>
                                <li><i class="fas fa-check text-success me-2"></i>Struktur organisasi</li>
                                <li><i class="fas fa-check text-success me-2"></i>Riwayat kerja</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Payroll Processing -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Proses Payroll Otomatis</h4>
                            <p class="text-muted">
                                Hitung gaji secara otomatis dengan komponen gaji yang fleksibel, 
                                tunjangan, bonus, dan potongan sesuai kebijakan perusahaan.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Perhitungan otomatis</li>
                                <li><i class="fas fa-check text-success me-2"></i>Komponen gaji fleksibel</li>
                                <li><i class="fas fa-check text-success me-2"></i>Bonus & tunjangan</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Tax Calculation -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Perhitungan PPh 21</h4>
                            <p class="text-muted">
                                Perhitungan pajak penghasilan (PPh 21) yang akurat sesuai 
                                regulasi terbaru dengan dukungan PTKP dan tarif progresif.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>PPh 21 otomatis</li>
                                <li><i class="fas fa-check text-success me-2"></i>PTKP terbaru</li>
                                <li><i class="fas fa-check text-success me-2"></i>Laporan pajak</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- BPJS Integration -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Integrasi BPJS</h4>
                            <p class="text-muted">
                                Perhitungan iuran BPJS Kesehatan dan Ketenagakerjaan yang 
                                akurat sesuai dengan ketentuan pemerintah Indonesia.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>BPJS Kesehatan</li>
                                <li><i class="fas fa-check text-success me-2"></i>BPJS Ketenagakerjaan</li>
                                <li><i class="fas fa-check text-success me-2"></i>Laporan BPJS</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Attendance Management -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Sistem Absensi</h4>
                            <p class="text-muted">
                                Kelola kehadiran karyawan dengan sistem check-in/check-out, 
                                lembur, dan cuti dalam satu platform terintegrasi.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Check-in/out</li>
                                <li><i class="fas fa-check text-success me-2"></i>Perhitungan lembur</li>
                                <li><i class="fas fa-check text-success me-2"></i>Manajemen cuti</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Reports & Analytics -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Laporan & Analitik</h4>
                            <p class="text-muted">
                                Dashboard analitik yang informatif dengan laporan payroll, 
                                absensi, dan tren pengeluaran perusahaan yang detail.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Dashboard real-time</li>
                                <li><i class="fas fa-check text-success me-2"></i>Laporan komprehensif</li>
                                <li><i class="fas fa-check text-success me-2"></i>Analitik tren</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-5" style="background: linear-gradient(135deg, var(--dark-color) 0%, #374151 100%); color: white;">
            <div class="container text-center">
                <div class="row justify-content-center">
                    <div class="col-lg-8" data-aos="fade-up">
                        <h2 class="display-5 fw-bold mb-4">Siap untuk Memulai?</h2>
                        <p class="lead mb-4">
                            Bergabunglah dengan ratusan perusahaan yang telah mempercayai 
                            KlikMedis Payroll untuk mengelola sistem penggajian mereka.
                        </p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4 py-3 fw-bold">
                                <i class="fas fa-rocket me-2"></i>
                                Mulai Trial Gratis
                            </a>
                            <a href="#contact" class="btn btn-outline-light btn-lg px-4 py-3">
                                <i class="fas fa-phone me-2"></i>
                                Konsultasi Gratis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer id="contact" class="py-5" style="background: var(--dark-color); color: white;">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-calculator text-primary me-2"></i>
                            KlikMedis Payroll
                        </h5>
                        <p class="text-muted">
                            Solusi lengkap sistem payroll terintegrasi untuk perusahaan modern. 
                            Mengelola gaji, absensi, dan laporan keuangan dengan mudah dan akurat.
                        </p>
                    </div>
                    
                    <div class="col-lg-4">
                        <h6 class="fw-bold mb-3">Kontak</h6>
                        <ul class="list-unstyled">
                            <li class="text-muted"><i class="fas fa-phone me-2"></i>+62 21 1234 5678</li>
                            <li class="text-muted"><i class="fas fa-envelope me-2"></i>info@klikmedis.com</li>
                            <li class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Jakarta, Indonesia</li>
                        </ul>
                    </div>
                    
                    <div class="col-lg-4">
                        <h6 class="fw-bold mb-3">Ikuti Kami</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-white fs-4"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-white fs-4"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white fs-4"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-white fs-4"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="text-muted mb-0">
                            © 2025 KlikMedis Payroll. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                        <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        
        <script>
            // Initialize AOS
            AOS.init({
                duration: 1000,
                easing: 'ease-in-out',
                once: true
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Navbar background on scroll
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                }
            });
        </script>
    </body>
</html>
