<?php

session_start();

include 'koneksi.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=payment.php?" . $_SERVER['QUERY_STRING']);
    exit;
}


$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// mendapatkan informasi tentang booking berdasarkan username ruangan dan nomor ruangannya
$sql = "SELECT b.*, r.room_number, rt.name as room_type, rt.price_per_night, username as user_name
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

$payment_message = "";
$payment_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_payment'])) {
    $payment_method = $_POST['payment_method'];
    $payment_proof = "";
    
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $target_dir = "uploads/bukti_pembayaran/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION);
        $new_file_name = "payment_" . $booking_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
            $payment_proof = $target_file;
        } else {
            $payment_status = "error";
            $payment_message = "Gagal mengunggah bukti pembayaran.";
        }
    }
    
    if (empty($payment_status)) {
        $payment_method_mapping = [
            'bank_transfer' => 'transfer',
            'credit_card' => 'credit_card',
            'e_wallet' => 'transfer'  
        ];
        

        $db_payment_method = isset($payment_method_mapping[$payment_method]) ? 
            $payment_method_mapping[$payment_method] : 'transfer'; 
        

        $conn->begin_transaction();
        
        try {
     
            $update_booking_sql = "UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?";
            $stmt = $conn->prepare($update_booking_sql);
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            
           
            $insert_payment_sql = "INSERT INTO payments (booking_id, amount, payment_method, status, transaction_id) 
                                  VALUES (?, ?, ?, 'completed', ?)";
            $stmt = $conn->prepare($insert_payment_sql);
            $transaction_id = $payment_proof ? basename($payment_proof) : "MANUAL_" . time();
            $stmt->bind_param("idss", $booking_id, $booking['total_price'], $db_payment_method, $transaction_id);
            $stmt->execute();
            

            $conn->commit();
            
            $payment_status = "success";
            $payment_message = "Pembayaran berhasil! Pemesanan Anda telah dikonfirmasi.";
            

            header("refresh:3;url=booking_confirmation.php?booking_id=" . $booking_id);
        } catch (Exception $e) {
    
            $conn->rollback();
            $payment_status = "error";
            $payment_message = "Terjadi kesalahan saat memproses pembayaran: " . $e->getMessage();
        }
    }
}


$check_in_date = new DateTime($booking['check_in_date']);
$check_out_date = new DateTime($booking['check_out_date']);
$nights = date_diff($check_in_date, $check_out_date)->days;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Hotel Shiro</title>
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
            background: #199fd4;
background: linear-gradient(90deg, rgba(25, 159, 212, 1) 0%, rgba(87, 122, 199, 1) 59%, rgba(67, 136, 171, 1) 100%);
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

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
        
        .payment-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .booking-summary {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-summary h2 {
            color: #0d6efd;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .summary-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .summary-item {
            margin-bottom: 10px;
        }
        
        .summary-item strong {
            display: block;
            color: #555;
            font-size: 0.9rem;
        }
        
        .summary-item span {
            display: block;
            font-size: 1.1rem;
            margin-top: 5px;
        }
        
        .price-summary {
            margin-top: 20px;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 8px;
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
        
        .payment-options h2 {
            color: #0d6efd;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .payment-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            background-color: white;
        }
        
        .payment-methods {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;

        }
        
        .payment-method {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #0d6efd;
        }
        
        .payment-method.active {
            border-color: #0d6efd;
            background-color: rgba(26, 60, 64, 0.05);
        }
        
        .method-name {
            margin-top: 10px;
            font-weight: 500;
        }
        
        .bank-details {
            margin: 20px 0;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }
        
        .bank-details h3 {
            color: #0d6efd;
            margin-bottom: 15px;
        }
        
        .bank-details p {
            margin-bottom: 10px;
        }
        
        .bank-details strong {
            font-weight: 600;
        }
        
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        
        .cta-button:hover {
            background-color: #1DA1F2;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #1DA1F2;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        footer {
            background: #199fd4;
background: linear-gradient(90deg, rgba(25, 159, 212, 1) 0%, rgba(87, 122, 199, 1) 59%, rgba(67, 136, 171, 1) 100%);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .summary-details {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<header>
        <h1>Hotel Shiro</h1>
        <p class="subtitle">Pembayaran Reservasi</p>
    </header>
    
    <div class="container">
        <?php if ($payment_message): ?>
        <div class="alert alert-<?php echo $payment_status; ?>">
            <?php echo htmlspecialchars($payment_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="payment-card">
            <div class="booking-summary">
                <h2>Ringkasan Pemesanan</h2>
                <div class="summary-details">
                    <div class="summary-item">
                        <strong>ID Pemesanan</strong>
                        <span><?php echo $booking_id; ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Nama Pemesan</strong>
                        <span><?php echo htmlspecialchars($booking['user_name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Tipe Kamar</strong>
                        <span><?php echo htmlspecialchars($booking['room_type']); ?> (No. <?php echo htmlspecialchars($booking['room_number']); ?>)</span>
                    </div>
                    <div class="summary-item">
                        <strong>Tanggal Check-in</strong>
                        <span><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Tanggal Check-out</strong>
                        <span><?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Durasi Menginap</strong>
                        <span><?php echo $nights; ?> malam</span>
                    </div>
                </div>
                
                <div class="price-summary">
                    <div class="price-row">
                        <div>Harga per malam</div>
                        <div>Rp <?php echo number_format($booking['price_per_night'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="price-row">
                        <div>Jumlah malam</div>
                        <div><?php echo $nights; ?> malam</div>
                    </div>
    
                    <div class="price-total">
                        <div>Total Pembayaran</div>
                        <div>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="payment-options">
                <h2>Pilih Metode Pembayaran</h2>
                
                <form method="post" action="" enctype="multipart/form-data" class="payment-form">
                    <div class="payment-methods">
                        <div class="payment-method" onclick="selectPaymentMethod('bank_transfer')">
                
                            <div class="method-name">Transfer Bank</div>
                        </div>
                    </div>
                    
                    <input type="hidden" id="payment_method" name="payment_method" value="bank_transfer">
                    
                    <div class="bank-details" id="bank_transfer_details">
                        <h3>Informasi Transfer Bank</h3>
                        <p><strong>Bank:</strong> Bank Shiro</p>
                        <p><strong>No. Rekening:</strong> 1234567890</p>
                        <p><strong>Atas Nama:</strong> PT Hotel Shiro</p>
                        <p>Harap transfer sesuai jumlah yang tertera dan unggah bukti pembayaran Anda.</p>
                    </div>
                    
                    <div class="bank-details" id="credit_card_details" style="display: none;">
                        <h3>Informasi Kartu Kredit</h3>
                        <p>Untuk pembayaran dengan kartu kredit, Anda akan diarahkan ke halaman pembayaran yang aman.</p>
                        <p>Kami menerima Visa, Mastercard, dan JCB.</p>
                    </div>
                    
                    <div class="bank-details" id="e_wallet_details" style="display: none;">
                        <h3>Informasi E-Wallet</h3>
                        <p><strong>OVO/GoPay/DANA/LinkAja</strong></p>
                        <p><strong>No.:</strong> 087712345678</p>
                        <p><strong>Atas Nama:</strong> Hotel Shiro</p>
                        <p>Harap transfer sesuai jumlah yang tertera dan unggah bukti pembayaran Anda.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_proof">Unggah Bukti Pembayaran:</label>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required>
                    </div>
                    
                    <button type="submit" name="submit_payment" class="cta-button">Konfirmasi Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; ShiroKen</p>
    </footer>

    <script>
        function selectPaymentMethod(method) {

            document.getElementById('payment_method').value = method;
            

            document.querySelectorAll('.payment-method').forEach(function(element) {
                element.classList.remove('active');
            });
            
            event.currentTarget.classList.add('active');
            

            document.getElementById('bank_transfer_details').style.display = 'none';
            document.getElementById('credit_card_details').style.display = 'none';
            document.getElementById('e_wallet_details').style.display = 'none';
            
            document.getElementById(method + '_details').style.display = 'block';
        }
        

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.payment-method').classList.add('active');
        });
    </script>
</body>
</html>