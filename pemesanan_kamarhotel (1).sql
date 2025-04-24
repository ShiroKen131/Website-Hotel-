-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 09 Apr 2025 pada 13.33
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pemesanan_kamarhotel`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `book_room` (IN `p_user_id` INT, IN `p_room_id` INT, IN `p_check_in` DATE, IN `p_check_out` DATE, IN `p_special_requests` TEXT, OUT `p_booking_id` INT)   BEGIN
    DECLARE room_price DECIMAL(10, 2);
    DECLARE days_count INT;
    DECLARE total DECIMAL(10, 2);
    
    -- Hitung jumlah hari
    SET days_count = DATEDIFF(p_check_out, p_check_in);
    
    -- Dapatkan harga kamar
    SELECT rt.price_per_night INTO room_price
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.room_type_id
    WHERE r.room_id = p_room_id;
    
    -- Hitung total harga
    SET total = room_price * days_count;
    
    -- Buat booking
    INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, total_price, special_requests)
    VALUES (p_user_id, p_room_id, p_check_in, p_check_out, total, p_special_requests);
    
    -- Set status kamar menjadi occupied
    UPDATE rooms SET status = 'occupied' WHERE room_id = p_room_id;
    
    SET p_booking_id = LAST_INSERT_ID();
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `calculate_stay_duration` (`check_in` DATE, `check_out` DATE) RETURNS INT DETERMINISTIC BEGIN
    RETURN DATEDIFF(check_out, check_in);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `available_rooms`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `available_rooms` (
`price_per_night` decimal(10,2)
,`room_id` int
,`room_number` varchar(10)
,`room_type` varchar(50)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `special_requests` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `total_price`, `status`, `special_requests`, `created_at`, `updated_at`) VALUES
(14, 3, 2, '2025-03-17', '2025-03-18', 500000.00, 'pending', '', '2025-03-17 09:37:25', '2025-03-17 09:37:25'),
(15, 3, 5, '2025-03-17', '2025-03-19', 3000000.00, 'confirmed', 'bla bla bla', '2025-03-17 09:45:11', '2025-03-17 09:46:19'),
(16, 1, 3, '2025-03-17', '2025-03-18', 750000.00, 'confirmed', '', '2025-03-17 10:01:16', '2025-03-17 10:01:25'),
(17, 1, 4, '2025-03-17', '2025-03-18', 750000.00, 'confirmed', 'yayayay', '2025-03-17 10:18:45', '2025-03-17 10:18:56'),
(19, 4, 1, '2025-03-20', '2025-03-22', 1000000.00, 'confirmed', 'Tes 123\r\n', '2025-03-20 06:40:42', '2025-03-20 06:40:54'),
(20, 4, 3, '2025-03-20', '2025-03-21', 750000.00, 'confirmed', '', '2025-03-20 06:44:06', '2025-03-20 06:44:15'),
(21, 4, 4, '2025-03-20', '2025-03-22', 1500000.00, 'confirmed', '', '2025-03-20 06:44:51', '2025-03-20 06:44:58'),
(22, 1, 1, '2025-04-08', '2025-04-09', 500000.00, 'confirmed', '', '2025-04-08 15:30:22', '2025-04-08 15:30:33'),
(23, 3, 3, '2025-04-09', '2025-04-11', 1500000.00, 'pending', 'kjk', '2025-04-09 00:29:12', '2025-04-09 00:29:12'),
(24, 1, 4, '2025-04-09', '2025-04-11', 1500000.00, 'confirmed', '', '2025-04-09 01:01:55', '2025-04-09 01:19:31'),
(25, 1, 5, '2025-04-09', '2025-04-11', 3000000.00, 'confirmed', '', '2025-04-09 01:50:11', '2025-04-09 01:50:18');

--
-- Trigger `bookings`
--
DELIMITER $$
CREATE TRIGGER `after_booking_status_update` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    -- Jika booking dibatalkan, ubah status kamar menjadi available
    IF NEW.status = 'cancelled' AND OLD.status != 'cancelled' THEN
        UPDATE rooms SET status = 'available' WHERE room_id = NEW.room_id;
    -- Jika booking selesai, ubah status kamar menjadi available
    ELSEIF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE rooms SET status = 'available' WHERE room_id = NEW.room_id;
    -- Jika booking dikonfirmasi, ubah status kamar menjadi occupied
    ELSEIF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        UPDATE rooms SET status = 'occupied' WHERE room_id = NEW.room_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `booking_statistics`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `booking_statistics` (
`average_booking_value` decimal(14,6)
,`average_rating` decimal(14,4)
,`month` int
,`total_bookings` bigint
,`total_revenue` decimal(32,2)
,`year` int
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `payment_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_method` enum('credit_card','debit_card','transfer','cash') NOT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `amount`, `payment_date`, `payment_method`, `status`, `transaction_id`) VALUES
(3, 15, 3000000.00, '2025-03-17 09:46:19', 'transfer', 'completed', 'payment_15_1742204779.PNG'),
(4, 16, 750000.00, '2025-03-17 10:01:25', 'transfer', 'completed', 'payment_16_1742205685.PNG'),
(5, 17, 750000.00, '2025-03-17 10:18:56', 'transfer', 'completed', 'payment_17_1742206736.PNG'),
(7, 19, 1000000.00, '2025-03-20 06:40:54', 'transfer', 'completed', 'payment_19_1742452854.jpg'),
(8, 20, 750000.00, '2025-03-20 06:44:15', 'transfer', 'completed', 'payment_20_1742453055.png'),
(9, 21, 1500000.00, '2025-03-20 06:44:58', 'transfer', 'completed', 'payment_21_1742453098.png'),
(10, 22, 500000.00, '2025-04-08 15:30:33', 'transfer', 'completed', 'payment_22_1744126233.png'),
(11, 24, 1500000.00, '2025-04-09 01:19:31', 'transfer', 'completed', 'payment_24_1744161571.png'),
(12, 25, 3000000.00, '2025-04-09 01:50:18', 'transfer', 'completed', 'payment_25_1744163418.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Trigger `reviews`
--
DELIMITER $$
CREATE TRIGGER `after_review_insert` AFTER INSERT ON `reviews` FOR EACH ROW BEGIN
    -- Update status booking menjadi completed setelah review
    UPDATE bookings SET status = 'completed' WHERE booking_id = NEW.booking_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_type_id` int NOT NULL,
  `floor` int NOT NULL,
  `status` enum('available','occupied','maintenance') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `room_type_id`, `floor`, `status`) VALUES
(1, '101', 1, 1, 'available'),
(2, '102', 1, 1, 'available'),
(3, '201', 2, 2, 'available'),
(4, '202', 2, 2, 'available'),
(5, '301', 3, 3, 'available'),
(6, '401', 4, 4, 'available');

-- --------------------------------------------------------

--
-- Struktur dari tabel `room_types`
--

CREATE TABLE `room_types` (
  `room_type_id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `max_capacity` int NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `room_types`
--

INSERT INTO `room_types` (`room_type_id`, `name`, `description`, `max_capacity`, `price_per_night`, `image_url`) VALUES
(1, 'Standard', 'Kamar standar dengan satu tempat tidur queen size', 8, 500000.00, '/images/standard.jpg'),
(2, 'Deluxe', 'Kamar luas dengan satu tempat tidur king size', 2, 750000.00, '/images/deluxe.jpg'),
(3, 'Suite', 'Kamar mewah dengan ruang tamu terpisah', 4, 1500000.00, '/images/suite.jpg'),
(4, 'Family', 'Kamar keluarga dengan dua tempat tidur queen size', 4, 1200000.00, '/images/family.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'kenzie', '$2y$10$cLCHK0F81BzSnwGhmuXupe.0vhf6ux/8T1lovo8I.mhgSU1R7j.t.', 'admin', '2025-03-15 03:49:48', '2025-03-15 07:56:33'),
(2, 'kenzie54', '$2y$10$4evTUSSDB2a05UYxaB1sQurDdIZF/tRSRX1U9xDmNipsJx.AbD/SO', 'client', '2025-03-15 03:53:04', '2025-03-15 03:53:04'),
(3, 'Ken', '$2y$10$wXSOgld9.yscIBNn6PLnK.1hUzO/aGpmCp0apIa56SqLTdyKZI9Tm', 'client', '2025-03-15 08:02:07', '2025-03-15 08:02:07'),
(4, 'Kenz', '$2y$10$8xdNoog4fzR5bJJkkQ2sTuFQzxBN6iiBrAdHPG5e8ukOBEZb7j036', 'client', '2025-03-15 08:17:56', '2025-03-20 06:42:52');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Indeks untuk tabel `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`room_type_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `room_types`
--
ALTER TABLE `room_types`
  MODIFY `room_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Struktur untuk view `available_rooms`
--
DROP TABLE IF EXISTS `available_rooms`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `available_rooms`  AS SELECT `r`.`room_id` AS `room_id`, `r`.`room_number` AS `room_number`, `rt`.`name` AS `room_type`, `rt`.`price_per_night` AS `price_per_night` FROM (`rooms` `r` join `room_types` `rt` on((`r`.`room_type_id` = `rt`.`room_type_id`))) WHERE ((`r`.`status` = 'available') AND `r`.`room_id` in (select `b`.`room_id` from `bookings` `b` where ((`b`.`status` in ('confirmed','pending')) AND (`b`.`check_in_date` <= '2025-04-01') AND (`b`.`check_out_date` >= '2025-03-25'))) is false) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `booking_statistics`
--
DROP TABLE IF EXISTS `booking_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `booking_statistics`  AS SELECT year(`bookings`.`check_in_date`) AS `year`, month(`bookings`.`check_in_date`) AS `month`, count(0) AS `total_bookings`, sum(`bookings`.`total_price`) AS `total_revenue`, avg(`bookings`.`total_price`) AS `average_booking_value`, (select avg(`r`.`rating`) from (`reviews` `r` join `bookings` `b` on((`r`.`booking_id` = `b`.`booking_id`))) where ((year(`b`.`check_in_date`) = year(`bookings`.`check_in_date`)) and (month(`b`.`check_in_date`) = month(`bookings`.`check_in_date`)))) AS `average_rating` FROM `bookings` GROUP BY year(`bookings`.`check_in_date`), month(`bookings`.`check_in_date`) ORDER BY `year` DESC, `month` DESC ;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`room_type_id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
