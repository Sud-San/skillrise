-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 29, 2026 at 02:57 PM
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
(1, 'admin_1776540575.png', 'Sudip', 'sitaramsantra07@gmail.com', '$2y$10$090f5wVKTlq.MttiEWSgI.MJzq5uAfI0v1NpwqQb8XPL1Lqu.EiTG', 1, '2025-12-18 12:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_tbl`
--

DROP TABLE IF EXISTS `assignment_tbl`;
CREATE TABLE IF NOT EXISTS `assignment_tbl` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `file_url` varchar(500) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `course_id` (`course_id`),
  KEY `tutor_id` (`tutor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assignment_tbl`
--

INSERT INTO `assignment_tbl` (`assignment_id`, `course_id`, `tutor_id`, `title`, `description`, `file_url`, `status`, `created_at`) VALUES
(3, 17, 5, 'Python Practice', 'wertyu', '1776625091_1776596057_sdf23 (1).pdf', 1, '2026-04-20 00:28:11');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category_tbl`
--

INSERT INTO `category_tbl` (`category_id`, `category_name`, `category_code`, `category_description`, `short_description`, `img`, `category_status`, `created_at`, `updated_at`) VALUES
(1, 'Web Development', '601', 'HTML is the standard markup language used to create web pages. It provides the structure and content of a page by using tags (like <h1>, <p>, <img>). Everything you see on a website—text, images, links, and forms—is positioned and defined using HTML. It is not a programming language; it cannot perform logical operations.', 'Build the foundation of the web with HTML and CSS structure.', 'html.png', 1, '2025-12-16 12:14:14', '2025-12-22 12:42:54'),
(2, 'PHP Development', '602', 'PHP is a widely used language primarily designed for web development. It runs on the server (backend) and generates HTML content dynamically. This allows for handling user input, managing databases, and creating sessions (like user logins). It is the backbone of major platforms like WordPress.', 'Master PHP for dynamic web development and server-side scripting.', 'php.png', 1, '2025-12-16 12:14:53', '2025-12-22 12:42:54'),
(3, 'Java Programming', '603', 'Java is a robust, mature language designed to be platform-independent (Write Once, Run Anywhere) via the Java Virtual Machine (JVM). It is heavily used in large, mission-critical business systems, banking applications, and historically, for native Android app development. It emphasizes strong typing and object-oriented principles.\r\n', 'Learn Java for robust, scalable enterprise applications and Android apps.', 'java.png', 1, '2025-12-16 12:15:20', '2025-12-22 12:42:54'),
(4, 'Python Programming', '604', 'Python is known for its readability and simple syntax. It is extremely versatile and used across many domains, including building web applications (Django, Flask), performing complex data analysis (Pandas, NumPy), machine learning (TensorFlow), and automating system tasks (scripting).', 'Dive into Python for data science, AI, and versatile web scripting.', 'python.png', 1, '2025-12-16 12:16:01', '2026-03-15 20:33:46'),
(5, 'Linux / System Administration', '605', 'Unix is an operating system originally developed in the 1970s. Its principles (simple, modular tools, everything is a file, command-line interface focus) led to a whole family of modern operating systems, including Linux, macOS, and BSD. It is the dominant OS for servers, large systems, and cloud infrastructure due to its stability and security.', 'Understand UNIX systems for powerful command-line and server management.', 'unix.png', 1, '2025-12-16 12:16:22', '2025-12-22 12:42:54');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `ip_address`, `user_agent`) VALUES
(1, 'df', 'sd@dfd.bvb', 'hello', 'fdfdfdfdfddfdfd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb'),
(2, 'qwert', 'sitaramsantra07@gmail.com', 'qwert', 'werthwersdefrsdg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb');

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `course_notes`
--

INSERT INTO `course_notes` (`note_id`, `course_id`, `lesson_id`, `file_url`, `description`, `file_size`, `file_type`, `created_at`) VALUES
(1, 1, 1, '/uploads/notes/css_concepts.pdf', 'Introduction to CSS Selectors and Box Model.', 1024, 'pdf', '2025-12-16 23:02:34'),
(2, 1, 2, '/uploads/notes/html1.pdf', 'Detailed guide on HTML5 form validation.', 2048, 'pdf', '2025-12-16 23:02:34'),
(3, 2, 4, '/uploads/notes/html3.pdf', 'Bootstrap Grid System Presentation slides.', 5120, 'pdf', '2025-12-16 23:02:34'),
(4, 3, 11, '/uploads/notes/html4.pdf', 'Cheat sheet for JS Arithmetic and Logical operators.', 850, 'pdf', '2025-12-16 23:02:34'),
(5, 17, 54, '/uploads/notes/python.pdf', 'Source code examples for Python basics.', 15360, 'pdf', '2025-12-16 23:02:34'),
(6, 19, 71, '/uploads/notes/linux_commands.txt', 'List of essential Linux shell commands.', 120, 'txt', '2025-12-16 23:02:34'),
(7, 2, 4, '/uploads/notes/note_1_1773600920_69b7009818ff1.pdf', 'bootsrap', 601162, 'pdf', '2026-03-16 00:25:20');

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
  `course_status` tinyint DEFAULT '0',
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_slug` (`course_slug`),
  KEY `tutor_id` (`tutor_id`),
  KEY `category_id` (`category_id`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(10, 3, 2, 'Core PHP Programming', 'core-php-programming', 'Learn the basics of PHP. Master the fundamentals of server-side scripting.', 'php.png', 'beginner', 3800.00, 15, 0, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(11, 3, 2, 'Advanced PHP and File Management', 'advanced-php-file-management', 'Understand core of PHP and File Management. Dive into advanced topics.', 'php_advanced.png', 'intermediate', 3200.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(12, 3, 2, 'Database Interaction and CodeIgniter Framework', 'database-interaction-codeigniter', 'Master advanced Database Interaction and CodeIgniter Framework.', 'codeigniter.png', 'advanced', 3320.00, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(13, 5, 4, 'Introduction to python', 'introduction-to-python', 'Learn the basics of python. Learn the fundamentals of programming.', 'python.png', 'beginner', 2300.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(14, 5, 4, 'Data Structures & Functions', 'python-data-structures-functions', 'Learn Basics of Data Structures & Functions. Explore Python data types.', 'python_dsa.png', 'beginner', 2300.00, 12, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(15, 5, 4, 'Python interaction with SQLite', 'python-interaction-with-sqlite', 'Understand core of Python interaction with SQLite.', 'sqlite.png', 'intermediate', 3250.00, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(16, 5, 4, 'Python Interaction with text and CSV', 'python-interaction-with-text-csv', 'Understand core of Python Interaction with text and CSV.', 'csv.png', 'intermediate', 3500.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(17, 5, 4, 'Data Visualization using dataframe', 'data-visualization-using-dataframe', 'Master advanced in Data Visualization using dataframe.', 'dataframe.png', 'advanced', 3800.00, 8, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(18, 4, 5, 'Introduction to Linux Operating System', 'introduction-to-linux-os', 'Learn the basics of LINUX. Get an overview of the OS.', 'linux.png', 'beginner', 1875.00, 10, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(19, 4, 5, 'Basic Linux', 'basic-linux-commands', 'Learn Basics of LINUX Commands. Learn essential command-line usage.', 'linux_commands.png', 'beginner', 1875.00, 20, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(20, 4, 5, 'Shell Scripting in Linux', 'shell-scripting-in-linux', 'Understand core of Shell Scripting in LINUX.', 'shell.png', 'intermediate', 1875.00, 15, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28'),
(21, 4, 5, 'Advanced Text Processing Tools', 'advanced-text-processing-tools', 'Master advanced in Text Processing Tools in LINUX (Awk/Sed).', 'awk_sed.png', 'advanced', 1875.00, 25, 1, 1, '2025-12-16 23:30:28', '2025-12-16 12:30:28');

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
  `progress` int DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `certificate_issued` tinyint DEFAULT '0',
  `enrolled_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrollment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `user_payment_id` (`user_payment_id`),
  KEY `enrollments_tbl_ibfk_3` (`course_id`),
  KEY `enrollments_tbl_ibfk_1` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `enrollments_tbl`
--

INSERT INTO `enrollments_tbl` (`enrollment_id`, `user_id`, `tutor_id`, `course_id`, `user_payment_id`, `amount`, `progress`, `completed_at`, `certificate_issued`, `enrolled_at`, `updated_at`, `enrollment_status`) VALUES
(1, 1, 5, 17, 1, 3800.00, 100, '2026-04-02 19:54:19', 1, '2026-03-06 17:57:35', '2026-04-17 15:50:23', 'active'),
(2, 2, 1, 1, 2, 1800.00, 0, NULL, 0, '2026-03-15 00:12:54', '2026-04-17 15:50:31', 'active'),
(3, 1, 1, 1, 3, 1800.00, 50, NULL, 0, '2026-04-08 21:36:09', '2026-04-19 22:03:04', 'active'),
(4, 1, 5, 15, 4, 3250.00, 0, NULL, 0, '2026-04-14 12:56:43', '2026-04-01 15:50:37', 'active'),
(5, 3, 2, 6, 5, 3000.00, 0, NULL, 0, '2026-04-17 12:17:46', '2026-03-31 15:50:43', 'active'),
(6, 7, 4, 19, 6, 1875.00, 0, NULL, 0, '2026-04-17 12:28:21', '2026-04-07 15:50:48', 'active'),
(7, 2, 5, 17, 7, 3800.00, 33, NULL, 0, '2026-04-17 17:15:48', '2026-04-17 17:15:56', 'active'),
(8, 1, 1, 2, 8, 1800.00, 0, NULL, 0, '2026-04-20 10:41:23', '2026-04-20 10:41:23', 'active'),
(9, 1, 4, 21, 9, 1875.00, 0, NULL, 0, '2026-04-20 11:23:38', '2026-04-20 11:23:38', 'active');

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
-- Table structure for table `feedback_tbl`
--

DROP TABLE IF EXISTS `feedback_tbl`;
CREATE TABLE IF NOT EXISTS `feedback_tbl` (
  `feedback_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `tutor_id` int DEFAULT NULL,
  `course_id` int DEFAULT NULL,
  `feedback_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'course' COMMENT 'course, tutor, platform, bug, suggestion',
  `rating` tinyint(1) DEFAULT NULL COMMENT 'Optional 1–5 rating',
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `is_anonymous` tinyint(1) DEFAULT '0',
  `status` tinyint DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`feedback_id`),
  KEY `user_id` (`user_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `course_id` (`course_id`),
  KEY `feedback_type` (`feedback_type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `feedback_tbl`
--

INSERT INTO `feedback_tbl` (`feedback_id`, `user_id`, `tutor_id`, `course_id`, `feedback_type`, `rating`, `message`, `is_anonymous`, `status`, `created_at`, `resolved_at`) VALUES
(1, 1, 5, 17, 'course', 4, 'very good', 0, 1, '2026-04-17 00:09:14', NULL),
(5, 2, 1, 1, 'course', 4, 'Very Nice Explaination', 0, 1, '2026-04-17 11:38:07', NULL),
(6, 3, 2, 6, 'course', 5, 'Very Nice explaination by deepa maam', 0, 1, '2026-04-17 12:18:19', NULL),
(7, 7, 4, 19, 'course', 4, 'Linux', 0, 1, '2026-04-17 12:28:35', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `slug`, `name`, `icon`, `category`, `description`, `difficulty`, `base_duration_minutes`, `is_active`, `created_at`) VALUES
(1, 'debugging-master', 'Debugging Master', '🐛', 'Debugging', 'Find and fix bugs in code snippets', 'Medium', 45, 0, '2025-12-30 10:07:22'),
(2, 'code-output-predictor', 'Code Output Predictor', '🔮', 'Logic', 'Predict code execution results', 'Medium', 10, 1, '2025-12-30 10:07:22'),
(3, 'code-complete', 'Code Complete', '✏️', 'Syntax', 'Fill in missing code', 'Easy', 12, 0, '2025-12-30 10:07:22'),
(4, 'typing-master', 'Typing Master', '⌨️', 'Speed', 'Type code as fast as possible', 'Easy', 5, 1, '2025-12-30 10:07:22'),
(5, 'code-maze', 'Code Maze', '🧩', 'Puzzle', 'Navigate through code logic puzzles', 'Hard', 20, 0, '2025-12-30 10:07:22'),
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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `game_challenges`
--

INSERT INTO `game_challenges` (`id`, `game_id`, `difficulty`, `challenges`, `created_at`) VALUES
(1, 10, 'easy', '{\"a\": \"object\", \"q\": \"typeof null returns?\"}', '2026-03-01 14:34:14'),
(2, 10, 'easy', '{\"a\": \"1\", \"q\": \"What is 10 % 3?\"}', '2026-03-01 14:34:14'),
(3, 10, 'easy', '{\"a\": \"parseInt\", \"q\": \"Method to convert string to number?\"}', '2026-03-01 14:34:14'),
(4, 10, 'easy', '{\"a\": \"false\", \"q\": \"What is !true?\"}', '2026-03-01 14:34:14'),
(5, 10, 'easy', '{\"a\": \"for\", \"q\": \"Loop to iterate array?\"}', '2026-03-01 14:34:14'),
(6, 10, 'easy', '{\"a\": \"8\", \"q\": \"What is 2 ** 3?\"}', '2026-03-01 14:34:14'),
(7, 10, 'easy', '{\"a\": \"pop\", \"q\": \"Method to remove last array item?\"}', '2026-03-01 14:34:14'),
(8, 10, 'easy', '{\"a\": \"55\", \"q\": \"What is \'5\' + 5?\"}', '2026-03-01 14:34:14'),
(9, 10, 'easy', '{\"a\": \"alphabetical\", \"q\": \"Default array sort order?\"}', '2026-03-01 14:34:14'),
(10, 10, 'easy', '{\"a\": \"3\", \"q\": \"What is [10,2,3].length?\"}', '2026-03-01 14:34:14'),
(11, 10, 'easy', '{\"a\": \"const\", \"q\": \"Keyword to declare constant?\"}', '2026-03-01 14:34:14'),
(12, 10, 'easy', '{\"a\": \"true\", \"q\": \"What is 5 == \'5\'?\"}', '2026-03-01 14:34:14'),
(13, 10, 'easy', '{\"a\": \"false\", \"q\": \"What is 5 === \'5\'?\"}', '2026-03-01 14:34:14'),
(14, 10, 'easy', '{\"a\": \"join\", \"q\": \"Method to join array items?\"}', '2026-03-01 14:34:14'),
(15, 10, 'easy', '{\"a\": \"4\", \"q\": \"What is Math.floor(4.7)?\"}', '2026-03-01 14:34:14'),
(16, 10, 'easy', '{\"a\": \"5\", \"q\": \"What is Math.ceil(4.1)?\"}', '2026-03-01 14:34:14'),
(17, 10, 'easy', '{\"a\": \"filter\", \"q\": \"Array method to filter items?\"}', '2026-03-01 14:34:14'),
(18, 10, 'easy', '{\"a\": \"false\", \"q\": \"What is true && false?\"}', '2026-03-01 14:34:14'),
(19, 7, 'easy', '{\"code\": \"function add(a,b){ return a - b; }\", \"correct\": 0, \"options\": [\"Should use + instead of -\", \"Missing semicolon\", \"Wrong function name\", \"Parameters are wrong\"], \"question\": \"What is wrong with this code?\", \"explanation\": \"The function should add numbers but uses subtraction.\"}', '2026-03-01 14:35:07'),
(20, 7, 'easy', '{\"code\": \"let x = 5 console.log(x);\", \"correct\": 0, \"options\": [\"Missing semicolon\", \"Wrong variable\", \"Missing bracket\", \"Nothing wrong\"], \"question\": \"What is wrong?\", \"explanation\": \"Semicolon missing after 5.\"}', '2026-03-01 14:35:07'),
(21, 7, 'easy', '{\"code\": \"if(a = 10){ console.log(a); }\", \"correct\": 0, \"options\": [\"Assignment instead of comparison\", \"Missing bracket\", \"Wrong console\", \"Nothing\"], \"question\": \"Find the bug\", \"explanation\": \"Should use == or === instead of =.\"}', '2026-03-01 14:35:07'),
(22, 7, 'easy', '{\"code\": \"const arr=[1,2]; arr.push[3];\", \"correct\": 0, \"options\": [\"push needs parentheses\", \"Missing comma\", \"Wrong const\", \"Nothing\"], \"question\": \"What is wrong?\", \"explanation\": \"push is a function and must use ().\"}', '2026-03-01 14:35:07'),
(23, 7, 'easy', '{\"code\": \"for(let i=0;i<5;i--){ }\", \"correct\": 0, \"options\": [\"Wrong increment\", \"Missing let\", \"Syntax error\", \"Nothing\"], \"question\": \"Identify the issue\", \"explanation\": \"i-- causes infinite loop.\"}', '2026-03-01 14:35:07'),
(24, 7, 'easy', '{\"code\": \"console.log(num); let num = 5;\", \"correct\": 0, \"options\": [\"Reference error\", \"Type error\", \"Syntax error\", \"No error\"], \"question\": \"What error occurs?\", \"explanation\": \"Cannot access variable before initialization.\"}', '2026-03-01 14:35:07'),
(25, 7, 'easy', '{\"code\": \"const a = 5; a = 10;\", \"correct\": 0, \"options\": [\"Assignment error\", \"Works fine\", \"Undefined\", \"Nothing\"], \"question\": \"What happens?\", \"explanation\": \"Cannot reassign const variable.\"}', '2026-03-01 14:35:07'),
(26, 7, 'easy', '{\"code\": \"let arr=[]; arr.length();\", \"correct\": 0, \"options\": [\"length is property not function\", \"Missing bracket\", \"Wrong variable\", \"Nothing\"], \"question\": \"What is wrong?\", \"explanation\": \"length is not a function.\"}', '2026-03-01 14:35:07'),
(28, 7, 'easy', '{\"code\": \"typeof null\", \"correct\": 0, \"options\": [\"object\", \"null\", \"undefined\", \"number\"], \"question\": \"What is the output?\", \"explanation\": \"This is a known JavaScript bug.\"}', '2026-03-01 14:40:12'),
(29, 7, 'easy', '{\"code\": \"0.1 + 0.2 === 0.3\", \"correct\": 0, \"options\": [\"false\", \"true\", \"error\", \"NaN\"], \"question\": \"What is the result?\", \"explanation\": \"Floating point precision issue.\"}', '2026-03-01 14:40:12'),
(30, 7, 'easy', '{\"code\": \"NaN === NaN\", \"correct\": 0, \"options\": [\"false\", \"true\", \"error\", \"undefined\"], \"question\": \"Result?\", \"explanation\": \"NaN is not equal to itself.\"}', '2026-03-01 14:40:12'),
(31, 7, 'easy', '{\"code\": \"let a = null; console.log(a.length);\", \"correct\": 0, \"options\": [\"TypeError\", \"SyntaxError\", \"ReferenceError\", \"No error\"], \"question\": \"What error occurs?\", \"explanation\": \"Cannot read property length of null.\"}', '2026-03-01 14:42:11'),
(32, 7, 'easy', '{\"code\": \"console.log(typeof undefinedVar);\", \"correct\": 0, \"options\": [\"ReferenceError\", \"undefined\", \"null\", \"object\"], \"question\": \"What happens?\", \"explanation\": \"Accessing undeclared variable throws ReferenceError.\"}', '2026-03-01 14:42:11'),
(33, 3, 'easy', '{\"code\": \"def greet(name):\\n    ___(\\\"Hello \\\" + name)\", \"blank\": \"___\", \"answer\": \"print\", \"language\": \"Python\", \"difficulty\": \"Easy\", \"explanation\": \"print() is used to display output in Python.\", \"instruction\": \"Complete the print statement\"}', '2026-03-01 14:46:12'),
(34, 3, 'easy', '{\"code\": \"console.___(\\\"Hello World\\\");\", \"blank\": \"___\", \"answer\": \"log\", \"language\": \"JavaScript\", \"difficulty\": \"Easy\", \"explanation\": \"console.log() prints output in JavaScript.\", \"instruction\": \"Complete the console statement\"}', '2026-03-01 14:46:12'),
(35, 3, 'easy', '{\"code\": \"System.out.___(\\\"Hello\\\");\", \"blank\": \"___\", \"answer\": \"println\", \"language\": \"Java\", \"difficulty\": \"Easy\", \"explanation\": \"println() prints with newline in Java.\", \"instruction\": \"Complete the print function\"}', '2026-03-01 14:46:12'),
(36, 3, 'easy', '{\"code\": \"___ \\\"Hello World\\\";\", \"blank\": \"___\", \"answer\": \"echo\", \"language\": \"PHP\", \"difficulty\": \"Easy\", \"explanation\": \"echo outputs text in PHP.\", \"instruction\": \"Complete the echo statement\"}', '2026-03-01 14:46:12'),
(37, 3, 'easy', '{\"code\": \"SELECT ___ FROM users;\", \"blank\": \"___\", \"answer\": \"*\", \"language\": \"SQL\", \"difficulty\": \"Easy\", \"explanation\": \"* selects all columns.\", \"instruction\": \"Complete the query to select all columns\"}', '2026-03-01 14:46:12'),
(38, 3, 'easy', '{\"code\": \"___ i in range(5):\\n    print(i)\", \"blank\": \"___\", \"answer\": \"for\", \"language\": \"Python\", \"difficulty\": \"Easy\", \"explanation\": \"for is used for iteration in Python.\", \"instruction\": \"Complete the loop keyword\"}', '2026-03-01 14:46:12'),
(39, 3, 'easy', '{\"code\": \"arr.___(5);\", \"blank\": \"___\", \"answer\": \"push\", \"language\": \"JavaScript\", \"difficulty\": \"Easy\", \"explanation\": \"push() adds element to array end.\", \"instruction\": \"Complete array method to add item\"}', '2026-03-01 14:46:12'),
(40, 3, 'easy', '{\"code\": \"___ x = 10;\", \"blank\": \"___\", \"answer\": \"int\", \"language\": \"Java\", \"difficulty\": \"Easy\", \"explanation\": \"int declares integer variable in Java.\", \"instruction\": \"Complete variable declaration\"}', '2026-03-01 14:46:12'),
(41, 3, 'easy', '{\"code\": \"___name = \\\"Sud\\\";\", \"blank\": \"___\", \"answer\": \"$\", \"language\": \"PHP\", \"difficulty\": \"Easy\", \"explanation\": \"PHP variables start with $ symbol.\", \"instruction\": \"Complete variable syntax\"}', '2026-03-01 14:46:12'),
(42, 3, 'easy', '{\"code\": \"SELECT * FROM users ___ id = 1;\", \"blank\": \"___\", \"answer\": \"WHERE\", \"language\": \"SQL\", \"difficulty\": \"Easy\", \"explanation\": \"WHERE filters records.\", \"instruction\": \"Complete WHERE clause\"}', '2026-03-01 14:46:12'),
(43, 5, 'hard', '{\"grid\": [[2, 1, 1, 3], [0, 0, 0, 0]], \"name\": \"Simple Path 1\", \"gridSize\": {\"cols\": 4, \"rows\": 2}, \"difficulty\": \"Easy\", \"description\": \"Move player (2) to goal (3)\", \"optimalMoves\": 3}', '2026-03-02 12:46:56'),
(44, 5, 'hard', '{\"grid\": [[2, 1, 0, 1, 3], [1, 1, 1, 0, 1]], \"name\": \"Obstacle Route\", \"gridSize\": {\"cols\": 5, \"rows\": 2}, \"difficulty\": \"Easy\", \"description\": \"Avoid obstacles (0) and reach goal\", \"optimalMoves\": 5}', '2026-03-02 12:48:45'),
(45, 5, 'hard', '{\"grid\": [[2, 1, 1], [0, 0, 1], [1, 1, 3]], \"name\": \"Corner Escape\", \"gridSize\": {\"cols\": 3, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Start in corner and reach end\", \"optimalMoves\": 4}', '2026-03-02 12:48:45'),
(46, 5, 'hard', '{\"grid\": [[2, 0, 1, 1], [1, 0, 1, 0], [1, 1, 1, 3]], \"name\": \"Zigzag Path\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Follow zigzag route\", \"optimalMoves\": 6}', '2026-03-02 12:48:45'),
(47, 5, 'hard', '{\"grid\": [[2, 1, 1], [1, 0, 1], [1, 1, 3]], \"name\": \"Blocked Center\", \"gridSize\": {\"cols\": 3, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Navigate around center block\", \"optimalMoves\": 4}', '2026-03-02 12:48:45'),
(48, 5, 'hard', '{\"grid\": [[2, 0, 0, 0], [1, 1, 1, 0], [0, 0, 1, 3]], \"name\": \"Narrow Tunnel\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Hard\", \"description\": \"One narrow path to goal\", \"optimalMoves\": 5}', '2026-03-02 12:48:45'),
(49, 5, 'hard', '{\"grid\": [[2, 1, 1, 1, 1], [1, 0, 0, 0, 1], [1, 1, 1, 1, 3]], \"name\": \"Wide Grid\", \"gridSize\": {\"cols\": 5, \"rows\": 3}, \"difficulty\": \"Hard\", \"description\": \"Large open grid\", \"optimalMoves\": 6}', '2026-03-02 12:48:46'),
(50, 5, 'hard', '{\"grid\": [[2, 1, 0, 1], [0, 1, 0, 1], [1, 1, 1, 3]], \"name\": \"Maze Loop\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Hard\", \"description\": \"Avoid dead-end loop\", \"optimalMoves\": 5}', '2026-03-02 12:48:46'),
(51, 5, 'hard', '{\"grid\": [[2, 1, 1, 1, 3], [0, 0, 0, 0, 0]], \"name\": \"Top Row Path\", \"gridSize\": {\"cols\": 5, \"rows\": 2}, \"difficulty\": \"Easy\", \"description\": \"Travel across top row\", \"optimalMoves\": 4}', '2026-03-02 12:48:46'),
(52, 5, 'hard', '{\"grid\": [[2, 1, 1, 1], [0, 0, 0, 1], [1, 1, 1, 1], [1, 0, 0, 3]], \"name\": \"Spiral Escape\", \"gridSize\": {\"cols\": 4, \"rows\": 4}, \"difficulty\": \"Hard\", \"description\": \"Follow spiral route to goal\", \"optimalMoves\": 7}', '2026-03-02 12:48:46'),
(53, 5, 'hard', '{\"grid\": [[2, 1, 0, 0], [0, 1, 1, 1], [0, 0, 0, 3]], \"name\": \"Bridge Crossing\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Cross narrow bridge to reach goal\", \"optimalMoves\": 5}', '2026-03-02 12:50:41'),
(54, 5, 'hard', '{\"grid\": [[2, 0, 0, 3], [1, 1, 1, 1]], \"name\": \"Side Escape\", \"gridSize\": {\"cols\": 4, \"rows\": 2}, \"difficulty\": \"Easy\", \"description\": \"Move along the side path\", \"optimalMoves\": 4}', '2026-03-02 12:50:41'),
(55, 5, 'hard', '{\"grid\": [[2, 1, 1], [0, 0, 1], [3, 1, 1]], \"name\": \"Double Turn\", \"gridSize\": {\"cols\": 3, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Two turns required to reach goal\", \"optimalMoves\": 4}', '2026-03-02 12:50:41'),
(56, 5, 'hard', '{\"grid\": [[2, 1, 1, 1], [1, 0, 0, 1], [1, 1, 1, 3]], \"name\": \"Central Block\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Center blocked by obstacles\", \"optimalMoves\": 6}', '2026-03-02 12:50:41'),
(57, 5, 'hard', '{\"grid\": [[2, 0, 0], [1, 1, 1], [0, 0, 3]], \"name\": \"Downward Path\", \"gridSize\": {\"cols\": 3, \"rows\": 3}, \"difficulty\": \"Easy\", \"description\": \"Move downward first to win\", \"optimalMoves\": 4}', '2026-03-02 12:50:41'),
(58, 5, 'hard', '{\"grid\": [[2, 1, 1, 1, 1, 1, 3], [0, 0, 0, 0, 0, 0, 0]], \"name\": \"Long Corridor\", \"gridSize\": {\"cols\": 7, \"rows\": 2}, \"difficulty\": \"Hard\", \"description\": \"Travel through long corridor\", \"optimalMoves\": 6}', '2026-03-02 12:50:41'),
(59, 5, 'hard', '{\"grid\": [[2, 1, 1], [1, 0, 1], [1, 1, 3]], \"name\": \"Blocked Corners\", \"gridSize\": {\"cols\": 3, \"rows\": 3}, \"difficulty\": \"Hard\", \"description\": \"Corners contain obstacles\", \"optimalMoves\": 4}', '2026-03-02 12:50:41'),
(60, 5, 'hard', '{\"grid\": [[0, 1, 0], [2, 1, 3], [0, 1, 0]], \"name\": \"Cross Pattern\", \"gridSize\": {\"cols\": 3, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Cross shaped open path\", \"optimalMoves\": 2}', '2026-03-02 12:50:41'),
(61, 5, 'hard', '{\"grid\": [[2, 1, 0, 0], [1, 1, 1, 0], [0, 0, 1, 3]], \"name\": \"Maze Trap\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Hard\", \"description\": \"Avoid dead-end trap\", \"optimalMoves\": 5}', '2026-03-02 12:50:41'),
(62, 5, 'hard', '{\"grid\": [[2, 1, 1, 1], [0, 0, 0, 1], [3, 1, 1, 1]], \"name\": \"Edge Runner\", \"gridSize\": {\"cols\": 4, \"rows\": 3}, \"difficulty\": \"Medium\", \"description\": \"Run along outer edge\", \"optimalMoves\": 5}', '2026-03-02 12:50:41'),
(73, 9, 'medium', '{\"code\": \"{\\r\\n  \\\"server\\\": {\\r\\n    \\\"port\\\": \\\"eight-zero-eight-zero\\\",\\r\\n    \\\"host\\\": \\\"localhost\\\",\\r\\n    \\\"debug\\\": \\\"yes\\\"\\r\\n  }\\r\\n}\", \"hint\": \"Port must be number. Debug must be boolean.\", \"type\": \"Fix Config\", \"title\": \"Fix Server Port\", \"answer\": \"{\\r\\n  \\\"server\\\": {\\r\\n    \\\"port\\\": 8080,\\r\\n    \\\"host\\\": \\\"localhost\\\",\\r\\n    \\\"debug\\\": false\\r\\n  }\\r\\n}\", \"description\": \"Fix the server port to 8080 and debug to false.\"}', '2026-03-02 13:08:12'),
(74, 9, 'medium', '{\"code\": \"{\\r\\n  name: \\\"Sud\\\",\\r\\n  age: 22,\\r\\n}\", \"hint\": \"Keys must be in double quotes and no trailing comma.\", \"type\": \"Fix JSON\", \"title\": \"Correct JSON Format\", \"answer\": \"{\\r\\n  \\\"name\\\": \\\"Sud\\\",\\r\\n  \\\"age\\\": 22\\r\\n}\", \"description\": \"Fix invalid JSON syntax.\"}', '2026-03-02 13:08:12'),
(75, 9, 'medium', '{\"code\": \"SELECT * FROM users WHERE id = \\\" + userId + \\\";\", \"hint\": \"Never concatenate user input directly into SQL.\", \"type\": \"Fix SQL\", \"title\": \"Secure Query\", \"answer\": \"Use prepared statements with parameter binding.\", \"description\": \"Fix SQL injection vulnerability.\"}', '2026-03-02 13:08:12'),
(76, 9, 'medium', '{\"code\": \"fetch(\\\"/api/login\\\", { method: \\\"GET\\\" })\", \"hint\": \"Login credentials should use POST.\", \"type\": \"Fix API\", \"title\": \"Correct API Method\", \"answer\": \"fetch(\\\"/api/login\\\", { method: \\\"POST\\\" })\", \"description\": \"Change method to POST.\"}', '2026-03-02 13:08:12'),
(77, 9, 'medium', '{\"code\": \"boolean isActive = \\\"true\\\";\", \"hint\": \"Boolean must not be string.\", \"type\": \"Fix Java\", \"title\": \"Correct Boolean\", \"answer\": \"boolean isActive = true;\", \"description\": \"Fix boolean assignment.\"}', '2026-03-02 13:08:12'),
(78, 9, 'medium', '{\"code\": \"def greet():\\nprint(\\\"Hello\\\")\", \"hint\": \"Python requires proper indentation.\", \"type\": \"Fix Python\", \"title\": \"Correct Indentation\", \"answer\": \"def greet():\\n    print(\\\"Hello\\\")\", \"description\": \"Fix indentation error.\"}', '2026-03-02 13:08:12'),
(79, 9, 'medium', '{\"code\": \"<div><p>Hello</div>\", \"hint\": \"All opened tags must be closed.\", \"type\": \"Fix HTML\", \"title\": \"Close Tag\", \"answer\": \"<div><p>Hello</p></div>\", \"description\": \"Fix missing closing tag.\"}', '2026-03-02 13:08:12'),
(80, 9, 'medium', '{\"code\": \"if(a = 5){ console.log(\\\"Yes\\\"); }\", \"hint\": \"Use comparison operator not assignment.\", \"type\": \"Fix JavaScript\", \"title\": \"Correct Equality\", \"answer\": \"if(a === 5){ console.log(\\\"Yes\\\"); }\", \"description\": \"Fix comparison operator.\"}', '2026-03-02 13:08:12'),
(81, 9, 'medium', '{\"code\": \"arr.length();\", \"hint\": \"length is property not function.\", \"type\": \"Fix Array\", \"title\": \"Correct Method\", \"answer\": \"arr.length;\", \"description\": \"Fix incorrect array method usage.\"}', '2026-03-02 13:08:12'),
(82, 9, 'medium', '{\"code\": \"const port = process.env.PORT || \\\"3000\\\";\", \"hint\": \"Port should be number not string.\", \"type\": \"Fix Node Config\", \"title\": \"Correct Environment Variable\", \"answer\": \"const port = process.env.PORT || 3000;\", \"description\": \"Fix environment variable usage.\"}', '2026-03-02 13:08:12'),
(83, 9, 'medium', '{\"code\": \"{\\r\\n  \\\"server\\\": {\\r\\n    \\\"port\\\": \\\"eight-zero-eight-zero\\\",\\r\\n    \\\"host\\\": \\\"localhost\\\",\\r\\n    \\\"debug\\\": \\\"yes\\\"\\r\\n  }\\r\\n}\", \"hint\": \"Port should be a number, not a string. Debug should be a boolean.\", \"type\": \"Fix Config\", \"title\": \"Fix Server Configuration\", \"answer\": \"{\\r\\n  \\\"server\\\": {\\r\\n    \\\"port\\\": 8080,\\r\\n    \\\"host\\\": \\\"localhost\\\",\\r\\n    \\\"debug\\\": false\\r\\n  }\\r\\n}\", \"description\": \"The server config has an error. Fix the port number to 8080 and set debug mode to false.\"}', '2026-03-02 13:09:49'),
(84, 9, 'medium', '{\"code\": \"Error: Cannot read property \\\"length\\\" of undefined\\nat processData (app.js:42)\", \"hint\": \"The error says \\\"of undefined\\\" - what does that tell you?\", \"type\": \"Decode Logs\", \"title\": \"Decode Error Message\", \"options\": [\"Variable is not declared\", \"Trying to access property on undefined/null object\", \"Syntax error in code\", \"Network connection failed\"], \"description\": \"What does this error mean? Select the correct cause.\", \"correctIndex\": 1}', '2026-03-02 13:09:49'),
(85, 9, 'medium', '{\"hint\": \"Use the modulo operator (%) to check if divisible by 2\", \"type\": \"Quick Script\", \"title\": \"Write a Quick Fix\", \"description\": \"Write a function that returns true if a number is even, false otherwise.\", \"expectedCode\": \"return num % 2 === 0\"}', '2026-03-02 13:09:49'),
(86, 9, 'medium', '{\"code\": \"let i = 0;\\nwhile (i < 5) {\\n  console.log(i);\\n  // Bug: missing increment\\n}\", \"hint\": \"The counter variable never increases!\", \"type\": \"Fix Bug\", \"title\": \"Debug the Loop\", \"answer\": \"let i = 0;\\nwhile (i < 5) {\\n  console.log(i);\\n  i++;\\n}\", \"description\": \"This loop never stops! Fix it so it only runs 5 times.\"}', '2026-03-02 13:09:49'),
(87, 9, 'medium', '{\"code\": \"const user = {\\n  name: \\\"Alice\\\"\\n};\\nconsole.log(user.age.toString());\", \"hint\": \"What happens when you try to call a method on undefined?\", \"type\": \"Error Analysis\", \"title\": \"Identify the Issue\", \"options\": [\"toString() is not a function\", \"user.age is undefined, cannot call toString() on undefined\", \"Const variables cannot be accessed\", \"Console.log syntax error\"], \"description\": \"Why does this code throw an error?\", \"correctIndex\": 1}', '2026-03-02 13:09:49'),
(88, 9, 'medium', '{\"code\": \"{\\r\\n  \\\"database\\\": {\\r\\n    \\\"port\\\": \\\"3306\\\",\\r\\n    \\\"host\\\": localhost,\\r\\n    \\\"connected\\\": \\\"true\\\"\\r\\n  }\\r\\n}\", \"hint\": \"Strings must be quoted. Numbers and booleans should not be strings.\", \"type\": \"Fix Config\", \"title\": \"Fix Database Config\", \"answer\": \"{\\r\\n  \\\"database\\\": {\\r\\n    \\\"port\\\": 3306,\\r\\n    \\\"host\\\": \\\"localhost\\\",\\r\\n    \\\"connected\\\": true\\r\\n  }\\r\\n}\", \"description\": \"Correct the database configuration.\"}', '2026-03-02 13:15:21'),
(89, 9, 'medium', '{\"code\": \"ReferenceError: userName is not defined\\nat login (app.js:12)\", \"hint\": \"The variable does not exist in scope.\", \"type\": \"Decode Logs\", \"title\": \"Decode Reference Error\", \"options\": [\"Variable was never declared\", \"Network error\", \"Syntax error\", \"File missing\"], \"description\": \"What caused this error?\", \"correctIndex\": 0}', '2026-03-02 13:15:21'),
(90, 9, 'medium', '{\"hint\": \"Use + operator to add values.\", \"type\": \"Quick Script\", \"title\": \"Add Two Numbers\", \"description\": \"Write a return statement that adds a and b.\", \"expectedCode\": \"return a + b\"}', '2026-03-02 13:15:21'),
(91, 9, 'medium', '{\"code\": \"let i = 0;\\nwhile(true){\\n  console.log(i);\\n}\", \"hint\": \"Add condition and increment.\", \"type\": \"Fix Bug\", \"title\": \"Fix Infinite Loop\", \"answer\": \"let i = 0;\\nwhile(i < 3){\\n  console.log(i);\\n  i++;\\n}\", \"description\": \"Stop this loop after 3 iterations.\"}', '2026-03-02 13:15:21'),
(92, 9, 'medium', '{\"code\": \"let num = 5;\\nnum();\", \"hint\": \"Only functions can be called with ().\", \"type\": \"Error Analysis\", \"title\": \"Type Error\", \"options\": [\"num is not a function\", \"Missing semicolon\", \"num is undefined\", \"Wrong syntax\"], \"description\": \"Why does this fail?\", \"correctIndex\": 0}', '2026-03-02 13:15:21'),
(93, 9, 'medium', '{\"code\": \"<input type=text name=username>\", \"hint\": \"Attribute values should be quoted.\", \"type\": \"Fix HTML\", \"title\": \"Fix Input Field\", \"answer\": \"<input type=\\\"text\\\" name=\\\"username\\\">\", \"description\": \"Correct the input element.\"}', '2026-03-02 13:15:21'),
(94, 9, 'medium', '{\"code\": \"SyntaxError: Unexpected token } in JSON at position 45\", \"hint\": \"Check bracket balance.\", \"type\": \"Decode Logs\", \"title\": \"JSON Parse Error\", \"options\": [\"Extra closing bracket in JSON\", \"Network timeout\", \"Variable undefined\", \"Wrong API route\"], \"description\": \"What caused this error?\", \"correctIndex\": 0}', '2026-03-02 13:15:21'),
(95, 9, 'medium', '{\"hint\": \"Use .length property.\", \"type\": \"Quick Script\", \"title\": \"Check String Length\", \"description\": \"Return true if string length > 5.\", \"expectedCode\": \"return str.length > 5\"}', '2026-03-02 13:15:21'),
(96, 9, 'medium', '{\"code\": \"if(x == null){ console.log(\\\"Empty\\\"); }\", \"hint\": \"Use strict equality.\", \"type\": \"Fix JavaScript\", \"title\": \"Fix Equality\", \"answer\": \"if(x === null){ console.log(\\\"Empty\\\"); }\", \"description\": \"Fix comparison issue.\"}', '2026-03-02 13:15:21'),
(97, 9, 'medium', '{\"code\": \"const arr = [1,2,3];\\nconsole.log(arr[5].toString());\", \"hint\": \"Accessing non-existing index returns undefined.\", \"type\": \"Error Analysis\", \"title\": \"Undefined Property\", \"options\": [\"arr[5] is undefined\", \"Array length exceeded\", \"toString not allowed\", \"Console error\"], \"description\": \"Why does this crash?\", \"correctIndex\": 0}', '2026-03-02 13:15:21'),
(98, 12, 'easy', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"The variable was never created\", \"title\": \"Variable not declared\"}, {\"desc\": \"Trying to read property from null/undefined object\", \"title\": \"Accessing property of undefined\", \"correct\": true}, {\"desc\": \"Wrong code syntax\", \"title\": \"Syntax error\"}, {\"desc\": \"API failed\", \"title\": \"Network error\"}], \"example\": \"const data = undefined;\\nconsole.log(data.length);\", \"message\": \"Cannot read property \\\"length\\\" of undefined\", \"location\": \"at processData (app.js:42)\", \"difficulty\": \"easy\", \"explanation\": \"This occurs when accessing a property on undefined.\"}', '2026-03-02 13:17:41'),
(99, 12, 'easy', '{\"type\": \"ReferenceError\", \"causes\": [{\"desc\": \"Variable does not exist\", \"title\": \"Variable never declared\", \"correct\": true}, {\"desc\": \"Data type mismatch\", \"title\": \"Wrong data type\"}, {\"desc\": \"Invalid syntax\", \"title\": \"Syntax error\"}, {\"desc\": \"Server offline\", \"title\": \"Network issue\"}], \"example\": \"console.log(userName);\", \"message\": \"userName is not defined\", \"location\": \"at login (auth.js:10)\", \"difficulty\": \"easy\", \"explanation\": \"ReferenceError occurs when variable is not declared.\"}', '2026-03-02 13:17:41'),
(100, 12, 'easy', '{\"type\": \"SyntaxError\", \"causes\": [{\"desc\": \"JSON has extra }\", \"title\": \"Extra closing bracket\", \"correct\": true}, {\"desc\": \"Variable not declared\", \"title\": \"Wrong variable\"}, {\"desc\": \"Server not responding\", \"title\": \"API failure\"}, {\"desc\": \"RAM overflow\", \"title\": \"Memory leak\"}], \"example\": \"{ \\\"name\\\":\\\"Sud\\\", }\", \"message\": \"Unexpected token }\", \"location\": \"at config.json:12\", \"difficulty\": \"easy\", \"explanation\": \"JSON syntax must be properly structured.\"}', '2026-03-02 13:17:41'),
(101, 12, 'easy', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"Trying to execute a number as function\", \"title\": \"Calling non-function\", \"correct\": true}, {\"desc\": \"Semicolon error\", \"title\": \"Missing semicolon\"}, {\"desc\": \"Variable missing\", \"title\": \"Variable undefined\"}, {\"desc\": \"Invalid statement\", \"title\": \"Wrong syntax\"}], \"example\": \"let num = 5;\\nnum();\", \"message\": \"num is not a function\", \"location\": \"at main.js:5\", \"difficulty\": \"easy\", \"explanation\": \"Only functions can be called using ().\"}', '2026-03-02 13:17:42'),
(102, 12, 'easy', '{\"type\": \"RangeError\", \"causes\": [{\"desc\": \"Function calls itself without stop\", \"title\": \"Infinite recursion\", \"correct\": true}, {\"desc\": \"Missing variable\", \"title\": \"Variable undefined\"}, {\"desc\": \"Invalid syntax\", \"title\": \"Syntax error\"}, {\"desc\": \"Server issue\", \"title\": \"Network error\"}], \"example\": \"function test(){ test(); }\\ntest();\", \"message\": \"Maximum call stack size exceeded\", \"location\": \"at recursiveFunc (app.js:22)\", \"difficulty\": \"easy\", \"explanation\": \"Occurs due to infinite recursive calls.\"}', '2026-03-02 13:17:42'),
(103, 12, 'easy', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"Trying to set property on undefined object\", \"title\": \"Object is undefined\", \"correct\": true}, {\"desc\": \"Missing bracket\", \"title\": \"Syntax error\"}, {\"desc\": \"Type mismatch\", \"title\": \"Wrong type\"}, {\"desc\": \"Database issue\", \"title\": \"Server error\"}], \"example\": \"let user;\\nuser.name=\\\"Sud\\\";\", \"message\": \"Cannot set property \\\"name\\\" of undefined\", \"location\": \"at updateUser (user.js:18)\", \"difficulty\": \"easy\", \"explanation\": \"Cannot assign property to undefined.\"}', '2026-03-02 13:17:42'),
(104, 12, 'easy', '{\"type\": \"ReferenceError\", \"causes\": [{\"desc\": \"require works only in Node\", \"title\": \"Running Node code in browser\", \"correct\": true}, {\"desc\": \"Variable not declared\", \"title\": \"Variable missing\"}, {\"desc\": \"Invalid code\", \"title\": \"Syntax error\"}, {\"desc\": \"Incorrect endpoint\", \"title\": \"Wrong API\"}], \"example\": \"require(\\\"fs\\\");\", \"message\": \"require is not defined\", \"location\": \"at script.js:1\", \"difficulty\": \"easy\", \"explanation\": \"require() is Node-specific.\"}', '2026-03-02 13:17:42'),
(105, 12, 'easy', '{\"type\": \"SyntaxError\", \"causes\": [{\"desc\": \"Code block not closed\", \"title\": \"Missing closing bracket\", \"correct\": true}, {\"desc\": \"Variable missing\", \"title\": \"Undefined variable\"}, {\"desc\": \"Wrong data type\", \"title\": \"Type mismatch\"}, {\"desc\": \"API delay\", \"title\": \"Network timeout\"}], \"example\": \"if(true){ console.log(\\\"Hi\\\");\", \"message\": \"Unexpected end of input\", \"location\": \"at script.js:20\", \"difficulty\": \"easy\", \"explanation\": \"Usually due to missing bracket or brace.\"}', '2026-03-02 13:17:42'),
(106, 12, 'easy', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"Const cannot be reassigned\", \"title\": \"Reassigning const\", \"correct\": true}, {\"desc\": \"Variable undefined\", \"title\": \"Wrong variable\"}, {\"desc\": \"Invalid code\", \"title\": \"Syntax error\"}, {\"desc\": \"Database error\", \"title\": \"Server issue\"}], \"example\": \"const a=5;\\na=10;\", \"message\": \"Assignment to constant variable\", \"location\": \"at app.js:8\", \"difficulty\": \"easy\", \"explanation\": \"Const variables cannot be reassigned.\"}', '2026-03-02 13:17:42'),
(107, 12, 'easy', '{\"type\": \"URIError\", \"causes\": [{\"desc\": \"Malformed escape sequence\", \"title\": \"Invalid URI encoding\", \"correct\": true}, {\"desc\": \"Server unreachable\", \"title\": \"Network issue\"}, {\"desc\": \"Variable missing\", \"title\": \"Wrong variable\"}, {\"desc\": \"Too many variables\", \"title\": \"Memory leak\"}], \"example\": \"decodeURI(\\\"%\\\");\", \"message\": \"URI malformed\", \"location\": \"at decodeURI (utils.js:4)\", \"difficulty\": \"easy\", \"explanation\": \"Occurs when decodeURI receives invalid format.\"}', '2026-03-02 13:17:42'),
(108, 12, 'medium', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"Object.keys cannot process null/undefined\", \"title\": \"Passing null to Object.keys\", \"correct\": true}, {\"desc\": \"Wrong bracket placement\", \"title\": \"Syntax error\"}, {\"desc\": \"API failed\", \"title\": \"Network issue\"}, {\"desc\": \"Too many variables\", \"title\": \"Memory overflow\"}], \"example\": \"Object.keys(null);\", \"message\": \"Cannot convert undefined or null to object\", \"location\": \"at Object.keys (<anonymous>)\", \"difficulty\": \"medium\", \"explanation\": \"Object.keys() requires a valid object.\"}', '2026-03-02 13:18:36'),
(109, 12, 'medium', '{\"type\": \"RangeError\", \"causes\": [{\"desc\": \"Array length cannot be negative\", \"title\": \"Negative array size\", \"correct\": true}, {\"desc\": \"Variable undefined\", \"title\": \"Wrong variable name\"}, {\"desc\": \"Bracket missing\", \"title\": \"Syntax issue\"}, {\"desc\": \"Server timeout\", \"title\": \"Network delay\"}], \"example\": \"new Array(-1);\", \"message\": \"Invalid array length\", \"location\": \"at Array (<anonymous>)\", \"difficulty\": \"medium\", \"explanation\": \"Array length must be positive integer.\"}', '2026-03-02 13:18:36'),
(110, 12, 'medium', '{\"type\": \"SyntaxError\", \"causes\": [{\"desc\": \"Two values written without operator\", \"title\": \"Missing operator\", \"correct\": true}, {\"desc\": \"Variable not declared\", \"title\": \"Undefined variable\"}, {\"desc\": \"Backend error\", \"title\": \"Server issue\"}, {\"desc\": \"Type mismatch\", \"title\": \"Wrong data type\"}], \"example\": \"let x = 5 10;\", \"message\": \"Unexpected identifier\", \"location\": \"at script.js:15\", \"difficulty\": \"medium\", \"explanation\": \"JavaScript expects operator between values.\"}', '2026-03-02 13:18:36'),
(111, 12, 'medium', '{\"type\": \"ReferenceError\", \"causes\": [{\"desc\": \"document exists only in browser\", \"title\": \"Running browser code in Node\", \"correct\": true}, {\"desc\": \"Missing semicolon\", \"title\": \"Syntax error\"}, {\"desc\": \"File not found\", \"title\": \"Wrong file path\"}, {\"desc\": \"Connection lost\", \"title\": \"Network error\"}], \"example\": \"console.log(document.title);\", \"message\": \"document is not defined\", \"location\": \"at app.js:3\", \"difficulty\": \"medium\", \"explanation\": \"document object is available only in browser environment.\"}', '2026-03-02 13:18:36'),
(112, 12, 'medium', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"Query selector returned null\", \"title\": \"Element not found\", \"correct\": true}, {\"desc\": \"Invalid statement\", \"title\": \"Wrong syntax\"}, {\"desc\": \"Not declared\", \"title\": \"Variable missing\"}, {\"desc\": \"Backend failed\", \"title\": \"Server down\"}], \"example\": \"document.getElementById(\\\"x\\\").value;\", \"message\": \"Cannot read properties of null (reading \\\"value\\\")\", \"location\": \"at submitForm (form.js:8)\", \"difficulty\": \"medium\", \"explanation\": \"Trying to access property of null element.\"}', '2026-03-02 13:18:36'),
(113, 12, 'medium', '{\"type\": \"EvalError\", \"causes\": [{\"desc\": \"Passing invalid expression\", \"title\": \"Unsafe eval usage\", \"correct\": true}, {\"desc\": \"Bracket missing\", \"title\": \"Syntax error\"}, {\"desc\": \"Undefined variable\", \"title\": \"Wrong variable\"}, {\"desc\": \"Stack overflow\", \"title\": \"Memory issue\"}], \"example\": \"eval(\\\"var x =\\\");\", \"message\": \"Invalid use of eval()\", \"location\": \"at script.js:22\", \"difficulty\": \"medium\", \"explanation\": \"eval() must receive valid JavaScript expression.\"}', '2026-03-02 13:18:36'),
(114, 12, 'medium', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"map exists only on arrays\", \"title\": \"arr is not an array\", \"correct\": true}, {\"desc\": \"Invalid code\", \"title\": \"Syntax error\"}, {\"desc\": \"Library not loaded\", \"title\": \"Missing import\"}, {\"desc\": \"Server issue\", \"title\": \"Network error\"}], \"example\": \"let arr = 5;\\narr.map(x=>x);\", \"message\": \"arr.map is not a function\", \"location\": \"at processArray (utils.js:14)\", \"difficulty\": \"medium\", \"explanation\": \"map() works only on arrays.\"}', '2026-03-02 13:18:37'),
(115, 12, 'medium', '{\"type\": \"URIError\", \"causes\": [{\"desc\": \"Malformed URI component\", \"title\": \"Invalid encoded string\", \"correct\": true}, {\"desc\": \"Bracket error\", \"title\": \"Wrong syntax\"}, {\"desc\": \"Variable missing\", \"title\": \"Undefined variable\"}, {\"desc\": \"Server failure\", \"title\": \"API issue\"}], \"example\": \"decodeURIComponent(\\\"%E0%A4%A\\\");\", \"message\": \"Failed to execute decodeURIComponent\", \"location\": \"at utils.js:10\", \"difficulty\": \"medium\", \"explanation\": \"decodeURIComponent requires valid encoding.\"}', '2026-03-02 13:18:37'),
(116, 12, 'medium', '{\"type\": \"TypeError\", \"causes\": [{\"desc\": \"Object.freeze prevents modification\", \"title\": \"Object is frozen\", \"correct\": true}, {\"desc\": \"Wrong syntax\", \"title\": \"Syntax error\"}, {\"desc\": \"Type mismatch\", \"title\": \"Wrong type\"}, {\"desc\": \"Database error\", \"title\": \"Server issue\"}], \"example\": \"const obj = Object.freeze({a:1});\\nobj.a=2;\", \"message\": \"Cannot assign to read only property\", \"location\": \"at updateConfig (config.js:9)\", \"difficulty\": \"medium\", \"explanation\": \"Frozen objects cannot be modified.\"}', '2026-03-02 13:18:37'),
(117, 12, 'medium', '{\"type\": \"RangeError\", \"causes\": [{\"desc\": \"toFixed argument out of range\", \"title\": \"Invalid precision value\", \"correct\": true}, {\"desc\": \"Missing bracket\", \"title\": \"Syntax error\"}, {\"desc\": \"Undefined variable\", \"title\": \"Wrong variable\"}, {\"desc\": \"API timeout\", \"title\": \"Network issue\"}], \"example\": \"(5.123).toFixed(200);\", \"message\": \"toFixed() digits argument must be between 0 and 100\", \"location\": \"at script.js:6\", \"difficulty\": \"medium\", \"explanation\": \"toFixed() accepts values between 0 and 100.\"}', '2026-03-02 13:18:37'),
(133, 11, 'easy', '{\"text\": \"Me: \\\"I\'ll just add one more feature\\\"\\n*6 hours later*\\nStill debugging...\", \"emoji\": \"😴\", \"caption\": \"Every developer ever\", \"correct\": 0, \"options\": [\"Feature Creep\", \"while(true)\", \"Infinite Loop\", \"Debug Mode\"]}', '2026-03-02 13:33:12'),
(134, 11, 'easy', '{\"text\": \"Code works perfectly...\\n*Don\'t touch it*\", \"emoji\": \"🔥\", \"caption\": \"Production Rule #1\", \"correct\": 0, \"options\": [\"Legacy Code\", \"Refactor Time\", \"Ship It\", \"Hotfix\"]}', '2026-03-02 13:33:12'),
(135, 11, 'easy', '{\"text\": \"When the bug disappears after adding console.log()\", \"emoji\": \"🤯\", \"caption\": \"Quantum Debugging\", \"correct\": 0, \"options\": [\"Heisenbug\", \"Magic\", \"Stack Overflow\", \"AI Fix\"]}', '2026-03-02 13:33:12'),
(136, 11, 'easy', '{\"text\": \"It works on my machine.\", \"emoji\": \"😂\", \"caption\": \"Developer Defense\", \"correct\": 0, \"options\": [\"Environment Issue\", \"Network Error\", \"Memory Leak\", \"Syntax Error\"]}', '2026-03-02 13:33:12'),
(137, 11, 'easy', '{\"text\": \"Fix one bug...\\nCreate three new ones.\", \"emoji\": \"💀\", \"caption\": \"Classic Refactor\", \"correct\": 0, \"options\": [\"Regression\", \"Upgrade\", \"Optimization\", \"Patch\"]}', '2026-03-02 13:33:12'),
(138, 11, 'easy', '{\"text\": \"Spent 2 hours debugging...\\nMissed a semicolon.\", \"emoji\": \"🧠\", \"caption\": \"Small Mistake, Big Pain\", \"correct\": 0, \"options\": [\"Syntax Error\", \"Runtime Error\", \"Logic Error\", \"Memory Error\"]}', '2026-03-02 13:33:12'),
(139, 11, 'easy', '{\"text\": \"Deploying on Friday evening.\", \"emoji\": \"🚀\", \"caption\": \"Brave or Foolish?\", \"correct\": 0, \"options\": [\"Risky Deploy\", \"Safe Deploy\", \"Unit Test\", \"Rollback\"]}', '2026-03-02 13:33:12'),
(140, 11, 'easy', '{\"text\": \"Copy from StackOverflow\\nPaste\\nPray\", \"emoji\": \"🤖\", \"caption\": \"Modern Development\", \"correct\": 0, \"options\": [\"StackOverflow Driven Dev\", \"Agile\", \"Scrum\", \"DevOps\"]}', '2026-03-02 13:33:13'),
(141, 11, 'easy', '{\"text\": \"Installing one package...\\n+1000 dependencies\", \"emoji\": \"📦\", \"caption\": \"npm Experience\", \"correct\": 0, \"options\": [\"Dependency Hell\", \"Version Control\", \"Patch Update\", \"Plugin Issue\"]}', '2026-03-02 13:33:13'),
(142, 11, 'easy', '{\"text\": \"When indentation breaks Python code.\", \"emoji\": \"😵\", \"caption\": \"Whitespace Matters\", \"correct\": 0, \"options\": [\"Indentation Error\", \"Memory Leak\", \"Compile Error\", \"Network Error\"]}', '2026-03-02 13:33:13'),
(143, 11, 'easy', '{\"text\": \"Waiting for build to finish...\", \"emoji\": \"⏳\", \"caption\": \"Coffee Break Time\", \"correct\": 0, \"options\": [\"Build Process\", \"Runtime Error\", \"Memory Issue\", \"API Delay\"]}', '2026-03-02 13:33:13'),
(144, 11, 'easy', '{\"text\": \"Restart server.\\nStill broken.\\nRestart again.\\nWorks.\", \"emoji\": \"🔄\", \"caption\": \"IT Solution\", \"correct\": 0, \"options\": [\"Have You Tried Turning It Off?\", \"Refactor\", \"Update Patch\", \"Rollback\"]}', '2026-03-02 13:33:13'),
(145, 11, 'easy', '{\"text\": \"Reading legacy code like solving a puzzle.\", \"emoji\": \"🧩\", \"caption\": \"Mystery Project\", \"correct\": 0, \"options\": [\"Legacy Code\", \"Clean Code\", \"Refactor\", \"Upgrade\"]}', '2026-03-02 13:33:13'),
(146, 11, 'easy', '{\"text\": \"Writing code without reading documentation.\", \"emoji\": \"📚\", \"caption\": \"YOLO Programming\", \"correct\": 0, \"options\": [\"Trial and Error\", \"Agile\", \"TDD\", \"DevOps\"]}', '2026-03-02 13:33:13'),
(147, 11, 'easy', '{\"text\": \"Production crashed.\\nBoss: \\\"What changed?\\\"\\nMe: \\\"Nothing...\\\"\", \"emoji\": \"🛑\", \"caption\": \"Silent Deployment\", \"correct\": 0, \"options\": [\"Hidden Bug\", \"Security Patch\", \"Database Crash\", \"Network Error\"]}', '2026-03-02 13:33:13'),
(148, 11, 'easy', '{\"text\": \"Looking at code I wrote 6 months ago.\\n\\\"What idiot wrote this?\\\"\", \"emoji\": \"👀\", \"caption\": \"Past You\", \"correct\": 0, \"options\": [\"Self Roast\", \"Refactor\", \"Optimization\", \"Upgrade\"]}', '2026-03-02 13:33:13'),
(149, 11, 'easy', '{\"text\": \"Adding one console.log fixed everything.\", \"emoji\": \"⚡\", \"caption\": \"Magic Fix\", \"correct\": 0, \"options\": [\"Heisenbug\", \"Compile Fix\", \"Runtime Fix\", \"Memory Patch\"]}', '2026-03-02 13:33:13'),
(150, 11, 'easy', '{\"text\": \"Code works.\\nAdd test case.\\nNow it fails.\", \"emoji\": \"🧪\", \"caption\": \"Testing Reality\", \"correct\": 0, \"options\": [\"Edge Case\", \"Deployment\", \"Syntax Error\", \"Network Error\"]}', '2026-03-02 13:33:13'),
(151, 11, 'easy', '{\"text\": \"Found solution at 2 AM.\\nForget it by morning.\", \"emoji\": \"💡\", \"caption\": \"Night Coding\", \"correct\": 0, \"options\": [\"Sleep Deprivation\", \"Memory Leak\", \"Compile Error\", \"Patch Update\"]}', '2026-03-02 13:33:13'),
(152, 11, 'easy', '{\"text\": \"Optimized code.\\nNow it runs slower.\", \"emoji\": \"📉\", \"caption\": \"Premature Optimization\", \"correct\": 0, \"options\": [\"Premature Optimization\", \"Bug Fix\", \"Security Patch\", \"Upgrade\"]}', '2026-03-02 13:33:13'),
(153, 6, 'easy', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Easy\", \"explanation\": \"SELECT * retrieves all columns from a table.\", \"instruction\": \"Select all columns from the users table\", \"acceptedAnswers\": [\"SELECT * FROM users\", \"SELECT * FROM users;\", \"select * from users\", \"select * from users;\"]}', '2026-03-02 13:38:07'),
(154, 6, 'easy', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Easy\", \"explanation\": \"Specify column names separated by commas.\", \"instruction\": \"Select only name and email from users\", \"acceptedAnswers\": [\"SELECT name, email FROM users\", \"SELECT name, email FROM users;\", \"select name, email from users\", \"select name, email from users;\"]}', '2026-03-02 13:38:07'),
(155, 6, 'easy', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Easy\", \"explanation\": \"Use WHERE clause to filter rows.\", \"instruction\": \"Select users where age is greater than 25\", \"acceptedAnswers\": [\"SELECT * FROM users WHERE age > 25\", \"SELECT * FROM users WHERE age > 25;\", \"select * from users where age > 25\", \"select * from users where age > 25;\"]}', '2026-03-02 13:38:07'),
(156, 6, 'easy', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Easy\", \"explanation\": \"COUNT(*) returns number of rows.\", \"instruction\": \"Count total number of users\", \"acceptedAnswers\": [\"SELECT COUNT(*) FROM users\", \"SELECT COUNT(*) FROM users;\", \"select count(*) from users\", \"select count(*) from users;\"]}', '2026-03-02 13:38:07'),
(157, 6, 'easy', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Easy\", \"explanation\": \"DISTINCT removes duplicate values.\", \"instruction\": \"Select distinct cities from users\", \"acceptedAnswers\": [\"SELECT DISTINCT city FROM users\", \"SELECT DISTINCT city FROM users;\", \"select distinct city from users\", \"select distinct city from users;\"]}', '2026-03-02 13:38:07'),
(158, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"ORDER BY sorts results.\", \"instruction\": \"Order users by age in descending order\", \"acceptedAnswers\": [\"SELECT * FROM users ORDER BY age DESC\", \"SELECT * FROM users ORDER BY age DESC;\", \"select * from users order by age desc\", \"select * from users order by age desc;\"]}', '2026-03-02 13:38:07'),
(159, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"LIMIT restricts number of rows.\", \"instruction\": \"Select first 5 users\", \"acceptedAnswers\": [\"SELECT * FROM users LIMIT 5\", \"SELECT * FROM users LIMIT 5;\", \"select * from users limit 5\", \"select * from users limit 5;\"]}', '2026-03-02 13:38:07'),
(160, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"LIKE with % wildcard matches patterns.\", \"instruction\": \"Select users where name starts with A\", \"acceptedAnswers\": [\"SELECT * FROM users WHERE name LIKE \\\"A%\\\"\", \"SELECT * FROM users WHERE name LIKE \\\"A%\\\";\", \"select * from users where name like \\\"A%\\\"\", \"select * from users where name like \\\"A%\\\";\"]}', '2026-03-02 13:38:07'),
(161, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"BETWEEN filters a range.\", \"instruction\": \"Select users between age 18 and 30\", \"acceptedAnswers\": [\"SELECT * FROM users WHERE age BETWEEN 18 AND 30\", \"SELECT * FROM users WHERE age BETWEEN 18 AND 30;\", \"select * from users where age between 18 and 30\", \"select * from users where age between 18 and 30;\"]}', '2026-03-02 13:38:07'),
(162, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\", \"age INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"GROUP BY groups rows.\", \"instruction\": \"Group users by city and count them\", \"acceptedAnswers\": [\"SELECT city, COUNT(*) FROM users GROUP BY city\", \"SELECT city, COUNT(*) FROM users GROUP BY city;\", \"select city, count(*) from users group by city\", \"select city, count(*) from users group by city;\"]}', '2026-03-02 13:38:08'),
(163, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"email VARCHAR(100)\"], \"orders\": [\"id INT\", \"user_id INT\", \"amount DECIMAL(10,2)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"INNER JOIN combines matching rows from both tables.\", \"instruction\": \"Join users and orders table on user id\", \"acceptedAnswers\": [\"SELECT * FROM users INNER JOIN orders ON users.id = orders.user_id\", \"SELECT * FROM users INNER JOIN orders ON users.id = orders.user_id;\", \"select * from users inner join orders on users.id = orders.user_id\", \"select * from users inner join orders on users.id = orders.user_id;\"]}', '2026-03-02 13:38:08'),
(164, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\"], \"orders\": [\"id INT\", \"user_id INT\", \"amount DECIMAL(10,2)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"LEFT JOIN returns all rows from left table.\", \"instruction\": \"Select all users and their orders (include users without orders)\", \"acceptedAnswers\": [\"SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id\", \"SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id;\", \"select * from users left join orders on users.id = orders.user_id\", \"select * from users left join orders on users.id = orders.user_id;\"]}', '2026-03-02 13:38:08'),
(165, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"city VARCHAR(50)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"HAVING filters grouped results.\", \"instruction\": \"Select cities having more than 5 users\", \"acceptedAnswers\": [\"SELECT city, COUNT(*) FROM users GROUP BY city HAVING COUNT(*) > 5\", \"SELECT city, COUNT(*) FROM users GROUP BY city HAVING COUNT(*) > 5;\", \"select city, count(*) from users group by city having count(*) > 5\", \"select city, count(*) from users group by city having count(*) > 5;\"]}', '2026-03-02 13:38:08'),
(166, 6, 'hard', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\", \"age INT\"]}, \"difficulty\": \"Hard\", \"explanation\": \"Subquery calculates average age first.\", \"instruction\": \"Select users with age greater than average age\", \"acceptedAnswers\": [\"SELECT * FROM users WHERE age > (SELECT AVG(age) FROM users)\", \"SELECT * FROM users WHERE age > (SELECT AVG(age) FROM users);\", \"select * from users where age > (select avg(age) from users)\", \"select * from users where age > (select avg(age) from users);\"]}', '2026-03-02 13:38:08'),
(167, 6, 'hard', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\"], \"orders\": [\"id INT\", \"user_id INT\"]}, \"difficulty\": \"Hard\", \"explanation\": \"EXISTS checks if related row exists.\", \"instruction\": \"Select users who have placed orders\", \"acceptedAnswers\": [\"SELECT * FROM users WHERE EXISTS (SELECT 1 FROM orders WHERE users.id = orders.user_id)\", \"SELECT * FROM users WHERE EXISTS (SELECT 1 FROM orders WHERE users.id = orders.user_id);\", \"select * from users where exists (select 1 from orders where users.id = orders.user_id)\", \"select * from users where exists (select 1 from orders where users.id = orders.user_id);\"]}', '2026-03-02 13:38:08'),
(168, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"age INT\"]}, \"difficulty\": \"Medium\", \"explanation\": \"UPDATE modifies existing records.\", \"instruction\": \"Increase age by 1 for all users\", \"acceptedAnswers\": [\"UPDATE users SET age = age + 1\", \"UPDATE users SET age = age + 1;\", \"update users set age = age + 1\", \"update users set age = age + 1;\"]}', '2026-03-02 13:38:08'),
(169, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"age INT\"]}, \"difficulty\": \"Medium\", \"explanation\": \"DELETE removes rows from table.\", \"instruction\": \"Delete users where age is less than 18\", \"acceptedAnswers\": [\"DELETE FROM users WHERE age < 18\", \"DELETE FROM users WHERE age < 18;\", \"delete from users where age < 18\", \"delete from users where age < 18;\"]}', '2026-03-02 13:38:08'),
(170, 6, 'hard', '{\"schema\": {\"users\": [\"id INT\", \"email VARCHAR(100)\"]}, \"difficulty\": \"Hard\", \"explanation\": \"INDEX improves search performance.\", \"instruction\": \"Create an index on email column\", \"acceptedAnswers\": [\"CREATE INDEX idx_email ON users(email)\", \"CREATE INDEX idx_email ON users(email);\", \"create index idx_email on users(email)\", \"create index idx_email on users(email);\"]}', '2026-03-02 13:38:08'),
(171, 6, 'medium', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\"]}, \"difficulty\": \"Medium\", \"explanation\": \"DROP TABLE deletes entire table structure.\", \"instruction\": \"Drop the users table\", \"acceptedAnswers\": [\"DROP TABLE users\", \"DROP TABLE users;\", \"drop table users\", \"drop table users;\"]}', '2026-03-02 13:38:08');
INSERT INTO `game_challenges` (`id`, `game_id`, `difficulty`, `challenges`, `created_at`) VALUES
(172, 6, 'hard', '{\"schema\": {\"users\": [\"id INT\", \"name VARCHAR(100)\"]}, \"difficulty\": \"Hard\", \"explanation\": \"ALTER TABLE modifies table structure.\", \"instruction\": \"Add a phone column to users table\", \"acceptedAnswers\": [\"ALTER TABLE users ADD phone VARCHAR(15)\", \"ALTER TABLE users ADD phone VARCHAR(15);\", \"alter table users add phone varchar(15)\", \"alter table users add phone varchar(15);\"]}', '2026-03-02 13:38:08'),
(173, 8, 'easy', '{\"hint\": \"Combine operations into single return statement.\", \"issues\": [{\"desc\": \"Use descriptive names\", \"icon\": \"📝\", \"title\": \"Poor Naming\"}, {\"desc\": \"Remove intermediate variables\", \"icon\": \"➖\", \"title\": \"Unnecessary Variables\"}, {\"desc\": \"Add proper spacing\", \"icon\": \"🎨\", \"title\": \"Formatting\"}], \"metrics\": {\"targetLines\": 3, \"originalLines\": 4}, \"language\": \"JavaScript\", \"cleanCode\": \"function sum(a, b, c) {\\n  return a + b + c;\\n}\", \"messyCode\": \"function calc(a,b,c){\\nvar x=a+b;\\nvar y=x+c;\\nreturn y;\\n}\", \"difficulty\": \"Easy\"}', '2026-03-02 14:13:46'),
(174, 8, 'easy', '{\"hint\": \"Return expression directly.\", \"issues\": [{\"desc\": \"Use meaningful function names\", \"icon\": \"📝\", \"title\": \"Poor Naming\"}, {\"desc\": \"z is unnecessary\", \"icon\": \"➖\", \"title\": \"Extra Variable\"}, {\"desc\": \"Use consistent indentation\", \"icon\": \"🎨\", \"title\": \"Indentation\"}], \"metrics\": {\"targetLines\": 2, \"originalLines\": 3}, \"language\": \"Python\", \"cleanCode\": \"def multiply(x, y):\\n    return x * y\", \"messyCode\": \"def f(x,y):\\n z=x*y\\n return z\", \"difficulty\": \"Easy\"}', '2026-03-02 14:13:46'),
(175, 8, 'easy', '{\"hint\": \"Return expression directly.\", \"issues\": [{\"desc\": \"c is unnecessary\", \"icon\": \"➖\", \"title\": \"Redundant Variable\"}, {\"desc\": \"Add spacing\", \"icon\": \"🎨\", \"title\": \"Formatting\"}, {\"desc\": \"Improve structure\", \"icon\": \"📝\", \"title\": \"Readability\"}], \"metrics\": {\"targetLines\": 3, \"originalLines\": 1}, \"language\": \"Java\", \"cleanCode\": \"int add(int a, int b) {\\n  return a + b;\\n}\", \"messyCode\": \"int add(int a,int b){int c=a+b;return c;}\", \"difficulty\": \"Easy\"}', '2026-03-02 14:13:47'),
(176, 8, 'easy', '{\"hint\": \"Remove unnecessary variable.\", \"issues\": [{\"desc\": \"Use descriptive names\", \"icon\": \"📝\", \"title\": \"Poor Naming\"}, {\"desc\": \"Remove $c\", \"icon\": \"➖\", \"title\": \"Extra Variable\"}, {\"desc\": \"Improve spacing\", \"icon\": \"🎨\", \"title\": \"Formatting\"}], \"metrics\": {\"targetLines\": 5, \"originalLines\": 1}, \"language\": \"PHP\", \"cleanCode\": \"<?php\\nfunction subtract($a, $b) {\\n  return $a - $b;\\n}\\n?>\", \"messyCode\": \"<?php function s($a,$b){$c=$a-$b;return $c;} ?>\", \"difficulty\": \"Easy\"}', '2026-03-02 14:13:47'),
(177, 8, 'easy', '{\"hint\": \"Return boolean expression directly.\", \"issues\": [{\"desc\": \"if-else not needed\", \"icon\": \"➖\", \"title\": \"Redundant Logic\"}, {\"desc\": \"Use meaningful function name\", \"icon\": \"📝\", \"title\": \"Naming\"}, {\"desc\": \"Use === instead of ==\", \"icon\": \"🎨\", \"title\": \"Strict Equality\"}], \"metrics\": {\"targetLines\": 3, \"originalLines\": 1}, \"language\": \"JavaScript\", \"cleanCode\": \"function isTrue(x) {\\n  return x === true;\\n}\", \"messyCode\": \"function check(x){if(x==true){return true;}else{return false;}}\", \"difficulty\": \"Easy\"}', '2026-03-02 14:13:47'),
(178, 8, 'medium', '{\"hint\": \"Return the expression directly.\", \"issues\": [{\"desc\": \"Remove result variable\", \"icon\": \"➖\", \"title\": \"Unnecessary Variable\"}, {\"desc\": \"Improve spacing\", \"icon\": \"🎨\", \"title\": \"Formatting\"}, {\"desc\": \"Simplify function body\", \"icon\": \"📝\", \"title\": \"Readability\"}], \"metrics\": {\"targetLines\": 3, \"originalLines\": 1}, \"language\": \"JavaScript\", \"cleanCode\": \"function square(n) {\\n  return n * n;\\n}\", \"messyCode\": \"function square(n){var result = n*n; return result;}\", \"difficulty\": \"Medium\"}', '2026-03-02 14:16:17'),
(179, 4, 'medium', '{\"PHP\": [\"<?php\\n    echo \\\"Hello, World!\\\";\\n?>\"], \"HTML\": [\"<div class=\\\"container\\\">\\n    <h1>Title</h1>\\n    <p>Paragraph</p>\\n</div>\"], \"Java\": [\"public class Main {\\n    public static void main(String[] args) {\\n        System.out.println(\\\"Hello\\\");\\n    }\\n}\"], \"Unix\": [\"ls -la\\ncd /home/user\\npwd\"], \"Python\": [\"print(\\\"Hello, World!\\\")\", \"def fibonacci(n):\\n    if n <= 1:\\n        return n\\n    return fibonacci(n-1) + fibonacci(n-2)\", \"class Animal:\\n    def __init__(self, name):\\n        self.name = name\"]}', '2026-03-03 00:03:41'),
(185, 4, 'medium', '{\"PHP\": [\"<?php\\nfor($i=0;$i<5;$i++){\\n echo $i;\\n}\\n?>\"], \"HTML\": [\"<ul>\\n  <li>Item 1</li>\\n  <li>Item 2</li>\\n</ul>\"], \"Java\": [\"for(int i=0;i<5;i++){\\n    System.out.println(i);\\n}\"], \"Unix\": [\"mkdir project\\ncd project\"], \"Python\": [\"for i in range(5):\\n    print(i)\", \"nums = [1, 2, 3]\\nprint(sum(nums))\"]}', '2026-03-03 00:19:17'),
(186, 4, 'medium', '{\"PHP\": [\"<?php\\n$arr = [\\\"name\\\"=>\\\"Sud\\\"];\\necho $arr[\\\"name\\\"];\\n?>\"], \"HTML\": [\"<a href=\\\"https://example.com\\\">Visit</a>\"], \"Java\": [\"String name = \\\"Sud\\\";\\nSystem.out.println(name);\"], \"Unix\": [\"touch file.txt\\nls\"], \"Python\": [\"x = {\\\"name\\\":\\\"Sud\\\",\\\"age\\\":22}\\nprint(x[\\\"name\\\"])\"]}', '2026-03-03 00:19:34'),
(187, 4, 'hard', '{\"PHP\": [\"<?php\\nfunction square($n){\\n return $n*$n;\\n}\\n?>\"], \"HTML\": [\"<form>\\n  <input type=\\\"text\\\" name=\\\"username\\\">\\n</form>\"], \"Java\": [\"int square(int n){\\n    return n*n;\\n}\"], \"Unix\": [\"grep \\\"error\\\" logfile.txt\"], \"Python\": [\"def square(n):\\n    return n*n\"]}', '2026-03-03 00:19:45'),
(188, 4, 'medium', '{\"PHP\": [\"<?php\\necho strlen(\\\"Hello\\\");\\n?>\"], \"HTML\": [\"<img src=\\\"image.jpg\\\" alt=\\\"Image\\\">\"], \"Java\": [\"System.out.println(\\\"Hello\\\".length());\"], \"Unix\": [\"whoami\\npwd\"], \"Python\": [\"print(len(\\\"Hello\\\"))\"]}', '2026-03-03 00:19:56'),
(189, 4, 'hard', '{\"PHP\": [\"<?php\\ntry {\\n  $x = 1/0;\\n} catch(Exception $e) {\\n  echo \\\"Error\\\";\\n}\\n?>\"], \"HTML\": [\"<table>\\n  <tr><td>Data</td></tr>\\n</table>\"], \"Java\": [\"try {\\n    int x = 1/0;\\n} catch(Exception e){\\n    System.out.println(\\\"Error\\\");\\n}\"], \"Unix\": [\"ps aux\\nkill -9 1234\"], \"Python\": [\"try:\\n    x = 1/0\\nexcept ZeroDivisionError:\\n    print(\\\"Error\\\")\"]}', '2026-03-03 00:20:04'),
(190, 2, 'medium', '{\"php\": [{\"code\": \"<?php\\n$x = \\\"5\\\";\\n$y = 5;\\necho $x == $y;\\n?>\", \"options\": [\"true\", \"1\", \"0\", \"false\"], \"explanation\": \"Loose comparison true → echo prints 1.\", \"correctIndex\": 1}, {\"code\": \"<?php\\n$arr = [1,2,3,4,5];\\necho count($arr)+2;\\n?>\", \"options\": [\"5\", \"7\", \"52\", \"Error\"], \"explanation\": \"5 + 2 = 7.\", \"correctIndex\": 1}, {\"code\": \"<?php\\n$name=\\\"World\\\";\\necho \\\"Hello, $name!\\\";\\n?>\", \"options\": [\"Hello, $name!\", \"Hello, World!\", \"Error\", \"Hello,World!\"], \"explanation\": \"Double quotes interpolate variables.\", \"correctIndex\": 1}, {\"code\": \"<?php\\necho 10 % 3;\\n?>\", \"options\": [\"3\", \"3.33\", \"1\", \"0\"], \"explanation\": \"Remainder is 1.\", \"correctIndex\": 2}, {\"code\": \"<?php\\necho str_repeat(\\\"*\\\",5);\\n?>\", \"options\": [\"*\", \"*5\", \"*****\", \"Error\"], \"explanation\": \"Repeats * five times.\", \"correctIndex\": 2}, {\"code\": \"<?php\\necho 5 . 5;\\n?>\", \"options\": [\"10\", \"55\", \"Error\", \"5 5\"], \"explanation\": \"Dot (.) is string concatenation in PHP.\", \"correctIndex\": 1}, {\"code\": \"<?php\\n$x = 5;\\necho $x++;\\n?>\", \"options\": [\"5\", \"6\", \"Error\", \"4\"], \"explanation\": \"Post increment prints old value.\", \"correctIndex\": 0}, {\"code\": \"<?php\\n$arr=[1,2];\\necho isset($arr[2]);\\n?>\", \"options\": [\"1\", \"0\", \"true\", \"false\"], \"explanation\": \"Index 2 does not exist → false.\", \"correctIndex\": 3}, {\"code\": \"<?php\\n$x = null;\\necho isset($x);\\n?>\", \"options\": [\"1\", \"0\", \"true\", \"false\"], \"explanation\": \"isset(null) returns false.\", \"correctIndex\": 3}, {\"code\": \"<?php\\necho strlen(\\\"PHP\\\");\\n?>\", \"options\": [\"2\", \"3\", \"4\", \"Error\"], \"explanation\": \"\\\"PHP\\\" has length 3.\", \"correctIndex\": 1}], \"java\": [{\"code\": \"int x=5;\\nint y=10;\\nSystem.out.println(x+y);\", \"options\": [\"510\", \"15\", \"Error\", \"5 + 10\"], \"explanation\": \"5 + 10 = 15.\", \"correctIndex\": 1}, {\"code\": \"String s1=\\\"Hello\\\";\\nString s2=\\\"Hello\\\";\\nSystem.out.println(s1==s2);\", \"options\": [\"true\", \"false\", \"Error\", \"null\"], \"explanation\": \"String literals are interned.\", \"correctIndex\": 0}, {\"code\": \"int x=7;\\nSystem.out.println(x/2);\", \"options\": [\"3\", \"3.5\", \"4\", \"Error\"], \"explanation\": \"Integer division truncates.\", \"correctIndex\": 0}, {\"code\": \"int x=10;\\nint y=x++;\\nSystem.out.println(y);\", \"options\": [\"10\", \"11\", \"Error\", \"9\"], \"explanation\": \"Post-increment returns old value.\", \"correctIndex\": 0}, {\"code\": \"boolean a=true;\\nboolean b=false;\\nSystem.out.println(a&&b);\", \"options\": [\"true\", \"false\", \"1\", \"0\"], \"explanation\": \"true && false = false.\", \"correctIndex\": 1}, {\"code\": \"int x=5;\\nSystem.out.println(++x);\", \"options\": [\"5\", \"6\", \"Error\", \"4\"], \"explanation\": \"Pre-increment increases first.\", \"correctIndex\": 1}, {\"code\": \"System.out.println(\\\"Java\\\".length());\", \"options\": [\"3\", \"4\", \"5\", \"Error\"], \"explanation\": \"\\\"Java\\\" has 4 characters.\", \"correctIndex\": 1}, {\"code\": \"int x=5;\\nSystem.out.println(x%2);\", \"options\": [\"1\", \"2\", \"0\", \"Error\"], \"explanation\": \"5 % 2 = 1.\", \"correctIndex\": 0}, {\"code\": \"int[] arr={1,2,3};\\nSystem.out.println(arr[2]);\", \"options\": [\"1\", \"2\", \"3\", \"Error\"], \"explanation\": \"Index starts at 0.\", \"correctIndex\": 2}, {\"code\": \"System.out.println(5 + \\\"5\\\");\", \"options\": [\"10\", \"55\", \"Error\", \"5 5\"], \"explanation\": \"String concatenation happens.\", \"correctIndex\": 1}], \"python\": [{\"code\": \"x = [1, 2, 3]\\ny = x\\ny.append(4)\\nprint(len(x))\", \"options\": [\"3\", \"4\", \"Error\", \"[1, 2, 3, 4]\"], \"explanation\": \"y references x, so original list is modified.\", \"correctIndex\": 1}, {\"code\": \"x = 10\\nif x > 5:\\n    x = x * 2\\nif x > 15:\\n    x = x - 5\\nprint(x)\", \"options\": [\"10\", \"15\", \"20\", \"25\"], \"explanation\": \"10 → 20 → 15.\", \"correctIndex\": 1}, {\"code\": \"text = \\\"Hello\\\"\\nprint(text[1:4])\", \"options\": [\"Hel\", \"ell\", \"ello\", \"Hell\"], \"explanation\": \"Slice from index 1 to 3.\", \"correctIndex\": 1}, {\"code\": \"x = \\\"abc\\\" * 3\\nprint(x)\", \"options\": [\"abc3\", \"abcabcabc\", \"aaa bbb ccc\", \"Error\"], \"explanation\": \"String repeats 3 times.\", \"correctIndex\": 1}, {\"code\": \"x = [1, 2]\\nx *= 2\\nprint(x)\", \"options\": [\"[1, 2, 1, 2]\", \"[2, 4]\", \"Error\", \"[1,2]\"], \"explanation\": \"List multiplication duplicates elements.\", \"correctIndex\": 0}, {\"code\": \"x = (1, 2, 3)\\nprint(type(x))\", \"options\": [\"<class \\\"list\\\">\", \"<class \\\"tuple\\\">\", \"tuple\", \"Error\"], \"explanation\": \"Parentheses create tuple.\", \"correctIndex\": 1}, {\"code\": \"print(bool([]))\", \"options\": [\"True\", \"False\", \"Error\", \"None\"], \"explanation\": \"Empty list is falsy.\", \"correctIndex\": 1}, {\"code\": \"x = {1,2,3}\\nx.add(4)\\nprint(len(x))\", \"options\": [\"3\", \"4\", \"Error\", \"5\"], \"explanation\": \"Set now contains 4 elements.\", \"correctIndex\": 1}, {\"code\": \"print(\\\"5\\\" + str(5))\", \"options\": [\"55\", \"10\", \"Error\", \"5 5\"], \"explanation\": \"String concatenation results in \\\"55\\\".\", \"correctIndex\": 0}, {\"code\": \"result = 10 // 3\\nprint(result)\", \"options\": [\"3\", \"3.33\", \"3.0\", \"4\"], \"explanation\": \"Floor division gives 3.\", \"correctIndex\": 0}]}', '2026-03-03 12:09:46');

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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `game_sessions`
--

INSERT INTO `game_sessions` (`session_id`, `user_id`, `game_id`, `language`, `score`, `time_taken_seconds`, `completed`, `result`, `accuracy_percentage`, `combo_max`, `started_at`, `completed_at`) VALUES
(20, 1, 4, '', 1310, 12, 1, 'WIN', 100.00, 0, '2026-03-12 12:13:38', '2026-03-12 12:13:50'),
(21, 1, 2, '', 0, NULL, 0, 'INCOMPLETE', NULL, 0, '2026-03-14 10:10:34', NULL),
(22, 2, 4, '', 1480, 28, 1, 'WIN', 93.50, 0, '2026-03-14 10:20:32', '2026-03-14 10:21:01'),
(23, 2, 4, '', 790, 9, 1, 'WIN', 100.00, 0, '2026-03-14 10:21:35', '2026-03-14 10:21:45'),
(24, 1, 7, '', 1480, 120, 1, 'LOSS', 58.33, 0, '2026-03-14 10:26:13', '2026-03-14 10:28:13'),
(25, 1, 10, '', 580, 59, 1, 'WIN', 86.00, 0, '2026-03-14 11:13:11', '2026-03-14 11:14:15'),
(26, 1, 10, '', 700, 41, 1, 'WIN', 100.00, 0, '2026-03-14 11:18:44', '2026-03-14 11:19:30'),
(27, 2, 11, '', 2200, NULL, 0, 'INCOMPLETE', 100.00, 0, '2026-03-14 18:44:12', NULL),
(28, 1, 7, '', 55420, NULL, 0, 'INCOMPLETE', 99.99, 0, '2026-03-14 18:47:18', NULL),
(29, 1, 6, '', 350, NULL, 0, 'INCOMPLETE', 87.50, 0, '2026-03-14 18:49:32', NULL),
(30, 1, 4, '', 1475, 26, 1, 'WIN', 95.50, 0, '2026-03-16 05:52:21', '2026-03-16 05:52:48'),
(31, 1, 4, '', 1100, 4, 1, 'WIN', 100.00, 0, '2026-03-16 05:53:25', '2026-03-16 05:53:29'),
(32, 1, 11, '', 0, NULL, 0, 'INCOMPLETE', NULL, 0, '2026-04-16 18:23:12', NULL),
(33, 3, 4, '', 1350, 26, 1, 'WIN', 95.00, 0, '2026-04-17 06:50:10', '2026-04-17 06:50:36'),
(34, 3, 4, '', 1445, 35, 1, 'WIN', 91.00, 0, '2026-04-20 04:38:07', '2026-04-20 04:38:43'),
(35, 1, 2, '', 0, 71, 1, 'LOSS', 0.00, 0, '2026-04-20 05:11:50', '2026-04-20 05:13:00'),
(36, 1, 4, '', 1215, 18, 1, 'WIN', 91.50, 0, '2026-04-20 06:03:23', '2026-04-20 06:03:42');

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
  `duration` int DEFAULT '0',
  `content` text,
  `status` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lesson_id`),
  KEY `course_id` (`course_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lessons_tbl`
--

INSERT INTO `lessons_tbl` (`lesson_id`, `course_id`, `category_id`, `lesson_title`, `lesson_slug`, `lesson_order`, `lesson_url`, `duration`, `content`, `status`, `created_at`) VALUES
(1, 1, 1, 'Concepts of CSS', 'concepts-of-css', 1, '/courses/1/lesson-1-concepts-of-css', NULL, 'Content for Concepts of CSS.', 1, '2025-12-17 11:02:49'),
(2, 1, 1, 'HTML forms', 'html-forms', 2, '/courses/1/lesson-2-html-forms', NULL, 'Content for HTML forms.', 1, '2025-12-17 11:02:49'),
(3, 1, 1, 'Images, Media Objects', 'images-media-objects', 3, '/courses/1/lesson-3-images-media-objects', NULL, 'Content for Images and Media Objects.', 1, '2025-12-17 11:02:49'),
(4, 2, 1, 'Bootstrap Introduction', 'bootstrap-introduction', 1, '/courses/2/lesson-1-bootstrap-introduction', NULL, 'Content for Bootstrap Introduction.', 1, '2025-12-17 11:03:26'),
(5, 2, 1, 'Grid Structure', 'grid-structure', 2, '/courses/2/lesson-2-grid-structure', NULL, 'Content for Grid Structure.', 1, '2025-12-17 11:03:26'),
(6, 2, 1, 'Table, Colours, Alerts, Form Controls', 'table-colours-alerts-form-controls', 3, '/courses/2/lesson-3-table-colours-alerts-form-controls', NULL, 'Content for Table, Colours, Alerts, Form Controls.', 1, '2025-12-17 11:03:26'),
(7, 2, 1, 'Buttons and Button Groups', 'buttons-and-button-groups', 4, '/courses/2/lesson-4-buttons-and-button-groups', NULL, 'Content for Buttons and Button Groups.', 1, '2025-12-17 11:03:26'),
(8, 2, 1, 'Pagination', 'pagination', 6, '/courses/2/lesson-6-pagination', NULL, 'Content for Pagination.', 1, '2025-12-17 11:03:26'),
(9, 2, 1, 'Bootstrap Grids', 'bootstrap-grids', 7, '/courses/2/lesson-7-bootstrap-grids', NULL, 'Content for Bootstrap Grids.', 1, '2025-12-17 11:03:26'),
(10, 2, 1, 'Bootstrap Themes', 'bootstrap-themes', 8, '/courses/2/lesson-8-bootstrap-themes', NULL, 'Content for Bootstrap Themes.', 1, '2025-12-17 11:03:26'),
(11, 3, 1, 'Overview of Client & Server-Side Scripti...', 'overview-of-client-server-side-scripting', 1, '/courses/3/lesson-9-overview-of-client-server-side-scripting', NULL, 'Content for Client & Server-Side Scripting.', 1, '2025-12-17 11:03:26'),
(12, 3, 1, 'Data types and Variables', 'data-types-and-variables', 2, '/courses/3/lesson-10-data-types-and-variables', NULL, 'Content for Data types and Variables.', 1, '2025-12-17 11:03:26'),
(13, 3, 1, 'Operators (Arthimetic, Assignment, Comp...', 'operators-arithmetic-assignment-comparison', 3, '/courses/3/lesson-11-operators-arithmetic-assignment-comparison', NULL, 'Content for Operators.', 1, '2025-12-17 11:03:26'),
(14, 3, 1, 'Control Structure', 'control-structure', 4, '/courses/3/lesson-12-control-structure', NULL, 'Content for Control Structure.', 1, '2025-12-17 11:03:26'),
(15, 3, 1, 'Java Script String and Events', 'java-script-string-and-events', 5, '/courses/3/lesson-13-java-script-string-and-events', NULL, 'Content for Java Script String and Events.', 1, '2025-12-17 11:03:26'),
(16, 4, 1, 'Creating object', 'creating-object', 1, '/courses/4/lesson-12-creating-object', NULL, 'Content for Creating object.', 1, '2025-12-17 11:03:26'),
(17, 4, 1, 'Date object', 'date-object', 2, '/courses/4/lesson-13-date-object', NULL, 'Content for Date object.', 1, '2025-12-17 11:03:26'),
(18, 4, 1, 'Document Object Model (DOM)', 'document-object-model-dom', 3, '/courses/4/lesson-14-document-object-model-dom', NULL, 'Content for Document Object Model (DOM).', 1, '2025-12-17 11:03:26'),
(19, 5, 1, 'JavaScript Functions', 'javascript-functions', 1, '/courses/5/lesson-15-javascript-functions', NULL, 'Content for JavaScript Functions.', 1, '2025-12-17 11:03:26'),
(20, 5, 1, 'Dialog boxes : Alert, confirm, prompt', 'dialog-boxes-alert-confirm-prompt', 2, '/courses/5/lesson-16-dialog-boxes-alert-confirm-prompt', NULL, 'Content for Dialog boxes.', 1, '2025-12-17 11:03:26'),
(21, 5, 1, 'Form validation', 'form-validation', 3, '/courses/5/lesson-17-form-validation', NULL, 'Content for Form validation.', 1, '2025-12-17 11:03:26'),
(22, 6, 3, 'Properties of Java', 'properties-of-java', 1, '/courses/6/lesson-18-properties-of-java', NULL, 'Content for Properties of Java.', 1, '2025-12-17 11:03:26'),
(23, 6, 3, 'Comparison of java with C++', 'comparison-of-java-with-c++', 2, '/courses/6/lesson-19-comparison-of-java-with-c++', NULL, 'Content for Comparison of java with C++.', 1, '2025-12-17 11:03:26'),
(24, 6, 3, 'Java Compiler, Java Interpreter', 'java-compiler-java-interpreter', 3, '/courses/6/lesson-20-java-compiler-java-interpreter', NULL, 'Content for Java Compiler, Java Interpreter.', 1, '2025-12-17 11:03:26'),
(25, 6, 3, 'Identifier, Literals, Operators, Variab...', 'identifier-literals-operators-variables', 4, '/courses/6/lesson-21-identifier-literals-operators-variables', NULL, 'Content for Identifier, Literals, Operators, Variables.', 1, '2025-12-17 11:03:26'),
(26, 6, 3, 'Branching: If – Else, Switch', 'branching-if-else-switch', 5, '/courses/6/lesson-22-branching-if-else-switch', NULL, 'Content for Branching.', 1, '2025-12-17 11:03:26'),
(27, 6, 3, 'Looping: While, Do-while, For', 'looping-while-do-while-for', 6, '/courses/6/lesson-23-looping-while-do-while-for', NULL, 'Content for Looping.', 1, '2025-12-17 11:03:26'),
(28, 8, 3, 'Type Casting', 'type-casting', 3, '/courses/8/lesson-24-type-casting', NULL, 'Content for Type Casting.', 1, '2025-12-17 11:03:26'),
(29, 8, 3, 'Static members, static block, static cl...', 'static-members-static-block-static-class', 4, '/courses/8/lesson-29-static-members-static-block-static-class', NULL, 'Content for Static members.', 1, '2025-12-17 11:03:26'),
(30, 8, 3, 'Interfaces', 'interfaces', 5, '/courses/8/lesson-30-interfaces', NULL, 'Content for Interfaces.', 1, '2025-12-17 11:03:26'),
(31, 8, 3, 'Strings', 'strings', 1, '/courses/8/lesson-31-strings', NULL, 'Content for Strings.', 1, '2025-12-17 11:03:26'),
(32, 8, 3, 'Introduction to Exceptions', 'introduction-to-exceptions', 2, '/courses/8/lesson-32-introduction-to-exceptions', NULL, 'Content for Introduction to Exceptions.', 1, '2025-12-17 11:03:26'),
(33, 7, 3, 'Simple Class, Field', 'simple-class-field', 1, '/courses/7/lesson-25-simple-class-field', NULL, 'Content for Simple Class, Field.', 1, '2025-12-17 11:03:26'),
(34, 7, 3, 'Access Controls, Object creation', 'access-controls-object-creation', 2, '/courses/7/lesson-26-access-controls-object-creation', NULL, 'Content for Access Controls, Object creation.', 1, '2025-12-17 11:03:26'),
(35, 7, 3, 'Construction and Initialization', 'construction-and-initialization', 3, '/courses/7/lesson-27-construction-and-initialization', NULL, 'Content for Construction and Initialization.', 1, '2025-12-17 11:03:26'),
(36, 7, 3, 'Inheritance and Polymorphism in Java', 'inheritance-and-polymorphism-in-java', 4, '/courses/7/lesson-27-inheritance-and-polymorphism-in-java', NULL, 'Content for Inheritance and Polymorphism in Java.', 1, '2025-12-17 11:03:26'),
(37, 7, 3, 'this and super keywords', 'this-and-super-keywords', 5, '/courses/7/lesson-28-this-and-super-keywords', NULL, 'Content for this and super keywords.', 1, '2025-12-17 11:03:26'),
(38, 9, 3, 'Thread', 'thread', 1, '/courses/9/lesson-33-thread', NULL, 'Content for Thread.', 1, '2025-12-17 11:03:26'),
(39, 9, 3, 'Package Naming, Type Imports', 'package-naming-type-imports', 2, '/courses/9/lesson-34-package-naming-type-imports', NULL, 'Content for Package Naming, Type Imports.', 1, '2025-12-17 11:03:26'),
(40, 9, 3, 'Implementation of Data Structure using ...', 'implementation-of-data-structure-using-java', 3, '/courses/9/lesson-35-implementation-of-data-structure-using-java', NULL, 'Content for Implementation of Data Structure.', 1, '2025-12-17 11:03:26'),
(41, 9, 3, 'Applet Basics, Applet Architecture', 'applet-basics-applet-architecture', 4, '/courses/9/lesson-36-applet-basics-applet-architecture', NULL, 'Content for Applet Basics, Applet Architecture.', 1, '2025-12-17 11:03:26'),
(42, 11, 2, 'Introduction to PHP', 'introduction-to-php', 1, '/courses/11/lesson-37-introduction-to-php', NULL, 'Content for Introduction to PHP.', 1, '2025-12-17 11:03:26'),
(43, 11, 2, 'Basic PHP Syntax and Variables', 'basic-php-syntax-and-variables', 2, '/courses/11/lesson-38-basic-php-syntax-and-variables', NULL, 'Content for Basic PHP Syntax and Variables.', 1, '2025-12-17 11:03:26'),
(44, 11, 2, 'Data Types and Operators', 'data-types-and-operators', 3, '/courses/11/lesson-39-data-types-and-operators', NULL, 'Content for Data Types and Operators.', 1, '2025-12-17 11:03:26'),
(45, 11, 2, 'Control Structures and Arrays', 'control-structures-and-arrays', 4, '/courses/11/lesson-40-control-structures-and-arrays', NULL, 'Content for Control Structures and Arrays.', 1, '2025-12-17 11:03:26'),
(46, 11, 2, 'Functions and Form Handling', 'functions-and-form-handling', 5, '/courses/11/lesson-41-functions-and-form-handling', NULL, 'Content for Functions and Form Handling.', 1, '2025-12-17 11:03:26'),
(47, 12, 2, 'File Handling and Directories', 'file-handling-and-directories', 1, '/courses/12/lesson-42-file-handling-and-directories', NULL, 'Content for File Handling and Directories.', 1, '2025-12-17 11:03:26'),
(48, 12, 2, 'Forms, Filters, and JSON', 'forms-filters-and-json', 2, '/courses/12/lesson-43-forms-filters-and-json', NULL, 'Content for Forms, Filters, and JSON.', 1, '2025-12-17 11:03:26'),
(49, 12, 2, 'Cookies, Sessions, and Emails', 'cookies-sessions-and-emails', 3, '/courses/12/lesson-44-cookies-sessions-and-emails', NULL, 'Content for Cookies, Sessions, and Emails.', 1, '2025-12-17 11:03:26'),
(50, 12, 2, 'OOP and Exception Handling in PHP', 'oop-and-exception-handling-in-php', 4, '/courses/12/lesson-45-oop-and-exception-handling-in-php', NULL, 'Content for OOP and Exception Handling in PHP.', 1, '2025-12-17 11:03:26'),
(51, 13, 2, 'PHP with MySQL/MongoDB', 'php-with-mysql-mongodb', 1, '/courses/13/lesson-46-php-with-mysql-mongodb', 0, 'Content for PHP with MySQL/MongoDB.', 1, '2025-12-17 11:03:26'),
(52, 13, 2, 'AJAX for Backend Integration', 'ajax-for-backend-integration', 2, '/courses/13/lesson-47-ajax-for-backend-integration', NULL, 'Content for AJAX for Backend Integration.', 1, '2025-12-17 11:03:26'),
(53, 13, 2, 'CodeIgniter Introduction', 'codeigniter-introduction', 3, '/courses/13/lesson-48-codeigniter-introduction', NULL, 'Content for CodeIgniter Introduction.', 1, '2025-12-17 11:03:26'),
(54, 13, 2, 'Core Features in CodeIgniter', 'core-features-in-codeigniter', 4, '/courses/13/lesson-49-core-features-in-codeigniter', NULL, 'Content for Core Features in CodeIgniter.', 1, '2025-12-17 11:03:26'),
(55, 14, 4, 'Getting Started with Python', 'getting-started-with-python', 1, '/courses/14/lesson-50-getting-started-with-python', NULL, 'Content for Getting Started with Python.', 1, '2025-12-17 11:03:26'),
(56, 14, 4, 'Python Basics', 'python-basics', 2, '/courses/14/lesson-51-python-basics', NULL, 'Content for Python Basics.', 1, '2025-12-17 11:03:26'),
(57, 14, 4, 'Control Flow Statements', 'control-flow-statements', 3, '/courses/14/lesson-52-control-flow-statements', NULL, 'Content for Control Flow Statements.', 1, '2025-12-17 11:03:26'),
(58, 16, 4, 'Input and Output', 'input-and-output', 3, '/courses/16/lesson-53-input-and-output', NULL, 'Content for Input and Output.', 1, '2025-12-17 11:03:26'),
(59, 16, 4, 'Module: Concepts of module and Using mo...', 'module-concepts-of-module-and-using-modules', 1, '/courses/16/lesson-57-module-concepts-of-module-and-using-modules', NULL, 'Content for Module Concepts.', 1, '2025-12-17 11:03:26'),
(60, 16, 4, 'Importing sqlite3 module', 'importing-sqlite3-module', 2, '/courses/16/lesson-58-importing-sqlite3-module', NULL, 'Content for Importing sqlite3 module.', 1, '2025-12-17 11:03:26'),
(61, 15, 4, 'Defining and calling functions', 'defining-and-calling-functions', 1, '/courses/15/lesson-54-defining-and-calling-functions', NULL, 'Content for Defining and calling functions.', 1, '2025-12-17 11:03:26'),
(62, 15, 4, 'Parameters, return statement', 'parameters-return-statement', 2, '/courses/15/lesson-55-parameters-return-statement', NULL, 'Content for Parameters, return statement.', 1, '2025-12-17 11:03:26'),
(63, 15, 4, 'Arrays and String Formatting', 'arrays-and-string-formatting', 3, '/courses/15/lesson-56-arrays-and-string-formatting', NULL, 'Content for Arrays and String Formatting.', 1, '2025-12-17 11:03:26'),
(64, 17, 4, 'File handling ( text and CSV files)', 'file-handling-text-and-csv-files', 1, '/courses/17/lesson-59-file-handling-text-and-csv-files', NULL, 'Content for File handling.', 1, '2025-12-17 11:03:26'),
(65, 17, 4, 'Important Classes and Functions of CSV ', 'important-classes-and-functions-of-csv-module', 2, '/courses/17/lesson-60-important-classes-and-functions-of-csv-module', NULL, 'Content for Important Classes and Functions of CSV.', 1, '2025-12-17 11:03:26'),
(66, 17, 4, 'Dataframe Handling using Panda and Numpy', 'dataframe-handling-using-panda-and-numpy', 3, '/courses/17/lesson-61-dataframe-handling-using-panda-and-numpy', NULL, 'Content for Dataframe Handling.', 1, '2025-12-17 11:03:26'),
(67, 18, 4, 'Importing matplotlib.pyplot and plottin...', 'importing-matplotlib-pyplot-and-plotting', 1, '/courses/18/lesson-62-importing-matplotlib-pyplot-and-plotting', NULL, 'Content for Importing matplotlib.pyplot.', 1, '2025-12-17 11:03:26'),
(68, 18, 4, 'Scatter plot: concept of Scatter plot, ...', 'scatter-plot-concept-of-scatter-plot', 2, '/courses/18/lesson-63-scatter-plot-concept-of-scatter-plot', NULL, 'Content for Scatter plot.', 1, '2025-12-17 11:03:26'),
(69, 18, 4, 'Line chart : Concept of line plot: plot...', 'line-chart-concept-of-line-plot', 3, '/courses/18/lesson-64-line-chart-concept-of-line-plot', NULL, 'Content for Line chart.', 1, '2025-12-17 11:03:26'),
(70, 18, 4, 'Histogram chart : Concepts of histogram...', 'histogram-chart-concepts-of-histogram', 4, '/courses/18/lesson-65-histogram-chart-concepts-of-histogram', NULL, 'Content for Histogram chart.', 1, '2025-12-17 11:03:26'),
(71, 18, 4, 'Bar Chart : Concepts of Bar chart, bar(...', 'bar-chart-concepts-of-bar-chart', 5, '/courses/18/lesson-66-bar-chart-concepts-of-bar-chart', NULL, 'Content for Bar Chart.', 1, '2025-12-17 11:03:26'),
(72, 19, 5, 'Features of Linux OS', 'features-of-linux-os', 1, '/courses/19/lesson-67-features-of-linux-os', NULL, 'Content for Features of Linux OS.', 1, '2025-12-17 11:03:26'),
(73, 19, 5, 'Components of Linux OS (Hardware, Kern...', 'components-of-linux-os', 2, '/courses/19/lesson-68-components-of-linux-os', NULL, 'Content for Components of Linux OS.', 1, '2025-12-17 11:03:26'),
(74, 19, 5, 'Shell in Linux (Bash, Zsh, Dash – Feat...', 'shell-in-linux', 3, '/courses/19/lesson-69-shell-in-linux', NULL, 'Content for Shell in Linux.', 1, '2025-12-17 11:03:26'),
(75, 19, 5, 'Introduction to Files and File Types in...', 'introduction-to-files-and-file-types-in-linux', 4, '/courses/19/lesson-70-introduction-to-files-and-file-types', NULL, 'Content for Files and File Types in Linux.', 1, '2025-12-17 11:03:26'),
(76, 19, 5, 'Linux Directory Structure and File Syst...', 'linux-directory-structure-and-file-system', 5, '/courses/19/lesson-71-linux-directory-structure-and-file-system', NULL, 'Content for Linux Directory Structure.', 1, '2025-12-17 11:03:26'),
(77, 20, 5, 'Directory Navigation Commands (pwd, cd...', 'directory-navigation-commands', 1, '/courses/20/lesson-72-directory-navigation-commands', NULL, 'Content for Directory Navigation Commands.', 1, '2025-12-17 11:03:26'),
(78, 20, 5, 'File Management Commands (cat, rm, cp, ...', 'file-management-commands', 2, '/courses/20/lesson-73-file-management-commands', NULL, 'Content for File Management Commands.', 1, '2025-12-17 11:03:26'),
(79, 20, 5, 'File Permissions and Ownership (chmod, ...', 'file-permissions-and-ownership', 3, '/courses/20/lesson-74-file-permissions-and-ownership', NULL, 'Content for File Permissions and Ownership.', 1, '2025-12-17 11:03:26'),
(80, 20, 5, 'Common System Commands (who, whoami, ma...', 'common-system-commands', 4, '/courses/20/lesson-75-common-system-commands', NULL, 'Content for Common System Commands.', 1, '2025-12-17 11:03:26'),
(81, 20, 5, 'Text Processing Commands (head, tail, c...', 'text-processing-commands', 5, '/courses/20/lesson-76-text-processing-commands', NULL, 'Content for Text Processing Commands.', 1, '2025-12-17 11:03:26'),
(82, 20, 5, 'Introduction to Process', 'introduction-to-process', 6, '/courses/20/lesson-77-introduction-to-process', NULL, 'Content for Introduction to Process.', 1, '2025-12-17 11:03:26'),
(83, 20, 5, 'Process Control commands : ps, fg, bg, ...', 'process-control-commands', 7, '/courses/20/lesson-78-process-control-commands', NULL, 'Content for Process Control commands.', 1, '2025-12-17 11:03:26'),
(84, 20, 5, 'Job Scheduling commands : at, batch, cr...', 'job-scheduling-commands', 8, '/courses/20/lesson-79-job-scheduling-commands', NULL, 'Content for Job Scheduling commands.', 1, '2025-12-17 11:03:26'),
(85, 21, 5, 'Creating and Executing Shell Scripts (n...', 'creating-and-executing-shell-scripts', 1, '/courses/21/lesson-80-creating-and-executing-shell-scripts', NULL, 'Content for Creating and Executing Shell Scripts.', 1, '2025-12-17 11:03:26'),
(86, 21, 5, 'Shell Metacharacters and Operators', 'shell-metacharacters-and-operators', 2, '/courses/21/lesson-81-shell-metacharacters-and-operators', NULL, 'Content for Shell Metacharacters and Operators.', 1, '2025-12-17 11:03:26'),
(87, 21, 5, 'Control Flow Structures (if-else, case,...', 'control-flow-structures', 3, '/courses/21/lesson-82-control-flow-structures', NULL, 'Content for Control Flow Structures.', 1, '2025-12-17 11:03:26'),
(88, 21, 5, 'Logical Operators (&&, ||, !)', 'logical-operators', 4, '/courses/21/lesson-83-logical-operators', NULL, 'Content for Logical Operators.', 1, '2025-12-17 11:03:26'),
(89, 21, 5, 'test and [ ] command for Condition Test...', 'test-and-command-for-condition-testing', 5, '/courses/21/lesson-84-test-and-command-for-condition-testing', NULL, 'Content for Condition Testing.', 1, '2025-12-17 11:03:26'),
(90, 21, 5, 'Arithmetic Operations (expr, $(( )))', 'arithmetic-operations', 6, '/courses/21/lesson-85-arithmetic-operations', NULL, 'Content for Arithmetic Operations.', 1, '2025-12-17 11:03:26'),
(91, 21, 5, 'Introduction to Regular Expressions (Ba...', 'introduction-to-regular-expressions', 1, '/courses/21/lesson-86-introduction-to-regular-expressions', NULL, 'Content for Introduction to Regular Expressions.', 1, '2025-12-17 11:03:26'),
(92, 21, 5, 'Pattern Matching using grep, egrep, and...', 'pattern-matching-using-grep-egrep-and-fgrep', 2, '/courses/21/lesson-87-pattern-matching-using-grep', NULL, 'Content for Pattern Matching using grep.', 1, '2025-12-17 11:03:26'),
(93, 21, 5, 'Stream Editing with sed (search, replac...', 'stream-editing-with-sed', 3, '/courses/21/lesson-88-stream-editing-with-sed', NULL, 'Content for Stream Editing with sed.', 1, '2025-12-17 11:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `note_downloads_tbl`
--

DROP TABLE IF EXISTS `note_downloads_tbl`;
CREATE TABLE IF NOT EXISTS `note_downloads_tbl` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note_id` int NOT NULL,
  `user_id` int NOT NULL,
  `downloaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_user` (`note_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `note_views_tbl`
--

DROP TABLE IF EXISTS `note_views_tbl`;
CREATE TABLE IF NOT EXISTS `note_views_tbl` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note_id` int NOT NULL,
  `user_id` int NOT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_view_note_user` (`note_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(5, 'Congratulations! You have completed 5% of the Linux Administration course.', 5, 1, '2025-12-17 01:58:25', 'Course', '2025-12-24 11:51:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `package_tbl`
--

INSERT INTO `package_tbl` (`package_id`, `package_name`, `price`, `valid_months`, `can_add_courses`, `can_add_videos`, `can_add_quiz`, `can_add_games`, `can_add_assignments`, `max_course`, `max_video_upload`, `package_status`, `created_at`) VALUES
(4, 'Premium', 6500.00, 6, 1, 1, 1, 1, 1, 12, 24, 1, '2025-12-16 11:27:10'),
(5, 'Pro', 3400.00, 3, 1, 1, 1, 0, 0, 8, 12, 1, '2025-12-16 11:26:08'),
(6, 'Basic', 1200.00, 1, 1, 1, 0, 0, 0, 4, 6, 1, '2025-12-16 11:25:10'),
(7, 'Booster', 1850.00, 1, 1, 1, 0, 0, 0, 5, 6, 0, '2026-01-26 09:53:03');

-- --------------------------------------------------------

--
-- Table structure for table `settings_tbl`
--

DROP TABLE IF EXISTS `settings_tbl`;
CREATE TABLE IF NOT EXISTS `settings_tbl` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings_tbl`
--

INSERT INTO `settings_tbl` (`setting_id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'website_name', 'SkillRise Academy', '2026-02-27 18:38:55'),
(3, 'key_secret', 'LLpuSxry8Kzyn8y5VWcB5Vu3', '2026-04-18 19:21:31'),
(4, 'key_id', 'rzp_test_RegvY7qWQeiLBC', '2026-04-18 19:22:18'),
(5, 'currency_symbol', '₹', '2026-02-27 18:38:55');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_package_tbl`
--

INSERT INTO `tutor_package_tbl` (`purchase_id`, `tutor_id`, `package_id`, `start_date`, `end_date`, `payment_status`, `razorpay_id`, `amount_paid`, `created_at`) VALUES
(1, 5, 6, '2026-02-10', '2026-03-10', 1, 'pay_SNvrMLSJnkQRrv', 1200.00, '2026-02-10 05:16:47'),
(2, 1, 4, '2026-03-06', '2026-04-06', 1, 'pay_SNvrLBSJnkQRrv', 2800.00, '2026-03-06 06:30:29'),
(6, 5, 5, '2026-04-17', '2026-07-17', 1, 'pay_SeYjpLSEETlOk5', 3400.00, '2026-04-17 12:26:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_profile_tbl`
--

INSERT INTO `tutor_profile_tbl` (`tutor_profile_id`, `tutor_id`, `bio`, `expertise`, `profile_pic`, `education`, `experience`, `achievements`, `country`, `languages_known`) VALUES
(1, 1, 'Experienced Web Development tutor specializing in front-end technologies and UI design.', 'HTML, CSS, JavaScript, Bootstrap', 'tutor_1_1773605274.jpg', 'M.Tech in Computer Science', '6 Years', 'Designed 50+ responsive websites; Top-rated instructor', 'India', 'English, Hindi'),
(2, 2, 'Backend developer and PHP specialist with strong database knowledge.', 'PHP, MySQL, APIs, MVC', 'tutor_2_1776598307.jpg', 'M.Tech in Computer Science', '5 Years', 'Built scalable backend systems for startups', 'India', 'English, Hindi, Marathi'),
(3, 3, 'Java programmer focused on object-oriented programming and problem-solving.', 'Java, OOP, Data Structures', 'rahul.jpg', 'M.Sc. Computer Science', '4 Years', 'Mentored 200+ students in Java', 'India', 'English, Hindi'),
(4, 4, 'Linux and system administration trainer with real-world server experience.', 'Linux, Shell Scripting, System Administration', 'kiran.jpg', 'B.Tech in Information Technology', '7 Years', 'Certified Linux Administrator', 'India', 'English, Kannada, Hindi'),
(5, 5, 'Passionate Python instructor with 5+ years of experience teaching programming to beginners and advanced learners.', 'Python Programming, Data Science, Machine Learning, Django', 'heer.jpg', 'M.Sc. in Computer Science', '5 Years Teaching Experience', 'Certified Python Developer | Google Data Analytics Certified', 'India', 'English, Hindi');

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutor_tbl`
--

INSERT INTO `tutor_tbl` (`tutor_id`, `tutor_name`, `tutor_email`, `tutor_phone`, `password`, `tutor_status`, `created_at`, `verification_status`) VALUES
(1, 'Smit Rao', 'smit@gmail.com', '9991113335', '$2y$10$dGbOnchJb1eRdkkbuICagerbc/KFTw2BkU9HijEGnYWrcxmHn2ena', 1, '2025-12-16 12:02:34', 'approved'),
(2, 'Deepa Singh', 'deepa@gmail.com', '9811223344', '$2y$10$xVCRf.xtwNoltUKKep12DOVighWJ.rsp/bXQ3r14RYUlT0t3Bfq..', 1, '2025-12-16 12:02:34', 'approved'),
(3, 'Rahul Mehta', 'rahul@gmail.com', '7778889990', '$2y$10$6WLOZFZdU8AJlJ82JiDZoubSxPop4J0g3YlsNxEvYd3bzBL514.M6', 1, '2025-12-16 12:02:34', 'approved'),
(4, 'Kiran Joshi', 'kiran@gmail.com', '8765432109', '$2y$10$BTio9mqOegJO58NZC4pqfeE1JYKSkci0Vthpny9P6Nci15r/pd60K', 1, '2025-12-16 12:02:34', 'approved'),
(5, 'Heer Rana', 'sitaramsantra07@gmail.com', '8930410057', '$2y$10$kImwrtFr94v2e.n3IDSENeW097cf50bUkX5bD70zArtw7oryXcsbK', 1, '2026-01-15 09:37:51', 'approved');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_achievements`
--

INSERT INTO `user_achievements` (`user_achievement_id`, `user_id`, `achievement_id`, `unlocked_at`, `progress_percentage`) VALUES
(1, 1, 1, '2026-04-16 18:27:37', 100.00);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_payment_tbl`
--

INSERT INTO `user_payment_tbl` (`user_payment_id`, `user_id`, `tutor_id`, `course_id`, `amount`, `razorpay_id`, `payment_status`, `payment_date`) VALUES
(1, 1, 5, 17, 3800.00, 'pay_SNwJyS14EPnf9h', 1, '2026-03-06 17:57:35'),
(2, 2, 1, 1, 1800.00, 'pay_SRCzUk0s2zhN7W', 1, '2026-03-15 00:12:54'),
(3, 1, 1, 1, 1800.00, 'pay_Sb3fwPLz46kIzL', 1, '2026-04-08 21:36:09'),
(4, 1, 5, 15, 3250.00, 'pay_SdI1wyUsvAocR6', 1, '2026-04-14 12:56:43'),
(5, 3, 2, 6, 3000.00, 'pay_SeSy3ituA4dFHE', 1, '2026-04-17 12:17:46'),
(6, 7, 4, 19, 1875.00, 'pay_SeT9Bn9T6mGsaP', 1, '2026-04-17 12:28:21'),
(7, 2, 5, 17, 3800.00, 'pay_SeY2qYYiTOhrxx', 1, '2026-04-17 17:15:48'),
(8, 1, 1, 2, 1800.00, 'pay_SfcvdFdkSAOdwQ', 1, '2026-04-20 10:41:23'),
(9, 1, 4, 21, 1875.00, 'pay_SfdeKKCRzysgpe', 1, '2026-04-20 11:23:38');

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
  `dob` date NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `profile_pic`, `user_name`, `dob`, `gender`, `city`, `user_email`, `user_password`, `mobile`, `user_status`, `created_at`) VALUES
(1, 'rahul.png', 'Rahul Sharma', '2007-03-08', 'male', 125, 'sitaramsantra07@gmail.com', '$2y$10$U7dDlyjahkm.tSzqt7Xjh.kwk.wndVmtfkjk9HoCSPEF6JaoJPBum', 9874501029, 1, '2025-12-18 18:47:05'),
(2, 'ritika_sharma.jpg', 'Priya Singh', '2007-10-06', 'female', 147, 'priya@gmail.com', '$2y$10$1VUomkmAmzy7MbgSiJAMyutBvx8tRkmbcdwAnjjajTPIDr3.VXugy', 8963201548, 1, '2025-12-18 18:47:05'),
(3, 's13.jpg', 'Amit Patel', '2004-01-21', 'male', 125, 'amit@gmail.com', '$2y$10$AexdIc84ZqkLJ3wILq3NAOasQV4JrIpfvblEmIw5xs05uPtwcIN0m', 3456789012, 1, '2025-12-18 18:47:05'),
(4, 'priya_patel.jpg', 'Neha Verma', '2009-04-29', 'female', 329, 'neha@gmail.com', '$2y$10$qrvqbVMH7L/GHyzbZ0qpM.tFFeGy7sPzQ29qlYBhAUXq6KD7QwEB2', 4567890123, 1, '2025-12-18 18:47:05'),
(5, 'rohan.jpg', 'Rohan Mehta', '2011-08-14', 'male', 147, 'rohan@gmail.com', '$2y$10$ieqWkzCCt0suCrhxU1fxROjGPyf7h.DvgVbYwDGb3FUFFj1/C05ly', 5678901234, 0, '2025-12-18 18:47:05'),
(6, 'sneha.jpg', 'Sneha Iyer', '2002-10-18', 'female', 225, 'sneha@gmail.com', '$2y$10$oE5fm53AiGtbXmsvXX4MwOivxNQ4LjPOFlJ594NbfKsmxvjXM.nhW', 1234567890, 1, '2025-12-18 18:47:05'),
(7, 'pexels-nandhukumar-12926479.jpg', 'Karan Malhotra', '2001-04-14', 'male', 147, 'karan@gmail.com', '$2y$10$fWgcqcneb/Hcq44Jfqke3usHAJeZGescQS8OtXID5O/oCpyDvahXq', 2345678901, 1, '2025-12-18 18:47:05'),
(8, 'janvi_telwala.jpg', 'Anjali Desai', '2012-12-19', 'other', 225, 'anjali@gmail.com', '$2y$10$J44g2yA.B4Tq/dqurhEcauKebtkEKYlO4hsEpiDZwgjnjlC2I0wVe', 3456789012, 1, '2025-12-18 18:47:05'),
(9, 'vikas.jpg', 'Vikas Gupta', '2011-09-12', 'male', 225, 'vikas@gmail.com', '$2y$10$KVFhUpGEf466r4Fmzheq/OEUxwqtsKHeXFMVTP2oZyIwyYOgOaaba', 4567890123, 0, '2025-12-18 18:47:05'),
(10, 'pexels-velroy-5192518.jpg', 'Poojan Nair', '2008-12-30', 'male', 329, 'poojan@gmail.com', '$2y$10$G7qwm4L49qB.C5QwtRkGh.jo.1oK6ELXfI6MQcpFGppMbbIUC/tYq', 4567890123, 1, '2025-12-18 18:47:05'),
(12, 'acc logo white.png', 'krish', '2006-04-05', 'male', 147, 'krish.chamapaneria9306@gmail.com', '$2y$10$hsLjZTBZqi3HwyhhMe1oo.2C7OwAuKSnoAIFbtV7Vk381t64b15Sm', 4578963010, 1, '2026-03-17 10:10:32');

-- --------------------------------------------------------

--
-- Table structure for table `videos_tbl`
--

DROP TABLE IF EXISTS `videos_tbl`;
CREATE TABLE IF NOT EXISTS `videos_tbl` (
  `video_id` int NOT NULL AUTO_INCREMENT,
  `tutor_id` int NOT NULL,
  `course_id` int NOT NULL,
  `lesson_id` int DEFAULT NULL,
  `video_url` varchar(500) NOT NULL,
  `video_status` tinyint(1) DEFAULT '1',
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`video_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `course_id` (`course_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `videos_tbl`
--

INSERT INTO `videos_tbl` (`video_id`, `tutor_id`, `course_id`, `lesson_id`, `video_url`, `video_status`, `uploaded_at`) VALUES
(1, 5, 17, 64, 'File Handling.mp4', 1, '2026-03-09 22:35:38'),
(2, 5, 17, 65, 'Python_Tutorial_CSV_Module_-_How_to_Read_Parse_and_Write_CSV_Files_1080P.mp4', 1, '2026-03-09 22:41:08'),
(3, 5, 17, 66, 'Complete Python Pandas Data Science Tutorial! (Reading CSV_Excel files, Sorting, Filtering, Groupby).mp4', 0, '2026-03-15 20:15:34');

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
  ADD CONSTRAINT `assignment_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`);

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
-- Constraints for table `enrollments_tbl`
--
ALTER TABLE `enrollments_tbl`
  ADD CONSTRAINT `enrollments_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`),
  ADD CONSTRAINT `enrollments_tbl_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `enrollments_tbl_ibfk_4` FOREIGN KEY (`user_payment_id`) REFERENCES `user_payment_tbl` (`user_payment_id`);

--
-- Constraints for table `feedback_tbl`
--
ALTER TABLE `feedback_tbl`
  ADD CONSTRAINT `feedback_tbl_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_tbl_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `game_challenges`
--
ALTER TABLE `game_challenges`
  ADD CONSTRAINT `game_challenges_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `fk_game_sessions_user_id_user_tbl` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_sessions_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_user_achievements_user_id_user_tbl` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`achievement_id`);

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
  ADD CONSTRAINT `fk_user_stats_user_id_user_tbl` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tbl`
--
ALTER TABLE `user_tbl`
  ADD CONSTRAINT `user_tbl_ibfk_1` FOREIGN KEY (`city`) REFERENCES `city_tbl` (`city_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `videos_tbl`
--
ALTER TABLE `videos_tbl`
  ADD CONSTRAINT `videos_tbl_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_tbl` (`course_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `videos_tbl_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor_tbl` (`tutor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `videos_tbl_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons_tbl` (`lesson_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
