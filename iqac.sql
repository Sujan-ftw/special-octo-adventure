-- Create database
CREATE DATABASE IF NOT EXISTS `iqac`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `iqac`;

------------------------------------------------------------
-- 1. students
------------------------------------------------------------
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `father_income` decimal(15,2) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `batch_year` varchar(20) DEFAULT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `student_mobile` varchar(15) DEFAULT NULL,
  `email_id` varchar(255) DEFAULT NULL,
  `father_mobile` varchar(15) DEFAULT NULL,
  `student_photo` longblob DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `entry_type` varchar(50) DEFAULT NULL,
  `management_quota` tinyint(1) DEFAULT NULL,
  `counselling_quota` tinyint(1) DEFAULT NULL,
  `father_photo` varchar(255) DEFAULT NULL,
  `mother_photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 2. staff_details
------------------------------------------------------------
CREATE TABLE `staff_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `designation` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `qualification` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 3. mou_files
------------------------------------------------------------
CREATE TABLE `mou_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(255) NOT NULL,
  `year` varchar(10) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `staff_id` varchar(50) DEFAULT NULL,
  `sign_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 4. staff  (depends on mou_files)
------------------------------------------------------------
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `mou_file_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `mou_file_id` (`mou_file_id`),
  CONSTRAINT `staff_ibfk_1`
    FOREIGN KEY (`mou_file_id`) REFERENCES `mou_files` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 5. academic_details  (depends on students)
------------------------------------------------------------
CREATE TABLE `academic_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `institution_type` enum('school','college') NOT NULL,
  `total_marks` int(11) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `year_completed` varchar(4) NOT NULL,
  `roll_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `institution_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `academic_details_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 6. achievement_details  (depends on students)
------------------------------------------------------------
CREATE TABLE `achievement_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `achievement_title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_awarded` date DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `achievement_level` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `achievement_details_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 7. industry_visits  (depends on students)
------------------------------------------------------------
CREATE TABLE `industry_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `roll_number` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `visit_duration` varchar(100) NOT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `industry_visits_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 8. non_academic_details  (depends on students)
------------------------------------------------------------
CREATE TABLE `non_academic_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `organization_name` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `non_academic_details_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

------------------------------------------------------------
-- 9. semester_marks  (depends on students)
------------------------------------------------------------
CREATE TABLE `semester_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `semester_number` int(11) NOT NULL,
  `register_number` varchar(50) DEFAULT NULL,
  `subjects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects`)),
  `pass_fail` varchar(10) DEFAULT NULL,
  `grade_or_avg` varchar(10) DEFAULT NULL,
  `mark_sheet_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_marks` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `semester_marks_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 10. snon_academic_details  (depends on staff_details)
------------------------------------------------------------
CREATE TABLE `snon_academic_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `snon_academic_details_ibfk_1`
    FOREIGN KEY (`staff_id`) REFERENCES `staff_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 11. sacheivement_details  (depends on staff_details)
------------------------------------------------------------
CREATE TABLE `sachievement_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `achievement_title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_awarded` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `sachievement_details_ibfk_1`
    FOREIGN KEY (`staff_id`) REFERENCES `staff_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 12. staff_fdp  (depends on staff_details)
------------------------------------------------------------
CREATE TABLE `staff_fdp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `enrollment_course` varchar(255) DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `staff_fdp_ibfk_1`
    FOREIGN KEY (`staff_id`) REFERENCES `staff_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 13. student_projects  (depends on students)
------------------------------------------------------------
CREATE TABLE `student_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `project_type` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_projects_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 14. student_sports  (depends on students)
------------------------------------------------------------
CREATE TABLE `student_sports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `sport_name` varchar(255) NOT NULL,
  `level` varchar(100) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `year_participated` varchar(50) DEFAULT NULL,
  `sports_certificate` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_sports_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 15. achievement_mark  (standalone, no FK)
------------------------------------------------------------
CREATE TABLE `achievement_mark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `semester_number` int(11) NOT NULL,
  `register_number` varchar(50) DEFAULT NULL,
  `subject1_code` varchar(20) DEFAULT NULL,
  `subject1_name` varchar(100) DEFAULT NULL,
  `subject1_marks` int(11) DEFAULT NULL,
  `subject1_pass_fail` varchar(10) DEFAULT NULL,
  `subject2_code` varchar(20) DEFAULT NULL,
  `subject2_name` varchar(100) DEFAULT NULL,
  `subject2_marks` int(11) DEFAULT NULL,
  `subject2_pass_fail` varchar(10) DEFAULT NULL,
  `subject3_code` varchar(20) DEFAULT NULL,
  `subject3_name` varchar(100) DEFAULT NULL,
  `subject3_marks` int(11) DEFAULT NULL,
  `subject3_pass_fail` varchar(10) DEFAULT NULL,
  `subject4_code` varchar(20) DEFAULT NULL,
  `subject4_name` varchar(100) DEFAULT NULL,
  `subject4_marks` int(11) DEFAULT NULL,
  `subject4_pass_fail` varchar(10) DEFAULT NULL,
  `subject5_code` varchar(20) DEFAULT NULL,
  `subject5_name` varchar(100) DEFAULT NULL,
  `subject5_marks` int(11) DEFAULT NULL,
  `subject5_pass_fail` varchar(10) DEFAULT NULL,
  `subject6_code` varchar(20) DEFAULT NULL,
  `subject6_name` varchar(100) DEFAULT NULL,
  `subject6_marks` int(11) DEFAULT NULL,
  `subject6_pass_fail` varchar(10) DEFAULT NULL,
  `subject7_code` varchar(20) DEFAULT NULL,
  `subject7_name` varchar(100) DEFAULT NULL,
  `subject7_marks` int(11) DEFAULT NULL,
  `subject7_pass_fail` varchar(10) DEFAULT NULL,
  `subject8_code` varchar(20) DEFAULT NULL,
  `subject8_name` varchar(100) DEFAULT NULL,
  `subject8_marks` int(11) DEFAULT NULL,
  `subject8_pass_fail` varchar(10) DEFAULT NULL,
  `subject9_code` varchar(20) DEFAULT NULL,
  `subject9_name` varchar(100) DEFAULT NULL,
  `subject9_marks` int(11) DEFAULT NULL,
  `subject9_pass_fail` varchar(10) DEFAULT NULL,
  `grade_or_avg` varchar(10) DEFAULT NULL,
  `mark_sheet_path` varchar(255) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 16. achievement_marks  (standalone, no FK)
------------------------------------------------------------
CREATE TABLE `achievement_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `semester_number` int(11) NOT NULL,
  `register_number` varchar(50) DEFAULT NULL,
  `subject1_code` varchar(20) DEFAULT NULL,
  `subject1_name` varchar(100) DEFAULT NULL,
  `subject1_marks` int(11) DEFAULT NULL,
  `subject1_pass_fail` varchar(10) DEFAULT NULL,
  `subject2_code` varchar(20) DEFAULT NULL,
  `subject2_name` varchar(100) DEFAULT NULL,
  `subject2_marks` int(11) DEFAULT NULL,
  `subject2_pass_fail` varchar(10) DEFAULT NULL,
  `subject3_code` varchar(20) DEFAULT NULL,
  `subject3_name` varchar(100) DEFAULT NULL,
  `subject3_marks` int(11) DEFAULT NULL,
  `subject3_pass_fail` varchar(10) DEFAULT NULL,
  `subject4_code` varchar(20) DEFAULT NULL,
  `subject4_name` varchar(100) DEFAULT NULL,
  `subject4_marks` int(11) DEFAULT NULL,
  `subject4_pass_fail` varchar(10) DEFAULT NULL,
  `subject5_code` varchar(20) DEFAULT NULL,
  `subject5_name` varchar(100) DEFAULT NULL,
  `subject5_marks` int(11) DEFAULT NULL,
  `subject5_pass_fail` varchar(10) DEFAULT NULL,
  `subject6_code` varchar(20) DEFAULT NULL,
  `subject6_name` varchar(100) DEFAULT NULL,
  `subject6_marks` int(11) DEFAULT NULL,
  `subject6_pass_fail` varchar(10) DEFAULT NULL,
  `subject7_code` varchar(20) DEFAULT NULL,
  `subject7_name` varchar(100) DEFAULT NULL,
  `subject7_marks` int(11) DEFAULT NULL,
  `subject7_pass_fail` varchar(10) DEFAULT NULL,
  `subject8_code` varchar(20) DEFAULT NULL,
  `subject8_name` varchar(100) DEFAULT NULL,
  `subject8_marks` int(11) DEFAULT NULL,
  `subject8_pass_fail` varchar(10) DEFAULT NULL,
  `subject9_code` varchar(20) DEFAULT NULL,
  `subject9_name` varchar(100) DEFAULT NULL,
  `subject9_marks` int(11) DEFAULT NULL,
  `subject9_pass_fail` varchar(10) DEFAULT NULL,
  `grade_or_avg` varchar(20) DEFAULT NULL,
  `mark_sheet_path` varchar(255) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 17. curriculum_gaps  (standalone)
------------------------------------------------------------
CREATE TABLE `curriculum_gaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) NOT NULL,
  `additional_content` text NOT NULL,
  `action_taken` text NOT NULL,
  `date` date NOT NULL,
  `resource_person` varchar(100) NOT NULL,
  `mode` varchar(50) NOT NULL,
  `no_of_students` int(11) NOT NULL,
  `relevance_to_POs_PSOs` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 18. institutions  (standalone)
------------------------------------------------------------
CREATE TABLE `institutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` enum('school','college') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 19. student_publications  (standalone)
------------------------------------------------------------
CREATE TABLE `student_publications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roll_no` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `paper_title` varchar(255) DEFAULT NULL,
  `journal_conference` varchar(255) DEFAULT NULL,
  `issn_isbn` varchar(50) DEFAULT NULL,
  `date_of_publication` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 20. technical_events  (standalone)
------------------------------------------------------------
CREATE TABLE `technical_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `organized_by` varchar(255) DEFAULT NULL,
  `event_date` date NOT NULL,
  `number_of_students_participated` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_upload` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

------------------------------------------------------------
-- 21. users  (standalone)
------------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
