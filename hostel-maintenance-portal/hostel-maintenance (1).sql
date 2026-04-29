-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 02:53 PM
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
-- Database: `hostel-maintenance`
--

-- --------------------------------------------------------

--
-- Table structure for table `issue_types`
--

CREATE TABLE `issue_types` (
  `id` int(11) NOT NULL,
  `issue_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issue_types`
--

INSERT INTO `issue_types` (`id`, `issue_name`) VALUES
(1, 'Plumbing'),
(2, 'Electrical'),
(3, 'Broken Furniture');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `status`, `created_at`) VALUES
(1, 4, 'Request #3 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 10:22:03'),
(2, 4, 'Request #2 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 10:22:03'),
(3, 4, 'Scheduling conflict for request #6: the request is logged but cannot be fulfilled at your chosen time (2026-03-16 15:09:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(4, 3, 'No technician available for request #6 at 2026-03-16 15:09:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(5, 2, 'Scheduling conflict for request #1: the request is logged but cannot be fulfilled at your chosen time (2026-03-16 19:40:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(6, 3, 'No technician available for request #1 at 2026-03-16 19:40:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(7, 4, 'Scheduling conflict for request #7: the request is logged but cannot be fulfilled at your chosen time (2026-03-17 11:00:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(8, 3, 'No technician available for request #7 at 2026-03-17 11:00:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(9, 4, 'Scheduling conflict for request #9: the request is logged but cannot be fulfilled at your chosen time (2026-03-19 09:07:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(10, 3, 'No technician available for request #9 at 2026-03-19 09:07:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(11, 14, 'Scheduling conflict for request #16: the request is logged but cannot be fulfilled at your chosen time (2026-03-20 08:08:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(12, 3, 'No technician available for request #16 at 2026-03-20 08:08:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(13, 4, 'Scheduling conflict for request #12: the request is logged but cannot be fulfilled at your chosen time (2026-03-20 10:00:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(14, 3, 'No technician available for request #12 at 2026-03-20 10:00:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(15, 4, 'Scheduling conflict for request #18: the request is logged but cannot be fulfilled at your chosen time (2026-03-20 10:40:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 10:22:03'),
(16, 3, 'No technician available for request #18 at 2026-03-20 10:40:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 10:22:03'),
(17, 14, 'Scheduling conflict for request #20: the request is logged but cannot be fulfilled at your chosen time (2026-04-28 16:47:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 14:47:17'),
(18, 3, 'No technician available for request #20 at 2026-04-28 16:47:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 14:47:17'),
(19, 4, 'Request #7 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 14:48:56'),
(20, 14, 'Request #16 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 17:55:28'),
(21, 16, 'Scheduling conflict for request #21: the request is logged but cannot be fulfilled at your chosen time (2026-04-28 19:57:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 17:58:28'),
(22, 3, 'No technician available for request #21 at 2026-04-28 19:57:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 17:58:28'),
(23, 16, 'Request #21 has been rescheduled to 2026-04-28 19:00:00. The assignment engine will retry at the revised interval.', 'unread', '2026-04-28 17:59:25'),
(24, 16, 'Scheduling conflict for request #21: the request is logged but cannot be fulfilled at your chosen time (2026-04-28 19:00:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 17:59:25'),
(25, 3, 'No technician available for request #21 at 2026-04-28 19:00:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 17:59:25'),
(26, 16, 'Request #21 has been rescheduled to 2026-04-28 20:01:00. The assignment engine will retry at the revised interval.', 'unread', '2026-04-28 18:00:37'),
(27, 16, 'Scheduling conflict for request #21: the request is logged but cannot be fulfilled at your chosen time (2026-04-28 20:01:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-28 18:01:58'),
(28, 3, 'No technician available for request #21 at 2026-04-28 20:01:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-28 18:01:58'),
(29, 4, 'Request #12 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 18:04:11'),
(30, 4, 'Request #18 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 18:04:11'),
(31, 16, 'Request #21 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 18:04:11'),
(32, 4, 'Request #6 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-28 18:04:38'),
(33, 16, 'Request #22 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-29 12:29:05'),
(34, 16, 'Scheduling conflict for request #23: the request is logged but cannot be fulfilled at your chosen time (2026-04-29 14:34:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-29 12:35:11'),
(35, 3, 'No technician available for request #23 at 2026-04-29 14:34:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-29 12:35:11'),
(36, 16, 'Request #23 has been rescheduled to 2026-04-29 14:37:00. The assignment engine will retry at the revised interval.', 'unread', '2026-04-29 12:35:38'),
(37, 16, 'Request #23 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-29 12:37:04'),
(38, 16, 'Request #24 is now in progress. Technician assignment completed for your selected time slot.', 'unread', '2026-04-29 12:45:26'),
(39, 14, 'Scheduling conflict for request #25: the request is logged but cannot be fulfilled at your chosen time (2026-04-29 14:44:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-29 12:45:26'),
(40, 3, 'No technician available for request #25 at 2026-04-29 14:44:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-29 12:45:26'),
(41, 14, 'Request #20 has been rescheduled to 2026-04-29 14:49:00. The assignment engine will retry at the revised interval.', 'unread', '2026-04-29 12:48:38'),
(42, 14, 'Scheduling conflict for request #20: the request is logged but cannot be fulfilled at your chosen time (2026-04-29 14:49:00) due to technician unavailability. Please update your preferred interval.', 'unread', '2026-04-29 12:50:10'),
(43, 3, 'No technician available for request #20 at 2026-04-29 14:49:00. Student has been asked to provide a new preferred interval.', 'unread', '2026-04-29 12:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `issue_type_id` int(11) DEFAULT NULL,
  `hostel` varchar(50) DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `available_time` datetime DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `assigned_staff` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `technician_arrived` tinyint(1) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `student_id`, `issue_type_id`, `hostel`, `room`, `description`, `available_time`, `status`, `assigned_staff`, `created_at`, `technician_arrived`, `rating`) VALUES
(1, 2, 1, '2', '211', 'burst pipe', '2026-03-16 19:40:00', 'pending', NULL, '2026-03-15 15:40:26', NULL, NULL),
(2, 4, 1, '4', '211', 'tape not working', '2026-03-16 10:00:00', 'in_progress', 8, '2026-03-15 19:32:15', NULL, NULL),
(3, 4, 1, '4', '211', 'burst pipe', '2026-03-15 21:49:00', 'in_progress', 7, '2026-03-15 19:49:11', NULL, NULL),
(4, 4, 2, '4', '211', 'light not working', '2026-03-15 22:04:00', 'completed', 1, '2026-03-15 20:02:14', NULL, NULL),
(6, 4, 1, '4', '211', 'the sink is not working', '2026-03-16 15:09:00', 'in_progress', 6, '2026-03-16 13:03:52', 0, NULL),
(7, 4, 3, '4', '211', 'broken chair', '2026-03-17 11:00:00', 'in_progress', 3, '2026-03-17 08:06:01', NULL, NULL),
(8, 13, 2, '2', '214', 'light not working', '2026-03-18 11:13:00', 'completed', 1, '2026-03-18 09:11:20', NULL, NULL),
(9, 4, 1, '4', '211', 'toilet wont flush', '2026-03-19 09:07:00', 'pending', NULL, '2026-03-19 07:02:29', 0, NULL),
(10, 4, 3, '4', '211', 'broken chair', '2026-03-19 09:14:00', 'completed', 3, '2026-03-19 07:11:11', NULL, NULL),
(11, 4, 2, '4', '101', 'Light bulb not switching on', '2026-03-19 12:37:00', 'in_progress', 5, '2026-03-19 08:35:35', NULL, NULL),
(12, 4, 2, '4', '213', 'Socket not working', '2026-03-20 10:00:00', 'in_progress', 1, '2026-03-19 08:37:13', NULL, NULL),
(13, 4, 2, '4', '214', 'faulty lights', '2026-03-19 11:02:00', 'completed', 1, '2026-03-19 08:56:48', NULL, NULL),
(14, 4, 2, '4', '211', 'light not working', '2026-03-19 13:30:00', 'in_progress', 1, '2026-03-19 11:21:27', NULL, NULL),
(15, 14, 1, '1', '123', 'blocked toilet', '2026-03-19 09:00:00', 'completed', 6, '2026-03-19 14:44:51', 1, 7),
(16, 14, 2, '1', '123', 'faulty lights', '2026-03-20 08:08:00', 'completed', 5, '2026-03-19 14:45:43', 1, 7),
(17, 14, 3, '1', '123', 'broken chair', '2026-03-19 17:46:00', 'completed', 3, '2026-03-19 14:46:39', 1, 8),
(18, 4, 2, '4', '211', 'socket not working', '2026-03-20 10:40:00', 'in_progress', 5, '2026-03-20 08:36:53', NULL, NULL),
(19, 4, 3, '4', '211', 'broken chair', '2026-03-20 11:21:00', 'in_progress', 4, '2026-03-20 09:18:50', NULL, NULL),
(20, 14, 1, '2', '211', 'broken table', '2026-04-29 14:49:00', 'pending', NULL, '2026-04-28 14:46:24', NULL, NULL),
(21, 16, 2, '3', '111', 'Faulty lights', '2026-04-28 20:01:00', 'completed', 9, '2026-04-28 17:52:51', 1, 7),
(22, 16, 2, '3', '111', 'faulty lights', '2026-04-29 14:28:00', 'in_progress', 9, '2026-04-29 12:26:49', NULL, NULL),
(23, 16, 2, '3', '111', 'broken study light', '2026-04-29 14:37:00', 'in_progress', 5, '2026-04-29 12:28:19', NULL, NULL),
(24, 16, 3, '2', '111', 'broken table', '2026-04-29 14:44:00', 'in_progress', 4, '2026-04-29 12:40:29', NULL, NULL),
(25, 14, 3, '2', '111', 'broken base', '2026-04-29 14:44:00', 'pending', NULL, '2026-04-29 12:40:56', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialization` enum('plumber','electrician','carpenter') DEFAULT NULL,
  `status` enum('free','occupied') DEFAULT 'free',
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `specialization`, `status`, `phone`) VALUES
(1, 5, 'electrician', 'occupied', '07756789765'),
(2, 6, 'plumber', 'occupied', '098978767'),
(3, 7, 'carpenter', 'occupied', '0778234575'),
(4, 8, 'carpenter', 'occupied', '0782323341'),
(5, 9, 'electrician', 'occupied', '0788723645'),
(6, 10, 'plumber', 'occupied', '0719374784'),
(7, 11, 'plumber', 'occupied', '0713746743'),
(8, 15, 'plumber', 'occupied', '0777107345'),
(9, 17, 'electrician', 'occupied', '0772108545');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','staff','supervisor') DEFAULT NULL,
  `hostel` varchar(50) DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `google_id` varchar(255) DEFAULT NULL,
  `profile_pic` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `hostel`, `room`, `created_at`, `google_id`, `profile_pic`) VALUES
(1, 'Test Student', 'student@test.com', '$2y$10$KbQi9QWc4Uu0m6k7yDPp..examplehash', 'student', NULL, NULL, '2026-03-15 15:10:18', NULL, NULL),
(2, 'Ashley zichawo', 'ashleyzichawo@gmail.com', '$2y$10$sYgSGI29pfVFQBPS/uM9UuBRlWp/YN5cOG3X4EMadrxvJ6IDRljt6', 'student', '2', '211', '2026-03-15 15:17:16', NULL, NULL),
(3, 'System Supervisor', 'supervisor@hit.ac.zw', '$2y$10$OWZKC0LZlGRSTKbvSnvQP.FfwZGcPdl.bM3ab303WAJVLgcGi1lPi', 'supervisor', NULL, NULL, '2026-03-15 15:32:44', NULL, NULL),
(4, 'Ratie', 'ratie@yahoo.com', '$2y$10$uBxv76vezYE3Qkq.FFSlWu1IRLD2kfIYDnjYyDB67nEzGjFp9VX6u', 'student', '4', '211', '2026-03-15 19:19:52', NULL, NULL),
(5, 'john doe', 'johnd@yahoo.com', '$2y$10$dWHA303FqJD2i3VloLbF3eY6.01i.Zrd0StqDBBE3CuCqPtVk57ly', 'staff', NULL, NULL, '2026-03-15 19:40:59', NULL, NULL),
(6, 'Marc', 'marc@yahoo.com', '$2y$10$Scm22BsA2IwS.FlVrEInoOSG5UH2xGxbiklyCzLjZNuRNjSq9G.JW', 'staff', NULL, NULL, '2026-03-15 19:45:09', NULL, NULL),
(7, 'Toney Jaa', 'toney@gmail.com', '$2y$10$7Im1Kt0ZvRx3XMhrFIlqpev8cdq7NizajHJ4RKFcy4Sv/voe2Uyem', 'staff', NULL, NULL, '2026-03-17 09:16:36', NULL, NULL),
(8, 'Kai Leng', 'kai@gmail.com', '$2y$10$r0U7XW1N.uFbPCPfCyH6y.GhjR/4paKnTknIJcnKogrzssVLXhv1u', 'staff', NULL, NULL, '2026-03-17 09:17:47', NULL, NULL),
(9, 'Jah Man', 'jah@gmail.com', '$2y$10$inCeCDDmzkcxfwF8uvWDzeB2EgTcKpV1lWdCzOqnR/TEVhXcsp.Zi', 'staff', NULL, NULL, '2026-03-17 09:21:40', NULL, NULL),
(10, 'Jay B', 'jayb@yahoo.com', '$2y$10$sPYid53onwxwrsBudzGCQO370J4aIqntRkE/3IJ7SXkmBd5Far1y.', 'staff', NULL, NULL, '2026-03-17 09:25:08', NULL, NULL),
(11, 'Smith Rowe', 'smith@yahoo.com', '$2y$10$Onn522.y8s.Cf3aKd9BqMuSZEM1oYpbB8hCWzA4jjjhDzT8FEbEha', 'staff', NULL, NULL, '2026-03-17 09:27:45', NULL, NULL),
(13, 'Mambo ', 'mambo@hit.ac.zw', '$2y$10$lhPyt4bktIU/0.FF0bKbge5cZATeuqE4C.J.Ig1bq6kOojrj44TOi', 'student', '2', '214', '2026-03-18 09:04:18', NULL, NULL),
(14, 'Nigel Majaya', 'majayanigel@gmail.com', '$2y$10$o0ZvDTDocE7MHc4CP3Gjs.38enZYVKCDchvN58qmQ64hLhj9x8doS', 'student', '1', '123', '2026-03-19 14:42:53', NULL, NULL),
(15, 'Lucious Edd', 'lucy@yahoo.com', '$2y$10$.uadTT3LMucTgmlKSIF7m.MpESs96TcSlXj8ICB6JEdm1ElbMcnMC', 'staff', NULL, NULL, '2026-03-19 14:49:05', NULL, NULL),
(16, 'Brian Tigere', 'Tigaz@hit.ac.zw', '$2y$10$diTzNzFUu5XCUFh60KoX5elJtoCCwtaiGkdpQ1GmmM8FNDQcD9mIC', 'student', '3', '111', '2026-04-28 17:49:48', NULL, NULL),
(17, 'tammy lee', 'tammylee@hit.ac.zw', '$2y$10$rcwrB8ED5mVMjFWPTgsAi.n1BGl4TiyMPfwgr1e32OYCAKxhhEqtG', 'staff', NULL, NULL, '2026-04-28 18:03:43', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `issue_types`
--
ALTER TABLE `issue_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `issue_type_id` (`issue_type_id`),
  ADD KEY `assigned_staff` (`assigned_staff`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `issue_types`
--
ALTER TABLE `issue_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`issue_type_id`) REFERENCES `issue_types` (`id`),
  ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`assigned_staff`) REFERENCES `staff` (`id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
