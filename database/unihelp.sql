-- UniHelp - Smart Career & Placement Guidance Platform
-- Database Schema for MySQL
-- Final Year Engineering Project

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Database: unihelp
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS unihelp DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE unihelp;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','alumni','admin') NOT NULL,
  `status` enum('pending','approved','rejected','active') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: user_profiles
-- --------------------------------------------------------
CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `graduation_year` int(4) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `department` (`department`),
  CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: student_profiles
-- --------------------------------------------------------
CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `career_goal` enum('software','government','mba','core','startup','research','other') DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `resume_feedback` text DEFAULT NULL,
  `resume_feedback_by` int(11) DEFAULT NULL,
  `resume_feedback_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `career_goal` (`career_goal`),
  CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: skills
-- --------------------------------------------------------
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` enum('technical','aptitude','coding','soft','domain') DEFAULT 'technical',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: user_skills
-- --------------------------------------------------------
CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `progress` int(3) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_skill` (`user_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: career_roadmaps
-- --------------------------------------------------------
CREATE TABLE `career_roadmaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `career_goal` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `steps` JSON DEFAULT NULL,
  `skills_required` JSON DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `career_goal` (`career_goal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: opportunities
-- --------------------------------------------------------
CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `posted_by` int(11) NOT NULL,
  `type` enum('placement','internship') NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `apply_deadline` date DEFAULT NULL,
  `status` enum('active','closed','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `posted_by` (`posted_by`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: applications
-- --------------------------------------------------------
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `status` enum('applied','shortlisted','rejected','selected','withdrawn') DEFAULT 'applied',
  `notes` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_opportunity` (`user_id`,`opportunity_id`),
  KEY `opportunity_id` (`opportunity_id`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: interview_experiences
-- --------------------------------------------------------
CREATE TABLE `interview_experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `experience_type` enum('placement','internship') DEFAULT 'placement',
  `rounds` text DEFAULT NULL,
  `questions` text DEFAULT NULL,
  `questions_json` JSON DEFAULT NULL,
  `tips` text DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `outcome` enum('selected','rejected','pending') DEFAULT 'selected',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `contributor_name` varchar(255) DEFAULT NULL,
  `contributor_email` varchar(255) DEFAULT NULL,
  `contributor_phone` varchar(30) DEFAULT NULL,
  `contributor_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `company_name` (`company_name`),
  KEY `status` (`status`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `interview_experiences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mentorship_requests
-- --------------------------------------------------------
CREATE TABLE `mentorship_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `mentor_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `mentor_id` (`mentor_id`),
  KEY `status` (`status`),
  CONSTRAINT `mentorship_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mentor_profiles
-- --------------------------------------------------------
CREATE TABLE `mentor_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `skills` text DEFAULT NULL COMMENT 'comma-separated skill tags',
  `experience` varchar(120) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `max_slots` int(11) NOT NULL DEFAULT 4,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `mentor_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mentor_slots
-- --------------------------------------------------------
CREATE TABLE `mentor_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mentor_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 30,
  `notes` varchar(255) DEFAULT NULL,
  `is_booked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `mentor_id` (`mentor_id`),
  CONSTRAINT `mentor_slots_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: slot_bookings
-- --------------------------------------------------------
CREATE TABLE `slot_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slot_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
  `booked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slot_id` (`slot_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `slot_bookings_ibfk_1` FOREIGN KEY (`slot_id`) REFERENCES `mentor_slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `slot_bookings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: test_scores
-- --------------------------------------------------------
CREATE TABLE `test_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `test_type` enum('aptitude','coding','technical','mock') NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) DEFAULT 100.00,
  `test_date` date NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `test_type` (`test_type`),
  CONSTRAINT `test_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: projects (for skill-based project recommendations)
-- --------------------------------------------------------
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `skills_required` JSON DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'intermediate',
  `category` varchar(100) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mock_tests (Faculty-created skill mock tests)
-- --------------------------------------------------------
CREATE TABLE `mock_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `skill_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `single_attempt` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `skill_id` (`skill_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `mock_tests_skill_fk` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mock_tests_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mock_test_questions (Exactly 10 MCQs per test)
-- --------------------------------------------------------
CREATE TABLE `mock_test_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mock_test_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` enum('A','B','C','D') NOT NULL,
  `points` int(11) DEFAULT 10,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mock_test_id` (`mock_test_id`),
  CONSTRAINT `mock_test_questions_fk` FOREIGN KEY (`mock_test_id`) REFERENCES `mock_tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mock_test_attempts (One row per attempt)
-- --------------------------------------------------------
CREATE TABLE `mock_test_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mock_test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_score` int(11) NOT NULL DEFAULT 100,
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_test` (`mock_test_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mock_test_attempts_fk_test` FOREIGN KEY (`mock_test_id`) REFERENCES `mock_tests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mock_test_attempts_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mock_test_attempt_answers (Detailed answers)
-- --------------------------------------------------------
CREATE TABLE `mock_test_attempt_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `points_awarded` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `mock_test_attempt_answers_fk_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `mock_test_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mock_test_attempt_answers_fk_question` FOREIGN KEY (`question_id`) REFERENCES `mock_test_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: user_skill_points (Auto-updated skill score via mock tests)
-- --------------------------------------------------------
CREATE TABLE `user_skill_points` (
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `total_attempts` int(11) NOT NULL DEFAULT 0,
  `last_score` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `user_skill_points_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_skill_points_fk_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert default admin user
-- --------------------------------------------------------
INSERT INTO `users` (`email`, `password`, `role`, `status`) VALUES
('admin@unihelp.edu', '$2y$10$F0QF/p.hp8WZXsvskNlhbeYcdPbhoNX8oHpVF0MQ5iI1n.XJHjbxO', 'admin', 'active');

INSERT INTO `user_profiles` (`user_id`, `full_name`) VALUES
(1, 'System Admin');

-- --------------------------------------------------------
-- Insert default skills
-- --------------------------------------------------------
INSERT INTO `skills` (`name`, `category`) VALUES
('Data Structures', 'technical'),
('Algorithms', 'technical'),
('Programming (C/C++)', 'coding'),
('Programming (Java)', 'coding'),
('Programming (Python)', 'coding'),
('Web Development', 'technical'),
('Database Management', 'technical'),
('Problem Solving', 'aptitude'),
('Logical Reasoning', 'aptitude'),
('Quantitative Aptitude', 'aptitude'),
('Verbal Ability', 'aptitude'),
('Communication', 'soft'),
('Teamwork', 'soft'),
('Leadership', 'soft'),
('Machine Learning', 'technical'),
('Cloud Computing', 'technical');

-- --------------------------------------------------------
-- Insert default career roadmaps
-- --------------------------------------------------------
-- --------------------------------------------------------
-- Insert sample projects for skill-based recommendations
-- --------------------------------------------------------
INSERT INTO `projects` (`title`, `description`, `skills_required`, `difficulty`, `category`) VALUES
('E-Commerce Website', 'Build a full-stack e-commerce site with cart, checkout', '["Web Development","Database Management"]', 'intermediate', 'Web'),
('DSA Practice Tracker', 'Track your DSA progress with LeetCode integration', '["Data Structures","Algorithms","Programming (Python)"]', 'beginner', 'Tools'),
('Portfolio Website', 'Create a personal portfolio with projects showcase', '["Web Development","Programming (JavaScript)"]', 'beginner', 'Web'),
('Chat Application', 'Real-time chat with WebSockets', '["Web Development","Programming (Java)","Database Management"]', 'advanced', 'Web'),
('ML Sentiment Analyzer', 'Sentiment analysis using NLP', '["Machine Learning","Programming (Python)"]', 'advanced', 'AI');

-- --------------------------------------------------------
-- Insert default career roadmaps
-- --------------------------------------------------------
INSERT INTO `career_roadmaps` (`career_goal`, `title`, `description`, `steps`, `skills_required`, `order_index`) VALUES
('software', 'Software Engineer Roadmap', 'Complete roadmap for aspiring software engineers', 
'["Master DSA fundamentals","Learn a programming language deeply","Build 2-3 portfolio projects","Practice on LeetCode/HackerRank","Prepare system design","Apply for internships"]',
'["Data Structures","Algorithms","Programming","Web Development"]', 1),
('government', 'Government Job Preparation', 'Roadmap for GATE, UPSC, and PSU exams',
'["Identify target exam (GATE/UPSC)","Create study schedule","Join coaching if needed","Practice previous papers","Stay updated with current affairs","Apply for notifications"]',
'["Quantitative Aptitude","Logical Reasoning","Verbal Ability","Domain Knowledge"]', 2),
('mba', 'MBA Preparation Roadmap', 'Path to top B-schools',
'["Take CAT/XAT/GMAT","Build strong profile","Prepare for GD-PI","Gain work experience","Research target colleges","Apply in multiple rounds"]',
'["Quantitative Aptitude","Logical Reasoning","Verbal Ability","Communication"]', 3),
('core', 'Core Engineering Career', 'Path for mechanical, civil, electrical engineers',
'["Master core subjects","Learn industry tools","Complete certifications","Build project portfolio","Network with professionals","Target PSUs and MNCs"]',
'["Domain Knowledge","Problem Solving","Technical Skills"]', 4),
('startup', 'Startup & Entrepreneurship', 'A roadmap for building your own business from idea to scale.',
'["Identify a Problem & Ideation","Market Research & Validation","Build an MVP (Minimum Viable Product)","Business Model Canvas & Finance","Legal & Registration","Marketing & User Acquisition","Pitching & Fundraising"]',
'["Leadership","Product Management","Sales & Marketing","Financial Planning","Networking"]', 5),
('research', 'Research & Higher Studies', 'Path for students interested in R&D, PhD, and academic careers.',
'["Identify Research Interests","Read Research Papers & Literature Review","Work on a Research Project","Publish Papers in Conferences/Journals","Prepare for GRE/GATE/NET","Apply to Labs/Universities"]',
'["Critical Thinking","Technical Writing","Data Analysis","Research Methodology","Mathematics"]', 6),
('other', 'Alternative Career Paths', 'For creative, non-traditional, and emerging career fields.',
'["Discover Your Passion & Strengths","Build a Portfolio/Showcase","Networking & Personal Branding","Freelancing or Internships","Continuous Learning & Upskilling","Find a Mentor"]',
'["Adaptability","Communication","Creativity","Self-Management","Networking"]', 7);

COMMIT;
