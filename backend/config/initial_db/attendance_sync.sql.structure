-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Apr 25, 2023 at 11:07 AM
-- Server version: 10.6.12-MariaDB-1:10.6.12+maria~ubu2004
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sync`
--

CREATE TABLE `attendance_sync` (
  `tablename` varchar(32) NOT NULL,
  `lastsync` datetime NOT NULL,
  `regular` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_sync`
--

INSERT INTO `attendance_sync` (`tablename`, `lastsync`, `regular`) VALUES
('students', NOW(), 1);
--('family', NOW(), 0),
--('family_fetcher', NOW(), 1),
--('remoteerror', NOW(), 0),
--('students', NOW(), 1),
--('students_photo', NOW(), 1),
--('names_departments', NOW(), 1),
--('names_levels', NOW(), 1),
--('names_sections', NOW(), 1),
--('names_strands', NOW(), 1),
--('setup_classes', NOW(), 1),
--('setup_periods', NOW(), 1),
--('setup_schoolyears', NOW(), 1),
--('setup_student_sections', NOW(), 1),
--('setup_student_elective_sections', NOW(), 1),
--('setup_student_subject_sections', NOW(), 1),
--('setup_subjects', NOW(), 1),
--('setup_subjects_electives', NOW(), 1),
--('setup_subjects_interlevel', NOW(), 1),
--('setup_subjects_strands2', NOW(), 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_sync`
--
ALTER TABLE `attendance_sync`
  ADD UNIQUE KEY `tablename` (`tablename`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
