
<?php
session_start();  // Memulai session


// Ambil data dari session
$username = $_SESSION['username'];  // Mengambil username dari session

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Paradise - Pemesanan Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/hotel-banner.jpg');
            background-size: cover;
            background-position: center;
            height: 70vh;
            color: white;
            display: flex;
            align-items: center;
        }
        .room-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .room-card:hover {
            transform: translateY(-5px);
        }
        .search-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            margin-top: -50px;
        }
        .feature-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .review-card {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        footer {
            background-color: #343a40;
            color: white;
            padding: 40px 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Hotel Paradise</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rooms.php">Kamar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="facilities.php">Fasilitas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Kontak</a>
                    </li>
                </ul>
                    <h5 id="profile-name" style="color:#f8f9fa"><?php echo htmlspecialchars($username); ?></h5>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Selamat Datang di Hotel Paradise</h1>
            <p class="lead">Nikmati pengalaman menginap tak terlupakan dengan fasilitas mewah dan pelayanan terbaik</p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <form class="search-form" action="cari_kamar.php" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Check-in</label>
                            <input type="date" class="form-control" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check-out</label>
                            <input type="date" class="form-control" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tamu</label>
                            <select class="form-select" name="guests">
                                <option value="1">1 Tamu</option>
                                <option value="2" selected>2 Tamu</option>
                                <option value="3">3 Tamu</option>
                                <option value="4">4 Tamu</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipe Kamar</label>
                            <select class="form-select" name="room_type">
                                <option value="">Semua</option>
                                <option value="1">Standard</option>
                                <option value="2">Deluxe</option>
                                <option value="3">Suite</option>
                                <option value="4">Family</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="../../cari"><button class="btn btn-primary w-100">Cari Kamar</button></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Featured Rooms -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Kamar Unggulan</h2>
        <div class="row">
            <!-- Room Card 1 -->
            <div class="col-md-4">
                <div class="card room-card">
                    <img src="images/standard.jpg" class="card-img-top" alt="Standard Room">
                    <div class="card-body">
                        <h5 class="card-title">Standard Room</h5>
                        <p class="card-text">Kamar nyaman dengan tempat tidur queen size, ideal untuk perjalanan bisnis atau liburan singkat.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rp 500.000/malam</span>
                            <a href="../client/detailkamarunggul.php" class="btn btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Room Card 2 -->
            <div class="col-md-4">
                <div class="card room-card">
                    <img src="images/deluxe.jpg" class="card-img-top" alt="Deluxe Room">
                    <div class="card-body">
                        <h5 class="card-title">Deluxe Room</h5>
                        <p class="card-text">Kamar luas dengan tempat tidur king size, pemandangan kota, dan fasilitas premium.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rp 750.000/malam</span>
                            <a href="../client/detailkamardeluxe.php" class="btn btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Room Card 3 -->
            <div class="col-md-4">
                <div class="card room-card">
                    <img src="images/suite.jpg" class="card-img-top" alt="Suite Room">
                    <div class="card-body">
                        <h5 class="card-title">Suite Room</h5>
                        <p class="card-text">Kamar mewah dengan ruang tamu terpisah, jacuzzi pribadi, dan layanan butler.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rp 1.500.000/malam</span>
                            <a href="../client/detailkamarsuite.php" class="btn btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="../client/listkamar.php" class="btn btn-primary">Lihat Semua Kamar</a>
        </div>
    </div>

    <!-- Facilities -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Fasilitas Kami</h2>
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h5>Wi-Fi Gratis</h5>
                <p>Koneksi internet cepat di seluruh area hotel</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-swimming-pool"></i>
                </div>
                <h5>Kolam Renang</h5>
                <p>Kolam renang outdoor dengan pemandangan kota</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h5>Restoran</h5>
                <p>Restoran dengan beragam menu lokal dan internasional</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-spa"></i>
                </div>
                <h5>Spa & Fitness</h5>
                <p>Pusat kebugaran dan spa untuk relaksasi Anda</p>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Testimoni Tamu</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="review-card">
                    <div class="d-flex align-items-center mb-2">
                        <img src="images/avatar1.jpg" alt="User" class="rounded-circle me-2" width="40">
                        <div>
                            <h6 class="mb-0">Budi Santoso</h6>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p>"Pelayanan sangat memuaskan, kamar bersih dan nyaman. Akan kembali lagi di lain waktu!"</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="d-flex align-items-center mb-2">
                        <img src="images/avatar2.jpg" alt="User" class="rounded-circle me-2" width="40">
                        <div>
                            <h6 class="mb-0">Siti Rahayu</h6>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p>"Lokasi strategis, dekat dengan pusat perbelanjaan. Sarapan buffet enak dengan banyak pilihan."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="d-flex align-items-center mb-2">
                        <img src="images/avatar3.jpg" alt="User" class="rounded-circle me-2" width="40">
                        <div>
                            <h6 class="mb-0">Andi Wijaya</h6>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p>"Suite room sangat mewah, pemandangan kota dari kamar luar biasa. Staff hotel ramah dan helpfull."</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Hotel Paradise</h5>
                    <p>Jl. Merdeka No. 123<br>Jakarta, Indonesia<br>Telp: (021) 1234-5678<br>Email: info@hotelparadise.com</p>
                </div>
                <div class="col-md-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="rooms.php" class="text-white">Kamar</a></li>
                        <li><a href="facilities.php" class="text-white">Fasilitas</a></li>
                        <li><a href="contact.php" class="text-white">Kontak</a></li>
                        <li><a href="about.php" class="text-white">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Sosial Media</h5>
                    <div class="d-flex gap-3 fs-4">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-