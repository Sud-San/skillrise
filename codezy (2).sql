-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 27, 2026 at 06:23 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `codezy`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_check_level_up`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_check_level_up` (IN `p_user_id` INT)   BEGIN
    DECLARE v_current_xp INT;
    DECLARE v_current_level INT;
    DECLARE v_xp_needed INT;
    
    SELECT current_xp, level INTO v_current_xp, v_current_level
    FROM user_stats
    WHERE user_id = p_user_id;
    
    -- XP needed for next level (simple formula: level * 1000)
    SET v_xp_needed = v_current_level * 1000;
    
    WHILE v_current_xp >= v_xp_needed DO
        SET v_current_xp = v_current_xp - v_xp_needed;
        SET v_current_level = v_current_level + 1;
        SET v_xp_needed = v_current_level * 1000;
        
        -- Log level up activity
        INSERT INTO user_activity (user_id, activity_type, activity_data)
        VALUES (p_user_id, 'level_up', JSON_OBJECT('new_level', v_current_level));
    END WHILE;
    
    UPDATE user_stats
    SET level = v_current_level, current_xp = v_current_xp
    WHERE user_id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS `sp_update_user_stats`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_user_stats` (IN `p_user_id` INT, IN `p_session_id` INT)   BEGIN
    DECLARE v_score INT;
    DECLARE v_result VARCHAR(20);
    
    -- Get session details
    SELECT score, result INTO v_score, v_result
    FROM game_sessions
    WHERE session_id = p_session_id;
    
    -- Update user stats
    UPDATE user_stats
    SET 
        total_score = total_score + v_score,
        total_games_played = total_games_played + 1,
        total_wins = total_wins + IF(v_result = 'WIN', 1, 0),
        total_losses = total_losses + IF(v_result = 'LOSS', 1, 0),
        current_streak = IF(v_result = 'WIN', current_streak + 1, 0),
        best_streak = GREATEST(best_streak, IF(v_result = 'WIN', current_streak + 1, current_streak)),
        win_rate = ROUND((total_wins + IF(v_result = 'WIN', 1, 0)) * 100.0 / (total_games_played + 1), 2)
    WHERE user_id = p_user_id;
    
    -- Check for level up
    CALL sp_check_level_up(p_user_id);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE IF NOT EXISTS `achievements` (
  `achievement_id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(10) DEFAULT NULL,
  `category` enum('milestone','skill','speed','streak','social','special') NOT NULL,
  `xp_reward` int DEFAULT '100',
  `criteria` json DEFAULT NULL,
  `rarity` enum('common','rare','epic','legendary') DEFAULT 'common',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`achievement_id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_rarity` (`rarity`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`achievement_id`, `slug`, `name`, `description`, `icon`, `category`, `xp_reward`, `criteria`, `rarity`, `is_active`, `created_at`) VALUES
(1, 'first-win', 'First Win', 'Won your first game', '🏆', 'milestone', 100, NULL, 'common', 1, '2025-12-30 10:07:22'),
(2, 'speed-demon', 'Speed Demon', 'Complete a challenge in under 60 seconds', '⚡', 'speed', 250, NULL, 'rare', 1, '2025-12-30 10:07:22'),
(3, 'bug-hunter', 'Bug Hunter', 'Found 100 bugs', '🎯', 'skill', 500, NULL, 'rare', 1, '2025-12-30 10:07:22'),
(4, 'hot-streak', 'Hot Streak', 'Win 10 games in a row', '🔥', 'streak', 750, NULL, 'epic', 1, '2025-12-30 10:07:22'),
(5, 'all-star', 'All-Star', 'Reach 5-star rating in any game', '⭐', 'skill', 1000, NULL, 'epic', 1, '2025-12-30 10:07:22'),
(6, 'algorithm-ace', 'Algorithm Ace', 'Master all algorithm challenges', '🧠', 'skill', 2000, NULL, 'legendary', 1, '2025-12-30 10:07:22'),
(7, 'code-diamond', 'Code Diamond', 'Reach Level 50', '💎', 'milestone', 5000, NULL, 'legendary', 1, '2025-12-30 10:07:22'),
(8, 'king-of-code', 'King of Code', 'Reach #1 on leaderboard', '👑', 'special', 10000, NULL, 'legendary', 1, '2025-12-30 10:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `admin_tbl`
--

DROP TABLE IF EXISTS `admin_tbl`;
CREATE TABLE IF NOT EXISTS `admin_tbl` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `admin_image` varchar(200) DEFAULT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(150) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `admin_email` (`admin_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_tbl`
--

INSERT INTO `admin_tbl` (`admin_id`, `admin_image`, `admin_name`, `admin_email`, `admin_password`, `admin_status`, `created_at`) VALUES
(1, 'admin.png', 'Sudip', 'sitaramsantra07@gmail.com', 'sudip@123', 1, '2025-12-18 12:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_tbl`
--

DROP TABLE IF EXISTS `assignment_tbl`;
CREATE TABLE IF NOT EXISTS `assignment_tbl` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `file_url` varchar(500) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `course_id` (`course_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bug_race_sessions`
--

DROP TABLE IF EXISTS `bug_race_sessions`;
CREATE TABLE IF NOT EXISTS `bug_race_sessions` (
  `race_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `bugs_fixed` int DEFAULT '0',
  `bugs_attempted` int DEFAULT '0',
  `current_combo` int DEFAULT '0',
  `max_combo` int DEFAULT '0',
  `total_score` int DEFAULT '0',
  `time_remaining_seconds` int DEFAULT NULL,
  `game_over` tinyint(1) DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`race_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_score` (`total_score` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_tbl`
--

DROP TABLE IF EXISTS `category_tbl`;
CREATE TABLE IF NOT EXISTS `category_tbl` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category_code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `short_description` varchar(100) NOT NULL,
  `img` varchar(100) NOT NULL,
  `category_status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_slug` (`category_code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category_tbl`
--

INSERT INTO `category_tbl` (`category_id`, `category_name`, `category_code`, `category_description`, `short_description`, `img`, `category_status`, `created_at`, `updated_at`) VALUES
(1, 'HTML', '601', 'HTML is the standard markup language used to create web pages. It provides the structure and content of a page by using tags (like <h1>, <p>, <img>). Everything you see on a website—text, images, links, and forms—is positioned and defined using HTML. It is not a programming language; it cannot perform logical operations.', 'Build the foundation of the web with HTML and CSS structure.', 'html.png', 1, '2025-12-16 12:14:14', '2025-12-22 12:42:54'),
(2, 'PHP', '602', 'PHP is a widely used language primarily designed for web development. It runs on the server (backend) and generates HTML content dynamically. This allows for handling user input, managing databases, and creating sessions (like user logins). It is the backbone of major platforms like WordPress.', 'Master PHP for dynamic web development and server-side scripting.', 'php.png', 1, '2025-12-16 12:14:53', '2025-12-22 12:42:54'),
(3, 'JAVA', '603', 'Java is a robust, mature language designed to be platform-independent (Write Once, Run Anywhere) via the Java Virtual Machine (JVM). It is heavily used in large, mission-critical business systems, banking applications, and historically, for native Android app development. It emphasizes strong typing and object-oriented principles.\r\n', 'Learn Java for robust, scalable enterprise applications and Android apps.', 'java.png', 1, '2025-12-16 12:15:20', '2025-12-22 12:42:54'),
(4, 'PYTHON', '604', 'Python is known for its readability and simple syntax. It is extremely versatile and used across many domains, including building web applications (Django, Flask), performing complex data analysis (Pandas, NumPy), machine learning (TensorFlow), and automating system tasks (scripting).', 'Dive into Python for data science, AI, and versatile web scripting.', 'python.png', 1, '2025-12-16 12:16:01', '2025-12-22 12:42:54'),
(5, 'UNIX', '605', 'Unix is an operating system originally developed in the 1970s. Its principles (simple, modular tools, everything is a file, command-line interface focus) led to a whole family of modern operating systems, including Linux, macOS, and BSD. It is the dominant OS for servers, large systems, and cloud infrastructure due to its stability and security.', 'Understand UNIX systems for powerful command-line and server management.', 'unix.png', 1, '2025-12-16 12:16:22', '2025-12-22 12:42:54');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_tbl`
--

DROP TABLE IF EXISTS `certificate_tbl`;
CREATE TABLE IF NOT EXISTS `certificate_tbl` (
  `certi_id` int NOT NULL AUTO_INCREMENT,
  `stud_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `tutor_id` int DEFAULT NULL,
  `enroll_id` int DEFAULT NULL,
  `certi_url` varchar(255) DEFAULT NULL,
  `issued_on` date DEFAULT NULL,
  `img` blob,
  `cert_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`certi_id`),
  UNIQUE KEY `cert_code` (`cert_code`),
  KEY `stud_id` (`stud_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `enroll_id` (`enroll_id`),
  KEY `fk_cert_language` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `certificate_tbl`
--

INSERT INTO `certificate_tbl` (`certi_id`, `stud_id`, `category_id`, `tutor_id`, `enroll_id`, `certi_url`, `issued_on`, `img`, `cert_code`) VALUES
(1, 1, 1, 1, 1, 'uploads/certs/anjali_web.pdf', '2025-12-17', NULL, 'WEB-2025-001'),
(2, 4, 4, 4, 4, 'uploads/certs/dinesh_py.pdf', '2025-12-17', NULL, 'PYT-2025-004');

-- --------------------------------------------------------

--
-- Table structure for table `city_tbl`
--

DROP TABLE IF EXISTS `city_tbl`;
CREATE TABLE IF NOT EXISTS `city_tbl` (
  `city_id` int NOT NULL AUTO_INCREMENT,
  `city_name` varchar(100) NOT NULL,
  `state_id` int NOT NULL,
  `city_status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`city_id`),
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=605 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `city_tbl`
--

INSERT INTO `city_tbl` (`city_id`, `city_name`, `state_id`, `city_status`, `created_at`, `updated_at`) VALUES
(1, 'North and Middle Andaman', 32, 1, '2025-12-02 10:37:22', '2025-12-23 07:38:06'),
(2, 'South Andaman', 32, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(3, 'Nicobar', 32, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(4, 'Adilabad', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(5, 'Anantapur', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(6, 'Chittoor', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(7, 'East Godavari', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(8, 'Guntur', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(9, 'Hyderabad', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(10, 'Kadapa', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(11, 'Karimnagar', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(12, 'Khammam', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(13, 'Krishna', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(14, 'Kurnool', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(15, 'Mahbubnagar', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(16, 'Medak', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(17, 'Nalgonda', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(18, 'Nellore', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(19, 'Nizamabad', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(20, 'Prakasam', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(21, 'Rangareddi', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(22, 'Srikakulam', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(23, 'Vishakhapatnam', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(24, 'Vizianagaram', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(25, 'Warangal', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(26, 'West Godavari', 1, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(27, 'Anjaw', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(28, 'Changlang', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(29, 'East Kameng', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(30, 'Lohit', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(31, 'Lower Subansiri', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(32, 'Papum Pare', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(33, 'Tirap', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(34, 'Dibang Valley', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(35, 'Upper Subansiri', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(36, 'West Kameng', 3, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(37, 'Barpeta', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(38, 'Bongaigaon', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(39, 'Cachar', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(40, 'Darrang', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(41, 'Dhemaji', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(42, 'Dhubri', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(43, 'Dibrugarh', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(44, 'Goalpara', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(45, 'Golaghat', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(46, 'Hailakandi', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(47, 'Jorhat', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(48, 'Karbi Anglong', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(49, 'Karimganj', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(50, 'Kokrajhar', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(51, 'Lakhimpur', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(52, 'Marigaon', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(53, 'Nagaon', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(54, 'Nalbari', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(55, 'North Cachar Hills', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(56, 'Sibsagar', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(57, 'Sonitpur', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(58, 'Tinsukia', 2, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(59, 'Araria', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(60, 'Aurangabad', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(61, 'Banka', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(62, 'Begusarai', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(63, 'Bhagalpur', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(64, 'Bhojpur', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(65, 'Buxar', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(66, 'Darbhanga', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(67, 'Purba Champaran', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(68, 'Gaya', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(69, 'Gopalganj', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(70, 'Jamui', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(71, 'Jehanabad', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(72, 'Khagaria', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(73, 'Kishanganj', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(74, 'Kaimur', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(75, 'Katihar', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(76, 'Lakhisarai', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(77, 'Madhubani', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(78, 'Munger', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(79, 'Madhepura', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(80, 'Muzaffarpur', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(81, 'Nalanda', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(82, 'Nawada', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(83, 'Patna', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(84, 'Purnia', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(85, 'Rohtas', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(86, 'Saharsa', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(87, 'Samastipur', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(88, 'Sheohar', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(89, 'Sheikhpura', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(90, 'Saran', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(91, 'Sitamarhi', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(92, 'Supaul', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(93, 'Siwan', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(94, 'Vaishali', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(95, 'Pashchim Champaran', 4, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(96, 'Bastar', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(97, 'Bilaspur', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(98, 'Dantewada', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(99, 'Dhamtari', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(100, 'Durg', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(101, 'Jashpur', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(102, 'Janjgir-Champa', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(103, 'Korba', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(104, 'Koriya', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(105, 'Kanker', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(106, 'Kawardha', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(107, 'Mahasamund', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(108, 'Raigarh', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(109, 'Rajnandgaon', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(110, 'Raipur', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(111, 'Surguja', 36, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(112, 'Diu', 29, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(113, 'Daman', 29, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(114, 'Central Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(115, 'East Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(116, 'New Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(117, 'North Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(118, 'North East Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(119, 'North West Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(120, 'South Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(121, 'South West Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(122, 'West Delhi', 25, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(123, 'North Goa', 26, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(124, 'South Goa', 26, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(125, 'Ahmedabad', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(126, 'Amreli District', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(127, 'Anand', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(128, 'Banaskantha', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(129, 'Bharuch', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(130, 'Bhavnagar', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(131, 'Dahod', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(132, 'The Dangs', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(133, 'Gandhinagar', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(134, 'Jamnagar', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(135, 'Junagadh', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(136, 'Kutch', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(137, 'Kheda', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(138, 'Mehsana', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(139, 'Narmada', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(140, 'Navsari', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(141, 'Patan', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(142, 'Panchmahal', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(143, 'Porbandar', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(144, 'Rajkot', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(145, 'Sabarkantha', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(146, 'Surendranagar', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(147, 'Surat', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(148, 'Vadodara', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(149, 'Valsad', 5, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(150, 'Ambala', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(151, 'Bhiwani', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(152, 'Faridabad', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(153, 'Fatehabad', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(154, 'Gurgaon', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(155, 'Hissar', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(156, 'Jhajjar', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(157, 'Jind', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(158, 'Karnal', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(159, 'Kaithal', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(160, 'Kurukshetra', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(161, 'Mahendragarh', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(162, 'Mewat', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(163, 'Panchkula', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(164, 'Panipat', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(165, 'Rewari', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(166, 'Rohtak', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(167, 'Sirsa', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(168, 'Sonepat', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(169, 'Yamuna Nagar', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(170, 'Palwal', 6, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(171, 'Bilaspur', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(172, 'Chamba', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(173, 'Hamirpur', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(174, 'Kangra', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(175, 'Kinnaur', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(176, 'Kulu', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(177, 'Lahaul and Spiti', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(178, 'Mandi', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(179, 'Shimla', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(180, 'Sirmaur', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(181, 'Solan', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(182, 'Una', 7, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(183, 'Anantnag', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(184, 'Badgam', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(185, 'Bandipore', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(186, 'Baramula', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(187, 'Doda', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(188, 'Jammu', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(189, 'Kargil', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(190, 'Kathua', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(191, 'Kupwara', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(192, 'Leh', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(193, 'Poonch', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(194, 'Pulwama', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(195, 'Rajauri', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(196, 'Srinagar', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(197, 'Samba', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(198, 'Udhampur', 8, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(199, 'Bokaro', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(200, 'Chatra', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(201, 'Deoghar', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(202, 'Dhanbad', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(203, 'Dumka', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(204, 'Purba Singhbhum', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(205, 'Garhwa', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(206, 'Giridih', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(207, 'Godda', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(208, 'Gumla', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(209, 'Hazaribagh', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(210, 'Koderma', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(211, 'Lohardaga', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(212, 'Pakur', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(213, 'Palamu', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(214, 'Ranchi', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(215, 'Sahibganj', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(216, 'Seraikela and Kharsawan', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(217, 'Pashchim Singhbhum', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(218, 'Ramgarh', 34, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(219, 'Bidar', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(220, 'Belgaum', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(221, 'Bijapur', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(222, 'Bagalkot', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(223, 'Bellary', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(224, 'Bangalore Rural District', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(225, 'Bangalore Urban District', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(226, 'Chamarajnagar', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(227, 'Chikmagalur', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(228, 'Chitradurga', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(229, 'Davanagere', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(230, 'Dharwad', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(231, 'Dakshina Kannada', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(232, 'Gadag', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(233, 'Gulbarga', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(234, 'Hassan', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(235, 'Haveri District', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(236, 'Kodagu', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(237, 'Kolar', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(238, 'Koppal', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(239, 'Mandya', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(240, 'Mysore', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(241, 'Raichur', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(242, 'Shimoga', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(243, 'Tumkur', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(244, 'Udupi', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(245, 'Uttara Kannada', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(246, 'Ramanagara', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(247, 'Chikballapur', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(248, 'Yadagiri', 9, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(249, 'Alappuzha', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(250, 'Ernakulam', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(251, 'Idukki', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(252, 'Kollam', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(253, 'Kannur', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(254, 'Kasaragod', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(255, 'Kottayam', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(256, 'Kozhikode', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(257, 'Malappuram', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(258, 'Palakkad', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(259, 'Pathanamthitta', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(260, 'Thrissur', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(261, 'Thiruvananthapuram', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(262, 'Wayanad', 10, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(263, 'Alirajpur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(264, 'Anuppur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(265, 'Ashok Nagar', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(266, 'Balaghat', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(267, 'Barwani', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(268, 'Betul', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(269, 'Bhind', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(270, 'Bhopal', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(271, 'Burhanpur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(272, 'Chhatarpur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(273, 'Chhindwara', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(274, 'Damoh', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(275, 'Datia', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(276, 'Dewas', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(277, 'Dhar', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(278, 'Dindori', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(279, 'Guna', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(280, 'Gwalior', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(281, 'Harda', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(282, 'Hoshangabad', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(283, 'Indore', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(284, 'Jabalpur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(285, 'Jhabua', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(286, 'Katni', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(287, 'Khandwa', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(288, 'Khargone', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(289, 'Mandla', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(290, 'Mandsaur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(291, 'Morena', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(292, 'Narsinghpur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(293, 'Neemuch', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(294, 'Panna', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(295, 'Rewa', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(296, 'Rajgarh', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(297, 'Ratlam', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(298, 'Raisen', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(299, 'Sagar', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(300, 'Satna', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(301, 'Sehore', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(302, 'Seoni', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(303, 'Shahdol', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(304, 'Shajapur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(305, 'Sheopur', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(306, 'Shivpuri', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(307, 'Sidhi', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(308, 'Singrauli', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(309, 'Tikamgarh', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(310, 'Ujjain', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(311, 'Umaria', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(312, 'Vidisha', 11, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(313, 'Ahmednagar', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(314, 'Akola', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(315, 'Amrawati', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(316, 'Aurangabad', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(317, 'Bhandara', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(318, 'Beed', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(319, 'Buldhana', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(320, 'Chandrapur', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(321, 'Dhule', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(322, 'Gadchiroli', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(323, 'Gondiya', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(324, 'Hingoli', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(325, 'Jalgaon', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(326, 'Jalna', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(327, 'Kolhapur', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(328, 'Latur', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(329, 'Mumbai City', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(330, 'Mumbai suburban', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(331, 'Nandurbar', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(332, 'Nanded', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(333, 'Nagpur', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(334, 'Nashik', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(335, 'Osmanabad', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(336, 'Parbhani', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(337, 'Pune', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(338, 'Raigad', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(339, 'Ratnagiri', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(340, 'Sindhudurg', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(341, 'Sangli', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(342, 'Solapur', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(343, 'Satara', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(344, 'Thane', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(345, 'Wardha', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(346, 'Washim', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(347, 'Yavatmal', 12, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(348, 'Bishnupur', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(349, 'Churachandpur', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(350, 'Chandel', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(351, 'Imphal East', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(352, 'Senapati', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(353, 'Tamenglong', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(354, 'Thoubal', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(355, 'Ukhrul', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(356, 'Imphal West', 13, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(357, 'East Garo Hills', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(358, 'East Khasi Hills', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(359, 'Jaintia Hills', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(360, 'Ri-Bhoi', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(361, 'South Garo Hills', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(362, 'West Garo Hills', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(363, 'West Khasi Hills', 14, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(364, 'Aizawl', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(365, 'Champhai', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(366, 'Kolasib', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(367, 'Lawngtlai', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(368, 'Lunglei', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(369, 'Mamit', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(370, 'Saiha', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(371, 'Serchhip', 15, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(372, 'Dimapur', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(373, 'Kohima', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(374, 'Mokokchung', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(375, 'Mon', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(376, 'Phek', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(377, 'Tuensang', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(378, 'Wokha', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(379, 'Zunheboto', 16, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(380, 'Angul', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(381, 'Boudh', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(382, 'Bhadrak', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(383, 'Bolangir', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(384, 'Bargarh', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(385, 'Baleswar', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(386, 'Cuttack', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(387, 'Debagarh', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(388, 'Dhenkanal', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(389, 'Ganjam', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(390, 'Gajapati', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(391, 'Jharsuguda', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(392, 'Jajapur', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(393, 'Jagatsinghpur', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(394, 'Khordha', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(395, 'Kendujhar', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(396, 'Kalahandi', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(397, 'Kandhamal', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(398, 'Koraput', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(399, 'Kendrapara', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(400, 'Malkangiri', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(401, 'Mayurbhanj', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(402, 'Nabarangpur', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(403, 'Nuapada', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(404, 'Nayagarh', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(405, 'Puri', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(406, 'Rayagada', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(407, 'Sambalpur', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(408, 'Subarnapur', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(409, 'Sundargarh', 17, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(410, 'Karaikal', 27, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(411, 'Mahe', 27, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(412, 'Puducherry', 27, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(413, 'Yanam', 27, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(414, 'Amritsar', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(415, 'Bathinda', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(416, 'Firozpur', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(417, 'Faridkot', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(418, 'Fatehgarh Sahib', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(419, 'Gurdaspur', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(420, 'Hoshiarpur', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(421, 'Jalandhar', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(422, 'Kapurthala', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(423, 'Ludhiana', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(424, 'Mansa', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(425, 'Moga', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(426, 'Mukatsar', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(427, 'Nawan Shehar', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(428, 'Patiala', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(429, 'Rupnagar', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(430, 'Sangrur', 18, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(431, 'Ajmer', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(432, 'Alwar', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(433, 'Bikaner', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(434, 'Barmer', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(435, 'Banswara', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(436, 'Bharatpur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(437, 'Baran', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(438, 'Bundi', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(439, 'Bhilwara', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(440, 'Churu', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(441, 'Chittorgarh', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(442, 'Dausa', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(443, 'Dholpur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(444, 'Dungapur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(445, 'Ganganagar', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(446, 'Hanumangarh', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(447, 'Juhnjhunun', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(448, 'Jalore', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(449, 'Jodhpur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(450, 'Jaipur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(451, 'Jaisalmer', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(452, 'Jhalawar', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(453, 'Karauli', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(454, 'Kota', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(455, 'Nagaur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(456, 'Pali', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(457, 'Pratapgarh', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(458, 'Rajsamand', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(459, 'Sikar', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(460, 'Sawai Madhopur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(461, 'Sirohi', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(462, 'Tonk', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(463, 'Udaipur', 19, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(464, 'East Sikkim', 20, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(465, 'North Sikkim', 20, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(466, 'South Sikkim', 20, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(467, 'West Sikkim', 20, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(468, 'Ariyalur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(469, 'Chennai', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(470, 'Coimbatore', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(471, 'Cuddalore', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(472, 'Dharmapuri', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(473, 'Dindigul', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(474, 'Erode', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(475, 'Kanchipuram', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(476, 'Kanyakumari', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(477, 'Karur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(478, 'Madurai', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(479, 'Nagapattinam', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(480, 'The Nilgiris', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(481, 'Namakkal', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(482, 'Perambalur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(483, 'Pudukkottai', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(484, 'Ramanathapuram', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(485, 'Salem', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(486, 'Sivagangai', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(487, 'Tiruppur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(488, 'Tiruchirappalli', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(489, 'Theni', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(490, 'Tirunelveli', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(491, 'Thanjavur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(492, 'Thoothukudi', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(493, 'Thiruvallur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(494, 'Thiruvarur', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(495, 'Tiruvannamalai', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(496, 'Vellore', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(497, 'Villupuram', 21, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(498, 'Dhalai', 22, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(499, 'North Tripura', 22, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(500, 'South Tripura', 22, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(501, 'West Tripura', 22, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(502, 'Almora', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(503, 'Bageshwar', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(504, 'Chamoli', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(505, 'Champawat', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(506, 'Dehradun', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(507, 'Haridwar', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(508, 'Nainital', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(509, 'Pauri Garhwal', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(510, 'Pithoragharh', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(511, 'Rudraprayag', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(512, 'Tehri Garhwal', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(513, 'Udham Singh Nagar', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(514, 'Uttarkashi', 33, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(515, 'Agra', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(516, 'Allahabad', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(517, 'Aligarh', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(518, 'Ambedkar Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(519, 'Auraiya', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(520, 'Azamgarh', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(521, 'Barabanki', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(522, 'Badaun', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(523, 'Bagpat', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(524, 'Bahraich', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(525, 'Bijnor', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(526, 'Ballia', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(527, 'Banda', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(528, 'Balrampur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(529, 'Bareilly', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(530, 'Basti', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(531, 'Bulandshahr', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(532, 'Chandauli', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(533, 'Chitrakoot', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(534, 'Deoria', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(535, 'Etah', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(536, 'Kanshiram Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(537, 'Etawah', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(538, 'Firozabad', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(539, 'Farrukhabad', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(540, 'Fatehpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(541, 'Faizabad', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(542, 'Gautam Buddha Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(543, 'Gonda', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(544, 'Ghazipur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(545, 'Gorkakhpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(546, 'Ghaziabad', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(547, 'Hamirpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(548, 'Hardoi', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(549, 'Mahamaya Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(550, 'Jhansi', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(551, 'Jalaun', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(552, 'Jyotiba Phule Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(553, 'Jaunpur District', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(554, 'Kanpur Dehat', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(555, 'Kannauj', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(556, 'Kanpur Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(557, 'Kaushambi', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(558, 'Kushinagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(559, 'Lalitpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(560, 'Lakhimpur Kheri', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(561, 'Lucknow', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(562, 'Mau', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(563, 'Meerut', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(564, 'Maharajganj', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(565, 'Mahoba', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(566, 'Mirzapur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(567, 'Moradabad', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(568, 'Mainpuri', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(569, 'Mathura', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(570, 'Muzaffarnagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(571, 'Pilibhit', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(572, 'Pratapgarh', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(573, 'Rampur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(574, 'Rae Bareli', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(575, 'Saharanpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(576, 'Sitapur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(577, 'Shahjahanpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(578, 'Sant Kabir Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(579, 'Siddharthnagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(580, 'Sonbhadra', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(581, 'Sant Ravidas Nagar', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(582, 'Sultanpur', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(583, 'Shravasti', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(584, 'Unnao', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(585, 'Varanasi', 23, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(586, 'Birbhum', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(587, 'Bankura', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(588, 'Bardhaman', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(589, 'Darjeeling', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(590, 'Dakshin Dinajpur', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(591, 'Hooghly', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(592, 'Howrah', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(593, 'Jalpaiguri', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(594, 'Cooch Behar', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(595, 'Kolkata', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(596, 'Malda', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(597, 'Midnapore', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(598, 'Murshidabad', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(599, 'Nadia', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(600, 'North 24 Parganas', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(601, 'South 24 Parganas', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(602, 'Purulia', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(603, 'Uttar Dinajpur', 24, 1, '2025-12-02 10:37:22', '2025-12-02 10:37:22'),
(604, 'Delhi', 25, 1, '2025-12-15 06:18:07', '2025-12-15 06:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `code_memes`
--

DROP TABLE IF EXISTS `code_memes`;
CREATE TABLE IF NOT EXISTS `code_memes` (
  `meme_id` int NOT NULL AUTO_INCREMENT,
  `image_url` varchar(255) NOT NULL,
  `meme_title` varchar(200) DEFAULT NULL,
  `difficulty` enum('Easy','Medium','Hard') NOT NULL,
  `correct_concept` varchar(100) NOT NULL,
  `options` json DEFAULT NULL,
  `explanation` text,
  `related_languages` json DEFAULT NULL,
  `points` int DEFAULT '50',
  `fun_rating` decimal(3,1) DEFAULT '5.0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`meme_id`),
  KEY `idx_difficulty` (`difficulty`),
  KEY `idx_concept` (`correct_concept`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `user_agent` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `ip_address`, `user_agent`) VALUES
(1, 'df', 'sd@dfd.bvb', 'hello', 'fdfdfdfdfddfdfd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb');

-- --------------------------------------------------------

--
-- Table structure for table `course_notes`
--

DROP TABLE IF EXISTS `course_notes`;
CREATE TABLE IF NOT EXISTS `course_notes` (
  `note_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `description` text,
  `file_size` int DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`),
  KEY `course_id` (`course_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `course_notes`
--

INSERT INTO `course_notes` (`note_id`, `course_id`, `lesson_id`, `file_url`, `description`, `file_size`, `file_type`, `created_at`) VALUES
(1, 1, 1, '/uploads/notes/css_concepts.pdf', 'Introduction to CSS Selectors and Box Model.', 1024, 'pdf', '2025-12-16 23:02:34'),
(2, 1, 2, '/uploads/notes/html_forms_guide.pdf', 'Detailed guide on HTML5 form validation.', 2048, 'pdf', '2025-12-16 23:02:34'),
(3, 2, 4, '/uploads/notes/bootstrap_intro.pptx', 'Bootstrap Grid System Presentation slides.', 5120, 'pptx', '2025-12-16 23:02:34'),
(4, 3, 11, '/uploads/notes/js_operators.pdf', 'Cheat sheet for JS Arithmetic and Logical operators.', 850, 'pdf', '2025-12-16 23:02:34'),
(5, 14, 54, '/uploads/notes/python_basics.zip', 'Source code examples for Python basics.', 15360, 'zip', '2025-12-16 23:02:34'),
(6, 19, 71, '/uploads/notes/linux_commands.txt', 'List of essential Linux shell commands.', 120, 'txt', '2025-12-16 23:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `course_tbl`
--

DROP TABLE IF EXISTS `course_tbl`;
CREATE TABLE IF NOT EXISTS `course_tbl` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `tutor_id` int NOT NULL,
  `category_id` int NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_slug` varchar(255) NOT NULL,
  `course_description` text,
  `course_thumbnail` varchar(255) DEFAULT NULL,
  `course_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `price` decimal(10,2) DEFAULT '0.00',
  `total_lesson` int DEFAULT '0',
  `course_status` tinyint DEFAULT '1',
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_slug` (`course_slug`),
  KEY `tutor_id` (`tutor_id`),
  KEY `category_id` (`category_id`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `course_tbl`
--

INSERT INTO `course_tbl` (`course_id`, `tutor_id`, `category_id`, `course_title`, `course_slug`, `course_description`, `course_thumbnail`, `course_level`, `price`, `total_lesson`, `course_status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 1, 1, 'Working with HTML and CSS', 'working-with-html-and-css', 'Learn the basics of HTML and CSS.', 'html_css.png', 'beginner', 1800.00, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(2, 1, 1, 'Design Web Sites Using Bootstrap', 'design-web-sites-using-bootstrap', 'Learn Basics of Bootstrap. Master responsive web design.', 'bootstrap.png', 'beginner', 1800.00, 25, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(3, 1, 1, 'Overview of Java Script', 'overview-of-java-script1', 'Understand core JavaScript. Get a solid introduction to JS.', 'javascript.png', 'intermediate', 1800.00, 30, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(4, 1, 1, 'JavaScript Objects', 'javascript-objects', 'Understand core JavaScript Objects. Explore JavaScript OOP.', 'javascript_objects.png', 'intermediate', 1800.00, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(5, 1, 1, 'JavaScript Functions', 'javascript-functions', 'Master advanced JavaScript functions. Learn how to use closures and async.', 'javascript_functions.png', 'advanced', 1800.00, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(6, 2, 3, 'Introduction to Java', 'introduction-to-java', 'Learn the basics of JAVA. Get started with Java programming.', 'java.png', 'beginner', 3000.00, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(7, 2, 3, 'Classes and Objects', 'classes-and-objects-java', 'Learn Basics of Classes and Objects in JAVA. Understand OOP concepts.', 'java_classes.png', 'beginner', 3000.00, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(8, 2, 3, 'Basic Concepts of Strings and Exceptions', 'basic-strings-exceptions-java', 'Understand core of String and Exceptions in JAVA.', 'java_strings.png', 'intermediate', 3000.00, 18, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(9, 2, 3, 'Threads and Packages', 'threads-and-packages-java', 'Understand core of Threads and Packages in JAVA.', 'java_threads.png', 'intermediate', 3000.00, 12, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(10, 3, 2, 'Core PHP Programming', 'core-php-programming', 'Learn the basics of PHP. Master the fundamentals of server-side scripting.', 'php.png', 'beginner', 3166.67, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(11, 3, 2, 'Advanced PHP and File Management', 'advanced-php-file-management', 'Understand core of PHP and File Management. Dive into advanced topics.', 'php_advanced.png', 'intermediate', 3166.67, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(12, 3, 2, 'Database Interaction and CodeIgniter Framework', 'database-interaction-codeigniter', 'Master advanced Database Interaction and CodeIgniter Framework.', 'codeigniter.png', 'advanced', 3166.66, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(13, 5, 4, 'Introduction to python', 'introduction-to-python', 'Learn the basics of python. Learn the fundamentals of programming.', 'python.png', 'beginner', 2300.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(14, 5, 4, 'Data Structures & Functions', 'python-data-structures-functions', 'Learn Basics of Data Structures & Functions. Explore Python data types.', 'python_dsa.png', 'beginner', 2300.00, 12, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(15, 5, 4, 'Python interaction with SQLite', 'python-interaction-with-sqlite', 'Understand core of Python interaction with SQLite.', 'sqlite.png', 'intermediate', 2300.00, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(16, 5, 4, 'Python Interaction with text and CSV', 'python-interaction-with-text-csv', 'Understand core of Python Interaction with text and CSV.', 'csv.png', 'intermediate', 2300.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(17, 5, 4, 'Data Visualization using dataframe', 'data-visualization-using-dataframe', 'Master advanced in Data Visualization using dataframe.', 'dataframe.png', 'advanced', 2300.00, 8, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(18, 4, 5, 'Introduction to Linux Operating System', 'introduction-to-linux-os', 'Learn the basics of LINUX. Get an overview of the OS.', 'linux.png', 'beginner', 1875.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(19, 4, 5, 'Basic Linux', 'basic-linux-commands', 'Learn Basics of LINUX Commands. Learn essential command-line usage.', 'linux_commands.png', 'beginner', 1875.00, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(20, 4, 5, 'Shell Scripting in Linux', 'shell-scripting-in-linux', 'Understand core of Shell Scripting in LINUX.', 'shell.png', 'intermediate', 1875.00, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(21, 4, 5, 'Advanced Text Processing Tools', 'advanced-text-processing-tools', 'Master advanced in Text Processing Tools in LINUX (Awk/Sed).', 'awk_sed.png', 'advanced', 1875.00, 25, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28');

-- --------------------------------------------------------

--
-- Table structure for table `daily_stats`
--

DROP TABLE IF EXISTS `daily_stats`;
CREATE TABLE IF NOT EXISTS `daily_stats` (
  `stat_id` int NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL,
  `total_active_users` int DEFAULT '0',
  `total_games_played` int DEFAULT '0',
  `total_sessions` int DEFAULT '0',
  `total_new_users` int DEFAULT '0',
  `most_popular_game_id` int DEFAULT NULL,
  `average_session_duration_seconds` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `unique_date` (`stat_date`),
  KEY `idx_date` (`stat_date` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duel_matches`
--

DROP TABLE IF EXISTS `duel_matches`;
CREATE TABLE IF NOT EXISTS `duel_matches` (
  `match_id` int NOT NULL AUTO_INCREMENT,
  `player1_id` int NOT NULL,
  `player2_id` int DEFAULT NULL,
  `match_status` enum('waiting','in_progress','completed','cancelled') DEFAULT 'waiting',
  `difficulty` enum('Easy','Medium','Hard') NOT NULL,
  `language` enum('PHP','Java','Python','JavaScript') NOT NULL,
  `round_count` int DEFAULT '3',
  `winner_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`match_id`),
  KEY `winner_id` (`winner_id`),
  KEY `idx_player1` (`player1_id`),
  KEY `idx_player2` (`player2_id`),
  KEY `idx_status` (`match_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duel_rankings`
--

DROP TABLE IF EXISTS `duel_rankings`;
CREATE TABLE IF NOT EXISTS `duel_rankings` (
  `ranking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `elo_rating` int DEFAULT '1200',
  `matches_played` int DEFAULT '0',
  `matches_won` int DEFAULT '0',
  `matches_lost` int DEFAULT '0',
  `win_rate` decimal(5,2) DEFAULT '0.00',
  `current_streak` int DEFAULT '0',
  `best_streak` int DEFAULT '0',
  `rank_position` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ranking_id`),
  UNIQUE KEY `unique_user` (`user_id`),
  KEY `idx_elo` (`elo_rating` DESC),
  KEY `idx_rank` (`rank_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duel_rounds`
--

DROP TABLE IF EXISTS `duel_rounds`;
CREATE TABLE IF NOT EXISTS `duel_rounds` (
  `round_id` int NOT NULL AUTO_INCREMENT,
  `match_id` int NOT NULL,
  `round_number` int NOT NULL,
  `challenge_id` int NOT NULL,
  `challenge_type` enum('debug','output_predict','code_complete','algorithm') NOT NULL,
  `player1_solved` tinyint(1) DEFAULT '0',
  `player2_solved` tinyint(1) DEFAULT '0',
  `player1_time` int DEFAULT NULL,
  `player2_time` int DEFAULT NULL,
  `player1_score` int DEFAULT '0',
  `player2_score` int DEFAULT '0',
  `round_winner_id` int DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`round_id`),
  KEY `round_winner_id` (`round_winner_id`),
  KEY `idx_match` (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments_tbl`
--

DROP TABLE IF EXISTS `enrollments_tbl`;
CREATE TABLE IF NOT EXISTS `enrollments_tbl` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `course_id` int DEFAULT NULL,
  `user_payment_id` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `enrollment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `enrolled_at` datetime DEFAULT NULL,
  `progress` int DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `certificate_issued` tinyint DEFAULT '0',
  PRIMARY KEY (`enrollment_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `user_payment_id` (`user_payment_id`),
  KEY `enrollments_tbl_ibfk_3` (`course_id`),
  KEY `enrollments_tbl_ibfk_1` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

DROP TABLE IF EXISTS `faq`;
CREATE TABLE IF NOT EXISTS `faq` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `status`, `created_at`, `updated_at`) VALUES
(1, 'How do I enroll in a course?', 'You can enroll by selecting a course, completing the payment, and starting the lessons immediately from your dashboard.', 1, '2025-12-19 16:34:10', '2025-12-19 16:34:10'),
(2, 'Do I get a certificate after course completion?', 'Yes, once you complete all lessons and assignments, a digital certificate will be issued automatically.', 1, '2025-12-19 16:34:10', '2025-12-19 16:34:10'),
(3, 'Can I access the course after completion?', 'Yes, enrolled courses remain accessible even after completion so you can revise anytime.', 1, '2025-12-19 16:34:10', '2025-12-19 16:34:10'),
(4, 'What happens if I fail an assignment?', 'You can resubmit the assignment if the tutor allows resubmission. Feedback will be provided for improvement.', 1, '2025-12-19 16:34:10', '2025-12-19 16:34:10'),
(5, 'Is there any refund policy?', 'Refunds are applicable only if requested within the allowed period as mentioned in our refund policy.', 1, '2025-12-19 16:34:10', '2025-12-19 16:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `category` enum('Debugging','Logic','Syntax','Speed','Puzzle','Database','Optimization','Competitive','Fun') NOT NULL,
  `description` text,
  `difficulty` enum('Easy','Medium','Hard','Expert') NOT NULL,
  `base_duration_minutes` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`game_id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_difficulty` (`difficulty`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `slug`, `name`, `icon`, `category`, `description`, `difficulty`, `base_duration_minutes`, `is_active`, `created_at`) VALUES
(1, 'debugging-master', 'Debugging Master', '🐛', 'Debugging', 'Find and fix bugs in code snippets', 'Medium', 45, 0, '2025-12-30 10:07:22'),
(2, 'code-output-predictor', 'Code Output Predictor', '🔮', 'Logic', 'Predict code execution results', 'Medium', 10, 1, '2025-12-30 10:07:22'),
(3, 'code-complete', 'Code Complete', '✏️', 'Syntax', 'Fill in missing code', 'Easy', 12, 0, '2025-12-30 10:07:22'),
(4, 'typing-master', 'Typing Master', '⌨️', 'Speed', 'Type code as fast as possible', 'Easy', 5, 1, '2025-12-30 10:07:22'),
(5, 'code-maze', 'Code Maze', '🧩', 'Puzzle', 'Navigate through code logic puzzles', 'Hard', 20, 1, '2025-12-30 10:07:22'),
(6, 'sql-query-master', 'SQL Query Master', '💾', 'Database', 'Write and fix SQL queries', 'Medium', 15, 1, '2025-12-30 10:07:22'),
(7, 'bug-race', 'Bug Race', '🏁', 'Speed', 'Race to fix bugs quickly', 'Hard', 10, 1, '2025-12-30 10:07:22'),
(8, 'code-refactor', 'Code Refactor Challenge', '🔧', 'Optimization', 'Transform messy code', 'Hard', 20, 0, '2025-12-30 10:07:22'),
(9, 'escape-room', 'Escape Room', '🚪', 'Puzzle', 'Solve puzzles to escape', 'Hard', 25, 1, '2025-12-30 10:07:22'),
(10, '1v1code', '1v1 Code Duel', '⚔️', 'Competitive', 'Real-time coding battles', 'Hard', 15, 1, '2025-12-30 10:07:22'),
(11, 'code-memes-guess', 'Code Memes Guess', '😂', 'Fun', 'Match memes with concepts', 'Easy', 8, 1, '2025-12-30 10:07:22'),
(12, 'guess-error', 'Guess The Error', '🎯', 'Debugging', 'Identify error types', 'Medium', 12, 1, '2025-12-30 10:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `game_challenges`
--

DROP TABLE IF EXISTS `game_challenges`;
CREATE TABLE IF NOT EXISTS `game_challenges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_id` int DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `challenges` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `game_challenges`
--

INSERT INTO `game_challenges` (`id`, `game_id`, `difficulty`, `challenges`) VALUES
(1, 10, 'easy', '{\"a\": \"object\", \"q\": \"typeof null returns?\"}'),
(2, 10, 'easy', '{\"a\": \"1\", \"q\": \"What is 10 % 3?\"}'),
(3, 10, 'easy', '{\"a\": \"parseInt\", \"q\": \"Method to convert string to number?\"}'),
(4, 10, 'easy', '{\"a\": \"false\", \"q\": \"What is !true?\"}'),
(5, 10, 'easy', '{\"a\": \"for\", \"q\": \"Loop to iterate array?\"}'),
(6, 10, 'easy', '{\"a\": \"8\", \"q\": \"What is 2 ** 3?\"}'),
(7, 10, 'easy', '{\"a\": \"pop\", \"q\": \"Method to remove last array item?\"}'),
(8, 10, 'easy', '{\"a\": \"55\", \"q\": \"What is \'5\' + 5?\"}'),
(9, 10, 'easy', '{\"a\": \"alphabetical\", \"q\": \"Default array sort order?\"}'),
(10, 10, 'easy', '{\"a\": \"3\", \"q\": \"What is [10,2,3].length?\"}'),
(11, 10, 'easy', '{\"a\": \"const\", \"q\": \"Keyword to declare constant?\"}'),
(12, 10, 'easy', '{\"a\": \"true\", \"q\": \"What is 5 == \'5\'?\"}'),
(13, 10, 'easy', '{\"a\": \"false\", \"q\": \"What is 5 === \'5\'?\"}'),
(14, 10, 'easy', '{\"a\": \"join\", \"q\": \"Method to join array items?\"}'),
(15, 10, 'easy', '{\"a\": \"4\", \"q\": \"What is Math.floor(4.7)?\"}'),
(16, 10, 'easy', '{\"a\": \"5\", \"q\": \"What is Math.ceil(4.1)?\"}'),
(17, 10, 'easy', '{\"a\": \"filter\", \"q\": \"Array method to filter items?\"}'),
(18, 10, 'easy', '{\"a\": \"false\", \"q\": \"What is true && false?\"}'),
(19, 10, 'easy', '{\"a\": \"8\", \"q\": \"What is 5 + 3?\"}'),
(20, 10, 'easy', '{\"a\": \"push\", \"q\": \"Array method to add item at end?\"}');

-- --------------------------------------------------------

--
-- Table structure for table `game_languages`
--

DROP TABLE IF EXISTS `game_languages`;
CREATE TABLE IF NOT EXISTS `game_languages` (
  `game_lang_id` int NOT NULL AUTO_INCREMENT,
  `game_id` int NOT NULL,
  `language` enum('PHP','HTML','Java','Python','Unix','SQL','JavaScript','CSS') NOT NULL,
  PRIMARY KEY (`game_lang_id`),
  UNIQUE KEY `unique_game_language` (`game_id`,`language`),
  KEY `idx_game` (`game_id`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game_sessions`
--

DROP TABLE IF EXISTS `game_sessions`;
CREATE TABLE IF NOT EXISTS `game_sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `game_id` int NOT NULL,
  `language` enum('PHP','HTML','Java','Python','Unix','SQL','JavaScript','CSS') DEFAULT NULL,
  `score` int DEFAULT '0',
  `time_taken_seconds` int DEFAULT NULL,
  `completed` tinyint(1) DEFAULT '0',
  `result` enum('WIN','LOSS','INCOMPLETE') DEFAULT 'INCOMPLETE',
  `accuracy_percentage` decimal(5,2) DEFAULT NULL,
  `combo_max` int DEFAULT '0',
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_game` (`game_id`),
  KEY `idx_score` (`score` DESC),
  KEY `idx_completed` (`completed_at`),
  KEY `idx_session_user_game` (`user_id`,`game_id`,`completed_at`),
  KEY `idx_session_score_time` (`score` DESC,`time_taken_seconds`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard_entries`
--

DROP TABLE IF EXISTS `leaderboard_entries`;
CREATE TABLE IF NOT EXISTS `leaderboard_entries` (
  `entry_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `game_id` int DEFAULT NULL,
  `period_type` enum('daily','weekly','monthly','all_time') NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `score` bigint DEFAULT '0',
  `games_played` int DEFAULT '0',
  `win_rate` decimal(5,2) DEFAULT '0.00',
  `rank_position` int DEFAULT NULL,
  `trend` enum('up','down','same','new') DEFAULT 'same',
  `rank_change` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entry_id`),
  UNIQUE KEY `unique_leaderboard_entry` (`user_id`,`game_id`,`period_type`,`period_start`),
  KEY `game_id` (`game_id`),
  KEY `idx_period` (`period_type`,`period_start`,`period_end`),
  KEY `idx_score` (`score` DESC),
  KEY `idx_rank` (`rank_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons_tbl`
--

DROP TABLE IF EXISTS `lessons_tbl`;
CREATE TABLE IF NOT EXISTS `lessons_tbl` (
  `lesson_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `category_id` int NOT NULL,
  `lesson_title` varchar(255) NOT NULL,
  `lesson_slug` varchar(255) NOT NULL,
  `lesson_order` int DEFAULT NULL,
  `lesson_url` varchar(500) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `content` text,
  `status` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lesson_id`),
  KEY `course_id` (`course_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lessons_tbl`
--

INSERT INTO `lessons_tbl` (`lesson_id`, `course_id`, `category_id`, `lesson_title`, `lesson_slug`, `lesson_order`, `lesson_url`, `duration`, `content`, `status`, `created_at`) VALUES
(1, 1, 1, 'Lesson 1: Concepts of CSS', 'lesson-1-concepts-of-css', 1, '/courses/1/lesson-1-concepts-of-css', NULL, 'Content for Concepts of CSS.', 1, '2025-12-17 11:02:49'),
(2, 1, 1, 'Lesson 2: HTML forms', 'lesson-2-html-forms', 2, '/courses/1/lesson-2-html-forms', NULL, 'Content for HTML forms.', 1, '2025-12-17 11:02:49'),
(3, 1, 1, 'Lesson 3: Images, Media Objects', 'lesson-3-images-media-objects', 3, '/courses/1/lesson-3-images-media-objects', NULL, 'Content for Images and Media Objects.', 1, '2025-12-17 11:02:49'),
(4, 2, 1, 'Lesson 1: Bootstrap Introduction', 'lesson-1-bootstrap-introduction', 1, '/courses/2/lesson-1-bootstrap-introduction', NULL, 'Content for Bootstrap Introduction.', 1, '2025-12-17 11:03:26'),
(5, 2, 1, 'Lesson 2: Grid Structure', 'lesson-2-grid-structure', 2, '/courses/2/lesson-2-grid-structure', NULL, 'Content for Grid Structure.', 1, '2025-12-17 11:03:26'),
(6, 2, 1, 'Lesson 3: Table, Colours, Alerts, Form Controls', 'lesson-3-table-colours-alerts-form-controls', 3, '/courses/2/lesson-3-table-colours-alerts-form-controls', NULL, 'Content for Table, Colours, Alerts, Form Controls.', 1, '2025-12-17 11:03:26'),
(7, 2, 1, 'Lesson 4: Buttons and Button Groups', 'lesson-4-buttons-and-button-groups', 4, '/courses/2/lesson-4-buttons-and-button-groups', NULL, 'Content for Buttons and Button Groups.', 1, '2025-12-17 11:03:26'),
(8, 2, 1, 'Lesson 6: Pagination', 'lesson-6-pagination', 6, '/courses/2/lesson-6-pagination', NULL, 'Content for Pagination.', 1, '2025-12-17 11:03:26'),
(9, 2, 1, 'Lesson 7: Bootstrap Grids', 'lesson-7-bootstrap-grids', 7, '/courses/2/lesson-7-bootstrap-grids', NULL, 'Content for Bootstrap Grids.', 1, '2025-12-17 11:03:26'),
(10, 2, 1, 'Lesson 8: Bootstrap Themes', 'lesson-8-bootstrap-themes', 8, '/courses/2/lesson-8-bootstrap-themes', NULL, 'Content for Bootstrap Themes.', 1, '2025-12-17 11:03:26'),
(11, 3, 1, 'Lesson 9: Overview of Client & Server-Side Scripti...', 'lesson-9-overview-of-client-server-side-scripting', 1, '/courses/3/lesson-9-overview-of-client-server-side-scripting', NULL, 'Content for Client & Server-Side Scripting.', 1, '2025-12-17 11:03:26'),
(12, 3, 1, 'Lesson 10: Data types and Variables', 'lesson-10-data-types-and-variables', 2, '/courses/3/lesson-10-data-types-and-variables', NULL, 'Content for Data types and Variables.', 1, '2025-12-17 11:03:26'),
(13, 3, 1, 'Lesson 11: Operators (Arthimetic, Assignment, Comp...', 'lesson-11-operators-arithmetic-assignment-comparison', 3, '/courses/3/lesson-11-operators-arithmetic-assignment-comparison', NULL, 'Content for Operators.', 1, '2025-12-17 11:03:26'),
(14, 3, 1, 'Lesson 12: Control Structure', 'lesson-12-control-structure', 4, '/courses/3/lesson-12-control-structure', NULL, 'Content for Control Structure.', 1, '2025-12-17 11:03:26'),
(15, 3, 1, 'Lesson 13: Java Script String and Events', 'lesson-13-java-script-string-and-events', 5, '/courses/3/lesson-13-java-script-string-and-events', NULL, 'Content for Java Script String and Events.', 1, '2025-12-17 11:03:26'),
(16, 4, 1, 'Lesson 12: Creating object', 'lesson-12-creating-object', 1, '/courses/4/lesson-12-creating-object', NULL, 'Content for Creating object.', 1, '2025-12-17 11:03:26'),
(17, 4, 1, 'Lesson 13: Date object', 'lesson-13-date-object', 2, '/courses/4/lesson-13-date-object', NULL, 'Content for Date object.', 1, '2025-12-17 11:03:26'),
(18, 4, 1, 'Lesson 14: Document Object Model (DOM)', 'lesson-14-document-object-model-dom', 3, '/courses/4/lesson-14-document-object-model-dom', NULL, 'Content for Document Object Model (DOM).', 1, '2025-12-17 11:03:26'),
(19, 5, 1, 'Lesson 15: JavaScript Functions', 'lesson-15-javascript-functions', 1, '/courses/5/lesson-15-javascript-functions', NULL, 'Content for JavaScript Functions.', 1, '2025-12-17 11:03:26'),
(20, 5, 1, 'Lesson 16: Dialog boxes : Alert, confirm, prompt', 'lesson-16-dialog-boxes-alert-confirm-prompt', 2, '/courses/5/lesson-16-dialog-boxes-alert-confirm-prompt', NULL, 'Content for Dialog boxes.', 1, '2025-12-17 11:03:26'),
(21, 5, 1, 'Lesson 17: Form validation', 'lesson-17-form-validation', 3, '/courses/5/lesson-17-form-validation', NULL, 'Content for Form validation.', 1, '2025-12-17 11:03:26'),
(22, 6, 3, 'Lesson 18: Properties of Java', 'lesson-18-properties-of-java', 1, '/courses/6/lesson-18-properties-of-java', NULL, 'Content for Properties of Java.', 1, '2025-12-17 11:03:26'),
(23, 6, 3, 'Lesson 19: Comparison of java with C++', 'lesson-19-comparison-of-java-with-c++', 2, '/courses/6/lesson-19-comparison-of-java-with-c++', NULL, 'Content for Comparison of java with C++.', 1, '2025-12-17 11:03:26'),
(24, 6, 3, 'Lesson 20: Java Compiler, Java Interpreter', 'lesson-20-java-compiler-java-interpreter', 3, '/courses/6/lesson-20-java-compiler-java-interpreter', NULL, 'Content for Java Compiler, Java Interpreter.', 1, '2025-12-17 11:03:26'),
(25, 6, 3, 'Lesson 21: Identifier, Literals, Operators, Variab...', 'lesson-21-identifier-literals-operators-variables', 4, '/courses/6/lesson-21-identifier-literals-operators-variables', NULL, 'Content for Identifier, Literals, Operators, Variables.', 1, '2025-12-17 11:03:26'),
(26, 6, 3, 'Lesson 22: Branching: If – Else, Switch', 'lesson-22-branching-if-else-switch', 5, '/courses/6/lesson-22-branching-if-else-switch', NULL, 'Content for Branching.', 1, '2025-12-17 11:03:26'),
(27, 6, 3, 'Lesson 23: Looping: While, Do-while, For', 'lesson-23-looping-while-do-while-for', 6, '/courses/6/lesson-23-looping-while-do-while-for', NULL, 'Content for Looping.', 1, '2025-12-17 11:03:26'),
(28, 8, 3, 'Lesson 24: Type Casting', 'lesson-24-type-casting', 3, '/courses/8/lesson-24-type-casting', NULL, 'Content for Type Casting.', 1, '2025-12-17 11:03:26'),
(29, 8, 3, 'Lesson 29: Static members, static block, static cl...', 'lesson-29-static-members-static-block-static-class', 4, '/courses/8/lesson-29-static-members-static-block-static-class', NULL, 'Content for Static members.', 1, '2025-12-17 11:03:26'),
(30, 8, 3, 'Lesson 30: Interfaces', 'lesson-30-interfaces', 5, '/courses/8/lesson-30-interfaces', NULL, 'Content for Interfaces.', 1, '2025-12-17 11:03:26'),
(31, 8, 3, 'Lesson 31: Strings', 'lesson-31-strings', 1, '/courses/8/lesson-31-strings', NULL, 'Content for Strings.', 1, '2025-12-17 11:03:26'),
(32, 8, 3, 'Lesson 32: Introduction to Exceptions', 'lesson-32-introduction-to-exceptions', 2, '/courses/8/lesson-32-introduction-to-exceptions', NULL, 'Content for Introduction to Exceptions.', 1, '2025-12-17 11:03:26'),
(33, 7, 3, 'Lesson 25: Simple Class, Field', 'lesson-25-simple-class-field', 1, '/courses/7/lesson-25-simple-class-field', NULL, 'Content for Simple Class, Field.', 1, '2025-12-17 11:03:26'),
(34, 7, 3, 'Lesson 26: Access Controls, Object creation', 'lesson-26-access-controls-object-creation', 2, '/courses/7/lesson-26-access-controls-object-creation', NULL, 'Content for Access Controls, Object creation.', 1, '2025-12-17 11:03:26'),
(35, 7, 3, 'Lesson 27: Construction and Initialization', 'lesson-27-construction-and-initialization', 3, '/courses/7/lesson-27-construction-and-initialization', NULL, 'Content for Construction and Initialization.', 1, '2025-12-17 11:03:26'),
(36, 7, 3, 'Lesson 27: Inheritance and Polymorphism in Java', 'lesson-27-inheritance-and-polymorphism-in-java', 4, '/courses/7/lesson-27-inheritance-and-polymorphism-in-java', NULL, 'Content for Inheritance and Polymorphism in Java.', 1, '2025-12-17 11:03:26'),
(37, 7, 3, 'Lesson 28: this and super keywords', 'lesson-28-this-and-super-keywords', 5, '/courses/7/lesson-28-this-and-super-keywords', NULL, 'Content for this and super keywords.', 1, '2025-12-17 11:03:26'),
(38, 9, 3, 'Lesson 33: Thread', 'lesson-33-thread', 1, '/courses/9/lesson-33-thread', NULL, 'Content for Thread.', 1, '2025-12-17 11:03:26'),
(39, 9, 3, 'Lesson 34: Package Naming, Type Imports', 'lesson-34-package-naming-type-imports', 2, '/courses/9/lesson-34-package-naming-type-imports', NULL, 'Content for Package Naming, Type Imports.', 1, '2025-12-17 11:03:26'),
(40, 9, 3, 'Lesson 35: Implementation of Data Structure using ...', 'lesson-35-implementation-of-data-structure-using-java', 3, '/courses/9/lesson-35-implementation-of-data-structure-using-java', NULL, 'Content for Implementation of Data Structure.', 1, '2025-12-17 11:03:26'),
(41, 9, 3, 'Lesson 36: Applet Basics, Applet Architecture', 'lesson-36-applet-basics-applet-architecture', 4, '/courses/9/lesson-36-applet-basics-applet-architecture', NULL, 'Content for Applet Basics, Applet Architecture.', 1, '2025-12-17 11:03:26'),
(42, 11, 2, 'Lesson 37: Introduction to PHP', 'lesson-37-introduction-to-php', 1, '/courses/11/lesson-37-introduction-to-php', NULL, 'Content for Introduction to PHP.', 1, '2025-12-17 11:03:26'),
(43, 11, 2, 'Lesson 38: Basic PHP Syntax and Variables', 'lesson-38-basic-php-syntax-and-variables', 2, '/courses/11/lesson-38-basic-php-syntax-and-variables', NULL, 'Content for Basic PHP Syntax and Variables.', 1, '2025-12-17 11:03:26'),
(44, 11, 2, 'Lesson 39: Data Types and Operators', 'lesson-39-data-types-and-operators', 3, '/courses/11/lesson-39-data-types-and-operators', NULL, 'Content for Data Types and Operators.', 1, '2025-12-17 11:03:26'),
(45, 11, 2, 'Lesson 40: Control Structures and Arrays', 'lesson-40-control-structures-and-arrays', 4, '/courses/11/lesson-40-control-structures-and-arrays', NULL, 'Content for Control Structures and Arrays.', 1, '2025-12-17 11:03:26'),
(46, 11, 2, 'Lesson 41: Functions and Form Handling', 'lesson-41-functions-and-form-handling', 5, '/courses/11/lesson-41-functions-and-form-handling', NULL, 'Content for Functions and Form Handling.', 1, '2025-12-17 11:03:26'),
(47, 12, 2, 'Lesson 42: File Handling and Directories', 'lesson-42-file-handling-and-directories', 1, '/courses/12/lesson-42-file-handling-and-directories', NULL, 'Content for File Handling and Directories.', 1, '2025-12-17 11:03:26'),
(48, 12, 2, 'Lesson 43: Forms, Filters, and JSON', 'lesson-43-forms-filters-and-json', 2, '/courses/12/lesson-43-forms-filters-and-json', NULL, 'Content for Forms, Filters, and JSON.', 1, '2025-12-17 11:03:26'),
(49, 12, 2, 'Lesson 44: Cookies, Sessions, and Emails', 'lesson-44-cookies-sessions-and-emails', 3, '/courses/12/lesson-44-cookies-sessions-and-emails', NULL, 'Content for Cookies, Sessions, and Emails.', 1, '2025-12-17 11:03:26'),
(50, 12, 2, 'Lesson 45: OOP and Exception Handling in PHP', 'lesson-45-oop-and-exception-handling-in-php', 4, '/courses/12/lesson-45-oop-and-exception-handling-in-php', NULL, 'Content for OOP and Exception Handling in PHP.', 1, '2025-12-17 11:03:26'),
(51, 13, 2, 'Lesson 46: PHP with MySQL/MongoDB', 'lesson-46-php-with-mysql-mongodb', 1, '/courses/13/lesson-46-php-with-mysql-mongodb', NULL, 'Content for PHP with MySQL/MongoDB.', 1, '2025-12-17 11:03:26'),
(52, 13, 2, 'Lesson 47: AJAX for Backend Integration', 'lesson-47-ajax-for-backend-integration', 2, '/courses/13/lesson-47-ajax-for-backend-integration', NULL, 'Content for AJAX for Backend Integration.', 1, '2025-12-17 11:03:26'),
(53, 13, 2, 'Lesson 48: CodeIgniter Introduction', 'lesson-48-codeigniter-introduction', 3, '/courses/13/lesson-48-codeigniter-introduction', NULL, 'Content for CodeIgniter Introduction.', 1, '2025-12-17 11:03:26'),
(54, 13, 2, 'Lesson 49: Core Features in CodeIgniter', 'lesson-49-core-features-in-codeigniter', 4, '/courses/13/lesson-49-core-features-in-codeigniter', NULL, 'Content for Core Features in CodeIgniter.', 1, '2025-12-17 11:03:26'),
(55, 14, 4, 'Lesson 50: Getting Started with Python', 'lesson-50-getting-started-with-python', 1, '/courses/14/lesson-50-getting-started-with-python', NULL, 'Content for Getting Started with Python.', 1, '2025-12-17 11:03:26'),
(56, 14, 4, 'Lesson 51: Python Basics', 'lesson-51-python-basics', 2, '/courses/14/lesson-51-python-basics', NULL, 'Content for Python Basics.', 1, '2025-12-17 11:03:26'),
(57, 14, 4, 'Lesson 52: Control Flow Statements', 'lesson-52-control-flow-statements', 3, '/courses/14/lesson-52-control-flow-statements', NULL, 'Content for Control Flow Statements.', 1, '2025-12-17 11:03:26'),
(58, 16, 4, 'Lesson 53: Input and Output', 'lesson-53-input-and-output', 3, '/courses/16/lesson-53-input-and-output', NULL, 'Content for Input and Output.', 1, '2025-12-17 11:03:26'),
(59, 16, 4, 'Lesson 57: Module: Concepts of module and Using mo...', 'lesson-57-module-concepts-of-module-and-using-modules', 1, '/courses/16/lesson-57-module-concepts-of-module-and-using-modules', NULL, 'Content for Module Concepts.', 1, '2025-12-17 11:03:26'),
(60, 16, 4, 'Lesson 58: Importing sqlite3 module', 'lesson-58-importing-sqlite3-module', 2, '/courses/16/lesson-58-importing-sqlite3-module', NULL, 'Content for Importing sqlite3 module.', 1, '2025-12-17 11:03:26'),
(61, 15, 4, 'Lesson 54: Defining and calling functions', 'lesson-54-defining-and-calling-functions', 1, '/courses/15/lesson-54-defining-and-calling-functions', NULL, 'Content for Defining and calling functions.', 1, '2025-12-17 11:03:26'),
(62, 15, 4, 'Lesson 55: Parameters, return statement', 'lesson-55-parameters-return-statement', 2, '/courses/15/lesson-55-parameters-return-statement', NULL, 'Content for Parameters, return statement.', 1, '2025-12-17 11:03:26'),
(63, 15, 4, 'Lesson 56: Arrays and String Formatting', 'lesson-56-arrays-and-string-formatting', 3, '/courses/15/lesson-56-arrays-and-string-formatting', NULL, 'Content for Arrays and String Formatting.', 1, '2025-12-17 11:03:26'),
(64, 17, 4, 'Lesson 59: File handling ( text and CSV files) usi...', 'lesson-59-file-handling-text-and-csv-files', 1, '/courses/17/lesson-59-file-handling-text-and-csv-files', NULL, 'Content for File handling.', 1, '2025-12-17 11:03:26'),
(65, 17, 4, 'Lesson 60: Important Classes and Functions of CSV ...', 'lesson-60-important-classes-and-functions-of-csv-module', 2, '/courses/17/lesson-60-important-classes-and-functions-of-csv-module', NULL, 'Content for Important Classes and Functions of CSV.', 1, '2025-12-17 11:03:26'),
(66, 17, 4, 'Lesson 61: Dataframe Handling using Panda and Nump...', 'lesson-61-dataframe-handling-using-panda-and-numpy', 3, '/courses/17/lesson-61-dataframe-handling-using-panda-and-numpy', NULL, 'Content for Dataframe Handling.', 1, '2025-12-17 11:03:26'),
(67, 18, 4, 'Lesson 62: Importing matplotlib.pyplot and plottin...', 'lesson-62-importing-matplotlib-pyplot-and-plotting', 1, '/courses/18/lesson-62-importing-matplotlib-pyplot-and-plotting', NULL, 'Content for Importing matplotlib.pyplot.', 1, '2025-12-17 11:03:26'),
(68, 18, 4, 'Lesson 63: Scatter plot: concept of Scatter plot, ...', 'lesson-63-scatter-plot-concept-of-scatter-plot', 2, '/courses/18/lesson-63-scatter-plot-concept-of-scatter-plot', NULL, 'Content for Scatter plot.', 1, '2025-12-17 11:03:26'),
(69, 18, 4, 'Lesson 64: Line chart : Concept of line plot: plot...', 'lesson-64-line-chart-concept-of-line-plot', 3, '/courses/18/lesson-64-line-chart-concept-of-line-plot', NULL, 'Content for Line chart.', 1, '2025-12-17 11:03:26'),
(70, 18, 4, 'Lesson 65: Histogram chart : Concepts of histogram...', 'lesson-65-histogram-chart-concepts-of-histogram', 4, '/courses/18/lesson-65-histogram-chart-concepts-of-histogram', NULL, 'Content for Histogram chart.', 1, '2025-12-17 11:03:26'),
(71, 18, 4, 'Lesson 66: Bar Chart : Concepts of Bar chart, bar(...', 'lesson-66-bar-chart-concepts-of-bar-chart', 5, '/courses/18/lesson-66-bar-chart-concepts-of-bar-chart', NULL, 'Content for Bar Chart.', 1, '2025-12-17 11:03:26'),
(72, 19, 5, 'Lesson 67: Features of Linux OS', 'lesson-67-features-of-linux-os', 1, '/courses/19/lesson-67-features-of-linux-os', NULL, 'Content for Features of Linux OS.', 1, '2025-12-17 11:03:26'),
(73, 19, 5, 'Lesson 68: Components of Linux OS (Hardware, Kern...', 'lesson-68-components-of-linux-os', 2, '/courses/19/lesson-68-components-of-linux-os', NULL, 'Content for Components of Linux OS.', 1, '2025-12-17 11:03:26'),
(74, 19, 5, 'Lesson 69: Shell in Linux (Bash, Zsh, Dash – Feat...', 'lesson-69-shell-in-linux', 3, '/courses/19/lesson-69-shell-in-linux', NULL, 'Content for Shell in Linux.', 1, '2025-12-17 11:03:26'),
(75, 19, 5, 'Lesson 70: Introduction to Files and File Types in...', 'lesson-70-introduction-to-files-and-file-types-in-linux', 4, '/courses/19/lesson-70-introduction-to-files-and-file-types', NULL, 'Content for Files and File Types in Linux.', 1, '2025-12-17 11:03:26'),
(76, 19, 5, 'Lesson 71: Linux Directory Structure and File Syst...', 'lesson-71-linux-directory-structure-and-file-system', 5, '/courses/19/lesson-71-linux-directory-structure-and-file-system', NULL, 'Content for Linux Directory Structure.', 1, '2025-12-17 11:03:26'),
(77, 20, 5, 'Lesson 72: Directory Navigation Commands (pwd, cd...', 'lesson-72-directory-navigation-commands', 1, '/courses/20/lesson-72-directory-navigation-commands', NULL, 'Content for Directory Navigation Commands.', 1, '2025-12-17 11:03:26'),
(78, 20, 5, 'Lesson 73: File Management Commands (cat, rm, cp, ...', 'lesson-73-file-management-commands', 2, '/courses/20/lesson-73-file-management-commands', NULL, 'Content for File Management Commands.', 1, '2025-12-17 11:03:26'),
(79, 20, 5, 'Lesson 74: File Permissions and Ownership (chmod, ...', 'lesson-74-file-permissions-and-ownership', 3, '/courses/20/lesson-74-file-permissions-and-ownership', NULL, 'Content for File Permissions and Ownership.', 1, '2025-12-17 11:03:26'),
(80, 20, 5, 'Lesson 75: Common System Commands (who, whoami, ma...', 'lesson-75-common-system-commands', 4, '/courses/20/lesson-75-common-system-commands', NULL, 'Content for Common System Commands.', 1, '2025-12-17 11:03:26'),
(81, 20, 5, 'Lesson 76: Text Processing Commands (head, tail, c...', 'lesson-76-text-processing-commands', 5, '/courses/20/lesson-76-text-processing-commands', NULL, 'Content for Text Processing Commands.', 1, '2025-12-17 11:03:26'),
(82, 20, 5, 'Lesson 77: Introduction to Process', 'lesson-77-introduction-to-process', 6, '/courses/20/lesson-77-introduction-to-process', NULL, 'Content for Introduction to Process.', 1, '2025-12-17 11:03:26'),
(83, 20, 5, 'Lesson 78: Process Control commands : ps, fg, bg, ...', 'lesson-78-process-control-commands', 7, '/courses/20/lesson-78-process-control-commands', NULL, 'Content for Process Control commands.', 1, '2025-12-17 11:03:26'),
(84, 20, 5, 'Lesson 79: Job Scheduling commands : at, batch, cr...', 'lesson-79-job-scheduling-commands', 8, '/courses/20/lesson-79-job-scheduling-commands', NULL, 'Content for Job Scheduling commands.', 1, '2025-12-17 11:03:26'),
(85, 21, 5, 'Lesson 80: Creating and Executing Shell Scripts (n...', 'lesson-80-creating-and-executing-shell-scripts', 1, '/courses/21/lesson-80-creating-and-executing-shell-scripts', NULL, 'Content for Creating and Executing Shell Scripts.', 1, '2025-12-17 11:03:26'),
(86, 21, 5, 'Lesson 81: Shell Metacharacters and Operators', 'lesson-81-shell-metacharacters-and-operators', 2, '/courses/21/lesson-81-shell-metacharacters-and-operators', NULL, 'Content for Shell Metacharacters and Operators.', 1, '2025-12-17 11:03:26'),
(87, 21, 5, 'Lesson 82: Control Flow Structures (if-else, case,...', 'lesson-82-control-flow-structures', 3, '/courses/21/lesson-82-control-flow-structures', NULL, 'Content for Control Flow Structures.', 1, '2025-12-17 11:03:26'),
(88, 21, 5, 'Lesson 83: Logical Operators (&&, ||, !)', 'lesson-83-logical-operators', 4, '/courses/21/lesson-83-logical-operators', NULL, 'Content for Logical Operators.', 1, '2025-12-17 11:03:26'),
(89, 21, 5, 'Lesson 84: test and [ ] command for Condition Test...', 'lesson-84-test-and-command-for-condition-testing', 5, '/courses/21/lesson-84-test-and-command-for-condition-testing', NULL, 'Content for Condition Testing.', 1, '2025-12-17 11:03:26'),
(90, 21, 5, 'Lesson 85: Arithmetic Operations (expr, $(( )))', 'lesson-85-arithmetic-operations', 6, '/courses/21/lesson-85-arithmetic-operations', NULL, 'Content for Arithmetic Operations.', 1, '2025-12-17 11:03:26'),
(91, 21, 5, 'Lesson 86: Introduction to Regular Expressions (Ba...', 'lesson-86-introduction-to-regular-expressions', 1, '/courses/21/lesson-86-introduction-to-regular-expressions', NULL, 'Content for Introduction to Regular Expressions.', 1, '2025-12-17 11:03:26'),
(92, 21, 5, 'Lesson 87: Pattern Matching using grep, egrep, and...', 'lesson-87-pattern-matching-using-grep-egrep-and-fgrep', 2, '/courses/21/lesson-87-pattern-matching-using-grep', NULL, 'Content for Pattern Matching using grep.', 1, '2025-12-17 11:03:26'),
(93, 21, 5, 'Lesson 88: Stream Editing with sed (search, replac...', 'lesson-88-stream-editing-with-sed', 3, '/courses/21/lesson-88-stream-editing-with-sed', NULL, 'Content for Stream Editing with sed.', 1, '2025-12-17 11:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `notification_tbl`
--

DROP TABLE IF EXISTS `notification_tbl`;
CREATE TABLE IF NOT EXISTS `notification_tbl` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `user_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notification_type` enum('System','Course','Payment') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `fk_notification_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification_tbl`
--

INSERT INTO `notification_tbl` (`notification_id`, `message`, `user_id`, `is_read`, `timestamp`, `notification_type`, `created_at`) VALUES
(1, 'Your payment of 9000.00 for HTML/Web has been successful.', 1, 1, '2025-12-17 01:58:25', 'Payment', '2025-12-24 11:51:01'),
(2, 'Welcome to the Java Masterclass! Your first lesson is now available.', 2, 1, '2025-12-17 01:58:25', 'Course', '2025-12-24 11:51:01'),
(3, 'Maintenance Alert: The platform will be down for 2 hours tonight.', 3, 0, '2025-12-17 01:58:25', 'System', '2025-12-24 11:51:01'),
(4, 'Refund Processed: Your request for the Python bonus module has been approved.', 4, 1, '2025-12-17 01:58:25', 'Payment', '2025-12-24 11:51:01'),
(5, 'Congratulations! You have completed 5% of the Linux Administration course.', 5, 0, '2025-12-17 01:58:25', 'Course', '2025-12-24 11:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `package_tbl`
--

DROP TABLE IF EXISTS `package_tbl`;
CREATE TABLE IF NOT EXISTS `package_tbl` (
  `package_id` int NOT NULL AUTO_INCREMENT,
  `package_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `valid_months` int NOT NULL,
  `can_add_courses` tinyint DEFAULT '0',
  `can_add_videos` tinyint DEFAULT '0',
  `can_add_quiz` tinyint DEFAULT '0',
  `can_add_games` tinyint DEFAULT '0',
  `can_add_assignments` tinyint DEFAULT '0',
  `max_course` int DEFAULT '0',
  `max_video_upload` int DEFAULT '0',
  `package_status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `package_tbl`
--

INSERT INTO `package_tbl` (`package_id`, `package_name`, `price`, `valid_months`, `can_add_courses`, `can_add_videos`, `can_add_quiz`, `can_add_games`, `can_add_assignments`, `max_course`, `max_video_upload`, `package_status`, `created_at`) VALUES
(1, 'Basic', 6500.00, 6, 1, 1, 0, 0, 0, 4, 6, 1, '2025-12-16 11:25:10'),
(2, 'Pro', 8500.00, 6, 1, 1, 1, 0, 0, 8, 12, 1, '2025-12-16 11:26:08'),
(3, 'Premium', 12000.00, 6, 1, 1, 1, 1, 1, 12, 24, 1, '2025-12-16 11:27:10'),
(4, 'Premium Go', 2800.00, 1, 1, 1, 1, 1, 1, 12, 24, 1, '2025-12-16 11:27:10'),
(5, 'Pro Gro', 1800.00, 1, 1, 1, 1, 0, 0, 8, 12, 1, '2025-12-16 11:26:08'),
(6, 'Basic Go', 1200.00, 1, 1, 1, 0, 0, 0, 4, 6, 1, '2025-12-16 11:25:10'),
(7, 'Booster', 1250.00, 0, 1, 1, 0, 0, 0, 5, 6, 0, '2026-01-26 09:53:03');

-- --------------------------------------------------------

--
-- Table structure for table `state_tbl`
--

DROP TABLE IF EXISTS `state_tbl`;
CREATE TABLE IF NOT EXISTS `state_tbl` (
  `state_id` int NOT NULL AUTO_INCREMENT,
  `state_name` varchar(100) NOT NULL,
  `state_status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`state_id`),
  UNIQUE KEY `state_name` (`state_name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `state_tbl`
--

INSERT INTO `state_tbl` (`state_id`, `state_name`, `state_status`, `created_at`, `updated_at`) VALUES
(1, 'ANDHRA PRADESH', 1, '2025-12-02 10:35:41', '2025-12-22 12:27:38'),
(2, 'ASSAM', 1, '2025-12-02 10:35:41', '2025-12-22 12:27:44'),
(3, 'ARUNACHAL PRADESH', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(4, 'BIHAR', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(5, 'GUJRAT', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(6, 'HARYANA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(7, 'HIMACHAL PRADESH', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(8, 'JAMMU & KASHMIR', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(9, 'KARNATAKA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(10, 'KERALA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(11, 'MADHYA PRADESH', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(12, 'MAHARASHTRA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(13, 'MANIPUR', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(14, 'MEGHALAYA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(15, 'MIZORAM', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(16, 'NAGALAND', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(17, 'ORISSA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(18, 'PUNJAB', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(19, 'RAJASTHAN', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(20, 'SIKKIM', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(21, 'TAMIL NADU', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(22, 'TRIPURA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(23, 'UTTAR PRADESH', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(24, 'WEST BENGAL', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(25, 'DELHI', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(26, 'GOA', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(27, 'PONDICHERY', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(28, 'LAKSHDWEEP', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(29, 'DAMAN & DIU', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(30, 'DADRA & NAGAR', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(31, 'CHANDIGARH', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(32, 'ANDAMAN & NICOBAR', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(33, 'UTTARANCHAL', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(34, 'JHARKHAND', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41'),
(35, 'CHATTISGARH', 1, '2025-12-02 10:35:41', '2025-12-02 10:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `tutor_details`
--

DROP TABLE IF EXISTS `tutor_details`;
CREATE TABLE IF NOT EXISTS `tutor_details` (
  `tutor_details_id` int NOT NULL AUTO_INCREMENT,
  `tutor_id` int NOT NULL,
  `degree_name` varchar(150) DEFAULT NULL,
  `clg_name` varchar(150) DEFAULT NULL,
  `degree_image` varchar(255) DEFAULT NULL,
  `passing_year` year DEFAULT NULL,
  `certificate_name` varchar(150) DEFAULT NULL,
  `certificate_image` varchar(255) DEFAULT NULL,
  `institute_name` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`tutor_details_id`),
  KEY `tutor_id` (`tutor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_details`
--

INSERT INTO `tutor_details` (`tutor_details_id`, `tutor_id`, `degree_name`, `clg_name`, `degree_image`, `passing_year`, `certificate_name`, `certificate_image`, `institute_name`) VALUES
(1, 1, 'M.Tech in Computer Science', 'Nirma University', 'smita_degree.pdf', '2018', 'Front-End Web Certification', 'smita_cert.jpg', 'Google Developer Academy'),
(2, 2, 'M.Tech in Computer Science', 'IIT Indore', 'deepa_degree.pdf', '2019', 'PHP & MySQL Certification', 'deepa_cert.jpg', 'Udemy'),
(3, 3, 'M.Sc. Computer Science', 'University of Rajasthan', 'rahul_degree.pdf', '2020', 'Java Professional Certification', 'rahul_cert.jpg', 'Oracle Academy'),
(4, 4, 'B.Tech in Information Technology', 'VTU University', 'kiran_degree.pdf', '2016', 'Linux System Administration', 'kiran_cert.jpg', 'Red Hat');

-- --------------------------------------------------------

--
-- Table structure for table `tutor_package_tbl`
--

DROP TABLE IF EXISTS `tutor_package_tbl`;
CREATE TABLE IF NOT EXISTS `tutor_package_tbl` (
  `purchase_id` int NOT NULL AUTO_INCREMENT,
  `tutor_id` int NOT NULL,
  `package_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_status` tinyint DEFAULT '1',
  `razorpay_id` varchar(100) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`purchase_id`),
  KEY `fk_tpkg_tutor` (`tutor_id`),
  KEY `fk_tpkg_package` (`package_id`),
  KEY `fk_tpkg_razorpay` (`razorpay_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_package_tbl`
--

INSERT INTO `tutor_package_tbl` (`purchase_id`, `tutor_id`, `package_id`, `start_date`, `end_date`, `payment_status`, `razorpay_id`, `amount_paid`, `created_at`) VALUES
(1, 2, 1, '2025-12-23', '2026-02-10', 1, 'ddfdggg', 3000.00, '2025-12-23 10:46:47');

-- --------------------------------------------------------

--
-- Table structure for table `tutor_profile_tbl`
--

DROP TABLE IF EXISTS `tutor_profile_tbl`;
CREATE TABLE IF NOT EXISTS `tutor_profile_tbl` (
  `tutor_profile_id` int NOT NULL AUTO_INCREMENT,
  `tutor_id` int NOT NULL,
  `bio` text,
  `expertise` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'acc logo white.png',
  `education` varchar(255) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `achievements` text,
  `country` varchar(100) DEFAULT NULL,
  `languages_known` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tutor_profile_id`),
  KEY `tutor_id` (`tutor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_profile_tbl`
--

INSERT INTO `tutor_profile_tbl` (`tutor_profile_id`, `tutor_id`, `bio`, `expertise`, `profile_pic`, `education`, `experience`, `achievements`, `country`, `languages_known`) VALUES
(1, 1, 'Experienced Web Development tutor specializing in front-end technologies and UI design.', 'HTML, CSS, JavaScript, Bootstrap', 'avatar-5.jpg', 'M.Tech in Computer Science', '6 Years', 'Designed 50+ responsive websites; Top-rated instructor', 'India', 'English, Hindi'),
(2, 2, 'Backend developer and PHP specialist with strong database knowledge.', 'PHP, MySQL, APIs, MVC', 'avatar-8.jpg', 'M.Tech in Computer Science', '5 Years', 'Built scalable backend systems for startups', 'India', 'English, Hindi, Marathi'),
(3, 3, 'Java programmer focused on object-oriented programming and problem-solving.', 'Java, OOP, Data Structures', 'avatar-7.jpg', 'M.Sc. Computer Science', '4 Years', 'Mentored 200+ students in Java', 'India', 'English, Hindi'),
(4, 4, 'Linux and system administration trainer with real-world server experience.', 'Linux, Shell Scripting, System Administration', 'acc logo white.png', 'B.Tech in Information Technology', '7 Years', 'Certified Linux Administrator', 'India', 'English, Kannada, Hindi'),
(5, 5, 'Passionate Python instructor with 5+ years of experience teaching programming to beginners and advanced learners.', 'Python Programming, Data Science, Machine Learning, Django', 'acc logo white.png', 'M.Sc. in Computer Science', '5 Years Teaching Experience', 'Certified Python Developer | Google Data Analytics Certified', 'India', 'English, Hindi');

-- --------------------------------------------------------

--
-- Table structure for table `tutor_tbl`
--

DROP TABLE IF EXISTS `tutor_tbl`;
CREATE TABLE IF NOT EXISTS `tutor_tbl` (
  `tutor_id` int NOT NULL AUTO_INCREMENT,
  `tutor_name` varchar(100) NOT NULL,
  `tutor_email` varchar(150) NOT NULL,
  `tutor_phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `tutor_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `verification_status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`tutor_id`),
  UNIQUE KEY `tutor_email` (`tutor_email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_tbl`
--

INSERT INTO `tutor_tbl` (`tutor_id`, `tutor_name`, `tutor_email`, `tutor_phone`, `password`, `tutor_status`, `created_at`, `verification_status`) VALUES
(1, 'Smit Rao', 'smitrao@gmail.com', '9991113335', '$2y$10$smitPassHash', 1, '2025-12-16 12:02:34', 'approved'),
(2, 'Deepa Singh', 'deepasingh@gmail.com', '9811223344', '$2y$10$deepaPassHash', 1, '2025-12-16 12:02:34', 'approved'),
(3, 'Rahul Mehta', 'rahulmehta@gmail.com', '7778889990', '$2y$10$rahulPassHash', 1, '2025-12-16 12:02:34', 'approved'),
(4, 'Kiran Joshi', 'kiranjoshi@gmail.com', '8765432109', '$2y$10$kiranPassHash', 1, '2025-12-16 12:02:34', 'approved'),
(5, 'Heer Rana', 'heer@gmail.com', '8930410058', 'heer@123', 1, '2026-02-16 09:37:51', 'approved');

--
-- Triggers `tutor_tbl`
--
DROP TRIGGER IF EXISTS `trg_update_course_status`;
DELIMITER $$
CREATE TRIGGER `trg_update_course_status` AFTER UPDATE ON `tutor_tbl` FOR EACH ROW BEGIN
    -- If tutor_status changes
    IF NEW.tutor_status <> OLD.tutor_status THEN
    
        UPDATE course_tbl
        SET course_status = NEW.tutor_status
        WHERE tutor_id = NEW.tutor_id;
        
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `user_achievement_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `unlocked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `progress_percentage` decimal(5,2) DEFAULT '100.00',
  PRIMARY KEY (`user_achievement_id`),
  UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  KEY `achievement_id` (`achievement_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_unlocked` (`unlocked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
CREATE TABLE IF NOT EXISTS `user_activity` (
  `activity_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `activity_type` enum('game_played','achievement_unlocked','level_up','streak_milestone','rank_change') NOT NULL,
  `activity_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

DROP TABLE IF EXISTS `user_details`;
CREATE TABLE IF NOT EXISTS `user_details` (
  `user_detail_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `dob` date DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `about_me` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `skills` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `lang_known` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_detail_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_language_stats`
--

DROP TABLE IF EXISTS `user_language_stats`;
CREATE TABLE IF NOT EXISTS `user_language_stats` (
  `stat_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `language` enum('PHP','HTML','Java','Python','Unix','SQL','JavaScript') NOT NULL,
  `games_played` int DEFAULT '0',
  `total_score` int DEFAULT '0',
  `accuracy_percentage` decimal(5,2) DEFAULT '0.00',
  `favorite_game_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `unique_user_language` (`user_id`,`language`),
  KEY `idx_user` (`user_id`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_tbl`
--

DROP TABLE IF EXISTS `user_payment_tbl`;
CREATE TABLE IF NOT EXISTS `user_payment_tbl` (
  `user_payment_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `course_id` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `razorpay_id` varchar(255) DEFAULT NULL,
  `payment_status` tinyint DEFAULT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_payment_id`),
  KEY `user_id` (`user_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `user_payment_tbl_ibfk_3` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

DROP TABLE IF EXISTS `user_stats`;
CREATE TABLE IF NOT EXISTS `user_stats` (
  `stat_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `level` int DEFAULT '1',
  `current_xp` int DEFAULT '0',
  `total_score` bigint DEFAULT '0',
  `total_games_played` int DEFAULT '0',
  `total_wins` int DEFAULT '0',
  `total_losses` int DEFAULT '0',
  `win_rate` decimal(5,2) DEFAULT '0.00',
  `current_streak` int DEFAULT '0',
  `best_streak` int DEFAULT '0',
  `rank_global` int DEFAULT NULL,
  `rank_country` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stat_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_total_score` (`total_score` DESC),
  KEY `idx_level` (`level` DESC),
  KEY `idx_user_stats_composite` (`total_score` DESC,`level` DESC,`win_rate` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

DROP TABLE IF EXISTS `user_tbl`;
CREATE TABLE IF NOT EXISTS `user_tbl` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `profile_pic` varchar(150) NOT NULL DEFAULT 'acc logo white.png',
  `user_name` varchar(100) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `city` int NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `mobile` decimal(10,0) NOT NULL,
  `user_status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  KEY `city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `profile_pic`, `user_name`, `display_name`, `gender`, `city`, `user_email`, `user_password`, `mobile`, `user_status`, `created_at`) VALUES
(1, 'acc logo white.png', 'Rahul Sharma', '', 'male', 125, 'rahul@gmail.com', 'rahul@123', 1234567890, 1, '2025-12-18 18:47:05'),
(2, 'avatar-3.jpg', 'Priya Singh', '', 'female', 147, 'priya@gmail.com', 'priya@123', 2345678901, 1, '2025-12-18 18:47:05'),
(3, 'avatar-1.jpg', 'Amit Patel', '', 'male', 125, 'amit.patel@example.com', '$2y$10$hashpass003', 3456789012, 1, '2025-12-18 18:47:05'),
(4, 'avatar-6.jpg', 'Neha Verma', '', 'female', 329, 'neha.verma@example.com', '$2y$10$hashpass004', 4567890123, 1, '2025-12-18 18:47:05'),
(5, 'avatar-2.jpg', 'Rohan Mehta', '', 'male', 147, 'rohan.mehta@example.com', '$2y$10$hashpass005', 5678901234, 0, '2025-12-18 18:47:05'),
(6, 'acc logo white.png', 'Sneha Iyer', '', 'female', 225, 'sneha.iyer@example.com', '$2y$10$hashpass006', 1234567890, 1, '2025-12-18 18:47:05'),
(7, 'avatar-4.jpg', 'Karan Malhotra', '', 'male', 147, 'karan.malhotra@example.com', '$2y$10$hashpass007', 2345678901, 1, '2025-12-18 18:47:05'),
(8, 'avatar-10.jpg', 'Anjali Desai', '', 'other', 225, 'anjali.desai@example.com', '$2y$10$hashpass008', 3456789012, 0, '2025-12-18 18:47:05'),
(9, 'avatar-9.jpg', 'Vikas Gupta', '', 'male', 225, 'vikas.gupta@example.com', '$2y$10$hashpass009', 4567890123, 0, '2025-12-18 18:47:05'),
(10, 'acc logo white.png', 'Pooja Nair', '', 'female', 329, 'pooja.nair@example.com', '$2y$10$hashpass010', 4567890123, 1, '2025-12-18 18:47:05');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_global_leaderboard`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_global_leaderboard`;
CREATE TABLE IF NOT EXISTS `v_global_leaderboard` (
`user_id` int
,`user_name` varchar(100)
,`display_name` varchar(100)
,`total_score` bigint
,`total_games_played` int
,`win_rate` decimal(5,2)
,`rank_global` int
,`current_streak` int
,`level` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_performance`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_user_performance`;
CREATE TABLE IF NOT EXISTS `v_user_performance` (
`user_id` int
,`game_name` varchar(100)
,`category` enum('Debugging','Logic','Syntax','Speed','Puzzle','Database','Optimization','Competitive','Fun')
,`games_played` bigint
,`avg_score` decimal(14,4)
,`best_score` int
,`avg_time` decimal(14,4)
,`wins` decimal(23,0)
,`win_rate` decimal(29,2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_global_leaderboard`
--
DROP TABLE IF EXISTS `v_global_leaderboard`;

DROP VIEW IF EXISTS `v_global_leaderboard`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_global_leaderboard`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`user_name` AS `user_name`, `u`.`display_name` AS `display_name`, `us`.`total_score` AS `total_score`, `us`.`total_games_played` AS `total_games_played`, `us`.`win_rate` AS `win_rate`, `us`.`rank_global` AS `rank_global`, `us`.`current_streak` AS `current_streak`, `us`.`level` AS `level` FROM (`user_tbl` `u` join `user_stats` `us` on((`u`.`user_id` = `us`.`user_id`))) WHERE (`u`.`user_status` = 1) ORDER BY `us`.`total_score` DESC LIMIT 0, 100 ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_performance`
--
DROP TABLE IF EXISTS `v_user_performance`;

DROP VIEW IF EXISTS `v_user_performance`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_performance`  AS SELECT `gs`.`user_id` AS `user_id`, `g`.`name` AS `game_name`, `g`.`category` AS `category`, count(`gs`.`session_id`) AS `games_played`, avg(`gs`.`score`) AS `avg_score`, max(`gs`.`score`) AS `best_score`, avg(`gs`.`time_taken_seconds`) AS `avg_time`, sum((case when (`gs`.`result` = 'WIN') then 1 else 0 end)) AS `wins`, round(((sum((case when (`gs`.`result` = 'WIN') then 1 else 0 end)) * 100.0) / count(0)),2) AS `win_rate` FROM (`game_sessions` `gs` join `games` `g` on((`gs`.`game_id` = `g`.`game_id`))) WHERE (`gs`.`completed` = true) GROUP BY `gs`.`user_id`, `g`.`game_id`, `g`.`name`, `g`.`category` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignment_tbl`
--
ALTER TABLE `assignment_tbl`
  ADD CONSTRAINT `assignment_tbl_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`),
  ADD CONSTRAINT `assignment_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`),
  ADD CONSTRAINT `assignment_tbl_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons_tbl` (`lesson_id`);

--
-- Constraints for table `bug_race_sessions`
--
ALTER TABLE `bug_race_sessions`
  ADD CONSTRAINT `bug_race_sessions_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`session_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bug_race_sessions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_notes`
--
ALTER TABLE `course_notes`
  ADD CONSTRAINT `course_notes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_notes_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons_tbl` (`lesson_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_tbl`
--
ALTER TABLE `course_tbl`
  ADD CONSTRAINT `course_tbl_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_tbl_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admin_tbl` (`admin_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `course_tbl_ibfk_3` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `duel_matches`
--
ALTER TABLE `duel_matches`
  ADD CONSTRAINT `duel_matches_ibfk_1` FOREIGN KEY (`player1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `duel_matches_ibfk_2` FOREIGN KEY (`player2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `duel_matches_ibfk_3` FOREIGN KEY (`winner_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `duel_rankings`
--
ALTER TABLE `duel_rankings`
  ADD CONSTRAINT `duel_rankings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `duel_rounds`
--
ALTER TABLE `duel_rounds`
  ADD CONSTRAINT `duel_rounds_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `duel_matches` (`match_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `duel_rounds_ibfk_2` FOREIGN KEY (`round_winner_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `enrollments_tbl`
--
ALTER TABLE `enrollments_tbl`
  ADD CONSTRAINT `enrollments_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`),
  ADD CONSTRAINT `enrollments_tbl_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `enrollments_tbl_ibfk_4` FOREIGN KEY (`user_payment_id`) REFERENCES `user_payment_tbl` (`user_payment_id`);

--
-- Constraints for table `game_challenges`
--
ALTER TABLE `game_challenges`
  ADD CONSTRAINT `game_challenges_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `game_languages`
--
ALTER TABLE `game_languages`
  ADD CONSTRAINT `game_languages_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `game_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_sessions_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `leaderboard_entries`
--
ALTER TABLE `leaderboard_entries`
  ADD CONSTRAINT `leaderboard_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaderboard_entries_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons_tbl`
--
ALTER TABLE `lessons_tbl`
  ADD CONSTRAINT `lessons_tbl_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lessons_tbl_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification_tbl`
--
ALTER TABLE `notification_tbl`
  ADD CONSTRAINT `notification_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tutor_details`
--
ALTER TABLE `tutor_details`
  ADD CONSTRAINT `tutor_details_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tutor_package_tbl`
--
ALTER TABLE `tutor_package_tbl`
  ADD CONSTRAINT `tutor_package_tbl_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `package_tbl` (`package_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tutor_package_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tutor_profile_tbl`
--
ALTER TABLE `tutor_profile_tbl`
  ADD CONSTRAINT `tutor_profile_tbl_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`achievement_id`);

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_language_stats`
--
ALTER TABLE `user_language_stats`
  ADD CONSTRAINT `user_language_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_tbl`
--
ALTER TABLE `user_payment_tbl`
  ADD CONSTRAINT `user_payment_tbl_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_payment_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_payment_tbl_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
