-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2025 at 10:52 PM
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
(3, 2, 2, 'B3', 'economy', 200.00, 'confirmed', '2025-01-09 15:45:10'),
(4, 2, 2, 'A4', 'economy', 200.00, 'confirmed', '2025-01-18 14:56:54');

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
(1, 'New York', 'London', '2025-01-20 10:00:00', '2025-01-20 22:00:00', 1000.00),
(2, 'London', 'Paris', '2025-01-21 08:00:00', '2025-01-21 09:30:00', 200.00),
(3, 'Paris', 'Tokyo', '2025-01-22 12:00:00', '2025-01-23 06:00:00', 800.00),
(7, 'Jeddah', 'Alzaher', '2025-01-20 23:36:00', '2026-01-22 12:40:00', 9000.00);

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
(4, 'ss', 'sss@gmail.com', '$2y$10$246.28.5P.CIKWR1tfV6ze/S5kaoI0F6b5v8kqQVw4F7KxLn2U9Zm', 'uploads/678c0caa85b5b.jpeg', '2025-01-18 20:18:50', 'user');

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
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `flight_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
