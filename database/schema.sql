-- Skillbridg Database Schema
-- MCA Minor Project: Skill Exchange & Peer Learning Portal
-- Engine: InnoDB, Charset: utf8mb4

CREATE DATABASE IF NOT EXISTS `skillbridg_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skillbridg_db`;

-- Drop tables if they exist (in proper dependency order)
DROP TABLE IF EXISTS `feedback`;
DROP TABLE IF EXISTS `learning_requests`;
DROP TABLE IF EXISTS `user_skills`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table (Students)
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `bio` TEXT DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `semester` VARCHAR(20) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Master Skills Table
CREATE TABLE `skills` (
  `skill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_name` VARCHAR(100) NOT NULL UNIQUE,
  `category` VARCHAR(50) DEFAULT 'General',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. User-Skill Pivot Mapping Table (Teach & Learn skills)
CREATE TABLE `user_skills` (
  `user_skill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  `skill_type` ENUM('teach', 'learn') NOT NULL,
  `proficiency_level` ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Intermediate',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE,
  CONSTRAINT `uk_user_skill_type` UNIQUE (`user_id`, `skill_id`, `skill_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Learning Requests Table
CREATE TABLE `learning_requests` (
  `request_id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,      -- Learner requesting help
  `receiver_id` INT NOT NULL,    -- Skill provider offering teach skill
  `skill_id` INT NOT NULL,
  `status` ENUM('Pending', 'Accepted', 'Rejected', 'Completed') DEFAULT 'Pending',
  `message` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_requests_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requests_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requests_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Feedback Table
CREATE TABLE `feedback` (
  `feedback_id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_id` INT NOT NULL UNIQUE,
  `given_by_user_id` INT NOT NULL,
  `received_by_user_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `feedback_text` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_feedback_request` FOREIGN KEY (`request_id`) REFERENCES `learning_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_giver` FOREIGN KEY (`given_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_receiver` FOREIGN KEY (`received_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
