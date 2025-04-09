<?php
session_start();  // Memulai session

include '../../koneksi.php';
// Ambil database
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
            background-image: url("../../images/Dashboard.jpeg");
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
        }

        .room-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .room-card:hover {
            transform: translateY(-5px);
        }

        .search-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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

        .iframe-container {
            position: relative;
            overflow: hidden;
            right:-250px ;
        }

        
  @media screen and (max-width: 768px) {
    .iframe-container{
        right: 0;
    }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand">Hotel Shiro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="listkamar.php">Kamar</a>
                    </li>
                </ul>
                <h5 id="profile-name" style="color:#f8f9fa"><?php echo htmlspecialchars($username); ?></h5>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Selamat Datang di Hotel Shiro</h1>
            <p class="lead">Nikmati pengalaman menginap tak terlupakan dengan fasilitas mewah dan pelayanan terbaik</p>
        </div>
    </div>


  <!-- Kamar  -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Kamar Unggulan</h2>
        <div class="row">
            <!-- Kamar Standard-->
            <div class="col-md-4">
                <div class="card room-card">
                    <img src="../../Images/HotelStandar.jpeg" class="card-img-top" alt="Standard Room">
                    <div class="card-body">
                        <h5 class="card-title">Standard Room</h5>
                        <p class="card-text">Kamar nyaman dengan tempat tidur queen size, ideal untuk perjalanan bisnis atau liburan singkat.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rp 500.000/malam</span>
                            <a href="../client/detailkamarstandar.php" class="btn btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kamar deluxe-->
            <div class="col-md-4">
                <div class="card room-card">
                    <img src="../../Images/HotelDeluxe.jpeg" class="card-img-top" alt="Deluxe Room">
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

            <!-- Kamar suite -->
            <div class="col-md-4">
                <div class="card room-card">
                    <img src="../../Images/HotelSuite.jpeg" class="card-img-top" alt="Suite Room">
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

    <!-- Alamat Hotel -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Alamat</h2>
        <div class="iframe-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3238.7409202637746!2d139.7047509741392!3d35.73258972715379!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188dedf33e7059%3A0xea32621efa99d9e2!2zaG90ZWwgU2lybyDmsaDooovvvIjjg5vjg4bjg6sm44Kw44Op44Oz44OU44Oz44Kw77yJ!5e0!3m2!1sid!2sid!4v1744159975962!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

    </div>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Hotel Shiro</h5>
                </div>
                <div class="col-md-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="listkamar.php" class="text-white">Kamar</a></li>
                    </ul>
                </div>