<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "pemesanan_kamarhotel";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi untuk mencari kamar tersedia
function searchAvailableRooms($checkIn, $checkOut, $roomType = null, $capacity = null) {
    global $conn;
    
    $sql = "SELECT r.room_id, r.room_number, rt.name as room_type, rt.description, 
                   rt.max_capacity, rt.price_per_night, rt.image_url
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.room_type_id
            WHERE r.status = 'available' 
            AND r.room_id NOT IN (
                SELECT b.room_id 
                FROM bookings b 
                WHERE b.status IN ('confirmed', 'pending')
                AND (
                    (b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date >= ? AND b.check_out_date <= ?)
                )
            )";
    
    $params = [$checkOut, $checkIn, $checkIn, $checkIn, $checkIn, $checkOut];
    $types = "ssssss";
    
    if ($roomType) {
        $sql .= " AND rt.room_type_id = ?";
        $params[] = $roomType;
        $types .= "i";
    }
    
    if ($capacity) {
        $sql .= " AND rt.max_capacity >= ?";
        $params[] = $capacity;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    
    return $rooms;
}

// Fungsi untuk melakukan booking
function createBooking($userId, $roomId, $checkIn, $checkOut, $specialRequests) {
    global $conn;
    
    $sql = "CALL book_room(?, ?, ?, ?, ?, @booking_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $userId, $roomId, $checkIn, $checkOut, $specialRequests);
    $stmt->execute();
    
    $result = $conn->query("SELECT @booking_id as booking_id");
    $bookingId = $result->fetch_assoc()['booking_id'];
    
    return $bookingId;
}

// Fungsi untuk mendapatkan riwayat booking user