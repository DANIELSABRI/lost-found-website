-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 29, 2026 at 09:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lost_found`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('lost','found') NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `item_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','matched','claimed','closed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `user_id`, `type`, `item_name`, `category`, `location`, `item_date`, `description`, `image`, `status`, `created_at`) VALUES
(1, 3, 'lost', 'Lost phone manual', 'Electronics', 'International Canteen', '2026-01-25', 'An empty phone manual', '1769337070_6975f0ee22536.jpg', 'claimed', '2026-01-25 10:31:10'),
(2, 3, 'found', 'T-shirt', 'Others', 'Main Building, MB404', '2026-01-12', 'A plain white T-shirt', '1769337173_6975f1557b878.jpg', 'approved', '2026-01-25 10:32:53'),
(3, 2, 'lost', 'water Bottle', 'Others', 'Room 831 Hostel D', '2026-01-25', 'A transparent water bottle', '1769364486_69765c06a0148.jpg', 'approved', '2026-01-25 18:08:06'),
(4, 2, 'lost', 'water Bottle', 'Others', 'Room 831 Hostel D', '2026-01-25', 'Transparent water bottle', '1769364533_69765c3500c37.jpg', 'claimed', '2026-01-25 18:08:53'),
(5, 2, 'lost', 'Black midi', 'Electronics', 'International Canteen', '2026-01-26', 'Midi piano', '1769366138_6976627a5f9d1.png', 'approved', '2026-01-25 18:35:38'),
(6, 3, 'found', 'Keychain', 'Documents (ID, Keys)', 'Lab 18', '2026-01-28', 'Keychain Found', '1769585724_6979bc3cd00ad.jpg', 'approved', '2026-01-28 07:35:24');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `lost_item_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `proposed_by_user_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `lost_item_id`, `found_item_id`, `proposed_by_user_id`, `status`, `created_at`) VALUES
(1, 5, 6, 3, 'pending', '2026-01-28 07:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `item_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 5, 3, 'hello', 1, '2026-01-25 11:18:48'),
(2, 2, 3, 5, 'is it your item?', 1, '2026-01-25 11:19:45'),
(3, 2, 3, 5, 'I found it in the international canteen', 1, '2026-01-25 11:27:01'),
(4, 2, 5, 3, 'yes it is mine', 1, '2026-01-25 11:27:22'),
(5, 4, 5, 2, 'This is my bottle', 1, '2026-01-25 18:37:07'),
(6, 4, 2, 5, 'Where did you leave it', 1, '2026-01-25 18:37:44'),
(7, 4, 2, 5, 'wfbaehfbqhweb', 0, '2026-01-28 09:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT 'system',
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `is_read`, `created_at`) VALUES
(1, 3, 'message', 'New Message', 'You have a new message regarding your item.', 1, '2026-01-25 11:18:48'),
(2, 5, 'message', 'New Message', 'You have a new message regarding your item.', 1, '2026-01-25 11:19:45'),
(3, 5, 'message', 'New Message', 'You have a new message regarding your item.', 0, '2026-01-25 11:27:01'),
(4, 3, 'message', 'New Message', 'You have a new message regarding your item.', 0, '2026-01-25 11:27:22'),
(5, 2, 'message', 'New Message', 'You have a new message regarding your item.', 1, '2026-01-25 18:37:07'),
(6, 5, 'message', 'New Message', 'You have a new message regarding your item.', 0, '2026-01-25 18:37:44'),
(7, 5, 'message', 'New Message', 'You have a new message regarding your item.', 0, '2026-01-28 09:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('allow_registration', '1'),
('auto_approve_items', '0'),
('contact_phone', '+919537293991'),
('maintenance_mode', '0'),
('reports_per_page', '30'),
('site_email', 'admin@gmail.com'),
('site_name', 'Lost & Found Portal');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff','admin') NOT NULL DEFAULT 'student',
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `social_links` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `profile_pic`, `phone`, `bio`, `department`, `social_links`) VALUES
(1, 'System Admin', 'admin@gmail.com', '$2y$12$5QJe7DmFp/3Js1JpsA1Csej/TU3VpmNcz1ccK11gSjaHizEQipSSC', 'admin', 'active', '2026-01-13 10:36:03', 'profile_1_1769592241.jpg', '', '', '', '{\"linkedin\":\"\",\"website\":\"\"}'),
(2, 'Daniel Sabri', 'danielsabri2005@gmail.com', '$2y$10$HTkHRICsvTWTaNfXVJY5OOBl0HtMQ9HL7y2ls3MU4baKadXePuuMK', 'student', 'active', '2026-01-13 10:49:24', 'profile_2_1769366175.jpg', '', 'BCA student', '', '{\"linkedin\":\"\",\"website\":\"\"}'),
(3, 'Daniel Sabri', 'dani@gmail.com', '$2y$10$4VPaA/soL6AGuURlLBWDh.OHkOvS6ew35Z7IR1YWX7pAJWy/t3Q7u', 'student', 'active', '2026-01-13 11:02:23', 'profile_3_1769336294.jpg', '+919537293991', 'Student at Marwadi university, BCA', 'BACHELOR OF COMPUTER APPLICATIONS', '{\"linkedin\":\"\",\"website\":\"\"}'),
(5, 'Ritnen Rita', 'ritnen@gmail.com', '$2y$10$eWd5FDeU2kHIQ5UaFcBJWeGgnSfK/zU/YBq8RDSfQFBqGxi6N5R9e', 'staff', 'active', '2026-01-13 11:52:22', 'profile_5_1769365342.jpg', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
