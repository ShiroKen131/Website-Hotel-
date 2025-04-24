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
    <title>Hotel Shiro - Pemesanan Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background-image: url("../../images/image1.jpg");
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
            background: #199fd4;
            background: linear-gradient(90deg, rgba(25, 159, 212, 1) 0%, rgba(87, 122, 199, 1) 59%, rgba(67, 136, 171, 1) 100%);
            color: white;
            padding: 40px 0;
        }

        .iframe-container {
  display: flex;
  justify-content: center;
  align-items: center; /* kalau mau vertikal tengah juga */
  height: 100vh; /* atau sesuaikan dengan kebutuhan */
}

.foto-gallery {
  display: flex;
  gap: 20px;
}

.column {
  display: flex;
  flex-direction: column;
  gap:20px;
  margin-top: 20px;
}


        @media screen and (max-width: 768px) {
            .iframe-container {
                right: 0;
            }
        }

        h6, p {
  font-family: sans-serif;
}
    .container-gambar {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.2rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background:  #3498db;
            border-radius: 2px;
        }
        
        .slider-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* Aspect ratio 16:9 */
        }
        
        .slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            transition: transform 0.7s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        
        .slide {
            min-width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease-out;
        }
        
        .caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, transparent 100%);
            color: white;
            padding: 20px 15px 15px;
            text-align: left;
        }
        
        .caption h3 {
            font-size: 1.4rem;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .caption p {
            font-size: 0.95rem;
            opacity: 0.9;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: #3498db;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(3px);
            z-index: 10;
        }
        
        .nav-btn:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        .prev-btn {
            left: 15px;
        }
        
        .next-btn {
            right: 15px;
        }
        
        .controls-container {
            margin-top: 15px;
            width: 100%;
        }
        
        .dots-container {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .dot {
            width: 10px;
            height: 10px;
            background-color: #ddd;
            border-radius: 50%;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .dot.active {
            background-color: #3498db;
        }
        
        .thumbnails {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .thumbnail {
            width: 70px;
            height: 45px;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            opacity: 0.7;
            flex-shrink: 0;
        }
        
        .thumbnail:hover {
            opacity: 1;
        }
        
        .thumbnail.active {
            border-color: #3498db;
            opacity: 1;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background-color: rgba(52, 152, 219, 0.7);
            width: 0%;
            transition: width 0.1s linear;
            z-index: 11;
        }

        /* Media Queries for Responsive Design */
        /* Mobile Devices (Small) */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            h1 {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
            
            .slider-container {
                border-radius: 8px;
                padding-bottom: 70%; /* Taller aspect ratio for mobile */
            }
            
            .nav-btn {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }
            
            .prev-btn {
                left: 8px;
            }
            
            .next-btn {
                right: 8px;
            }
            
            .caption {
                padding: 12px 10px 10px;
            }
            
            .caption h3 {
                font-size: 1rem;
                margin-bottom: 4px;
            }
            
            .caption p {
                font-size: 0.8rem;
                -webkit-line-clamp: 1; /* Show only 1 line on very small screens */
            }
            
            .thumbnail {
                width: 50px;
                height: 34px;
            }
            
            .dot {
                width: 8px;
                height: 8px;
                margin: 3px;
            }
        }
        
        /* Mobile Devices (Medium) */
        @media (min-width: 481px) and (max-width: 767px) {
            h1 {
                font-size: 1.8rem;
            }
            
            .slider-container {
                padding-bottom: 65%; /* Adjusted aspect ratio */
            }
            
            .nav-btn {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }
            
            .caption h3 {
                font-size: 1.1rem;
            }
            
            .caption p {
                font-size: 0.85rem;
            }
        }
        
        /* Tablets */
        @media (min-width: 768px) and (max-width: 1023px) {
            .slider-container {
                padding-bottom: 60%; /* Adjusted aspect ratio */
            }
            
            .caption h3 {
                font-size: 1.2rem;
            }
        }
        
        /* Landscape Orientation */
        @media (max-height: 500px) and (orientation: landscape) {
            .slider-container {
                padding-bottom: 50%; /* Wider aspect ratio for landscape */
            }
            
            .caption {
                padding-top: 10px;
            }
            
            .caption h3 {
                font-size: 1rem;
                margin-bottom: 3px;
            }
            
            .caption p {
                -webkit-line-clamp: 1;
                font-size: 0.8rem;
            }
            
            .nav-btn {
                width: 36px;
                height: 36px;
            }
            
            .thumbnails {
                display: none; /* Hide thumbnails in landscape on small screens */
            }
        }
        
        /* Prefers Reduced Motion */
        @media (prefers-reduced-motion) {
            .slider,
            .slide img,
            .nav-btn,
            .thumbnail,
            .dot {
                transition: none;
            }
        }
        
        /* Touch-specific styles */
        @media (hover: none) {
            .nav-btn {
                opacity: 0.8; /* Always slightly visible on touch devices */
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            h1 {
                color: #f0f0f0;
            }
            
            .slider-container {
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            }
            
            .dot {
                background-color: #555;
            }
            
            .dot.active {
                background-color: #64b5f6;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: #199fd4;
background: linear-gradient(90deg, rgba(25, 159, 212, 1) 0%, rgba(87, 122, 199, 1) 59%, rgba(67, 136, 171, 1) 100%);">

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

    <!-- Fasilitas Hotel -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Fasilitas Hotel</h2>
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h5>Wi-Fi Gratis</h5>
                <p>Internet gratis di wilayah hotel</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-swimming-pool"></i>
                </div>
                <h5>Kolam Renang</h5>
                <p>Kolam renang outdoor </p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h5>Restoran</h5>
                <p>Restoran dengan beragam menu </p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-icon">
                    <i class="fas fa-spa"></i>
                </div>
                <h5>Spa & Fitness</h5>
                <p>Untuk olahraga</p>
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

    <!-- Galeri Hotel -->
    <div class="container mt-5">
    <div class="container-gambar">
    <h2 class="text-center mb-4">Galeri Hotel</h2>
        
        <div class="slider-container">
            <div class="slider">
                <div class="slide">
                    <img src="../../Images/image1.jpg" alt="Pemandangan Alam">
                    <div class="caption">
                        <h3>Pemandangan</h3>
                        <p>Pemandangan hotel</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="../../Images/image2.jpg" alt="Pantai">
                    <div class="caption">
                        <h3>Ruang makan</h3>
                        <p>Ruang makan</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="../../Images/image3.jpg" alt="Kota Modern">
                    <div class="caption">
                        <h3>Ruang Tunggu</h3>
                        <p>Ruang tunggu</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="../../Images/Family.jpeg" alt="Arsitektur">
                    <div class="caption">
                        <h3>Kamar Mandi</h3>
                        <p>Kamar mandi dengan design yang mewah dan elegan.</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="../../Images/HotelFamily.jpeg" alt="Air Terjun">
                    <div class="caption">
                        <h3>Kamar Hotel</h3>
                        <p>Kamar dengan berbagai fasilitas</p>
                    </div>
                </div>
            </div>
            
            <button class="nav-btn prev-btn" aria-label="Slide sebelumnya">❮</button>
            <button class="nav-btn next-btn" aria-label="Slide selanjutnya">❯</button>
            
            <div class="progress-bar"></div>
        </div>
        
        <div class="controls-container">
            <div class="dots-container">
                <span class="dot active" aria-label="Slide 1"></span>
                <span class="dot" aria-label="Slide 2"></span>
                <span class="dot" aria-label="Slide 3"></span>
                <span class="dot" aria-label="Slide 4"></span>
                <span class="dot" aria-label="Slide 5"></span>
            </div>
            
            <div class="thumbnails">
                <div class="thumbnail active">
                    <img src="../../Images/image1.jpg" alt="Gambar 1">
                </div>
                <div class="thumbnail">
                    <img src="../../Images/image2.jpg" alt="Gambar 2">
                </div>
                <div class="thumbnail">
                    <img src="../../Images/image3.jpg" alt="Gambar 3">
                </div>
                <div class="thumbnail">
                    <img src="../../Images/Family.jpeg" alt="Gambar 4">
                </div>
                <div class="thumbnail">
                    <img src="../../Images/HotelFamily.jpeg" alt="Gambar 5">
                </div>
            </div>
        </div>
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

                <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.querySelector('.slider');
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.dot');
            const thumbnails = document.querySelectorAll('.thumbnail');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            const progressBar = document.querySelector('.progress-bar');
            
            let currentIndex = 0;
            const totalSlides = slides.length;
            let autoSlideInterval;
            let progressInterval;
            const slideInterval = 5000; 
            
            // fungsi untuk menampilkan slide
            function showSlide(index) {
                if (index < 0) {
                    currentIndex = totalSlides - 1;
                } else if (index >= totalSlides) {
                    currentIndex = 0;
                } else {
                    currentIndex = index;
                }
                
                // annimasi transisi slide
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
                
                
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === currentIndex);
                });
                
                // update tampilan /thumbail
                thumbnails.forEach((thumb, i) => {
                    thumb.classList.toggle('active', i === currentIndex);
                });
                
                // ngereset setiap udah selesai
                resetProgressBar();
            }
            
        
            function resetProgressBar() {
                clearInterval(progressInterval);
                progressBar.style.width = '0%';
                
                progressInterval = setInterval(() => {
                    let currentWidth = parseFloat(progressBar.style.width) || 0;
                    if (currentWidth < 100) {
                        currentWidth += 0.1;
                        progressBar.style.width = currentWidth + '%';
                    }
                }, slideInterval / 1000);
            }
            
    
            function startAutoSlide() {
                resetProgressBar();
                clearInterval(autoSlideInterval);
                autoSlideInterval = setInterval(() => {
                    showSlide(currentIndex + 1);
                }, slideInterval);
            }
            
            prevBtn.addEventListener('click', () => {
                showSlide(currentIndex - 1);
                startAutoSlide();
            });
            
            nextBtn.addEventListener('click', () => {
                showSlide(currentIndex + 1);
                startAutoSlide();
            });
            
    
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    showSlide(index);
                    startAutoSlide();
                });
            });
            
            // Event handler untuk thumbnails
            thumbnails.forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', () => {
                    showSlide(index);
                    startAutoSlide();
                });
            });
            
            // Tambahkan touch swipe support
            let touchStartX = 0;
            let touchEndX = 0;
            
            slider.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            slider.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, false);
            
            function handleSwipe() {
                if (touchEndX < touchStartX - 50) {
                    // Swipe kiri
                    showSlide(currentIndex + 1);
                    startAutoSlide();
                } else if (touchEndX > touchStartX + 50) {
                    // Swipe kanan
                    showSlide(currentIndex - 1);
                    startAutoSlide();
                }
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    showSlide(currentIndex - 1);
                    startAutoSlide();
                } else if (e.key === 'ArrowRight') {
                    showSlide(currentIndex + 1);
                    startAutoSlide();
                }
            });
            
            // Pause autoplay saat mouse hover
            const sliderContainer = document.querySelector('.slider-container');
            sliderContainer.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
            });
            
            sliderContainer.addEventListener('mouseleave', () => {
                startAutoSlide();
            });
            
            // Mulai slider
            startAutoSlide();
        });
    </script>

            </body>
            </html>
            