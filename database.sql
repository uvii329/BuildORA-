-- ========================================================
-- BuildORA Blog Application - Database Dump for Deployment
-- Generated: 2026-08-23 16:40:38
-- PHP Version: 8.0.30
-- MySQL Server Version: 10.4.32-MariaDB
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `post_likes`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, '245095X', 'uviisamarakoon@gmail.com', '$2y$10$dNt8vemZp4gySpg2fmCeC.qI/kGGCsp/5RUaWNm.6Ye2V3kGLS6kG', 'user', '2026-08-18 14:33:37'),
(2, '24000X', 'abc@uom.lk', '$2y$10$Ws3y7ZtsL8k2iZ8hTguP6OtCpImFpbHjIubpO87iI/G2GDatGZI8K', 'user', '2026-08-23 00:48:03');

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Web Development'),
(2, 'Software Development'),
(3, 'Java'),
(4, 'Spring Boot'),
(5, 'Robotics'),
(6, 'UI/UX Design'),
(7, 'Team Projects'),
(8, 'Learning & Experiences'),
(9, 'Other');

-- --------------------------------------------------------
-- Table structure for table `posts`
-- --------------------------------------------------------
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `posts`
INSERT INTO `posts` (`id`, `user_id`, `category_id`, `title`, `content`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 'Lost & Found Management System', 'A web-based system that helps users report,\r\nmanage, and track lost and found items.', NULL, '2026-08-18 14:45:25', '2026-08-23 18:39:54'),
(2, 1, 9, 'abcxhhxxn', 'xxx', NULL, '2026-08-18 16:08:38', '2026-08-23 18:39:54');

-- --------------------------------------------------------
-- Table structure for table `post_likes`
-- --------------------------------------------------------
CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_post_user_like` (`post_id`, `user_id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `post_likes`
INSERT INTO `post_likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(6, 2, 1, '2026-08-23 18:52:51'),
(7, 1, 1, '2026-08-23 18:52:52'),
(9, 2, 2, '2026-08-23 18:53:46'),
(10, 1, 2, '2026-08-23 18:53:48');

SET FOREIGN_KEY_CHECKS = 1;
