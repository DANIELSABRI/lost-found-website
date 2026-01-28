-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 28, 2026 at 08:39 AM
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
(4, 2, 'lost', 'water Bottle', 'Others', 'Room 831 Hostel D', '2026-01-25', 'Transparent water bottle', '1769364533_69765c3500c37.jpg', 'matched', '2026-01-25 18:08:53'),
(5, 2, 'lost', 'Black midi', 'Electronics', 'International Canteen', '2026-01-26', 'Midi piano', '1769366138_6976627a5f9d1.png', 'approved', '2026-01-25 18:35:38'),
(6, 3, 'found', 'Keychain', 'Documents (ID, Keys)', 'Lab 18', '2026-01-28', 'Keychain Found', '1769585724_6979bc3cd00ad.jpg', 'approved', '2026-01-28 07:35:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
