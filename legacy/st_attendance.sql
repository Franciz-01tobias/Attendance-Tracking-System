-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2022 at 02:31 PM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `st_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `Attendace_id` int(11) NOT NULL,
  `Stud_id` int(11) NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Present',
  `Attendace_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `stud_id` int(11) NOT NULL,
  `student_name` varchar(25) NOT NULL,
  `email` varchar(25) NOT NULL,
  `dateofbirth` date NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`stud_id`, `student_name`, `email`, `dateofbirth`, `teacher_id`) VALUES
(1, 'Abuu', 'abuu@gmail.com', '2010-05-03', 1),
(2, 'abel', 'abel@gmail.com', '2022-06-08', 1),
(3, 'erick', 'erick@gmail.com', '2020-06-13', 1),
(4, 'riziki', 'riziki@gmail.com', '2022-06-14', 1),
(5, 'martius', 'martius@gmail.com', '2022-06-27', 1),
(6, 'Juma', 'juma@gmail.php', '2013-06-04', 2),
(7, 'jack', 'jack@gmail.com', '2000-03-01', 2),
(8, 'thailand', 'thailand@gmail.com', '1999-01-02', 2),
(9, 'wilson', 'wilson@gmail.com', '1998-01-05', 2),
(10, 'nice', 'nice@gmail.com', '2000-12-06', 2),
(11, 'mwinyi', 'mwinyi@gmail.com', '2000-12-03', 3),
(12, 'hassan', 'hassan@gmail.com', '1998-03-08', 3),
(13, 'winnie', 'winnie@gmail.com', '1998-05-07', 3),
(14, 'irene', 'irene@gmail.com', '1999-09-10', 3),
(15, 'rumion', 'rumion@gmail.com', '1997-06-10', 3),
(16, 'michael', 'michael@gmail.com', '2000-03-04', 4),
(17, 'yumbio', 'yumbio@gmail.com', '2000-08-10', 4),
(18, 'Francis', 'francis@gmail.com', '1995-12-05', 4),
(19, 'razack', 'razack@gmail.com', '2000-03-12', 4),
(20, 'winnie', 'winnie@gmail.com', '2022-06-14', 4);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `FirstName` varchar(25) NOT NULL,
  `LastName` varchar(25) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Class` varchar(25) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `FirstName`, `LastName`, `Email`, `Class`, `Password`) VALUES
(1, 'francis', 'thobias', 'francisallen9@gmail.com', '1', '$2y$10$/SnIKjotrF42qzvRMMjgL.u/EMhrwwHVAleg64Qhg1oRpTsX.1oYy'),
(2, 'luguda', 'charles', 'luguda@gmail.com', '2', '$2y$10$o0ncYi3Wf.YwZSGD4q7G8epma3bJ8/JJYjqE0KthO.hV.jjVW6Yye'),
(3, 'charles', 'chakupewa', 'chakupewa@gmail.com', '3', '$2y$10$JPIydHz3daHNSjUJCva8BesUJn9LkYzF9mTwvBiFnPscI6npzTl2S'),
(4, 'kimati', 'kimario', 'kimati@gmail.com', '4', '$2y$10$1lh0dtIkdAUefyje69.rc.QHlzB2p9/BDGSa8SEXCHpW4N02y2PUC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`Attendace_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`stud_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `Attendace_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `stud_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
