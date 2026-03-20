-- Database Migration: settings_tbl and dashboard metrics

-- Table for system settings
CREATE TABLE IF NOT EXISTS `settings_tbl` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert default settings
INSERT IGNORE INTO `settings_tbl` (`setting_key`, `setting_value`) VALUES
('website_name', 'SkillRise Academy'),
('admin_email', 'sitaramsantra07@gmail.com'),
('theme_mode', 'light'),
('api_key_razorpay', 'rzp_test_example'),
('currency_symbol', '₹');
