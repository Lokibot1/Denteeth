-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2024 at 02:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dental`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_additional_service_types`
--

CREATE TABLE `tbl_additional_service_types` (
  `id` int(11) NOT NULL,
  `additional_service_type_1` int(11) DEFAULT NULL,
  `additional_service_type_2` int(11) DEFAULT NULL,
  `additional_service_type_3` int(11) DEFAULT NULL,
  `additional_service_type_4` int(11) DEFAULT NULL,
  `additional_service_type_5` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_appointments`
--

CREATE TABLE `tbl_appointments` (
  `id` int(11) NOT NULL,
  `name` int(255) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_time` time DEFAULT NULL,
  `modified_by` int(3) DEFAULT NULL,
  `service_type` int(11) NOT NULL,
  `status` int(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_appointments`
--

INSERT INTO `tbl_appointments` (`id`, `name`, `contact`, `date`, `time`, `modified_date`, `modified_time`, `modified_by`, `service_type`, `status`) VALUES
(22, 22, '09123435467', '2024-11-21', '17:54:00', '2024-11-23', '10:49:00', 1, 7, 3),
(27, 27, '09123456789', '2024-11-19', '17:26:00', '2024-11-21', '10:51:00', 1, 9, 3),
(35, 35, '21474836471', '2024-11-19', '11:37:00', '2024-11-19', '10:30:00', NULL, 10, 3),
(36, 36, '2147483647', '2024-11-18', '12:39:00', NULL, NULL, NULL, 7, 3),
(37, 36, '09123456789', '2024-11-16', '23:51:50', NULL, NULL, 1, 10, 3),
(38, 26, '09123456789', '2024-11-16', '10:57:00', '2024-11-05', '14:36:00', 1, 10, 3),
(39, 39, '09123456789', '2024-11-02', '11:27:00', NULL, NULL, NULL, 8, 3),
(41, 41, '09123456789', '2024-11-02', '11:29:00', NULL, NULL, 1, 11, 3),
(42, 42, '09123456789', '2024-11-21', '16:21:00', '2024-11-25', '12:06:00', 1, 9, 3),
(43, 43, '09123456789', '2024-11-05', '14:28:00', NULL, NULL, 1, 9, 3),
(44, 44, '09123456789', '2024-11-08', '15:47:00', NULL, NULL, 1, 1, 3),
(45, 45, '09123456789', '2024-11-08', '17:28:00', NULL, NULL, 1, 1, 3),
(46, 36, '09123456789', '2024-11-08', '16:51:00', NULL, NULL, 1, 1, 3),
(47, 26, '09123456789', '2024-11-08', '11:51:00', NULL, NULL, 1, 1, 3),
(48, 48, '09123456789', '2024-11-08', '16:28:00', NULL, NULL, 1, 1, 3),
(49, 49, '09123456789', '2024-11-14', '17:30:00', NULL, NULL, 1, 1, 3),
(50, 50, '09142536789', '2024-11-08', '17:27:00', NULL, NULL, 1, 10, 3),
(51, 51, '09123456789', '2024-11-14', '11:47:00', NULL, NULL, 1, 1, 3),
(52, 52, '09126126268', '2024-11-13', '16:48:00', NULL, NULL, 1, 2, 3),
(53, 53, '09125116981', '2024-11-14', '12:52:00', NULL, NULL, 1, 3, 3),
(54, 54, '09916116516', '2024-11-14', '11:54:00', NULL, NULL, 1, 4, 3),
(55, 55, '03628191991', '2024-11-14', '17:56:00', NULL, NULL, 1, 5, 3),
(56, 56, '09510556161', '2024-11-13', '12:00:00', NULL, NULL, 1, 6, 3),
(57, 57, '09165145641', '2024-11-24', '12:03:00', '2024-11-24', '03:00:00', 1, 10, 2),
(58, 58, '09156616516', '2024-11-25', '12:07:00', NULL, NULL, 1, 8, 2),
(59, 59, '09516165113', '2024-11-11', '09:09:00', NULL, NULL, 1, 9, 3),
(60, 60, '06152312323', '2024-11-12', '12:12:00', NULL, NULL, 1, 10, 3),
(61, 61, '09543532161', '2024-11-12', '09:18:00', NULL, NULL, 1, 11, 3),
(62, 62, '09543532161', '2024-11-12', '09:18:00', NULL, NULL, 1, 11, 3),
(63, 63, '05996513232', '2024-11-12', '12:17:00', NULL, NULL, 1, 10, 3),
(64, 64, '20651516321', '2024-11-08', '09:30:00', NULL, NULL, 1, 10, 3),
(65, 65, '16513213212', '2024-11-08', '09:34:00', NULL, NULL, 1, 9, 3),
(66, 66, '09516213321', '2024-11-09', '15:17:00', NULL, NULL, NULL, 7, 3),
(67, 67, '09213333123', '2024-11-09', '17:30:00', NULL, NULL, NULL, 5, 3),
(68, 68, '09468733895', '2024-11-09', '17:30:00', NULL, NULL, NULL, 5, 3),
(69, 69, '09111111111', '2024-11-09', '17:59:00', NULL, NULL, NULL, 5, 3),
(71, 71, '09123456789', '2024-11-12', '14:00:00', NULL, NULL, NULL, 7, 3),
(72, 72, '12137176381', '2024-11-14', '17:57:00', NULL, NULL, NULL, 5, 3),
(73, 73, '18973187917', '2024-11-15', '11:59:00', '2024-11-15', '18:00:00', NULL, 10, 3),
(74, 74, '09222222222', '2024-11-09', '10:10:00', NULL, NULL, NULL, 10, 3),
(75, 75, '21319723731', '2024-11-10', '17:02:00', NULL, NULL, NULL, 4, 3),
(76, 76, '09111111111', '2024-11-11', '10:15:00', NULL, NULL, NULL, 5, 3),
(77, 77, '09111111111', '2024-11-09', '09:30:00', NULL, NULL, NULL, 5, 3),
(86, 86, '11111111111', '2024-11-19', '09:00:00', NULL, NULL, NULL, 10, 2),
(87, 87, '11111111111', '2024-11-20', '09:00:00', '2024-11-20', '04:30:00', 1, 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_archives`
--

CREATE TABLE `tbl_archives` (
  `id` int(11) NOT NULL,
  `name` int(255) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_time` time DEFAULT NULL,
  `modified_by` int(3) DEFAULT NULL,
  `service_type` int(11) NOT NULL,
  `additional_service_type` int(11) DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `status` int(4) DEFAULT 4,
  `price` decimal(10,2) NOT NULL,
  `completion` enum('1','2','3') NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_archives`
--

INSERT INTO `tbl_archives` (`id`, `name`, `contact`, `date`, `time`, `modified_date`, `modified_time`, `modified_by`, `service_type`, `additional_service_type`, `recommendation`, `status`, `price`, `completion`) VALUES
(23, 23, '2147483647', '2024-11-01', '17:54:00', '0000-00-00', '00:00:00', NULL, 10, NULL, NULL, 2, 0.00, '1'),
(24, 24, '2147483647', '2024-11-02', '17:57:00', '0000-00-00', '00:00:00', NULL, 9, NULL, NULL, 2, 0.00, '2'),
(31, 31, '1234567890', '2024-11-01', '15:36:00', '0000-00-00', '00:00:00', NULL, 8, NULL, NULL, 2, 0.00, '1'),
(34, 34, '1234567890', '2024-11-01', '10:51:00', '0000-00-00', '00:00:00', NULL, 7, NULL, NULL, 2, 0.00, '1'),
(71, 67, '09213333123', '2024-11-09', '17:30:00', '0000-00-00', '00:00:00', NULL, 5, NULL, 'sss', 1, 20000.00, '1'),
(72, 68, '09468733895', '2024-11-09', '17:30:00', '0000-00-00', '00:00:00', NULL, 5, NULL, 'ssss', 1, 20000.00, '1'),
(73, 69, '09111111111', '2024-11-09', '17:59:00', '0000-00-00', '00:00:00', NULL, 5, NULL, 'ssss', 1, 20000.00, '1'),
(74, 72, '12137176381', '2024-11-14', '17:57:00', '0000-00-00', '00:00:00', NULL, 5, NULL, 'sssss', 1, 20000.00, '1'),
(75, 73, '18973187917', '2024-11-15', '11:59:00', '2024-11-15', '18:00:00', NULL, 10, NULL, 'ss', 1, 40000.00, '1'),
(76, 58, '09156616516', '2024-11-25', '12:07:00', '0000-00-00', '00:00:00', NULL, 8, NULL, 'ss', 1, 2000.00, '1'),
(77, 58, '09156616516', '2024-11-25', '12:07:00', '0000-00-00', '00:00:00', NULL, 8, NULL, 'ssss', 4, 2000.00, '1'),
(78, 57, '09165145641', '2024-11-24', '12:03:00', '2024-11-24', '03:00:00', NULL, 10, NULL, 'ssss', 4, 40000.00, '1');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bin`
--

CREATE TABLE `tbl_bin` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_time` time DEFAULT NULL,
  `modified_by` int(3) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_bin`
--

INSERT INTO `tbl_bin` (`id`, `name`, `contact`, `date`, `time`, `modified_date`, `modified_time`, `modified_by`, `service_type`, `status`, `deleted_at`) VALUES
(78, '78', '09912312313', '2024-11-19', '11:10:00', '0000-00-00', '00:00:00', NULL, '11', '2', '2024-11-19 11:29:56'),
(79, '79', '12312312312', '2024-11-19', '11:07:00', '0000-00-00', '00:00:00', NULL, '10', '2', '2024-11-19 11:29:57'),
(80, '80', '22323232323', '2024-11-19', '11:08:00', '0000-00-00', '00:00:00', NULL, '9', '2', '2024-11-19 11:29:56'),
(81, '81', '12131232323', '2024-11-19', '11:09:00', '0000-00-00', '00:00:00', NULL, '9', '2', '2024-11-19 11:29:56'),
(82, '82', '12313131111', '2024-11-19', '11:14:00', '0000-00-00', '00:00:00', NULL, '9', '2', '2024-11-19 11:29:55'),
(83, '83', '11111111111', '2024-11-19', '11:10:00', '0000-00-00', '00:00:00', NULL, '10', '2', '2024-11-19 11:29:56'),
(84, '84', '11111111111', '2024-11-19', '09:38:00', '0000-00-00', '00:00:00', NULL, '11', '2', '2024-11-19 12:11:52'),
(85, '85', '11111111111', '2024-11-19', '15:53:00', '0000-00-00', '00:00:00', NULL, '3', '2', '2024-11-19 12:11:51');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_patient`
--

CREATE TABLE `tbl_patient` (
  `id` int(11) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `middle_name` varchar(2) DEFAULT NULL,
  `last_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_patient`
--

INSERT INTO `tbl_patient` (`id`, `first_name`, `middle_name`, `last_name`) VALUES
(22, 'Brian', 'G.', 'Dijamco'),
(23, 'Julia', 'H.', 'Salgado'),
(24, 'Allyah', 'M.', 'Laroa'),
(25, 'Sarah', 'S.', 'Pedillaga'),
(26, 'Brent Kian', 'M.', 'Rasonabe '),
(27, 'Justin', 'A.', 'Reyes'),
(28, 'Justin', 'T.', 'Reyes'),
(29, 'Justin', 'T.', 'Reyes'),
(30, 'Justin', 'Z.', 'Reyes'),
(31, 'Justin', 'A.', 'Reyes'),
(32, 'Justin', 'T.', 'Reyes'),
(33, 'Justin', 'T.', 'Reyes'),
(34, 'Justin', 'T.', 'Reyes'),
(35, 'Justin', 'T.', 'Reyes'),
(36, 'Justin', 'T.', 'Reyes'),
(37, 'Justinee', 'T.', 'Reyes'),
(38, 'Justin', 'Z.', 'Reyes'),
(39, 'JayR', 'T.', 'Reyes'),
(40, 'J', 'Z.', 'T'),
(41, 'Tin', 'T.', 'Reyes'),
(42, 'Justin', 'T.', 'Reyes'),
(43, 'GG', 'G.', 'HAD'),
(44, 'Justin', 'T.', 'Reyes'),
(45, 'Dijamco', 'A.', 'Brian '),
(46, 'g', 'g', 'g'),
(47, 'g', 'g', 'g'),
(48, 'ss', 'ss', 'ss'),
(49, 'ss', 'ss', 'ss'),
(50, 'CD', 'EF', 'AB'),
(51, 'a', 'a', 'a'),
(52, 'b', 'b', 'b'),
(53, 'c', 'c', 'c'),
(54, 'd', 'd', 'd'),
(55, 'e', 'e', 'e'),
(56, 'f', 'f', 'f'),
(57, 'g', 'g', 'g'),
(58, 'h', 'h', 'h'),
(59, 'i', 'i', 'i'),
(60, 'j', 'j', 'j'),
(61, 'k', 'k', 'k'),
(62, 'k', 'k', 'k'),
(63, 'f', 'f', 'f'),
(64, 'aa', 'aa', 'aa'),
(65, 'aa', 'aa', 'aa'),
(66, 'aaa', 'aa', 'aa'),
(67, 'Jessner', 'I', 'Montero'),
(68, 'jaja', 'H', 'salgado'),
(69, 'Louise', 'C', 'Moreno'),
(70, 'luisito', 'I', 'soriano'),
(71, 'Juliana Rox', 'I.', 'Laurencio'),
(72, 'Naruto', 'N.', 'Uzumaki'),
(73, 'Ipos', 'M.', 'Makanochi'),
(74, 'Lyra', 'NA', 'Genabe'),
(75, 'Clyvix', 'x', 'Eskalabermberm'),
(76, 'test', 'a', 'admin'),
(77, 'kian', 'a', 'admin'),
(78, 'sss', 'ss', 'ss'),
(79, 'sss', 'ss', 'ssss'),
(80, 'ssss', 'ss', 'ssss'),
(81, 'sss', 'ss', 'sss'),
(82, 'sssssss', 'ss', 'ssssssss'),
(83, 'sssss', 'ss', 'ss'),
(84, 'sssssssssssssssssssssss', 'ss', 'sdsssssssssss'),
(85, 'ss', 'ss', 'ss'),
(86, 'ss', 'ss', 'ss'),
(87, 'sss', 'ss', 'ss');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_role`
--

CREATE TABLE `tbl_role` (
  `id` int(11) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_role`
--

INSERT INTO `tbl_role` (`id`, `role`) VALUES
(1, 'admin'),
(2, 'doctor'),
(3, 'dental_assistant');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_services`
--

CREATE TABLE `tbl_services` (
  `id` int(11) NOT NULL,
  `service_image` varchar(255) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_description` text NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_services`
--

INSERT INTO `tbl_services` (`id`, `service_image`, `service_name`, `service_description`, `price`) VALUES
(21, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/att.kzztlIPm6WQjw49NU43LQ-FjkW7fVT7Fh9Avo57z1NA.jpg', 'All Porcelain Veneers & Zirconia', 'Dental veneers are custom-made shells that fit over the front surfaces of your teeth. They conceal cracks, chips, stains and other cosmetic imperfections.', 10000.00),
(22, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/att.vF9Xup2c0HmPNW9y711D11AaSEl7GwDfYo5xBzNH9EQ.jpg', 'Crown & Bridge', 'This is a custom-made solution to restore damaged or missing teeth. Crowns cover a weakened tooth, strengthen, and keep the natural appearance of the teeth. Bridges completely replace one or more teeth by anchoring the adjacent tooth and filling gaps.', 0.00),
(23, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/before-and-after-photo-of-dentist-cleaning-teeth.jpg', 'Dental Cleaning', 'Professional cleaning that removes plaques, tartar, stains that are stuck in your teeth and gums, and polishing to prevent cavities, gum disease, and bad breath. Giving you a cleaner, brighter and healthier smile', 0.00),
(24, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/att.MmQNc0krmGLGMoirJ-Rphh5KnSl_luV-qCJi2mE6igQ.jpg', 'Dental Implants', 'Permanent solution for missing teeth. This procedure includes putting a titanium post in your jawbone that acts as a strong foundation for a natural looking crown.', 0.00),
(25, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/whiteing-blog-pic.jpg', 'Dental Whitening', 'Removing stains caused by coffee, wine, tea or aging. This procedure can lighten your teeth giving you a brighter smile.', 0.00),
(26, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/depositphotos_595237212-stock-photo-pictures-dental-implants-press-ceramic.jpg', 'Dentures', 'Dentures are removable oral appliances that replace missing teeth. They help restore oral health and function so you can chew and speak more easily.', 500.00),
(27, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/DentalSurgicalExtraction.jpg', 'Extraction', 'Safe removal of badly decaying, infected teeth or overcrowding.', 0.00),
(28, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/att.f7PTUiLO7KKupv17ibJLse6-PUYbOV9IHy1_JfskSCg.jpg', 'Full Exam & X-Ray', 'Gives a full check-up of your teeth and gums and X-ray helps to spot hidden problems ensuring early diagnosis and prevention for healthier teeth.', 0.00),
(29, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/att.eFo73D7w5khpFqds095oE_STPx-qtnvZMu9w0Kvk0vI.jpg', 'Orthodontic Braces', 'Straighten misaligned teeth and correct bite issues by using wires, brackets, and braces to slowly shift your teeth into the ideal position. This service is customized depending on the patient\'s issue.', 500.00),
(30, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/mercury-free-restorations.jpg', 'Restoration', 'Repairing damaged and decaying teeth to restore their functions and appearance. This gives your teeth protection from further damage.', 0.00),
(31, 'C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/Root+canal+radiograp+x-rays+showing+molar+teeth+before+and+after+treatment.jpg', 'Root Canal Treatment', 'Removal of decaying or badly infected tooth, clean the inside of the tooth, and seal it to prevent further damage. This treatment relieves pain, saves your natural tooth, and restores normal function of the tooth.', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_service_type`
--

CREATE TABLE `tbl_service_type` (
  `id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_service_type`
--

INSERT INTO `tbl_service_type` (`id`, `service_type`, `price`) VALUES
(1, 'All Porcelain Veneers & Zirconia', 30000.00),
(2, 'Crown & Bridge', 30000.00),
(3, 'Dental Cleaning', 2000.00),
(4, 'Dental Implants', 100000.00),
(5, 'Dental Whitening', 20000.00),
(6, 'Dentures', 30000.00),
(7, 'Extraction', 1500.00),
(8, 'Full Exam & X-Ray', 2000.00),
(9, 'Orthodontic Braces', 280000.00),
(10, 'Restoration', 40000.00),
(11, 'Root Canal Treatment', 40000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_status`
--

CREATE TABLE `tbl_status` (
  `id` int(11) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_status`
--

INSERT INTO `tbl_status` (`id`, `status`) VALUES
(1, 'pending'),
(2, 'declined'),
(3, 'approved'),
(4, 'finished');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transaction_history`
--

CREATE TABLE `tbl_transaction_history` (
  `id` int(11) NOT NULL,
  `name` int(255) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `service_type` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `bill` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `outstanding_balance` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_transaction_history`
--

INSERT INTO `tbl_transaction_history` (`id`, `name`, `contact`, `service_type`, `date`, `time`, `bill`, `change_amount`, `outstanding_balance`) VALUES
(71, 26, '11111111111', 5, '2024-11-14', '09:43:00', 11.00, 11.00, 11.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `username`, `password`, `role`) VALUES
(13, 'doctor', '$2y$10$fON8fGAXO/XSQmP0u1fK4eArKL1/UIAq8lG4Yq1zqORFCXm/wNeO2', 2),
(14, 'admin', '$2y$10$3nObhg0eCoRN9wrwdn75AOI/pnfnG9hZFCEJYZ1QP.5VpTimUwcFu', 1),
(15, 'dental', '$2y$10$kFqaObkRUetj00kIS.r.huQ2R06JYKse3gz0IAl/bThA.pb5OJvUK', 3),
(17, 'ss', '$2y$10$SbMfVs7W94Us6R7pMMKG5O2OllJ2K5qyzyVsVSHnB7LpT0fXpwjvK', 2),
(18, 'ssss', '$2y$10$Ztw2s2AZad8kJI/UeLIAvu7fBckrSwfJJv2biXUHGYs/Ld/7FKqty', 1),
(19, 'sssss', '$2y$10$0lfrOAMxKkv22iNCzaGn5up6kVXG86kwUTRYgURp3PdJa5Pv7SNo.', 3),
(20, 'ssssssss', '$2y$10$elk8cx/rJRhG7mz96bunauDceZmrJ8Rl.7QLuuqQVZRLcfOe8qCrK', 0),
(21, 'sss', '$2y$10$wzVGor8lgRr8sG46sh/pUeyAEeBwHK6tYNaSWzW9m.m0gGKk99qXm', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_additional_service_types`
--
ALTER TABLE `tbl_additional_service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_appointments`
--
ALTER TABLE `tbl_appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_archives`
--
ALTER TABLE `tbl_archives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_bin`
--
ALTER TABLE `tbl_bin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_patient`
--
ALTER TABLE `tbl_patient`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_role`
--
ALTER TABLE `tbl_role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_services`
--
ALTER TABLE `tbl_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_service_type`
--
ALTER TABLE `tbl_service_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_status`
--
ALTER TABLE `tbl_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_transaction_history`
--
ALTER TABLE `tbl_transaction_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_additional_service_types`
--
ALTER TABLE `tbl_additional_service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_appointments`
--
ALTER TABLE `tbl_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `tbl_archives`
--
ALTER TABLE `tbl_archives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `tbl_bin`
--
ALTER TABLE `tbl_bin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `tbl_patient`
--
ALTER TABLE `tbl_patient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `tbl_role`
--
ALTER TABLE `tbl_role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_services`
--
ALTER TABLE `tbl_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tbl_service_type`
--
ALTER TABLE `tbl_service_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_status`
--
ALTER TABLE `tbl_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_transaction_history`
--
ALTER TABLE `tbl_transaction_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
