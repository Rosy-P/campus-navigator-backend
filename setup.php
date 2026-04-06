<?php
// setup.php - Run once to import database
header("Content-Type: application/json");

$host = 'mysql.railway.internal';
$username = 'root';
$password = 'BZxOqkRtcTIFlFAUdSrZARaDbHDjppUQ';
$database = 'railway';
$port = 3306;

try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $e->getMessage()]));
}

$queries = [

// --------------------------------------------------------
// events
// --------------------------------------------------------
"CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// --------------------------------------------------------
// facilities
// --------------------------------------------------------
"CREATE TABLE IF NOT EXISTS `facilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('open','closed','crowded') DEFAULT 'open',
  `image_url` varchar(255) DEFAULT NULL,
  `hours` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `is_manual_override` tinyint(1) DEFAULT 0,
  `manual_status` enum('open','closed') DEFAULT NULL,
  `occupancy` int(11) DEFAULT 0,
  `distance` varchar(50) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `image` varchar(500) DEFAULT NULL,
  `website` varchar(255) DEFAULT 'https://mcc.edu.in',
  `address` varchar(255) DEFAULT 'MCC Campus, Tambaram',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// --------------------------------------------------------
// facility_ratings
// --------------------------------------------------------
"CREATE TABLE IF NOT EXISTS `facility_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// --------------------------------------------------------
// saved_locations
// --------------------------------------------------------
"CREATE TABLE IF NOT EXISTS `saved_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `block` varchar(100) DEFAULT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// --------------------------------------------------------
// system_settings
// --------------------------------------------------------
"CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `default_location` varchar(100) DEFAULT 'Main Gate',
  `default_zoom` int(11) DEFAULT 17,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// --------------------------------------------------------
// users
// --------------------------------------------------------
"CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','suspended') DEFAULT 'active',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// --------------------------------------------------------
// Insert events
// --------------------------------------------------------
"INSERT IGNORE INTO `events` (`id`, `title`, `description`, `location`, `event_date`, `event_time`, `created_by`, `created_at`, `latitude`, `longitude`) VALUES
(9, 'AI Workshop', 'An AI workshop introduces participants to the fundamentals of artificial intelligence, including concepts like machine learning, data analysis, and real-world applications.', 'Anderson Hall', '2026-03-23', '12:00:00', 8, '2026-03-23 00:54:50', NULL, NULL),
(10, 'Digital Photography & Visual Storytelling', 'This workshop teaches Visual Communication students the fundamentals of camera handling, composition, lighting, and storytelling through images.', 'Center for Media Studies', '2026-03-24', '11:00:00', 8, '2026-03-23 00:56:20', NULL, NULL),
(11, 'Intercollegiate Dance Fest', 'This dance event brings together talented students to showcase various styles like classical, hip-hop, and contemporary.', 'Heber Hall', '2026-03-24', '10:00:00', 8, '2026-03-23 00:57:31', NULL, NULL),
(12, 'AI workshop', 'AI workshop conducted by computer science at anderson hall which is free for anyone to come and participate.', 'Anderson Hall', '2026-03-26', '12:00:00', 8, '2026-03-26 09:39:31', NULL, NULL),
(13, 'Easter Prayer', 'All are welcome to join to praise God.', 'Bishop Heber Chapel', '2026-03-30', '06:00:00', 8, '2026-03-30 07:29:38', NULL, NULL)",

// --------------------------------------------------------
// Insert facilities
// --------------------------------------------------------
"INSERT IGNORE INTO `facilities` (`id`, `name`, `category`, `description`, `latitude`, `longitude`, `status`, `image_url`, `hours`, `phone`, `is_manual_override`, `manual_status`, `occupancy`, `distance`, `rating`, `image`, `website`, `address`) VALUES
(1, 'Computer Lab', 'Labs', 'High-performance computing center.', 12.92400000, 80.12400000, 'closed', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97', '09:00 - 18:00', '+91 44 2239 6789', 0, NULL, 0, NULL, 0.0, '', 'https://mcc.edu.in', ''),
(2, 'Zoology Lab', 'Labs', 'Equipped laboratory for biological specimen study and research.', 12.91862000, 80.13875000, 'closed', 'https://images.unsplash.com/photo-1581091012184-7f3c37d9b4b4', '08:30 - 16:30', '+91 44 2239 0001', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(3, 'Botany Lab', 'Labs', 'Plant research and botanical experiment laboratory.', 12.91870000, 80.13860000, 'open', 'https://images.unsplash.com/photo-1581091870627-3d6b1d4d6f7b', '08:30 - 16:30', '+91 44 2239 0002', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(4, 'Library', 'Academic', 'Central academic library with extensive research and reference materials.', 12.91820000, 80.13900000, 'open', 'https://images.unsplash.com/photo-1507842217343-583bb7270b66', '08:00 - 20:00', '+91 44 2239 9999', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(5, 'Bishop Heber Chapel', 'Spiritual', 'Historic chapel serving as a peaceful prayer and reflection space.', 12.91876640, 80.12379430, 'open', 'https://images.unsplash.com/photo-1548625361-195fe0182a7f', '06:00 - 20:00', 'N/A', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(6, 'MCC Campus Clinic', 'Medical', 'On-campus medical clinic providing first aid and health support.', 12.92074350, 80.12276500, 'open', 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d', '08:00 - 20:00', '+91 44 2239 0005', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(7, 'MCC Cafeteria', 'Food', 'Main student cafeteria offering meals and refreshments.', 12.92034000, 80.12273000, 'crowded', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5', '08:00 - 18:00', 'N/A', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(9, 'MCC Indoor Stadium', 'Sports', 'Indoor stadium for basketball, badminton and indoor events.', 12.91691000, 80.11940000, 'open', 'https://images.unsplash.com/photo-1541252260730-0412e8e2108e', '06:00 - 21:00', 'N/A', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(11, 'Main Gate', 'Entrance', 'Primary entrance of the campus connecting to Tambaram Main Road.', 12.92316300, 80.12058400, 'open', 'https://images.unsplash.com/photo-1590059523275-58a36c568f6a', 'Open 24 Hours', '+91 44 2239 0001', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram'),
(12, 'Fashion Studio', 'Creative', 'A creative space where students can explore fashion design, styling, and garment creation.', 12.92077095, 80.12223632, 'open', 'https://acfitouts.com.au/wp-content/uploads/2021/07/workplaces-of-clothing-designers-of-start-up-business-.jpg', '09:00 - 17:00', 'N/A', 0, NULL, 0, NULL, 0.0, NULL, 'https://mcc.edu.in', 'MCC Campus, Tambaram')",

// --------------------------------------------------------
// Insert facility_ratings
// --------------------------------------------------------
"INSERT IGNORE INTO `facility_ratings` (`id`, `facility_id`, `rating`, `created_at`) VALUES
(1, 2, 5, '2026-03-28 11:00:39'),(2, 3, 5, '2026-03-28 11:01:08'),(3, 1, 1, '2026-03-28 11:08:13'),
(4, 1, 2, '2026-03-28 11:08:15'),(5, 1, 3, '2026-03-28 11:08:16'),(6, 1, 4, '2026-03-28 11:08:19'),
(7, 1, 3, '2026-03-28 11:08:21'),(8, 1, 3, '2026-03-28 11:08:23'),(9, 1, 3, '2026-03-28 11:08:24'),
(10, 1, 3, '2026-03-28 11:08:25'),(11, 1, 3, '2026-03-28 11:08:26'),(12, 1, 3, '2026-03-28 11:08:26'),
(13, 1, 3, '2026-03-28 11:08:26'),(14, 1, 3, '2026-03-28 11:08:27'),(15, 1, 3, '2026-03-28 11:08:27'),
(16, 1, 3, '2026-03-28 11:08:27'),(17, 1, 3, '2026-03-28 11:08:27'),(18, 1, 3, '2026-03-28 11:08:28'),
(19, 1, 3, '2026-03-28 11:08:29'),(20, 1, 3, '2026-03-28 11:08:30'),(21, 1, 3, '2026-03-28 11:08:31'),
(22, 1, 4, '2026-03-28 11:08:33'),(23, 5, 1, '2026-03-28 16:05:06'),(24, 1, 4, '2026-03-31 06:39:58')",

// --------------------------------------------------------
// Insert saved_locations
// --------------------------------------------------------
"INSERT IGNORE INTO `saved_locations` (`id`, `user_id`, `name`, `block`, `floor`, `latitude`, `longitude`, `created_at`) VALUES
(1, 0, 'Macphails Arts Center', '', '', 12.919368000444521, 80.12175679206848, '2026-03-19 13:37:53'),
(2, 0, 'MCC School of Continuing Education', '', '', 12.919464076344127, 80.1202929764986, '2026-03-19 13:54:06'),
(3, 1, 'Macphails Arts Center', '', '', 12.919368000444521, 80.12175679206848, '2026-03-25 02:15:26'),
(4, 1, 'Quadrangle MCC', '', '', 12.921765965419254, 80.12211687862873, '2026-03-25 02:29:23'),
(5, 1, 'MCC Campus Clinic', '', '', 12.92074345087238, 80.12276496738195, '2026-03-25 02:29:44'),
(10, 8, 'Bell Tower', '', '', 12.920524176674007, 80.12206323444843, '2026-03-30 07:26:17')",

// --------------------------------------------------------
// Insert system_settings
// --------------------------------------------------------
"INSERT IGNORE INTO `system_settings` (`id`, `default_location`, `default_zoom`, `updated_at`) VALUES
(1, 'Main Gate', 17, '2026-03-23 01:01:10')",

// --------------------------------------------------------
// Insert users
// --------------------------------------------------------
"INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `status`, `reset_token`, `token_expiry`) VALUES
(1, 'ros', 'rosy@gmail.com', '\$2y\$10\$KNzkRSC0oqCAdFnCyHRDr.yKQG0HfoFpObpNEgtfM4udxiCH6c/b6', 'student', '2026-02-07 10:54:48', 'active', NULL, NULL),
(2, 'rosy', 'bc@gmail.cpm', '\$2y\$10\$C55YxZ0.pQ/bskZtsU5zfuaXrL84VxGb0GojS1wuia7ICuT1u1CU2', 'student', '2026-02-07 11:15:48', 'active', NULL, NULL),
(3, 'bv', 'bv@gmail.com', '\$2y\$10\$bR/4b47ce4ewdPosQLn4ju7/fbEMs7LJZeKlsVEPJG.QbH4LoIUOu', 'student', '2026-02-07 11:24:12', 'active', NULL, NULL),
(4, 'bs', 'bs@gmail.com', '\$2y\$10\$Hkrbv25iY0I6s/tQaVSlJOZGIXlkRrm5SnbxT/yGQ2vBDpV/YNTVC', 'student', '2026-02-07 11:39:26', 'active', NULL, NULL),
(5, 'bc', 'bc@gmail.com', '\$2y\$10\$mQ.uzAoEgRNHQZ0sJpgXKOux5ZH4PuVVoB/F9TUsq9mZtWFByJkka', 'student', '2026-02-07 11:45:47', 'active', NULL, NULL),
(6, 'rose', 'roose@gmail.com', '\$2y\$10\$EUdW8YNdeL/k2mU7M0YyteXHlI3izgJld8NkSDC/2SCWO6Rs.nFWW', 'student', '2026-02-07 11:55:21', 'active', NULL, NULL),
(7, 'sha', 'sha@gmail.com', '\$2y\$10\$tWB/Xn8K/Oyb0jRzaAqA2eNVlKcwwwDmMFaPhhNLhM6Ww1fg9ZZA6', 'student', '2026-02-07 13:26:54', 'active', NULL, NULL),
(8, 'AD', 'admin@gmail.com', '\$2y\$10\$X1A5ITzZDKFnmwZiHUvgn.tb0n2y0ZSJt9ohmgeELSrNvg9uWiP32', 'admin', '2026-02-11 16:26:16', 'active', NULL, NULL),
(9, 'John', 'superadmin@gmail.com', '\$2y\$10\$1WyLhkcfhyScqN2KaD4XzO2eSWm4WTPDV7ydvC/xBayAWzuMMoUXK', 'superadmin', '2026-02-14 10:17:01', 'active', NULL, NULL),
(10, 'shiny', 'shiny@gmail.com', '\$2y\$10\$CdWDE4oOagNzlT.S/PFU2e3EtifVVoJwcZ3A1Y1TMJKdhRB04zZT.', 'student', '2026-03-19 12:30:38', 'active', NULL, NULL),
(11, 'Test Tester', 'tester@mcc.edu.in', '\$2y\$10\$PxkIbBXLdSOd0ArhZA2yBOJtQ.0Ni6tqJS9.5Ex2asItZtLVTe6hm', 'student', '2026-03-28 10:20:38', 'active', NULL, NULL),
(12, 'Srubi', 'roserosy1811@gmail.com', '\$2y\$10\$oAVWfYtnqRCAFJSs.8NrdOHkiq6n5N.WqKhWi8/X5x2taZR9Uvw72', 'student', '2026-03-30 09:09:19', 'active', NULL, NULL)",

// --------------------------------------------------------
// Foreign keys
// --------------------------------------------------------
"ALTER TABLE `events` ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY IF NOT EXISTS (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL",
"ALTER TABLE `facility_ratings` ADD CONSTRAINT `facility_ratings_ibfk_1` FOREIGN KEY IF NOT EXISTS (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE",

// Auto increment values
"ALTER TABLE `events` AUTO_INCREMENT = 14",
"ALTER TABLE `facilities` AUTO_INCREMENT = 13",
"ALTER TABLE `facility_ratings` AUTO_INCREMENT = 25",
"ALTER TABLE `saved_locations` AUTO_INCREMENT = 11",
"ALTER TABLE `users` AUTO_INCREMENT = 13",
];

$results = [];
$errors = [];

foreach ($queries as $sql) {
    try {
        if ($conn->query($sql)) {
            $results[] = "✅ OK";
        } else {
            $errors[] = "❌ " . $conn->error;
        }
    } catch (Exception $e) {
        $errors[] = "❌ " . $e->getMessage();
    }
}

$conn->close();

echo json_encode([
    "status" => count($errors) === 0 ? "success" : "partial",
    "message" => count($errors) === 0 ? "Database imported successfully!" : "Completed with some errors",
    "executed" => count($results),
    "errors" => $errors
], JSON_PRETTY_PRINT);
?>
