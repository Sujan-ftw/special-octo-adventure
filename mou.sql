-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 12:57 PM
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
-- Database: `mou`
--

-- --------------------------------------------------------

--
-- Table structure for table `mou_files`
--

CREATE TABLE `mou_files` (
  `id` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `year` varchar(10) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mou_files`
--

INSERT INTO `mou_files` (`id`, `department`, `year`, `filename`, `filepath`, `upload_date`) VALUES
(1, 'Department 1', '2020-21', '688b3eb3e1c12_AIT course.pdf', 'uploads/688b3eb3e1c12_AIT course.pdf', '2025-07-31 10:00:19'),
(2, 'Diploma Computer Engineering', '2021-22', '688c3c9727c74_dslabcourse.pdf', 'uploads/688c3c9727c74_dslabcourse.pdf', '2025-08-01 04:03:35'),
(3, 'Diploma Mechanical Engineering', '2022-23', '688c7b1f1c8e4_Internet of Things.pdf', 'uploads/688c7b1f1c8e4_Internet of Things.pdf', '2025-08-01 08:30:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mou_files`
--
ALTER TABLE `mou_files`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mou_files`
--
ALTER TABLE `mou_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
