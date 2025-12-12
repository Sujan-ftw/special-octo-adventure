-- Schema updates for role-based access system
USE `iqac`;

-- Add department and year to students table if not exists
ALTER TABLE `students` 
ADD COLUMN IF NOT EXISTS `department` varchar(100) DEFAULT NULL AFTER `batch_year`,
ADD COLUMN IF NOT EXISTS `year_class` varchar(20) DEFAULT NULL AFTER `department`,
ADD COLUMN IF NOT EXISTS `tutor_id` int(11) DEFAULT NULL AFTER `year_class`,
ADD COLUMN IF NOT EXISTS `user_id` int(11) DEFAULT NULL AFTER `tutor_id`;

-- Add user_id to staff_details if not exists
ALTER TABLE `staff_details` 
ADD COLUMN IF NOT EXISTS `user_id` int(11) DEFAULT NULL AFTER `email`;

-- Create student_registrations table for form submissions
CREATE TABLE IF NOT EXISTS `student_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `form_type` varchar(50) NOT NULL COMMENT 'initial, sports_club, etc',
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`form_data`)),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_date` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'staff user_id',
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `user_id` (`user_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `student_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `student_registrations_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create staff_subjects table to track which subjects staff can enter marks for
CREATE TABLE IF NOT EXISTS `staff_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `staff_subjects_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_details` (`id`),
  CONSTRAINT `staff_subjects_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add index on users for faster role-based queries
CREATE INDEX IF NOT EXISTS `idx_users_role` ON `users` (`role`);
