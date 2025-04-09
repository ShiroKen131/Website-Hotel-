<?php

session_start();

include 'koneksi.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=booking_confirmation.php?" . $_SERVER['QUERY_STRING']);
    exit;
}

//mendapatkan booking id dari url didapat dari pemesanan ketika sudah dipencet 
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

$sql = "SELECT b.*, r.room_number, rt.name as room_type, rt.image_url, 
        rt.price_per_night, username as user_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Pemesanan tidak ditemukan atau Anda tidak memiliki akses.";
    exit;
}

$booking = $result->fetch_assoc();

// menghitung jumlah dia menginap berapa hari 
$check_in_date = new DateTime($booking['check_in_date']);
$check_out_date = new DateTime($booking['check_out_date']);
$nights = date_diff($check_in_date, $check_out_date)->days;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan - Hotel Shiro</title>
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
            background-color: #1a6efd;
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
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .confirmation-header {
            background-color: #0d6efd;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .confirmation-header h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .confirmation-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .confirmation-body {
            padding: 30px;
        }
        
        .booking-info {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .room-image {
            border-radius: 8px;
            overflow: hidden;
            height: 200px;
        }
        
        
        .booking-details h3 {
            color: #0d6efd;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-item strong {
            display: block;
            color: #555;
            font-size: 0.9rem;
        }
        
        .detail-item span {
            display: block;
            font-size: 1.1rem;
            margin-top: 5px;
        }
        
        .price-details {
            margin-top: 30px;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 8px;
        }
        
        .price-details h3 {
            color: #0d6efd;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .price-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .important-info {
            margin-top: 30px;
            padding: 20px;
            background: #e9f5f5;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }
        
        .important-info h3 {
            color: #0d6efd;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .important-info ul {
            padding-left: 20px;
        }
        
        .important-info li {
            margin-bottom: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-button {
            flex: 1;
            display: inline-block;
            padding: 15px 20px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        
        .action-button.secondary {
            background-color: #fff;
            color: #1DA1F2;
            border: 1px solid #0d6efd;
        }
        
        .action-button:hover {
            background-color: #1DA1F2;
        }
        
        .action-button.secondary:hover {
            background-color: #f5f5f5;
        }
        
        footer {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .booking-info {
                grid-template-columns: 1fr;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        .booking-id {
            font-family: monospace;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-badge.paid {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-badge.confirmed {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .qr-code {
            text-align: center;
            margin-top: 30px;
        }
        
        
        .qr-code p {
            font-size: 0.9rem;
            color: #555;
        }
    </style>
</head>
<body>
    <header>
        <h1>Hotel Shiro</h1>
        <p class="subtitle">Konfirmasi Pemesanan</p>
    </header>
    
    <div class="container">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <h2>Pemesanan Berhasil!</h2>
                <p>Terima kasih telah memilih Hotel Shiro</p>
            </div>
            
            <div class="confirmation-body">
                <div class="booking-info">
                    
                    <div class="booking-details">
                        <h3>Detail Pemesanan</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <strong>ID Pemesanan</strong>
                                <span class="booking-id"><?php echo $booking_id; ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Status</strong>
                                <span>
                                    <?php if ($booking['status'] == 'paid'): ?>
                                    <span class="status-badge paid">Dibayar</span>
                                    <?php elseif ($booking['status'] == 'confirmed'): ?>
                                    <span class="status-badge confirmed">Dikonfirmasi</span>
                                    <?php else: ?>
                                    <span class="status-badge pending">Menunggu</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <strong>Tipe Kamar</strong>
                                <span><?php echo htmlspecialchars($booking['room_type']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Nomor Kamar</strong>
                                <span><?php echo htmlspecialchars($booking['room_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Check-in</strong>
                                <span><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Check-out</strong>
                                <span><?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Nama Tamu</strong>
                                <span><?php echo htmlspecialchars($booking['user_name']); ?></span>
                            </div>
                            <?php if (!empty($booking['special_requests'])): ?>
                            <div class="detail-item" style="grid-column: span 2;">
                                <strong>Permintaan Khusus</strong>
                                <span><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="price-details">
                    <h3>Rincian Harga</h3>
                    <div class="price-row">
                        <div>Harga kamar (<?php echo $nights; ?> malam)</div>
                        <div>Rp <?php echo number_format(($booking['price_per_night'] ?? 0) * $nights, 0, ',', '.'); ?></div>
                    </div>
                    <div class="price-row">
                    </div>
                    <div class="price-total">
                        <div>Total</div>
                        <div>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></div>
                    </div>
                </div>
                

                
                <div class="action-buttons">
                    <a href="javascript:window.print();" class="action-button secondary">Cetak Konfirmasi</a>
                    <a href="views/client/clientside.php" class="action-button">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Hotel Shiro. Semua hak dilindungi.</p>
    </footer>
</body>
</html>