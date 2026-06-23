-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 09:08 AM
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
-- Database: `rpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_battles`
--

CREATE TABLE `active_battles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quest_name` varchar(100) NOT NULL,
  `monster_name` varchar(100) NOT NULL,
  `monster_max_hp` int(11) NOT NULL,
  `monster_hp` int(11) NOT NULL,
  `reward_gold` int(11) NOT NULL,
  `reward_exp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE `characters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `gender` varchar(25) NOT NULL,
  `class` varchar(25) NOT NULL,
  `level` int(11) DEFAULT 1,
  `gold` int(11) DEFAULT 25,
  `hp` int(11) DEFAULT 100,
  `max_hp` int(11) DEFAULT 100,
  `mana` int(11) DEFAULT 100,
  `max_mana` int(11) DEFAULT 100,
  `stamina` int(11) DEFAULT 100,
  `max_stamina` int(11) DEFAULT 100,
  `exp` int(11) DEFAULT 0,
  `max_exp` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `characters`
--

INSERT INTO `characters` (`id`, `user_id`, `name`, `gender`, `class`, `level`, `gold`, `hp`, `max_hp`, `mana`, `max_mana`, `stamina`, `max_stamina`, `exp`, `max_exp`) VALUES
(1, 1, 'xendor599', 'male', 'warrior', 99, 99000600, 81, 120, 40, 40, 120, 120, 550, 100),
(8, 8, 'wizard', 'male', 'wizard', 3, 240, 120, 120, 220, 220, 160, 160, 25, 400);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_type` varchar(50) NOT NULL,
  `item_stat` varchar(100) NOT NULL,
  `item_img` varchar(255) NOT NULL,
  `is_equipped` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `user_id`, `item_name`, `item_type`, `item_stat`, `item_img`, `is_equipped`) VALUES
(10, 8, 'Wooden Staff', 'Weapon', '+8 MATK', 'img/weapon/waff.png', 0),
(11, 8, 'Bone Bow', 'Weapon', '+15 ATK', 'img/weapon/bobow.png', 1),
(14, 1, 'Crystal Staff', 'Weapon', '+35 MATK', 'img/weapon/crystaf.png', 0),
(18, 1, 'Rubber Chicken', 'Weapon', '+10000 ATK', 'img/weapon/chicken.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password_hash`, `created_at`) VALUES
(1, 'nathanael@gmail.com', 'xendor599', '$2y$10$WEdeY28urydKCcoF8amKHeyPEqp6rW8BYRtZxPsW0jfeFU4CV0vKq', '2026-06-19 18:31:53'),
(8, 'wizard@gmail.com', 'wizard', '$2y$10$OnWG349DlRmBDrLe0iJimeehdrFJagwFrEifo9U4h/Q6EVOgal//O', '2026-06-23 06:01:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_battles`
--
ALTER TABLE `active_battles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_battle` (`user_id`);

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_battles`
--
ALTER TABLE `active_battles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
