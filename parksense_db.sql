-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 03:42 PM
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
-- Database: `parksense_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `registered_vehicles`
--

CREATE TABLE `registered_vehicles` (
  `id` int(11) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `car_brand` varchar(50) DEFAULT NULL,
  `car_model` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `sticker_type` enum('Student','Teacher') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_vehicles`
--

INSERT INTO `registered_vehicles` (`id`, `license_plate`, `car_brand`, `car_model`, `email`, `contact_number`, `sticker_type`) VALUES
(1, 'DKH-4791', 'Toyota', 'Vios', '1email@gmail.com', '0917 482 3610', 'Student'),
(2, 'RQV-2308', 'Honda', 'Civic', '2email@gmail.com', '0995 238 7401', 'Student'),
(3, 'LBM-7154', 'Mitsubishi', 'Mirage G4', '3email@gmail.com', '0908 671 2945', 'Student'),
(4, 'NPY-6023', 'Toyota', 'Fortuner', '4email@gmail.com', '0961 553 0872', 'Student'),
(5, 'SJT-8810', 'Nissan', 'Almera', '5email@gmail.com', '0927 420 9183', 'Student'),
(6, 'FWC-3496', 'Hyundai', 'Tucson', '6email@gmail.com', '0915 834 2097', 'Student'),
(7, 'UZR-1567', 'Ford', 'Ranger', '7email@gmail.com', '0998 742 1604', 'Student'),
(8, 'HNG-9402', 'Toyota', 'Innova', '8email@gmail.com', '0906 235 7841', 'Student'),
(9, 'KPL-5079', 'Kia', 'Stonic', '9email@gmail.com', '0977 890 3256', 'Student'),
(10, 'VEX-3321', 'Suzuki', 'Ertiga', '10email@gmail.com', '0929 411 5780', 'Student'),
(11, 'QRM-6685', 'Toyota', 'Wigo', '11email@gmail.com', '0919 652 8473', 'Teacher'),
(12, 'TSB-4210', 'Isuzu', 'D-Max', '12email@gmail.com', '0945 371 2690', 'Teacher'),
(13, 'WLY-2538', 'Honda', 'City', '13email@gmail.com', '0991 820 5374', 'Teacher'),
(14, 'GFA-7894', 'Mitsubishi', 'Xpander', '14email@gmail.com', '0905 214 8796', 'Teacher'),
(15, 'MNC-0146', 'Toyota', 'Corolla Cross', '15email@gmail.com', '0936 408 7521', 'Teacher'),
(16, 'PZD-8957', 'Nissan', 'Navara', '16email@gmail.com', '0918 574 3062', 'Teacher'),
(17, 'XUV-4703', 'Hyundai', 'Stargazer', '17email@gmail.com', '0956 237 4908', 'Teacher'),
(18, 'JKH-3369', 'Ford', 'Everest', '18email@gmail.com', '0928 701 5439', 'Teacher'),
(19, 'YBR-5802', 'Toyota', 'Hilux', '19email@gmail.com', '0907 912 6584', 'Teacher'),
(20, 'OLT-1275', 'Honda', 'BR-V', '20email@gmail.com', '0999 843 2750', 'Teacher');

-- --------------------------------------------------------

--
-- Table structure for table `violations`
--

CREATE TABLE `violations` (
  `id` int(11) NOT NULL,
  `violation_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `license_plate` varchar(20) NOT NULL,
  `violation_description` varchar(255) NOT NULL,
  `vehicle_status` enum('registered','unregistered') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violations`
--

INSERT INTO `violations` (`id`, `violation_time`, `license_plate`, `violation_description`, `vehicle_status`) VALUES
(1, '2023-01-01 00:13:00', 'LBM-7154', 'Parked in Admin', 'registered'),
(2, '2023-01-01 02:08:00', 'HNG-9402', 'Parked in Admin', 'registered'),
(3, '2023-01-01 04:03:00', 'MNC-0146', 'Parked in Student', 'registered'),
(4, '2023-01-01 05:58:00', 'TSB-4210', 'Parked in Student', 'registered'),
(5, '2023-01-01 07:53:00', 'PZD-8957', 'Parked in Student', 'registered'),
(6, '2023-01-01 00:37:00', 'ZKM-5924', 'Parked in Admin', 'unregistered'),
(7, '2023-01-01 01:25:00', 'BTD-3187', 'Parked in Student', 'unregistered'),
(8, '2023-01-01 03:42:00', 'RXG-7405', 'Parked in Student', 'unregistered'),
(9, '2023-01-01 05:16:00', 'JLP-2096', 'Parked in Admin', 'unregistered'),
(10, '2023-01-01 07:48:00', 'HQA-8653', 'Parked in Student', 'unregistered');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registered_vehicles`
--
ALTER TABLE `registered_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`);

--
-- Indexes for table `violations`
--
ALTER TABLE `violations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registered_vehicles`
--
ALTER TABLE `registered_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `violations`
--
ALTER TABLE `violations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
