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

$roomTypesQuery = "SELECT room_type_id, name FROM room_types ORDER BY name";
$roomTypes = $conn->query($roomTypesQuery);

// Mendapatkan semua informasi tentan kamar mengambil dari kamar
$roomsQuery = "SELECT 
    r.room_id,
    r.room_number,
    r.floor,
    r.status,
    rt.name as room_type,
    rt.price_per_night,
    rt.max_capacity
FROM rooms r
JOIN room_types rt ON r.room_type_id = rt.room_type_id
ORDER BY r.floor, r.room_number";
$rooms = $conn->query($roomsQuery);

// Add new room
if (isset($_POST['add_room'])) {
    $room_number = $_POST['room_number'];
    $room_type_id = $_POST['room_type_id'];
    $floor = $_POST['floor'];
    $status = $_POST['status'];

    // Check if room number already exists
    $checkQuery = "SELECT COUNT(*) as count FROM rooms WHERE room_number = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $room_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Nomor kamar sudah ada dalam sistem!";
    } else {
        $insertQuery = "INSERT INTO rooms (room_number, room_type_id, floor, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("siis", $room_number, $room_type_id, $floor, $status);
        
        if ($stmt->execute()) {
            $success_message = "Kamar baru berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan kamar: " . $conn->error;
        }
    }
}

// Update room
if (isset($_POST['update_room'])) {
    $room_id = $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $room_type_id = $_POST['room_type_id'];
    $floor = $_POST['floor'];
    $status = $_POST['status'];

    // Check if room number already exists for different room
    $checkQuery = "SELECT COUNT(*) as count FROM rooms WHERE room_number = ? AND room_id != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $room_number, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Nomor kamar sudah ada dalam sistem!";
    } else {
        $updateQuery = "UPDATE rooms SET room_number = ?, room_type_id = ?, floor = ?, status = ? WHERE room_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("siisi", $room_number, $room_type_id, $floor, $status, $room_id);
        
        if ($stmt->execute()) {
            $success_message = "Informasi kamar berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui kamar: " . $conn->error;
        }
    }
}

// Delete room
if (isset($_POST['delete_room'])) {
    $room_id = $_POST['room_id'];
    
    // Check if room is used in any bookings
    $checkQuery = "SELECT COUNT(*) as count FROM bookings WHERE room_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Kamar tidak dapat dihapus karena terdapat pemesanan yang terkait!";
    } else {
        $deleteQuery = "DELETE FROM rooms WHERE room_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $room_id);
        
        if ($stmt->execute()) {
            $success_message = "Kamar berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus kamar: " . $conn->error;
        }
    }
}

// Get room types management
$allRoomTypesQuery = "SELECT * FROM room_types ORDER BY name";
$allRoomTypes = $conn->query($allRoomTypesQuery);

// Add new room type
if (isset($_POST['add_room_type'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $max_capacity = $_POST['max_capacity'];
    $price_per_night = $_POST['price_per_night'];
    $image_url = $_POST['image_url'];

    $insertQuery = "INSERT INTO room_types (name, description, max_capacity, price_per_night, image_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssids", $name, $description, $max_capacity, $price_per_night, $image_url);
    
    if ($stmt->execute()) {
        $success_message = "Tipe kamar baru berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan tipe kamar: " . $conn->error;
    }
}

// Update room type
if (isset($_POST['update_room_type'])) {
    $room_type_id = $_POST['room_type_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $max_capacity = $_POST['max_capacity'];
    $price_per_night = $_POST['price_per_night'];
    $image_url = $_POST['image_url'];

    $updateQuery = "UPDATE room_types SET name = ?, description = ?, max_capacity = ?, price_per_night = ?, image_url = ? WHERE room_type_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssidsi", $name, $description, $max_capacity, $price_per_night, $image_url, $room_type_id);
    
    if ($stmt->execute()) {
        $success_message = "Tipe kamar berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui tipe kamar: " . $conn->error;
    }
}

// Delete room type
if (isset($_POST['delete_room_type'])) {
    $room_type_id = $_POST['room_type_id'];
    
    // Check if room type is used in any rooms
    $checkQuery = "SELECT COUNT(*) as count FROM rooms WHERE room_type_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Tipe kamar tidak dapat dihapus karena terdapat kamar yang menggunakan tipe ini!";
    } else {
        $deleteQuery = "DELETE FROM room_types WHERE room_type_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $room_type_id);
        
        if ($stmt->execute()) {
            $success_message = "Tipe kamar berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus tipe kamar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Hotel Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color:#1DA1F2;
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
                            <a class="nav-link" href="adminside.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="kelolakamar.php">
                                <i class="fas fa-bed me-2"></i>
                                Kelola Kamar
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Kelola Kamar & Tipe Kamar</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Management Tabs -->
                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms"
                            type="button" role="tab" aria-controls="rooms" aria-selected="true">Daftar Kamar</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="myTabContent">
                    <!-- Rooms Tab -->
                    <div class="tab-pane fade show active" id="rooms" role="tabpanel" aria-labelledby="rooms-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Daftar Kamar</h3>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nomor Kamar</th>
                                        <th scope="col">Lantai</th>
                                        <th scope="col">Tipe Kamar</th>
                                        <th scope="col">Kapasitas</th>
                                        <th scope="col">Harga/Malam</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if ($rooms->num_rows > 0) {
                                        while ($room = $rooms->fetch_assoc()) {
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
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $room['room_number']; ?></td>
                                                <td><?php echo $room['floor']; ?></td>
                                                <td><?php echo $room['room_type']; ?></td>
                                                <td><?php echo $room['max_capacity']; ?> orang</td>
                                                <td>Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge status-badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editRoomModal<?php echo $room['room_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteRoomModal<?php echo $room['room_id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Edit Room Modal -->
                                            <div class="modal fade" id="editRoomModal<?php echo $room['room_id']; ?>" tabindex="-1"
                                                aria-labelledby="editRoomModalLabel<?php echo $room['room_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="editRoomModalLabel<?php echo $room['room_id']; ?>">Edit Kamar</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="" method="post">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="room_number" class="form-label">Nomor Kamar</label>
                                                                    <input type="text" class="form-control" id="room_number" name="room_number"
                                                                        value="<?php echo $room['room_number']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="room_type_id" class="form-label">Tipe Kamar</label>
                                                                    <select class="form-select" id="room_type_id" name="room_type_id" required>
                                                                        <?php
                                                                        $roomTypes->data_seek(0);
                                                                        while ($type = $roomTypes->fetch_assoc()) {
                                                                            $selected = '';
                                                                            if ($room['room_type'] == $type['name']) {
                                                                                $selected = 'selected';
                                                                            }
                                                                            echo "<option value='{$type['room_type_id']}' {$selected}>{$type['name']}</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="floor" class="form-label">Lantai</label>
                                                                    <input type="number" class="form-control" id="floor" name="floor"
                                                                        value="<?php echo $room['floor']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="status" class="form-label">Status</label>
                                                                    <select class="form-select" id="status" name="status" required>
                                                                        <option value="available" <?php echo ($room['status'] == 'available') ? 'selected' : ''; ?>>
                                                                            Tersedia</option>
                                                                        <option value="occupied" <?php echo ($room['status'] == 'occupied') ? 'selected' : ''; ?>>
                                                                            Terisi</option>
                                                                        <option value="maintenance" <?php echo ($room['status'] == 'maintenance') ? 'selected' : ''; ?>>
                                                                            Perbaikan</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Tutup</button>
                                                                <button type="submit" name="update_room"
                                                                    class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Room Modal -->
                                            <div class="modal fade" id="deleteRoomModal<?php echo $room['room_id']; ?>" tabindex="-1"
                                                aria-labelledby="deleteRoomModalLabel<?php echo $room['room_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="deleteRoomModalLabel<?php echo $room['room_id']; ?>">Konfirmasi Hapus Kamar</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Anda yakin ingin menghapus kamar nomor <strong><?php echo $room['room_number']; ?></strong>?</p>
                                                            <p class="text-danger"><small>* Catatan: Kamar yang terkait dengan pemesanan tidak dapat dihapus.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <form action="" method="post">
                                                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                                                <button type="submit" name="delete_room" class="btn btn-danger">Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">Tidak ada data kamar</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Room Types Tab -->
                    <div class="tab-pane fade" id="room-types" role="tabpanel" aria-labelledby="room-types-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Daftar Tipe Kamar</h3>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
                                <i class="fas fa-plus me-1"></i> Tambah Tipe Kamar
                            </button>
                        </div>
                        
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <?php
                            if ($allRoomTypes->num_rows > 0) {
                                while ($type = $allRoomTypes->fetch_assoc()) {
                            ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0"><?php echo $type['name']; ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <img src="<?php echo (!empty($type['image_url'])) ? $type['image_url'] : '../../images/default-room.jpg'; ?>" 
                                                class="img-fluid rounded mb-3" alt="<?php echo $type['name']; ?>">
                                            <p class="card-text"><?php echo nl2br($type['description']); ?></p>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <i class="fas fa-users me-2"></i> Kapasitas: <?php echo $type['max_capacity']; ?> orang
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-money-bill-wave me-2"></i> Harga: Rp <?php echo number_format($type['price_per_night'], 0, ',', '.'); ?>/malam
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-between">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                    data-bs-target="#editRoomTypeModal<?php echo $type['room_type_id']; ?>">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteRoomTypeModal<?php echo $type['room_type_id']; ?>">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Room Type Modal -->
                                <div class="modal fade" id="editRoomTypeModal<?php echo $type['room_type_id']; ?>" tabindex="-1"
                                    aria-labelledby="editRoomTypeModalLabel<?php echo $type['room_type_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="editRoomTypeModalLabel<?php echo $type['room_type_id']; ?>">Edit Tipe Kamar</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form action="" method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="room_type_id" value="<?php echo $type['room_type_id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="name" class="form-label">Nama Tipe</label>
                                                        <input type="text" class="form-control" id="name" name="name"
                                                            value="<?php echo $type['name']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="description" class="form-label">Deskripsi</label>
                                                        <textarea class="form-control" id="description" name="description" rows="3"
                                                            required><?php echo $type['description']; ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="max_capacity" class="form-label">Kapasitas Maksimal</label>
                                                        <input type="number" class="form-control" id="max_capacity" name="max_capacity"
                                                            value="<?php echo $type['max_capacity']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="price_per_night" class="form-label">Harga per Malam</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="number" class="form-control" id="price_per_night"
                                                                name="price_per_night" value="<?php echo $type['price_per_night']; ?>"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="image_url" class="form-label">URL Gambar</label>
                                                        <input type="text" class="form-control" id="image_url" name="image_url"
                                                            value="<?php echo $type['image_url']; ?>">
                                                        <div class="form-text">Masukkan URL gambar atau biarkan kosong untuk gambar default</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" name="update_room_type"
                                                        class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                                <?php 
    } // Tutup while loop
} // Tutup if statement
?>
                                            </form>

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

