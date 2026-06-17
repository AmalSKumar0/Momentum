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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `email` varchar(24) NOT NULL,
  `gold` int(5) NOT NULL DEFAULT 0,
  `hp` int(5) NOT NULL DEFAULT 100,
  `password` varchar(250) NOT NULL,
  `xp` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 1,
  `daily_streak` int(11) NOT NULL DEFAULT 0,
  `last_active` date DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `xp_reward` int(11) DEFAULT 5,
  `gold_reward` int(11) DEFAULT 10,
  `clicked` date DEFAULT NULL,
  `streak` int(11) NOT NULL DEFAULT 0,
  `last_completed` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `ActID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `HabitID` int(11) DEFAULT NULL,
  `isComplete` tinyint(1) DEFAULT 1,
  `completedDay` date DEFAULT NULL,
  PRIMARY KEY (`ActID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customshop`
--

CREATE TABLE `customshop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `xp_reward` int(11) DEFAULT 5,
  `gold_cost` int(11) DEFAULT 50,
  `type` enum('potion','scroll','gear','custom') DEFAULT 'custom',
  `description` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE `shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `xp_reward` int(11) DEFAULT 5,
  `gold_cost` int(11) DEFAULT 10,
  `type` enum('potion','scroll','gear') DEFAULT 'potion',
  `description` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop`
--

INSERT INTO `shop` (`id`, `title`, `difficulty`, `xp_reward`, `gold_cost`, `type`, `description`) VALUES
(1, 'Minor Health Potion', 'easy', 25, 50, 'potion', 'Restores 25 Health Points (HP).'),
(2, 'Major Health Potion', 'medium', 50, 90, 'potion', 'Restores 50 Health Points (HP).'),
(3, 'Elixir of Life', 'hard', 100, 150, 'potion', 'Fully restores Health Points (HP) to 100.'),
(4, 'Scroll of Wisdom', 'medium', 100, 80, 'scroll', 'Grants 100 Experience Points (XP) immediately.');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `baught` date DEFAULT NULL,
  `Quantity` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

ALTER TABLE `users`
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `activity`
  ADD KEY `UserID` (`UserID`),
  ADD KEY `HabitID` (`HabitID`),
  ADD KEY `UserID_HabitID_completedDay` (`UserID`, `HabitID`, `completedDay`);

ALTER TABLE `customshop`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `habits`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `inventory`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sid` (`sid`),
  ADD KEY `cid` (`cid`);

--
-- Constraints for dumped tables
--

ALTER TABLE `activity`
  ADD CONSTRAINT `activity_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `activity_ibfk_2` FOREIGN KEY (`HabitID`) REFERENCES `habits` (`id`);

ALTER TABLE `customshop`
  ADD CONSTRAINT `customshop_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `habits`
  ADD CONSTRAINT `habits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `shop` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`cid`) REFERENCES `customshop` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
