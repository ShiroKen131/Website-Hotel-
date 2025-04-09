<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Shiro Indonesia - Pilihan Kamar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            background-color:#007BFF;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.2rem;
            font-weight: 300;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .room-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 320px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .card-image {
            height: 200px;
            overflow: hidden;
        }
        
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .card:hover .card-image img {
            transform: scale(1.05);
        }
        
        .card-content {
            padding: 20px;
        }
        
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #1a3c40;
        }
        
        .card-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .card-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1a3c40;
            margin-bottom: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #1DA1F2;
        }
        
        .view-all {
            text-align: center;
            margin: 40px 0 20px;
        }
        
        .view-all-btn {
            padding: 12px 30px;
            font-size: 1.1rem;
            background-color: transparent;
            color: #007BFF;
            border: 2px solid #007BFF;
        }
        
        .view-all-btn:hover {
            background-color: #007BFF;
            color: white;
        }
        
        footer {
            background-color: #007BFF;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .room-cards {
                flex-direction: column;
                align-items: center;
            }
            
            .card {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hotel Shiro</h1>
        <p class="subtitle">Kemewahan dan Kenyamanan dalam Satu Tempat</p>
    </header>
    
    <div class="container">
        <div class="room-cards">
            <div class="card">
                <div class="card-image">
                <img src="../../Images/HotelStandar.jpeg" alt="Standard">
                </div>
                <div class="card-content">
                    <h2 class="card-title">Kamar Standar</h2>
                    <p class="card-description">Kamar nyaman dengan tempat tidur queen size, ideal untuk perjalanan bisnis atau liburan singkat.</p>
                    <p class="card-price">Rp 500.000/malam</p>
                    <a href="../client/detailkamarstandar.php" class="btn">Lihat Detail</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-image">
                    <img src="../../Images/HotelDeluxe.jpeg" alt="Deluxe">
                </div>
                <div class="card-content">
                    <h2 class="card-title">Deluxe Room</h2>
                    <p class="card-description">Kamar luas dengan tempat tidur king size, pemandangan kota, dan fasilitas premium.</p>
                    <p class="card-price">Rp 750.000/malam</p>
                    <a href="../client/detailkamardeluxe.php" class="btn">Lihat Detail</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-image">
                    <img src="../../Images/HotelSuite.jpeg" alt="Suite Room">
                </div>
                <div class="card-content">
                    <h2 class="card-title">Suite Room</h2>
                    <p class="card-description">Kamar mewah dengan ruang tamu terpisah, jacuzzi pribadi, dan layanan butler.</p>
                    <p class="card-price">Rp 1.500.000/malam</p>
                    <a href="../client/detailkamarsuite.php" class="btn">Lihat Detail</a>
                </div>
            </div>
        
            <div class="card">
                <div class="card-image">
                    <img src="../../Images/HotelFamily.jpeg" alt="Family Room">
                </div>
                <div class="card-content">
                    <h2 class="card-title">Family</h2>
                    <p class="card-description">Kamar keluarga dengan dua tempat tidur queen size.</p>
                    <p class="card-price">Rp 1.200.000/malam</p>
                    <a href="../client/detailkamarfamily.php" class="btn">Lihat Detail</a>
                </div>
            </div>
        </div>
        
        <div class="view-all">
            <a href="#" class="btn view-all-btn">Lihat Semua Kamar</a>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Hotel Shiro. Semua hak dilindungi.</p>
    </footer>
</body>
</html> 