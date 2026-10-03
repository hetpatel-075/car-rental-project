-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2026 at 08:40 PM
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
-- Database: `car_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(8, 'admin', '$2y$10$tKtV13wbHpEgFyAf7mjhE.fcQxN.wBdIU1KI/6V0nMsEH9.Kcn8p2');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `booking_status` varchar(30) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `car_id`, `pickup_date`, `return_date`, `total_days`, `total_amount`, `booking_status`, `created_at`) VALUES
(1, 1, 1, '2026-09-05', '2026-09-07', 2, 3000.00, 'Confirmed', '2026-09-02 17:53:47'),
(2, 1, 6, '2026-09-10', '2026-09-11', 1, 5500.00, 'Cancelled', '2026-09-04 18:12:59'),
(3, 2, 6, '2026-09-10', '2026-09-11', 1, 5500.00, 'Confirmed', '2026-09-04 18:27:54'),
(4, 1, 7, '2026-09-19', '2026-09-20', 1, 2000.00, 'Confirmed', '2026-09-18 16:57:51'),
(5, 1, 2, '2026-09-22', '2026-09-24', 2, 5000.00, 'Cancelled', '2026-09-18 16:59:41');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `car_name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `car_type` varchar(50) DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `fuel_type` varchar(30) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `car_name`, `brand`, `model`, `car_type`, `price_per_day`, `fuel_type`, `seats`, `image`, `status`, `created_at`) VALUES
(1, 'Swift', 'Maruti', '2024', 'Hatchback', 1500.00, 'Petrol', 5, 'swift.jpg', 'Available', '2026-09-02 14:35:51'),
(2, 'Creta', 'Hyundai', '2024', 'SUV', 2500.00, 'Petrol', 5, 'creta.jpg', 'Available', '2026-09-02 14:35:51'),
(3, 'City', 'Honda', '2024', 'Sedan', 2200.00, 'Petrol', 5, 'city.jpg', 'Available', '2026-09-02 14:35:51'),
(5, 'Fortuner', 'Toyota', '2024', 'SUV', 4500.00, 'Diesel', 7, 'fortuner.jpg', 'Available', '2026-09-02 14:35:51'),
(6, 'm340i', 'BMW', '2023', 'sedan', 5500.00, 'petrol', 5, 'bmw.jpg', 'Available', '2026-09-03 05:23:16'),
(7, 'venue', 'hyundai', '2024', 'SUV', 2000.00, 'Petrol', 5, 'venue.jpg', 'Available', '2026-09-04 18:42:39'),
(8, 'Innova CRYSTA', 'toyota', '2023', 'MUV', 3000.00, 'Petrol', 7, 'innova.jpg', 'Available', '2026-09-18 17:15:27'),
(9, 'Virtus TURBO', 'volkswagen', '2025', 'Sedan', 2000.00, 'Petrol', 5, '1789752256_6aad73c03880b.jpg', 'Available', '2026-09-18 17:24:16');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password`, `created_at`) VALUES
(1, 'het patel', 'patelhet6527@gmail.com', '7990146599', '$2y$10$Tzk/sMp7fHB8fqHAXxiWG.Q2Fty/IdqoJ7V4EKSf729d0J99cY2N2', '2026-09-02 17:06:04'),
(2, 'jainil sahani', 'jainild@gmail.com', '8998766734', '$2y$10$P6B8G6LbT2LFGT44vYqTbuFVW81f.7.HC8DoQQG/jJmusFDZ8Pydq', '2026-09-04 18:16:53'),
(3, 'dhruv', 'dhruv153@gmail.com', '8990399219', '$2y$10$vxXufEuLsVANnPt0yRe6.OUwNPSYRyOtg6QhOZcly00p2u.FlHTqG', '2026-09-18 18:13:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
