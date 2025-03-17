<?php
session_start();
require_once '../../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    if (!$bypass_redirect) {
        header("Location: ../../index.php");
        exit();
    } else {
        die("Akses ditolak - Anda bukan admin atau belum login");
    }
}

// Get statistics for admin dashboard
$statsQuery = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
    SUM(total_price) as total_revenue
FROM bookings";
$statsResult = $conn->query($statsQuery);
$statistics = $statsResult->fetch_assoc();

// Get bookings with user and room information
$bookingsQuery = "SELECT 
    b.booking_id as id,
    b.created_at as booking_date,
    u.username,
    rt.name as room_type,
    r.room_number,
    b.check_in_date,
    DATEDIFF(b.check_out_date, b.check_in_date) as nights,
    rt.price_per_night as price,
    b.total_price,
    b.status,
    p.payment_id,
    p.payment_method,
    p.status as payment_status,
    p.transaction_id
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN rooms r ON b.room_id = r.room_id
JOIN room_types rt ON r.room_type_id = rt.room_type_id
LEFT JOIN payments p ON b.booking_id = p.booking_id
ORDER BY b.created_at DESC";

$bookings = $conn->query($bookingsQuery);

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];

    $updateQuery = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $new_status, $booking_id);

    if ($stmt->execute()) {
        header("Location: ?status_updated=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, .5);
        }

        .sidebar .nav-link.active {
            color: #fff;
        }

        .sidebar .nav-link:hover {
            color: #fff;
        }

        .status-badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }

        .card-dashboard {
            transition: all 0.3s;
        }

        .card-dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <div class="text-center mb-4">
                                <h4 class="text-white">Hotel Admin</h4>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-bed me-2"></i>
                                Kelola Kamar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-users me-2"></i>
                                Kelola Pengguna
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-line me-2"></i>
                                Laporan
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-danger" href="../../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Admin</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <?php if (isset($_GET['status_updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Status pemesanan berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-dashboard bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Pemesanan</h6>
                                        <h2 class="mb-0"><?php echo $statistics['total_bookings']; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-dashboard bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pemesanan Pending</h6>
                                        <h2 class="mb-0"><?php echo $statistics['pending_bookings']; ?></h2>
                                    </div>
                                    <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-dashboard bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Pendapatan</h6>
                                        <h2 class="mb-0">Rp
                                            <?php echo number_format($statistics['total_revenue'], 0, ',', '.'); ?></h2>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking and Room Tabs -->
                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab"
                            data-bs-target="#bookings" type="button" role="tab" aria-controls="bookings"
                            aria-selected="true">Pemesanan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms"
                            type="button" role="tab" aria-controls="rooms" aria-selected="false">Status Kamar</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments"
                            type="button" role="tab" aria-controls="payments" aria-selected="false">Pembayaran</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="myTabContent">
                    <!-- Bookings Tab -->
                    <div class="tab-pane fade show active" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
                        <h3 class="mb-3">Daftar Pemesanan</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Tanggal Pesan</th>
                                        <th scope="col">Tamu</th>
                                        <th scope="col">Kamar</th>
                                        <th scope="col">Check-in</th>
                                        <th scope="col">Durasi</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if ($bookings->num_rows > 0) {
                                        while ($row = $bookings->fetch_assoc()) {
                                            $status_class = '';
                                            switch ($row['status']) {
                                                case 'pending':
                                                    $status_class = 'bg-warning text-dark';
                                                    break;
                                                case 'confirmed':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'bg-danger';
                                                    break;
                                                case 'completed':
                                                    $status_class = 'bg-info';
                                                    break;
                                            }
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['booking_date'])); ?></td>
                                                <td>
                                                    <span data-bs-toggle="tooltip">
                                                        <?php echo $row['username']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $row['room_type'] . ' (' . $row['room_number'] . ')'; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['check_in_date'])); ?></td>
                                                <td><?php echo $row['nights']; ?> malam</td>
                                                <td>Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge status-badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal<?php echo $row['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1"
                                                aria-labelledby="editModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="editModalLabel<?php echo $row['id']; ?>">Update Status
                                                                Pemesanan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="" method="post">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="booking_id"
                                                                    value="<?php echo $row['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="new_status" class="form-label">Status
                                                                        Baru</label>
                                                                    <select class="form-select" id="new_status"
                                                                        name="new_status" required>
                                                                        <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>
                                                                            Pending</option>
                                                                        <option value="confirmed" <?php echo ($row['status'] == 'confirmed') ? 'selected' : ''; ?>>
                                                                            Confirmed</option>
                                                                        <option value="cancelled" <?php echo ($row['status'] == 'cancelled') ? 'selected' : ''; ?>>
                                                                            Cancelled</option>
                                                                        <option value="completed" <?php echo ($row['status'] == 'completed') ? 'selected' : ''; ?>>
                                                                            Completed</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Tutup</button>
                                                                <button type="submit" name="update_status"
                                                                    class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Detail Modal -->
                                            <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1"
                                                aria-labelledby="detailModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="detailModalLabel<?php echo $row['id']; ?>">Detail Pemesanan
                                                                #<?php echo $row['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6>Informasi Pemesanan</h6>
                                                                    <hr>
                                                                    <p><strong>Tanggal Pemesanan:</strong>
                                                                        <?php echo date('d M Y', strtotime($row['booking_date'])); ?>
                                                                    </p>
                                                                    <p><strong>Nama Tamu:</strong>
                                                                        <?php echo $row['username']; ?></p>

                                                                    <p><strong>Tipe Kamar:</strong>
                                                                        <?php echo $row['room_type']; ?></p>
                                                                    <p><strong>Nomor Kamar:</strong>
                                                                        <?php echo $row['room_number']; ?></p>
                                                                    <p><strong>Check-in:</strong>
                                                                        <?php echo date('d M Y', strtotime($row['check_in_date'])); ?>
                                                                    </p>
                                                                    <p><strong>Lama Menginap:</strong>
                                                                        <?php echo $row['nights']; ?> malam</p>
                                                                    <p><strong>Total Harga:</strong> Rp
                                                                        <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                                                                    </p>
                                                                    <p><strong>Status:</strong> <span
                                                                            class="badge status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Informasi Pembayaran</h6>
                                                                    <hr>
                                                                    <?php if ($row['payment_id']): ?>
                                                                        <p><strong>Metode Pembayaran:</strong>
                                                                            <?php echo ucfirst(str_replace('_', ' ', $row['payment_method'])); ?>
                                                                        </p>
                                                                        <p><strong>Status Pembayaran:</strong>
                                                                            <span class="badge <?php
                                                                                                echo ($row['payment_status'] == 'completed') ? 'bg-success' : (($row['payment_status'] == 'pending') ? 'bg-warning text-dark' : (($row['payment_status'] == 'failed') ? 'bg-danger' : 'bg-info'));
                                                                                                ?>">
                                                                                <?php echo ucfirst($row['payment_status']); ?>
                                                                            </span>
                                                                        </p>
                                                                        <?php if ($row['transaction_id']): ?>
                                                                            <p><strong>ID Transaksi:</strong>
                                                                                <?php echo $row['transaction_id']; ?></p>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <p class="text-danger">Belum ada informasi pembayaran</p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="9" class="text-center">Tidak ada data pemesanan</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Rooms Tab -->
                    <div class="tab-pane fade" id="rooms" role="tabpanel" aria-labelledby="rooms-tab">
                        <h3 class="mb-3">Status Kamar</h3>
                        <?php
                        // Get room status information
                        $roomsQuery = "SELECT 
                        r.room_id,
                        r.room_number,
                        rt.name as room_type,
                        r.floor,
                        r.status,
                        IFNULL(u.username, '-') as guest_name,
                        IFNULL(b.check_in_date, '-') as check_in,
                        IFNULL(b.check_out_date, '-') as check_out
                    FROM rooms r
                    JOIN room_types rt ON r.room_type_id = rt.room_type_id
                    LEFT JOIN (
                        SELECT b.room_id, b.user_id, b.check_in_date, b.check_out_date 
                        FROM bookings b 
                        WHERE b.status = 'confirmed' 
                        AND (b.check_in_date <= CURDATE() AND b.check_out_date >= CURDATE())
                    ) b ON r.room_id = b.room_id
                    LEFT JOIN users u ON b.user_id = u.user_id
                    ORDER BY r.floor, r.room_number";

                        $roomsResult = $conn->query($roomsQuery);
                        ?>

                        <div class="row">
                            <?php
                            $currentFloor = 0;
                            while ($room = $roomsResult->fetch_assoc()) {
                                if ($currentFloor != $room['floor']) {
                                    if ($currentFloor != 0) {
                                        echo '</div></div>';
                                    }
                                    $currentFloor = $room['floor'];
                                    echo '<div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0">Lantai ' . $room['floor'] . '</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">';
                                }

                                $statusClass = '';
                                $statusText = '';

                                switch ($room['status']) {
                                    case 'available':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Tersedia';
                                        break;
                                    case 'occupied':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Terisi';
                                        break;
                                    case 'maintenance':
                                        $statusClass = 'bg-warning text-dark';
                                        $statusText = 'Perbaikan';
                                        break;
                                }
                            ?>

                                <div class="col-md-3 mb-3">
                                    <div
                                        class="card h-100 border-<?php echo ($room['status'] == 'available') ? 'success' : (($room['status'] == 'occupied') ? 'danger' : 'warning'); ?>">
                                        <div class="card-header <?php echo $statusClass; ?> text-white py-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><strong>Kamar <?php echo $room['room_number']; ?></strong></span>
                                                <span class="badge bg-light text-dark"><?php echo $statusText; ?></span>
                                            </div>
                                        </div>
                                        <div class="card-body p-2">
                                            <p class="card-text mb-1"><small>Tipe: <?php echo $room['room_type']; ?></small>
                                            </p>
                                            <?php if ($room['status'] == 'occupied' && $room['guest_name'] != '-'): ?>
                                                <p class="card-text mb-1"><small>Tamu:
                                                        <?php echo $room['guest_name']; ?></small></p>
                                                <p class="card-text mb-1"><small>Check-in:
                                                        <?php echo date('d/m/Y', strtotime($room['check_in'])); ?></small></p>
                                                <p class="card-text mb-0"><small>Check-out:
                                                        <?php echo date('d/m/Y', strtotime($room['check_out'])); ?></small></p>
                                            <?php else: ?>
                                                <p class="card-text mb-0"><small>Status: <?php echo $statusText; ?></small></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            <?php
                            }
                            if ($currentFloor != 0) {
                                echo '</div></div></div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Payments Tab -->
                    <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                        <h3 class="mb-3">Daftar Pembayaran</h3>
                        <?php
                        // Get payment information
                        $paymentsQuery = "SELECT 
                        p.payment_id,
                        p.booking_id,
                        u.username,
                        r.room_number,
                        rt.name as room_type,
                        b.check_in_date,
                        b.check_out_date,
                        p.amount,
                        p.payment_date,
                        p.payment_method,
                        p.status,
                        p.transaction_id
                    FROM payments p
                    JOIN bookings b ON p.booking_id = b.booking_id
                    JOIN users u ON b.user_id = u.user_id
                    JOIN rooms r ON b.room_id = r.room_id
                    JOIN room_types rt ON r.room_type_id = rt.room_type_id
                    ORDER BY p.payment_date DESC";

                        $paymentsResult = $conn->query($paymentsQuery);
                        ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Tamu</th>
                                        <th scope="col">Kamar</th>
                                        <th scope="col">Jumlah</th>
                                        <th scope="col">Metode</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if ($paymentsResult->num_rows > 0) {
                                        while ($payment = $paymentsResult->fetch_assoc()) {
                                            $paymentStatusClass = '';
                                            switch ($payment['status']) {
                                                case 'pending':
                                                    $paymentStatusClass = 'bg-warning text-dark';
                                                    break;
                                                case 'completed':
                                                    $paymentStatusClass = 'bg-success';
                                                    break;
                                                case 'failed':
                                                    $paymentStatusClass = 'bg-danger';
                                                    break;
                                                case 'refunded':
                                                    $paymentStatusClass = 'bg-info';
                                                    break;
                                            }
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></td>
                                                <td>
                                                    <span data-bs-toggle="tooltip">
                                                        <?php echo $payment['username']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $payment['room_type'] . ' (' . $payment['room_number'] . ')'; ?>
                                                </td>
                                                <td>Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></td>
                                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge <?php echo $paymentStatusClass; ?>">
                                                        <?php echo ucfirst($payment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentDetailModal<?php echo $payment['payment_id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Payment Detail Modal -->
                                            <div class="modal fade" id="paymentDetailModal<?php echo $payment['payment_id']; ?>"
                                                tabindex="-1"
                                                aria-labelledby="paymentDetailModalLabel<?php echo $payment['payment_id']; ?>"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="paymentDetailModalLabel<?php echo $payment['payment_id']; ?>">
                                                                Detail Pembayaran #<?php echo $payment['payment_id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>ID Booking:</strong>
                                                                #<?php echo $payment['booking_id']; ?></p>
                                                            <p><strong>Tamu:</strong> <?php echo $payment['username']; ?></p>
                                                            <p><strong>Kamar:</strong>
                                                                <?php echo $payment['room_type'] . ' (' . $payment['room_number'] . ')'; ?>
                                                            </p>
                                                            <p><strong>Check-in:</strong>
                                                                <?php echo date('d M Y', strtotime($payment['check_in_date'])); ?>
                                                            </p>
                                                            <p><strong>Check-out:</strong>
                                                                <?php echo date('d M Y', strtotime($payment['check_out_date'])); ?>
                                                            </p>
                                                            <p><strong>Jumlah Pembayaran:</strong> Rp
                                                                <?php echo number_format($payment['amount'], 0, ',', '.'); ?>
                                                            </p>
                                                            <p><strong>Metode Pembayaran:</strong>
                                                                <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                                            </p>
                                                            <p><strong>Tanggal Pembayaran:</strong>
                                                                <?php echo date('d M Y H:i:s', strtotime($payment['payment_date'])); ?>
                                                            </p>
                                                            <p><strong>Status:</strong>
                                                                <span class="badge <?php echo $paymentStatusClass; ?>">
                                                                    <?php echo ucfirst($payment['status']); ?>
                                                                </span>
                                                            </p>
                                                            <?php if ($payment['transaction_id']): ?>
                                                                <p><strong>ID Transaksi:</strong>
                                                                    <?php echo $payment['transaction_id']; ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">Tidak ada data pembayaran</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>

</html>