-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2025 at 07:24 PM
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
(44, 2, 2, 'A2', 'economy', 200.00, 'confirmed', '2025-02-03 18:08:27');

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
(1, 'New York', 'London', '2025-04-03 08:30:00', '2025-04-03 20:45:00', 850.00),
(2, 'London', 'Paris', '2025-04-03 22:15:00', '2025-04-03 23:45:00', 200.00),
(3, 'Paris', 'Dubai', '2025-04-04 01:30:00', '2025-04-04 09:45:00', 750.00),
(4, 'Dubai', 'Singapore', '2025-04-04 11:20:00', '2025-04-04 23:30:00', 680.00),
(5, 'Singapore', 'Tokyo', '2025-04-05 01:15:00', '2025-04-05 09:30:00', 520.00),
(6, 'Tokyo', 'Sydney', '2025-04-05 11:45:00', '2025-04-05 23:15:00', 890.00),
(7, 'Sydney', 'Los Angeles', '2025-04-06 01:30:00', '2025-04-06 20:45:00', 1200.00),
(8, 'Los Angeles', 'Chicago', '2025-04-06 22:30:00', '2025-04-07 04:45:00', 350.00),
(9, 'Chicago', 'Miami', '2025-04-07 06:15:00', '2025-04-07 10:30:00', 280.00),
(10, 'Miami', 'New York', '2025-04-07 12:00:00', '2025-04-07 15:15:00', 220.00),
(11, 'New York', 'Toronto', '2025-04-07 17:30:00', '2025-04-07 19:00:00', 180.00),
(12, 'Toronto', 'Vancouver', '2025-04-07 21:15:00', '2025-04-08 00:45:00', 420.00),
(13, 'Vancouver', 'San Francisco', '2025-04-08 02:30:00', '2025-04-08 05:15:00', 290.00),
(14, 'San Francisco', 'Mexico City', '2025-04-08 07:00:00', '2025-04-08 13:30:00', 460.00),
(15, 'Mexico City', 'Sao Paulo', '2025-04-08 15:45:00', '2025-04-09 04:30:00', 890.00),
(16, 'Sao Paulo', 'Rio de Janeiro', '2025-04-09 06:15:00', '2025-04-09 07:30:00', 150.00),
(17, 'Rio de Janeiro', 'Madrid', '2025-04-09 09:45:00', '2025-04-09 22:30:00', 920.00),
(18, 'Madrid', 'Rome', '2025-04-10 00:15:00', '2025-04-10 02:45:00', 180.00),
(19, 'Rome', 'Athens', '2025-04-10 04:30:00', '2025-04-10 06:00:00', 160.00),
(20, 'Athens', 'Istanbul', '2025-04-10 08:15:00', '2025-04-10 09:45:00', 140.00),
(21, 'Istanbul', 'Dubai', '2025-04-10 11:30:00', '2025-04-10 16:45:00', 380.00),
(22, 'Dubai', 'Mumbai', '2025-04-10 18:30:00', '2025-04-10 23:45:00', 420.00),
(23, 'Mumbai', 'Bangkok', '2025-04-11 01:30:00', '2025-04-11 07:45:00', 440.00),
(24, 'Bangkok', 'Hong Kong', '2025-04-11 09:30:00', '2025-04-11 13:45:00', 380.00),
(25, 'Hong Kong', 'Seoul', '2025-04-11 15:30:00', '2025-04-11 20:45:00', 360.00);

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
(1, 's1', 'ss1@gmail.com', '$2y$10$Zct5mHlPGRXWfPyPNE654.2GC8HZpMX6mounAfj17cUl.NnvcAKb2', 'imges/default-profile.jpg', '2025-01-09 13:29:57', 'user'),
(2, 'vision', 'vision@gmail.com', '$2y$10$4uCM.UseLnqRxlFLgXMDxuTufUWvirBr4/d6bOgoKssxIESDcQLOK', 'uploads/67a0b08c6ca51.png', '2025-01-09 15:44:24', 'user'),
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
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `flight_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
