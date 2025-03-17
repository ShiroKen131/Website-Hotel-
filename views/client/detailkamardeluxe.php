<?php
// Start session to manage user login state
session_start();

include '../../koneksi.php';

// Get room details for rooms 201 and 202
$sql = "SELECT r.room_id, r.room_number, rt.name as room_type, rt.description, 
        rt.max_capacity, rt.price_per_night, rt.image_url
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE r.room_number IN ('201', '202')";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Kamar tidak ditemukan";
    exit;
}

// Process booking form
$booking_message = "";
$booking_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_booking'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $booking_status = "error";
        $booking_message = "Silahkan login terlebih dahulu untuk melakukan pemesanan.";
    } else {
        $user_id = $_SESSION['user_id'];
        $selected_room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $special_requests = $_POST['special_requests'];
        
        // Get both room IDs for 201 and 202
        $room_201_id = null;
        $room_202_id = null;
        foreach ($rooms as $r) {
            if ($r['room_number'] == '201') {
                $room_201_id = $r['room_id'];
            } elseif ($r['room_number'] == '202') {
                $room_202_id = $r['room_id'];
            }
        }
        
        // Validate dates
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
            // Function to check room availability
            function checkRoomAvailability($conn, $room_id, $check_in, $check_out) {
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
                
                return $availability['booked'] == 0; // Return true if room is available
            }
            
            // Check availability for room 201 first (regardless of selected_room_id)
            $room201_available = checkRoomAvailability($conn, $room_201_id, $check_in, $check_out);
            
            // If room 201 is available, book it
            if ($room201_available) {
                $room_id_to_book = $room_201_id;
                $room_number_booked = "201";
            } else {
                // If room 201 is not available, check room 202
                $room202_available = checkRoomAvailability($conn, $room_202_id, $check_in, $check_out);
                
                if ($room202_available) {
                    $room_id_to_book = $room_202_id;
                    $room_number_booked = "202";
                } else {
                    // Neither room is available
                    $booking_status = "error";
                    $booking_message = "Kamar 201 dan 202 tidak tersedia pada tanggal yang dipilih.";
                    $room_id_to_book = null;
                }
            }
            
            // If we have a room to book
            if ($room_id_to_book) {
                // Call the stored procedure to book the room
                $proc_sql = "CALL book_room(?, ?, ?, ?, ?, @booking_id)";
                $stmt = $conn->prepare($proc_sql);
                $stmt->bind_param("iisss", $user_id, $room_id_to_book, $check_in, $check_out, $special_requests);
                $stmt->execute();
                
                // Get the output parameter
                $result = $conn->query("SELECT @booking_id as booking_id");
                $row = $result->fetch_assoc();
                $booking_id = $row['booking_id'];
                
                if ($booking_id) {
                    $booking_status = "success";
                    $booking_message = "Pemesanan berhasil dibuat untuk kamar " . $room_number_booked . "!";
                    
                    // Redirect ke pembayaran.php di direktori utama (satu level ke atas)
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

// Get current user data if logged in
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
    <title>Deluxe Room - Hotel Pesona Indonesia</title>
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
            background-color: #1a3c40;
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
            color: #1a3c40;
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
            margin-bottom: 30px;
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
            color: #1a3c40;
            margin-bottom: 10px;
        }
        
        .room-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a3c40;
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
            color: #1a3c40;
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
            background-color: #1a3c40;
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
            background-color: #2a5559;
        }
        
        footer {
            background-color: #1a3c40;
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
        
        .room-availability {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: 500;
            text-align: center;
        }
        
        .available {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .unavailable {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <h1>Hotel Pesona Indonesia</h1>
        <p class="subtitle">Kemewahan dan Kenyamanan dalam Satu Tempat</p>
    </header>
    
    <div class="container">
        <a href="../client/clientside.php" class="back-link">← Kembali ke Daftar Kamar</a>
        
        <?php if ($booking_message): ?>
        <div class="alert alert-<?php echo $booking_status; ?>">
            <?php echo htmlspecialchars($booking_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="room-description" style="margin-bottom: 20px;">
        <div class="room-description" style="margin-bottom: 20px;">
            <p>Kami menawarkan kamar Deluxe dengan nomor kamar 201 & 202. Sistem kami akan otomatis memilih kamar 201 jika tersedia. Jika kamar 201 telah terisi, sistem akan secara otomatis memesan kamar 202 untuk Anda.</p>
        </div>
        
        <?php
        // Display each room
        foreach ($rooms as $room): 
            // Check room availability for display purposes
            $availability_sql = "SELECT COUNT(*) as booked FROM bookings 
                WHERE room_id = ? 
                AND status IN ('confirmed', 'pending')
                AND (
                    (check_in_date < CURDATE() + INTERVAL 1 DAY AND check_out_date > CURDATE()) OR
                    (check_in_date >= CURDATE() AND check_in_date < CURDATE() + INTERVAL 7 DAY)
                )";
            
            $stmt = $conn->prepare($availability_sql);
            $stmt->bind_param("i", $room['room_id']);
            $stmt->execute();
            $availability_result = $stmt->get_result();
            $availability = $availability_result->fetch_assoc();
            $is_available = $availability['booked'] == 0;
        ?>
        <div class="room-details">
            <div class="room-gallery">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($room['image_url'] ?: '/api/placeholder/800/400'); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>">
                </div>
                <div class="small-image">
                    <img src="/api/placeholder/400/200" alt="<?php echo htmlspecialchars($room['room_type']); ?> Bathroom">
                </div>
                <div class="small-image">
                    <img src="/api/placeholder/400/200" alt="<?php echo htmlspecialchars($room['room_type']); ?> View">
                </div>
            </div>
            
            <div class="room-info">
                <h2 class="room-title"><?php echo htmlspecialchars($room['room_type']); ?> - Kamar <?php echo htmlspecialchars($room['room_number']); ?></h2>
                <p class="room-price">Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?>/malam</p>
                
                <div class="room-availability <?php echo $is_available ? 'available' : 'unavailable'; ?>">
                    <?php echo $is_available ? 'Tersedia' : 'Tidak Tersedia (untuk 7 hari ke depan)'; ?>
                </div>
                
                <div class="room-description">
                    <p><?php echo nl2br(htmlspecialchars($room['description'] ?: 'Kamar Deluxe dengan luas 32m² yang dirancang dengan gaya modern dan elegan. Nikmati istirahat nyaman dengan tempat tidur king size premium dan pemandangan kota yang menakjubkan. Kamar ini ideal untuk perjalanan bisnis atau liburan romantis.')); ?></p>
                </div>
                
                <div class="facilities">
                    <h3>Fasilitas Kamar</h3>
                    <div class="facilities-list">
                        <div class="facility-item">
                            <span>✓</span>
                            <span>Tempat tidur King (180x200cm)</span>
                        </div>
                        <div class="facility-item">
                            <span>✓</span>
                            <span>TV LED 48 inch</span>
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
                            <span>Kamar mandi dengan bathtub</span>
                        </div>
                        <div class="facility-item">
                            <span>✓</span>
                            <span>Toiletries premium</span>
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
                            <span>Akses fitness center & kolam renang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Single booking form for both rooms -->
        <div class="booking-form">
            <h3>Pesan Kamar Deluxe</h3>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="login-prompt">
                <p>Silahkan login terlebih dahulu untuk melakukan pemesanan.</p>
                <a href="../../login.php" class="cta-button">Login</a>
                <p>Belum memiliki akun? <a href="../../register.php">Daftar disini</a></p>
            </div>
            <?php else: ?>
            <form method="post" action="">
                <input type="hidden" name="room_id" value="<?php echo $rooms[0]['room_id']; ?>">
                <div class="date-inputs">
                    <div class="form-group">
                        <label for="check_in">Tanggal Check-in:</label>
                        <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="check_out">Tanggal Check-out:</label>
                        <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="special_requests">Permintaan Khusus:</label>
                    <textarea id="special_requests" name="special_requests" rows="4" placeholder="Contoh: Kamar bebas rokok, lantai tinggi, dekat lift, dll."></textarea>
                </div>
                
                <button type="submit" name="submit_booking" class="cta-button">Pesan Sekarang</button>
                <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
                    Sistem akan otomatis memilih kamar 201 jika tersedia. Jika tidak tersedia, sistem akan memilih kamar 202.
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Hotel Pesona Indonesia. Semua hak dilindungi.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', function() {
                // Set minimum check-out date to be the day after check-in
                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(checkInDate.getDate() + 1);
                const nextDayStr = nextDay.toISOString().split('T')[0];
                checkOutInput.min = nextDayStr;
                
                // If current check-out date is before new minimum, update it
                if (checkOutInput.value && new Date(checkOutInput.value) <= checkInDate) {
                    checkOutInput.value = nextDayStr;
                }
            });
        }
    });
    </script>
</body>
</html>