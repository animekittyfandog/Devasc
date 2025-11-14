-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 11:06 AM
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
-- Table structure for table `archive`
--

CREATE TABLE `archive` (
  `id` int(11) NOT NULL,
  `original_violation_id` int(11) NOT NULL,
  `violation_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `license_plate` varchar(20) NOT NULL,
  `violation_description` varchar(255) NOT NULL,
  `vehicle_status` enum('registered','unregistered') NOT NULL,
  `archive_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive`
--

INSERT INTO `archive` (`id`, `original_violation_id`, `violation_time`, `license_plate`, `violation_description`, `vehicle_status`, `archive_time`) VALUES
(31, 74, '2025-10-31 23:15:00', 'SJF-7601', 'Parked in Teacher', 'registered', '2025-11-14 08:38:16'),
(51, 86, '2025-01-18 03:22:00', 'HQA-8653', 'Parked in Student', 'unregistered', '2025-11-14 09:25:45'),
(58, 77, '2023-01-01 04:03:00', 'MNC-0146', 'Parked in Student', 'registered', '2025-11-14 09:25:54'),
(59, 66, '2025-04-06 04:26:00', 'GJD-7316', 'Parked in Teacher', 'registered', '2025-11-14 09:25:55'),
(60, 98, '2025-10-31 23:32:00', 'MPA-2846', 'Parked in Admin', 'registered', '2025-11-14 09:25:56'),
(61, 72, '2025-12-13 06:28:00', 'RQV-2308', 'Parked in Teacher', 'registered', '2025-11-14 09:25:59'),
(62, 97, '2025-12-24 04:03:00', 'UDB-3129', 'Blocking Gate', 'registered', '2025-11-14 09:26:00'),
(63, 90, '2023-01-01 05:58:00', 'TSB-4210', 'Parked in Student', 'registered', '2025-11-14 09:42:33'),
(69, 93, '2025-07-12 01:10:00', 'XUV-4703', 'No Parking Permit Displayed', 'registered', '2025-11-14 09:42:40'),
(72, 67, '2025-11-28 04:47:00', 'FTX-3198', 'Parked in Reserved Slot', 'registered', '2025-11-14 09:42:44'),
(74, 102, '2025-11-01 05:18:00', 'ZKM-5924', 'Parked in Admin', 'unregistered', '2025-11-14 09:42:50'),
(75, 83, '2025-11-10 05:09:07', 'BJX-7219', 'Parked in Student', 'unregistered', '2025-11-14 09:42:52'),
(76, 84, '2025-11-10 05:10:02', 'ABC-1234', 'Parked in Admin', 'unregistered', '2025-11-14 09:42:54'),
(77, 103, '2025-12-14 00:27:00', 'BTD-3187', 'Parked in Student', 'unregistered', '2025-11-14 09:42:55');

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
(20, 'OLT-1275', 'Honda', 'BR-V', '20email@gmail.com', '0999 843 2750', 'Teacher'),
(21, 'BDX-7284', 'Honda', 'Jazz', '21email@gmail.com', '0917 563 2841', 'Student'),
(22, 'KMG-4197', 'Toyota', 'Camry', '22email@gmail.com', '0908 372 5619', 'Student'),
(23, 'LZT-0842', 'Mazda', 'CX-5', '23email@gmail.com', '0951 637 8402', 'Student'),
(24, 'QAP-5739', 'Hyundai', 'Accent', '24email@gmail.com', '0998 214 6573', 'Student'),
(25, 'SRW-9825', 'Nissan', 'Terra', '25email@gmail.com', '0922 417 9536', 'Student'),
(26, 'JHU-2641', 'Suzuki', 'Swift', '26email@gmail.com', '0912 804 3765', 'Student'),
(27, 'MGV-8610', 'Ford', 'EcoSport', '27email@gmail.com', '0943 218 6750', 'Student'),
(28, 'ZCK-5073', 'Mitsubishi', 'L300', '28email@gmail.com', '0906 453 7824', 'Student'),
(29, 'FTX-3198', 'Toyota', 'Avanza', '29email@gmail.com', '0935 287 4106', 'Student'),
(30, 'PLD-6752', 'Chevrolet', 'Trailblazer', '30email@gmail.com', '0977 602 1985', 'Student'),
(31, 'RHB-8426', 'Honda', 'HR-V', '31email@gmail.com', '0918 754 9630', 'Teacher'),
(32, 'WQP-1954', 'Toyota', 'Yaris', '32email@gmail.com', '0909 341 5028', 'Teacher'),
(33, 'TNC-6807', 'Subaru', 'Forester', '33email@gmail.com', '0954 730 9661', 'Teacher'),
(34, 'VSM-4032', 'Isuzu', 'MU-X', '34email@gmail.com', '0921 864 3305', 'Teacher'),
(35, 'PYX-2590', 'Mitsubishi', 'Montero Sport', '35email@gmail.com', '0979 863 4012', 'Teacher'),
(36, 'GJD-7316', 'Kia', 'Seltos', '36email@gmail.com', '0914 762 5803', 'Teacher'),
(37, 'HAB-5549', 'Hyundai', 'Elantra', '37email@gmail.com', '0942 150 2973', 'Teacher'),
(38, 'XFT-1038', 'Ford', 'Explorer', '38email@gmail.com', '0991 573 8246', 'Teacher'),
(39, 'BLP-4670', 'Nissan', 'Livina', '39email@gmail.com', '0930 752 6094', 'Teacher'),
(40, 'KRT-9205', 'Toyota', 'RAV4', '40email@gmail.com', '0907 615 9342', 'Teacher'),
(41, 'MPA-2846', 'Honda', 'BR-V', '41email@gmail.com', '0917 350 2741', 'Student'),
(42, 'SJF-7601', 'Suzuki', 'Jimny', '42email@gmail.com', '0964 810 3257', 'Student'),
(43, 'UDB-3129', 'Mitsubishi', 'Strada', '43email@gmail.com', '0925 901 3468', 'Student'),
(44, 'LHG-9582', 'Toyota', 'Hiace', '44email@gmail.com', '0950 482 7136', 'Student'),
(45, 'QWX-6405', 'Honda', 'Civic RS', '45email@gmail.com', '0975 304 8931', 'Student'),
(46, 'RVL-8013', 'Hyundai', 'Venue', '46email@gmail.com', '0916 241 7538', 'Teacher'),
(47, 'ZTB-1294', 'Mazda', 'Mazda 3', '47email@gmail.com', '0928 573 0142', 'Teacher'),
(48, 'FGR-5558', 'Toyota', 'Corolla Altis', '48email@gmail.com', '0933 976 2541', 'Teacher'),
(49, 'NCP-3742', 'Isuzu', 'Crosswind', '49email@gmail.com', '0948 735 1049', 'Teacher'),
(50, 'YMK-8476', 'Ford', 'Fiesta', '50email@gmail.com', '0905 610 9837', 'Teacher');

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
(104, '2023-01-01 00:13:00', 'LBM-7154', 'Parked in Admin', 'registered'),
(105, '2023-01-01 02:08:00', 'HNG-9402', 'Parked in Admin', 'registered'),
(106, '2023-01-01 07:53:00', 'PZD-8957', 'Parked in Student', 'registered'),
(107, '2025-02-13 06:03:00', 'VSM-4032', 'No Sticker', 'registered'),
(108, '2025-03-26 03:05:00', 'SRW-9825', 'Parked in Handicap Only', 'registered'),
(109, '2025-03-23 05:09:07', 'KMG-4197', 'Parked in Admin', 'registered'),
(110, '2025-01-02 05:44:00', 'KRT-9205', 'Double Parking', 'registered'),
(111, '2025-04-11 05:02:00', 'HNG-9402', 'Parked in Admin', 'registered'),
(112, '2025-04-10 01:50:00', 'JKH-3369', 'Parked in Teacher', 'registered'),
(113, '2025-05-12 00:05:00', 'ZTB-1294', 'Blocking Driveway', 'registered'),
(114, '2025-09-17 02:14:00', 'YBR-5802', 'Occupying Two Slots', 'registered'),
(115, '2025-10-08 03:41:00', 'QWX-6405', 'Parked in No Parking Zone', 'registered'),
(116, '2025-10-10 02:28:00', 'LBM-7154', 'Parked in Admin', 'registered'),
(117, '2025-02-12 03:42:00', 'RXG-7405', 'Parked in Student', 'unregistered'),
(118, '2025-04-11 01:25:00', 'BTD-3187', 'Parked in Student', 'unregistered'),
(119, '2025-10-04 07:48:00', 'HQA-8653', 'Parked in Student', 'unregistered'),
(120, '2023-01-01 00:37:00', 'ZKM-5924', 'Parked in Admin', 'unregistered'),
(121, '2025-10-18 05:16:00', 'JLP-2096', 'Parked in Admin', 'unregistered'),
(122, '2025-11-01 01:33:00', 'RXG-7405', 'Parked in Fire Lane', 'unregistered');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `archive`
--
ALTER TABLE `archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `registered_vehicles`
--
ALTER TABLE `registered_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `violations`
--
ALTER TABLE `violations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
