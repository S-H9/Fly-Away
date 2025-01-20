-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2025 at 05:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fly_away`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `seat_number` varchar(5) NOT NULL,
  `class` varchar(20) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'confirmed',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `flight_id`, `seat_number`, `class`, `price`, `status`, `booking_date`) VALUES
(1, 1, 2, 'F4', 'economy', 200.00, 'cancelled', '2025-01-09 13:50:05'),
(3, 2, 1, 'B3', 'first', 2000.00, 'confirmed', '2025-01-09 15:45:10'),
(4, 2, 2, 'A4', 'economy', 200.00, 'confirmed', '2025-01-18 14:56:54'),
(5, 5, 7, 'D3', 'economy', 9000.00, 'confirmed', '2025-01-19 22:59:29'),
(6, 5, 7, 'F4', 'first', 18000.00, 'confirmed', '2025-01-19 23:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `flight_id` int(11) NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `arrival_city` varchar(100) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`flight_id`, `departure_city`, `arrival_city`, `departure_time`, `arrival_time`, `price`) VALUES
(1, 'New York', 'Bali', '2025-01-19 10:00:00', '2025-01-20 22:00:00', 1000.00),
(2, 'London', 'Paris', '2025-01-21 08:00:00', '2025-01-21 09:30:00', 200.00),
(3, 'Paris', 'Tokyo', '2025-01-22 12:00:00', '2025-01-23 06:00:00', 800.00),
(7, 'Jeddah', 'Japan', '2025-01-20 23:36:00', '2026-02-12 03:00:00', 9000.00),
(8, 'Jeddah', 'Dubai', '2025-01-25 08:00:00', '2025-01-25 10:30:00', 450.00),
(9, 'Riyadh', 'Kuwait', '2025-01-25 11:00:00', '2025-01-25 12:30:00', 350.00),
(10, 'Dubai', 'Makkah', '2025-01-26 07:00:00', '2025-01-26 09:30:00', 500.00),
(11, 'Jeddah', 'Madinah', '2025-01-26 14:00:00', '2025-01-26 15:30:00', 200.00),
(12, 'Riyadh', 'Abu Dhabi', '2025-01-27 09:00:00', '2025-01-27 10:30:00', 400.00),
(13, 'London', 'Paris', '2025-01-27 08:00:00', '2025-01-27 09:30:00', 200.00),
(14, 'Paris', 'Rome', '2025-01-28 10:00:00', '2025-01-28 12:00:00', 180.00),
(15, 'Berlin', 'Amsterdam', '2025-01-28 13:00:00', '2025-01-28 14:30:00', 150.00),
(16, 'Madrid', 'Barcelona', '2025-01-29 07:30:00', '2025-01-29 08:30:00', 120.00),
(17, 'Rome', 'Athens', '2025-01-29 11:00:00', '2025-01-29 13:00:00', 160.00),
(18, 'Tokyo', 'Seoul', '2025-01-30 09:00:00', '2025-01-30 11:30:00', 300.00),
(19, 'Beijing', 'Shanghai', '2025-01-30 14:00:00', '2025-01-30 16:00:00', 250.00),
(20, 'Singapore', 'Bangkok', '2025-01-31 10:00:00', '2025-01-31 11:30:00', 200.00),
(21, 'Hong Kong', 'Tokyo', '2025-01-31 13:00:00', '2025-01-31 18:00:00', 450.00),
(22, 'Seoul', 'Singapore', '2025-02-01 08:00:00', '2025-02-01 14:00:00', 500.00),
(23, 'New York', 'London', '2025-02-01 22:00:00', '2025-02-02 10:00:00', 800.00),
(24, 'Dubai', 'Los Angeles', '2025-02-02 01:00:00', '2025-02-02 16:00:00', 1200.00),
(25, 'Tokyo', 'Paris', '2025-02-02 23:00:00', '2025-02-03 14:00:00', 1100.00),
(26, 'Sydney', 'Dubai', '2025-02-03 06:00:00', '2025-02-03 20:00:00', 900.00),
(27, 'Jeddah', 'New York', '2025-02-03 12:00:00', '2025-02-04 04:00:00', 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` varchar(10) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `profile_image`, `created_at`, `user_type`) VALUES
(1, 's1', 'ss1@gmail.com', '$2y$10$Zct5mHlPGRXWfPyPNE654.2GC8HZpMX6mounAfj17cUl.NnvcAKb2', 'imges/default-profile.jpg', '2025-01-09 13:29:57', 'admin'),
(2, 's', 'sohiyb7@gmail.com', '$2y$10$4uCM.UseLnqRxlFLgXMDxuTufUWvirBr4/d6bOgoKssxIESDcQLOK', 'imges/default-profile.jpg', '2025-01-09 15:44:24', 'admin'),
(3, 'admin', '', '$2y$10$lUBvKJ8wb4ObIol.HmrY9OPJpmXNle7z9azfIQV.X7a6sdTmod/Ii', 'uploads/678c0caa85b5b.jpeg', '2025-01-18 16:02:36', 'admin'),
(4, 'ss', 'sss@gmail.com', '$2y$10$246.28.5P.CIKWR1tfV6ze/S5kaoI0F6b5v8kqQVw4F7KxLn2U9Zm', 'uploads/678c0caa85b5b.jpeg', '2025-01-18 20:18:50', 'user'),
(5, 'not', 'not@not.n', '$2y$10$9h04hHxMSemRnDkfwzrEPeb8xlALzJDWy2XS51tENSZk3brw/dWbG', 'imges/default-profile.jpg', '2025-01-19 22:55:49', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_bookings_users` (`user_id`),
  ADD KEY `fk_bookings_flights` (`flight_id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`flight_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `flight_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_flights` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
