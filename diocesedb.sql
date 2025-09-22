-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 11:53 AM
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
-- Database: `diocesedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_superadmin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `full_name`, `created_at`, `is_superadmin`) VALUES
(1, 'superadmin', 'admin123', 'Super Admin', '2025-04-03 06:36:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `parish_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_by` enum('admin','parish') NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `parish_id`, `image_path`, `caption`, `uploaded_by`, `uploader_id`, `uploaded_at`) VALUES
(1, 5, 'gallery/gallery_67eebb987a7679.95788131.jpg', 'PRIESTS', 'admin', 1, '2025-04-03 16:47:20'),
(2, 1, 'gallery/gallery_67eec479695e12.29121604.jpg', 'priest', 'parish', 1, '2025-04-03 17:25:13'),
(5, NULL, 'gallery/gallery_67f6a2bb10bed1.24169498.png', 'ANNIVERSARY', 'admin', 1, '2025-04-09 16:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `parish_id` int(11) NOT NULL,
  `title` varchar(10) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `other_names` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `baptism_date` date DEFAULT NULL,
  `baptism_place` varchar(100) DEFAULT NULL,
  `nlb` varchar(50) DEFAULT NULL,
  `is_communicant` tinyint(1) DEFAULT 0,
  `confirmation_date` date DEFAULT NULL,
  `confirmation_place` varchar(100) DEFAULT NULL,
  `nlc` varchar(50) DEFAULT NULL,
  `confraternity` varchar(100) DEFAULT NULL,
  `confraternity_position` varchar(100) DEFAULT NULL,
  `marital_status` enum('Single','Married','Other') DEFAULT NULL,
  `children_count` int(11) DEFAULT 0,
  `occupation` varchar(100) DEFAULT NULL,
  `is_welfare_member` tinyint(1) DEFAULT 0,
  `relative_name` varchar(100) DEFAULT NULL,
  `relative_contact` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `christian_community` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mission_help` decimal(10,2) DEFAULT 0.00,
  `special_contribution` decimal(10,2) DEFAULT 0.00,
  `mother_parish` varchar(255) NOT NULL,
  `outstation` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `parish_id`, `title`, `image_path`, `last_name`, `other_names`, `dob`, `baptism_date`, `baptism_place`, `nlb`, `is_communicant`, `confirmation_date`, `confirmation_place`, `nlc`, `confraternity`, `confraternity_position`, `marital_status`, `children_count`, `occupation`, `is_welfare_member`, `relative_name`, `relative_contact`, `address`, `christian_community`, `contact`, `created_at`, `mission_help`, `special_contribution`, `mother_parish`, `outstation`) VALUES
(7, 1, 'Mr', '', 'GEFU', 'ROMAN', '2025-04-15', '2025-03-31', 'TEGBI', '47745', 1, '2025-03-30', 'KETA', '74454', '', '', 'Single', 0, '0', 0, '', '', '', '', '0240443965', '2025-04-04 10:50:40', 0.00, 0.00, '', ''),
(8, 1, 'Miss', '', 'DAVOR', 'STELLA', '2025-04-30', '2025-03-30', 'AFLAO', '7447', 1, NULL, '', '', '', '', '', 0, '0', 0, '', '', '', '', '544', '2025-04-04 10:51:37', 0.00, 0.00, '', ''),
(9, 1, 'Mr', 'members/member_67efba0193bc52.26179940.jpg', 'DOTSEY', 'SELORM', '2025-04-24', '2025-04-07', '', '', 0, NULL, '', '', '', '', '', 0, '0', 0, '', '', '', '', '544', '2025-04-04 10:52:49', 0.00, 0.00, '', ''),
(10, 2, '', 'members/member_67f40ddf269774.49395789.jpg', 'DAVOR', 'MICHAEL', '1986-05-08', '2021-05-21', 'TEGBI', '7447', 0, NULL, '', '', '', '', 'Married', 2, '0', 1, 'DAVOR JUDAH', '0245436133', 'TE1/10058, TEGBI AGBEDRAFOR', 'ST MARY', '0546401633', '2025-04-07 17:39:43', 0.00, 0.00, '', ''),
(11, 2, 'Miss', 'members/member_67f40e95c7d918.90670566.jpeg', 'GEFU', 'STELLA', '2000-02-05', '2024-12-31', 'TEGBI', '7887544', 1, '2000-01-07', 'KETA', '575545', 'KNIGHT OF ST, JOHN', 'MEMBER', 'Single', 0, '0', 1, 'GEFU PROSPER', '0245436133', 'TE1/1008', 'ST MARY', '057875422', '2025-04-07 17:42:45', 0.00, 0.00, '', ''),
(12, 2, 'Mrs', 'members/member_67f4dca50ee7b5.18529868.jpeg', 'SETSOAFIA', 'JUDE', '1998-10-06', '2025-04-16', 'AFLAO', '477454', 1, '2025-04-08', 'KETA', '322322', 'KNIGHT OF ST, JOHN', 'MEMBER', 'Single', 0, '0', 1, 'GEFU PROSPER', '0245436133', 'RTFFV/RFR', 'ST MARY', '024578568', '2025-04-08 08:21:57', 0.00, 0.00, '', ''),
(14, 7, 'Mr', '', 'GEFU', 'SELORM', '2025-04-08', NULL, 'TEGBI', '477454', 0, NULL, '', '', '', '', 'Single', 0, '0', 0, '', '', '', '', '0240443965', '2025-04-11 14:20:56', 0.00, 0.00, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `parishes`
--

CREATE TABLE `parishes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `deanery` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parishes`
--

INSERT INTO `parishes` (`id`, `name`, `username`, `password`, `deanery`, `address`, `created_at`) VALUES
(1, 'St. Michael Parish', 'st_michael', 'parish123', 'Keta Deanery', NULL, '2025-04-03 06:36:31'),
(2, 'Our Lady of Lourdes', 'our_lady', 'lady', 'Keta Deanery', '<br />\r\n<b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\xamp\\htdocs\\ketakatsi-diocese\\admin\\actions\\parish-crud.php</b> on line <b>330</b><br />', '2025-04-03 06:36:31'),
(3, 'Sacred Heart Parish', 'sacred_heart', 'parish123', 'Akatsi Deanery', NULL, '2025-04-03 06:36:31'),
(4, 'St. Anthony Parish', 'st_anthony', 'parish123', 'Akatsi Deanery', NULL, '2025-04-03 06:36:31'),
(5, 'Christ the King Parish', 'christ_king', 'parish123', 'Keta Deanery', NULL, '2025-04-03 06:36:31'),
(6, 'St Cecelia', 'st_cecelia', 'parish123', 'Keta', 'fkkv', '2025-04-04 06:34:53'),
(7, 'St Prosper', 'St_prosper', 'prosper', 'Keta', 'vfv', '2025-04-06 13:36:25'),
(9, 'St Anthony of padua', 'padua', 'padua123', 'Keta Deanery', '2', '2025-04-07 07:54:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parish_id` (`parish_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parish_id` (`parish_id`);

--
-- Indexes for table `parishes`
--
ALTER TABLE `parishes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `parishes`
--
ALTER TABLE `parishes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`parish_id`) REFERENCES `parishes` (`id`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`parish_id`) REFERENCES `parishes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
