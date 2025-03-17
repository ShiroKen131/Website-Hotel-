<?php
// Start session if not already started
session_start();
include 'koneksi.php';



try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get search parameters
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;
$room_type = isset($_GET['room_type']) ? $_GET['room_type'] : '';

// Validate dates
if (empty($check_in) || empty($check_out)) {
    header("Location: index.php?error=dateempty");
    exit();
}

if (strtotime($check_out) <= strtotime($check_in)) {
    header("Location: index.php?error=dateinvalid");
    exit();
}

// Calculate stay duration
$stay_duration = floor((strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24));

// Build query to find available rooms
$query = "
    SELECT r.room_id, r.room_number, r.floor, rt.room_type_id, rt.name AS room_type, 
           rt.description, rt.max_capacity, rt.price_per_night, rt.image_url,
           (rt.price_per_night * :stay_duration) AS total_price
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.room_type_id
    WHERE r.status = 'available' 
    AND r.room_id NOT IN (
        SELECT b.room_id 
        FROM bookings b 
        WHERE b.status IN ('confirmed', 'pending')
        AND (
            (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
        )
    )
";

// Apply room type filter if specified
if (!empty($room_type)) {
    $query .= " AND rt.room_type_id = :room_type";
}

// Apply guest capacity filter
$query .= " AND rt.max_capacity >= :guests";
$query .= " ORDER BY rt.price_per_night ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':check_in', $check_in);
$stmt->bindParam(':check_out', $check_out);
$stmt->bindParam(':stay_duration', $stay_duration);
$stmt->bindParam(':guests', $guests);

if (!empty($room_type)) {
    $stmt->bindParam(':room_type', $room_type);
}

$stmt->execute();
$available_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Store search parameters in session for booking process
$_SESSION['search'] = [
    'check_in' => $check_in,
    'check_out' => $check_out,
    'guests' => $guests,
    'stay_duration' => $stay_duration
];

// Get username if logged in (for navbar)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Kamar - Hotel Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar (include from your template) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Hotel Paradise</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="rooms.php">Kamar</a>
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

    <!-- Search Results Header -->
    <div class="container mt-4">
        <h2>Hasil Pencarian Kamar</h2>
        <div class="search-summary bg-light p-3 rounded mb-4">
            <div class="row">
                <div class="col-md-3">
                    <strong>Check-in:</strong> <?php echo date('d M Y', strtotime($check_in)); ?>
                </div>
                <div class="col-md-3">
                    <strong>Check-out:</strong> <?php echo date('d M Y', strtotime($check_out)); ?>
                </div>
                <div class="col-md-3">
                    <strong>Lama Menginap:</strong> <?php echo $stay_duration; ?> malam
                </div>
                <div class="col-md-3">
                    <strong>Jumlah Tamu:</strong> <?php echo $guests; ?> orang
                </div>
            </div>
        </div>
        
        <!-- Search Results -->
        <?php if (count($available_rooms) > 0): ?>
            <div class="row">
                <?php foreach ($available_rooms as $room): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card room-card h-100">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="<?php echo htmlspecialchars($room['image_url']); ?>" class="img-fluid rounded-start h-100" alt="<?php echo htmlspecialchars($room['room_type']); ?>">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($room['room_type']); ?> - Room <?php echo htmlspecialchars($room['room_number']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($room['description']); ?></p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-user-friends"></i> Kapasitas: <?php echo $room['max_capacity']; ?> tamu</li>
                                            <li><i class="fas fa-building"></i> Lantai: <?php echo $room['floor']; ?></li>
                                            <li><i class="fas fa-tag"></i> Harga: Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?>/malam</li>
                                            <li><i class="fas fa-calculator"></i> Total: Rp <?php echo number_format($room['total_price'], 0, ',', '.'); ?></li>
                                        </ul>
                                        <div class="d-grid gap-2">
                                            <a href="book_room.php?room_id=<?php echo $room['room_id']; ?>" class="btn btn-primary mt-2">Pesan Sekarang</a>
                                            <a href="room_detail.php?id=<?php echo $room['room_id']; ?>" class="btn btn-outline-secondary mt-1">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h4>Tidak ada kamar tersedia</h4>
                <p>Maaf, tidak ada kamar tersedia untuk kriteria pencarian Anda. Silakan coba tanggal lain atau ubah kriteria pencarian.</p>
                <a href="index.php" class="btn btn-primary">Kembali ke Pencarian</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer (add your footer) -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Hotel Paradise</h5>
                    <p>Jl. Pemandangan Indah No. 123<br>Jakarta, Indonesia 12345</p>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p>
                        <i class="fas fa-phone"></i> +62-21-1234-5678<br>
                        <i class="fas fa-envelope"></i> info@hotelparadise.com
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Ikuti Kami</h5>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <p>&copy; 2025 Hotel Paradise. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>