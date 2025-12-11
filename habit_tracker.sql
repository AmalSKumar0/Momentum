-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2025 at 06:31 AM
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
-- Database: `habit_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `ActID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `HabitID` int(11) DEFAULT NULL,
  `isComplete` tinyint(1) DEFAULT 1,
  `completedDay` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity`
--

INSERT INTO `activity` (`ActID`, `UserID`, `HabitID`, `isComplete`, `completedDay`) VALUES
(26, 8, 24, 0, '2025-12-09'),
(28, 9, 26, 1, '2025-12-09');

-- --------------------------------------------------------

--
-- Table structure for table `customshop`
--

CREATE TABLE `customshop` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `xp_reward` int(11) DEFAULT 5,
  `gold_cost` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `xp_reward` int(11) DEFAULT 5,
  `gold_reward` int(11) DEFAULT 10,
  `clicked` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habits`
--

INSERT INTO `habits` (`id`, `user_id`, `title`, `difficulty`, `xp_reward`, `gold_reward`, `clicked`) VALUES
(5, 6, 'gym', 'easy', 3, 5, NULL),
(24, 8, 'help', 'easy', 3, 5, NULL),
(26, 9, 'Read 10 pages', 'easy', 3, 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `baught` date DEFAULT NULL,
  `Quantity` int(10) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE `shop` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `xp_reward` int(11) DEFAULT 5,
  `gold_cost` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(5) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(24) NOT NULL,
  `gold` int(5) NOT NULL DEFAULT 0,
  `xp` int(5) NOT NULL DEFAULT 100,
  `password` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `name`, `email`, `gold`, `xp`, `password`) VALUES
(6, 'amalskumar', 'amals@gmail.com', 219650, 240, '$2y$10$JU0dM4POlqnVIZUZlfTN2ud1W2xPrgjHFSeWaqyR35fQPy9QZIdlS'),
(7, 'amalskumar', 'amal@gmail.com', 0, 100, '$2y$10$MtukRSx6olC1Zcslnrf5/eyDmOg8bsVBhIVmdp1KSWER3bu2Su5/S'),
(8, 'amal', 'amalskumar@gmail.com', 0, 30, '$2y$10$t.llcxmqjz1bFK7ShN9FGejQxEKEIMr1mktP6BThH4eOZJPZ85TTC'),
(9, 'ama@gmail.com', 'ama@gmail.com', 15, 100, '$2y$10$9DaJHW9WJbOcjaF6YdcDUOEClYuK8XNJ9rhZdht7yghZId3ihAqCK');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`ActID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `HabitID` (`HabitID`);

--
-- Indexes for table `customshop`
--
ALTER TABLE `customshop`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sid` (`sid`),
  ADD KEY `cid` (`cid`);

--
-- Indexes for table `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
  MODIFY `ActID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `customshop`
--
ALTER TABLE `customshop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `activity_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `activity_ibfk_2` FOREIGN KEY (`HabitID`) REFERENCES `habits` (`id`);

--
-- Constraints for table `customshop`
--
ALTER TABLE `customshop`
  ADD CONSTRAINT `customshop_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

--
-- Constraints for table `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `habits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `shop` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`cid`) REFERENCES `customshop` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
