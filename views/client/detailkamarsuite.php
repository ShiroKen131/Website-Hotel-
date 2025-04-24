<?php
// Start session to manage user login state
session_start();

include '../../koneksi.php';

// Mendapatkan kamar 301 dan 302
$sql = "SELECT r.room_id, r.room_number, rt.name as room_type, rt.description, 
        rt.max_capacity, rt.price_per_night, rt.image_url
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE r.room_number IN ('301', '302')";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Kamar tidak ditemukan";
    exit;
}


$booking_message = "";
$booking_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_booking'])) {
//cek apakah user udah login
    if (!isset($_SESSION['user_id'])) {
        $booking_status = "error";
        $booking_message = "Silahkan login terlebih dahulu untuk melakukan pemesanan.";
    } else {
        $user_id = $_SESSION['user_id'];
        $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $special_requests = $_POST['special_requests'];
        
        // mengkonfirmasi ruangan
        $valid_room = false;
        foreach ($rooms as $r) {
            if ($r['room_id'] == $room_id) {
                $valid_room = true;
                break;
            }
        }
        
        if (!$valid_room) {
            $booking_status = "error";
            $booking_message = "Kamar tidak valid.";
        } else {
            // Mengkonfirmasi tanggal check in dan check out 
            $today = date('Y-m-d');
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $today_date = new DateTime($today);
            
            if ($check_in_date < $today_date) {
                $booking_status = "error";
                $booking_message = "Tanggal check-in tidak boleh sebelum hari ini.";
            } else if ($check_in_date >= $check_out_date) {
                $booking_status = "error";
                $booking_message = "Tanggal check-out harus setelah tanggal check-in.";
            } else {
                // Ngecek apakah ada ruanfan dengan tanggal ....
                $availability_sql = "SELECT COUNT(*) as booked FROM bookings 
                WHERE room_id = ? 
                AND status IN ('confirmed', 'pending')
                AND (
                    (check_in_date < ? AND check_out_date > ?) OR
                    (check_in_date >= ? AND check_in_date < ?) OR
                    (check_in_date <= ? AND check_out_date >= ?)
                )";
                
                $stmt = $conn->prepare($availability_sql);
                $stmt->bind_param("issssss", $room_id, $check_out, $check_in, $check_in, $check_out, $check_in, $check_out);
                $stmt->execute();
                $availability_result = $stmt->get_result();
                $availability = $availability_result->fetch_assoc();
                
                if ($availability['booked'] > 0) {
                    $booking_status = "error";
                    $booking_message = "Kamar tidak tersedia pada tanggal yang dipilih.";
                } else {
                    
                    $proc_sql = "CALL book_room(?, ?, ?, ?, ?, @booking_id)";
                    $stmt = $conn->prepare($proc_sql);
                    $stmt->bind_param("iisss", $user_id, $room_id, $check_in, $check_out, $special_requests);
                    $stmt->execute();
                    
           
                    $result = $conn->query("SELECT @booking_id as booking_id");
                    $row = $result->fetch_assoc();
                    $booking_id = $row['booking_id'];
                    
                    if ($booking_id) {
                        $booking_status = "success";
                        $booking_message = "Pemesanan berhasil dibuat!";
                        
                       
                        header("Location: ../../pembayaran.php?booking_id=" . $booking_id);
                        exit;
                    } else {
                        $booking_status = "error";
                        $booking_message = "Terjadi kesalahan saat membuat pemesanan.";
                    }
                }
            }
        }
    }
}


$user = null;
if (isset($_SESSION['user_id'])) {
    $user_sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suite Room - Hotel Shiro</title>
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
            max-width: 1200px;
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
        
        .room-details {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .room-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-gap: 10px;
            margin-bottom: 20px;
        }
        
        .main-image {
            grid-row: span 2;
            height: 400px;
        }
        
        .small-image {
            height: 195px;
        }
        
        .room-gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .room-info {
            padding: 30px;
        }
        
        .room-title {
            font-size: 2rem;
            color: #1DA1F2;
            margin-bottom: 10px;
        }
        
        .room-price {
            font-size: 1.5rem;
            font-weight: bold;
            color:  #1DA1F2;
            margin-bottom: 20px;
        }
        
        .room-description {
            margin-bottom: 30px;
            font-size: 1.1rem;
            color: #555;
        }
        
        .facilities {
            margin-bottom: 30px;
        }
        
        .facilities h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color:  #1DA1F2;
        }
        
        .facilities-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .facility-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 1.2rem;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .cta-button:hover {
            background-color:  #1DA1F2;
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
            .room-gallery {
                grid-template-columns: 1fr;
            }
            
            .main-image, .small-image {
                height: 250px;
            }
            
            .facilities-list {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        /* Booking form styles */
        .booking-form {
            margin-top: 2rem;
            padding: 2rem;
            background: #f8f8f8;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .booking-form h3 {
            color: #1a3c40;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #444;
        }
        
        .form-group input, 
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s ease;
        }
        
        .form-group input:focus, 
        .form-group textarea:focus {
            border-color: #1a3c40;
            outline: none;
        }
        
        .date-inputs {
            display: flex;
            gap: 15px;
        }
        
        .date-inputs .form-group {
            flex: 1;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .login-prompt {
            margin-bottom: 20px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        
        .login-prompt p {
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .login-prompt .cta-button {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Hotel Shiro</h1>
        <p class="subtitle">Kemewahan dan Kenyamanan dalam Satu Tempat</p>
    </header>
    
    <div class="container">
    <a href="../client/clientside.php" class="back-link">← Kembali ke Daftar Kamar</a>
    
    <?php foreach ($rooms as $room): ?>
    <div class="room-details">
        <div class="room-gallery">
            <div class="main-image">
            <img src="../../Images/HotelSuite.jpeg">
            </div>
            <div class="small-image">
            <img src="../../Images/Suite.jpeg">
            </div>
        </div>
        
        <div class="room-info">
            <h2 class="room-title"><?php echo htmlspecialchars($room['room_type']); ?> - Kamar <?php echo htmlspecialchars($room['room_number']); ?></h2>
            <p class="room-price">Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?>/malam</p>
            
            <div class="room-description">
                <p><?php echo nl2br(htmlspecialchars($room['description'] ?: 'Kamar nyaman dengan luas 24m² yang dirancang untuk memberikan kenyamanan optimal. Dilengkapi dengan tempat tidur queen size berkualitas tinggi dan interior modern yang elegan. Kamar ini ideal untuk perjalanan bisnis atau liburan singkat di kota.')); ?></p>
                <p>Nikmati fasilitas standar hotel bintang 4 dengan sentuhan kemewahan yang akan membuat pengalaman menginap Anda berkesan.</p>
            </div>
            
            <div class="facilities">
                <h3>Fasilitas Kamar</h3>
                <div class="facilities-list">
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Tempat tidur Queen (160x200cm)</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>TV LED 42 inch</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>AC</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>WiFi Gratis</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Kamar mandi dengan shower</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Toiletries lengkap</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Meja kerja</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Minibar</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Sarapan untuk 2 orang</span>
                    </div>
                    <div class="facility-item">
                        <span>✓</span>
                        <span>Akses fitness center</span>
                    </div>
                </div>
            </div>

            <?php if ($booking_message): ?>
            <div class="alert alert-<?php echo $booking_status; ?>">
                <?php echo htmlspecialchars($booking_message); ?>
            </div>
            <?php endif; ?>
            
            <div class="booking-form">
                <h3>Pesan Kamar <?php echo htmlspecialchars($room['room_number']); ?></h3>
                <form method="post" action="">
                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                    <div class="date-inputs">
                        <div class="form-group">
                            <label for="check_in_<?php echo $room['room_id']; ?>">Tanggal Check-in:</label>
                            <input type="date" id="check_in_<?php echo $room['room_id']; ?>" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="check_out_<?php echo $room['room_id']; ?>">Tanggal Check-out:</label>
                            <input type="date" id="check_out_<?php echo $room['room_id']; ?>" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests_<?php echo $room['room_id']; ?>">Permintaan Khusus:</label>
                        <textarea id="special_requests_<?php echo $room['room_id']; ?>" name="special_requests" rows="4" placeholder="Contoh: Kamar bebas rokok, lantai tinggi, dekat lift, dll."></textarea>
                    </div>
                    
                    <button type="submit" name="submit_booking" class="cta-button">Pesan Sekarang</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
    
    <footer>
        <p>&copy; 2025 Hotel Shiro. Semua hak dilindungi.</p>
    </footer>

    <script>
        
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($rooms as $room): ?>
        const checkInInput_<?php echo $room['room_id']; ?> = document.getElementById('check_in_<?php echo $room['room_id']; ?>');
        const checkOutInput_<?php echo $room['room_id']; ?> = document.getElementById('check_out_<?php echo $room['room_id']; ?>');
        
        if (checkInInput_<?php echo $room['room_id']; ?> && checkOutInput_<?php echo $room['room_id']; ?>) {
            checkInInput_<?php echo $room['room_id']; ?>.addEventListener('change', function() {

                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(checkInDate.getDate() + 1);
                const nextDayStr = nextDay.toISOString().split('T')[0];
                checkOutInput_<?php echo $room['room_id']; ?>.min = nextDayStr;
                
         
                if (checkOutInput_<?php echo $room['room_id']; ?>.value && new Date(checkOutInput_<?php echo $room['room_id']; ?>.value) <= checkInDate) {
                    checkOutInput_<?php echo $room['room_id']; ?>.value = nextDayStr;
                }
            });
        }
        <?php endforeach; ?>
    });
</script>
</body>
</html>