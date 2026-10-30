-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: unihelp
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `status` enum('applied','shortlisted','rejected','selected','withdrawn') DEFAULT 'applied',
  `notes` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resume_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_opportunity` (`user_id`,`opportunity_id`),
  KEY `opportunity_id` (`opportunity_id`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_roadmaps`
--

DROP TABLE IF EXISTS `career_roadmaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_roadmaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `career_goal` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`steps`)),
  `skills_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_required`)),
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `career_goal` (`career_goal`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_roadmaps`
--

LOCK TABLES `career_roadmaps` WRITE;
/*!40000 ALTER TABLE `career_roadmaps` DISABLE KEYS */;
INSERT INTO `career_roadmaps` VALUES (8,'software','Software Engineer Roadmap','Complete roadmap for aspiring software engineers','[\"Master DSA fundamentals\",\"Learn a programming language deeply\",\"Build 2-3 portfolio projects\",\"Practice on LeetCode/HackerRank\",\"Prepare system design\",\"Apply for internships\"]','[\"Data Structures\",\"Algorithms\",\"Programming\",\"Web Development\"]',1,'2026-03-28 04:39:14'),(9,'government','Government Job Preparation','Roadmap for GATE, UPSC, and PSU exams','[\"Identify target exam (GATE/UPSC)\",\"Create study schedule\",\"Join coaching if needed\",\"Practice previous papers\",\"Stay updated with current affairs\",\"Apply for notifications\"]','[\"Quantitative Aptitude\",\"Logical Reasoning\",\"Verbal Ability\",\"Domain Knowledge\"]',2,'2026-03-28 04:39:14'),(10,'mba','MBA Preparation Roadmap','Path to top B-schools','[\"Take CAT/XAT/GMAT\",\"Build strong profile\",\"Prepare for GD-PI\",\"Gain work experience\",\"Research target colleges\",\"Apply in multiple rounds\"]','[\"Quantitative Aptitude\",\"Logical Reasoning\",\"Verbal Ability\",\"Communication\"]',3,'2026-03-28 04:39:14'),(11,'core','Core Engineering Career','Path for mechanical, civil, electrical engineers','[\"Master core subjects\",\"Learn industry tools\",\"Complete certifications\",\"Build project portfolio\",\"Network with professionals\",\"Target PSUs and MNCs\"]','[\"Domain Knowledge\",\"Problem Solving\",\"Technical Skills\"]',4,'2026-03-28 04:39:14'),(12,'startup','Startup & Entrepreneurship','A roadmap for building your own business from idea to scale.','[\"Identify a Problem & Ideation\",\"Market Research & Validation\",\"Build an MVP (Minimum Viable Product)\",\"Business Model Canvas & Finance\",\"Legal & Registration\",\"Marketing & User Acquisition\",\"Pitching & Fundraising\"]','[\"Leadership\",\"Product Management\",\"Sales & Marketing\",\"Financial Planning\",\"Networking\"]',5,'2026-03-28 04:39:14'),(13,'research','Research & Higher Studies','Path for students interested in R&D, PhD, and academic careers.','[\"Identify Research Interests\",\"Read Research Papers & Literature Review\",\"Work on a Research Project\",\"Publish Papers in Conferences/Journals\",\"Prepare for GRE/GATE/NET\",\"Apply to Labs/Universities\"]','[\"Critical Thinking\",\"Technical Writing\",\"Data Analysis\",\"Research Methodology\",\"Mathematics\"]',6,'2026-03-28 04:39:14'),(14,'other','Alternative Career Paths','For creative, non-traditional, and emerging career fields.','[\"Discover Your Passion & Strengths\",\"Build a Portfolio/Showcase\",\"Networking & Personal Branding\",\"Freelancing or Internships\",\"Continuous Learning & Upskilling\",\"Find a Mentor\"]','[\"Adaptability\",\"Communication\",\"Creativity\",\"Self-Management\",\"Networking\"]',7,'2026-03-28 04:39:14');
/*!40000 ALTER TABLE `career_roadmaps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_documents`
--

DROP TABLE IF EXISTS `chatbot_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_id` varchar(32) NOT NULL COMMENT 'MD5 hash of filename',
  `filename` varchar(255) NOT NULL,
  `chunk_count` int(11) NOT NULL DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_documents`
--

LOCK TABLES `chatbot_documents` WRITE;
/*!40000 ALTER TABLE `chatbot_documents` DISABLE KEYS */;
INSERT INTO `chatbot_documents` VALUES (1,'b73337620904c7bb33cbf836f6669d08','UniHelp Student Support_ Internal Knowledge Base & Policy Manual.pdf',4,'2026-03-29 07:03:58');
/*!40000 ALTER TABLE `chatbot_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contest_answers`
--

DROP TABLE IF EXISTS `contest_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contest_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` char(1) DEFAULT NULL,
  `text_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `marks_awarded` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `contest_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `contest_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contest_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `contest_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest_answers`
--

LOCK TABLES `contest_answers` WRITE;
/*!40000 ALTER TABLE `contest_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contest_attempts`
--

DROP TABLE IF EXISTS `contest_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contest_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `contest_id` (`contest_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `contest_attempts_ibfk_1` FOREIGN KEY (`contest_id`) REFERENCES `contests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contest_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest_attempts`
--

LOCK TABLES `contest_attempts` WRITE;
/*!40000 ALTER TABLE `contest_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contest_questions`
--

DROP TABLE IF EXISTS `contest_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contest_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` varchar(20) DEFAULT 'mcq',
  `option_a` text DEFAULT NULL,
  `option_b` text DEFAULT NULL,
  `option_c` text DEFAULT NULL,
  `option_d` text DEFAULT NULL,
  `correct_option` char(1) DEFAULT NULL,
  `correct_text` text DEFAULT NULL,
  `marks` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `contest_id` (`contest_id`),
  CONSTRAINT `contest_questions_ibfk_1` FOREIGN KEY (`contest_id`) REFERENCES `contests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest_questions`
--

LOCK TABLES `contest_questions` WRITE;
/*!40000 ALTER TABLE `contest_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests`
--

DROP TABLE IF EXISTS `contests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `week_key` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `contests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests`
--

LOCK TABLES `contests` WRITE;
/*!40000 ALTER TABLE `contests` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interview_experiences`
--

DROP TABLE IF EXISTS `interview_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interview_experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `experience_type` enum('placement','internship') DEFAULT 'placement',
  `rounds` text DEFAULT NULL,
  `questions` text DEFAULT NULL,
  `questions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions_json`)),
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `company_name` (`company_name`),
  KEY `status` (`status`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `interview_experiences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interview_experiences`
--

LOCK TABLES `interview_experiences` WRITE;
/*!40000 ALTER TABLE `interview_experiences` DISABLE KEYS */;
/*!40000 ALTER TABLE `interview_experiences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentor_profiles`
--

DROP TABLE IF EXISTS `mentor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `mentor_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentor_profiles`
--

LOCK TABLES `mentor_profiles` WRITE;
/*!40000 ALTER TABLE `mentor_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `mentor_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentor_slots`
--

DROP TABLE IF EXISTS `mentor_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mentor_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 30,
  `notes` varchar(255) DEFAULT NULL,
  `is_booked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mentor_id` (`mentor_id`),
  CONSTRAINT `mentor_slots_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentor_slots`
--

LOCK TABLES `mentor_slots` WRITE;
/*!40000 ALTER TABLE `mentor_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `mentor_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentorship_requests`
--

DROP TABLE IF EXISTS `mentorship_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentorship_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `mentor_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `mentor_id` (`mentor_id`),
  KEY `status` (`status`),
  CONSTRAINT `mentorship_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentorship_requests`
--

LOCK TABLES `mentorship_requests` WRITE;
/*!40000 ALTER TABLE `mentorship_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `mentorship_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mock_interview_messages`
--

DROP TABLE IF EXISTS `mock_interview_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mock_interview_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interview_id` int(11) NOT NULL,
  `sender` enum('user','ai') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mock_interview_messages_ibfk_1` (`interview_id`),
  CONSTRAINT `mock_interview_messages_ibfk_1` FOREIGN KEY (`interview_id`) REFERENCES `mock_interviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mock_interview_messages`
--

LOCK TABLES `mock_interview_messages` WRITE;
/*!40000 ALTER TABLE `mock_interview_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `mock_interview_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mock_interviews`
--

DROP TABLE IF EXISTS `mock_interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mock_interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `status` enum('started','completed','canceled') DEFAULT 'started',
  `score` int(3) DEFAULT NULL,
  `overall_feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mock_interviews_ibfk_1` (`user_id`),
  CONSTRAINT `mock_interviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mock_interviews`
--

LOCK TABLES `mock_interviews` WRITE;
/*!40000 ALTER TABLE `mock_interviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `mock_interviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mock_test_attempt_answers`
--

DROP TABLE IF EXISTS `mock_test_attempt_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mock_test_attempt_answers`
--

LOCK TABLES `mock_test_attempt_answers` WRITE;
/*!40000 ALTER TABLE `mock_test_attempt_answers` DISABLE KEYS */;
INSERT INTO `mock_test_attempt_answers` VALUES (11,2,21,'A',1,10),(12,2,22,'A',1,10),(13,2,23,'A',1,10),(14,2,24,'A',0,0),(15,2,25,'C',0,0),(16,2,26,'A',1,10),(17,2,27,'C',0,0),(18,2,28,'A',0,0),(19,2,29,'B',0,0),(20,2,30,'B',0,0);
/*!40000 ALTER TABLE `mock_test_attempt_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mock_test_attempts`
--

DROP TABLE IF EXISTS `mock_test_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mock_test_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mock_test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_score` int(11) NOT NULL DEFAULT 100,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_test` (`mock_test_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mock_test_attempts_fk_test` FOREIGN KEY (`mock_test_id`) REFERENCES `mock_tests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mock_test_attempts_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mock_test_attempts`
--

LOCK TABLES `mock_test_attempts` WRITE;
/*!40000 ALTER TABLE `mock_test_attempts` DISABLE KEYS */;
INSERT INTO `mock_test_attempts` VALUES (2,2,8,40,100,'2026-04-01 12:38:48');
/*!40000 ALTER TABLE `mock_test_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mock_test_questions`
--

DROP TABLE IF EXISTS `mock_test_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mock_test_questions`
--

LOCK TABLES `mock_test_questions` WRITE;
/*!40000 ALTER TABLE `mock_test_questions` DISABLE KEYS */;
INSERT INTO `mock_test_questions` VALUES (21,2,'daes','A','b','c','d','A',10,0),(22,2,'hjkb','bghj','hbjh','kjn','b','A',10,1),(23,2,'ku','ku','kjbkj','bkj','bk','A',10,2),(24,2,'kjb','kjb','kjb','kj','bjk','B',10,3),(25,2,'jk','bjk','bk','jb','kjb','A',10,4),(26,2,'kj','bkj','bkj','b','kjb','A',10,5),(27,2,'hb','jkhb','jkh','bhj','b','A',10,6),(28,2,'jhb','hjb','hjb','hjbhj','bhj','B',10,7),(29,2,'bhjk','bkhj','b','khjb','khjb','A',10,8),(30,2,'hk','bkhk','b','hjkjb','khb','A',10,9);
/*!40000 ALTER TABLE `mock_test_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mock_tests`
--

DROP TABLE IF EXISTS `mock_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mock_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `skill_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `single_attempt` tinyint(1) DEFAULT 1,
  `duration_mins` int(11) DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `skill_id` (`skill_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `mock_tests_skill_fk` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mock_tests_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mock_tests`
--

LOCK TABLES `mock_tests` WRITE;
/*!40000 ALTER TABLE `mock_tests` DISABLE KEYS */;
INSERT INTO `mock_tests` VALUES (2,'DSA','edfcads',17,9,1,1,0,NULL,'2026-04-01 12:21:36','2026-04-01 12:21:36');
/*!40000 ALTER TABLE `mock_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities`
--

DROP TABLE IF EXISTS `opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `package` varchar(120) DEFAULT NULL,
  `min_cgpa` decimal(3,2) DEFAULT 0.00,
  `min_10th` decimal(5,2) DEFAULT 0.00,
  `min_12th` decimal(5,2) DEFAULT 0.00,
  `allowed_depts` text DEFAULT NULL,
  `required_skills` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `posted_by` (`posted_by`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities`
--

LOCK TABLES `opportunities` WRITE;
/*!40000 ALTER TABLE `opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `skills_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_required`)),
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'intermediate',
  `category` varchar(100) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (6,'E-Commerce Website','Build a full-stack e-commerce site with cart, checkout','[\"Web Development\",\"Database Management\"]','intermediate','Web',NULL,'2026-03-28 04:39:14'),(7,'DSA Practice Tracker','Track your DSA progress with LeetCode integration','[\"Data Structures\",\"Algorithms\",\"Programming (Python)\"]','beginner','Tools',NULL,'2026-03-28 04:39:14'),(8,'Portfolio Website','Create a personal portfolio with projects showcase','[\"Web Development\",\"Programming (JavaScript)\"]','beginner','Web',NULL,'2026-03-28 04:39:14'),(9,'Chat Application','Real-time chat with WebSockets','[\"Web Development\",\"Programming (Java)\",\"Database Management\"]','advanced','Web',NULL,'2026-03-28 04:39:14'),(10,'ML Sentiment Analyzer','Sentiment analysis using NLP','[\"Machine Learning\",\"Programming (Python)\"]','advanced','AI',NULL,'2026-03-28 04:39:14');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resume_reviews`
--

DROP TABLE IF EXISTS `resume_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `review_type` enum('ai','faculty') NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `ai_score` int(11) DEFAULT 0,
  `ai_feedback` longtext DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `faculty_feedback` text DEFAULT NULL,
  `faculty_score` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `faculty_id` (`faculty_id`),
  CONSTRAINT `resume_reviews_faculty_fk` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resume_reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resume_reviews`
--

LOCK TABLES `resume_reviews` WRITE;
/*!40000 ALTER TABLE `resume_reviews` DISABLE KEYS */;
INSERT INTO `resume_reviews` VALUES (4,8,'resume-sample.pdf','resume_sample.pdf','pdf','ai','completed',0,'{\"overall_score\":0,\"ats_score\":0,\"experience_level\":\"fresher\",\"summary_feedback\":\"The provided text appears to be a PDF\'s internal structure rather than actual resume content. No summary or other sections could be extracted for review.\",\"skills_found\":[],\"missing_keywords\":[\"Python\",\"Java\",\"Data Structures\",\"Algorithms\",\"Machine Learning\",\"Cloud Computing\",\"Web Development\"],\"strengths\":[\"No discernible strengths as the content is unreadable.\"],\"weaknesses\":[\"The resume content is unreadable, appearing as PDF metadata and internal structure.\",\"No actual resume sections (education, experience, skills, projects) could be identified or parsed.\"],\"suggestions\":[\"Please provide the actual text content of your resume for a proper review.\",\"Ensure the resume is in a text-readable format (e.g., plain text, DOCX, or a properly extracted PDF text).\"],\"sections_found\":[],\"missing_sections\":[\"summary\",\"education\",\"skills\",\"projects\",\"experience\",\"certifications\",\"achievements\",\"extracurriculars\"],\"grammar_issues\":[\"Cannot assess grammar as no human-readable text was provided.\"],\"formatting_notes\":[\"The input is not a human-readable resume document, but rather PDF internal code.\",\"Cannot assess formatting without actual resume content.\"]}',NULL,NULL,NULL,'2026-04-01 09:07:55','2026-04-01 09:07:28');
/*!40000 ALTER TABLE `resume_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` enum('technical','aptitude','coding','soft','domain') DEFAULT 'technical',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (17,'Data Structures','technical',NULL),(18,'Algorithms','technical',NULL),(19,'Programming (C/C++)','coding',NULL),(20,'Programming (Java)','coding',NULL),(21,'Programming (Python)','coding',NULL),(22,'Web Development','technical',NULL),(23,'Database Management','technical',NULL),(24,'Problem Solving','aptitude',NULL),(25,'Logical Reasoning','aptitude',NULL),(26,'Quantitative Aptitude','aptitude',NULL),(27,'Verbal Ability','aptitude',NULL),(28,'Communication','soft',NULL),(29,'Teamwork','soft',NULL),(30,'Leadership','soft',NULL),(31,'Machine Learning','technical',NULL),(32,'Cloud Computing','technical',NULL);
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slot_bookings`
--

DROP TABLE IF EXISTS `slot_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slot_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slot_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slot_id` (`slot_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `slot_bookings_ibfk_1` FOREIGN KEY (`slot_id`) REFERENCES `mentor_slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `slot_bookings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slot_bookings`
--

LOCK TABLES `slot_bookings` WRITE;
/*!40000 ALTER TABLE `slot_bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `slot_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_profiles`
--

DROP TABLE IF EXISTS `student_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `career_goal` enum('software','government','mba','core','startup','research','other') DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `resume_feedback` text DEFAULT NULL,
  `resume_feedback_by` int(11) DEFAULT NULL,
  `resume_feedback_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tenth_percentage` decimal(5,2) DEFAULT NULL,
  `twelfth_percentage` decimal(5,2) DEFAULT NULL,
  `degree_level` varchar(50) DEFAULT NULL,
  `current_semester` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `career_goal` (`career_goal`),
  CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_profiles`
--

LOCK TABLES `student_profiles` WRITE;
/*!40000 ALTER TABLE `student_profiles` DISABLE KEYS */;
INSERT INTO `student_profiles` VALUES (2,5,'software',8.00,NULL,NULL,NULL,NULL,'2026-03-28 04:28:26','2026-03-28 04:44:06',80.00,90.00,'Graduate',7),(5,8,NULL,NULL,'resume_sample.pdf',NULL,NULL,NULL,'2026-03-31 06:01:55','2026-04-01 09:07:28',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `student_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_projects`
--

DROP TABLE IF EXISTS `student_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `skills` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `github_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `student_projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_projects`
--

LOCK TABLES `student_projects` WRITE;
/*!40000 ALTER TABLE `student_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_scores`
--

DROP TABLE IF EXISTS `test_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `test_type` enum('aptitude','coding','technical','mock') NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) DEFAULT 100.00,
  `test_date` date NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `test_type` (`test_type`),
  CONSTRAINT `test_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_scores`
--

LOCK TABLES `test_scores` WRITE;
/*!40000 ALTER TABLE `test_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `department` (`department`),
  CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
INSERT INTO `user_profiles` VALUES (5,1,'System Administrator',NULL,'Administration',NULL,NULL,NULL,NULL,NULL,'2026-03-28 04:17:59','2026-03-28 04:17:59'),(6,5,'Student User','1234567890','MSC (AI/ML)',NULL,NULL,NULL,'I am student',NULL,'2026-03-28 04:28:26','2026-03-28 04:44:06'),(9,8,'Student Demo',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-31 06:01:55','2026-03-31 06:01:55'),(10,9,'Faculty Member',NULL,'BSC (AI/ML)',NULL,NULL,NULL,NULL,NULL,'2026-04-01 12:20:08','2026-04-01 12:20:08'),(11,10,'Faculty Tester',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-01 12:30:52','2026-04-01 12:30:52'),(12,11,'Faculty User',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-01 12:33:59','2026-04-01 12:33:59');
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_skill_points`
--

DROP TABLE IF EXISTS `user_skill_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_skill_points` (
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `total_attempts` int(11) NOT NULL DEFAULT 0,
  `last_score` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `user_skill_points_fk_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_skill_points_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_skill_points`
--

LOCK TABLES `user_skill_points` WRITE;
/*!40000 ALTER TABLE `user_skill_points` DISABLE KEYS */;
INSERT INTO `user_skill_points` VALUES (8,17,40,1,40,'2026-04-01 12:38:48');
/*!40000 ALTER TABLE `user_skill_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_skills`
--

DROP TABLE IF EXISTS `user_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `progress` int(3) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_skill` (`user_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_skills`
--

LOCK TABLES `user_skills` WRITE;
/*!40000 ALTER TABLE `user_skills` DISABLE KEYS */;
INSERT INTO `user_skills` VALUES (2,8,17,'intermediate',40,NULL,'2026-04-01 12:38:48');
/*!40000 ALTER TABLE `user_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','alumni','admin') NOT NULL,
  `status` enum('pending','approved','rejected','active') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@unihelp.edu','$2y$10$QeEW.220iYZqPppndXVthuySiwWNkz57Av.JeEjt/uN4xojduXFi2','admin','active','2026-03-28 04:18:00','2026-03-28 04:33:51',NULL,NULL,NULL,NULL),(5,'student@example.com','$2y$10$8UzS/U9rGnCJqM.TvGZ9LObuDN5BAHm5cI6mHERklhyYU9sIvW8pG','student','active','2026-03-28 04:28:26','2026-03-28 04:33:51',NULL,NULL,NULL,NULL),(8,'student2@example.com','$2y$10$8UzS/U9rGnCJqM.TvGZ9LObuDN5BAHm5cI6mHERklhyYU9sIvW8pG','student','active','2026-03-31 06:01:55','2026-03-31 07:31:53',NULL,NULL,NULL,NULL),(9,'faculty@example.com','$2y$10$8UzS/U9rGnCJqM.TvGZ9LObuDN5BAHm5cI6mHERklhyYU9sIvW8pG','faculty','active','2026-04-01 12:20:08','2026-04-01 12:20:08',NULL,NULL,NULL,NULL),(10,'faculty_test@example.com','$2y$10$8UzS/U9rGnCJqM.TvGZ9LObuDN5BAHm5cI6mHERklhyYU9sIvW8pG','faculty','active','2026-04-01 12:30:52','2026-04-01 12:30:52',NULL,NULL,NULL,NULL),(11,'faculty2@example.com','$2y$10$8UzS/U9rGnCJqM.TvGZ9LObuDN5BAHm5cI6mHERklhyYU9sIvW8pG','faculty','active','2026-04-01 12:33:59','2026-04-01 12:33:59',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-01 18:37:42
